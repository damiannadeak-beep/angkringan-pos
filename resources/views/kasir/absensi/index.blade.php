@extends('layouts.kasir')

@section('content')
<div class="row justify-content-center mt-5">
    <div class="col-md-6">
        <div class="card kasir-card border-0 shadow-sm text-center">
            <div class="card-header bg-transparent border-0 pt-4">
                <h4 class="fw-bold mb-0">
                    <i class="bi bi-geo-alt-fill text-danger me-2"></i> Absensi Shift Kasir
                </h4>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle me-1"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <h5 class="fw-bold mb-3">{{ auth()->user()->name }}</h5>
                <p class="text-muted">Shift Anda: <span class="badge bg-primary text-uppercase">{{ auth()->user()->shift ?? 'Pagi' }}</span></p>

                <div class="my-4">
                    <p class="mb-1 text-muted small">Status Absensi Hari Ini:</p>
                    @if(!$absensi)
                        <h4 class="text-danger fw-bold"><i class="bi bi-x-circle me-1"></i> Belum Absen Masuk</h4>
                    @elseif($absensi && !$absensi->jam_keluar)
                        <h4 class="text-success fw-bold"><i class="bi bi-check-circle me-1"></i> Sudah Absen Masuk ({{ \Carbon\Carbon::parse($absensi->jam_masuk)->format('H:i') }})</h4>
                        <p class="text-warning fw-bold small"><i class="bi bi-info-circle me-1"></i> Belum Absen Keluar</p>
                    @else
                        <h4 class="text-secondary fw-bold"><i class="bi bi-check-all me-1"></i> Shift Selesai</h4>
                        <p class="small text-muted mb-0">Masuk: {{ \Carbon\Carbon::parse($absensi->jam_masuk)->format('H:i') }} | Keluar: {{ \Carbon\Carbon::parse($absensi->jam_keluar)->format('H:i') }}</p>
                    @endif
                </div>

                <hr>

                @if(!isset($settings['warung_latitude']) || !isset($settings['warung_longitude']))
                    <div class="alert alert-warning">
                        Admin belum mengatur titik kordinat warung. Anda tidak dapat melakukan absensi.
                    </div>
                @else
                    @if(!$absensi || !$absensi->jam_keluar)
                        <form action="{{ route('kasir.absensi.store') }}" method="POST" id="form-absensi">
                            @csrf
                            <input type="hidden" name="latitude" id="latitude">
                            <input type="hidden" name="longitude" id="longitude">

                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-lg {{ !$absensi ? 'btn-danger' : 'btn-warning' }} fw-bold rounded-pill" onclick="prosesAbsensi()">
                                    <i class="bi bi-geo-alt me-2"></i> {{ !$absensi ? 'Proses Absen Masuk' : 'Proses Absen Keluar' }}
                                </button>
                            </div>
                            <div class="mt-3 small text-muted" id="lokasi-status">
                                Membutuhkan akses lokasi (GPS) untuk validasi jarak 5 meter dari warung.
                            </div>
                        </form>
                    @else
                        <div class="alert alert-secondary">
                            Terima kasih atas kerja keras Anda hari ini!
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    function prosesAbsensi() {
        const btn = document.querySelector('button[onclick="prosesAbsensi()"]');
        const status = document.getElementById('lokasi-status');
        const latInput = document.getElementById('latitude');
        const lngInput = document.getElementById('longitude');
        const form = document.getElementById('form-absensi');

        if (!navigator.geolocation) {
            status.innerHTML = "<span class='text-danger'>Browser Anda tidak mendukung Geolocation. Gunakan browser modern.</span>";
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Mencari Lokasi...';
        status.innerHTML = "<span class='text-primary fw-bold'>Mencari sinyal GPS Anda...</span>";

        navigator.geolocation.getCurrentPosition(
            (position) => {
                latInput.value = position.coords.latitude;
                lngInput.value = position.coords.longitude;
                status.innerHTML = "<span class='text-success fw-bold'>Lokasi ditemukan! (Akurasi: " + Math.round(position.coords.accuracy) + " meter). Memproses...</span>";
                form.submit();
            },
            (error) => {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-geo-alt me-2"></i> Coba Lagi';
                let msg = "";
                switch(error.code) {
                    case error.PERMISSION_DENIED: msg = "Anda menolak izin akses lokasi. Pastikan GPS HP nyala dan izinkan browser mengakses lokasi."; break;
                    case error.POSITION_UNAVAILABLE: msg = "Informasi lokasi tidak tersedia (Sinyal GPS lemah)."; break;
                    case error.TIMEOUT: msg = "Waktu pencarian lokasi habis."; break;
                    default: msg = "Terjadi kesalahan."; break;
                }
                status.innerHTML = "<span class='text-danger fw-bold'>" + msg + "</span>";
            },
            { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
        );
    }
</script>
@endsection
