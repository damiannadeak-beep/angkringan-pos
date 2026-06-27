<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\{Pesanan, DetailPesanan, Pembayaran, Menu, Meja, Promo, Setting};
use Illuminate\Support\Facades\Http;

class OrderController extends Controller
{
    /**
     * Menampilkan Menu berdasarkan scan QR Meja
     */
    public function showMenu(Request $request, $id_meja)
    {
        $meja = Meja::findOrFail($id_meja);
        
        // Pengecekan Soft Warning (Jika meja tidak tersedia dan user belum konfirmasi)
        if (!$meja->is_available && $request->query('confirm') != '1') {
            return view('konsumen.konfirmasi_meja', compact('meja'));
        }

        $menus = Menu::where('is_available', true)->where('stok', '>', 0)->get();

        // Cek apakah ada pesanan 'unpaid' aktif di meja ini (Konsep Open Bill)
        $pesananAktif = Pesanan::where('id_meja', $id_meja)
            ->where('status', '!=', 'completed')
            ->whereHas('pembayaran', function($q) {
                $q->where('status', 'unpaid');
            })->first();

        // Mengambil promo aktif
        // Mengambil promo aktif
        $promos = Promo::with('menus')->where('is_active', true)
            ->where(function($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->get();

        $promoMenuIds = [];
        foreach($promos as $promo) {
            if($promo->type == 'package') {
                foreach($promo->menus as $pm) {
                    $promoMenuIds[] = $pm->id;
                }
            }
        }

        return view('konsumen.menu', compact('meja', 'menus', 'pesananAktif', 'promos', 'promoMenuIds'));
    }

    /**
     * Menampilkan pilihan tipe pesanan (dine-in vs takeaway).
     */
    public function pilihTipePesanan()
    {
        return view('konsumen.pilih_tipe_pesanan');
    }

    /**
     * Menampilkan daftar meja untuk konsumen sebelum memesan.
     */
    public function pilihMeja()
    {
        $mejas = Meja::all();
        return view('konsumen.pilih_meja', compact('mejas'));
    }

    /**
     * Menampilkan menu untuk pesanan takeaway.
     */
    public function menuTakeaway()
    {
        $menus = Menu::where('is_available', true)->where('stok', '>', 0)->get();
        // Mengambil promo aktif
        $promos = Promo::with('menus')->where('is_active', true)
            ->where(function($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->get();
            
        $promoMenuIds = [];
        foreach($promos as $promo) {
            if($promo->type == 'package') {
                foreach($promo->menus as $pm) {
                    $promoMenuIds[] = $pm->id;
                }
            }
        }
            
        return view('konsumen.menu_takeaway', compact('menus', 'promos', 'promoMenuIds'));
    }

    /**
     * Menampilkan menu untuk pesanan Dine-In dari jarak jauh (tanpa meja).
     */
    public function menuNanti()
    {
        $menus = Menu::where('is_available', true)->where('stok', '>', 0)->get();
        
        $promos = Promo::with('menus')->where('is_active', true)
            ->where(function($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->get();
            
        $promoMenuIds = [];
        foreach($promos as $promo) {
            if($promo->type == 'package') {
                foreach($promo->menus as $pm) {
                    $promoMenuIds[] = $pm->id;
                }
            }
        }
            
        return view('konsumen.menu_nanti', compact('menus', 'promos', 'promoMenuIds'));
    }

    /**
     * Menambahkan item ke pesanan aktif atau membuat pesanan baru (Open Bill)
     */
    public function tambahPesanan(Request $request)
    {
        // 1. Validasi Input (Standar Keamanan)
        $validated = $request->validate([
            'id_meja' => 'nullable|integer',
            'tipe_pesanan' => 'nullable|in:dine_in,takeaway',
            'items' => 'required|array',
            'items.*.id_menu' => 'required|exists:menu,id',
            'items.*.jumlah' => 'required|integer|min:1',
            'items.*.catatan' => 'nullable|string|max:255',
            'items.*.variants' => 'nullable|array',
            'promo_id' => 'nullable|exists:promos,id'
        ]);

        try {
            DB::beginTransaction();

            // Tentukan tipe pesanan
            $tipe_pesanan = $validated['tipe_pesanan'] ?? 'dine_in';
            $id_meja = $validated['id_meja'] ?? null;

            // Jika tipe adalah takeaway, set id_meja menjadi null
            if ($tipe_pesanan === 'takeaway') {
                $id_meja = null;
            }

            // 2. Cari Pesanan Aktif (Open Bill) hanya untuk dine_in dengan id_meja yang sama
            $pesanan = null;
            // Dinonaktifkan agar setiap pesanan konsumen masuk sebagai tiket/pesanan baru di kasir

            // 3. Jika tidak ada, buat Pesanan & Pembayaran Baru
            if (!$pesanan) {
                $pesanan = Pesanan::create([
                    'id_konsumen' => auth()->id(),
                    'id_meja' => $id_meja,
                    'tipe_pesanan' => $tipe_pesanan,
                    'tanggal' => now(),
                    'status' => 'pending',
                ]);

                Pembayaran::create([
                    'id_pesanan' => $pesanan->id,
                    'status' => 'unpaid'
                ]);
            }

            // =====================================================================
            // FIX #1 (Deadlock Prevention): Lock semua row dalam urutan ID ascending
            // FIX #2 (N+1 Query): Batch-load semua menu & bahan sekaligus di luar loop
            // =====================================================================

            // 4a. Kumpulkan semua menu IDs, sort ascending untuk konsistensi lock order
            $menuIds = collect($validated['items'])->pluck('id_menu')->unique()->sort()->values()->all();

            // 4b. Lock & load semua menu sekaligus (1 query, bukan N query)
            $menus = Menu::with('bahans')->whereIn('id', $menuIds)
                ->where('is_available', true)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // Validasi semua menu ditemukan
            foreach ($menuIds as $menuId) {
                if (!$menus->has($menuId)) {
                    throw new \Exception("Produk ID {$menuId} tidak ditemukan atau tidak tersedia.");
                }
            }

            // 4c. Kumpulkan semua bahan IDs dari seluruh menu, sort ascending, lock sekaligus
            $allBahanIds = collect();
            foreach ($menus as $menu) {
                foreach ($menu->bahans as $bahan) {
                    $allBahanIds->push($bahan->id);
                }
            }
            $allBahanIds = $allBahanIds->unique()->sort()->values()->all();

            $bahans = \App\Models\Bahan::whereIn('id', $allBahanIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // 4d. Proses setiap item dari data yang sudah di-load (tanpa query tambahan)
            $tambahanTotal = 0;
            $tambahanHPP = 0;
            foreach ($validated['items'] as $item) {
                $menu = $menus->get($item['id_menu']);

                // Cek dan kurangi stok bahan baku (dari collection, bukan query baru)
                foreach ($menu->bahans as $bahanItem) {
                    $bahan = $bahans->get($bahanItem->id);
                    $dibutuhkan = $bahanItem->pivot->jumlah_dibutuhkan * $item['jumlah'];
                    if ($bahan->stok < $dibutuhkan) {
                        throw new \Exception("Stok bahan {$bahan->nama_bahan} tidak mencukupi untuk produk {$menu->nama_menu}.");
                    }
                    $bahan->decrement('stok', $dibutuhkan);
                    $bahan->stok -= $dibutuhkan; // Sync in-memory agar iterasi berikutnya akurat
                    $tambahanHPP += $bahan->harga_beli * $dibutuhkan;
                }

                $hargaBase = $menu->harga;
                $hargaVarian = 0;
                $selectedVariants = [];

                if (!empty($item['variants']) && is_array($item['variants']) && $menu->variants_json) {
                    $menuVariants = json_decode($menu->variants_json, true);
                    if (is_array($menuVariants)) {
                        foreach ($item['variants'] as $selVar) {
                            // Validasi harga dari backend
                            foreach ($menuVariants as $g) {
                                if (isset($selVar['group']) && $g['group_name'] === $selVar['group']) {
                                    foreach ($g['options'] as $opt) {
                                        if (isset($selVar['name']) && $opt['name'] === $selVar['name']) {
                                            $hargaVarian += $opt['price'];
                                            $selectedVariants[] = [
                                                'group' => $g['group_name'],
                                                'name' => $opt['name'],
                                                'price' => $opt['price']
                                            ];
                                            break 2;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                $hargaTotalPerItem = $hargaBase + $hargaVarian;
                $subtotal = $hargaTotalPerItem * $item['jumlah'];
                $tambahanTotal += $subtotal;

                // Append ke detail pesanan
                DetailPesanan::create([
                    'id_pesanan' => $pesanan->id,
                    'id_menu' => $menu->id,
                    'jumlah' => $item['jumlah'],
                    'subtotal' => $subtotal,
                    'catatan' => $item['catatan'] ?? null,
                    'selected_variants' => !empty($selectedVariants) ? json_encode($selectedVariants) : null
                ]);

                // Kurangi stok menu
                if ($menu->stok >= $item['jumlah']) {
                    $menu->decrement('stok', $item['jumlah']);
                    $menu->stok -= $item['jumlah']; // Sync in-memory
                } else {
                    throw new \Exception("Stok produk {$menu->nama_menu} tidak mencukupi.");
                }
            }

            // 5. Update Total Keseluruhan
            $pesanan->total += $tambahanTotal;
            $pesanan->total_hpp += $tambahanHPP;
            
            // 6. Handle Promo
            $discountAmount = 0;
            if (!empty($validated['promo_id'])) {
                $promo = Promo::find($validated['promo_id']);
                if ($promo && $promo->is_active) {
                    $pesanan->promo_id = $promo->id;
                }
            }
            
            if ($pesanan->promo_id) {
                $promo = Promo::find($pesanan->promo_id);
                if ($promo && $promo->is_active && $promo->type === 'discount') {
                    if ($promo->discount_type === 'percentage') {
                        $discountAmount = $pesanan->total * ($promo->value / 100);
                    } else { // Nominal
                        $discountAmount = $promo->value;
                    }
                    if ($discountAmount > $pesanan->total) $discountAmount = $pesanan->total;
                }
            }
            
            $pesanan->discount_amount = $discountAmount;
            $pesanan->save();

            $pesanan->pembayaran()->update([
                'total_bayar' => $pesanan->total - $pesanan->discount_amount
            ]);

            DB::commit();

            // Trigger Push Notification to Admin and Kasir
            $adminsAndKasirs = \App\Models\User::role(['pemilik', 'kasir'])->get();
            \Illuminate\Support\Facades\Notification::send($adminsAndKasirs, new \App\Notifications\WebPushNotification(
                'Pesanan Baru Masuk!',
                'Order #' . $pesanan->id . ' baru saja dibuat. Segera cek pesanan aktif.',
                '/kasir/pesanan-aktif'
            ));

            return response()->json(['message' => 'Pesanan berhasil ditambahkan', 'id_pesanan' => $pesanan->id]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Membatalkan pesanan dari sisi konsumen (sebelum dibayar/diproses).
     */
    public function cancelOrder(Request $request, $id_pesanan)
    {
        try {
            DB::beginTransaction();

            $pesanan = Pesanan::with(['pembayaran'])->findOrFail($id_pesanan);

            if ($pesanan->id_konsumen != auth()->id()) {
                throw new \Exception('Anda tidak berhak membatalkan pesanan ini.');
            }

            if ($pesanan->status !== 'pending' || ($pesanan->pembayaran && $pesanan->pembayaran->status === 'paid')) {
                throw new \Exception('Pesanan sudah diproses atau dibayar, tidak dapat dibatalkan.');
            }

            $pesanan->cancelOrder();

            DB::commit();
            return response()->json(['message' => 'Pesanan berhasil dibatalkan.']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Memanggil Pelayan (Call Bell) dari meja.
     */
    public function callBell(Request $request)
    {
        $validated = $request->validate([
            'id_meja' => 'required|exists:meja,id'
        ]);

        $meja = Meja::findOrFail($validated['id_meja']);

        // Mencegah spam (misal: cek apakah ada notifikasi call_bell untuk meja ini dalam 2 menit terakhir)
        $recentCall = \App\Models\Notification::where('type', 'call_bell')
            ->where('id_meja', $meja->id)
            ->where('created_at', '>=', now()->subMinutes(2))
            ->first();

        if ($recentCall) {
            return response()->json(['error' => 'Pelayan sudah dipanggil. Mohon tunggu sebentar.'], 429);
        }

        \App\Models\Notification::create([
            'type' => 'call_bell',
            'message' => 'Panggilan Meja ' . $meja->nomor_meja,
            'id_meja' => $meja->id,
            'is_read' => false
        ]);

        // Trigger Push Notification to Admin and Kasir
        $adminsAndKasirs = \App\Models\User::role(['pemilik', 'kasir'])->get();
        \Illuminate\Support\Facades\Notification::send($adminsAndKasirs, new \App\Notifications\WebPushNotification(
            'Panggilan Meja!',
            'Konsumen di Meja ' . $meja->nomor_meja . ' memanggil pelayan.',
            '/kasir/pos'
        ));

        return response()->json(['message' => 'Pelayan segera datang ke meja Anda.']);
    }


}