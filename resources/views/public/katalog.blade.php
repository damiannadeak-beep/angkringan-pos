@extends('layouts.app')

@section('content')
<!-- Header Banner -->
<div class="bg-dark text-white pt-5 pb-4 mb-5 position-relative overflow-hidden rounded-bottom-4 shadow-sm">
    <div class="position-absolute top-0 start-0 w-100 h-100" style="background: radial-gradient(circle at top right, rgba(13,110,253,0.3) 0%, transparent 60%); pointer-events: none;"></div>
    <div class="container text-center position-relative z-index-1">
        <h1 class="display-5 fw-bold mb-3">Katalog Menu Kami</h1>
        <p class="fs-5 text-light opacity-75 mx-auto mb-4" style="max-width: 600px;">
            Jelajahi berbagai pilihan menu lezat kami. Datang, duduk manis, dan <strong>Scan QR Code</strong> di meja Anda untuk memesan.
        </p>
        
        @guest
            <div class="d-flex justify-content-center gap-3 mb-3">
                <a href="/login" class="btn btn-primary fw-bold px-4 rounded-pill shadow-sm">Login / Masuk</a>
                <a href="/register" class="btn btn-outline-light fw-bold px-4 rounded-pill">Daftar Akun</a>
            </div>
            <p class="small text-light opacity-75">
                <i class="bi bi-info-circle me-1"></i> Silakan <strong>Login</strong> atau <strong>Daftar</strong> terlebih dahulu agar bisa melakukan pemesanan secara mandiri.
            </p>
        @else
            @role('konsumen')
                <div class="d-flex justify-content-center">
                    <a href="/konsumen/pilih-tipe" class="btn btn-primary fw-bold px-5 rounded-pill shadow-sm btn-lg">
                        <i class="bi bi-cart-plus me-2"></i> Mulai Pesan
                    </a>
                </div>
            @endrole
        @endguest
    </div>
</div>

<div class="container mb-5 pb-5">
    
    <!-- Filter Kategori -->
    <div class="d-flex justify-content-center mb-5">
        <div class="bg-light rounded-pill p-1 shadow-sm border d-inline-flex" role="group">
            <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold filter-btn" data-filter="semua">Semua</button>
            <button type="button" class="btn btn-light rounded-pill px-4 fw-bold text-muted filter-btn" data-filter="makanan">Makanan</button>
            <button type="button" class="btn btn-light rounded-pill px-4 fw-bold text-muted filter-btn" data-filter="minuman">Minuman</button>
        </div>
    </div>

    <!-- Daftar Menu -->
    <div class="row g-4" id="menu-container">
        @forelse($menus as $menu)
        <div class="col-6 col-md-4 col-lg-3 menu-item" data-kategori="{{ strtolower($menu->kategori ?? 'makanan') }}">
            <div class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden hover-lift bg-white">
                <div class="position-relative">
                    <!-- Gambar -->
                    @if($menu->image)
                        <div class="ratio ratio-4x3 bg-light">
                            <img src="{{ asset('storage/'.$menu->image) }}" alt="{{ $menu->nama_menu }}" style="object-fit: cover; width: 100%; height: 100%;">
                        </div>
                    @else
                        <div class="ratio ratio-4x3 bg-light d-flex align-items-center justify-content-center text-secondary">
                            <div class="text-center w-100 pt-5">
                                <i class="bi bi-image fs-1 opacity-50"></i>
                            </div>
                        </div>
                    @endif
                    
                    <!-- Overlay Kategori -->
                    <div class="position-absolute top-0 start-0 m-2">
                        @if(strtolower($menu->kategori) === 'minuman')
                            <span class="badge bg-info bg-opacity-75 text-white backdrop-blur rounded-pill border border-info border-opacity-25 px-2 py-1"><i class="bi bi-cup-straw"></i> Minuman</span>
                        @else
                            <span class="badge bg-warning bg-opacity-75 text-dark backdrop-blur rounded-pill border border-warning border-opacity-25 px-2 py-1"><i class="bi bi-egg-fried"></i> Makanan</span>
                        @endif
                    </div>
                </div>
                
                <div class="card-body p-3 p-md-4 d-flex flex-column">
                    <h5 class="fw-bold mb-1 text-dark">{{ $menu->nama_menu }}</h5>
                    <div class="mb-2">
                        <span class="text-primary fw-bold fs-5">Rp {{ number_format($menu->harga, 0, ',', '.') }}</span>
                    </div>
                    <p class="text-muted small flex-grow-1 mb-3" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                        {{ $menu->deskripsi ?? 'Nikmati kelezatan tiada tara dari hidangan ini.' }}
                    </p>
                    
                    <div class="mt-auto">
                        @if($menu->stok > 0)
                            <div class="bg-success bg-opacity-10 text-success text-center py-2 rounded-3 fw-bold small">
                                <i class="bi bi-check-circle me-1"></i> Tersedia
                            </div>
                        @else
                            <div class="bg-danger bg-opacity-10 text-danger text-center py-2 rounded-3 fw-bold small">
                                <i class="bi bi-x-circle me-1"></i> Stok Habis
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <div class="d-inline-block bg-light p-4 rounded-circle mb-3">
                <i class="bi bi-basket text-muted" style="font-size: 3rem;"></i>
            </div>
            <h5 class="text-muted fw-bold">Katalog Kosong</h5>
            <p class="text-muted">Menu belum tersedia saat ini. Silakan kembali lagi nanti.</p>
        </div>
        @endforelse
    </div>
</div>

<style>
    .hover-lift {
        transition: transform 0.25s ease-in-out, box-shadow 0.25s ease-in-out;
    }
    .hover-lift:hover {
        transform: translateY(-8px);
        box-shadow: 0 1rem 3rem rgba(0,0,0,.15)!important;
    }
    .backdrop-blur {
        backdrop-filter: blur(4px);
    }
    .ratio-4x3 {
        --bs-aspect-ratio: 75%;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const filterBtns = document.querySelectorAll('.filter-btn');
        const menuItems = document.querySelectorAll('.menu-item');

        filterBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                // Update active state of buttons
                filterBtns.forEach(b => {
                    b.classList.remove('btn-primary');
                    b.classList.add('btn-light', 'text-muted');
                });
                this.classList.remove('btn-light', 'text-muted');
                this.classList.add('btn-primary');

                // Filter items
                const target = this.getAttribute('data-filter');
                
                menuItems.forEach(item => {
                    if (target === 'semua' || item.getAttribute('data-kategori') === target) {
                        item.style.display = 'block';
                        // Add a small animation effect
                        item.animate([
                            { opacity: 0, transform: 'scale(0.95)' },
                            { opacity: 1, transform: 'scale(1)' }
                        ], {
                            duration: 300,
                            easing: 'ease-out'
                        });
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        });
    });
</script>
@endsection