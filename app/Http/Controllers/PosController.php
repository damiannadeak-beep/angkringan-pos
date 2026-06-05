<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Bahan;
use App\Models\VoidLog;
use App\Mail\ReceiptMail;
use Illuminate\Support\Facades\Mail;
use Exception;
// PASTIKAN MODEL 'Meja' DITAMBAHKAN DI SINI
use App\Models\{Pesanan, DetailPesanan, Pembayaran, Menu, Meja, Promo};

class PosController extends Controller
{
    /**
     * Menampilkan Halaman POS untuk Kasir
     */
    public function index()
    {
        // Mengambil menu yang tersedia dan stoknya > 0
        $menus = Menu::where('is_available', true)->where('stok', '>', 0)->get();
        
        // Mengambil semua meja
        $mejas = Meja::all();

        // Mengambil promo aktif
        $promos = Promo::where('is_active', true)
            ->where(function($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->get();

        return view('kasir.pos', compact('menus', 'mejas', 'promos'));
    }

    /**
     * Menampilkan Halaman Pesanan Aktif Konsumen
     */
    public function pesananAktif()
    {
        // Menampilkan pesanan konsumen yang belum selesai ke kasir
        $orders = Pesanan::with(['meja', 'detail_pesanan.menu', 'pembayaran', 'konsumen'])
            ->whereIn('status', ['pending', 'processing'])
            ->orderBy('created_at', 'asc')
            ->get();

        return view('kasir.pesanan_aktif', compact('orders'));
    }

    /**
     * Memproses pesanan manual dari Kasir
     */
    public function storeManualOrder(Request $request)
    {
        // 1. Validasi Input Kasir
        $validated = $request->validate([
            'id_meja' => 'required|exists:meja,id',
            'tipe_pesanan' => 'required|in:dine_in,takeaway',
            'pembayaran_langsung' => 'required|boolean', // true = Cash/QRIS langsung, false = Open Bill
            'metode_pembayaran' => 'nullable|in:cash,qris,pending',
            'items' => 'required|array',
            'items.*.id_menu' => 'required|exists:menu,id',
            'items.*.jumlah' => 'required|integer|min:1',
            'promo_id' => 'nullable|exists:promos,id'
        ]);

        try {
            DB::beginTransaction();

            // 2. Buat Data Pesanan Baru
            $pesanan = Pesanan::create([
                'id_konsumen' => null, // Null karena walk-in tanpa akun
                'id_meja' => $validated['id_meja'],
                'id_kasir' => auth()->id(),
                'tipe_pesanan' => $validated['tipe_pesanan'],
                'tanggal' => now(),
                // Jika langsung bayar, status pesanan bisa langsung completed/processing
                'status' => $validated['pembayaran_langsung'] ? 'completed' : 'pending',
                'promo_id' => $validated['promo_id'] ?? null
            ]);

            $totalSemua = 0;
            $total_hpp = 0;

            // 3. Masukkan Detail Pesanan & Kurangi Stok
            foreach ($validated['items'] as $item) {
                $menu = Menu::with('bahans')->where('id', $item['id_menu'])->where('is_available', true)->lockForUpdate()->first();
                
                if (!$menu) {
                    throw new \Exception("Gagal: Menu tidak tersedia.");
                }

                // Cek dan kurangi stok bahan baku
                foreach ($menu->bahans as $bahan) {
                    $dibutuhkan = $bahan->pivot->jumlah_dibutuhkan * $item['jumlah'];
                    if ($bahan->stok < $dibutuhkan) {
                        throw new \Exception("Gagal: Stok bahan {$bahan->nama_bahan} tidak mencukupi untuk menu {$menu->nama_menu}.");
                    }
                    $bahan->decrement('stok', $dibutuhkan);
                    $total_hpp += $bahan->harga_beli * $dibutuhkan;
                }

                $subtotal = $menu->harga * $item['jumlah'];
                $totalSemua += $subtotal;

                DetailPesanan::create([
                    'id_pesanan' => $pesanan->id,
                    'id_menu' => $menu->id,
                    'jumlah' => $item['jumlah'],
                    'subtotal' => $subtotal
                ]);

                // Tetap kurangi stok menu (jika digunakan sebagai kuota/stok harian produk jadi)
                if ($menu->stok >= $item['jumlah']) {
                    $menu->decrement('stok', $item['jumlah']);
                } else {
                     throw new \Exception("Gagal: Stok produk {$menu->nama_menu} tidak mencukupi.");
                }
            }

            // 3.5. Hitung Diskon jika ada promo
            $discountAmount = 0;
            if (!empty($validated['promo_id'])) {
                $promo = Promo::find($validated['promo_id']);
                if ($promo && $promo->is_active) {
                    if ($promo->type === 'discount') {
                        if ($promo->value <= 100) { // Persentase
                            $discountAmount = $totalSemua * ($promo->value / 100);
                        } else { // Nominal
                            $discountAmount = $promo->value;
                        }
                        if ($discountAmount > $totalSemua) $discountAmount = $totalSemua; // Jangan sampai diskon melebihi tagihan
                    }
                }
            }

            $totalBayar = $totalSemua - $discountAmount;

            // Update total harga dan diskon di tabel pesanan
            $pesanan->update([
                'total' => $totalSemua,
                'discount_amount' => $discountAmount,
                'total_hpp' => $total_hpp
            ]);

            // 4. Proses Status Pembayaran
            $statusBayar = $validated['pembayaran_langsung'] ? 'paid' : 'unpaid';
            $metodeBayar = $validated['pembayaran_langsung'] ? ($validated['metode_pembayaran'] ?? 'cash') : null;

            Pembayaran::create([
                'id_pesanan' => $pesanan->id,
                'metode' => $metodeBayar,
                'status' => $statusBayar,
                'total_bayar' => $totalBayar,
                'tanggal' => $validated['pembayaran_langsung'] ? now() : null,
            ]);

            DB::commit();
            return response()->json([
                'message' => 'Pesanan manual berhasil diproses.',
                'id_pesanan' => $pesanan->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Update status pesanan dari kasir
     */
    public function updateOrderStatus(Request $request, $id_pesanan)
    {
        $validated = $request->validate([
            'status' => 'required|in:processing,completed',
        ]);

        try {
            $pesanan = Pesanan::findOrFail($id_pesanan);
            
            // Update status
            $pesanan->update([
                'status' => $validated['status'],
                'id_kasir' => auth()->id() // Kasir yang memproses pesanan
            ]);

            return response()->json([
                'message' => 'Status pesanan berhasil diupdate',
                'status' => $pesanan->status
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Update status pembayaran dari kasir
     */
    public function payOrder(Request $request, $id_pesanan)
    {
        $validated = $request->validate([
            'metode' => 'required|in:cash,qris',
            'email_pelanggan' => 'nullable|email'
        ]);

        try {
            DB::beginTransaction();
            $pesanan = Pesanan::findOrFail($id_pesanan);
            $pembayaran = Pembayaran::where('id_pesanan', $id_pesanan)->first();

            if (!$pembayaran) {
                throw new \Exception('Data pembayaran tidak ditemukan.');
            }

            if ($pembayaran->status === 'paid') {
                throw new \Exception('Pesanan ini sudah dibayar.');
            }

            $pembayaran->update([
                'status' => 'paid',
                'metode' => $validated['metode'],
                'tanggal' => now(),
            ]);

            // Jika dibayar, kasir yang menangani pembayaran ini dicatat
            $pesanan->update(['id_kasir' => auth()->id()]);

            // Kirim E-Receipt jika ada email pelanggan (opsional dari kasir) ATAU jika pesanan punya relasi konsumen dengan email
            $targetEmail = $validated['email_pelanggan'] ?? null;
            if (!$targetEmail && $pesanan->konsumen && $pesanan->konsumen->email) {
                $targetEmail = $pesanan->konsumen->email;
            }

            if ($targetEmail) {
                try {
                    Mail::to($targetEmail)->send(new ReceiptMail($pesanan));
                } catch (\Exception $mailEx) {
                    \Illuminate\Support\Facades\Log::error("Gagal mengirim e-receipt: " . $mailEx->getMessage());
                }
            }

            DB::commit();
            return response()->json([
                'message' => 'Pembayaran berhasil dikonfirmasi.',
                'id_pesanan' => $pesanan->id
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Cetak struk pesanan (Thermal 58mm)
     */
    public function printReceipt($id)
    {
        $order = Pesanan::with(['detail_pesanan.menu', 'pembayaran', 'kasir', 'meja'])->findOrFail($id);
        
        if (!$order->pembayaran || $order->pembayaran->status !== 'paid') {
            abort(403, 'Pesanan belum dibayar lunas.');
        }

        return view('kasir.receipt', compact('order'));
    }

    /**
     * Membatalkan pesanan dari kasir (Void).
     */
    public function voidOrder(Request $request, $id_pesanan)
    {
        try {
            DB::beginTransaction();
            $pesanan = Pesanan::findOrFail($id_pesanan);

            if ($pesanan->status === 'completed') {
                throw new \Exception('Pesanan sudah selesai dan tidak dapat divoid.');
            }

            // Simpan log void
            DB::table('void_logs')->insert([
                'pesanan_id' => $pesanan->id,
                'kasir_id' => auth()->id(),
                'alasan' => $request->input('alasan') ?? 'Batal',
                'total_nilai' => $pesanan->total,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Batalkan pesanan dan restore stok
            $pesanan->cancelOrder();

            DB::commit();
            return response()->json(['message' => 'Pesanan berhasil divoid. Stok telah dikembalikan.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Cetak struk dapur (tanpa harga).
     */
    public function printKitchenReceipt($id)
    {
        $order = Pesanan::with(['detail_pesanan.menu', 'meja'])->findOrFail($id);
        return view('kasir.kitchen_receipt', compact('order'));
    }

    /**
     * Menampilkan laporan tutup shift kasir
     */
    public function shiftReport()
    {
        $kasir_id = auth()->id();
        $hariIni = now()->format('Y-m-d');

        $pembayarans = Pembayaran::with('pesanan')
            ->whereHas('pesanan', function($q) use ($kasir_id) {
                $q->where('id_kasir', $kasir_id);
            })
            ->whereDate('tanggal', $hariIni)
            ->where('status', 'paid')
            ->get();

        $totalCash = $pembayarans->where('metode', 'cash')->sum('total_bayar');
        $totalQris = $pembayarans->where('metode', 'qris')->sum('total_bayar');
        $totalSemua = $totalCash + $totalQris;

        return view('kasir.shift_report', compact('totalCash', 'totalQris', 'totalSemua', 'pembayarans'));
    }

    /**
     * Memisahkan pesanan (Split Bill)
     */
    public function splitOrder(Request $request, $id_pesanan)
    {
        $validated = $request->validate([
            'split_items' => 'required|array',
            'split_items.*.id_detail' => 'required|exists:detail_pesanan,id',
            'split_items.*.jumlah' => 'required|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            $pesananAsli = Pesanan::with('detail_pesanan')->findOrFail($id_pesanan);

            if ($pesananAsli->status === 'completed' || ($pesananAsli->pembayaran && $pesananAsli->pembayaran->status === 'paid')) {
                throw new \Exception('Pesanan sudah dibayar, tidak bisa dipisah.');
            }

            // 1. Buat Pesanan Baru (Clone)
            $pesananBaru = $pesananAsli->replicate();
            $pesananBaru->total = 0;
            $pesananBaru->discount_amount = 0; // Reset diskon
            $pesananBaru->promo_id = null; // Promo tidak dipindah otomatis
            $pesananBaru->save();

            $totalBaru = 0;

            // 2. Pindahkan Detail Pesanan
            foreach ($validated['split_items'] as $item) {
                $detail = DetailPesanan::where('id', $item['id_detail'])->where('id_pesanan', $pesananAsli->id)->first();
                if ($detail) {
                    if ($item['jumlah'] < $detail->jumlah) {
                        // Pecah record detail pesanan
                        $sisaJumlah = $detail->jumlah - $item['jumlah'];
                        $hargaSatuan = $detail->subtotal / $detail->jumlah;
                        
                        $subtotalBaru = $hargaSatuan * $item['jumlah'];
                        $subtotalSisa = $hargaSatuan * $sisaJumlah;
                        
                        $detail->update([
                            'jumlah' => $sisaJumlah,
                            'subtotal' => $subtotalSisa
                        ]);

                        DetailPesanan::create([
                            'id_pesanan' => $pesananBaru->id,
                            'id_menu' => $detail->id_menu,
                            'jumlah' => $item['jumlah'],
                            'subtotal' => $subtotalBaru
                        ]);
                        $totalBaru += $subtotalBaru;
                    } else if ($item['jumlah'] >= $detail->jumlah) {
                        // Pindah seluruhnya
                        $totalBaru += $detail->subtotal;
                        $detail->update(['id_pesanan' => $pesananBaru->id]);
                    }
                }
            }

            // 3. Update Total Pesanan Baru
            $pesananBaru->update(['total' => $totalBaru]);
            Pembayaran::create([
                'id_pesanan' => $pesananBaru->id,
                'status' => 'unpaid',
                'total_bayar' => $totalBaru
            ]);

            // 4. Update Total Pesanan Lama (Asli)
            $totalAsli = DetailPesanan::where('id_pesanan', $pesananAsli->id)->sum('subtotal');
            $pesananAsli->update([
                'total' => $totalAsli,
                'promo_id' => null, // Hapus promo jika pesanan pecah
                'discount_amount' => 0
            ]);
            $pesananAsli->pembayaran()->update([
                'total_bayar' => $totalAsli
            ]);

            // Cek apakah pesanan asli jadi kosong, hapus jika iya
            if ($totalAsli == 0) {
                $pesananAsli->delete();
            }

            DB::commit();
            return response()->json(['message' => 'Pesanan berhasil dipisah.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}