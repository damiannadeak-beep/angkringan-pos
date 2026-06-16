@extends('layouts.kasir')

@section('content')
<div class="container-fluid px-4 py-5 d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="card shadow-lg border-0 rounded-4" style="max-width: 500px; width: 100%;">
        <div class="card-body p-5">
            <div class="text-center mb-4">
                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex p-3 mb-3">
                    <i class="bi bi-cash-stack fs-1"></i>
                </div>
                <h3 class="fw-bold">Buka Shift Kasir</h3>
                <p class="text-muted">Masukkan nominal uang tunai (uang kembalian) yang saat ini ada di laci kasir.</p>
            </div>

            @if(session('warning'))
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i> {{ session('warning') }}
                </div>
            @endif

            <form action="{{ route('kasir.shift.storeBuka') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="form-label fw-bold">Modal Awal (Rp)</label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-light border-end-0">Rp</span>
                        <input type="number" class="form-control border-start-0 ps-0" name="modal_awal" placeholder="Contoh: 100000" min="0" required autofocus>
                    </div>
                    @error('modal_awal')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold rounded-pill shadow-sm">
                    Mulai Shift & Buka Akses Kasir <i class="bi bi-arrow-right ms-2"></i>
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
