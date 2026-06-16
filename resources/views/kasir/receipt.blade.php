<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pesanan #{{ $order->id }}</title>
    <style>
        /* Gaya Khusus untuk Printer Thermal 58mm */
        @page {
            margin: 0;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            line-height: 1.4;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 10px;
            width: 58mm; /* Lebar standar kertas thermal kecil */
            box-sizing: border-box;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .fw-bold { font-weight: bold; }
        .mb-1 { margin-bottom: 5px; }
        .mb-2 { margin-bottom: 10px; }
        .mt-1 { margin-top: 5px; }
        .mt-2 { margin-top: 10px; }
        
        .header {
            margin-bottom: 10px;
            border-bottom: 1px dashed #000;
            padding-bottom: 5px;
        }
        .header h3 {
            margin: 0 0 5px 0;
            font-size: 16px;
        }
        .header p {
            margin: 0;
            font-size: 11px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        td {
            vertical-align: top;
            padding: 2px 0;
        }
        
        .divider {
            border-bottom: 1px dashed #000;
            margin: 5px 0;
        }
        
        .totals {
            margin-top: 10px;
            border-top: 1px dashed #000;
            padding-top: 5px;
        }
        
        .footer {
            margin-top: 15px;
            text-align: center;
            font-size: 11px;
        }
        
        /* Hilangkan tombol print saat mencetak */
        @media print {
            .no-print { display: none !important; }
        }
        
        .btn-print {
            display: block;
            width: 100%;
            padding: 10px;
            background: #27ae60;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-bottom: 20px;
            font-weight: bold;
        }
    </style>
</head>
<body onload="window.print()">
    
    <button class="no-print btn-print" onclick="window.print()">Cetak Struk Sekarang</button>

    <div class="header text-center">
        <h3>{{ \App\Models\Setting::getVal('store_name', 'ANGKRINGAN POS') }}</h3>
        <p>{{ \App\Models\Setting::getVal('store_address', 'Jl. Contoh Angkringan No. 123') }}<br>Telp: {{ \App\Models\Setting::getVal('store_phone', '08123456789') }}</p>
    </div>
    
    <div class="mb-2" style="font-size: 11px;">
        <table style="width: 100%;">
            <tr>
                <td>Tgl</td>
                <td>: {{ \Carbon\Carbon::parse($order->created_at)->format('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <td>Order</td>
                <td>: #{{ $order->id }}</td>
            </tr>
            <tr>
                <td>Meja</td>
                <td>: {{ $order->meja->nama_meja_atau_nomor ?? 'Takeaway' }}</td>
            </tr>
            <tr>
                <td>Kasir</td>
                <td>: {{ $order->kasir->name ?? 'Kasir' }}</td>
            </tr>
        </table>
    </div>
    
    <div class="divider"></div>
    
    <table>
        @foreach($order->detail_pesanan as $item)
        <tr>
            <td colspan="3" class="fw-bold">
                {{ $item->menu->nama_menu ?? 'Menu' }}
                @if($item->selected_variants)
                    @php 
                        $variants = json_decode($item->selected_variants, true); 
                    @endphp
                    @if(is_array($variants) && count($variants) > 0)
                        <br><span style="font-size: 10px; font-weight: normal;">- {{ implode(', ', array_column($variants, 'name')) }}</span>
                    @endif
                @endif
                @if($item->catatan)
                    <br><span style="font-size: 10px; font-style: italic; font-weight: normal;">* {{ $item->catatan }}</span>
                @endif
            </td>
        </tr>
        <tr>
            <td style="width: 25%;">{{ $item->jumlah }}x</td>
            <td style="width: 35%;">{{ number_format($item->subtotal / $item->jumlah, 0, ',', '.') }}</td>
            <td class="text-right" style="width: 40%;">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
        </tr>
        @endforeach
    </table>
    
    <div class="totals">
        <table>
            <tr>
                <td class="fw-bold">Total Tagihan</td>
                <td class="text-right fw-bold">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Metode</td>
                <td class="text-right">{{ strtoupper($order->pembayaran->metode ?? 'CASH') }}</td>
            </tr>
            <tr>
                <td>Status</td>
                <td class="text-right">LUNAS</td>
            </tr>
        </table>
    </div>
    
    <div class="divider"></div>
    
    <div class="footer">
        {!! nl2br(e(\App\Models\Setting::getVal('receipt_footer', "Terima Kasih Atas Kunjungan Anda!\nKritik & Saran: angkringan@pos.com"))) !!}
    </div>

</body>
</html>
