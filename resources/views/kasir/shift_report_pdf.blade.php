<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Shift Kasir - {{ $hariIni }}</title>
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
            padding-bottom: 10px;
            margin-bottom: 16px;
        }
        .brand-title {
            font-size: 18pt;
            font-weight: bold;
            color: #3E2723;
            margin: 0;
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
        .section-title {
            font-size: 11pt;
            font-weight: bold;
            color: #3E2723;
            background-color: #ebe6dd;
            padding: 6px 10px;
            margin-top: 16px;
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
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        
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
    </style>
</head>
<body>

    <!-- KOP HEADER -->
    <table class="kop-header">
        <tr>
            <td style="border: none; padding: 0;">
                <h1 class="brand-title">ANGKRINGAN POS</h1>
                <div class="brand-subtitle">Laporan Rekonsiliasi Tutup Shift Kasir</div>
            </td>
            <td style="border: none; padding: 0; text-align: right;" class="report-meta">
                <strong>STAF KASIR: {{ strtoupper(auth()->user()->name) }}</strong><br>
                Tanggal Shift: {{ \Carbon\Carbon::parse($hariIni)->translatedFormat('d F Y') }}<br>
                Dicetak: {{ now()->translatedFormat('H:i') }} WIB
            </td>
        </tr>
    </table>

    <!-- KPI SUMMARY GRID -->
    <table class="kpi-grid">
        <tr>
            <td class="kpi-card" width="33%">
                <div class="kpi-label">Kas Tunai (Laci Kas)</div>
                <div class="kpi-value val-success">Rp {{ number_format($totalCash, 0, ',', '.') }}</div>
            </td>
            <td class="kpi-card" width="33%">
                <div class="kpi-label">Non-Tunai (QRIS)</div>
                <div class="kpi-value val-info">Rp {{ number_format($totalQris, 0, ',', '.') }}</div>
            </td>
            <td class="kpi-card" width="34%" style="background-color: #3E2723; border-color: #3E2723;">
                <div class="kpi-label" style="color: #d7ccc8;">Total Omzet Shift</div>
                <div class="kpi-value" style="color: #ffd54f;">Rp {{ number_format($totalSemua, 0, ',', '.') }}</div>
            </td>
        </tr>
    </table>

    <!-- ITEM PENJUALAN -->
    <div class="section-title">REKAP ITEM MENU TERJUAL SHIFT INI</div>
    <table>
        <thead>
            <tr>
                <th width="8%" class="text-center">No</th>
                <th>Nama Menu / Item</th>
                <th width="20%" class="text-center">Jumlah Terjual</th>
                <th width="25%" class="text-right">Subtotal Penjualan</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @forelse($rekapMenu as $nama => $data)
            <tr>
                <td class="text-center">{{ $no++ }}</td>
                <td class="fw-bold">{{ $nama }}</td>
                <td class="text-center">{{ $data['jumlah'] }} porsi</td>
                <td class="text-right fw-bold">Rp {{ number_format($data['subtotal'], 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center" style="color: #888;">Belum ada item terjual pada shift ini.</td>
            </tr>
            @endforelse
            @if($totalItemTerjual > 0)
            <tr style="font-weight: bold; background-color: #f5f2ec;">
                <td colspan="2" class="text-right" style="border-top: 1pt solid #3E2723; border-bottom: 2.25pt double #3E2723;">TOTAL ITEM TERJUAL</td>
                <td class="text-center" style="border-top: 1pt solid #3E2723; border-bottom: 2.25pt double #3E2723;">{{ $totalItemTerjual }} porsi</td>
                <td class="text-right val-success" style="border-top: 1pt solid #3E2723; border-bottom: 2.25pt double #3E2723;">Rp {{ number_format($totalSemua, 0, ',', '.') }}</td>
            </tr>
            @endif
        </tbody>
    </table>

    <!-- FOOTER NOTE -->
    <table class="footer-note">
        <tr>
            <td style="border: none; padding: 0;">
                Laporan Tutup Shift Kasir sah secara sistem komputerisasi Angkringan POS.
            </td>
            <td style="border: none; padding: 0; text-align: right;">
                Dicetak pada {{ date('d/m/Y H:i:s') }}
            </td>
        </tr>
    </table>

</body>
</html>

