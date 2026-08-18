@extends('layouts.app')

@section('content')
<style>
    /* Premium Aesthetic Palette & Variables */
    :root {
        --color-primary: #3e2723;
        --color-secondary: #5d4037;
        --color-accent: #b05923;
        --color-accent-light: #d49b78;
        --color-bg-light: #fdfbf7;
        --color-cream: #f0e9dd;
        --glass-card-bg: rgba(255, 255, 255, 0.88);
        --glass-card-border: rgba(62, 39, 35, 0.08);
    }
    
    body {
        background-color: var(--color-bg-light);
        overflow-x: hidden;
    }

    /* Hero Section with Animated Soft Gradient */
    .hero-section {
        position: relative;
        padding: 7rem 0 7rem 0;
        background: linear-gradient(135deg, #f0e9dd 0%, #e6ddcf 40%, #fdfbf7 70%, #d7ccc8 100%);
        background-size: 300% 300%;
        animation: gradientShift 18s ease infinite;
        overflow: hidden;
        border-bottom-left-radius: 3rem;
        border-bottom-right-radius: 3rem;
        box-shadow: 0 20px 40px rgba(62, 39, 35, 0.06);
    }

    @keyframes gradientShift {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }

    /* Decorative Ambient Glow Blobs */
    .blob {
        position: absolute;
        filter: blur(70px);
        z-index: 0;
        opacity: 0.55;
        border-radius: 50%;
        animation: blobFloat 12s ease-in-out infinite;
    }
    .blob-1 { top: -15%; left: -10%; width: 450px; height: 450px; background: #e6ddcf; animation-delay: 0s; }
    .blob-2 { bottom: -25%; right: -10%; width: 550px; height: 550px; background: #d7ccc8; animation-delay: -6s; }

    @keyframes blobFloat {
        0% { transform: translate(0, 0) scale(1); }
        50% { transform: translate(25px, -35px) scale(1.08); }
        100% { transform: translate(0, 0) scale(1); }
    }

    .hero-content {
        position: relative;
        z-index: 2;
    }

    /* Glassmorphism Feature Cards */
    .glass-feature-card {
        background: var(--glass-card-bg);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid var(--glass-card-border);
        border-radius: 1.75rem;
        box-shadow: 0 12px 36px rgba(62, 39, 35, 0.05);
        transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        position: relative;
        overflow: hidden;
    }

    .glass-feature-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--color-secondary), var(--color-accent));
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .glass-feature-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 22px 48px rgba(62, 39, 35, 0.12);
        border-color: rgba(176, 89, 35, 0.2);
    }

    .glass-feature-card:hover::before {
        opacity: 1;
    }

    /* Icon Badge Wrapper */
    .icon-badge {
        width: 72px;
        height: 72px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 1.25rem;
        background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
        color: #f0e9dd;
        box-shadow: 0 10px 24px rgba(62, 39, 35, 0.2);
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .glass-feature-card:hover .icon-badge {
        transform: scale(1.08) rotate(4deg);
        background: linear-gradient(135deg, var(--color-accent), var(--color-primary));
        color: #ffffff;
    }

    /* Primary Animated Button */
    .btn-brand-primary {
        background: linear-gradient(135deg, var(--color-primary), #2d1a11);
        color: #f0e9dd;
        border: none;
        position: relative;
        z-index: 1;
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        box-shadow: 0 10px 24px rgba(62, 39, 35, 0.25);
    }

    .btn-brand-primary::after {
        content: '';
        position: absolute;
        top: 0; left: 0; width: 100%; height: 100%;
        background: linear-gradient(to right, transparent, rgba(255,255,255,0.2), transparent);
        transform: translateX(-100%);
        transition: transform 0.6s ease;
        z-index: -1;
    }

    .btn-brand-primary:hover::after {
        transform: translateX(100%);
    }

    .btn-brand-primary:hover {
        color: #ffffff;
        transform: translateY(-3px);
        box-shadow: 0 16px 32px rgba(62, 39, 35, 0.35);
    }

    .btn-brand-outline {
        background: rgba(255, 255, 255, 0.7);
        color: var(--color-primary);
        border: 2px solid var(--color-secondary);
        backdrop-filter: blur(8px);
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .btn-brand-outline:hover {
        background: var(--color-secondary);
        color: #ffffff;
        transform: translateY(-3px);
        box-shadow: 0 12px 28px rgba(93, 64, 55, 0.2);
    }

    /* Section Title Polish */
    .section-title {
        font-family: 'Playfair Display', Georgia, serif;
        position: relative;
        display: inline-block;
        color: var(--color-primary);
    }
    
    .section-title::after {
        content: '';
        position: absolute;
        width: 48px;
        height: 3px;
        background: var(--color-accent);
        bottom: -12px;
        left: 0;
        border-radius: 999px;
        transition: width 0.3s ease;
    }

    .section-title:hover::after {
        width: 90px;
    }

    /* Stat Highlight Counter Pill */
    .stat-pill {
        background: rgba(255, 255, 255, 0.85);
        border: 1px solid rgba(62, 39, 35, 0.1);
        backdrop-filter: blur(10px);
        border-radius: 1.25rem;
        padding: 1.25rem 1.75rem;
        box-shadow: 0 8px 24px rgba(62, 39, 35, 0.04);
        transition: transform 0.25s ease;
    }

    .stat-pill:hover {
        transform: translateY(-3px);
    }

    /* Fade-in Animation Utilities */
    .fade-up {
        animation: fadeUp 0.9s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        opacity: 0;
        transform: translateY(28px);
    }
    .delay-1 { animation-delay: 0.15s; }
    .delay-2 { animation-delay: 0.3s; }
    .delay-3 { animation-delay: 0.45s; }

    @keyframes fadeUp {
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<!-- Hero Section -->
<section class="hero-section">
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
    
    <div class="container text-center hero-content fade-up">
        <div class="d-inline-flex align-items-center px-4 py-2 rounded-pill bg-white shadow-sm border border-secondary border-opacity-10 text-primary fw-bold mb-4" style="font-size: 0.88rem; font-family: 'Inter', sans-serif;">
            <i class="bi bi-stars text-warning me-2 fs-5"></i> Cita Rasa Nusantara & Pemesanan QR Code Cerdas
        </div>
        
        <h1 class="display-3 fw-bold mb-4" style="font-family: 'Playfair Display', serif; color: #3e2723; line-height: 1.15;">
            Kehangatan Malam,<br>
            <span style="color: var(--color-accent); font-style: italic;">Kesederhanaan Rasa</span>
        </h1>
        
        <p class="fs-5 text-muted mx-auto mb-5 delay-1" style="max-width: 640px; font-family: 'Inter', sans-serif; line-height: 1.75;">
            Nikmati aroma kopi jahe hangat, aneka sate bakar autentik, dan atmosfer tanpa sekat. Silakan duduk, pesan dari meja Anda, dan biarkan cerita mengalir alami.
        </p>

        <div class="d-flex flex-column flex-sm-row justify-content-center align-items-center gap-3 fade-up delay-2">
            <a href="/katalog" class="btn btn-brand-primary btn-lg px-5 py-3 rounded-pill fw-bold" style="font-family: 'Inter', sans-serif;">
                <i class="bi bi-grid-fill me-2"></i> Jelajahi Menu Kami
            </a>
            <a href="/lokasi" class="btn btn-brand-outline btn-lg px-4 py-3 rounded-pill fw-bold" style="font-family: 'Inter', sans-serif;">
                <i class="bi bi-geo-alt-fill me-2"></i> Lokasi Warung
            </a>
        </div>
    </div>
</section>

<!-- Stats Highlights Bar -->
<section class="position-relative z-3" style="margin-top: -3rem;">
    <div class="container">
        <div class="row g-3 justify-content-center">
            <div class="col-6 col-md-3">
                <div class="stat-pill text-center">
                    <h3 class="fw-bold text-primary mb-1 font-sans">30+</h3>
                    <small class="text-muted font-sans fw-semibold">Varian Sate & Nasi</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-pill text-center">
                    <h3 class="fw-bold text-primary mb-1 font-sans">100%</h3>
                    <small class="text-muted font-sans fw-semibold">Bahan Segar & Halal</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-pill text-center">
                    <h3 class="fw-bold text-primary mb-1 font-sans">Instant</h3>
                    <small class="text-muted font-sans fw-semibold">Self-Ordering QR Code</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-pill text-center">
                    <h3 class="fw-bold text-primary mb-1 font-sans">PWA</h3>
                    <small class="text-muted font-sans fw-semibold">Siap Melayani Offline</small>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Values & Highlights Section (Redesigned Glassmorphism Cards) -->
<section class="py-5 my-4">
    <div class="container">
        <div class="text-center mb-5 fade-up">
            <h2 class="section-title display-5 fw-bold mb-3">Kenapa Memilih Kami?</h2>
            <p class="text-muted font-sans fs-6 mx-auto mt-4" style="max-width: 540px;">
                Komitmen kami menyajikan esensi kuliner angkringan autentik dengan sentuhan teknologi modern.
            </p>
        </div>

        <div class="row g-4 justify-content-center">
            
            <!-- Card 1: Harga Bersahabat -->
            <div class="col-lg-4 col-md-6 fade-up delay-1">
                <div class="glass-feature-card p-4 p-xl-5 h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-start mb-4">
                            <div class="icon-badge">
                                <i class="bi bi-wallet2 fs-2"></i>
                            </div>
                            <span class="badge rounded-pill bg-light text-muted border px-3 py-2 font-sans fw-bold">01</span>
                        </div>
                        <h4 class="fw-bold mb-3" style="font-family: 'Playfair Display', Georgia, serif; color: #3e2723;">
                            Harga Bersahabat
                        </h4>
                        <p class="text-muted mb-0 font-sans" style="line-height: 1.7; font-size: 0.95rem;">
                            Cita rasa bintang lima dengan pilihan harga yang jujur dan bersahabat. Semua kalangan dapat menikmati hidangan lezat tanpa beban.
                        </p>
                    </div>
                    <div class="mt-4 pt-3 border-top border-secondary border-opacity-10 d-flex align-items-center justify-content-between">
                        <span class="small font-sans fw-semibold text-primary"><i class="bi bi-check-circle-fill text-success me-1"></i> Ramah Kantong</span>
                        <i class="bi bi-arrow-right text-muted"></i>
                    </div>
                </div>
            </div>

            <!-- Card 2: Ruang Tanpa Sekat -->
            <div class="col-lg-4 col-md-6 fade-up delay-2">
                <div class="glass-feature-card p-4 p-xl-5 h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-start mb-4">
                            <div class="icon-badge" style="background: linear-gradient(135deg, var(--color-accent), var(--color-primary));">
                                <i class="bi bi-people-fill fs-2"></i>
                            </div>
                            <span class="badge rounded-pill bg-light text-muted border px-3 py-2 font-sans fw-bold">02</span>
                        </div>
                        <h4 class="fw-bold mb-3" style="font-family: 'Playfair Display', Georgia, serif; color: #3e2723;">
                            Ruang Tanpa Sekat
                        </h4>
                        <p class="text-muted mb-0 font-sans" style="line-height: 1.7; font-size: 0.95rem;">
                            Tempat bertemunya beragam cerita. Tanpa sekat status sosial, siapapun bisa duduk berdampingan dan menikmati suasana hangat malam.
                        </p>
                    </div>
                    <div class="mt-4 pt-3 border-top border-secondary border-opacity-10 d-flex align-items-center justify-content-between">
                        <span class="small font-sans fw-semibold text-primary"><i class="bi bi-check-circle-fill text-success me-1"></i> Suasana Inklusif</span>
                        <i class="bi bi-arrow-right text-muted"></i>
                    </div>
                </div>
            </div>

            <!-- Card 3: Pemesanan Cerdas -->
            <div class="col-lg-4 col-md-6 fade-up delay-3">
                <div class="glass-feature-card p-4 p-xl-5 h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-start mb-4">
                            <div class="icon-badge">
                                <i class="bi bi-qr-code-scan fs-2"></i>
                            </div>
                            <span class="badge rounded-pill bg-light text-muted border px-3 py-2 font-sans fw-bold">03</span>
                        </div>
                        <h4 class="fw-bold mb-3" style="font-family: 'Playfair Display', Georgia, serif; color: #3e2723;">
                            Pemesanan Cerdas
                        </h4>
                        <p class="text-muted mb-0 font-sans" style="line-height: 1.7; font-size: 0.95rem;">
                            Rasakan kemudahan pesan langsung dari meja Anda via Scan QR Code. Praktis, cepat, dan didukung teknologi PWA Offline-First.
                        </p>
                    </div>
                    <div class="mt-4 pt-3 border-top border-secondary border-opacity-10 d-flex align-items-center justify-content-between">
                        <span class="small font-sans fw-semibold text-primary"><i class="bi bi-check-circle-fill text-success me-1"></i> Scan & Enjoy</span>
                        <i class="bi bi-arrow-right text-muted"></i>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- Story / Heritage Section -->
<section class="py-5 my-4">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-5 position-relative fade-up">
                <div class="rounded-4 overflow-hidden shadow-lg position-relative d-flex align-items-center justify-content-center p-4" style="aspect-ratio: 4/5; background: linear-gradient(135deg, #5d4037 0%, #3e2723 50%, #2d1a11 100%);">
                    
                    <!-- Floating Ambient Circles -->
                    <div class="position-absolute rounded-circle" style="width: 180px; height: 180px; background: rgba(212, 155, 120, 0.15); top: 8%; left: 8%; animation: blobFloat 7s ease-in-out infinite;"></div>
                    <div class="position-absolute rounded-circle" style="width: 220px; height: 220px; background: rgba(176, 89, 35, 0.12); bottom: 12%; right: -5%; animation: blobFloat 9s ease-in-out infinite reverse;"></div>
                    
                    <!-- Center Badge Icon -->
                    <div class="text-center position-relative z-1">
                        <div class="d-inline-flex p-4 rounded-circle mb-3" style="background: rgba(255,255,255,0.12); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.25); box-shadow: 0 12px 36px rgba(0,0,0,0.25);">
                            <i class="bi bi-shop text-white" style="font-size: 3.8rem; filter: drop-shadow(0 4px 8px rgba(0,0,0,0.3));"></i>
                        </div>
                        <h3 class="text-white fw-bold mt-2" style="font-family: 'Playfair Display', serif; text-shadow: 0 2px 4px rgba(0,0,0,0.4);">Ruang Hangat Malam</h3>
                    </div>

                    <!-- Overlay Glass Quote Card -->
                    <div class="position-absolute bottom-0 start-0 m-4 p-4 rounded-4" style="background: rgba(255,255,255,0.88); backdrop-filter: blur(14px); width: calc(100% - 2rem); border: 1px solid rgba(255,255,255,0.5); box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
                        <p class="mb-0 fw-bold text-primary fst-italic text-center" style="font-size: 1.05rem; font-family: 'Playfair Display', serif;">
                            "Lebih dari sekadar makan, ini tentang titik temu & cerita manusia."
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-lg-7 fade-up delay-2">
                <div class="ps-lg-3">
                    <span class="text-uppercase tracking-wider fw-bold text-muted small font-sans d-block mb-2">Cerita Kami</span>
                    <h2 class="section-title display-5 fw-bold mb-4">Awal Perjalanan Angkringan</h2>
                    
                    <p class="text-muted lh-lg mb-4 fs-6 font-sans" style="text-align: justify;">
                        Di tengah rutinitas harian yang padat, kami menyadari betapa pentingnya ruang sederhana untuk beristirahat dan melepas penat. Angkringan ini hadir bukan sekadar tempat makan, melainkan ruang pelarian hangat bagi siapapun.
                    </p>
                    <p class="text-muted lh-lg mb-4 fs-6 font-sans" style="text-align: justify;">
                        Berawal dari keinginan menyajikan kehangatan nasi kucing gurih, aneka sate bakar dengan racikan bumbu khas, dan kepulan teh atau kopi jahe yang menenangkan. Di sini, kepenatan berganti menjadi tawa dan pertemanan baru.
                    </p>

                    <div class="pt-3">
                        <a href="/lokasi" class="btn btn-brand-primary px-4 py-3 rounded-pill fw-bold font-sans">
                            <i class="bi bi-geo-alt-fill me-2"></i> Temukan Lokasi Warung Kami
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection