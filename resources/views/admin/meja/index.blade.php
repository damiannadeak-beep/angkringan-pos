@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>Manajemen Meja & QR Code</h2>
            <p class="text-muted mb-0">Kelola meja dan cetak QR code untuk pemesanan konsumen.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <!-- Kolom Kiri: Form Tambah Meja -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header fw-bold bg-white">
                    <i class="bi bi-plus-square text-primary me-2"></i> Tambah Meja
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.meja.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Nama/Nomor Meja</label>
                            <input type="text" name="nama_meja_atau_nomor" class="form-control" placeholder="Misal: Meja 1, VIP A" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Simpan Meja</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Tabel Data Meja -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header fw-bold bg-white">
                    Daftar Meja
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Nama / Nomor Meja</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($mejas as $index => $m)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td class="fw-medium">{{ $m->nama_meja_atau_nomor }}</td>
                                    <td class="text-center">
                                        <!-- Tombol Print QR -->
                                        <a href="{{ route('admin.meja.print_qr', $m->id) }}" target="_blank" class="btn btn-sm btn-outline-success me-1" title="Cetak QR Code">
                                            <i class="bi bi-qr-code"></i> Cetak QR
                                        </a>
                                        
                                        <!-- Tombol Edit -->
                                        <button type="button" class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#editModal{{ $m->id }}">
                                            <i class="bi bi-pencil"></i>
                                        </button>

                                        <!-- Tombol Hapus -->
                                        <form class="d-inline" action="{{ route('admin.meja.destroy', $m->id) }}" method="POST" onsubmit="return confirm('Hapus meja ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                        </form>

                                        <!-- Modal Edit -->
                                        <div class="modal fade text-start" id="editModal{{ $m->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Edit Meja</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form action="{{ route('admin.meja.update', $m->id) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label">Nama/Nomor Meja</label>
                                                                <input type="text" name="nama_meja_atau_nomor" class="form-control" value="{{ $m->nama_meja_atau_nomor }}" required>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="3" class="text-center text-muted py-4">Belum ada meja.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
