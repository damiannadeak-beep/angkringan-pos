@extends('layouts.kasir')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="fw-bold text-accent"><i class="bi bi-cart-plus me-2"></i>Daftar Permintaan Belanja</h4>
            <p class="text-muted">Beri tahu Pemilik (Admin) barang atau bahan baku apa saja yang mulai menipis dan perlu dibeli.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            Terdapat kesalahan pada input Anda.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Kolom Kiri: Form -->
        <div class="col-md-4">
            <div class="card kasir-card h-100">
                <div class="card-header bg-transparent border-bottom pt-4 pb-3 px-4">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-plus-circle-fill me-2 text-primary"></i>Buat Permintaan</h5>
                </div>
                <div class="card-body px-4">
                    <form action="{{ route('kasir.permintaan.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Barang/Bahan <span class="text-danger">*</span></label>
                            <!-- Kita bisa gunakan datalist agar kasir bisa ketik bebas atau pilih dari daftar bahan -->
                            <input class="form-control" list="bahanOptions" name="nama_barang" placeholder="Ketik nama barang..." required>
                            <datalist id="bahanOptions">
                                @foreach($bahans as $bahan)
                                    <option value="{{ $bahan->nama_bahan }}">
                                @endforeach
                            </datalist>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Sisa Stok Saat Ini</label>
                            <input type="text" class="form-control" name="sisa_stok" placeholder="Contoh: 2 kaleng">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Jumlah yang Diminta <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="jumlah_diminta" placeholder="Contoh: 20 kaleng" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">Catatan Opsional</label>
                            <textarea class="form-control" name="catatan" rows="3" placeholder="Merek tertentu, ukuran, dll..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 fw-bold shadow-sm">
                            <i class="bi bi-send me-1"></i> Kirim Permintaan
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Riwayat -->
        <div class="col-md-8">
            <div class="card kasir-card h-100">
                <div class="card-header bg-transparent border-bottom pt-4 pb-3 px-4">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-clock-history me-2 text-info"></i>Riwayat Permintaan Saya</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Tanggal</th>
                                    <th>Barang</th>
                                    <th>Minta</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($permintaans as $req)
                                    <tr>
                                        <td class="ps-4 text-muted small">{{ $req->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <span class="fw-bold">{{ $req->nama_barang }}</span>
                                            @if($req->sisa_stok)
                                                <div class="small text-muted">Sisa: {{ $req->sisa_stok }}</div>
                                            @endif
                                        </td>
                                        <td class="fw-medium">{{ $req->jumlah_diminta }}</td>
                                        <td>
                                            @if($req->status === 'menunggu')
                                                <span class="badge bg-warning text-dark rounded-pill"><i class="bi bi-hourglass-split me-1"></i>Menunggu</span>
                                            @elseif($req->status === 'sudah_dibeli')
                                                <span class="badge bg-success rounded-pill"><i class="bi bi-check-circle me-1"></i>Sudah Dibeli</span>
                                            @else
                                                <span class="badge bg-danger rounded-pill"><i class="bi bi-x-circle me-1"></i>Ditolak</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted">
                                            <i class="bi bi-inbox fs-1 d-block mb-3 opacity-50"></i>
                                            Belum ada riwayat permintaan belanja.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white border-top px-4 py-3">
                    {{ $permintaans->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
