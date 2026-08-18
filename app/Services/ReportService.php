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
        $query = Pembayaran::with(['pesanan.konsumen', 'pesanan.kasir']);

        if (Pembayaran::where('status', 'paid')->exists()) {
            $query->where('status', 'paid')
                  ->whereBetween('tanggal', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        }

        $rows = $query->orderBy('id', 'desc')->get();

        $tahun = Carbon::parse($startDate)->format('Y');
        $filename = 'laporan_penjualan_angkringan_' . $tahun . '.xls';

        $html = '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
        $html .= '<head><meta charset="utf-8"><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Laporan Penjualan</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head>';
        $html .= '<body style="font-family: Arial, sans-serif; font-size: 10pt;">';
        $html .= '<table border="1" cellpadding="5" cellspacing="0" style="font-family: Arial, sans-serif; font-size: 10pt; border-collapse: collapse; border: 1px solid #000000;">';

        // Row 1: Title Header (Merged A1:H1)
        $html .= '<tr><td colspan="8" style="font-size: 11pt; font-weight: bold; text-align: center; height: 30px; vertical-align: middle; border: 1px solid #000000;">LAPORAN PENJUALAN ANGKRINGAN - TAHUN ' . $tahun . '</td></tr>';

        // Row 2: Table Column Headers (A2:H2)
        $html .= '<tr style="font-weight: bold; text-align: center; background-color: #ffffff;">';
        $html .= '<td style="border: 1px solid #000000;">No. Invoice</td>';
        $html .= '<td style="border: 1px solid #000000;">Tanggal</td>';
        $html .= '<td style="border: 1px solid #000000;">Nama Pelanggan</td>';
        $html .= '<td style="border: 1px solid #000000;">Kasir</td>';
        $html .= '<td style="border: 1px solid #000000;">Metode Bayar</td>';
        $html .= '<td style="border: 1px solid #000000;">Total Tagihan (Rp)</td>';
        $html .= '<td style="border: 1px solid #000000;">Uang Diterima (Rp)</td>';
        $html .= '<td style="border: 1px solid #000000;">Kembalian (Rp)</td>';
        $html .= '</tr>';

        $totalTagihanSum = 0;
        $totalDiterimaSum = 0;
        $totalKembalianSum = 0;

        if ($rows->count() > 0) {
            foreach ($rows as $r) {
                $invoiceNo = 'INV/' . ($r->tanggal ? Carbon::parse($r->tanggal)->format('Ymd') : now()->format('Ymd')) . '/' . str_pad($r->id_pesanan, 5, '0', STR_PAD_LEFT);
                $tanggalFormatted = $r->tanggal ? Carbon::parse($r->tanggal)->format('d/m/Y H.i') : now()->format('d/m/Y H.i');
                $namaPelanggan = $r->pesanan->nama_pemesan ?? ($r->pesanan->konsumen->name ?? 'Umum');
                $namaKasir = $r->pesanan->kasir->name ?? 'Kasir';
                
                $metodeBayar = strtoupper($r->metode ?? 'CASH');
                if ($metodeBayar === 'QRIS') {
                    $metodeBayar = 'TRANSFER_BANK_QRIS';
                }

                $totalTagihan = (float) $r->total_bayar;
                $uangDiterima = (float) ($r->uang_diterima ?? $r->total_bayar);
                $kembalian = (float) ($r->uang_kembali ?? 0);

                $totalTagihanSum += $totalTagihan;
                $totalDiterimaSum += $uangDiterima;
                $totalKembalianSum += $kembalian;

                $html .= '<tr>';
                $html .= '<td style="border: 1px solid #d0d0d0;">' . $invoiceNo . '</td>';
                $html .= '<td style="border: 1px solid #d0d0d0; text-align: center;">' . $tanggalFormatted . '</td>';
                $html .= '<td style="border: 1px solid #d0d0d0;">' . $namaPelanggan . '</td>';
                $html .= '<td style="border: 1px solid #d0d0d0;">' . $namaKasir . '</td>';
                $html .= '<td style="border: 1px solid #d0d0d0;">' . $metodeBayar . '</td>';
                $html .= '<td style="border: 1px solid #d0d0d0; text-align: right;">' . number_format($totalTagihan, 0, ',', '.') . '</td>';
                $html .= '<td style="border: 1px solid #d0d0d0; text-align: right;">' . number_format($uangDiterima, 0, ',', '.') . '</td>';
                $html .= '<td style="border: 1px solid #d0d0d0; text-align: right;">' . number_format($kembalian, 0, ',', '.') . '</td>';
                $html .= '</tr>';
            }
        } else {
            $html .= '<tr><td colspan="8" style="text-align: center; color: #888888; border: 1px solid #d0d0d0;">Belum ada data transaksi penjualan pada periode ini.</td></tr>';
        }

        // Summary Total Row
        $html .= '<tr style="font-weight: bold;">';
        $html .= '<td colspan="5" style="text-align: right; border: none;">TOTAL</td>';
        $html .= '<td style="text-align: right; border: 1px solid #000000;">' . number_format($totalTagihanSum, 0, ',', '.') . '</td>';
        $html .= '<td style="text-align: right; border: 1px solid #000000;">' . number_format($totalDiterimaSum, 0, ',', '.') . '</td>';
        $html .= '<td style="text-align: right; border: 1px solid #000000;">' . number_format($totalKembalianSum, 0, ',', '.') . '</td>';
        $html .= '</tr>';

        $html .= '</table></body></html>';

        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
