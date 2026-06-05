<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Penjualan Angkringan POS</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
        }
        .kop-surat {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .kop-surat h1 {
            margin: 0;
            font-size: 24px;
            color: #d32f2f;
        }
        .kop-surat p {
            margin: 5px 0 0;
            font-size: 14px;
            color: #555;
        }
        .periode {
            text-align: center;
            font-size: 14px;
            margin-bottom: 20px;
            font-weight: bold;
        }
        h3 {
            margin-top: 30px;
            margin-bottom: 10px;
            font-size: 16px;
            color: #444;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #999;
        }
        th {
            background-color: #f4f4f4;
            padding: 8px;
            text-align: left;
        }
        td {
            padding: 8px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .badge {
            padding: 3px 6px;
            background-color: #e0e0e0;
            border-radius: 4px;
            font-size: 11px;
        }
    </style>
</head>
<body>

    <div class="kop-surat">
        <h1>ANGKRINGAN POS</h1>
        <p>Sistem Kasir & Manajemen Angkringan Modern</p>
    </div>

    <div class="periode">
        Laporan Penjualan<br>
        Periode: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
    </div>

    <!-- 1. Menu Terlaris -->
    <h3>Menu Terlaris (Top 10)</h3>
    <table>
        <thead>
            <tr>
                <th width="10%" class="text-center">Peringkat</th>
                <th>Nama Menu</th>
                <th width="20%" class="text-center">Total Terjual</th>
            </tr>
        </thead>
        <tbody>
            @forelse($bestSeller as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $item->nama_menu }}</td>
                <td class="text-center">{{ $item->total_terjual }} porsi</td>
            </tr>
            @empty
            <tr><td colspan="3" class="text-center">Belum ada data penjualan.</td></tr>
            @endforelse
        </tbody>
    </table>

    <!-- 2. Kinerja Kasir per Shift -->
    <h3>Kinerja Kasir per Shift</h3>
    <table>
        <thead>
            <tr>
                <th>Nama Kasir</th>
                <th>Shift</th>
                <th class="text-center">Total Transaksi</th>
                <th class="text-right">Total Pendapatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($kasirPerformance as $kasir)
            <tr>
                <td>{{ $kasir->name }}</td>
                <td>{{ ucfirst($kasir->shift) }}</td>
                <td class="text-center">{{ $kasir->total_transaksi }}</td>
                <td class="text-right">Rp {{ number_format($kasir->total_pendapatan, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr><td colspan="4" class="text-center">Belum ada data kasir.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary-box mb-4">
        <div style="font-size: 16px; font-weight: bold;">
            <p>Total Transaksi: {{ array_sum(array_column($paymentMethods->toArray(), 'total_transaksi')) }} Transaksi</p>
            <p style="color: #27ae60;">Total Pendapatan (Paid): Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</p>
            <p style="color: #e74c3c;">Total Pengeluaran: Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</p>
            <p style="color: #2980b9; margin-top: 10px; font-size: 18px; border-top: 1px dashed #ccc; padding-top: 10px;">Laba Bersih: Rp {{ number_format($labaBersih, 0, ',', '.') }}</p>
        </div>
    </div>

    <!-- 3. Penggunaan Stok Bahan Baku -->
    <h3>Penggunaan Stok Bahan Baku</h3>
    <table>
        <thead>
            <tr>
                <th>Nama Bahan Baku</th>
                <th width="30%" class="text-center">Total Terpakai</th>
            </tr>
        </thead>
        <tbody>
            @forelse($stockUsage as $stok)
            <tr>
                <td>{{ $stok->nama_bahan }}</td>
                <td class="text-center">{{ $stok->total_penggunaan }} {{ $stok->satuan }}</td>
            </tr>
            @empty
            <tr><td colspan="2" class="text-center">Belum ada data penggunaan bahan.</td></tr>
            @endforelse
        </tbody>
    </table>

    <!-- 4. Metode Pembayaran -->
    <h3>Ringkasan Metode Pembayaran</h3>
    <table>
        <thead>
            <tr>
                <th>Metode Pembayaran</th>
                <th class="text-center">Total Transaksi</th>
                <th class="text-right">Total Pendapatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($paymentMethods as $pm)
            <tr>
                <td>{{ strtoupper($pm->metode) }}</td>
                <td class="text-center">{{ $pm->total_transaksi }}</td>
                <td class="text-right">Rp {{ number_format($pm->total, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr><td colspan="3" class="text-center">Belum ada data metode pembayaran.</td></tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
