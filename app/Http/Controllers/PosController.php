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
use App\Models\{Pesanan, DetailPesanan, Pembayaran, Menu, Meja, Promo, Setting};
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer;
use Illuminate\Support\Facades\Hash;

class PosController extends Controller
{
    /**
     * Menampilkan Halaman POS untuk Kasir
     */
    public function index()
    {
        // Mengambil menu yang tersedia, termasuk yang stok habis agar kasir tetap bisa melihatnya
        $menus = Menu::where('is_available', true)->get();
        
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
            ->where(function ($query) {
                $query->whereIn('status', ['pending', 'processing'])
                      ->orWhere(function ($q) {
                          $q->where('status', 'completed')
                            ->whereHas('pembayaran', function ($p) {
                                $p->where('status', '!=', 'paid');
                            });
                      });
            })
            ->orderBy('created_at', 'asc')
            ->get();

        return view('kasir.pesanan_aktif', compact('orders'));
    }

    /**
     * Mengambil jumlah pesanan aktif untuk badge notifikasi
     */
    public function activeOrdersCount()
    {
        $count = Pesanan::whereIn('status', ['pending', 'processing'])
            ->orWhere(function ($q) {
                $q->where('status', 'completed')
                  ->whereHas('pembayaran', function ($p) {
                      $p->where('status', '!=', 'paid');
                  });
            })
            ->count();

        return response()->json(['count' => $count]);
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
            'items.*.catatan' => 'nullable|string|max:255',
            'items.*.variants' => 'nullable|array',
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
                // Selalu pending agar muncul di pesanan aktif untuk diproses dapur meskipun sudah dibayar
                'status' => 'pending',
                'promo_id' => $validated['promo_id'] ?? null
            ]);

            // Otomatis matikan ketersediaan meja jika ini pesanan dine-in
            if ($validated['id_meja'] && $validated['tipe_pesanan'] === 'dine_in') {
                \App\Models\Meja::where('id', $validated['id_meja'])->update(['is_available' => false]);
            }

            $totalSemua = 0;
            $total_hpp = 0;

            // =====================================================================
            // FIX #1 (Deadlock Prevention): Lock semua row dalam urutan ID ascending
            // FIX #2 (N+1 Query): Batch-load semua menu & bahan sekaligus di luar loop
            // =====================================================================

            // 3a. Kumpulkan semua menu IDs, sort ascending
            $menuIds = collect($validated['items'])->pluck('id_menu')->unique()->sort()->values()->all();

            // 3b. Lock & load semua menu sekaligus
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

            // 3c. Kumpulkan & lock semua bahan baku sekaligus
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

            // 3d. Masukkan Detail Pesanan & Kurangi Stok (dari collection, tanpa query tambahan)
            foreach ($validated['items'] as $item) {
                $menu = $menus->get($item['id_menu']);

                // Cek dan kurangi stok bahan baku
                foreach ($menu->bahans as $bahanItem) {
                    $bahan = $bahans->get($bahanItem->id);
                    $dibutuhkan = $bahanItem->pivot->jumlah_dibutuhkan * $item['jumlah'];
                    if ($bahan->stok < $dibutuhkan) {
                        throw new \Exception("Gagal: Stok bahan {$bahan->nama_bahan} tidak mencukupi untuk menu {$menu->nama_menu}.");
                    }
                    $bahan->decrement('stok', $dibutuhkan);
                    $bahan->stok -= $dibutuhkan; // Sync in-memory
                    $total_hpp += $bahan->harga_beli * $dibutuhkan;
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
                                            $qty = isset($selVar['qty']) ? (int) $selVar['qty'] : 1;
                                            if ($qty < 1) $qty = 1;

                                            $hargaVarian += ($opt['price'] * $qty);
                                            $selectedVariants[] = [
                                                'group' => $g['group_name'],
                                                'name' => $opt['name'],
                                                'price' => $opt['price'],
                                                'qty' => $qty
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
                $totalSemua += $subtotal;

                DetailPesanan::create([
                    'id_pesanan' => $pesanan->id,
                    'id_menu' => $menu->id,
                    'jumlah' => $item['jumlah'],
                    'subtotal' => $subtotal,
                    'catatan' => $item['catatan'] ?? null,
                    'selected_variants' => !empty($selectedVariants) ? json_encode($selectedVariants) : null
                ]);

                // Tetap kurangi stok menu (jika digunakan sebagai kuota/stok harian produk jadi)
                if ($menu->stok >= $item['jumlah']) {
                    $menu->decrement('stok', $item['jumlah']);
                    $menu->stok -= $item['jumlah']; // Sync in-memory
                } else {
                     throw new \Exception("Gagal: Stok produk {$menu->nama_menu} tidak mencukupi.");
                }
            }

            // 3.5. Hitung Diskon jika ada promo
            $discountAmount = 0;
            if (!empty($validated['promo_id'])) {
                $promo = Promo::find($validated['promo_id']);
                if ($promo && $promo->is_active) {
                    
                    // Filter Hari Promo
                    $todayName = now()->format('l');
                    $promoDays = is_string($promo->days) ? json_decode($promo->days, true) : $promo->days;
                    if (is_array($promoDays) && count($promoDays) > 0) {
                        if (!in_array($todayName, $promoDays)) {
                            throw new \Exception("Promo '{$promo->title}' tidak berlaku untuk hari ini (" . now()->translatedFormat('l') . ").");
                        }
                    }

                    if ($promo->type === 'discount') {
                        if ($promo->discount_type === 'percentage') {
                            $discountAmount = $totalSemua * ($promo->value / 100);
                        } else { // Nominal
                            $discountAmount = $promo->value;
                        }
                        if ($discountAmount > $totalSemua) $discountAmount = $totalSemua; // Jangan sampai diskon melebihi tagihan
                    } else if ($promo->type === 'package') {
                        // =====================================================================
                        // FIX #5: Promo Paket Multiple — hitung berapa kali paket terpenuhi
                        // =====================================================================
                        $packageItems = $promo->menus;
                        $packageNormalPrice = 0;
                        
                        $cartMap = [];
                        foreach ($validated['items'] as $item) {
                            if (!isset($cartMap[$item['id_menu']])) $cartMap[$item['id_menu']] = 0;
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

                        // Diskon per paket x jumlah paket yang terpenuhi
                        $discountPerPackage = $packageNormalPrice - $promo->value;
                        if ($discountPerPackage < 0) $discountPerPackage = 0;
                        $discountAmount = $discountPerPackage * $maxPackageCount;
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

            // Notify Customer via Web Push
            if ($pesanan->konsumen) {
                $statusText = $validated['status'] === 'completed' ? 'Selesai' : 'Diproses';
                $pesanan->konsumen->notify(new \App\Notifications\WebPushNotification(
                    'Pesanan ' . $statusText,
                    'Pesanan Anda (Order #' . $pesanan->id . ') saat ini ' . strtolower($statusText) . '.',
                    '/konsumen/profil'
                ));
            }

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

            // Notify Customer via Web Push
            if ($pesanan->konsumen) {
                $pesanan->konsumen->notify(new \App\Notifications\WebPushNotification(
                    'Pembayaran Diterima',
                    'Pembayaran untuk Order #' . $pesanan->id . ' telah dikonfirmasi oleh Kasir.',
                    '/konsumen/profil'
                ));
            }

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
     * Cetak struk langsung ke Printer Thermal (Raw ESC/POS Network)
     */
    public function printThermalReceipt($id)
    {
        $order = Pesanan::with(['detail_pesanan.menu', 'pembayaran', 'kasir', 'meja'])->findOrFail($id);
        
        if (!$order->pembayaran || $order->pembayaran->status !== 'paid') {
            return response()->json(['error' => 'Pesanan belum dibayar lunas.'], 403);
        }

        $printer_active = Setting::getVal('printer_active') == '1';
        $printer_ip = Setting::getVal('printer_ip');
        $printer_port = Setting::getVal('printer_port', 9100);

        if (!$printer_active || empty($printer_ip)) {
            return response()->json(['error' => 'Fitur printer thermal tidak aktif atau IP belum diatur di Pengaturan.'], 400);
        }

        try {
            $connector = new NetworkPrintConnector($printer_ip, $printer_port);
            $printer = new Printer($connector);
            
            // Pengaturan Struk
            $storeName = Setting::getVal('store_name', 'Angkringan POS');
            $storeAddress = Setting::getVal('store_address', '');
            
            // Header
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setEmphasis(true);
            $printer->text($storeName . "\n");
            $printer->setEmphasis(false);
            $printer->text($storeAddress . "\n");
            $printer->text("--------------------------------\n");

            // Info Pesanan
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text("Waktu   : " . \Carbon\Carbon::parse($order->pembayaran->tanggal)->format('d/m/Y H:i') . "\n");
            $printer->text("Kasir   : " . ($order->kasir->name ?? 'Kasir') . "\n");
            $printer->text("Meja    : " . ($order->meja->nama_meja_atau_nomor ?? '-') . "\n");
            $printer->text("Metode  : " . strtoupper($order->pembayaran->metode ?? '-') . "\n");
            $printer->text("--------------------------------\n");

            // Items
            foreach ($order->detail_pesanan as $detail) {
                $namaMenu = substr($detail->menu->nama_menu, 0, 20); // Potong jika kepanjangan
                $qty = str_pad($detail->jumlah . "x", 4, " ", STR_PAD_RIGHT);
                $harga = str_pad(number_format($detail->subtotal, 0, ',', '.'), 8, " ", STR_PAD_LEFT);
                
                $printer->text($namaMenu . "\n");
                $printer->text("    " . $qty . $harga . "\n");
                
                if (!empty($detail->selected_variants)) {
                    $variants = json_decode($detail->selected_variants, true);
                    if (is_array($variants) && count($variants) > 0) {
                        $varText = implode(', ', array_column($variants, 'name'));
                        $printer->text("    - " . substr($varText, 0, 26) . "\n");
                    }
                }

                if (!empty($detail->catatan)) {
                    $printer->text("    * " . substr($detail->catatan, 0, 26) . "\n");
                }
            }
            $printer->text("--------------------------------\n");

            // Total
            $printer->setJustification(Printer::JUSTIFY_RIGHT);
            if ($order->discount_amount > 0) {
                $printer->text("Subtotal : Rp " . number_format($order->total, 0, ',', '.') . "\n");
                $printer->text("Diskon   : Rp " . number_format($order->discount_amount, 0, ',', '.') . "\n");
            }
            $printer->setEmphasis(true);
            $printer->text("TOTAL : Rp " . number_format($order->pembayaran->total_bayar, 0, ',', '.') . "\n");
            $printer->setEmphasis(false);
            $printer->text("--------------------------------\n");

            // Footer
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $footerText = Setting::getVal('receipt_footer', 'Terima kasih atas kunjungan Anda!');
            $printer->text(str_replace('\n', "\n", $footerText) . "\n\n\n\n\n");

            // Potong kertas
            $printer->cut();
            $printer->close();

            return response()->json(['message' => 'Struk berhasil dikirim ke printer thermal.']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal terhubung ke printer (' . $printer_ip . ':' . $printer_port . '). Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Membatalkan pesanan dari kasir (Void).
     */
    public function voidOrder(Request $request, $id_pesanan)
    {
        try {
            DB::beginTransaction();
            $pesanan = Pesanan::findOrFail($id_pesanan);

            if (!Hash::check($request->input('password'), auth()->user()->password)) {
                throw new \Exception('Password yang dimasukkan salah.');
            }

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
        
        $shift = \App\Models\KasirShift::where('user_id', $kasir_id)->latest('id')->first();
        if (!$shift) {
            return redirect()->back()->with('error', 'Tidak ada data shift ditemukan.');
        }

        $query = Pembayaran::with('pesanan')
            ->whereHas('pesanan', function($q) use ($kasir_id) {
                $q->where('id_kasir', $kasir_id);
            })
            ->where('status', 'paid')
            ->where('updated_at', '>=', $shift->waktu_buka);

        if ($shift->waktu_tutup) {
            $query->where('updated_at', '<=', $shift->waktu_tutup);
        }
        
        $pembayarans = $query->get();

        $totalCash = $pembayarans->where('metode', 'cash')->sum('total_bayar');
        $totalQris = $pembayarans->where('metode', 'qris')->sum('total_bayar');
        $totalSemua = $totalCash + $totalQris;

        // Hitung rekap menu terjual
        $rekapMenu = [];
        $totalItemTerjual = 0;
        foreach ($pembayarans as $pay) {
            if ($pay->pesanan) {
                foreach ($pay->pesanan->detail_pesanan as $detail) {
                    if ($detail->menu) {
                        $nama = $detail->menu->nama_menu;
                        if (!isset($rekapMenu[$nama])) {
                            $rekapMenu[$nama] = ['jumlah' => 0, 'subtotal' => 0];
                        }
                        $rekapMenu[$nama]['jumlah'] += $detail->jumlah;
                        $rekapMenu[$nama]['subtotal'] += $detail->subtotal;
                        $totalItemTerjual += $detail->jumlah;
                    }
                }
            }
        }

        return view('kasir.shift_report', compact('totalCash', 'totalQris', 'totalSemua', 'pembayarans', 'shift', 'rekapMenu', 'totalItemTerjual'));
    }

    public function exportShiftReportPdf()
    {
        $kasir_id = auth()->id();
        
        $shift = \App\Models\KasirShift::where('user_id', $kasir_id)->latest('id')->first();
        if (!$shift) {
            return redirect()->back()->with('error', 'Tidak ada data shift ditemukan.');
        }

        $query = Pembayaran::with('pesanan.detail_pesanan.menu')
            ->whereHas('pesanan', function($q) use ($kasir_id) {
                $q->where('id_kasir', $kasir_id);
            })
            ->where('status', 'paid')
            ->where('updated_at', '>=', $shift->waktu_buka);

        if ($shift->waktu_tutup) {
            $query->where('updated_at', '<=', $shift->waktu_tutup);
        }

        $pembayarans = $query->get();

        $totalCash = $pembayarans->where('metode', 'cash')->sum('total_bayar');
        $totalQris = $pembayarans->where('metode', 'qris')->sum('total_bayar');
        $totalSemua = $totalCash + $totalQris;

        // Hitung rekap menu terjual
        $rekapMenu = [];
        $totalItemTerjual = 0;
        foreach ($pembayarans as $pay) {
            if ($pay->pesanan) {
                foreach ($pay->pesanan->detail_pesanan as $detail) {
                    if ($detail->menu) {
                        $nama = $detail->menu->nama_menu;
                        if (!isset($rekapMenu[$nama])) {
                            $rekapMenu[$nama] = ['jumlah' => 0, 'subtotal' => 0];
                        }
                        $rekapMenu[$nama]['jumlah'] += $detail->jumlah;
                        $rekapMenu[$nama]['subtotal'] += $detail->subtotal;
                        $totalItemTerjual += $detail->jumlah;
                    }
                }
            }
        }

        $hariIni = $shift->waktu_buka->format('Y-m-d');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('kasir.shift_report_pdf', compact('totalCash', 'totalQris', 'totalSemua', 'pembayarans', 'hariIni', 'shift', 'rekapMenu', 'totalItemTerjual'));
        return $pdf->download('Laporan_Shift_' . $hariIni . '.pdf');
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

            // Simpan total asli sebelum split untuk menghitung rasio HPP
            $totalAsliSebelumSplit = $pesananAsli->total;
            $hppAsliSebelumSplit = $pesananAsli->total_hpp;

            // 1. Buat Pesanan Baru (Clone)
            $pesananBaru = $pesananAsli->replicate();
            $pesananBaru->total = 0;
            $pesananBaru->total_hpp = 0; // Reset HPP pesanan baru
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

            // =====================================================================
            // FIX #3: Distribusi HPP secara proporsional berdasarkan rasio harga jual
            // =====================================================================
            $hppBaru = 0;
            if ($totalAsliSebelumSplit > 0) {
                // Rasio HPP = (total harga jual yang dipindah / total harga jual sebelum split) * HPP asli
                $rasio = $totalBaru / $totalAsliSebelumSplit;
                $hppBaru = round($hppAsliSebelumSplit * $rasio, 2);
            }

            // 3. Update Total Pesanan Baru (termasuk HPP)
            $pesananBaru->update([
                'total' => $totalBaru,
                'total_hpp' => $hppBaru
            ]);
            Pembayaran::create([
                'id_pesanan' => $pesananBaru->id,
                'status' => 'unpaid',
                'total_bayar' => $totalBaru
            ]);

            // 4. Update Total Pesanan Lama (Asli) termasuk HPP
            $totalAsli = DetailPesanan::where('id_pesanan', $pesananAsli->id)->sum('subtotal');
            $hppAsli = $hppAsliSebelumSplit - $hppBaru; // Sisa HPP = HPP awal - HPP yang dipindah
            
            $pesananAsli->update([
                'total' => $totalAsli,
                'total_hpp' => $hppAsli,
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

    /**
     * Mengambil daftar notifikasi terbaru (misal untuk Panggil Pelayan)
     */
    public function getNotifications()
    {
        $notifications = \App\Models\Notification::where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->get();
            
        return response()->json($notifications);
    }

    /**
     * Tandai notifikasi sudah dibaca
     */
    public function readNotification($id)
    {
        $notif = \App\Models\Notification::find($id);
        if ($notif) {
            $notif->update(['is_read' => true]);
            return response()->json(['message' => 'Notifikasi ditandai dibaca']);
        }
        return response()->json(['error' => 'Notifikasi tidak ditemukan'], 404);
    }
}