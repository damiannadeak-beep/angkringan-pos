@extends('layouts.app')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
    
    .auth-wrapper {
        background-color: #fdfbf7;
        font-family: 'Plus Jakarta Sans', sans-serif !important;
        min-height: 88vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2.5rem 1rem;
    }

    .auth-card {
        background: #ffffff;
        border: 1px solid #eae3d8;
        border-radius: 1.75rem;
        box-shadow: 0 12px 36px rgba(62, 39, 35, 0.05);
        padding: 2.5rem;
        width: 100%;
        max-width: 440px;
    }

    .auth-brand-icon {
        width: 68px;
        height: 68px;
        border-radius: 1.25rem;
        background: linear-gradient(135deg, #3e2723, #5d4037);
        color: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1.25rem;
        box-shadow: 0 8px 20px rgba(62, 39, 35, 0.18);
    }

    .auth-input-group .input-group-text {
        background: #faf6f0;
        border-color: #e2d8ca;
        border-right: none;
        color: #5d4037;
        border-top-left-radius: 0.85rem;
        border-bottom-left-radius: 0.85rem;
    }

    .auth-input-group .form-control {
        background: #ffffff;
        border-color: #e2d8ca;
        border-left: none;
        border-top-right-radius: 0.85rem;
        border-bottom-right-radius: 0.85rem;
        font-size: 0.95rem;
        padding: 0.75rem 1rem;
    }

    .auth-input-group .form-control:focus {
        border-color: #5d4037;
        box-shadow: none;
    }

    .btn-auth-primary {
        background: #3e2723;
        color: #ffffff !important;
        font-weight: 700;
        padding: 0.85rem 1.5rem;
        border-radius: 999px;
        border: none;
        box-shadow: 0 8px 20px rgba(62, 39, 35, 0.2);
        transition: all 0.2s ease;
    }

    .btn-auth-primary:hover {
        background: #2d1a11;
        transform: translateY(-2px);
        box-shadow: 0 12px 28px rgba(62, 39, 35, 0.3);
    }

    .btn-google-auth {
        background: #ffffff;
        color: #3e2723 !important;
        font-weight: 700;
        padding: 0.8rem 1.5rem;
        border-radius: 999px;
        border: 1px solid #d7ccc8;
        transition: all 0.2s ease;
    }

    .btn-google-auth:hover {
        background: #f0e9dd;
        border-color: #3e2723;
    }
</style>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="text-center mb-4">
            <div class="auth-brand-icon">
                <i class="bi bi-shop fs-2"></i>
            </div>
            <h3 class="fw-bold text-dark mb-1">Masuk ke Angkringan</h3>
            <p class="text-muted small">Pesan makanan & minuman racikan langsung dari meja</p>
        </div>

        @if (session('error'))
            <div class="alert alert-danger shadow-sm border-0 rounded-3 text-center mb-4 small">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            
            <div class="mb-3">
                <label for="email" class="form-label fw-bold small text-muted">Email Address</label>
                <div class="input-group auth-input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="nama@email.com">
                </div>
                @error('email')
                    <span class="text-danger small mt-1 d-block fw-bold">{{ $message }}</span>
                @enderror
            </div>

            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label for="password" class="form-label fw-bold small text-muted mb-0">Password</label>
                    @if (Route::has('password.request'))
                        <a class="small text-decoration-none fw-bold text-primary" href="{{ route('password.request') }}">Lupa Password?</a>
                    @endif
                </div>
                <div class="input-group auth-input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" placeholder="••••••••">
                </div>
                @error('password')
                    <span class="text-danger small mt-1 d-block fw-bold">{{ $message }}</span>
                @enderror
            </div>

            <div class="mb-4 d-flex justify-content-between align-items-center">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label class="form-check-label text-muted fw-semibold small" for="remember">
                        Ingat Saya
                    </label>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-auth-primary">
                    Masuk Sekarang <i class="bi bi-arrow-right-circle ms-2"></i>
                </button>
                <a href="{{ route('google.login') }}" class="btn btn-google-auth d-flex align-items-center justify-content-center">
                    <img src="https://fonts.gstatic.com/s/i/productlogos/googleg/v6/24px.svg" alt="Google" class="me-2" style="width: 20px; height: 20px;">
                    Masuk dengan Google
                </a>
            </div>
        </form>
        
        <div class="text-center mt-4 pt-3 border-top">
            <p class="text-muted small mb-0">Belum punya akun konsumen? <a href="{{ route('register') }}" class="fw-bold text-decoration-none text-primary">Daftar Sekarang</a></p>
        </div>
    </div>
</div>
@endsection
