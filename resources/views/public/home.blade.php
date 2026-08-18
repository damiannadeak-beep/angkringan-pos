@extends('layouts.app')

@section('content')
<style>
    /* Modern Clean F&B Design System */
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
    
    :root {
        --brand-dark: #2d1a11;
        --brand-brown: #3e2723;
        --brand-warm: #5d4037;
        --brand-accent: #b05923;
        --brand-cream: #f0e9dd;
        --bg-warm: #fcfaf7;
    }
    
    body {
        background-color: var(--bg-warm);
        font-family: 'Plus Jakarta Sans', sans-serif !important;
        color: var(--brand-dark);
        overflow-x: hidden;
    }

    /* Hero Banner Section */
    .hero-banner {
        position: relative;
        padding: 5.5rem 0 5rem 0;
        background: linear-gradient(135deg, #f0e9dd 0%, #e6ddcf 50%, #fdfbf7 100%);
        border-bottom: 1px solid rgba(62, 39, 35, 0.08);
        overflow: hidden;
    }

    .hero-badge {
        background: #ffffff;
        color: var(--brand-brown);
        border: 1px solid rgba(62, 39, 35, 0.12);
        padding: 0.5rem 1.25rem;
        border-radius: 999px;
        font-size: 0.88rem;
        font-weight: 700;
        box-shadow: 0 4px 12px rgba(62, 39, 35, 0.04);
    }

    .hero-title {
        font-weight: 800;
        color: var(--brand-dark);
        letter-spacing: -0.02em;
        line-height: 1.2;
    }

    .hero-title span {
        color: var(--brand-accent);
    }

    .hero-desc {
        color: #665247;
        font-size: 1.1rem;
        line-height: 1.7;
    }

    /* Primary Action Buttons */
    .btn-main-order {
        background: linear-gradient(135deg, #3e2723 0%, #2d1a11 100%);
        color: #ffffff !important;
        font-weight: 700;
        padding: 0.95rem 2.25rem;
        border-radius: 999px;
        border: none;
        box-shadow: 0 10px 24px rgba(62, 39, 35, 0.25);
        transition: all 0.25s ease;
    }

    .btn-main-order:hover {
        transform: translateY(-2px);
        box-shadow: 0 14px 30px rgba(62, 39, 35, 0.35);
    }

    .btn-secondary-outline {
        background: #ffffff;
        color: var(--brand-brown) !important;
        font-weight: 700;
        padding: 0.95rem 2rem;
        border-radius: 999px;
        border: 2px solid var(--brand-warm);
        transition: all 0.25s ease;
    }

    .btn-secondary-outline:hover {
        background: var(--brand-warm);
        color: #ffffff !important;
        transform: translateY(-2px);
    }

    /* Stat Highlight Bar */
    .stat-bar-card {
        background: #ffffff;
        border: 1px solid rgba(62, 39, 35, 0.08);
        border-radius: 1.25rem;
        padding: 1.25rem;
        box-shadow: 0 8px 24px rgba(62, 39, 35, 0.04);
        transition: transform 0.25s ease;
    }

    .stat-bar-card:hover {
        transform: translateY(-3px);
    }

    /* Popular Product Cards Modern Showcase */
    .product-showcase-card {
        background: #ffffff;
        border: 1px solid rgba(62, 39, 35, 0.08);
        border-radius: 1.5rem;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(62, 39, 35, 0.05);
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .product-showcase-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 18px 40px rgba(62, 39, 35, 0.12);
        border-color: rgba(176, 89, 35, 0.25);
    }

    .product-img-wrapper {
        height: 180px;
        background: #faf5ee;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .product-img-wrapper img {
        max-height: 85%;
        max-width: 85%;
        object-fit: contain;
        transition: transform 0.35s ease;
    }

    .product-showcase-card:hover .product-img-wrapper img {
        transform: scale(1.08);
    }

    .price-tag {
        background: var(--brand-brown);
        color: #ffffff;
        font-weight: 700;
        font-size: 0.85rem;
        padding: 0.35rem 0.85rem;
        border-radius: 999px;
        position: absolute;
        bottom: 12px;
        left: 12px;
    }

    .badge-category {
        background: rgba(176, 89, 35, 0.12);
        color: var(--brand-accent);
        font-weight: 700;
        font-size: 0.75rem;
        padding: 0.3rem 0.75rem;
        border-radius: 999px;
    }

    /* Clean Info Cards */
    .info-feature-box {
        background: #ffffff;
        border: 1px solid rgba(62, 39, 35, 0.08);
        border-radius: 1.5rem;
        padding: 2.25rem 1.75rem;
        box-shadow: 0 8px 24px rgba(62, 39, 35, 0.04);
        transition: all 0.3s ease;
    }

    .info-feature-box:hover {
        transform: translateY(-4px);
        box-shadow: 0 16px 36px rgba(62, 39, 35, 0.1);
        border-color: rgba(93, 64, 55, 0.2);
    }

    .info-icon-circle {
        width: 64px;
        height: 64px;
        border-radius: 1.25rem;
        background: linear-gradient(135deg, var(--brand-brown), var(--brand-warm));
        color: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1.25rem;
        box-shadow: 0 8px 20px rgba(62, 39, 35, 0.15);
    }
</style>

<!-- Hero Section -->
<section class="hero-banner">
    <div class="container text-center">
        <div class="d-inline-flex align-items-center hero-badge mb-4">
            <i class="bi bi-stars text-warning me-2 fs-5"></i> Aneka Makanan, Kopi & Minuman Segar
        </div>
        
        <h1 class="display-4 hero-title mb-4">
            Nikmati Kehangatan Kuliner<br>
            <span>& Pemesanan QR Code Cerdas</span>
        </h1>
        
        <p class="hero-desc mx-auto mb-5" style="max-width: 620px;">
            Sajikan racikan minuman favorit dan hidangan lezat langsung dari meja Anda. Praktis, cepat, dan tanpa perlu antre lama.
        </p>

        <div class="d-flex flex-column flex-sm-row justify-content-center align-items-center gap-3">
            <a href="/katalog" class="btn btn-main-order text-decoration-none">
                <i class="bi bi-bag-check-fill me-2"></i> Lihat Semua Menu
            </a>
            <a href="/lokasi" class="btn btn-secondary-outline text-decoration-none">
                <i class="bi bi-geo-alt-fill me-2"></i> Lokasi Warung
            </a>
        </div>
    </div>
</section>

<!-- Stat Bar Highlights (Sesuai Menu Asli Store: Mie & Kopi/Minuman) -->
<section class="position-relative z-3" style="margin-top: -2rem;">
    <div class="container">
        <div class="row g-3 justify-content-center">
            <div class="col-6 col-md-3">
                <div class="stat-bar-card text-center">
                    <h4 class="fw-bold text-dark mb-1">Makanan & Mie</h4>
                    <small class="text-muted fw-semibold">Hidangan Lezat & Gurih</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-bar-card text-center">
                    <h4 class="fw-bold text-dark mb-1">Coffee & Tea</h4>
                    <small class="text-muted fw-semibold">Minuman Segar & Rempah</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-bar-card text-center">
                    <h4 class="fw-bold text-dark mb-1">Order QR Code</h4>
                    <small class="text-muted fw-semibold">Pesan Langsung dari Meja</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-bar-card text-center">
                    <h4 class="fw-bold text-dark mb-1">PWA Offline</h4>
                    <small class="text-muted fw-semibold">Aplikasi Selalu Siap 24/7</small>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Popular Menu Cards Showcase -->
<section class="py-5 my-3">
    <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4 gap-2">
            <div>
                <span class="badge-category mb-2 d-inline-block"><i class="bi bi-fire me-1"></i> Paling Disukai</span>
                <h2 class="fw-bold mb-0 text-dark" style="letter-spacing: -0.01em;">Menu Terfavorit</h2>
            </div>
            <a href="/katalog" class="text-decoration-none fw-bold text-primary">
                Lihat Semua Menu <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="row g-4 justify-content-center">
            @if(isset($featuredMenus) && count($featuredMenus) > 0)
                @foreach($featuredMenus as $menu)
                    <div class="col-12 col-sm-6 col-lg-4">
                        <div class="product-showcase-card h-100 d-flex flex-column justify-content-between">
                            <div>
                                <div class="product-img-wrapper">
                                    @if($menu->image)
                                        <img src="{{ asset('storage/'.$menu->image) }}" alt="{{ $menu->nama_menu }}">
                                    @else
                                        <i class="bi bi-cup-hot text-muted" style="font-size: 3.5rem; opacity: 0.4;"></i>
                                    @endif
                                    <span class="price-tag">Rp {{ number_format($menu->harga, 0, ',', '.') }}</span>
                                </div>
                                <div class="p-4">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge-category text-uppercase">{{ $menu->kategori }}</span>
                                        <small class="text-muted fw-semibold">Sisa: {{ $menu->stok }}</small>
                                    </div>
                                    <h5 class="fw-bold text-dark mb-2">{{ $menu->nama_menu }}</h5>
                                </div>
                            </div>
                            <div class="px-4 pb-4">
                                <a href="/katalog" class="btn btn-outline-secondary w-100 rounded-pill fw-bold py-2 border-2">
                                    Pesan Sekarang <i class="bi bi-cart-plus ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="col-12 text-center py-5">
                    <i class="bi bi-basket text-muted" style="font-size: 3rem; opacity: 0.4;"></i>
                    <p class="text-muted mt-2 fw-semibold">Belum ada menu populer yang ditampilkan.</p>
                </div>
            @endif
        </div>
    </div>
</section>

<!-- Values Section (Modern Feature Cards) -->
<section class="py-4 mb-5">
    <div class="container">
        <div class="row g-4 justify-content-center">
            
            <div class="col-lg-4 col-md-6">
                <div class="info-feature-box h-100">
                    <div class="info-icon-circle">
                        <i class="bi bi-wallet2 fs-3"></i>
                    </div>
                    <h4 class="fw-bold text-dark mb-3">Harga Bersahabat</h4>
                    <p class="text-muted mb-0" style="line-height: 1.7; font-size: 0.95rem;">
                        Sajian hidangan lezat dan minuman segar dengan harga yang terjangkau untuk semua pelanggan.
                    </p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="info-feature-box h-100">
                    <div class="info-icon-circle" style="background: linear-gradient(135deg, var(--brand-accent), var(--brand-brown));">
                        <i class="bi bi-qr-code-scan fs-3"></i>
                    </div>
                    <h4 class="fw-bold text-dark mb-3">Pesanan QR Code</h4>
                    <p class="text-muted mb-0" style="line-height: 1.7; font-size: 0.95rem;">
                        Pesan makanan & minuman langsung dari meja Anda secara praktis tanpa perlu antre di kasir.
                    </p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="info-feature-box h-100">
                    <div class="info-icon-circle">
                        <i class="bi bi-cup-hot-fill fs-3"></i>
                    </div>
                    <h4 class="fw-bold text-dark mb-3">Suasana Hangat</h4>
                    <p class="text-muted mb-0" style="line-height: 1.7; font-size: 0.95rem;">
                        Tempat santai yang nyaman untuk berkumpul bersama teman dan keluarga sambil menikmati sajian favorit.
                    </p>
                </div>
            </div>

        </div>
    </div>
</section>
@endsection