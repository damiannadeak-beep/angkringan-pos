@extends('layouts.app')

@section('content')
<div class="container mt-4 mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h3 class="fw-bold mb-3">Pilih Meja untuk Memesan</h3>
                    <p class="text-muted mb-4">Silakan pilih meja yang tersedia. Setelah memilih meja, Anda dapat memilih menu dan membuat pesanan.</p>

                    <!-- Opsi Pesan Nanti (Kampus) -->
                    <div class="card bg-primary text-white border-0 shadow mb-4 hover-lift">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-1 fw-bold"><i class="bi bi-geo-alt me-2"></i>Belum di Lokasi?</h5>
                                <p class="mb-0 small text-white-50">Pesan dulu sekarang, cari meja kosong saat tiba di Angkringan!</p>
                            </div>
                            <a href="{{ route('menu_nanti') }}" class="btn btn-light fw-bold px-4 text-primary rounded-pill">Pesan Sekarang</a>
                        </div>
                    </div>

                    <div class="row g-3">
                        @forelse($mejas as $meja)
                            <div class="col-12 col-md-6">
                                <div class="card border-0 shadow-sm {{ !$meja->is_available ? 'bg-light' : '' }}">
                                    <div class="card-body d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="mb-1">
                                                {{ $meja->nama_meja_atau_nomor }}
                                                @if(!$meja->is_available)
                                                    <span class="badge bg-danger ms-2" style="font-size: 0.7em;">Terisi</span>
                                                @endif
                                            </h5>
                                            <small class="text-muted">{{ $meja->keterangan ?? 'Meja tersedia untuk pemesanan.' }}</small>
                                        </div>
                                        @if($meja->is_available)
                                            <a href="{{ URL::signedRoute('konsumen.menu.meja', ['id_meja' => $meja->id]) }}" class="btn btn-primary">Pilih</a>
                                        @else
                                            <a href="{{ URL::signedRoute('konsumen.menu.meja', ['id_meja' => $meja->id]) }}" class="btn btn-outline-secondary">Pilih (Gabung)</a>
                                        @endif
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
