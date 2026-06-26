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
                <div class="card-footer bg-white py-4">
                    <h6 class="fw-bold mb-3 text-center">Pilih Metode Pembayaran:</h6>
                    
                    <div class="accordion" id="paymentAccordion">
                        
                        <!-- Midtrans Online Payment Removed -->
                        <!-- Pilihan 2: Tunai (Cash) -->
                        <div class="accordion-item border-success mb-2 rounded shadow-sm">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed fw-bold text-success" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCash" aria-expanded="false" aria-controls="collapseCash">
                                    <i class="bi bi-cash-stack me-2"></i> Bayar Tunai (Cash)
                                </button>
                            </h2>
                            <div id="collapseCash" class="accordion-collapse collapse show" data-bs-parent="#paymentAccordion">
                                <div class="accordion-body text-center bg-success bg-opacity-10">
                                    <i class="bi bi-person-check fs-1 text-success mb-2 d-block"></i>
                                    <p class="mb-0 fw-bold">Silakan datangi meja kasir.</p>
                                    <p class="small text-muted">Sebutkan nama atau nomor meja Anda kepada kasir untuk melakukan pembayaran tunai.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Pilihan 3: QRIS Manual -->
                        @php $qrisImage = \App\Models\Setting::getVal('qris_image'); @endphp
                        @if($qrisImage)
                        <div class="accordion-item border-info mb-2 rounded shadow-sm">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed fw-bold text-info" type="button" data-bs-toggle="collapse" data-bs-target="#collapseQris" aria-expanded="false" aria-controls="collapseQris">
                                    <i class="bi bi-qr-code-scan me-2"></i> Scan QRIS Manual
                                </button>
                            </h2>
                            <div id="collapseQris" class="accordion-collapse collapse" data-bs-parent="#paymentAccordion">
                                <div class="accordion-body text-center bg-light">
                                    <p class="small text-muted mb-3">Scan kode QR di bawah ini dengan aplikasi DANA, OVO, Gopay, atau M-Banking Anda.</p>
                                    <img src="{{ asset('storage/'.$qrisImage) }}" alt="QRIS Warung" class="img-fluid rounded border border-3 border-info mb-3" style="max-height: 250px;">
                                    
                                    <p class="small text-danger fw-bold"><i class="bi bi-exclamation-triangle-fill me-1"></i> Setelah transfer, wajib tunjukkan bukti transfer ke Kasir untuk konfirmasi pesanan!</p>
                                </div>
                            </div>
                        </div>
                        @endif

                    </div>

                    <div class="mt-4 pt-3 border-top text-center">
                        <button onclick="cancelOrder({{ $pesanan->id }})" class="btn btn-outline-danger w-100 fw-bold rounded-pill">
                            Batalkan Pesanan
                        </button>
                        <a href="/konsumen/profil" class="btn btn-link text-muted mt-2 small text-decoration-none">
                            <i class="bi bi-arrow-left me-1"></i> Kembali ke Profil
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')


<script type="text/javascript">

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
                window.location.href = "/konsumen/profil";
            }
        })
        .catch(err => {
            console.error(err);
            alert("Terjadi kesalahan sistem.");
        });
    }
</script>
@endsection