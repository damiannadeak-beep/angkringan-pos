@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>Manajemen Pengeluaran</h2>
            <p class="text-muted mb-0">Catat pengeluaran harian untuk menghitung laba bersih.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row">
        <!-- Kolom Kiri: Form Tambah Pengeluaran -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header fw-bold bg-white">
                    <i class="bi bi-wallet2 text-danger me-2"></i> Tambah Pengeluaran
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.pengeluaran.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Tanggal</label>
                            <input type="date" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <input type="text" name="deskripsi" class="form-control" placeholder="Misal: Beli gas LPG" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nominal (Rp)</label>
                            <input type="number" name="nominal" class="form-control" min="0" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Keterangan (Opsional)</label>
                            <textarea name="keterangan" class="form-control" rows="2"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Simpan Pengeluaran</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Tabel Data Pengeluaran -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header fw-bold bg-white">
                    Riwayat Pengeluaran
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Deskripsi</th>
                                    <th>Keterangan</th>
                                    <th>Diinput Oleh</th>
                                    <th class="text-end">Nominal</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pengeluarans as $p)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($p->tanggal)->format('d M Y') }}</td>
                                    <td class="fw-medium">{{ $p->deskripsi }}</td>
                                    <td class="text-muted small">{{ $p->keterangan ?: '-' }}</td>
                                    <td>
                                        @if($p->user)
                                            <span class="badge bg-secondary">{{ $p->user->name }} ({{ ucfirst($p->user->role) }})</span>
                                        @else
                                            <span class="text-muted small">Sistem / Admin</span>
                                        @endif
                                    </td>
                                    <td class="text-end text-danger fw-bold">Rp {{ number_format($p->nominal, 0, ',', '.') }}</td>
                                    <td class="text-center">
                                        <form action="{{ route('admin.pengeluaran.destroy', $p->id) }}" method="POST" onsubmit="return confirm('Hapus data pengeluaran ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="text-center text-muted py-4">Belum ada data pengeluaran.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($pengeluarans->hasPages())
                <div class="card-footer">
                    {{ $pengeluarans->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
