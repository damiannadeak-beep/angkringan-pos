@extends('layouts.app')

@section('content')
<style>
    /* Modern Aesthetic Custom Styles */
    :root {
        --color-primary: #5d4037;
        --color-accent: #d49b78;
        --glass-bg: rgba(255, 255, 255, 0.85);
        --glass-border: rgba(255, 255, 255, 0.3);
    }
    
    body {
        background-color: #fdfbf7;
        overflow-x: hidden;
    }

    /* Animated Gradient Background */
    .hero-section {
        position: relative;
        padding: 8rem 0 6rem 0;
        background: linear-gradient(-45deg, #f0e9dd, #e6ddcf, #fdfbf7, #d7ccc8);
        background-size: 400% 400%;
        animation: gradientBG 15s ease infinite;
        overflow: hidden;
        border-bottom-left-radius: 3rem;
        border-bottom-right-radius: 3rem;
        box-shadow: 0 10px 30px rgba(93, 64, 55, 0.05);
    }

    @keyframes gradientBG {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }

    /* Floating blobs for depth */
    .blob {
        position: absolute;
        filter: blur(60px);
        z-index: 0;
        opacity: 0.6;
        animation: float 10s ease-in-out infinite;
    }
    .blob-1 { top: -10%; left: -10%; width: 400px; height: 400px; background: #e6ddcf; animation-delay: 0s; }
    .blob-2 { bottom: -20%; right: -10%; width: 500px; height: 500px; background: #d7ccc8; animation-delay: -5s; }

    @keyframes float {
        0% { transform: translate(0, 0) scale(1); }
        33% { transform: translate(30px, -50px) scale(1.1); }
        66% { transform: translate(-20px, 20px) scale(0.9); }
        100% { transform: translate(0, 0) scale(1); }
    }

    .hero-content {
        position: relative;
        z-index: 2;
    }

    .glass-card {
        background: var(--glass-bg);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid var(--glass-border);
        border-radius: 1.5rem;
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    .glass-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 45px rgba(93, 64, 55, 0.15);
    }

    .icon-wrapper {
        width: 80px;
        height: 80px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--color-primary), #3e2723);
        color: white;
        margin-bottom: 1.5rem;
        box-shadow: 0 10px 20px rgba(93, 64, 55, 0.2);
        transition: transform 0.3s ease;
    }

    .glass-card:hover .icon-wrapper {
        transform: scale(1.1) rotate(5deg);
    }

    .btn-glow {
        background: linear-gradient(135deg, var(--color-primary), #3e2723);
        color: white;
        border: none;
        position: relative;
        z-index: 1;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .btn-glow::after {
        content: '';
        position: absolute;
        bottom: 0; left: 0; width: 100%; height: 100%;
        background: linear-gradient(to right, transparent, rgba(255,255,255,0.2), transparent);
        transform: translateX(-100%);
        transition: transform 0.6s ease;
        z-index: -1;
    }

    .btn-glow:hover::after {
        transform: translateX(100%);
    }

    .btn-glow:hover {
        color: white;
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(93, 64, 55, 0.3);
    }

    .section-title {
        font-family: 'Playfair Display', serif;
        position: relative;
        display: inline-block;
        color: var(--color-primary);
    }
    
    .section-title::after {
        content: '';
        position: absolute;
        width: 40px;
        height: 3px;
        background: var(--color-accent);
        bottom: -10px;
        left: 50%;
        transform: translateX(-50%);
        border-radius: 2px;
        transition: width 0.3s ease;
    }

    .section-title:hover::after {
        width: 80px;
    }

    /* Fade-in Animation Classes */
    .fade-up {
        animation: fadeUp 1s ease forwards;
        opacity: 0;
        transform: translateY(30px);
    }
    .delay-1 { animation-delay: 0.2s; }
    .delay-2 { animation-delay: 0.4s; }
    .delay-3 { animation-delay: 0.6s; }

    @keyframes fadeUp {
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<!-- Hero Section -->
<section class="hero-section">
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
    
    <div class="container text-center hero-content fade-up">
        <div class="d-inline-flex px-4 py-2 rounded-pill bg-white shadow-sm text-primary fw-bold mb-4" style="font-size: 0.9rem;">
            <i class="bi bi-stars text-warning me-2"></i> Pengalaman Kuliner Berkesan
        </div>
        <h1 class="display-3 fw-bold mb-4" style="font-family: 'Playfair Display', serif; color: #3e2723;">
            Kehangatan Malam,<br>
            <span style="color: var(--color-accent);">Kesederhanaan Rasa</span>
        </h1>
        <p class="fs-5 text-muted mx-auto mb-5 delay-1" style="max-width: 600px; font-family: 'Inter', sans-serif;">
            Tempat di mana rasa rindu akan kesederhanaan terobati. Silakan duduk, nikmati hidangan kami, dan biarkan kehangatan malam mengalir tanpa sekat.
        </p>
        <div class="d-flex justify-content-center gap-3 fade-up delay-2">
            <a href="/katalog" class="btn btn-glow btn-lg px-5 py-3 rounded-pill fw-bold shadow-lg">
                Lihat Menu Kami
            </a>
        </div>
    </div>
</section>

<!-- Values Section (Glassmorphism) -->
<section class="py-5" style="margin-top: -3rem; position: relative; z-index: 10;">
    <div class="container">
        <div class="row g-4 justify-content-center">
            
            <div class="col-lg-4 col-md-6 fade-up delay-1">
                <div class="glass-card p-5 h-100 text-center">
                    <div class="icon-wrapper">
                        <i class="bi bi-wallet2 fs-2"></i>
                    </div>
                    <h4 class="fw-bold mb-3" style="font-family: 'Playfair Display', serif; color: #3e2723;">Ramah di Kantong</h4>
                    <p class="text-muted mb-0">Harga mahasiswa, kualitas bintang lima. Kami memastikan semua kalangan bisa menikmati hidangan lezat tanpa beban.</p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 fade-up delay-2">
                <div class="glass-card p-5 h-100 text-center">
                    <div class="icon-wrapper" style="background: linear-gradient(135deg, var(--color-accent), #B05923);">
                        <i class="bi bi-people fs-2"></i>
                    </div>
                    <h4 class="fw-bold mb-3" style="font-family: 'Playfair Display', serif; color: #3e2723;">Ruang Inklusif</h4>
                    <p class="text-muted mb-0">Tidak ada sekat pembatas. Siapapun bisa duduk berdampingan, mengukir cerita, dan menjalin relasi hangat.</p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 fade-up delay-3">
                <div class="glass-card p-5 h-100 text-center">
                    <div class="icon-wrapper">
                        <i class="bi bi-phone-vibrate fs-2"></i>
                    </div>
                    <h4 class="fw-bold mb-3" style="font-family: 'Playfair Display', serif; color: #3e2723;">Inovasi Digital</h4>
                    <p class="text-muted mb-0">Rasakan esensi tradisional yang berpadu dengan kemudahan teknologi sistem pemesanan cerdas kami.</p>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- Story Section -->
<section class="py-5 my-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-5 mb-5 mb-lg-0 position-relative fade-up">
                <!-- Pure CSS Attractive Design (No Image Required) -->
                <div class="rounded-4 overflow-hidden shadow-lg position-relative d-flex align-items-center justify-content-center" style="aspect-ratio: 4/5; background: linear-gradient(-45deg, #5d4037, #8d6e63, #d49b78, #bcaaa4); background-size: 400% 400%; animation: gradientBG 15s ease infinite;">
                    
                    <!-- Decorative Floating Elements -->
                    <div class="position-absolute rounded-circle" style="width: 150px; height: 150px; background: rgba(255,255,255,0.1); top: 10%; left: 10%; animation: float 6s ease-in-out infinite;"></div>
                    <div class="position-absolute rounded-circle" style="width: 200px; height: 200px; background: rgba(255,255,255,0.15); bottom: 20%; right: -10%; animation: float 8s ease-in-out infinite reverse;"></div>
                    
                    <!-- Center Icon -->
                    <div class="text-center position-relative z-1">
                        <div class="d-inline-flex p-4 rounded-circle mb-3" style="background: rgba(255,255,255,0.2); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.3); box-shadow: 0 8px 32px rgba(0,0,0,0.1);">
                            <i class="bi bi-shop text-white" style="font-size: 4rem; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.2));"></i>
                        </div>
                        <h3 class="text-white fw-bold" style="font-family: 'Playfair Display', serif; text-shadow: 0 2px 4px rgba(0,0,0,0.3);">Ruang Hangat</h3>
                    </div>

                    <!-- Overlay glass box -->
                    <div class="position-absolute bottom-0 start-0 m-4 p-4 rounded-4" style="background: rgba(255,255,255,0.85); backdrop-filter: blur(12px); width: calc(100% - 2rem); border: 1px solid rgba(255,255,255,0.4); border-top: 2px solid rgba(255,255,255,0.8);">
                        <p class="mb-0 fw-bold text-primary fst-italic text-center" style="font-size: 1.1rem;">"Lebih dari sekadar makan, ini tentang titik temu manusia."</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 offset-lg-1 fade-up delay-2">
                <h2 class="section-title mb-5 display-5">Awal Perjalanan Kami</h2>
                <p class="text-muted lh-lg mb-4 fs-5" style="text-align: justify; font-family: 'Inter', sans-serif;">
                    Berada di lingkungan yang padat, kami menyadari hiruk-pikuk rutinitas sering membuat lelah. Angkringan ini hadir sebagai pelarian manis untuk meredakan penat.
                </p>
                <p class="text-muted lh-lg mb-4 fs-5" style="text-align: justify; font-family: 'Inter', sans-serif;">
                    Sebuah gerobak sederhana yang kami ubah menjadi <strong>"titik temu"</strong>. Jabatan dan status sosial menguap di sini, tergantikan oleh tawa ringan dan cerita keseharian ditemani kepulan kopi jahe dan nikmatnya nasi kucing.
                </p>
                <div class="d-flex align-items-center gap-3 mt-4">
                    <a href="/lokasi" class="text-decoration-none fw-bold" style="color: var(--color-accent); font-family: 'Inter', sans-serif;">
                        Lihat Lokasi Kami <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection