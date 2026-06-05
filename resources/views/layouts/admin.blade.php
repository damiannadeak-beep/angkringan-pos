<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard Admin - Angkringan POS</title>
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <style>
        body { background: #f7eff1; }
        .admin-layout { min-height: 100vh; display: flex; flex-direction: column; }
        .admin-topbar { background: #e6c9cb; color: #6a3a45; padding: 1rem 1.5rem; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.08); }
        .admin-topbar .brand { font-weight: 700; font-size: 1.1rem; display: flex; align-items: center; gap: 0.5rem; }
        .admin-topbar .logout-top { background: white; color: #6a3a45; border: none; padding: 0.5rem 1.25rem; border-radius: 0.5rem; cursor: pointer; font-weight: 500; transition: all 0.2s ease; }
        .admin-topbar .logout-top:hover { background: #f8f0f2; }
        .admin-main-wrapper { display: flex; flex: 1; }
        .admin-sidebar { width: 240px; background: #e6c9cb; color: #6a3a45; display: flex; flex-direction: column; padding: 1.5rem; box-shadow: 2px 0 4px rgba(0,0,0,0.08); }
        .admin-sidebar .brand-title { font-weight: 700; font-size: 1.1rem; letter-spacing: 0.03em; color: #6a3a45; }
        .admin-sidebar .brand-subtitle { font-size: 0.85rem; color: #7d555f; }
        .admin-sidebar .nav-link { color: #6a3a45; border-radius: 0.75rem; padding: 0.85rem 1rem; transition: all 0.2s ease; }
        .admin-sidebar .nav-link:hover, .admin-sidebar .nav-link.active { background: rgba(255,255,255,0.95); color: #b34b63; }
        .admin-sidebar .nav-link i { width: 1.25rem; }
        .admin-sidebar .logout-btn { background: #ffffff; color: #6a3a45; border: 1px solid rgba(0,0,0,0.08); border-radius: 1rem; }
        .admin-sidebar .logout-btn:hover { background: #f8f0f2; }
        .admin-content { flex: 1; padding: 2rem; }
        .admin-topbar { background: transparent; border-bottom: none; }
        .admin-card { border-radius: 1.25rem; }
        .card-summary { border: none; border-radius: 1.25rem; }
    </style>
</head>
<body>
    <div id="app" class="admin-layout">
        <div class="admin-topbar">
            <div class="brand">
                <i class="bi bi-shop"></i> Angkringan POS
            </div>
            <div class="d-flex align-items-center gap-3">
                <!-- Notification Bell -->
                <div class="dropdown">
                    <button class="btn btn-link text-dark text-decoration-none position-relative p-0" type="button" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-bell-fill fs-5" style="color: #6a3a45;"></i>
                        @if(isset($stokMenipisCount) && $stokMenipisCount > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                                {{ $stokMenipisCount }}
                            </span>
                        @endif
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" aria-labelledby="notificationDropdown" style="width: 300px; max-height: 400px; overflow-y: auto;">
                        <li><h6 class="dropdown-header fw-bold">Notifikasi Stok Menipis</h6></li>
                        @if(isset($stokMenipisCount) && $stokMenipisCount > 0)
                            @foreach($menuMenipis as $menu)
                                <li>
                                    <a class="dropdown-item d-flex justify-content-between align-items-center py-2" href="{{ route('admin.menu.index') }}">
                                        <div>
                                            <i class="bi bi-box-seam text-warning me-2"></i> {{ $menu->nama_menu }}
                                        </div>
                                        <span class="badge bg-danger rounded-pill">{{ $menu->stok }}</span>
                                    </a>
                                </li>
                            @endforeach
                            @foreach($bahanMenipis as $bahan)
                                <li>
                                    <a class="dropdown-item d-flex justify-content-between align-items-center py-2" href="{{ route('admin.stok.index') }}">
                                        <div>
                                            <i class="bi bi-layers text-warning me-2"></i> {{ $bahan->nama_bahan }}
                                        </div>
                                        <span class="badge bg-danger rounded-pill">{{ $bahan->stok }}</span>
                                    </a>
                                </li>
                            @endforeach
                        @else
                            <li><span class="dropdown-item text-muted small text-center py-3">Semua stok aman</span></li>
                        @endif
                    </ul>
                </div>

                <button class="logout-top" onclick="document.getElementById('logout-form').submit();">
                    <i class="bi bi-box-arrow-right me-2"></i> Logout
                </button>
            </div>
        </div>

        <div class="admin-main-wrapper">
            <aside class="admin-sidebar">
                <div class="mb-4">
                    <div class="brand-title d-flex align-items-center mb-1">
                        <i class="bi bi-layout-text-sidebar-reverse me-2"></i>
                        Admin Panel
                    </div>
                    <div class="brand-subtitle">Admin Warung</div>
                </div>

                <nav class="nav flex-column gap-1 mb-3">
                    <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                        <i class="bi bi-house-door-fill me-2"></i> Dashboard
                    </a>
                    <a class="nav-link {{ request()->routeIs('admin.meja.*') ? 'active' : '' }}" href="{{ route('admin.meja.index') }}">
                        <i class="bi bi-display me-2"></i> Meja & QR Code
                    </a>
                    <a class="nav-link {{ request()->routeIs('admin.menu.*') ? 'active' : '' }}" href="{{ route('admin.menu.index') }}">
                        <i class="bi bi-list-ul me-2"></i> Produk
                    </a>
                    <a class="nav-link {{ request()->routeIs('admin.stok.*') ? 'active' : '' }}" href="{{ route('admin.stok.index') }}">
                        <i class="bi bi-box-seam me-2"></i> Stok
                    </a>
                    <a class="nav-link {{ request()->routeIs('admin.kasir.*') ? 'active' : '' }}" href="{{ route('admin.kasir.index') }}">
                        <i class="bi bi-people-fill me-2"></i> Kasir
                    </a>
                    <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                        <i class="bi bi-person-lines-fill me-2"></i> Pengguna (User)
                    </a>
                    <a class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}" href="{{ route('admin.reports.index') }}">
                        <i class="bi bi-graph-up-arrow me-2"></i> Laporan
                    </a>
                    <a class="nav-link {{ request()->routeIs('admin.pengeluaran.*') ? 'active' : '' }}" href="{{ route('admin.pengeluaran.index') }}">
                        <i class="bi bi-wallet2 me-2"></i> Pengeluaran
                    </a>
                    <a class="nav-link {{ request()->routeIs('admin.promo.*') ? 'active' : '' }}" href="{{ route('admin.promo.index') }}">
                        <i class="bi bi-tag-fill me-2"></i> Promo
                    </a>
                    <a class="nav-link {{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}" href="{{ route('admin.reviews.index') }}">
                        <i class="bi bi-chat-left-text-fill me-2"></i> Ulasan
                    </a>
                    <a class="nav-link {{ request()->routeIs('admin.settings') ? 'active' : '' }}" href="{{ route('admin.settings') }}">
                        <i class="bi bi-gear-fill me-2"></i> Pengaturan
                    </a>
                </nav>
            </aside>

            <main class="admin-content">
                @yield('content')
            </main>
        </div>
    </div>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
        @csrf
    </form>
</body>
</html>
