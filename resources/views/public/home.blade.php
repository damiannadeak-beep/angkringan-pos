@extends('layouts.app')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
    
    body {
        background-color: #fdfbf7;
        font-family: 'Plus Jakarta Sans', sans-serif !important;
        color: #2d1a11;
    }

    /* Minimalist Warm Hero Section */
    .hero-warm {
        padding: 5rem 0 4rem 0;
        background: linear-gradient(180deg, #f0e9dd 0%, #fdfbf7 100%);
        border-bottom: 1px solid #eae3d8;
    }

    .badge-student {
        background: #ffffff;
        color: #3e2723;
        border: 1px solid #d7ccc8;
        font-size: 0.88rem;
        font-weight: 700;
        padding: 0.45rem 1.25rem;
        border-radius: 999px;
        box-shadow: 0 4px 12px rgba(62, 39, 35, 0.04);
    }

    .hero-title-student {
        font-weight: 800;
        font-size: 3rem;
        color: #3e2723;
        line-height: 1.2;
        letter-spacing: -0.02em;
    }

    .hero-sub-student {
        color: #6d584c;
        font-size: 1.1rem;
        line-height: 1.75;
        max-width: 660px;
    }

    /* Action Buttons */
    .btn-student-primary {
        background: #3e2723;
        color: #ffffff !important;
        font-weight: 700;
        padding: 0.9rem 2.25rem;
        border-radius: 999px;
        border: none;
        box-shadow: 0 8px 20px rgba(62, 39, 35, 0.2);
        transition: all 0.2s ease;
    }

    .btn-student-primary:hover {
        background: #2d1a11;
        transform: translateY(-2px);
        box-shadow: 0 12px 28px rgba(62, 39, 35, 0.3);
    }

    .btn-student-outline {
        background: #ffffff;
        color: #3e2723 !important;
        font-weight: 700;
        padding: 0.9rem 2rem;
        border-radius: 999px;
        border: 1.5px solid #d7ccc8;
        transition: all 0.2s ease;
    }

    .btn-student-outline:hover {
        border-color: #3e2723;
        background: #f0e9dd;
        transform: translateY(-2px);
    }

    /* 3 Pilar Feature Cards (No Product Grid) */
    .pillar-card {
        background: #ffffff;
        border: 1px solid #eae3d8;
        border-radius: 1.5rem;
        padding: 2.25rem 1.75rem;
        box-shadow: 0 8px 24px rgba(62, 39, 35, 0.04);
        transition: all 0.25s ease;
    }

    .pillar-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 16px 36px rgba(62, 39, 35, 0.09);
        border-color: #b05923;
    }

    .pillar-icon-box {
        width: 60px;
        height: 60px;
        border-radius: 1.25rem;
        background: linear-gradient(135deg, #3e2723, #5d4037);
        color: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1.25rem;
        box-shadow: 0 8px 20px rgba(62, 39, 35, 0.15);
    }

    /* Minimalist Story Section */
    .story-card-minimal {
        background: #ffffff;
        border: 1px solid #eae3d8;
        border-radius: 1.75rem;
        padding: 2.75rem;
        box-shadow: 0 10px 30px rgba(62, 39, 35, 0.04);
    }
</style>

<!-- Hero Section (Opsi 1: Minimalis Warm Sanctuary - Khusus Mahasiswa) -->
<section class="hero-warm">
    <div class="container text-center">
        <div class="d-inline-block mb-3">
            <span class="badge-student">
                <i class="bi bi-mortarboard-fill me-2 text-warning"></i> Ruang Nongkrong & Nugas Mahasiswa
            </span>
        </div>

        <h1 class="hero-title-student mb-4">
            Ruang Nongkrong & Nugas<br>
            Favorit Mahasiswa
        </h1>

        <p class="hero-sub-student mx-auto mb-5">
            Suasana angkringan yang hangat, harga ramah di kantong mahasiswa, dan kemudahan pesan dari meja via Scan QR Code tanpa memutus obrolan atau konsentrasi nugas.
        </p>

        <div class="d-flex flex-wrap justify-content-center gap-3">
            <a href="/katalog" class="btn btn-student-primary text-decoration-none">
                <i class="bi bi-grid-fill me-2"></i> Jelajahi Katalog Menu
            </a>
            <a href="/lokasi" class="btn btn-student-outline text-decoration-none">
                <i class="bi bi-geo-alt-fill me-2"></i> Lokasi Warung
            </a>
        </div>
    </div>
</section>

<!-- 3 Pilar Pengalaman Mahasiswa (Pengganti Grid Produk) -->
<section class="py-5 my-2">
    <div class="container">
        <div class="text-center mb-5">
            <span class="text-uppercase fw-bold small text-muted">Pengalaman Spesial</span>
            <h2 class="fw-bold mb-0 text-dark">Dibuat Khusus Untuk Mahasiswa</h2>
        </div>

        <div class="row g-4 justify-content-center">
            
            <!-- Pilar 1: Harga Kantong Mahasiswa -->
            <div class="col-lg-4 col-md-6">
                <div class="pillar-card h-100">
                    <div class="pillar-icon-box">
                        <i class="bi bi-wallet2 fs-3"></i>
                    </div>
                    <h4 class="fw-bold text-dark mb-3">Harga Kantong Mahasiswa</h4>
                    <p class="text-muted mb-0" style="line-height: 1.75; font-size: 0.95rem;">
                        Hidangan & minuman racikan berkualitas yang pas dengan uang saku mahasiswa tanpa biaya tersembunyi.
                    </p>
                </div>
            </div>

            <!-- Pilar 2: Pesan Cerdas Tanpa Antre -->
            <div class="col-lg-4 col-md-6">
                <div class="pillar-card h-100">
                    <div class="pillar-icon-box" style="background: linear-gradient(135deg, #b05923, #3e2723);">
                        <i class="bi bi-qr-code-scan fs-3"></i>
                    </div>
                    <h4 class="fw-bold text-dark mb-3">Pesan Cerdas Tanpa Antre</h4>
                    <p class="text-muted mb-0" style="line-height: 1.75; font-size: 0.95rem;">
                        Scan QR Code langsung dari meja. Tetap fokus nugas atau ngobrol bareng teman tanpa perlu berdiri ke kasir.
                    </p>
                </div>
            </div>

            <!-- Pilar 3: Suasana Warm & Nugas Friendly -->
            <div class="col-lg-4 col-md-6">
                <div class="pillar-card h-100">
                    <div class="pillar-icon-box">
                        <i class="bi bi-cup-hot-fill fs-3"></i>
                    </div>
                    <h4 class="fw-bold text-dark mb-3">Suasana Warm & Nugas Friendly</h4>
                    <p class="text-muted mb-0" style="line-height: 1.75; font-size: 0.95rem;">
                        Ruang santai tanpa sekat. Tempat ideal untuk diskusi kelompok, nugas malam, atau sekadar melepas lelah.
                    </p>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- Narasi Latar Belakang Warung & Tujuan Aplikasi untuk Mahasiswa -->
<section class="pb-5 mb-4">
    <div class="container">
        <div class="story-card-minimal">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <span class="badge-student mb-3 d-inline-block">
                        <i class="bi bi-info-circle-fill me-1 text-warning"></i> Latar Belakang & Tujuan
                    </span>
                    <h3 class="fw-bold text-dark mb-3 display-6">Diciptakan Sebagai Ruang Santai Mahasiswa</h3>
                    <p class="text-muted mb-3" style="line-height: 1.8; text-align: justify; font-size: 1rem;">
                        Kami memahami betapa melelahkannya rutinitas kuliah dan tumpukan tugas harian. Angkringan ini didirikan khusus sebagai tempat pelarian yang hangat bagi mahasiswa—tempat di mana Anda bisa menikmati sajian favorit dengan harga bersahabat.
                    </p>
                    <p class="text-muted mb-0" style="line-height: 1.8; text-align: justify; font-size: 1rem;">
                        Melalui sistem pemesanan QR Code cerdas ini, Anda dapat memesan hidangan favorit langsung dari meja tanpa harus memutus obrolan atau mengganggu konsentrasi belajar Anda.
                    </p>
                </div>
                <div class="col-lg-4 text-center">
                    <div class="p-4 rounded-4 bg-light border border-dashed">
                        <i class="bi bi-cup-straw text-primary fs-1 mb-2 d-block opacity-75"></i>
                        <h6 class="fw-bold mb-1 text-dark">Nugas & Nongkrong Santai</h6>
                        <small class="text-muted d-block mb-3">Sistem POS QR Code Cerdas</small>
                        <a href="/katalog" class="btn btn-student-primary btn-sm rounded-pill px-4 fw-bold">
                            Lihat Katalog
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection