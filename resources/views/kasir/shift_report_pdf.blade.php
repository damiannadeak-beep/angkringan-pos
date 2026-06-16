<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Shift Kasir - {{ $hariIni }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table, th, td { border: 1px solid #000; }
        th, td { padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .summary { float: right; width: 300px; }
        .summary table { border: none; }
        .summary td { border: none; padding: 4px 8px; }
        .summary .total { font-weight: bold; border-top: 1px solid #000; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Laporan Penjualan Shift</h2>
        <p>Kasir: {{ auth()->user()->name }} | Tanggal: {{ $hariIni }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Menu / Item Terjual</th>
                <th>Qty</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @forelse($rekapMenu as $nama => $data)
            <tr>
                <td>{{ $no++ }}</td>
                <td>{{ $nama }}</td>
                <td>{{ $data['jumlah'] }}</td>
                <td>Rp {{ number_format($data['subtotal'], 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="text-align: center;">Belum ada item terjual</td>
            </tr>
            @endforelse
            @if($totalItemTerjual > 0)
            <tr style="font-weight: bold; background-color: #f9f9f9;">
                <td colspan="2" style="text-align: right;">Total Keseluruhan Item</td>
                <td>{{ $totalItemTerjual }}</td>
                <td></td>
            </tr>
            @endif
        </tbody>
    </table>

    <div class="summary">
        <table>
            <tr>
                <td>Total Tunai:</td>
                <td style="text-align: right;">Rp {{ number_format($totalCash, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Total QRIS:</td>
                <td style="text-align: right;">Rp {{ number_format($totalQris, 0, ',', '.') }}</td>
            </tr>
            <tr class="total">
                <td>Total Keseluruhan:</td>
                <td style="text-align: right;">Rp {{ number_format($totalSemua, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>
</body>
</html>
