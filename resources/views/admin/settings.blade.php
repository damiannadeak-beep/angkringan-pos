@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="bi bi-gear me-2"></i>Pengaturan</h2>
            <p class="text-muted mb-0">Kelola informasi warung dan keamanan akun Anda.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Kolom Profil Warung -->
        <div class="col-lg-6">
            <div class="card admin-card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-shop me-2 text-primary"></i>Profil Warung & Struk</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.profile') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Warung</label>
                            <input type="text" class="form-control" name="store_name" value="{{ $settings['store_name'] ?? 'Angkringan POS' }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Alamat Lengkap</label>
                            <textarea class="form-control" name="store_address" rows="2" required>{{ $settings['store_address'] ?? '' }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nomor Telepon / WhatsApp</label>
                            <input type="text" class="form-control" name="store_phone" value="{{ $settings['store_phone'] ?? '' }}" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">Pesan Bawah Struk (Footer)</label>
                            <textarea class="form-control" name="receipt_footer" rows="2" placeholder="Terima kasih atas kunjungan Anda!">{{ str_replace('\n', "\n", $settings['receipt_footer'] ?? '') }}</textarea>
                            <div class="form-text">Bisa menggunakan beberapa baris. Teks ini akan dicetak di bagian paling bawah struk kasir.</div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 fw-bold">Simpan Profil Warung</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Kolom Keamanan Akun -->
        <div class="col-lg-6">
            <div class="card admin-card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-shield-lock me-2 text-danger"></i>Keamanan Akun</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.security') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Admin</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', auth()->user()->name) }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email', auth()->user()->email) }}" required>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <hr class="my-4">
                        <h6 class="fw-bold mb-3">Ubah Password (Opsional)</h6>
                        <p class="small text-muted mb-3">Kosongkan bagian ini jika Anda tidak ingin mengubah password.</p>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Password Saat Ini</label>
                            <input type="password" class="form-control @error('current_password') is-invalid @enderror" name="current_password">
                            @error('current_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Password Baru</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" minlength="8">
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">Konfirmasi Password Baru</label>
                            <input type="password" class="form-control" name="password_confirmation" minlength="8">
                        </div>
                        
                        <button type="submit" class="btn btn-danger w-100 fw-bold">Perbarui Keamanan</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Kolom Pengaturan Pembayaran -->
        <div class="col-12 mt-4">
            <div class="card admin-card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-credit-card me-2 text-success"></i>Pengaturan Pembayaran & QRIS</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.payment') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-4">
                            <!-- Kolom Upload QRIS -->
                            <div class="col-md-6 border-end">
                                <h6 class="fw-bold mb-3">QRIS Statis Warung</h6>
                                <p class="text-muted small">Unggah gambar QRIS Statis agar kasir bisa menampilkannya di layar saat pelanggan ingin membayar menggunakan QRIS (E-Wallet/M-Banking).</p>
                                
                                <div class="mb-3">
                                    @if(isset($settings['qris_image']) && $settings['qris_image'])
                                        <div class="mb-3">
                                            <img src="{{ asset('storage/'.$settings['qris_image']) }}" alt="QRIS Warung" class="img-thumbnail" style="max-height: 200px;">
                                        </div>
                                    @endif
                                    <label class="form-label fw-bold">Unggah Barcode QRIS</label>
                                    <input class="form-control" type="file" name="qris_image" accept="image/jpeg, image/png, image/jpg">
                                    <div class="form-text">Maksimal 2MB. Format: JPG, PNG.</div>
                                </div>
                            </div>

                            <!-- Kolom Midtrans -->
                            <div class="col-md-6">
                                <h6 class="fw-bold mb-3">API Key Midtrans <span class="badge bg-secondary">Opsional</span></h6>
                                <p class="text-muted small">Masukkan API Key Midtrans Anda jika ingin pesanan pelanggan (via HP) terverifikasi secara otomatis (tanpa kasir harus mengecek manual).</p>
                                
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Server Key</label>
                                    <input type="text" class="form-control" name="midtrans_server_key" value="{{ $settings['midtrans_server_key'] ?? '' }}" placeholder="SB-Mid-server-xxx">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Client Key</label>
                                    <input type="text" class="form-control" name="midtrans_client_key" value="{{ $settings['midtrans_client_key'] ?? '' }}" placeholder="SB-Mid-client-xxx">
                                </div>
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" role="switch" id="midtransProductionSwitch" name="midtrans_is_production" value="1" {{ isset($settings['midtrans_is_production']) && $settings['midtrans_is_production'] == '1' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="midtransProductionSwitch">Gunakan Mode Production (Live)</label>
                                    <div class="form-text">Biarkan mati jika masih menggunakan akun Sandbox (Testing).</div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-success w-100 fw-bold">Simpan Pengaturan Pembayaran</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
