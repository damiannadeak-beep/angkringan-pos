<?php

namespace App\Services;

use App\Models\{Pembayaran, DetailPesanan, Pengeluaran, KasirShift};
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ReportService
{
    /**
     * Ambil data laporan lengkap berdasarkan rentang tanggal.
     */
    public function getReportsData(string $startDate, string $endDate): array
    {
        // 1. Grafik Penjualan Harian
        $salesQuery = Pembayaran::selectRaw('DATE(tanggal) AS day, SUM(total_bayar) AS total')
            ->whereBetween('tanggal', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->where('status', 'paid')
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $salesByDate = $salesQuery->keyBy('day');
        $chartLabels = [];
        $chartData = [];

        $currentDate = Carbon::parse($startDate);
        $lastDate = Carbon::parse($endDate);

        while ($currentDate <= $lastDate) {
            $dateString = $currentDate->toDateString();
            $chartLabels[] = $currentDate->format('d M Y');
            $chartData[] = (float) ($salesByDate[$dateString]->total ?? 0);
            $currentDate->addDay();
        }

        // 2. Menu Terlaris (Best Seller)
        $bestSeller = DetailPesanan::join('pesanan', 'detail_pesanan.id_pesanan', '=', 'pesanan.id')
            ->join('menu', 'detail_pesanan.id_menu', '=', 'menu.id')
            ->join('pembayaran', 'pesanan.id', '=', 'pembayaran.id_pesanan')
            ->whereBetween('pembayaran.tanggal', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->where('pembayaran.status', 'paid')
            ->selectRaw('menu.nama_menu, SUM(detail_pesanan.jumlah) as total_terjual')
            ->groupBy('menu.id', 'menu.nama_menu')
            ->orderByDesc('total_terjual')
            ->limit(10)
            ->get();

        // 3. Kinerja Kasir per Shift
        $kasirPerformance = Pembayaran::join('pesanan', 'pembayaran.id_pesanan', '=', 'pesanan.id')
            ->join('users', 'pesanan.id_kasir', '=', 'users.id')
            ->whereBetween('pembayaran.tanggal', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->where('pembayaran.status', 'paid')
            ->selectRaw('users.name, users.shift, SUM(pembayaran.total_bayar) as total_pendapatan, COUNT(pembayaran.id) as total_transaksi')
            ->groupBy('users.id', 'users.name', 'users.shift')
            ->orderByDesc('total_pendapatan')
            ->get();

        // 4. Penggunaan Stok Bahan Baku
        $stockUsage = DB::table('detail_pesanan')
            ->join('pesanan', 'detail_pesanan.id_pesanan', '=', 'pesanan.id')
            ->join('pembayaran', 'pesanan.id', '=', 'pembayaran.id_pesanan')
            ->join('bahan_menu', 'detail_pesanan.id_menu', '=', 'bahan_menu.menu_id')
            ->join('bahans', 'bahan_menu.bahan_id', '=', 'bahans.id')
            ->whereBetween('pembayaran.tanggal', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->where('pembayaran.status', 'paid')
            ->selectRaw('bahans.nama_bahan, bahans.satuan, SUM(detail_pesanan.jumlah * bahan_menu.jumlah_dibutuhkan) as total_penggunaan')
            ->groupBy('bahans.id', 'bahans.nama_bahan', 'bahans.satuan')
            ->orderByDesc('total_penggunaan')
            ->get();

        // 5. Metode Pembayaran (Cash vs QRIS)
        $paymentMethods = Pembayaran::selectRaw('metode, count(id) as total_transaksi, sum(total_bayar) as total')
            ->whereBetween('tanggal', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->where('status', 'paid')
            ->groupBy('metode')
            ->get();

        // 6. Ringkasan Finansial
        $totalPendapatan = Pembayaran::whereBetween('tanggal', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->where('status', 'paid')
            ->sum('total_bayar');

        $totalHpp = DB::table('pesanan')
            ->join('pembayaran', 'pesanan.id', '=', 'pembayaran.id_pesanan')
            ->whereBetween('pembayaran.tanggal', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->where('pembayaran.status', 'paid')
            ->sum('pesanan.total_hpp');

        $totalPengeluaran = Pengeluaran::whereBetween('tanggal', [$startDate, $endDate])
            ->sum('nominal');

        $labaKotor = $totalPendapatan - $totalHpp;
        $labaBersih = $labaKotor - $totalPengeluaran;

        $kasirShifts = KasirShift::with('user')
            ->whereBetween('waktu_buka', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderBy('waktu_buka', 'desc')
            ->get();

        return compact(
            'startDate',
            'endDate',
            'chartLabels',
            'chartData',
            'bestSeller',
            'kasirPerformance',
            'stockUsage',
            'paymentMethods',
            'totalPendapatan',
            'totalHpp',
            'labaKotor',
            'totalPengeluaran',
            'labaBersih',
            'kasirShifts'
        );
    }

    /**
     * Export PDF.
     */
    public function generatePdfReport(string $startDate, string $endDate)
    {
        $data = $this->getReportsData($startDate, $endDate);
        $pdf = Pdf::loadView('admin.reports_pdf', $data);
        return $pdf->download("Laporan_Angkringan_{$startDate}_sd_{$endDate}.pdf");
    }

    /**
     * Export CSV Revenue Report.
     */
    public function generateRevenueCsv(string $startDate, string $endDate)
    {
        $rows = Pembayaran::whereBetween('tanggal', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->where('status', 'paid')
            ->orderBy('tanggal')
            ->get(['tanggal', 'metode', 'total_bayar']);

        $filename = 'laporan_pendapatan_' . now()->format('Ymd_His') . '.csv';
        $handle = fopen('php://memory', 'r+');
        fputcsv($handle, ['Tanggal', 'Metode', 'Total Bayar']);
        foreach ($rows as $r) {
            fputcsv($handle, [$r->tanggal, $r->metode, $r->total_bayar]);
        }
        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return response($content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Export Full Excel/CSV Report.
     */
    public function generateFullExcelReport(string $startDate, string $endDate)
    {
        $reportsData = $this->getReportsData($startDate, $endDate);
        $bestSeller = $reportsData['bestSeller'];
        $kasirPerformance = $reportsData['kasirPerformance'];
        $stockUsage = $reportsData['stockUsage'];

        $filename = 'laporan_lengkap_' . now()->format('Ymd_His') . '.xls';

        $html = '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
        $html .= '<head><meta charset="utf-8"><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Laporan</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head>';
        $html .= '<body>';
        $html .= '<table border="1" cellpadding="5" cellspacing="0" style="font-family: Arial, sans-serif;">';

        $html .= '<tr><td colspan="4" style="background-color: #4CAF50; color: white; font-size: 16px; font-weight: bold; text-align: center;">LAPORAN LENGKAP ANGKRINGAN POS</td></tr>';
        $html .= '<tr><td colspan="4" style="text-align: center; font-weight: bold;">Periode: ' . $startDate . ' s/d ' . $endDate . '</td></tr>';
        $html .= '<tr><td colspan="4"></td></tr>';

        $html .= '<tr><td colspan="4" style="background-color: #f2f2f2; font-weight: bold;">--- MENU TERLARIS ---</td></tr>';
        $html .= '<tr style="font-weight: bold; background-color: #e0e0e0;"><td>No</td><td colspan="2">Nama Menu</td><td>Total Terjual (Porsi)</td></tr>';
        $no = 1;
        foreach ($bestSeller as $item) {
            $html .= '<tr><td>' . $no++ . '</td><td colspan="2">' . $item->nama_menu . '</td><td>' . $item->total_terjual . '</td></tr>';
        }
        $html .= '<tr><td colspan="4"></td></tr>';

        $html .= '<tr><td colspan="4" style="background-color: #f2f2f2; font-weight: bold;">--- KINERJA KASIR ---</td></tr>';
        $html .= '<tr style="font-weight: bold; background-color: #e0e0e0;"><td>Nama Kasir</td><td>Shift</td><td>Total Transaksi</td><td>Total Pendapatan (Rp)</td></tr>';
        foreach ($kasirPerformance as $kasir) {
            $html .= '<tr><td>' . $kasir->name . '</td><td>' . $kasir->shift . '</td><td>' . $kasir->total_transaksi . '</td><td>Rp ' . number_format($kasir->total_pendapatan, 0, ',', '.') . '</td></tr>';
        }
        $html .= '<tr><td colspan="4"></td></tr>';

        $html .= '<tr><td colspan="4" style="background-color: #f2f2f2; font-weight: bold;">--- PENGGUNAAN STOK BAHAN BAKU ---</td></tr>';
        $html .= '<tr style="font-weight: bold; background-color: #e0e0e0;"><td>No</td><td colspan="2">Nama Bahan</td><td>Total Terpakai</td></tr>';
        $no = 1;
        foreach ($stockUsage as $stok) {
            $html .= '<tr><td>' . $no++ . '</td><td colspan="2">' . $stok->nama_bahan . '</td><td>' . $stok->total_penggunaan . ' ' . $stok->satuan . '</td></tr>';
        }

        $html .= '</table></body></html>';

        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
