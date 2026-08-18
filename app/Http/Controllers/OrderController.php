<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\{Pesanan, DetailPesanan, Pembayaran, Menu, Meja, Promo, Setting};
use App\Http\Requests\Konsumen\{TambahPesananRequest, CallBellRequest};

class OrderController extends Controller
{
    /**
     * Menampilkan Menu berdasarkan scan QR Meja
     */
    public function showMenu(Request $request, $id_meja)
    {
        $meja = Meja::findOrFail($id_meja);
        
        // Cek apakah ada pesanan 'unpaid' aktif di meja ini (Konsep Open Bill)
        $pesananAktif = Pesanan::where('id_meja', $id_meja)
            ->where('status', '!=', 'completed')
            ->whereHas('pembayaran', function($q) {
                $q->where('status', 'unpaid');
            })->first();

        // Pengecekan Soft Warning (Jika meja tidak tersedia ATAU ada pesanan aktif dan user belum konfirmasi)
        if ((!$meja->is_available || $pesananAktif) && $request->query('confirm') != '1') {
            return view('konsumen.konfirmasi_meja', compact('meja'));
        }

        $menus = Menu::where('is_available', true)->where('stok', '>', 0)->get();
        [$promos, $promoMenuIds] = $this->getActivePromosWithMenuIds();

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
        [$promos, $promoMenuIds] = $this->getActivePromosWithMenuIds();
            
        return view('konsumen.menu_takeaway', compact('menus', 'promos', 'promoMenuIds'));
    }

    /**
     * Menampilkan menu untuk pesanan Dine-In dari jarak jauh (tanpa meja).
     */
    public function menuNanti()
    {
        $menus = Menu::where('is_available', true)->where('stok', '>', 0)->get();
        [$promos, $promoMenuIds] = $this->getActivePromosWithMenuIds();
            
        return view('konsumen.menu_nanti', compact('menus', 'promos', 'promoMenuIds'));
    }

    /**
     * Menambahkan item ke pesanan aktif atau membuat pesanan baru (Open Bill)
     */
    public function tambahPesanan(TambahPesananRequest $request, OrderService $orderService)
    {
        $validated = $request->validated();

        try {
            DB::beginTransaction();

            $tipe_pesanan = $validated['tipe_pesanan'] ?? 'dine_in';
            $id_meja = $validated['id_meja'] ?? null;

            if ($tipe_pesanan === 'takeaway') {
                $id_meja = null;
            }

            // Buat Pesanan & Pembayaran Baru
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

            if ($id_meja && $tipe_pesanan === 'dine_in') {
                Meja::where('id', $id_meja)->update(['is_available' => false]);
            }

            // Proses item pesanan via OrderService
            $result = $orderService->processOrderItems($pesanan, $validated['items']);

            $pesanan->total = $result['total'];
            $pesanan->total_hpp = $result['total_hpp'];

            // Handle Promo via OrderService
            $discountAmount = 0;
            if (!empty($validated['promo_id'])) {
                $pesanan->promo_id = $validated['promo_id'];
                $discountAmount = $orderService->calculateDiscount(
                    $result['total'],
                    $validated['promo_id'],
                    $validated['items']
                );
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
    public function callBell(CallBellRequest $request)
    {
        $validated = $request->validated();

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

    /**
     * Helper: ambil promo aktif + kumpulkan menu IDs dari promo paket.
     *
     * @return array [$promos, $promoMenuIds]
     */
    private function getActivePromosWithMenuIds(): array
    {
        $promos = Promo::with('menus')->active()->get();

        $promoMenuIds = $promos
            ->where('type', 'package')
            ->flatMap(fn($p) => $p->menus->pluck('id'))
            ->all();

        return [$promos, $promoMenuIds];
    }
}