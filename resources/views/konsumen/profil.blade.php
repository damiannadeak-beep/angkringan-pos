@extends('layouts.app')

@section('content')
<div class="container py-5">
    
    <div class="row mb-4">
        <div class="col-md-8 mx-auto text-center">
            @if($user->foto)
                <img src="{{ asset('uploads/profil/' . $user->foto) }}" alt="Foto Profil" class="rounded-circle mb-3 border border-4 border-white shadow-sm" style="width: 100px; height: 100px; object-fit: cover;">
            @else
                <div class="d-inline-block bg-primary bg-opacity-10 text-primary p-3 rounded-circle mb-3" style="width: 100px; height: 100px; line-height: 70px;">
                    <i class="bi bi-person-fill" style="font-size: 3rem;"></i>
                </div>
            @endif
            <h2 class="fw-bold">Halo, {{ $user->name }}!</h2>
            <p class="text-muted">Kelola profil dan pantau status pesanan Anda di sini.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="row">
            <div class="col-md-10 mx-auto">
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="row">
            <div class="col-md-10 mx-auto">
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-md-10 mx-auto">
            <div class="card border-0 shadow-sm overflow-hidden rounded-4">
                <div class="card-header bg-white border-bottom-0 p-0">
                    <ul class="nav nav-pills nav-justified" id="pills-tab" role="tablist" style="border-bottom: 2px solid #f1f3f5;">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active rounded-0 fw-bold py-3" id="pills-aktif-tab" data-bs-toggle="pill" data-bs-target="#pills-aktif" type="button" role="tab" style="border-bottom: 3px solid transparent;">
                                <i class="bi bi-basket me-2"></i>Pesanan Aktif
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link rounded-0 fw-bold py-3 text-secondary" id="pills-riwayat-tab" data-bs-toggle="pill" data-bs-target="#pills-riwayat" type="button" role="tab" style="border-bottom: 3px solid transparent;" onclick="this.classList.remove('text-secondary');">
                                <i class="bi bi-clock-history me-2"></i>Riwayat
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link rounded-0 fw-bold py-3 text-secondary" id="pills-profil-tab" data-bs-toggle="pill" data-bs-target="#pills-profil" type="button" role="tab" style="border-bottom: 3px solid transparent;" onclick="this.classList.remove('text-secondary');">
                                <i class="bi bi-gear me-2"></i>Profil
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="card-body p-4 p-md-5 bg-light">
                    <div class="tab-content" id="pills-tabContent">
                        
                        <!-- TAB PESANAN AKTIF -->
                        <div class="tab-pane fade show active" id="pills-aktif" role="tabpanel">
                            <h5 class="fw-bold mb-4">Pesanan Sedang Berlangsung</h5>
                            @forelse($pesananAktif as $pesanan)
                                <div class="card border-0 shadow-sm mb-4 rounded-4">
                                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3 border-bottom-0 rounded-top-4">
                                        <div>
                                            <h6 class="fw-bold mb-0 text-dark">Order #{{ $pesanan->created_at->format('YmdHi') }}</h6>
                                            <small class="text-muted">{{ $pesanan->created_at->translatedFormat('l, d F Y - H:i') }} WIB</small>
                                        </div>
                                        <div>
                                            @if($pesanan->status === 'pending')
                                                <span class="badge bg-warning text-dark px-3 py-2 rounded-pill"><i class="bi bi-hourglass-split me-1"></i> Menunggu Diproses</span>
                                            @elseif($pesanan->status === 'processing')
                                                <span class="badge bg-primary px-3 py-2 rounded-pill"><i class="bi bi-fire me-1"></i> Sedang Dimasak</span>
                                            @elseif($pesanan->status === 'completed')
                                                <span class="badge bg-success px-3 py-2 rounded-pill"><i class="bi bi-check2-all me-1"></i> Selesai Dimasak</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="card-body bg-light bg-opacity-50">
                                        <div class="d-flex mb-3">
                                            <div class="me-3 text-primary"><i class="bi bi-geo-alt-fill"></i></div>
                                            <div>
                                                <h6 class="mb-0 fw-bold">{{ $pesanan->tipe_pesanan == 'takeaway' ? 'Bungkus / Takeaway' : 'Makan di Tempat' }}</h6>
                                                <small class="text-muted">{{ $pesanan->meja->nama_meja_atau_nomor ?? '-' }}</small>
                                            </div>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-borderless table-sm mb-0">
                                                <tbody>
                                                    @foreach($pesanan->detail_pesanan as $detail)
                                                        <tr>
                                                            <td class="text-muted" style="width: 30px;">{{ $detail->jumlah }}x</td>
                                                            <td class="fw-medium">{{ $detail->menu->nama_menu ?? 'Menu tidak ditemukan' }}</td>
                                                            <td class="text-end text-muted">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    @php
                                        $hasAction = $pesanan->pembayaran->status == 'unpaid' || ($pesanan->tipe_pesanan === 'dine_in' && $pesanan->id_meja);
                                    @endphp
                                    <div class="card-footer bg-white py-3 rounded-bottom-4">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="d-flex align-items-center">
                                                @if($pesanan->pembayaran->status == 'unpaid')
                                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger px-2 py-1"><i class="bi bi-x-circle me-1"></i> Belum Lunas</span>
                                                @else
                                                    <span class="badge bg-success bg-opacity-10 text-success border border-success px-2 py-1"><i class="bi bi-check-circle me-1"></i> Lunas</span>
                                                @endif
                                            </div>
                                            <div class="text-end">
                                                <p class="small text-muted mb-0">Total Tagihan ({{ $pesanan->detail_pesanan->sum('jumlah') }} Item)</p>
                                                @if($pesanan->discount_amount > 0)
                                                    <p class="small text-danger mb-0 text-decoration-line-through">Rp {{ number_format($pesanan->total, 0, ',', '.') }}</p>
                                                    <h5 class="fw-bold text-primary mb-0">Rp {{ number_format($pesanan->total - $pesanan->discount_amount, 0, ',', '.') }}</h5>
                                                @else
                                                    <h5 class="fw-bold text-primary mb-0">Rp {{ number_format($pesanan->total, 0, ',', '.') }}</h5>
                                                @endif
                                            </div>
                                        </div>
                                        @if($hasAction)
                                            <div class="d-flex justify-content-end gap-2 flex-wrap border-top pt-3 mt-3">
                                                @if($pesanan->pembayaran->status == 'unpaid')
                                                    <a href="/konsumen/checkout/{{ $pesanan->id }}" class="btn btn-success fw-bold px-4 rounded-pill">
                                                        Pilih Pembayaran <i class="bi bi-arrow-right ms-1"></i>
                                                    </a>
                                                @endif
                                                @if($pesanan->tipe_pesanan === 'dine_in' && $pesanan->id_meja)
                                                    <button onclick="callBell({{ $pesanan->id_meja }})" class="btn btn-outline-danger fw-bold px-4 rounded-pill">
                                                        <i class="bi bi-bell-fill me-1"></i> Panggil Pelayan
                                                    </button>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="text-center bg-white p-5 rounded-4 shadow-sm">
                                    <div class="d-inline-block bg-light text-muted p-4 rounded-circle mb-3">
                                        <i class="bi bi-cup-hot fs-1"></i>
                                    </div>
                                    <h5 class="fw-bold">Belum Ada Pesanan</h5>
                                    <p class="text-muted mb-4">Anda belum memesan makanan apa pun. Yuk lihat menu kami!</p>
                                    <a href="/katalog" class="btn btn-primary rounded-pill px-4 py-2 fw-bold">Lihat Katalog Menu</a>
                                </div>
                            @endforelse
                        </div>

                        <!-- TAB RIWAYAT -->
                        <div class="tab-pane fade" id="pills-riwayat" role="tabpanel">
                            <h5 class="fw-bold mb-4">Riwayat Pesanan Saya</h5>
                            @forelse($riwayat as $pesanan)
                                <div class="card border-0 shadow-sm mb-4 rounded-4">
                                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3 border-bottom-0 rounded-top-4">
                                        <div>
                                            <h6 class="fw-bold mb-0 text-dark">Order #{{ $pesanan->created_at->format('YmdHi') }}</h6>
                                            <small class="text-muted">{{ $pesanan->created_at->translatedFormat('l, d F Y - H:i') }} WIB</small>
                                        </div>
                                        <div>
                                            @if($pesanan->status === 'completed')
                                                <span class="badge bg-success bg-opacity-10 text-success border border-success px-3 py-2 rounded-pill"><i class="bi bi-check2-all me-1"></i> Selesai</span>
                                            @else
                                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger px-3 py-2 rounded-pill"><i class="bi bi-x-circle me-1"></i> Dibatalkan</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="card-body bg-light bg-opacity-50 border-top border-bottom">
                                        <p class="mb-1 text-muted small">Ringkasan Pesanan:</p>
                                        <p class="mb-0 fw-medium">
                                            @foreach($pesanan->detail_pesanan as $detail)
                                                {{ $detail->jumlah }}x {{ $detail->menu->nama_menu ?? 'Menu' }}@if(!$loop->last), @endif
                                            @endforeach
                                        </p>
                                        @if($pesanan->discount_amount > 0)
                                            <h6 class="fw-bold text-dark mt-2 mb-0">Total ({{ $pesanan->detail_pesanan->sum('jumlah') }} Item): <span class="text-muted text-decoration-line-through fw-normal small">Rp {{ number_format($pesanan->total, 0, ',', '.') }}</span> Rp {{ number_format($pesanan->total - $pesanan->discount_amount, 0, ',', '.') }}</h6>
                                        @else
                                            <h6 class="fw-bold text-dark mt-2 mb-0">Total ({{ $pesanan->detail_pesanan->sum('jumlah') }} Item): Rp {{ number_format($pesanan->total, 0, ',', '.') }}</h6>
                                        @endif
                                    </div>
                                    
                                    @if($pesanan->status === 'completed')
                                        <div class="card-footer bg-white py-3 rounded-bottom-4">
                                            @if(!$pesanan->rating)
                                                <form action="/konsumen/rating/store" method="POST" class="bg-primary bg-opacity-10 p-3 rounded-3">
                                                    @csrf
                                                    <input type="hidden" name="id_pesanan" value="{{ $pesanan->id }}">
                                                    <label class="small fw-bold text-primary mb-2 d-block"><i class="bi bi-star-fill text-warning me-1"></i> Berikan Penilaian untuk Pesanan Ini</label>
                                                    <div class="row g-2">
                                                        <div class="col-md-4">
                                                            <select name="rating" class="form-select border-primary bg-white text-dark" required>
                                                                <option value="" class="text-muted">Pilih Bintang...</option>
                                                                <option value="5">⭐⭐⭐⭐⭐ Sangat Bagus</option>
                                                                <option value="4">⭐⭐⭐⭐ Bagus</option>
                                                                <option value="3">⭐⭐⭐ Cukup</option>
                                                                <option value="2">⭐⭐ Kurang</option>
                                                                <option value="1">⭐ Kecewa</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <input type="text" name="komentar" class="form-control border-primary bg-white" placeholder="Ulasan Anda (Opsional)">
                                                        </div>
                                                        <div class="col-md-2">
                                                            <button type="submit" class="btn btn-primary w-100 fw-bold">Kirim</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            @else
                                                <div class="d-flex justify-content-between align-items-center bg-light p-3 rounded-3 border">
                                                    <div>
                                                        <small class="d-block fw-bold mb-1 text-muted">Penilaian Anda:</small>
                                                        <div class="text-warning mb-1">
                                                            @for($i=1; $i<=5; $i++)
                                                                <i class="bi bi-star{{ $i <= $pesanan->rating->rating ? '-fill' : '' }}"></i>
                                                            @endfor
                                                        </div>
                                                        <p class="mb-0 text-dark small fst-italic">"{{ $pesanan->rating->komentar ?? 'Tidak ada ulasan tertulis' }}"</p>
                                                    </div>
                                                    @if($pesanan->rating->balasan_admin)
                                                        <div class="bg-white border p-2 rounded small w-50">
                                                            <strong class="text-primary d-block mb-1"><i class="bi bi-reply-fill"></i> Balasan Admin:</strong>
                                                            {{ $pesanan->rating->balasan_admin }}
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="text-center bg-white p-5 rounded-4 shadow-sm">
                                    <div class="d-inline-block bg-light text-muted p-4 rounded-circle mb-3">
                                        <i class="bi bi-clock-history fs-1"></i>
                                    </div>
                                    <h5 class="fw-bold">Belum Ada Riwayat</h5>
                                    <p class="text-muted mb-0">Riwayat pesanan Anda yang sudah selesai akan tampil di sini.</p>
                                </div>
                            @endforelse
                        </div>

                        <!-- TAB PROFIL -->
                        <div class="tab-pane fade" id="pills-profil" role="tabpanel">
                            <div class="row">
                                <div class="col-md-8 mx-auto">
                                    <div class="card border-0 shadow-sm rounded-4">
                                        <div class="card-body p-4 p-md-5">
                                            <h5 class="fw-bold mb-4 text-center">Informasi Akun</h5>
                                            
                                            <form action="/konsumen/profil/update" method="POST" enctype="multipart/form-data">
                                                @csrf
                                                <div class="mb-5 text-center">
                                                    <div class="position-relative d-inline-block shadow-sm rounded-circle" style="width: 120px; height: 120px;">
                                                        <img id="foto-preview" src="{{ $user->foto ? asset('uploads/profil/' . $user->foto) : 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&background=random&color=fff&size=120' }}" alt="Foto Profil" class="rounded-circle border border-4 border-white" style="width: 120px; height: 120px; object-fit: cover; cursor: pointer;" onclick="document.getElementById('foto-input').click();">
                                                        <div class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center border border-2 border-white" style="width: 35px; height: 35px; cursor: pointer; transform: translate(10%, 10%);" onclick="document.getElementById('foto-input').click();">
                                                            <i class="bi bi-camera-fill"></i>
                                                        </div>
                                                    </div>
                                                    <input type="file" id="foto-input" name="foto" class="d-none" accept="image/*" onchange="previewFoto(event)">
                                                    <small class="text-muted d-block mt-3">Klik ikon kamera untuk mengganti foto (JPG/PNG). Maksimal 2MB.</small>
                                                </div>
                                                <div class="mb-4">
                                                    <label class="form-label text-muted small fw-bold text-uppercase">Nama Lengkap</label>
                                                    <input type="text" name="name" class="form-control form-control-lg bg-light" value="{{ $user->name }}" required>
                                                </div>
                                                <div class="mb-4">
                                                    <label class="form-label text-muted small fw-bold text-uppercase">Email</label>
                                                    <input type="email" name="email" class="form-control form-control-lg bg-light" value="{{ $user->email }}" required>
                                                </div>
                                                <div class="mb-4">
                                                    <label class="form-label text-muted small fw-bold text-uppercase">Nomor HP</label>
                                                    <div class="input-group input-group-lg">
                                                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-telephone text-muted"></i></span>
                                                        <input type="text" name="no_hp" class="form-control bg-light border-start-0" value="{{ $user->no_hp }}" placeholder="08xxxxxxxx">
                                                    </div>
                                                </div>
                                                <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold rounded-pill mb-4 shadow-sm">
                                                    Simpan Perubahan
                                                </button>
                                            </form>
                                            
                                            <hr class="mb-4 border-light">
                                            
                                            <div class="text-center">
                                                <p class="text-muted small mb-3">Ingin keluar dari akun ini?</p>
                                                <form action="{{ route('logout') }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-danger px-4 rounded-pill fw-bold">
                                                        <i class="bi bi-box-arrow-right me-1"></i> Keluar
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Styling Tabs */
    .nav-pills .nav-link {
        color: #6c757d;
        background: transparent;
        transition: 0.3s;
    }
    .nav-pills .nav-link:hover {
        color: #0d6efd;
    }
    .nav-pills .nav-link.active {
        color: #0d6efd !important;
        background: transparent;
        border-bottom: 3px solid #0d6efd !important;
    }
    .rounded-4 {
        border-radius: 1rem !important;
    }
    .form-control:focus {
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1);
        border-color: #86b7fe;
    }
</style>

<script>
    function previewFoto(event) {
        const input = event.target;
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('foto-preview').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function callBell(id_meja) {
        if(confirm('Panggil pelayan ke meja Anda?')) {
            fetch('/konsumen/call-bell', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ id_meja: id_meja })
            })
            .then(res => res.json())
            .then(data => {
                if(data.error) {
                    alert(data.error);
                } else {
                    alert(data.message);
                }
            })
            .catch(err => {
                alert('Terjadi kesalahan koneksi.');
            });
        }
    }

    // Live Order Tracking: Reload active tab automatically if there are active orders
    @if(count($pesananAktif) > 0)
    setInterval(() => {
        // Only refresh if 'Pesanan Aktif' tab is currently visible
        let activeTab = document.querySelector('#pills-aktif');
        if (activeTab.classList.contains('active')) {
            // Optional: Fetch only the html of this tab or just reload
            location.reload();
        }
    }, 30000); // 30 seconds
    @endif
</script>
@endsection