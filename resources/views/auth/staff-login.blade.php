@extends('layouts.app')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
    
    .staff-auth-wrapper {
        background-color: #2d1a11;
        font-family: 'Plus Jakarta Sans', sans-serif !important;
        min-height: 88vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2.5rem 1rem;
    }

    .staff-auth-card {
        background: #3e2723;
        border: 1px solid rgba(255, 255, 255, 0.12);
        border-radius: 1.75rem;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
        padding: 2.75rem 2.25rem;
        width: 100%;
        max-width: 440px;
        color: #f0e9dd;
    }

    .staff-brand-icon {
        width: 72px;
        height: 72px;
        border-radius: 1.25rem;
        background: linear-gradient(135deg, #b05923, #5d4037);
        color: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1.25rem;
        box-shadow: 0 8px 24px rgba(176, 89, 35, 0.3);
    }

    .staff-input-group .input-group-text {
        background: rgba(255, 255, 255, 0.08);
        border-color: rgba(255, 255, 255, 0.15);
        border-right: none;
        color: #d7ccc8;
        border-top-left-radius: 0.85rem;
        border-bottom-left-radius: 0.85rem;
    }

    .staff-input-group .form-control {
        background: rgba(255, 255, 255, 0.05);
        border-color: rgba(255, 255, 255, 0.15);
        border-left: none;
        border-top-right-radius: 0.85rem;
        border-bottom-right-radius: 0.85rem;
        color: #ffffff;
        font-size: 0.95rem;
        padding: 0.75rem 1rem;
    }

    .staff-input-group .form-control::placeholder {
        color: rgba(255, 255, 255, 0.4);
    }

    .staff-input-group .form-control:focus {
        background: rgba(255, 255, 255, 0.1);
        border-color: #b05923;
        color: #ffffff;
        box-shadow: none;
    }

    .btn-staff-primary {
        background: linear-gradient(135deg, #b05923 0%, #8b2c2c 100%);
        color: #ffffff !important;
        font-weight: 700;
        padding: 0.85rem 1.5rem;
        border-radius: 999px;
        border: none;
        box-shadow: 0 8px 24px rgba(176, 89, 35, 0.35);
        transition: all 0.25s ease;
    }

    .btn-staff-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 30px rgba(176, 89, 35, 0.5);
    }
</style>

<div class="staff-auth-wrapper">
    <div class="staff-auth-card text-center">
        <div class="staff-brand-icon">
            <i class="bi bi-shield-lock-fill fs-2"></i>
        </div>
        <h3 class="fw-bold text-white mb-1">Portal Staf & Kasir</h3>
        <p class="text-white-50 small mb-4">Masuk ke sistem Mesin POS atau Dashboard Admin</p>

        @if (session('error'))
            <div class="alert alert-danger shadow-sm border-0 rounded-3 text-center mb-4 small">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="text-start">
            @csrf
            
            <div class="mb-3">
                <label for="email" class="form-label fw-bold small text-white-50">Email Staf / Kasir</label>
                <div class="input-group staff-input-group">
                    <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="staf@angkringan.com">
                </div>
                @error('email')
                    <span class="text-warning small mt-1 d-block fw-bold">{{ $message }}</span>
                @enderror
            </div>

            <div class="mb-4">
                <label for="password" class="form-label fw-bold small text-white-50">Password</label>
                <div class="input-group staff-input-group">
                    <span class="input-group-text"><i class="bi bi-key-fill"></i></span>
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" placeholder="••••••••">
                </div>
                @error('password')
                    <span class="text-warning small mt-1 d-block fw-bold">{{ $message }}</span>
                @enderror
            </div>

            <div class="mb-4 d-flex justify-content-between align-items-center">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label class="form-check-label text-white-50 small fw-semibold" for="remember">
                        Ingat Sesi Login Ini
                    </label>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-staff-primary">
                    <i class="bi bi-box-arrow-in-right me-2"></i> Masuk Ke Portal Staf
                </button>
            </div>
        </form>
        
        <div class="text-center mt-4 pt-3 border-top border-secondary border-opacity-25">
            <p class="text-white-50 small mb-0">Bukan Staf/Kasir? <a href="{{ route('login') }}" class="fw-bold text-decoration-none text-warning">Login Konsumen</a></p>
        </div>
    </div>
</div>
@endsection
