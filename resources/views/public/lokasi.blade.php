@extends('layouts.app')

@section('content')
<!-- Header Banner -->
<div class="bg-primary text-white pt-5 pb-5 mb-5 position-relative overflow-hidden rounded-bottom-4 shadow-sm" style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
    <div class="position-absolute top-0 start-0 w-100 h-100" style="background: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'0.1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E'); pointer-events: none;"></div>
    <div class="container text-center position-relative z-index-1 py-3">
        <h1 class="display-5 fw-bold mb-3">Titik Temu Kita</h1>
        <p class="fs-5 text-light opacity-75 mx-auto mb-0" style="max-width: 600px;">
            Temukan lokasi gerobak angkringan kami. Suasana hangat dan hidangan lezat telah menanti kedatangan Anda.
        </p>
    </div>
</div>

<div class="container mb-5 pb-5">
    
    <div class="row g-4 align-items-center mb-5">
        <!-- Info Cards -->
        <div class="col-lg-4">
            <div class="d-flex flex-column gap-4">
                
                <!-- Card Alamat -->
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden hover-lift" style="transition: transform 0.3s ease;">
                    <div class="card-body p-4 d-flex align-items-start">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3 me-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; flex-shrink: 0;">
                            <i class="bi bi-geo-alt-fill fs-3"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-2">Lokasi Utama</h5>
                            <p class="text-muted mb-0 lh-base">
                                <strong>Rusunawa Politeknik Negeri Bengkalis (Polbeng)</strong><br>
                                Jl. Bathin Alam, Sungai Alam, Kec. Bengkalis<br>
                                Kab. Bengkalis, Riau 28711
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Card Jam Operasional -->
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden hover-lift" style="transition: transform 0.3s ease;">
                    <div class="card-body p-4 d-flex align-items-start">
                        <div class="bg-warning bg-opacity-10 text-warning rounded-circle p-3 me-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; flex-shrink: 0;">
                            <i class="bi bi-clock-fill fs-3"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-2">Jam Operasional</h5>
                            <div class="d-flex justify-content-between text-muted mb-1 border-bottom pb-1">
                                <span>Senin - Sabtu</span>
                                <span class="fw-bold text-dark">08.00 - 24.00</span>
                            </div>
                            <div class="d-flex justify-content-between text-danger mb-0">
                                <span>Minggu</span>
                                <span class="fw-bold">Tutup</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Card Panduan -->
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-light">
                    <div class="card-body p-4">
                        <h6 class="fw-bold text-primary mb-2"><i class="bi bi-info-circle-fill me-2"></i>Panduan Menuju Lokasi</h6>
                        <p class="text-muted small mb-0">
                            Masuk dari gerbang utama Polbeng, lurus ikuti jalan utama, lalu belok ke arah asrama mahasiswa (Rusunawa). Gerobak angkringan kami berada tepat di area titik kumpul Rusunawa.
                        </p>
                    </div>
                </div>
                
            </div>
        </div>

        <!-- Google Maps -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                <div class="card-body p-0 position-relative" style="min-height: 500px;">
                    <iframe 
                        src="https://maps.google.com/maps?q=Politeknik%20Negeri%20Bengkalis&t=&z=16&ie=UTF8&iwloc=&output=embed" 
                        class="w-100 h-100 position-absolute top-0 start-0" 
                        style="border:0;" 
                        allowfullscreen="" 
                        loading="lazy" 
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                    
                    <!-- Floating Badge on Map -->
                    <div class="position-absolute bottom-0 start-50 translate-middle-x mb-4 bg-white px-4 py-2 rounded-pill shadow fw-bold text-primary d-flex align-items-center" style="z-index: 10;">
                        <span class="spinner-grow spinner-grow-sm text-danger me-2" role="status"></span>
                        Posisi Kami di Sini
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .hover-lift:hover {
        transform: translateY(-5px);
        box-shadow: 0 .5rem 1.5rem rgba(0,0,0,.15)!important;
    }
</style>
@endsection