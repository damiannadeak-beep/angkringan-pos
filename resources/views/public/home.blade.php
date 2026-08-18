@extends('layouts.app')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
    
    body {
        background-color: #fdfbf7;
        font-family: 'Plus Jakarta Sans', sans-serif !important;
        color: #2d1a11;
    }

    /* Minimalist Hero Section */
    .hero-minimal {
        padding: 4.5rem 0 3.5rem 0;
        background: #fdfbf7;
        border-bottom: 1px solid #eee8df;
    }

    .badge-subtle {
        background: #f0e9dd;
        color: #5d4037;
        font-size: 0.85rem;
        font-weight: 700;
        padding: 0.4rem 1.1rem;
        border-radius: 999px;
    }

    .hero-headline {
        font-weight: 800;
        font-size: 2.75rem;
        color: #3e2723;
        line-height: 1.25;
        letter-spacing: -0.02em;
    }

    .hero-subtext {
        color: #6d584c;
        font-size: 1.05rem;
        line-height: 1.7;
        max-width: 600px;
    }

    /* Buttons */
    .btn-minimal-primary {
        background: #3e2723;
        color: #ffffff !important;
        font-weight: 700;
        padding: 0.85rem 2rem;
        border-radius: 999px;
        border: none;
        transition: background 0.2s ease, transform 0.2s ease;
    }

    .btn-minimal-primary:hover {
        background: #2d1a11;
        transform: translateY(-2px);
    }

    .btn-minimal-outline {
        background: transparent;
        color: #3e2723 !important;
        font-weight: 700;
        padding: 0.85rem 1.75rem;
        border-radius: 999px;
        border: 1.5px solid #d7ccc8;
        transition: all 0.2s ease;
    }

    .btn-minimal-outline:hover {
        border-color: #3e2723;
        background: #f0e9dd;
    }

    /* Minimalist Product Card */
    .card-minimal-product {
        background: #ffffff;
        border: 1px solid #eae3d8;
        border-radius: 1.25rem;
        padding: 1.25rem;
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    }

    .card-minimal-product:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 28px rgba(62, 39, 35, 0.08);
        border-color: #d49b78;
    }

    .product-img-box {
        background: #faf6f0;
        border-radius: 1rem;
        height: 190px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
        overflow: hidden;
    }

    .product-img-box img {
        max-height: 85%;
        max-width: 85%;
        object-fit: contain;
    }

    .price-text {
        color: #b05923;
        font-weight: 800;
        font-size: 1.1rem;
    }

    /* Minimalist Story Section */
    .story-card {
        background: #ffffff;
        border: 1px solid #eae3d8;
        border-radius: 1.5rem;
        padding: 2.5rem;
    }
</style>

<!-- Hero Section -->
<section class="hero-minimal">
    <div class="container text-center">
        <div class="d-inline-block mb-3">
            <span class="badge-subtle">
                <i class="bi bi-cup-hot-fill me-1"></i> Angkringan & Racikan Minuman Segar
            </span>
        </div>

        <h1 class="hero-headline mb-3">
            Kehangatan Racikan Minuman,<br>
            Kesederhanaan Suasana Angkringan
        </h1>

        <p class="hero-subtext mx-auto mb-4">
            Nikmati sajian khas Teh Gentong, Lemon Tea, dan aneka racikan segar langsung dari meja Anda via Scan QR Code.
        </p>

        <div class="d-flex flex-wrap justify-content-center gap-3">
            <a href="/katalog" class="btn btn-minimal-primary text-decoration-none">
                <i class="bi bi-grid-fill me-2"></i> Lihat Katalog Menu
            </a>
            <a href="/lokasi" class="btn btn-minimal-outline text-decoration-none">
                <i class="bi bi-geo-alt-fill me-1"></i> Lokasi Warung
            </a>
        </div>
    </div>
</section>

<!-- Menu Highlights Section -->
<section class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <span class="text-uppercase fw-bold small text-muted">Menu Pilihan</span>
                <h3 class="fw-bold mb-0 text-dark">Daftar Minuman Segar</h3>
            </div>
            <a href="/katalog" class="text-decoration-none fw-bold text-dark small">
                Lihat Semua Menu <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="row g-4">
            @if(isset($featuredMenus) && count($featuredMenus) > 0)
                @foreach($featuredMenus as $menu)
                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="card-minimal-product h-100 d-flex flex-column justify-content-between">
                            <div>
                                <div class="product-img-box">
                                    @if($menu->image)
                                        <img src="{{ asset('storage/'.$menu->image) }}" alt="{{ $menu->nama_menu }}">
                                    @else
                                        <i class="bi bi-cup-straw text-muted fs-1 opacity-50"></i>
                                    @endif
                                </div>
                                <span class="badge bg-light text-dark fw-bold mb-2 font-sans" style="font-size: 0.75rem; border: 1px solid #ddd;">
                                    {{ strtoupper($menu->kategori) }}
                                </span>
                                <h6 class="fw-bold text-dark mb-2">{{ $menu->nama_menu }}</h6>
                            </div>
                            <div class="pt-3 border-top d-flex align-items-center justify-content-between">
                                <span class="price-text">Rp {{ number_format($menu->harga, 0, ',', '.') }}</span>
                                <a href="/katalog" class="btn btn-sm btn-outline-dark rounded-pill px-3 fw-semibold">Pesan</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="col-12 text-center py-4">
                    <p class="text-muted">Menu sedang disiapkan.</p>
                </div>
            @endif
        </div>
    </div>
</section>

<!-- Latar Belakang & Fitur Minimalis -->
<section class="pb-5 mb-4">
    <div class="container">
        <div class="row g-4 align-items-stretch">
            
            <!-- Latar Belakang Pendirian Warung -->
            <div class="col-lg-7">
                <div class="story-card h-100">
                    <span class="badge-subtle mb-3 d-inline-block">Latar Belakang Warung</span>
                    <h3 class="fw-bold mb-3">Tentang Angkringan Kami</h3>
                    <p class="text-muted mb-3" style="line-height: 1.8; text-align: justify;">
                        Angkringan ini didirikan dari keinginan sederhana: menyediakan tempat santai yang hangat, ramah kantong, dan nyaman untuk melepas penat setelah seharian beraktivitas.
                    </p>
                    <p class="text-muted mb-0" style="line-height: 1.8; text-align: justify;">
                        Fokus utama kami adalah menyajikan aneka racikan teh khas (seperti Teh Gentong, Lemon Tea, Teh Caramel, dan Teh Leci) serta sajian segar lainnya dengan pelayanan pemesanan mandiri berbasis QR Code yang cepat dan praktis.
                    </p>
                </div>
            </div>

            <!-- Ringkasan Fitur Layanan -->
            <div class="col-lg-5">
                <div class="story-card h-100 d-flex flex-column justify-content-between">
                    <div>
                        <span class="badge-subtle mb-3 d-inline-block">Keunggulan Layanan</span>
                        <h4 class="fw-bold mb-4">Mengapa Pesan di Sini?</h4>
                        
                        <div class="d-flex align-items-start mb-3">
                            <i class="bi bi-qr-code-scan fs-4 text-dark me-3"></i>
                            <div>
                                <h6 class="fw-bold mb-1">Scan QR Code Mandiri</h6>
                                <small class="text-muted">Pesan minuman favorit langsung dari meja tanpa perlu mengantre.</small>
                            </div>
                        </div>

                        <div class="d-flex align-items-start mb-3">
                            <i class="bi bi-wallet2 fs-4 text-dark me-3"></i>
                            <div>
                                <h6 class="fw-bold mb-1">Harga Terjangkau</h6>
                                <small class="text-muted">Pilihan menu nikmat dan bersahabat untuk semua kalangan.</small>
                            </div>
                        </div>

                        <div class="d-flex align-items-start">
                            <i class="bi bi-wifi-off fs-4 text-dark me-3"></i>
                            <div>
                                <h6 class="fw-bold mb-1">Sistem PWA Offline-First</h6>
                                <small class="text-muted">Aplikasi handal dan siap memproses pesanan kapan saja.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
@endsection