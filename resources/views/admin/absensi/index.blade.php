@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-0 text-dark"><i class="bi bi-calendar-check-fill text-danger me-2"></i> Laporan Absensi Kasir</h2>
        <p class="text-muted mb-0">Pantau kehadiran kasir berdasarkan shift dan lokasi GPS.</p>
    </div>
</div>

<div class="card admin-card border-0 shadow-sm mb-4">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card-body">
        <form method="GET" action="{{ route('admin.absensi.index') }}" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-bold">Dari Tanggal</label>
                <input type="date" class="form-control" name="start_date" value="{{ $startDate }}">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold">Sampai Tanggal</label>
                <input type="date" class="form-control" name="end_date" value="{{ $endDate }}">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100 fw-bold">
                    <i class="bi bi-filter"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

@if(count($rekapAbsensi) > 0)
<div class="row mb-4">
    <div class="col-12">
        <h5 class="fw-bold mb-3"><i class="bi bi-star-fill text-warning me-2"></i>Rekapitulasi Kehadiran Kasir (Berdasarkan Filter)</h5>
        <div class="row g-3">
            @foreach($rekapAbsensi as $rekap)
                <div class="col-md-4 col-sm-6">
                    <div class="card admin-card border-0 shadow-sm h-100">
                        <div class="card-body text-center py-4">
                            <div class="mb-2">
                                <i class="bi bi-person-circle fs-1 text-primary opacity-75"></i>
                            </div>
                            <h5 class="fw-bold mb-1">{{ $rekap['nama'] }}</h5>
                            <h3 class="text-success mb-0 fw-bold">{{ $rekap['total_hadir'] }} <span class="fs-6 text-muted fw-normal">Kali Hadir</span></h3>
                            <div class="mt-2 text-muted small fw-bold">
                                <i class="bi bi-clock-history"></i> Total: {{ $rekap['format_jam'] }}
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endif

<div class="card admin-card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Tanggal</th>
                        <th>Nama Kasir</th>
                        <th>Shift</th>
                        <th>Jam Masuk</th>
                        <th>Jam Keluar</th>
                        <th>Jarak (GPS)</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($absensis as $absen)
                        <tr>
                            <td class="ps-4">{{ \Carbon\Carbon::parse($absen->tanggal)->translatedFormat('d F Y') }}</td>
                            <td class="fw-bold">{{ $absen->user->name ?? 'User Dihapus' }}</td>
                            <td>
                                @if($absen->shift == 'pagi')
                                    <span class="badge bg-info text-dark"><i class="bi bi-sun-fill me-1"></i> Pagi</span>
                                @else
                                    <span class="badge bg-dark"><i class="bi bi-moon-stars-fill me-1"></i> Malam</span>
                                @endif
                            </td>
                            <td>{{ $absen->jam_masuk ? \Carbon\Carbon::parse($absen->jam_masuk)->format('H:i') : '-' }}</td>
                            <td>{{ $absen->jam_keluar ? \Carbon\Carbon::parse($absen->jam_keluar)->format('H:i') : '-' }}</td>
                            <td>
                                @if($absen->jarak_meter !== null)
                                    <span class="badge bg-light text-dark border"><i class="bi bi-geo-alt-fill text-danger"></i> {{ $absen->jarak_meter }} m</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($absen->status == 'hadir')
                                    <span class="badge bg-success">Hadir</span>
                                @elseif($absen->status == 'terlambat')
                                    <span class="badge bg-warning text-dark">Terlambat</span>
                                @else
                                    <span class="badge bg-danger">{{ ucfirst($absen->status) }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Belum ada data absensi untuk periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
