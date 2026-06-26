@extends('layouts.app')

@section('content')
<div class="container-fluid" style="background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%); min-height: 90vh;">
    <div class="row h-100 align-items-center justify-content-center py-5">
        <div class="col-12 col-md-8 col-lg-5">
            <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                <div class="row g-0">
                    <div class="col-12 p-4 p-md-5">
                        <div class="text-center mb-4">
                            <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow" style="width: 70px; height: 70px;">
                                <i class="bi bi-person-plus fs-1"></i>
                            </div>
                            <h3 class="fw-bold text-dark mb-1">Daftar Akun</h3>
                            <p class="text-muted">Buat akun untuk memesan menu</p>
                        </div>

                        <form method="POST" action="{{ route('register') }}">
                            @csrf
                            
                            <div class="mb-3">
                                <label for="name" class="form-label fw-bold small text-muted">Nama Lengkap</label>
                                <div class="input-group input-group-lg shadow-sm rounded-3">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-person"></i></span>
                                    <input id="name" type="text" class="form-control border-start-0 ps-0 @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus placeholder="Contoh: Damian Nadeak">
                                </div>
                                @error('name')
                                    <span class="text-danger small mt-1 d-block fw-bold">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label fw-bold small text-muted">Email Address</label>
                                <div class="input-group input-group-lg shadow-sm rounded-3">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
                                    <input id="email" type="email" class="form-control border-start-0 ps-0 @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="contoh@email.com">
                                </div>
                                @error('email')
                                    <span class="text-danger small mt-1 d-block fw-bold">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label fw-bold small text-muted">Password</label>
                                <div class="input-group input-group-lg shadow-sm rounded-3">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-lock"></i></span>
                                    <input id="password" type="password" class="form-control border-start-0 ps-0 @error('password') is-invalid @enderror" name="password" required autocomplete="new-password" placeholder="Minimal 8 karakter">
                                </div>
                                @error('password')
                                    <span class="text-danger small mt-1 d-block fw-bold">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="password-confirm" class="form-label fw-bold small text-muted">Konfirmasi Password</label>
                                <div class="input-group input-group-lg shadow-sm rounded-3">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-check-circle"></i></span>
                                    <input id="password-confirm" type="password" class="form-control border-start-0 ps-0" name="password_confirmation" required autocomplete="new-password" placeholder="Ulangi password Anda">
                                </div>
                            </div>

                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" class="btn btn-success btn-lg rounded-pill fw-bold shadow-sm py-3 mb-2">
                                    Daftar Sekarang <i class="bi bi-person-plus-fill ms-2"></i>
                                </button>
                                <a href="{{ route('google.login') }}" class="btn btn-light btn-lg rounded-pill fw-bold shadow-sm py-3 d-flex align-items-center justify-content-center border" style="color: #444;">
                                    <img src="https://fonts.gstatic.com/s/i/productlogos/googleg/v6/24px.svg" alt="Google" class="me-2" style="width: 24px; height: 24px;">
                                    Daftar dengan Google
                                </a>
                            </div>
                        </form>
                        
                        <div class="text-center mt-4">
                            <p class="text-muted small">Sudah punya akun? <a href="{{ route('login') }}" class="fw-bold text-decoration-none text-success">Masuk di sini</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
