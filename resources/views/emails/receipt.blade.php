<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>E-Receipt Pesanan</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f7f9fc; margin: 0; padding: 20px; color: #333; }
        .receipt-container { max-width: 400px; margin: 0 auto; background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px dashed #eee; padding-bottom: 20px; }
        .header h1 { margin: 0; color: #1a1a1a; font-size: 24px; }
        .header p { margin: 5px 0 0; color: #666; font-size: 14px; }
        .details { margin-bottom: 20px; font-size: 14px; }
        .details table { width: 100%; }
        .details td { padding: 4px 0; }
        .details .label { color: #666; }
        .details .value { text-align: right; font-weight: bold; }
        .items { border-top: 2px solid #eee; border-bottom: 2px solid #eee; padding: 15px 0; margin-bottom: 20px; }
        .items table { width: 100%; border-collapse: collapse; }
        .items th { text-align: left; padding-bottom: 10px; color: #666; font-size: 13px; text-transform: uppercase; border-bottom: 1px solid #eee; }
        .items td { padding: 8px 0; font-size: 14px; }
        .items .qty { width: 40px; text-align: center; }
        .items .price { text-align: right; }
        .totals { margin-bottom: 20px; }
        .totals table { width: 100%; font-size: 14px; }
        .totals td { padding: 6px 0; }
        .totals .total-row td { border-top: 2px dashed #eee; padding-top: 15px; font-size: 18px; font-weight: bold; }
        .footer { text-align: center; font-size: 13px; color: #888; }
        .badge { background-color: #e6f4ea; color: #1e8e3e; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="header">
            <h1>Angkringan POS</h1>
            <p>{{ \App\Models\Setting::getVal('store_address', 'Jl. Contoh Alamat No. 123') }}</p>
            <p>{{ \App\Models\Setting::getVal('store_phone', '081234567890') }}</p>
        </div>

        <div class="details">
            <table>
                <tr>
                    <td class="label">No. Pesanan:</td>
                    <td class="value">#{{ str_pad($pesanan->id, 4, '0', STR_PAD_LEFT) }}</td>
                </tr>
                <tr>
                    <td class="label">Tanggal:</td>
                    <td class="value">{{ \Carbon\Carbon::parse($pesanan->created_at)->translatedFormat('d M Y, H:i') }}</td>
                </tr>
                <tr>
                    <td class="label">Pelanggan:</td>
                    <td class="value">{{ $pesanan->konsumen->name ?? 'Walk-in / Umum' }}</td>
                </tr>
                <tr>
                    <td class="label">Kasir:</td>
                    <td class="value">{{ $pesanan->kasir->name ?? 'Sistem' }}</td>
                </tr>
                <tr>
                    <td class="label">Status:</td>
                    <td class="value"><span class="badge">LUNAS</span></td>
                </tr>
                <tr>
                    <td class="label">Metode Bayar:</td>
                    <td class="value">{{ strtoupper($pesanan->pembayaran->metode ?? 'TUNAI') }}</td>
                </tr>
            </table>
        </div>

        <div class="items">
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th class="qty">Qty</th>
                        <th class="price">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pesanan->detail_pesanan as $detail)
                    <tr>
                        <td>{{ $detail->menu->nama_menu }}</td>
                        <td class="qty">x{{ $detail->jumlah }}</td>
                        <td class="price">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="totals">
            <table>
                <tr>
                    <td class="label">Subtotal</td>
                    <td class="price">Rp {{ number_format($pesanan->total, 0, ',', '.') }}</td>
                </tr>
                @if($pesanan->discount_amount > 0)
                <tr>
                    <td class="label" style="color: #d93025;">Diskon (Promo)</td>
                    <td class="price" style="color: #d93025;">- Rp {{ number_format($pesanan->discount_amount, 0, ',', '.') }}</td>
                </tr>
                @endif
                <tr class="total-row">
                    <td>TOTAL BAYAR</td>
                    <td class="price">Rp {{ number_format($pesanan->pembayaran->total_bayar ?? ($pesanan->total - $pesanan->discount_amount), 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        <div class="footer">
            @php 
                $footerText = \App\Models\Setting::getVal('receipt_footer');
                if (empty($footerText)) {
                    $footerText = 'Terima kasih atas kunjungan Anda!';
                }
            @endphp
            <p>{{ $footerText }}</p>
            <p style="margin-top: 15px; font-size: 11px; color: #aaa;">Ini adalah struk otomatis yang sah. Harap simpan sebagai bukti pembayaran Anda.</p>
        </div>
    </div>
</body>
</html>
