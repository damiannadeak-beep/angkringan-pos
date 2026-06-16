@extends('layouts.app')

@section('content')
<div class="container-fluid" style="background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%); min-height: 90vh;">
    <div class="row h-100 align-items-center justify-content-center py-5">
        <div class="col-12 col-md-8 col-lg-5">
            <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                <div class="row g-0">
                    <div class="col-12 p-4 p-md-5">
                        <div class="text-center mb-4">
                            <div class="bg-warning text-dark rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow" style="width: 70px; height: 70px;">
                                <i class="bi bi-envelope-exclamation fs-1"></i>
                            </div>
                            <h3 class="fw-bold text-dark mb-1">Verifikasi Email Anda</h3>
                            <p class="text-muted">Langkah terakhir sebelum memesan</p>
                        </div>

                        @if (session('resent'))
                            <div class="alert alert-success border-0 shadow-sm rounded-3 mb-4" role="alert">
                                <i class="bi bi-check-circle-fill me-2"></i> Link verifikasi baru telah dikirimkan ke alamat email Anda.
                            </div>
                        @endif

                        <div class="text-center mb-4">
                            <p class="text-muted mb-2">
                                Sebelum melanjutkan, silakan periksa email Anda (termasuk folder Spam) untuk mengklik link konfirmasi.
                            </p>
                            <p class="text-muted mb-4">
                                Jika Anda tidak menerima email tersebut, klik tombol di bawah ini untuk meminta ulang.
                            </p>
                        </div>

                        <form class="d-inline" method="POST" action="{{ route('verification.resend') }}">
                            @csrf
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-warning btn-lg rounded-pill fw-bold shadow-sm py-3 text-dark">
                                    <i class="bi bi-send-fill me-2"></i> Kirim Ulang Link
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
