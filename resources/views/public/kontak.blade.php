@extends('layouts.app')

@section('content')
<div class="container mt-4 mb-5 pb-5">
    <div class="text-center mb-5">
        <h2 class="fw-bold">Hubungi Kami</h2>
        <p class="text-muted fs-5">Punya pertanyaan, kritik, atau saran? Kami siap mendengarkan.</p>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm p-4">
                <div class="row g-4">
                    
                    <div class="col-md-5 border-end">
                        <h5 class="fw-bold mb-4">Informasi Kontak</h5>
                        
                        @php
                            $waRaw = \App\Models\Setting::getVal('kontak_wa') ?? '+62 812-3456-7890';
                            $waClean = preg_replace('/[^0-9]/', '', $waRaw);
                            if (str_starts_with($waClean, '0')) {
                                $waClean = '62' . substr($waClean, 1);
                            }
                            
                            $emailRaw = \App\Models\Setting::getVal('kontak_email') ?? 'halo@angkringan.com';

                            $sosmedDynamic = json_decode(\App\Models\Setting::getVal('kontak_sosmed_dynamic') ?? '[]', true);
                            if (empty($sosmedDynamic)) {
                                $igRaw = \App\Models\Setting::getVal('kontak_ig');
                                $tiktokRaw = \App\Models\Setting::getVal('kontak_tiktok');
                                if (!empty($igRaw)) {
                                    $sosmedDynamic[] = ['platform' => 'Instagram', 'url' => 'https://instagram.com/'.ltrim($igRaw, '@'), 'label' => $igRaw, 'icon' => 'bi-instagram'];
                                }
                                if (!empty($tiktokRaw)) {
                                    $sosmedDynamic[] = ['platform' => 'TikTok', 'url' => 'https://tiktok.com/@'.ltrim($tiktokRaw, '@'), 'label' => $tiktokRaw, 'icon' => 'bi-tiktok'];
                                }
                            }
                        @endphp

                        <a href="https://wa.me/{{ $waClean }}" target="_blank" class="text-decoration-none text-dark">
                            <div class="d-flex align-items-center mb-4 item-kontak">
                                <i class="bi bi-whatsapp fs-3 text-success me-3"></i>
                                <div>
                                    <small class="text-muted d-block">WhatsApp (Admin)</small>
                                    <span class="fw-bold">{{ $waRaw }}</span>
                                </div>
                            </div>
                        </a>

                        <a href="mailto:{{ $emailRaw }}" class="text-decoration-none text-dark">
                            <div class="d-flex align-items-center mb-4 item-kontak">
                                <i class="bi bi-envelope fs-3 text-primary me-3"></i>
                                <div>
                                    <small class="text-muted d-block">Email</small>
                                    <span class="fw-bold">{{ $emailRaw }}</span>
                                </div>
                            </div>
                        </a>

                        @foreach($sosmedDynamic as $sosmed)
                            <a href="{{ $sosmed['url'] }}" target="_blank" class="text-decoration-none text-dark">
                                <div class="d-flex align-items-center item-kontak mt-4">
                                    <i class="bi {{ $sosmed['icon'] ?? 'bi-link-45deg' }} fs-3 text-secondary me-3"></i>
                                    <div>
                                        <small class="text-muted d-block">{{ $sosmed['platform'] }}</small>
                                        <span class="fw-bold">{{ $sosmed['label'] }}</span>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>

                    <div class="col-md-7 ps-md-4">
                        <h5 class="fw-bold mb-4">Kirim Pesan</h5>
                        <form>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Nama Lengkap</label>
                                <input type="text" class="form-control" placeholder="Masukkan nama Anda">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Alamat Email</label>
                                <input type="email" class="form-control" placeholder="nama@email.com">
                            </div>
                            <div class="mb-4">
                                <label class="form-label small fw-bold">Pesan Anda</label>
                                <textarea class="form-control" rows="4" placeholder="Ketik pesan Anda di sini..."></textarea>
                            </div>
                            <button type="button" class="btn btn-primary w-100 fw-bold py-2" onclick="alert('Terima kasih! Pesan Anda (simulasi) telah terkirim.')">
                                Kirim Pesan Sekarang
                            </button>
                        </form>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>
@endsection