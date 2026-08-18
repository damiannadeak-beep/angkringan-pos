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
        $totalPendapatan = $reportsData['totalPendapatan'];
        $totalHpp = $reportsData['totalHpp'];
        $labaKotor = $reportsData['labaKotor'];
        $totalPengeluaran = $reportsData['totalPengeluaran'];
        $labaBersih = $reportsData['labaBersih'];

        $filename = 'Laporan_Keuangan_Lengkap_' . now()->format('Ymd_His') . '.xls';

        $html = '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
        $html .= '<head><meta charset="utf-8"><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Laporan Keuangan</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head>';
        $html .= '<body style="font-family: Arial, sans-serif;">';
        $html .= '<table border="1" cellpadding="6" cellspacing="0" style="font-family: Arial, sans-serif; font-size: 11pt; border-collapse: collapse; border: 1px solid #d7ccc8;">';

        // Title Header Bar
        $html .= '<tr><td colspan="5" style="background-color: #3E2723; color: #FFFFFF; font-size: 16pt; font-weight: bold; text-align: center; height: 45px; vertical-align: middle;">LAPORAN KEUANGAN & BISNIS ANGKRINGAN POS</td></tr>';
        $html .= '<tr><td colspan="5" style="background-color: #5D4037; color: #F0E9DD; font-size: 11pt; font-weight: bold; text-align: center; height: 28px; vertical-align: middle;">Periode Laporan: ' . Carbon::parse($startDate)->translatedFormat('d F Y') . ' s/d ' . Carbon::parse($endDate)->translatedFormat('d F Y') . '</td></tr>';
        $html .= '<tr><td colspan="5" style="height: 15px;"></td></tr>';

        // 1. RINGKASAN FINANSIAL
        $html .= '<tr><td colspan="5" style="background-color: #F0E9DD; color: #3E2723; font-weight: bold; font-size: 12pt; height: 32px; vertical-align: middle; border-bottom: 2px solid #3E2723;">📊 I. RINGKASAN KEUANGAN & LABA RUGI</td></tr>';
        $html .= '<tr style="font-weight: bold; background-color: #E6DDCF; text-align: center;"><td>No</td><td colspan="2">Keterangan Finansial</td><td colspan="2">Jumlah Nominal (Rp)</td></tr>';
        $html .= '<tr><td style="text-align: center;">1</td><td colspan="2">Total Pendapatan Penjualan</td><td colspan="2" style="text-align: right; font-weight: bold; color: #2e7d32;">Rp ' . number_format($totalPendapatan, 0, ',', '.') . '</td></tr>';
        $html .= '<tr><td style="text-align: center;">2</td><td colspan="2">Total HPP (Modal Bahan Utama)</td><td colspan="2" style="text-align: right; color: #c62828;">- Rp ' . number_format($totalHpp, 0, ',', '.') . '</td></tr>';
        $html .= '<tr style="background-color: #faf6f0; font-weight: bold;"><td style="text-align: center;">3</td><td colspan="2">Laba Kotor (Gross Profit)</td><td colspan="2" style="text-align: right; color: #1565c0;">Rp ' . number_format($labaKotor, 0, ',', '.') . '</td></tr>';
        $html .= '<tr><td style="text-align: center;">4</td><td colspan="2">Total Pengeluaran Operasional</td><td colspan="2" style="text-align: right; color: #c62828;">- Rp ' . number_format($totalPengeluaran, 0, ',', '.') . '</td></tr>';
        $html .= '<tr style="background-color: #3E2723; color: #FFFFFF; font-weight: bold; font-size: 11pt;"><td style="text-align: center; color: #FFFFFF;">5</td><td colspan="2" style="color: #FFFFFF;">LABA BERSIH MURNI (NET PROFIT)</td><td colspan="2" style="text-align: right; color: #FFD54F; font-size: 12pt;">Rp ' . number_format($labaBersih, 0, ',', '.') . '</td></tr>';
        $html .= '<tr><td colspan="5" style="height: 20px;"></td></tr>';

        // 2. MENU TERLARIS
        $html .= '<tr><td colspan="5" style="background-color: #F0E9DD; color: #3E2723; font-weight: bold; font-size: 12pt; height: 32px; vertical-align: middle; border-bottom: 2px solid #3E2723;">🏆 II. REKAP MENU TERLARIS (TOP 10)</td></tr>';
        $html .= '<tr style="font-weight: bold; background-color: #E6DDCF; text-align: center;"><td>Peringkat</td><td colspan="3">Nama Menu / Produk</td><td>Total Terjual (Porsi)</td></tr>';
        $no = 1;
        foreach ($bestSeller as $item) {
            $bgColor = ($no % 2 == 0) ? '#FAF6F0' : '#FFFFFF';
            $html .= '<tr style="background-color: ' . $bgColor . ';"><td style="text-align: center;">' . $no++ . '</td><td colspan="3">' . $item->nama_menu . '</td><td style="text-align: center; font-weight: bold;">' . number_format($item->total_terjual, 0, ',', '.') . ' Porsi</td></tr>';
        }
        $html .= '<tr><td colspan="5" style="height: 20px;"></td></tr>';

        // 3. KINERJA KASIR
        $html .= '<tr><td colspan="5" style="background-color: #F0E9DD; color: #3E2723; font-weight: bold; font-size: 12pt; height: 32px; vertical-align: middle; border-bottom: 2px solid #3E2723;">👤 III. KINERJA & PENJUALAN STAF KASIR</td></tr>';
        $html .= '<tr style="font-weight: bold; background-color: #E6DDCF; text-align: center;"><td>No</td><td>Nama Kasir</td><td>Shift Kerja</td><td>Total Transaksi</td><td>Total Pendapatan (Rp)</td></tr>';
        $no = 1;
        foreach ($kasirPerformance as $kasir) {
            $bgColor = ($no % 2 == 0) ? '#FAF6F0' : '#FFFFFF';
            $html .= '<tr style="background-color: ' . $bgColor . ';"><td style="text-align: center;">' . $no++ . '</td><td>' . $kasir->name . '</td><td style="text-align: center;">' . ucfirst($kasir->shift) . '</td><td style="text-align: center;">' . $kasir->total_transaksi . ' Transaksi</td><td style="text-align: right; font-weight: bold; color: #2e7d32;">Rp ' . number_format($kasir->total_pendapatan, 0, ',', '.') . '</td></tr>';
        }
        $html .= '<tr><td colspan="5" style="height: 20px;"></td></tr>';

        // 4. PENGGUNAAN STOK BAHAN BAKU
        $html .= '<tr><td colspan="5" style="background-color: #F0E9DD; color: #3E2723; font-weight: bold; font-size: 12pt; height: 32px; vertical-align: middle; border-bottom: 2px solid #3E2723;">📦 IV. REKAP PENGGUNAAN STOK BAHAN BAKU</td></tr>';
        $html .= '<tr style="font-weight: bold; background-color: #E6DDCF; text-align: center;"><td>No</td><td colspan="3">Nama Bahan Baku</td><td>Total Terpakai</td></tr>';
        $no = 1;
        foreach ($stockUsage as $stok) {
            $bgColor = ($no % 2 == 0) ? '#FAF6F0' : '#FFFFFF';
            $html .= '<tr style="background-color: ' . $bgColor . ';"><td style="text-align: center;">' . $no++ . '</td><td colspan="3">' . $stok->nama_bahan . '</td><td style="text-align: center; font-weight: bold;">' . $stok->total_penggunaan . ' ' . $stok->satuan . '</td></tr>';
        }

        $html .= '</table></body></html>';

        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
