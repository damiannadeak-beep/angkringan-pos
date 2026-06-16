@extends('layouts.kasir')

@section('content')
<div class="container-fluid px-4 py-5 d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="card shadow-lg border-0 rounded-4" style="max-width: 500px; width: 100%;">
        <div class="card-body p-5">
            <div class="text-center mb-4">
                <div class="bg-danger bg-opacity-10 text-danger rounded-circle d-inline-flex p-3 mb-3">
                    <i class="bi bi-box-arrow-right fs-1"></i>
                </div>
                <h3 class="fw-bold">Tutup Shift Kasir</h3>
                <p class="text-muted">Hitung seluruh fisik uang tunai yang ada di dalam laci kasir sekarang dan masukkan totalnya ke bawah ini.</p>
            </div>

            <div class="alert alert-info py-2">
                <small><i class="bi bi-info-circle me-1"></i> Waktu mulai shift: {{ $shift->waktu_buka->format('d M Y, H:i') }}</small>
            </div>

            <form action="{{ route('kasir.shift.storeTutup') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin jumlah fisik uang tunai sudah dihitung dengan benar? Shift akan ditutup dan Anda akan keluar dari sistem.')">
                @csrf
                <div class="mb-4">
                    <label class="form-label fw-bold">Total Uang Fisik Aktual di Laci (Rp)</label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-light border-end-0">Rp</span>
                        <input type="number" class="form-control border-start-0 ps-0" name="uang_fisik_aktual" placeholder="Contoh: 650000" min="0" required autofocus>
                    </div>
                    <small class="text-muted mt-2 d-block">Sistem akan secara otomatis mencocokkan jumlah ini dengan histori pesanan tunai Anda selama shift berlangsung.</small>
                    @error('uang_fisik_aktual')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ route('kasir.pos') }}" class="btn btn-light btn-lg flex-grow-1 fw-bold rounded-pill">Batal</a>
                    <button type="submit" class="btn btn-danger btn-lg flex-grow-1 fw-bold rounded-pill shadow-sm">
                        Akhiri Shift & Logout
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
