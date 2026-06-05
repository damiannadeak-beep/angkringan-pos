<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kasir POS - Angkringan</title>
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <style>
        body { background: #f4e9eb; color: #5b3e45; }
        .kasir-navbar { background: #eac6cc; border-bottom: 1px solid #f0d8dd; }
        .kasir-navbar .navbar-brand { color: #6a3a45; font-weight: 700; }
        .kasir-navbar .navbar-text, .kasir-navbar .nav-link { color: #6a3a45; }
        .kasir-navbar .nav-link:hover { color: #46252c; }
        .kasir-main { padding: 1.5rem 1.25rem; }
        .kasir-container { max-width: 1300px; margin: 0 auto; }
        .kasir-header { background: #fff4f6; border: 1px solid #f1d7dc; border-radius: 1.5rem; }
        .kasir-header h5 { color: #6a3a45; }
        .kasir-card { border-radius: 1.5rem; border: none; box-shadow: 0 20px 40px rgba(151, 103, 118, .08); }
        .kasir-card .card-body { padding: 1.75rem; }
        .menu-card { border: none; border-radius: 1.25rem; background: #fff; transition: transform .2s ease, box-shadow .2s ease; }
        .menu-card:hover { transform: translateY(-4px); box-shadow: 0 18px 32px rgba(131, 72, 88, 0.11); }
        .menu-card .price { color: #a94f64; font-weight: 700; }
        .btn-soft { background: #ffe3e8; border: 1px solid #f3d3d9; color: #7a3a45; }
        .btn-soft:hover { background: #f7d9df; }
        .payment-pill.active { background: #d98b94; color: #fff; border-color: #d98b94; }
        .form-control, .form-select { border-radius: 999px; }
        .cart-list { min-height: 240px; }
        .cart-item { border-bottom: 1px solid #f0d0d6; padding-bottom: .85rem; margin-bottom: .85rem; }
        .cart-item:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
    </style>
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-lg kasir-navbar shadow-sm py-3">
            <div class="container-fluid">
                <a class="navbar-brand d-flex align-items-center" href="{{ route('kasir.pos') }}">
                    <i class="bi bi-shop me-2"></i>
                    Kasir Pos
                </a>
                
                <div class="collapse navbar-collapse d-flex justify-content-center">
                    <ul class="navbar-nav mb-2 mb-lg-0 gap-3">
                        <li class="nav-item">
                            <a class="nav-link fw-bold {{ request()->routeIs('kasir.pos') ? 'active text-primary' : '' }}" href="{{ route('kasir.pos') }}">
                                <i class="bi bi-cart-plus me-1"></i> Transaksi Kasir
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-bold {{ request()->routeIs('kasir.pesanan_aktif') ? 'active text-primary' : '' }}" href="{{ route('kasir.pesanan_aktif') }}">
                                <i class="bi bi-bell me-1"></i> Pesanan Konsumen Aktif
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-bold {{ request()->routeIs('kasir.shift_report') ? 'active text-primary' : '' }}" href="{{ route('kasir.shift_report') }}">
                                <i class="bi bi-calendar-check me-1"></i> Laporan Tutup Shift
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="d-flex align-items-center gap-3">
                    <div class="navbar-text small">{{ auth()->user()->name ?? 'Kasir' }}</div>
                    <a class="btn btn-light btn-sm rounded-pill border" href="{{ route('logout') }}"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="bi bi-box-arrow-right me-1"></i> Logout
                    </a>
                </div>
            </div>
        </nav>

        <main class="kasir-main">
            <div class="kasir-container">
                @yield('content')
            </div>
        </main>
    </div>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
        @csrf
    </form>
</body>
</html>
