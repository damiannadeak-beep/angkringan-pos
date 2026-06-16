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

                            <!-- Kolom AI API Key -->
                            <div class="col-md-6">
                                <h6 class="fw-bold mb-3">Sistem Verifikasi Otomatis <span class="badge bg-secondary">Opsional</span></h6>
                                <p class="text-muted small">Masukkan API Key agar sistem dapat memvalidasi struk transfer pelanggan secara otomatis.</p>
                                
                                <div class="mb-3">
                                    <label class="form-label fw-bold">API Key (Gemini)</label>
                                    <input type="text" class="form-control" name="gemini_api_key" value="{{ $settings['gemini_api_key'] ?? '' }}" placeholder="AIzaSyB...">
                                    <div class="form-text">Dapatkan gratis di <a href="https://aistudio.google.com/" target="_blank">Google AI Studio</a>.</div>
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
        <!-- Kolom Pengaturan Absensi -->
        <div class="col-12 mt-4">
            <div class="card admin-card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-calendar-check me-2 text-warning"></i>Pengaturan Absensi & Shift</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.absensi') }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Jam Kerja Shift Pagi</label>
                                <div class="input-group">
                                    <span class="input-group-text">Mulai</span>
                                    <input type="time" class="form-control" name="shift_pagi_start" value="{{ $settings['shift_pagi_start'] ?? '08:00' }}" required>
                                    <span class="input-group-text">Selesai</span>
                                    <input type="time" class="form-control" name="shift_pagi_end" value="{{ $settings['shift_pagi_end'] ?? '17:00' }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Jam Kerja Shift Malam</label>
                                <div class="input-group">
                                    <span class="input-group-text">Mulai</span>
                                    <input type="time" class="form-control" name="shift_malam_start" value="{{ $settings['shift_malam_start'] ?? '17:00' }}" required>
                                    <span class="input-group-text">Selesai</span>
                                    <input type="time" class="form-control" name="shift_malam_end" value="{{ $settings['shift_malam_end'] ?? '00:00' }}" required>
                                </div>
                            </div>
                            <div class="col-md-12 mt-3">
                                <label class="form-label fw-bold">Toleransi Terlambat (Menit)</label>
                                <input type="number" class="form-control w-50" name="toleransi_terlambat" value="{{ $settings['toleransi_terlambat'] ?? '15' }}" min="0" required>
                                <div class="form-text">Batas maksimal dari jam mulai shift. Jika melebihi ini maka statusnya "Terlambat".</div>
                            </div>
                        </div>
                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-warning fw-bold px-4">Simpan Pengaturan Absensi</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Kolom Pengaturan Printer Thermal -->
        <div class="col-12 mt-4">
            <div class="card admin-card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-printer me-2 text-info"></i>Pengaturan Printer Thermal (ESC/POS)</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.printer') }}" method="POST">
                        @csrf
                        <div class="row g-4">
                            <div class="col-md-6">
                                <h6 class="fw-bold mb-3">Koneksi Jaringan (Network)</h6>
                                <p class="text-muted small">Masukkan IP Address printer thermal Anda yang terhubung dalam satu jaringan Wi-Fi/LAN dengan server ini.</p>
                                
                                <div class="mb-3">
                                    <label class="form-label fw-bold">IP Address Printer</label>
                                    <input type="text" class="form-control" name="printer_ip" value="{{ $settings['printer_ip'] ?? '' }}" placeholder="Contoh: 192.168.1.100">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Port</label>
                                    <input type="text" class="form-control" name="printer_port" value="{{ $settings['printer_port'] ?? '9100' }}" placeholder="Default: 9100">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <h6 class="fw-bold mb-3">Status Fungsionalitas</h6>
                                <div class="form-check form-switch mb-3 mt-3">
                                    <input class="form-check-input" type="checkbox" role="switch" id="printerActiveSwitch" name="printer_active" value="1" {{ isset($settings['printer_active']) && $settings['printer_active'] == '1' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="printerActiveSwitch">Aktifkan Fitur Cetak Thermal ESC/POS</label>
                                    <div class="form-text">Jika dimatikan, fitur cetak thermal akan disembunyikan dari layar kasir.</div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-info text-white w-100 fw-bold">Simpan Pengaturan Printer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- Kolom Pengaturan Lokasi -->
        <div class="col-12 mt-4">
            <div class="card admin-card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-geo-alt me-2 text-primary"></i>Pengaturan Halaman Lokasi</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.lokasi') }}" method="POST">
                        @csrf
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Judul Halaman Lokasi</label>
                                    <input type="text" class="form-control" name="lokasi_judul" value="{{ $settings['lokasi_judul'] ?? 'Lokasi Angkringan Kami' }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Deskripsi Halaman</label>
                                    <textarea class="form-control" name="lokasi_deskripsi" rows="3">{{ $settings['lokasi_deskripsi'] ?? 'Kami berlokasi di pusat kota, mudah dijangkau, dan menawarkan suasana santai untuk menikmati hidangan tradisional.' }}</textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Nama Tempat/Jalan</label>
                                    <input type="text" class="form-control" name="lokasi_utama_nama" value="{{ $settings['lokasi_utama_nama'] ?? 'Rusunawa Politeknik Negeri Bengkalis (Polbeng)' }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Panduan Menuju Lokasi</label>
                                    <textarea class="form-control" name="lokasi_panduan" rows="3">{{ $settings['lokasi_panduan'] ?? 'Masuk dari gerbang utama Polbeng, lurus ikuti jalan utama, lalu belok ke arah asrama mahasiswa (Rusunawa). Gerobak angkringan kami berada tepat di area titik kumpul Rusunawa.' }}</textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Alamat Lengkap</label>
                                    <textarea class="form-control" name="lokasi_utama_alamat" rows="2">{{ $settings['lokasi_utama_alamat'] ?? "Jl. Bathin Alam, Sungai Alam, Kec. Bengkalis\nKab. Bengkalis, Riau 28711" }}</textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Jam Operasional (Teks)</label>
                                    <input type="text" class="form-control" name="lokasi_jam_operasional" value="{{ $settings['lokasi_jam_operasional'] ?? 'Buka Setiap Hari: 17.00 - 01.00 WIB' }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Link Google Maps (Iframe SRC) atau URL biasa</label>
                                    <textarea class="form-control" name="lokasi_gmaps_url" rows="2" placeholder="https://maps.google.com/...">{{ $settings['lokasi_gmaps_url'] ?? 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15814.773827415443!2d110.36603099999999!3d-7.789182!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e7a57a3e791b8d5%3A0xc3f8e5d26ff8f8a!2sYogyakarta!5e0!3m2!1sen!2sid!4v1700000000000!5m2!1sen!2sid' }}</textarea>
                                    <div class="form-text">Bisa menggunakan URL biasa atau src dari embed peta (Google Maps).</div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary w-100 fw-bold">Simpan Pengaturan Lokasi</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Kolom Pengaturan Absensi Geolocation -->
        <div class="col-12 mt-4">
            <div class="card admin-card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-geo-fill me-2 text-danger"></i>Pengaturan Absensi Geolocation (Lokasi Warung)</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.absensi') }}" method="POST">
                        @csrf
                        <div class="row g-4">
                            <div class="col-md-6">
                                <h6 class="fw-bold mb-3">Koordinat Pusat Warung</h6>
                                <p class="text-muted small mb-3">Tentukan titik kordinat (Latitude & Longitude) warung. Kasir hanya bisa melakukan absensi jika berada dalam radius tertentu dari titik ini.</p>
                                
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Latitude</label>
                                    <input type="text" class="form-control" id="warung_latitude" name="warung_latitude" value="{{ $settings['warung_latitude'] ?? '' }}" placeholder="Contoh: -6.200000">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Longitude</label>
                                    <input type="text" class="form-control" id="warung_longitude" name="warung_longitude" value="{{ $settings['warung_longitude'] ?? '' }}" placeholder="Contoh: 106.816666">
                                </div>
                                <button type="button" class="btn btn-outline-danger btn-sm fw-bold mb-2" onclick="getCurrentLocation()">
                                    <i class="bi bi-crosshair me-1"></i> Ambil Lokasi Saya Saat Ini
                                </button>
                                <div class="form-text text-muted small" id="location-status">Klik tombol di atas jika Anda sedang berada tepat di warung sekarang.</div>
                            </div>

                            <div class="col-md-6">
                                <h6 class="fw-bold mb-3">Radius Absensi</h6>
                                <p class="text-muted small">Tentukan batas maksimal jarak (dalam meter) agar kasir bisa absen. Standarnya adalah 5 meter sesuai instruksi.</p>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Radius Maksimal (Meter)</label>
                                    <input type="number" class="form-control" name="absensi_radius_meter" value="{{ $settings['absensi_radius_meter'] ?? '5' }}" min="1" max="1000">
                                    <div class="form-text">Semakin kecil angkanya, semakin ketat sistem absensinya. Disarankan minimal 5-15 meter karena akurasi GPS HP bisa sedikit meleset.</div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-danger w-100 fw-bold">Simpan Pengaturan Absensi</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Kolom Pengaturan Kontak -->
        <div class="col-12 mt-4">
            <div class="card admin-card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-person-lines-fill me-2 text-warning"></i>Pengaturan Halaman Kontak</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.kontak') }}" method="POST">
                        @csrf
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Nomor WhatsApp</label>
                                <input type="text" class="form-control" name="kontak_wa" value="{{ $settings['kontak_wa'] ?? '+62 812-3456-7890' }}">
                                <div class="form-text">Format yang disarankan: +62 8xx-xxxx-xxxx</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Alamat Email</label>
                                <input type="email" class="form-control" name="kontak_email" value="{{ $settings['kontak_email'] ?? 'halo@angkringan.com' }}">
                            </div>
                        </div>

                        <hr class="my-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold mb-0">Daftar Sosial Media Tambahan</h6>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addSosmedRow()">
                                <i class="bi bi-plus-circle me-1"></i>Tambah Sosial Media
                            </button>
                        </div>
                        <div id="sosmed-container">
                            @php
                                $sosmedList = json_decode($settings['kontak_sosmed_dynamic'] ?? '[]', true);
                                if (empty($sosmedList) && (!empty($settings['kontak_ig']) || !empty($settings['kontak_tiktok']))) {
                                    if (!empty($settings['kontak_ig'])) {
                                        $sosmedList[] = [
                                            'platform' => 'Instagram',
                                            'label' => $settings['kontak_ig'],
                                            'url' => 'https://instagram.com/' . ltrim($settings['kontak_ig'], '@'),
                                            'icon' => 'bi-instagram'
                                        ];
                                    }
                                    if (!empty($settings['kontak_tiktok'])) {
                                        $sosmedList[] = [
                                            'platform' => 'TikTok',
                                            'label' => $settings['kontak_tiktok'],
                                            'url' => 'https://tiktok.com/@' . ltrim($settings['kontak_tiktok'], '@'),
                                            'icon' => 'bi-tiktok'
                                        ];
                                    }
                                }
                            @endphp

                            @foreach($sosmedList as $index => $sosmed)
                            <div class="row g-2 mb-3 align-items-end sosmed-row">
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">Platform</label>
                                    <select class="form-select sosmed-platform" name="sosmed[platform][]" onchange="updateSosmedIcon(this)">
                                        <option value="Instagram" data-icon="bi-instagram" {{ $sosmed['platform'] == 'Instagram' ? 'selected' : '' }}>Instagram</option>
                                        <option value="TikTok" data-icon="bi-tiktok" {{ $sosmed['platform'] == 'TikTok' ? 'selected' : '' }}>TikTok</option>
                                        <option value="Facebook" data-icon="bi-facebook" {{ $sosmed['platform'] == 'Facebook' ? 'selected' : '' }}>Facebook</option>
                                        <option value="X/Twitter" data-icon="bi-twitter-x" {{ $sosmed['platform'] == 'X/Twitter' ? 'selected' : '' }}>X/Twitter</option>
                                        <option value="YouTube" data-icon="bi-youtube" {{ $sosmed['platform'] == 'YouTube' ? 'selected' : '' }}>YouTube</option>
                                        <option value="Lainnya" data-icon="bi-link-45deg" {{ $sosmed['platform'] == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">Label / Username</label>
                                    <input type="text" class="form-control" name="sosmed[label][]" value="{{ $sosmed['label'] }}" required>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label small fw-bold">URL Target</label>
                                    <input type="url" class="form-control" name="sosmed[url][]" value="{{ $sosmed['url'] }}" required>
                                </div>
                                <div class="col-md-1 text-end">
                                    <input type="hidden" name="sosmed[icon][]" class="sosmed-icon-input" value="{{ $sosmed['icon'] ?? 'bi-link-45deg' }}">
                                    <button type="button" class="btn btn-outline-danger w-100" onclick="removeSosmedRow(this)"><i class="bi bi-trash"></i></button>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="mt-4">
                            <button type="submit" class="btn btn-warning w-100 fw-bold">Simpan Pengaturan Kontak</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- Kolom Backup Database -->
        <div class="col-12 mt-4">
            <div class="card admin-card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-database-down me-2 text-primary"></i>Backup Database</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">Unduh salinan lengkap database sistem (transaksi, menu, dll) dalam format <code>.sql</code> untuk mengamankan data Anda.</p>
                    <a href="{{ route('admin.backup') }}" class="btn btn-primary fw-bold">
                        <i class="bi bi-download me-2"></i> Download Backup SQL
                    </a>
                    @if($errors->any())
                        <div class="text-danger mt-3 small"><i class="bi bi-exclamation-triangle"></i> {{ $errors->first() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function getCurrentLocation() {
    const status = document.getElementById('location-status');
    const latInput = document.getElementById('warung_latitude');
    const lngInput = document.getElementById('warung_longitude');

    if (!navigator.geolocation) {
        status.innerHTML = "<span class='text-danger'>Geolocation tidak didukung oleh browser Anda.</span>";
        return;
    }

    status.innerHTML = "<span class='text-primary'>Mencari lokasi Anda...</span>";

    navigator.geolocation.getCurrentPosition(
        (position) => {
            latInput.value = position.coords.latitude;
            lngInput.value = position.coords.longitude;
            status.innerHTML = "<span class='text-success'>Lokasi berhasil didapatkan! (Akurasi: " + Math.round(position.coords.accuracy) + " meter)</span>";
        },
        (error) => {
            let msg = "";
            switch(error.code) {
                case error.PERMISSION_DENIED: msg = "Anda menolak permintaan akses lokasi."; break;
                case error.POSITION_UNAVAILABLE: msg = "Informasi lokasi tidak tersedia."; break;
                case error.TIMEOUT: msg = "Waktu pencarian lokasi habis."; break;
                default: msg = "Terjadi kesalahan yang tidak diketahui."; break;
            }
            status.innerHTML = "<span class='text-danger'>" + msg + "</span>";
        },
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
    );
}

function addSosmedRow() {
    const container = document.getElementById('sosmed-container');
    const row = document.createElement('div');
    row.className = 'row g-2 mb-3 align-items-end sosmed-row';
    row.innerHTML = `
        <div class="col-md-3">
            <label class="form-label small fw-bold">Platform</label>
            <select class="form-select sosmed-platform" name="sosmed[platform][]" onchange="updateSosmedIcon(this)">
                <option value="Instagram" data-icon="bi-instagram">Instagram</option>
                <option value="TikTok" data-icon="bi-tiktok">TikTok</option>
                <option value="Facebook" data-icon="bi-facebook">Facebook</option>
                <option value="X/Twitter" data-icon="bi-twitter-x">X/Twitter</option>
                <option value="YouTube" data-icon="bi-youtube">YouTube</option>
                <option value="Lainnya" data-icon="bi-link-45deg" selected>Lainnya</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-bold">Label / Username</label>
            <input type="text" class="form-control" name="sosmed[label][]" placeholder="@username" required>
        </div>
        <div class="col-md-5">
            <label class="form-label small fw-bold">URL Target</label>
            <input type="url" class="form-control" name="sosmed[url][]" placeholder="https://" required>
        </div>
        <div class="col-md-1 text-end">
            <input type="hidden" name="sosmed[icon][]" class="sosmed-icon-input" value="bi-link-45deg">
            <button type="button" class="btn btn-outline-danger w-100" onclick="removeSosmedRow(this)"><i class="bi bi-trash"></i></button>
        </div>
    `;
    container.appendChild(row);
}

function removeSosmedRow(button) {
    button.closest('.sosmed-row').remove();
}

function updateSosmedIcon(select) {
    const iconInput = select.closest('.sosmed-row').querySelector('.sosmed-icon-input');
    const selectedOption = select.options[select.selectedIndex];
    iconInput.value = selectedOption.getAttribute('data-icon');
}
</script>
@endsection
