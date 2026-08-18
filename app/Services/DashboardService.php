<?php

namespace App\Services;

use App\Models\{Pembayaran, Menu, Pengeluaran, Setting};
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardService
{
    /**
     * Ambil data lengkap dashboard analytics admin (penjualan, HPP, laba, chart).
     */
    public function getDashboardMetrics(): array
    {
        $hariIni = Carbon::today();

        // 1. Penjualan Hari Ini
        $totalPenjualanHariIni = Pembayaran::whereDate('tanggal', $hariIni)
            ->where('status', 'paid')
            ->sum('total_bayar');

        // 2. Pendapatan per Metode (Cash vs QRIS)
        $pendapatanPerMetode = Pembayaran::selectRaw('metode, sum(total_bayar) as total')
            ->whereDate('tanggal', $hariIni)
            ->where('status', 'paid')
            ->groupBy('metode')
            ->pluck('total', 'metode');

        $totalCash = $pendapatanPerMetode->get('cash', 0);
        $totalQris = $pendapatanPerMetode->get('qris', 0);

        // 3. Stok Menipis
        $stokMenipis = Menu::where('stok', '<', 10)
            ->where('is_available', true)
            ->orderBy('stok', 'asc')
            ->get();

        // 4. Metrik Bulanan
        $startBulan = Carbon::now()->startOfMonth();
        $endBulan = Carbon::now()->endOfMonth();

        $totalPenjualanBulan = Pembayaran::whereBetween('tanggal', [$startBulan, $endBulan])
            ->where('status', 'paid')
            ->sum('total_bayar');

        $totalHppBulan = DB::table('pesanan')
            ->join('pembayaran', 'pesanan.id', '=', 'pembayaran.id_pesanan')
            ->whereBetween('pembayaran.tanggal', [$startBulan, $endBulan])
            ->where('pembayaran.status', 'paid')
            ->sum('pesanan.total_hpp');

        $totalPengeluaranBulan = Pengeluaran::whereBetween('tanggal', [$startBulan, $endBulan])
            ->sum('nominal');

        $labaKotorBulan = $totalPenjualanBulan - $totalHppBulan;
        $labaBersihBulan = $labaKotorBulan - $totalPengeluaranBulan;

        // 5. Data Chart Harian
        $dailySalesQuery = Pembayaran::selectRaw('DATE(tanggal) AS day, SUM(total_bayar) AS total')
            ->whereMonth('tanggal', $hariIni->month)
            ->whereYear('tanggal', $hariIni->year)
            ->where('status', 'paid')
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $dailyHppQuery = DB::table('pesanan')
            ->join('pembayaran', 'pesanan.id', '=', 'pembayaran.id_pesanan')
            ->whereMonth('pembayaran.tanggal', $hariIni->month)
            ->whereYear('pembayaran.tanggal', $hariIni->year)
            ->where('pembayaran.status', 'paid')
            ->selectRaw('DATE(pembayaran.tanggal) AS day, SUM(pesanan.total_hpp) AS total')
            ->groupBy('day')
            ->get()->keyBy('day');

        $dailyPengeluaranQuery = Pengeluaran::selectRaw('DATE(tanggal) AS day, SUM(nominal) AS total')
            ->whereMonth('tanggal', $hariIni->month)
            ->whereYear('tanggal', $hariIni->year)
            ->groupBy('day')
            ->get()->keyBy('day');

        $dailySalesByDate = $dailySalesQuery->keyBy('day');
        $daysInMonth = $hariIni->daysInMonth;
        $chartDailyLabels = [];
        $chartDailyData = [];
        $chartDailyLaba = [];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dateString = $hariIni->copy()->day($day)->toDateString();
            $chartDailyLabels[] = $hariIni->copy()->day($day)->format('d');

            $salesObj = $dailySalesByDate->get($dateString);
            $salesVal = $salesObj ? (float) $salesObj->total : 0;
            $chartDailyData[] = $salesVal;

            $hppObj = $dailyHppQuery->get($dateString);
            $hppVal = $hppObj ? (float) $hppObj->total : 0;

            $expObj = $dailyPengeluaranQuery->get($dateString);
            $expVal = $expObj ? (float) $expObj->total : 0;

            $labaVal = $salesVal - $hppVal - $expVal;
            $chartDailyLaba[] = $labaVal;
        }

        return compact(
            'totalPenjualanHariIni',
            'totalCash',
            'totalQris',
            'stokMenipis',
            'totalPenjualanBulan',
            'totalHppBulan',
            'totalPengeluaranBulan',
            'labaKotorBulan',
            'labaBersihBulan',
            'chartDailyLabels',
            'chartDailyData',
            'chartDailyLaba'
        );
    }
}
