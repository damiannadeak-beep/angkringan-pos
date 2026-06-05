<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\{Pesanan, DetailPesanan, Pembayaran, Menu, Meja, Promo};

class OrderController extends Controller
{
    /**
     * Menampilkan Menu berdasarkan scan QR Meja
     */
    public function showMenu($id_meja)
    {
        $meja = Meja::findOrFail($id_meja);
        $menus = Menu::where('is_available', true)->where('stok', '>', 0)->get();

        // Cek apakah ada pesanan 'unpaid' aktif di meja ini (Konsep Open Bill)
        $pesananAktif = Pesanan::where('id_meja', $id_meja)
            ->where('status', '!=', 'completed')
            ->whereHas('pembayaran', function($q) {
                $q->where('status', 'unpaid');
            })->first();

        // Mengambil promo aktif
        $promos = Promo::where('is_active', true)
            ->where(function($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->get();

        return view('konsumen.menu', compact('meja', 'menus', 'pesananAktif', 'promos'));
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
        $promos = Promo::where('is_active', true)
            ->where(function($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->get();
            
        return view('konsumen.menu_takeaway', compact('menus', 'promos'));
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
            if ($tipe_pesanan === 'dine_in' && $id_meja) {
                $pesanan = Pesanan::where('id_meja', $id_meja)
                    ->where('status', '!=', 'completed')
                    ->whereHas('pembayaran', function($q) {
                        $q->where('status', 'unpaid');
                    })->first();
            }

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

            // 4. Masukkan Detail Pesanan
            $tambahanTotal = 0;
            $tambahanHPP = 0;
            foreach ($validated['items'] as $item) {
                $menu = Menu::with('bahans')->where('id', $item['id_menu'])->where('is_available', true)->lockForUpdate()->first();
                
                if (!$menu) {
                    throw new \Exception("Produk tidak ditemukan atau tidak tersedia.");
                }

                // Cek dan kurangi stok bahan baku
                foreach ($menu->bahans as $bahan) {
                    $dibutuhkan = $bahan->pivot->jumlah_dibutuhkan * $item['jumlah'];
                    if ($bahan->stok < $dibutuhkan) {
                        throw new \Exception("Stok bahan {$bahan->nama_bahan} tidak mencukupi untuk produk {$menu->nama_menu}.");
                    }
                    $bahan->decrement('stok', $dibutuhkan);
                    $tambahanHPP += $bahan->harga_beli * $dibutuhkan;
                }

                $subtotal = $menu->harga * $item['jumlah'];
                $tambahanTotal += $subtotal;

                // Append ke detail pesanan
                DetailPesanan::create([
                    'id_pesanan' => $pesanan->id,
                    'id_menu' => $menu->id,
                    'jumlah' => $item['jumlah'],
                    'subtotal' => $subtotal
                ]);

                // Kurangi stok menu
                if ($menu->stok >= $item['jumlah']) {
                    $menu->decrement('stok', $item['jumlah']);
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
                    if ($promo->value <= 100) { // Persentase
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
            return response()->json(['message' => 'Pesanan berhasil ditambahkan', 'id_pesanan' => $pesanan->id]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Menampilkan Riwayat Pesanan Konsumen
     */
    public function riwayatPesanan()
    {
        $riwayat = Pesanan::with(['detail_pesanan.menu', 'pembayaran'])
            ->where('id_konsumen', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('konsumen.riwayat', compact('riwayat'));
    }

    /**
     * Pengecekan entitas meja virtual khusus takeaway
     */
    public function isTakeaway($id_meja) {
        return $id_meja == config('app.takeaway_table_id'); 
    }

    /**
     * Membatalkan pesanan dari sisi konsumen (sebelum dibayar/diproses).
     */
    public function cancelOrder(Request $request, $id_pesanan)
    {
        try {
            DB::beginTransaction();

            $pesanan = Pesanan::with(['pembayaran'])->findOrFail($id_pesanan);

            // Validasi: hanya bisa dibatalkan jika milik konsumen yang sedang login
            if ($pesanan->id_konsumen !== auth()->id()) {
                throw new \Exception('Anda tidak berhak membatalkan pesanan ini.');
            }

            // Hanya bisa batal jika status belum dibayar (unpaid) dan pesanan pending
            if ($pesanan->status !== 'pending' || ($pesanan->pembayaran && $pesanan->pembayaran->status === 'paid')) {
                throw new \Exception('Pesanan sudah diproses atau dibayar, tidak dapat dibatalkan.');
            }

            // Lakukan pembatalan dan restore stok
            $pesanan->cancelOrder();

            DB::commit();
            return response()->json(['message' => 'Pesanan berhasil dibatalkan.']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}