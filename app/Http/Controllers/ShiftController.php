<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KasirShift;
use App\Models\Pesanan;
use App\Models\Pembayaran;
use App\Models\Pengeluaran;

class ShiftController extends Controller
{
    public function bukaShift()
    {
        $shift = KasirShift::where('user_id', auth()->id())->where('status', 'open')->first();
        if ($shift) {
            return redirect()->route('kasir.pos');
        }
        return view('kasir.shift.buka');
    }

    public function storeBukaShift(Request $request)
    {
        $request->validate([
            'modal_awal' => 'required|numeric|min:0'
        ]);

        KasirShift::create([
            'user_id' => auth()->id(),
            'modal_awal' => $request->modal_awal,
            'status' => 'open'
        ]);

        return redirect()->route('kasir.pos')->with('success', 'Shift berhasil dibuka. Selamat bekerja!');
    }

    public function tutupShift()
    {
        $shift = KasirShift::where('user_id', auth()->id())->where('status', 'open')->first();
        if (!$shift) {
            return redirect()->route('kasir.pos')->with('error', 'Tidak ada shift yang aktif.');
        }

        return view('kasir.shift.tutup', compact('shift'));
    }

    public function storeTutupShift(Request $request)
    {
        $request->validate([
            'uang_fisik_aktual' => 'required|numeric|min:0'
        ]);

        $shift = KasirShift::where('user_id', auth()->id())->where('status', 'open')->first();
        if (!$shift) {
            return redirect()->route('kasir.pos');
        }

        // Kalkulasi pemasukan tunai selama shift (hanya pesanan kasir ini)
        // FIX #9: Sum dari pembayaran.total_bayar (setelah diskon), bukan pesanan.total (sebelum diskon)
        $totalTunai = Pembayaran::where('status', 'paid')
            ->where('metode', 'cash')
            ->where('updated_at', '>=', $shift->waktu_buka)
            ->whereHas('pesanan', function($q) {
                $q->where('id_kasir', auth()->id());
            })->sum('total_bayar');

        // Kalkulasi pengeluaran kasir selama shift
        $totalPengeluaran = Pengeluaran::where('user_id', auth()->id())
            ->where('created_at', '>=', $shift->waktu_buka)
            ->sum('nominal');

        $harapanFisik = $shift->modal_awal + $totalTunai - $totalPengeluaran;
        $selisih = $request->uang_fisik_aktual - $harapanFisik;

        $shift->update([
            'waktu_tutup' => now(),
            'uang_fisik_aktual' => $request->uang_fisik_aktual,
            'total_pemasukan_tunai' => $totalTunai,
            'total_pengeluaran' => $totalPengeluaran,
            'selisih' => $selisih,
            'status' => 'closed'
        ]);

        // Notify Admin
        $admins = \App\Models\User::role('pemilik')->get();
        $kasirName = auth()->user()->name;
        \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\WebPushNotification(
            'Laporan Shift Kasir',
            "Kasir {$kasirName} telah menutup shift. Pemasukan: Rp " . number_format($totalTunai, 0, ',', '.'),
            '/admin/kasir'
        ));

        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'Shift berhasil ditutup. Terima kasih atas kerja keras Anda!');
    }
}
