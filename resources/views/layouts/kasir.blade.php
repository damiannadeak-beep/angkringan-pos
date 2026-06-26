<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kasir POS - Angkringan</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <style>
        body { background: transparent; color: #3e2723; font-family: 'Playfair Display', serif !important; }
        .kasir-navbar { background: #3e2723; border-bottom: none; position: relative; z-index: 1050; }
        .kasir-navbar .navbar-brand { color: #f0e9dd; font-weight: 700; }
        .kasir-navbar .navbar-text, .kasir-navbar .nav-link { color: #f0e9dd; }
        .kasir-navbar .nav-link.active { color: #d49b78 !important; }
        .kasir-navbar .nav-link:hover { color: #d7ccc8; }
        .kasir-navbar .navbar-toggler-icon { filter: invert(1); }
        .kasir-main { padding: 1.5rem 1.25rem; }
        .kasir-container { max-width: 1300px; margin: 0 auto; }
        .kasir-header { background: rgba(253, 251, 247, 0.85); backdrop-filter: blur(10px); border: 1px solid rgba(62,39,35,0.1); border-radius: 1.5rem; }
        .kasir-header h5 { color: #3e2723; }
        .kasir-card { border-radius: 1.5rem; border: 1px solid rgba(62, 39, 35, 0.1); background: rgba(253, 251, 247, 0.85) !important; backdrop-filter: blur(10px); box-shadow: 0 10px 30px rgba(62, 39, 35, .05); }
        .kasir-card .card-body { padding: 1.75rem; }
        .menu-card { border: 1px solid rgba(62, 39, 35, 0.05); border-radius: 1.25rem; background: rgba(255, 255, 255, 0.7) !important; backdrop-filter: blur(5px); transition: transform .2s ease, box-shadow .2s ease; }
        .menu-card:hover { transform: translateY(-4px); box-shadow: 0 18px 32px rgba(62, 39, 35, 0.08); background: rgba(255, 255, 255, 0.95) !important; }
        .menu-card .price { color: #5d4037; font-weight: 700; }
        .btn-soft { background: #f0e9dd; border: 1px solid #d7ccc8; color: #3e2723; }
        .btn-soft:hover { background: #e6ddcf; }
        .payment-pill.active { background: #5d4037; color: #fff; border-color: #5d4037; }
        input.form-control, select.form-control, .form-select, .input-group, .input-group-text { border-radius: 999px; }
        textarea.form-control { border-radius: 1rem; }
        .cart-list { min-height: 240px; }
        .cart-item { border-bottom: 1px solid rgba(62,39,35,0.1); padding-bottom: .85rem; margin-bottom: .85rem; }
        .cart-item:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
        
        .font-sans { font-family: 'Inter', sans-serif !important; }
        
        /* Dark Mode Styles */
        body.dark-mode { background: #121212; color: #e0e0e0; }
        body.dark-mode .kasir-navbar { background: #1e1e1e !important; border-bottom: 1px solid #333; }
        body.dark-mode .kasir-navbar .navbar-brand { color: #fff; }
        body.dark-mode .kasir-header, body.dark-mode .kasir-card, body.dark-mode .menu-card, body.dark-mode .card, body.dark-mode .bg-white { 
            background-color: rgba(30, 30, 30, 0.95) !important; 
            background: none !important;
            border-color: rgba(255,255,255,0.1) !important; 
            color: #e0e0e0 !important;
        }
        body.dark-mode .bg-light { background-color: #2c2c2c !important; color: #e0e0e0 !important; }
        body.dark-mode .bg-light.bg-opacity-50 { background-color: transparent !important; }
        body.dark-mode .kasir-header h5, body.dark-mode .menu-card .price { color: #e0e0e0; }
        body.dark-mode .text-muted, body.dark-mode .text-white-50 { color: #aaa !important; }
        body.dark-mode .table-light, body.dark-mode thead th { background-color: #2c2c2c !important; color: #fff !important; border-color: #444 !important; }
        body.dark-mode .table { --bs-table-bg: transparent; --bs-table-color: #e0e0e0; background-color: transparent !important; }
        body.dark-mode tbody td, body.dark-mode tbody tr { background-color: transparent !important; color: #e0e0e0 !important; border-color: #444 !important; }
        body.dark-mode .table-hover tbody tr:hover td, body.dark-mode .table-hover tbody tr:hover th { background-color: rgba(255, 255, 255, 0.05) !important; }
        body.dark-mode .form-control, body.dark-mode .form-select, body.dark-mode .input-group-text { background-color: #2c2c2c !important; color: #fff !important; border-color: #444 !important; }
        body.dark-mode .form-control::placeholder, body.dark-mode .form-select::placeholder, body.dark-mode textarea::placeholder { color: #888 !important; opacity: 1 !important; }
        body.dark-mode .btn-soft { background: #2c2c2c; border-color: #444; color: #e0e0e0; }
        body.dark-mode .btn-soft:hover, body.dark-mode .btn-soft.active { background: #3d3d3d; border-color: #555; color: #fff; }
        body.dark-mode .payment-pill.active { background: #B05923; border-color: #B05923; color: #fff; }
        body.dark-mode .cart-item { border-color: rgba(255,255,255,0.1) !important; background: rgba(255,255,255,0.05) !important; color: #e0e0e0; }
        
        /* Utility overrides for dark mode */
        .order-config-box { background-color: #fcfaf8; border: 1px dashed #e6ddcf; }
        body.dark-mode .order-config-box { background-color: rgba(30, 30, 30, 0.95) !important; background: none !important; border: 1px dashed #444 !important; }

        .text-accent { color: #5d4037; }
        body.dark-mode .text-accent { color: #d7ccc8 !important; }

        .icon-accent { color: #5d4037; }
        body.dark-mode .icon-accent { color: #1e1e1e !important; }

        .gradient-card-primary { background: linear-gradient(135deg, #8d6e63, #5d4037); color: white; }
        body.dark-mode .gradient-card-primary { background: linear-gradient(135deg, #3e2723, #261613) !important; color: #e0e0e0; }

        .hover-lift { transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .hover-lift:hover { transform: translateY(-4px); box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important; }
        body.dark-mode .hover-lift:hover { box-shadow: 0 10px 20px rgba(0,0,0,0.5) !important; }

        /* Fix primary colors blending into dark backgrounds */
        body.dark-mode .text-primary { color: #d49b78 !important; }
        body.dark-mode .bg-primary { background-color: #B05923 !important; }
        body.dark-mode .btn-primary { background-color: #B05923 !important; border-color: #B05923 !important; color: #fff !important; }
        body.dark-mode .btn-primary:hover { background-color: #964a1d !important; border-color: #964a1d !important; }
        body.dark-mode .btn-outline-primary { color: #d49b78 !important; border-color: #d49b78 !important; }
        body.dark-mode .btn-outline-primary:hover { background-color: #d49b78 !important; color: #fff !important; }
    </style>
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-lg kasir-navbar shadow-sm py-3 d-print-none">
            <div class="container-fluid">
                <a class="navbar-brand d-flex align-items-center" href="{{ route('kasir.pos') }}">
                    <i class="bi bi-shop me-2"></i>
                    Kasir Pos
                </a>
                
                <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#kasirNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <div class="collapse navbar-collapse justify-content-center mt-3 mt-lg-0" id="kasirNav">
                    <ul class="navbar-nav mb-2 mb-lg-0 gap-3 text-center text-lg-start">
                        <li class="nav-item">
                            <a class="nav-link fw-bold {{ request()->routeIs('kasir.pos') ? 'active' : '' }}" href="{{ route('kasir.pos') }}">
                                <i class="bi bi-cart-plus me-1"></i> Transaksi Kasir
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-bold {{ request()->routeIs('kasir.pesanan_aktif') ? 'active' : '' }}" href="{{ route('kasir.pesanan_aktif') }}">
                                <i class="bi bi-bell me-1"></i> Pesanan Aktif
                                <span class="badge bg-danger rounded-pill ms-1 shadow-sm" id="badge-active-orders" style="display: none;">0</span>
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link fw-bold dropdown-toggle {{ request()->routeIs('kasir.shift_report', 'kasir.pengeluaran.*', 'kasir.stok.*', 'kasir.permintaan.*', 'kasir.meja.*', 'kasir.absensi.*') ? 'active' : '' }}" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-grid-fill me-1"></i> Menu Lainnya
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark shadow-sm border-0" aria-labelledby="navbarDropdown" style="background-color: #3e2723;">
                                <li>
                                    <a class="dropdown-item text-light {{ request()->routeIs('kasir.stok.*') ? 'active' : '' }}" href="{{ route('kasir.stok.index') }}">
                                        <i class="bi bi-box-seam me-1"></i> Update Stok
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item text-light {{ request()->routeIs('kasir.permintaan.*') ? 'active' : '' }}" href="{{ route('kasir.permintaan.index') }}">
                                        <i class="bi bi-bag-plus me-1"></i> Permintaan Belanja
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item text-light {{ request()->routeIs('kasir.pengeluaran.*') ? 'active' : '' }}" href="{{ route('kasir.pengeluaran.index') }}">
                                        <i class="bi bi-wallet2 me-1"></i> Pengeluaran
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item text-light {{ request()->routeIs('kasir.shift_report') ? 'active' : '' }}" href="{{ route('kasir.shift_report') }}">
                                        <i class="bi bi-journal-text me-1"></i> Laporan Shift
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider border-secondary"></li>
                                <li>
                                    <a class="dropdown-item text-light {{ request()->routeIs('kasir.meja.*') ? 'active' : '' }}" href="{{ route('kasir.meja.index') }}">
                                        <i class="bi bi-grid-3x3-gap-fill me-1"></i> Manajemen Meja
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item text-light {{ request()->routeIs('kasir.absensi.*') ? 'active' : '' }}" href="{{ route('kasir.absensi.index') }}">
                                        <i class="bi bi-geo-alt-fill me-1"></i> Absensi Shift
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>

                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-sm btn-outline-light rounded-pill" id="darkModeToggle" title="Toggle Dark Mode">
                        <i class="bi bi-moon-stars"></i>
                    </button>
                    <div class="navbar-text small text-light">{{ auth()->user()->name ?? 'Kasir' }}</div>
                    @php
                        $isShiftOwner = \App\Models\KasirShift::where('user_id', auth()->id())->where('status', 'open')->exists();
                    @endphp
                    @if($isShiftOwner)
                        {{-- Kasir 1 (Pemilik Laci) → Wajib Tutup Shift --}}
                        <a class="btn btn-danger btn-sm rounded-pill shadow-sm" href="{{ route('kasir.shift.tutup') }}">
                            <i class="bi bi-x-circle me-1"></i> Tutup Shift
                        </a>
                    @else
                        {{-- Kasir 2 (Bukan Pemilik Laci) → Logout Biasa --}}
                        <a class="btn btn-outline-light btn-sm rounded-pill" href="{{ route('logout') }}"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="bi bi-box-arrow-right me-1"></i> Logout
                        </a>
                    @endif
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

    <script>
        function fetchActiveOrdersCount() {
            fetch('{{ route('kasir.active_orders_count') }}')
                .then(response => response.json())
                .then(data => {
                    const badge = document.getElementById('badge-active-orders');
                    if (data.count > 0) {
                        badge.innerText = data.count;
                        badge.style.display = 'inline-block';
                    } else {
                        badge.style.display = 'none';
                    }
                })
                .catch(err => console.error(err));
        }

        // Dark Mode Logic
        const darkModeToggle = document.getElementById('darkModeToggle');
        const icon = darkModeToggle.querySelector('i');
        
        function applyDarkMode(isDark) {
            if (isDark) {
                document.body.classList.add('dark-mode');
                icon.classList.replace('bi-moon-stars', 'bi-sun');
                localStorage.setItem('kasirDarkMode', 'true');
            } else {
                document.body.classList.remove('dark-mode');
                icon.classList.replace('bi-sun', 'bi-moon-stars');
                localStorage.setItem('kasirDarkMode', 'false');
            }
        }

        // Initialize from LocalStorage
        if (localStorage.getItem('kasirDarkMode') === 'true') {
            applyDarkMode(true);
        }

        darkModeToggle.addEventListener('click', () => {
            const isCurrentlyDark = document.body.classList.contains('dark-mode');
            applyDarkMode(!isCurrentlyDark);
        });

        // Setup audio element for notification
        const notifSound = new Audio('https://actions.google.com/sounds/v1/alarms/beep_short.ogg');

        // Fetch Notifications
        function fetchNotifications() {
            fetch('{{ url("/kasir/api/notifications") }}')
                .then(response => response.json())
                .then(data => {
                    if (data && data.length > 0) {
                        // Play sound
                        notifSound.play().catch(e => console.log('Autoplay prevented:', e));
                        
                        data.forEach(notif => {
                            // Tampilkan alert dengan delay sedikit agar suara sempat diputar
                            setTimeout(() => {
                                alert('🔔 Notifikasi Baru:\n' + notif.message);
                            }, 500);
                            
                            // Tandai sudah dibaca
                            fetch('{{ url("/kasir/api/notifications") }}/' + notif.id + '/read', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                }
                            });
                        });
                    }
                })
                .catch(error => console.error('Error fetching notifications:', error));
        }

        // Cek setiap 10 detik
        setInterval(() => {
            fetchActiveOrdersCount();
            fetchNotifications();
        }, 10000);
        // Cek saat pertama load
        document.addEventListener('DOMContentLoaded', () => {
            fetchActiveOrdersCount();
            fetchNotifications();
        });
    </script>
    
    @include('components.webpush')
</body>
</html>
