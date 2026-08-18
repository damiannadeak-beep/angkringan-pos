<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Keuangan & Bisnis - Angkringan POS</title>
    <style>
        @page {
            margin: 15mm 15mm 15mm 15mm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10pt;
            color: #2d1a11;
            line-height: 1.4;
            background: #ffffff;
        }
        .kop-header {
            width: 100%;
            border-bottom: 2px solid #3E2723;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .brand-title {
            font-size: 20pt;
            font-weight: bold;
            color: #3E2723;
            margin: 0;
            letter-spacing: 0.5px;
        }
        .brand-subtitle {
            font-size: 9pt;
            color: #6d4c41;
            margin-top: 2px;
        }
        .report-meta {
            text-align: right;
            font-size: 9pt;
            color: #5d4037;
        }
        .periode-badge {
            background-color: #f5f2ec;
            color: #3E2723;
            padding: 6px 12px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 10pt;
            display: inline-block;
            margin-bottom: 16px;
            border: 1px solid #e0d7cb;
        }
        .section-title {
            font-size: 11pt;
            font-weight: bold;
            color: #3E2723;
            background-color: #ebe6dd;
            padding: 6px 10px;
            margin-top: 18px;
            margin-bottom: 8px;
            border-left: 4px solid #3E2723;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 9.5pt;
        }
        th {
            background-color: #3E2723;
            color: #ffffff;
            font-weight: bold;
            padding: 7px 8px;
            text-align: left;
            border: 0.5pt solid #3E2723;
        }
        td {
            padding: 6px 8px;
            border: 0.5pt solid #d0c7bc;
            vertical-align: middle;
        }
        tr:nth-child(even) td {
            background-color: #faf8f5;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .fw-bold {
            font-weight: bold;
        }
        .kpi-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px;
            margin-bottom: 16px;
        }
        .kpi-card {
            background-color: #faf8f5;
            border: 1pt solid #e0d7cb;
            border-radius: 6px;
            padding: 8px 10px;
            text-align: center;
        }
        .kpi-label {
            font-size: 8pt;
            color: #6d4c41;
            text-transform: uppercase;
            font-weight: bold;
        }
        .kpi-value {
            font-size: 12pt;
            font-weight: bold;
            margin-top: 3px;
        }
        .val-success { color: #1b5e20; }
        .val-danger { color: #b71c1c; }
        .val-info { color: #0d47a1; }
        .val-cacao { color: #3E2723; }
        
        .footer-note {
            margin-top: 24px;
            border-top: 0.5pt solid #e0d7cb;
            padding-top: 8px;
            font-size: 8pt;
            color: #8d6e63;
            width: 100%;
        }
        .total-row td {
            font-weight: bold;
            border-top: 1pt solid #3E2723;
            border-bottom: 2.25pt double #3E2723;
            background-color: #f5f2ec !important;
        }
    </style>
</head>
<body>

    <!-- KOP HEADER -->
    <table class="kop-header">
        <tr>
            <td style="border: none; padding: 0;">
                <h1 class="brand-title">ANGKRINGAN POS</h1>
                <div class="brand-subtitle">Sistem Kasir & Operasional Angkringan Modern</div>
            </td>
            <td style="border: none; padding: 0; text-align: right;" class="report-meta">
                <strong>LAPORAN KEUANGAN & BISNIS</strong><br>
                Dicetak: {{ now()->translatedFormat('d F Y H:i') }}<br>
                Oleh Staf: {{ auth()->user()->name }}
            </td>
        </tr>
    </table>

    <div class="periode-badge">
        Periode Laporan: {{ \Carbon\Carbon::parse($startDate)->translatedFormat('d F Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') }}
    </div>

    <!-- 1. KPI SUMMARY GRID -->
    <table class="kpi-grid">
        <tr>
            <td class="kpi-card" width="20%">
                <div class="kpi-label">Pendapatan Omzet</div>
                <div class="kpi-value val-success">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</div>
            </td>
            <td class="kpi-card" width="20%">
                <div class="kpi-label">Modal Bahan (HPP)</div>
                <div class="kpi-value val-danger">Rp {{ number_format($totalHpp, 0, ',', '.') }}</div>
            </td>
            <td class="kpi-card" width="20%">
                <div class="kpi-label">Laba Kotor</div>
                <div class="kpi-value val-info">Rp {{ number_format($labaKotor, 0, ',', '.') }}</div>
            </td>
            <td class="kpi-card" width="20%">
                <div class="kpi-label">Pengeluaran</div>
                <div class="kpi-value val-danger">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</div>
            </td>
            <td class="kpi-card" width="20%" style="background-color: #3E2723; border-color: #3E2723;">
                <div class="kpi-label" style="color: #d7ccc8;">Laba Bersih</div>
                <div class="kpi-value" style="color: #ffd54f;">Rp {{ number_format($labaBersih, 0, ',', '.') }}</div>
            </td>
        </tr>
    </table>

    <!-- 2. MENU TERLARIS -->
    <div class="section-title">1. REKAP MENU TERLARIS (TOP 10)</div>
    <table>
        <thead>
            <tr>
                <th width="10%" class="text-center">No</th>
                <th>Nama Menu / Produk</th>
                <th width="25%" class="text-right">Total Terjual</th>
            </tr>
        </thead>
        <tbody>
            @forelse($bestSeller as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $item->nama_menu }}</td>
                <td class="text-right fw-bold">{{ number_format($item->total_terjual, 0, ',', '.') }} porsi</td>
            </tr>
            @empty
            <tr><td colspan="3" class="text-center" style="color: #888;">Belum ada data penjualan pada periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>

    <!-- 3. KINERJA KASIR -->
    <div class="section-title">2. KINERJA & PENJUALAN STAF KASIR</div>
    <table>
        <thead>
            <tr>
                <th width="8%" class="text-center">No</th>
                <th>Nama Staf Kasir</th>
                <th width="18%" class="text-center">Shift Kerja</th>
                <th width="20%" class="text-center">Jumlah Transaksi</th>
                <th width="25%" class="text-right">Total Pendapatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($kasirPerformance as $index => $kasir)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="fw-bold">{{ $kasir->name }}</td>
                <td class="text-center">{{ ucfirst($kasir->shift) }}</td>
                <td class="text-center">{{ $kasir->total_transaksi }} Transaksi</td>
                <td class="text-right fw-bold val-success">Rp {{ number_format($kasir->total_pendapatan, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center" style="color: #888;">Belum ada data aktivitas kasir.</td></tr>
            @endforelse
        </tbody>
    </table>

    <!-- 4. PENGGUNAAN STOK BAHAN BAKU -->
    <div class="section-title">3. REKAP PENGGUNAAN STOK BAHAN BAKU</div>
    <table>
        <thead>
            <tr>
                <th width="10%" class="text-center">No</th>
                <th>Nama Bahan Baku</th>
                <th width="30%" class="text-right">Total Terpakai</th>
            </tr>
        </thead>
        <tbody>
            @forelse($stockUsage as $index => $stok)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $stok->nama_bahan }}</td>
                <td class="text-right fw-bold">{{ $stok->total_penggunaan }} {{ $stok->satuan }}</td>
            </tr>
            @empty
            <tr><td colspan="3" class="text-center" style="color: #888;">Belum ada penggunaan bahan baku.</td></tr>
            @endforelse
        </tbody>
    </table>

    <!-- 5. METODE PEMBAYARAN -->
    <div class="section-title">4. RINGKASAN METODE PEMBAYARAN</div>
    <table>
        <thead>
            <tr>
                <th>Metode Pembayaran</th>
                <th width="25%" class="text-center">Total Transaksi</th>
                <th width="30%" class="text-right">Nominal Pendapatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($paymentMethods as $pm)
            <tr>
                <td class="fw-bold">{{ strtoupper($pm->metode === 'qris' ? 'TRANSFER BANK / QRIS' : $pm->metode) }}</td>
                <td class="text-center">{{ $pm->total_transaksi }} Transaksi</td>
                <td class="text-right fw-bold val-success">Rp {{ number_format($pm->total, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr><td colspan="3" class="text-center" style="color: #888;">Belum ada transaksi pembayaran.</td></tr>
            @endforelse
        </tbody>
    </table>

    <!-- FOOTER -->
    <table class="footer-note">
        <tr>
            <td style="border: none; padding: 0;">
                Dokumen resmi komputerisasi Angkringan POS - Berlaku tanpa tanda tangan basah.
            </td>
            <td style="border: none; padding: 0; text-align: right;">
                Halaman 1 dari 1
            </td>
        </tr>
    </table>

</body>
</html>

