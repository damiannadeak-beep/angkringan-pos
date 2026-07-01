<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Warung Angkringan</title>
    
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="apple-touch-icon" href="{{ asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap" rel="stylesheet"></noscript>
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"></noscript>

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <style>
        /* Anti-lag touch */
        button, a, input, select, textarea {
            touch-action: manipulation;
        }
        
        .btn-angkringan {
            background-color: #5d4037 !important;
            color: #ffffff !important;
            border: none;
        }
        .btn-angkringan:hover {
            background-color: #4e342e !important;
            color: #ffffff !important;
        }
        .text-angkringan {
            color: #5d4037 !important;
        }
        .bg-angkringan {
            background-color: #5d4037 !important;
            color: #ffffff !important;
        }
    </style>
</head>
<body class="bg-light">
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-light border-bottom border-secondary shadow-sm sticky-top">
            <div class="container">
                <a class="navbar-brand fw-bold text-primary" href="{{ url('/') }}">
                    <i class="bi bi-shop me-1"></i> Warung Angkringan
                </a>
                
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0 fw-semibold">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('/') ? 'active text-primary' : '' }}" href="/">Beranda</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('katalog') ? 'active text-primary' : '' }}" href="/katalog">Katalog Menu</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('lokasi') ? 'active text-primary' : '' }}" href="/lokasi">Lokasi</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('kontak') ? 'active text-primary' : '' }}" href="/kontak">Kontak</a>
                        </li>
                    </ul>

                    <ul class="navbar-nav ms-auto">
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">Masuk</a>
                                </li>
                            @endif
                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="btn btn-primary rounded-pill px-3 ms-2 fw-bold" href="{{ route('register') }}">Daftar</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle fw-bold text-dark d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    @if(Auth::user()->foto)
                                        <img src="{{ asset('uploads/profil/' . Auth::user()->foto) }}" alt="Foto" class="rounded-circle me-2" style="width: 25px; height: 25px; object-fit: cover;">
                                    @else
                                        <i class="bi bi-person-circle text-primary me-2 fs-5"></i>
                                    @endif
                                    {{ Auth::user()->name }}
                                </a>

                                <div class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2" aria-labelledby="navbarDropdown">
                                    
                                    @role('pemilik')
                                        <a class="dropdown-item" href="/admin/dashboard"><i class="bi bi-speedometer2 me-2"></i> Dashboard Admin</a>
                                    @endrole

                                    @role('kasir')
                                        <a class="dropdown-item" href="/kasir/pos"><i class="bi bi-calculator me-2"></i> Mesin POS Kasir</a>
                                    @endrole

                                    @role('konsumen')
                                        <a class="dropdown-item" href="/konsumen/profil"><i class="bi bi-person-lines-fill me-2"></i> Profil & Pesanan Saya</a>
                                    @endrole
                                    
                                    <hr class="dropdown-divider">

                                    <a class="dropdown-item text-danger" href="{{ route('logout') }}"
                                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        <i class="bi bi-box-arrow-right me-2"></i> Keluar
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        @if(isset($isStoreOpen) && !$isStoreOpen && (!auth()->check() || auth()->user()->hasRole('konsumen')))
            <div class="alert alert-warning text-center rounded-0 mb-0 border-0 shadow-sm px-3" style="z-index: 1040; position: relative;">
                <i class="bi bi-info-circle-fill me-1"></i>
                <strong>Perhatian:</strong> Warung saat ini belum buka. Anda tetap dapat melihat menu & memesan, namun pesanan Anda akan diproses setelah kasir kami tiba.
            </div>
        @endif

        <main class="py-0">
            @yield('content')
        </main>
        
        <footer class="text-center py-4 mt-auto w-100" style="padding-bottom: env(safe-area-inset-bottom, 120px) !important;">
            <small class="text-muted">&copy; {{ date('Y') }} Warung Angkringan. Hak Cipta Dilindungi.</small>
        </footer>
    </div>
    
    @yield('scripts')
    @include('components.webpush')


</body>
</html>

