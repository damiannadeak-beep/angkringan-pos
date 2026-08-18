<?php

namespace App\Services;

use App\Models\{Menu, Bahan, Pesanan, DetailPesanan, Promo};

class OrderService
{
    /**
     * Proses item pesanan: lock menu & bahan, validasi stok, kurangi stok, buat detail pesanan.
     *
     * @param  Pesanan  $pesanan
     * @param  array    $items  Array of ['id_menu', 'jumlah', 'catatan'?, 'variants'?]
     * @return array    ['total' => int, 'total_hpp' => int]
     *
     * @throws \Exception  Jika stok tidak mencukupi atau menu tidak tersedia.
     */
    public function processOrderItems(Pesanan $pesanan, array $items): array
    {
        // 1. Kumpulkan semua menu IDs, sort ascending untuk konsistensi lock order
        $menuIds = collect($items)->pluck('id_menu')->unique()->sort()->values()->all();

        // 2. Lock & load semua menu sekaligus (1 query, bukan N query)
        $menus = Menu::with('bahans')->whereIn('id', $menuIds)
            ->where('is_available', true)
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        foreach ($menuIds as $menuId) {
            if (!$menus->has($menuId)) {
                throw new \Exception("Gagal: Menu ID {$menuId} tidak tersedia.");
            }
        }

        // 3. Kumpulkan & lock semua bahan baku sekaligus
        $allBahanIds = $menus->flatMap(fn($m) => $m->bahans->pluck('id'))
            ->unique()->sort()->values()->all();

        $bahans = Bahan::whereIn('id', $allBahanIds)
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        // 4. Proses setiap item
        $totalHarga = 0;
        $totalHpp = 0;

        foreach ($items as $item) {
            $menu = $menus->get($item['id_menu']);

            // 4a. Kurangi stok bahan baku
            foreach ($menu->bahans as $bahanItem) {
                $bahan = $bahans->get($bahanItem->id);
                $dibutuhkan = $bahanItem->pivot->jumlah_dibutuhkan * $item['jumlah'];

                if ($bahan->stok < $dibutuhkan) {
                    throw new \Exception("Gagal: Stok bahan {$bahan->nama_bahan} tidak mencukupi untuk menu {$menu->nama_menu}.");
                }

                $bahan->decrement('stok', $dibutuhkan);
                $bahan->stok -= $dibutuhkan; // Sync in-memory
                $totalHpp += $bahan->harga_beli * $dibutuhkan;
            }

            // 4b. Hitung harga varian
            [$hargaVarian, $selectedVariants] = $this->resolveVariants($menu, $item['variants'] ?? []);

            // 4c. Hitung subtotal
            $hargaTotalPerItem = $menu->harga + $hargaVarian;
            $subtotal = $hargaTotalPerItem * $item['jumlah'];
            $totalHarga += $subtotal;

            // 4d. Buat detail pesanan
            DetailPesanan::create([
                'id_pesanan' => $pesanan->id,
                'id_menu' => $menu->id,
                'jumlah' => $item['jumlah'],
                'subtotal' => $subtotal,
                'catatan' => $item['catatan'] ?? null,
                'selected_variants' => !empty($selectedVariants) ? json_encode($selectedVariants) : null,
            ]);

            // 4e. Kurangi stok menu (kuota harian)
            if ($menu->stok >= $item['jumlah']) {
                $menu->decrement('stok', $item['jumlah']);
                $menu->stok -= $item['jumlah']; // Sync in-memory
            } else {
                throw new \Exception("Gagal: Stok produk {$menu->nama_menu} tidak mencukupi.");
            }
        }

        return ['total' => $totalHarga, 'total_hpp' => $totalHpp];
    }

    /**
     * Resolve selected variants terhadap variants_json menu (validasi harga dari backend).
     *
     * @return array [int $hargaVarian, array $selectedVariants]
     */
    private function resolveVariants(Menu $menu, array $clientVariants): array
    {
        $hargaVarian = 0;
        $selectedVariants = [];

        if (empty($clientVariants) || !$menu->variants_json) {
            return [$hargaVarian, $selectedVariants];
        }

        $menuVariants = json_decode($menu->variants_json, true);
        if (!is_array($menuVariants)) {
            return [$hargaVarian, $selectedVariants];
        }

        foreach ($clientVariants as $selVar) {
            foreach ($menuVariants as $group) {
                if (!isset($selVar['group']) || $group['group_name'] !== $selVar['group']) {
                    continue;
                }
                foreach ($group['options'] as $opt) {
                    if (!isset($selVar['name']) || $opt['name'] !== $selVar['name']) {
                        continue;
                    }

                    $qty = max(1, (int) ($selVar['qty'] ?? 1));
                    $hargaVarian += ($opt['price'] * $qty);
                    $selectedVariants[] = [
                        'group' => $group['group_name'],
                        'name' => $opt['name'],
                        'price' => $opt['price'],
                        'qty' => $qty,
                    ];
                    break 2;
                }
            }
        }

        return [$hargaVarian, $selectedVariants];
    }

    /**
     * Hitung diskon berdasarkan promo.
     *
     * @param  int       $totalHarga  Total harga sebelum diskon
     * @param  int|null  $promoId     ID promo (nullable)
     * @param  array     $items       Items dari request (untuk validasi paket)
     * @return int       Jumlah diskon
     *
     * @throws \Exception  Jika promo tidak berlaku hari ini atau syarat paket tidak terpenuhi.
     */
    public function calculateDiscount(int $totalHarga, ?int $promoId, array $items): int
    {
        if (!$promoId) {
            return 0;
        }

        $promo = Promo::with('menus')->find($promoId);
        if (!$promo || !$promo->is_active) {
            return 0;
        }

        $this->validatePromoDays($promo);

        return match ($promo->type) {
            'discount' => $this->calcDiscountType($promo, $totalHarga),
            'package' => $this->calcPackageType($promo, $totalHarga, $items),
            default => 0,
        };
    }

    /**
     * Validasi apakah promo berlaku untuk hari ini.
     */
    private function validatePromoDays(Promo $promo): void
    {
        $promoDays = is_string($promo->days) ? json_decode($promo->days, true) : $promo->days;

        if (!is_array($promoDays) || count($promoDays) === 0) {
            return;
        }

        $todayName = now()->format('l');
        if (!in_array($todayName, $promoDays)) {
            throw new \Exception("Promo '{$promo->title}' tidak berlaku untuk hari ini (" . now()->translatedFormat('l') . ").");
        }
    }

    /**
     * Hitung diskon tipe 'discount' (persentase / nominal).
     */
    private function calcDiscountType(Promo $promo, int $totalHarga): int
    {
        if ($promo->discount_type === 'percentage') {
            $discount = $totalHarga * ($promo->value / 100);
        } else {
            $discount = $promo->value;
        }

        return (int) min($discount, $totalHarga);
    }

    /**
     * Hitung diskon tipe 'package' (paket menu).
     */
    private function calcPackageType(Promo $promo, int $totalHarga, array $items): int
    {
        $packageItems = $promo->menus;
        $packageNormalPrice = 0;

        $cartMap = [];
        foreach ($items as $item) {
            if (!isset($cartMap[$item['id_menu']])) {
                $cartMap[$item['id_menu']] = 0;
            }
            $cartMap[$item['id_menu']] += $item['jumlah'];
        }

        // Hitung berapa kali paket bisa dipenuhi
        $maxPackageCount = PHP_INT_MAX;
        foreach ($packageItems as $pkgMenu) {
            $requiredQty = $pkgMenu->pivot->jumlah;
            $availableQty = $cartMap[$pkgMenu->id] ?? 0;

            if ($availableQty < $requiredQty) {
                $maxPackageCount = 0;
                break;
            }
            $maxPackageCount = min($maxPackageCount, intdiv($availableQty, $requiredQty));
            $packageNormalPrice += ($pkgMenu->harga * $requiredQty);
        }

        if ($maxPackageCount === 0 || $maxPackageCount === PHP_INT_MAX) {
            throw new \Exception("Pesanan tidak memenuhi syarat menu untuk Promo Paket '{$promo->title}'.");
        }

        $discountPerPackage = max(0, $packageNormalPrice - $promo->value);

        return $discountPerPackage * $maxPackageCount;
    }
}
