@extends('layouts.app') @section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Ringkasan Pesanan #{{ $pesanan->id }}</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush mb-3">
                        @foreach($pesanan->detail_pesanan as $detail)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="my-0">{{ $detail->menu->nama_menu }}</h6>
                                <small class="text-muted">{{ $detail->jumlah }}x @ Rp {{ number_format($detail->menu->harga, 0, ',', '.') }}</small>
                            </div>
                            <span class="text-muted">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</span>
                        </li>
                        @endforeach
                    </ul>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold fs-5">
                        <span>Total Bayar</span>
                        <span>Rp {{ number_format($pembayaran->total_bayar, 0, ',', '.') }}</span>
                    </div>
                </div>
                <div class="card-footer bg-white text-center py-3">
                    <button id="pay-button" class="btn btn-success btn-lg w-100 mb-2">
                        Bayar dengan QRIS
                    </button>
                    <button onclick="cancelOrder({{ $pesanan->id }})" class="btn btn-outline-danger w-100 fw-bold">
                        Batalkan Pesanan
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('services.midtrans.clientKey') }}"></script>

<script type="text/javascript">
    document.getElementById('pay-button').onclick = function () {
        // Panggil fungsi snap.pay() dengan token yang sudah di-generate dari Controller
        snap.pay('{{ $pembayaran->snap_token }}', {
            // Callback ketika pembayaran sukses
            onSuccess: function(result){
                alert("Pembayaran berhasil!"); 
                window.location.href = "/konsumen/profil"; // Redirect ke halaman riwayat
            },
            // Callback ketika pembayaran pending
            onPending: function(result){
                alert("Menunggu konfirmasi pembayaran Anda.");
            },
            // Callback ketika pembayaran gagal
            onError: function(result){
                alert("Pembayaran gagal, silakan coba lagi.");
            },
            // Callback ketika konsumen menutup popup tanpa membayar
            onClose: function(){
                alert("Anda menutup halaman pembayaran sebelum menyelesaikannya.");
            }
        });
    };

    function cancelOrder(id) {
        if (!confirm('Apakah Anda yakin ingin membatalkan pesanan ini?')) return;

        fetch(`/konsumen/order/${id}/cancel`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
            } else {
                alert(data.message);
                window.location.href = "/konsumen/profil"; // Kembali ke profil
            }
        })
        .catch(err => {
            console.error(err);
            alert("Terjadi kesalahan sistem.");
        });
    }
</script>
@endsection