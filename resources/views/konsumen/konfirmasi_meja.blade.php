@extends('layouts.app')

@section('content')
<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8 text-center">
            <div class="card shadow-sm border-0 rounded-4 p-4">
                <div class="card-body">
                    <div class="mb-4">
                        <i class="bi bi-exclamation-circle text-warning" style="font-size: 4rem;"></i>
                    </div>
                    <h3 class="fw-bold text-accent mb-3">Meja Sedang Digunakan</h3>
                    <p class="text-muted fs-5 mb-4">
                        Sistem mendeteksi bahwa <strong>{{ $meja->nama_meja_atau_nomor }}</strong> saat ini sedang ada yang memesan.
                    </p>
                    <hr class="mb-4">
                    <p class="fw-bold mb-3">Apakah Anda berada di rombongan yang sama dengan pemesan sebelumnya?</p>
                    
                    <div class="d-grid gap-3">
                        <a href="{{ url('/konsumen/menu/' . $meja->id . '?confirm=1') }}" class="btn btn-primary btn-lg rounded-pill fw-bold shadow-sm">
                            <i class="bi bi-check-circle me-2"></i> Ya, Kami Satu Rombongan
                        </a>
                        <a href="{{ url('/konsumen/menu') }}" class="btn btn-outline-secondary btn-lg rounded-pill fw-bold">
                            <i class="bi bi-arrow-left me-2"></i> Bukan, Pilih Meja Lain
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
