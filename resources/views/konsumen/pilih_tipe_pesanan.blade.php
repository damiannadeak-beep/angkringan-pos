@extends('layouts.app')

@section('content')
<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow-lg border-0">
                <div class="card-body p-5">
                    <h2 class="fw-bold text-center mb-2">Pilih Jenis Pesanan</h2>
                    <p class="text-center text-muted mb-5">Apakah Anda ingin memesan untuk dimakan di tempat atau dibawa pulang?</p>
                    
                    <div class="row g-3">
                        <!-- Dine In Option -->
                        <div class="col-md-6">
                            <a href="{{ url('/konsumen/menu') }}" class="text-decoration-none">
                                <div class="card h-100 border-2 border-primary text-center p-4 cursor-pointer hover-shadow"
                                     style="transition: all 0.3s ease; cursor: pointer;">
                                    <div class="mb-3">
                                        <i class="fas fa-chair" style="font-size: 48px; color: #007bff;"></i>
                                    </div>
                                    <h5 class="fw-bold">Makan di Tempat</h5>
                                    <p class="text-muted small">Pilih meja untuk dinikmati di restoran</p>
                                </div>
                            </a>
                        </div>

                        <!-- Takeaway Option -->
                        <div class="col-md-6">
                            <a href="{{ url('/konsumen/menu-takeaway') }}" class="text-decoration-none">
                                <div class="card h-100 border-2 border-success text-center p-4 cursor-pointer hover-shadow"
                                     style="transition: all 0.3s ease; cursor: pointer;">
                                    <div class="mb-3">
                                        <i class="fas fa-bag-shopping" style="font-size: 48px; color: #198754;"></i>
                                    </div>
                                    <h5 class="fw-bold">Dibawa Pulang</h5>
                                    <p class="text-muted small">Pesan untuk dibawa pulang</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .hover-shadow:hover {
        box-shadow: 0 8px 20px rgba(0,0,0,0.15) !important;
        transform: translateY(-2px);
    }
</style>
@endsection
