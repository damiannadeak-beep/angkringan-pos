@extends('layouts.app')

@section('content')
<div class="container-fluid" style="background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%); min-height: 90vh;">
    <div class="row h-100 align-items-center justify-content-center py-5">
        <div class="col-12 col-md-8 col-lg-5">
            <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                <div class="row g-0">
                    <div class="col-12 p-4 p-md-5">
                        <div class="text-center mb-4">
                            <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow" style="width: 70px; height: 70px;">
                                <i class="bi bi-shop fs-1"></i>
                            </div>
                            <h3 class="fw-bold text-dark mb-1">Selamat Datang</h3>
                            <p class="text-muted">Masuk ke sistem Angkringan POS</p>
                        </div>

                        <form method="POST" action="{{ route('login') }}">
                            @csrf
                            <div class="mb-4">
                                <label for="email" class="form-label fw-bold small text-muted">Email Address</label>
                                <div class="input-group input-group-lg shadow-sm rounded-3">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
                                    <input id="email" type="email" class="form-control border-start-0 ps-0 @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="contoh@email.com">
                                </div>
                                @error('email')
                                    <span class="text-danger small mt-1 d-block fw-bold">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label for="password" class="form-label fw-bold small text-muted mb-0">Password</label>
                                    @if (Route::has('password.request'))
                                        <a class="small text-decoration-none fw-bold text-primary" href="{{ route('password.request') }}">Lupa Password?</a>
                                    @endif
                                </div>
                                <div class="input-group input-group-lg shadow-sm rounded-3">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-lock"></i></span>
                                    <input id="password" type="password" class="form-control border-start-0 ps-0 @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" placeholder="••••••••">
                                </div>
                                @error('password')
                                    <span class="text-danger small mt-1 d-block fw-bold">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <div class="form-check">
                                    <input class="form-check-input shadow-sm" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                    <label class="form-check-label text-muted fw-semibold small" for="remember">
                                        Ingat Saya (Remember Me)
                                    </label>
                                </div>
                            </div>

                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" class="btn btn-primary btn-lg rounded-pill fw-bold shadow-sm py-3">
                                    Masuk Sekarang <i class="bi bi-arrow-right-circle ms-2"></i>
                                </button>
                            </div>
                        </form>
                        
                        <div class="text-center mt-5">
                            <p class="text-muted small">Belum punya akun konsumen? <br> <a href="{{ route('register') }}" class="fw-bold text-decoration-none fs-6">Daftar Sekarang</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
