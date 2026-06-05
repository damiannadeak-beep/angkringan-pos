@extends('layouts.app')

@section('content')
<!-- Hero Section -->
<div class="bg-primary bg-opacity-10 py-5 mb-5 rounded-bottom-4 shadow-sm">
    <div class="container py-5 text-center">
        <h1 class="display-4 fw-bold text-primary mb-3">Cerita di Balik Gerobak Kami</h1>
        <p class="fs-5 text-muted mx-auto" style="max-width: 700px;">
            Lebih dari sekadar tempat makan, ini adalah ruang hangat untuk berbagi cerita, tawa, dan kenangan—dibangun dari semangat kebersamaan.
        </p>
    </div>
</div>

<div class="container mb-5 pb-4">
    <!-- Story Section -->
    <div class="row align-items-center mb-5 pb-5">
        <div class="col-lg-6 mb-4 mb-lg-0 text-center position-relative">
            <!-- Decorative Elements -->
            <div class="position-absolute top-0 start-0 translate-middle-y bg-warning rounded-circle" style="width: 100px; height: 100px; opacity: 0.2; z-index: -1;"></div>
            <div class="position-absolute bottom-0 end-0 bg-success rounded-circle" style="width: 150px; height: 150px; opacity: 0.1; z-index: -1; transform: translate(20%, 20%);"></div>
            
            <div class="bg-white p-5 rounded-4 shadow border border-light" style="position: relative; z-index: 1;">
                <i class="bi bi-shop text-primary mb-3 d-inline-block" style="font-size: 5rem;"></i>
                <h3 class="fw-bold text-dark">Awal Mula Perjalanan</h3>
            </div>
        </div>
        <div class="col-lg-6 ps-lg-5">
            <h3 class="fw-bold mb-4 text-dark">Berawal dari Obrolan Sederhana</h3>
            <p class="text-muted lh-lg mb-4" style="text-align: justify;">
                Pembuatan angkringan ini bermula dari kerinduan kami akan sebuah tempat nongkrong yang sederhana, merakyat, namun tetap nyaman. Berada di lingkungan kampus dan perumahan, kami melihat banyak mahasiswa dan warga yang membutuhkan tempat untuk melepas penat setelah seharian beraktivitas, tanpa harus mengeluarkan biaya yang mahal.
            </p>
            <p class="text-muted lh-lg mb-4" style="text-align: justify;">
                Gerobak angkringan kami desain tidak hanya sebagai tempat berjualan nasi kucing dan sate-satean, tetapi sebagai "titik temu". Sebuah ruang di mana perbedaan status sosial dilebur oleh segelas es teh dan hangatnya seduhan kopi jahe.
            </p>
            <div class="d-inline-block bg-light p-3 rounded-3 border-start border-4 border-primary shadow-sm">
                <p class="mb-0 fst-italic text-secondary">
                    "Membawa cita rasa tradisional ke dalam balutan teknologi modern, demi kenyamanan setiap pengunjung."
                </p>
            </div>
        </div>
    </div>

    <!-- Core Values Section -->
    <div class="row g-4 mt-2">
        <div class="col-12 text-center mb-4">
            <h2 class="fw-bold">Nilai yang Kami Pegang</h2>
            <div class="mx-auto bg-primary mt-2 rounded" style="width: 60px; height: 4px;"></div>
        </div>
        
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm rounded-4 text-center p-4 hover-lift" style="transition: transform 0.3s ease;">
                <div class="card-body">
                    <div class="d-inline-flex bg-success bg-opacity-10 text-success p-4 rounded-circle mb-4">
                        <i class="bi bi-cash-coin fs-1"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Harga Mahasiswa, Rasa Bintang Lima</h5>
                    <p class="text-muted mb-0">Kami berkomitmen menyajikan hidangan berkualitas yang ramah di kantong, memastikan semua kalangan bisa menikmati santapan sedap tanpa beban.</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm rounded-4 text-center p-4 hover-lift" style="transition: transform 0.3s ease;">
                <div class="card-body">
                    <div class="d-inline-flex bg-warning bg-opacity-10 text-warning p-4 rounded-circle mb-4">
                        <i class="bi bi-people-fill fs-1"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Suasana Kekeluargaan</h5>
                    <p class="text-muted mb-0">Tidak ada sekat pembatas. Di sini, siapapun bisa duduk berdampingan, ngobrol santai, dan menjalin relasi baru di bawah temaramnya malam.</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm rounded-4 text-center p-4 hover-lift" style="transition: transform 0.3s ease;">
                <div class="card-body">
                    <div class="d-inline-flex bg-primary bg-opacity-10 text-primary p-4 rounded-circle mb-4">
                        <i class="bi bi-phone-vibrate fs-1"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Tradisi Bertemu Inovasi</h5>
                    <p class="text-muted mb-0">Mempertahankan esensi angkringan klasik, namun ditingkatkan dengan sistem pemesanan cerdas (POS & QR Code) agar pelayanan lebih cepat dan akurat.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Call to Action -->
    <div class="row mt-5 pt-5">
        <div class="col-12">
            <div class="bg-dark text-white rounded-4 p-5 text-center shadow-lg position-relative overflow-hidden">
                <div class="position-absolute top-0 start-0 w-100 h-100" style="background: radial-gradient(circle at top right, rgba(13,110,253,0.2) 0%, transparent 60%); pointer-events: none;"></div>
                <h3 class="fw-bold mb-3 position-relative z-index-1">Siap Mencicipi Hidangan Kami?</h3>
                <p class="text-light mb-4 opacity-75 mx-auto position-relative z-index-1" style="max-width: 500px;">
                    Jangan biarkan rasa penasaran Anda berlalu. Jelajahi berbagai macam menu sate, nasi kucing, dan minuman hangat kami sekarang juga.
                </p>
                <a href="/katalog" class="btn btn-primary btn-lg fw-bold px-5 rounded-pill position-relative z-index-1 shadow">Lihat Katalog Menu</a>
            </div>
        </div>
    </div>
</div>

<style>
    .hover-lift:hover {
        transform: translateY(-10px);
        box-shadow: 0 1rem 3rem rgba(0,0,0,.175)!important;
    }
</style>
@endsection