@extends('layouts.app')

@section('content')
<div class="container mt-4 mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h3 class="fw-bold mb-3">Pilih Meja untuk Memesan</h3>
                    <p class="text-muted mb-4">Silakan pilih meja yang tersedia. Setelah memilih meja, Anda dapat memilih menu dan membuat pesanan.</p>
                    <div class="row g-3">
                        @forelse($mejas as $meja)
                            <div class="col-12 col-md-6">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-body d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="mb-1">{{ $meja->nama_meja_atau_nomor }}</h5>
                                            <small class="text-muted">{{ $meja->keterangan ?? 'Meja tersedia untuk pemesanan.' }}</small>
                                        </div>
                                        <a href="/konsumen/menu/{{ $meja->id }}" class="btn btn-primary">Pilih</a>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="alert alert-warning mb-0">Tidak ada meja tersedia saat ini.</div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
