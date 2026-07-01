<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk Dapur - #{{ $order->id }}</title>
    <style>
        body { font-family: 'Courier New', Courier, monospace; width: 58mm; margin: 0 auto; padding: 10px; font-size: 12px; }
        .text-center { text-align: center; }
        .font-weight-bold { font-weight: bold; }
        .mb-1 { margin-bottom: 5px; }
        .mt-2 { margin-top: 10px; }
        .border-bottom { border-bottom: 1px dashed #000; padding-bottom: 5px; margin-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; vertical-align: top; }
        .text-right { text-align: right; }
        
        @media print {
            body { width: 100%; padding: 0; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="text-center border-bottom">
        <h3 class="mb-1 mt-2">STRUK DAPUR</h3>
        <p style="font-size: 14px; margin: 0;"><strong>PESANAN #{{ $order->id }}</strong></p>
    </div>
    
    <div class="border-bottom" style="padding-top: 5px;">
        <table>
            <tr>
                <td>Tanggal</td>
                <td class="text-right">{{ $order->created_at->format('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <td>Tipe</td>
                <td class="text-right font-weight-bold">{{ strtoupper(str_replace('_', ' ', $order->tipe_pesanan)) }}</td>
            </tr>
            <tr>
                <td>Meja</td>
                <td class="text-right font-weight-bold">{{ $order->meja->nama_meja_atau_nomor ?? '-' }}</td>
            </tr>
        </table>
    </div>

    <div class="border-bottom">
        <table class="mt-2" style="margin-bottom: 10px;">
            <thead>
                <tr>
                    <th style="width: 20px;">Qty</th>
                    <th>Item Menu</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->detail_pesanan as $item)
                <tr>
                    <td class="font-weight-bold" style="font-size: 14px;">{{ $item->jumlah }}x</td>
                    <td style="font-size: 14px;">
                        {{ $item->menu->nama_menu }}
                        @if($item->selected_variants)
                            @php 
                                $variants = json_decode($item->selected_variants, true); 
                            @endphp
                            @if(is_array($variants) && count($variants) > 0)
                                <br><small style="font-size: 11px;">- 
                                    @foreach($variants as $idx => $v)
                                        {{ isset($v['qty']) && $v['qty'] > 1 ? $v['qty'].'x ' : '' }}{{ $v['name'] }}{{ $idx < count($variants) - 1 ? ', ' : '' }}
                                    @endforeach
                                </small>
                            @endif
                        @endif
                        @if($item->catatan)
                            <br><small style="font-size: 11px; font-style: italic;">* {{ $item->catatan }}</small>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="text-center mt-2">
        <p class="font-weight-bold" style="font-size: 16px;">-- SEGERA SIAPKAN --</p>
    </div>
</body>
</html>
