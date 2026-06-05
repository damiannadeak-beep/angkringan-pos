@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="card shadow-sm border-0 py-5">
                <div class="card-body">
                    <i class="bi bi-qr-code-scan text-primary mb-3" style="font-size: 4rem;"></i>
                    <h2 class="fw-bold">Selamat Datang, {{ auth()->user()->nama }}!</h2>
                    <p class="text-muted fs-5 mb-4">
                        Untuk mulai memesan, silakan buka kamera HP Anda dan <br> 
                        <strong>Scan QR Code</strong> yang terdapat di meja Anda.
                    </p>
                    
                    <a href="/konsumen/riwayat" class="btn btn-outline-primary px-4 rounded-pill">
                        Lihat Riwayat Pesanan Saya
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection