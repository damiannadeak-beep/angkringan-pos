<div style="font-family: sans-serif; line-height:1.6;">
    <h2>Promo Baru: {{ $promo->title }}</h2>
    @if($promo->description)
        <p>{{ $promo->description }}</p>
    @endif
    <p>Tipe: {{ $promo->type }}, Nilai: {{ $promo->value }}</p>
    <p>Periode: {{ $promo->starts_at? $promo->starts_at->format('d M Y H:i') : '-' }} sampai {{ $promo->ends_at? $promo->ends_at->format('d M Y H:i') : '-' }}</p>
    <p>Terima kasih telah menjadi pelanggan kami.</p>
</div>
