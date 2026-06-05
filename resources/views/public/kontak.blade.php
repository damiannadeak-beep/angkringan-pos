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
                        
                        <div class="d-flex align-items-center mb-4">
                            <i class="bi bi-whatsapp fs-3 text-success me-3"></i>
                            <div>
                                <small class="text-muted d-block">WhatsApp (Admin)</small>
                                <span class="fw-bold">+62 812-3456-7890</span>
                            </div>
                        </div>

                        <div class="d-flex align-items-center mb-4">
                            <i class="bi bi-envelope fs-3 text-primary me-3"></i>
                            <div>
                                <small class="text-muted d-block">Email</small>
                                <span class="fw-bold">halo@angkringan.com</span>
                            </div>
                        </div>

                        <div class="d-flex align-items-center">
                            <i class="bi bi-instagram fs-3 text-danger me-3"></i>
                            <div>
                                <small class="text-muted d-block">Instagram</small>
                                <span class="fw-bold">@angkringan.pos</span>
                            </div>
                        </div>
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