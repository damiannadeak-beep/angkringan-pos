@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>Manajemen Stok Bahan Baku</h2>
            <p class="text-muted mb-0">Kelola persediaan bahan baku yang digunakan untuk membuat produk.</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBahanModal">Tambah Bahan</button>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nama Bahan</th>
                            <th>Stok</th>
                            <th>Satuan</th>
                            <th>Harga Beli</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bahans as $bahan)
                            <tr>
                                <td>{{ $bahan->nama_bahan }}</td>
                                <td>{{ $bahan->stok }}</td>
                                <td>{{ $bahan->satuan }}</td>
                                <td>Rp {{ number_format($bahan->harga_beli, 0, ',', '.') }} / {{ $bahan->satuan }}</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editBahanModal{{ $bahan->id }}">Edit</button>
                                    <form class="d-inline" action="{{ route('admin.stok.destroy', $bahan->id) }}" method="POST" onsubmit="return confirm('Hapus bahan ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Hapus</button>
                                    </form>
                                </td>
                            </tr>

                            <!-- Edit Modal -->
                            <div class="modal fade" id="editBahanModal{{ $bahan->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <form action="{{ route('admin.stok.update', $bahan->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Bahan Baku</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Nama Bahan</label>
                                                    <input type="text" name="nama_bahan" class="form-control" value="{{ $bahan->nama_bahan }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Stok</label>
                                                    <input type="number" name="stok" class="form-control" value="{{ $bahan->stok }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Satuan</label>
                                                    <input type="text" name="satuan" class="form-control" value="{{ $bahan->satuan }}" placeholder="pcs, gram, ml, dll" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Harga Beli (Per Satuan)</label>
                                                    <input type="number" name="harga_beli" class="form-control" value="{{ $bahan->harga_beli }}" required>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                <button type="submit" class="btn btn-primary">Simpan</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>

                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4">Belum ada data bahan baku.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            {{ $bahans->links() }}
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addBahanModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.stok.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Bahan Baku</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Bahan</label>
                        <input type="text" name="nama_bahan" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Stok Awal</label>
                        <input type="number" name="stok" class="form-control" value="0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Satuan</label>
                        <input type="text" name="satuan" class="form-control" placeholder="pcs, gram, ml, dll" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Harga Beli (Per Satuan)</label>
                        <input type="number" name="harga_beli" class="form-control" value="0" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection
