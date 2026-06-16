@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <h4 class="fw-bold text-accent mb-1"><i class="bi bi-cart-check-fill me-2"></i>Daftar Permintaan Belanja</h4>
            <p class="text-muted mb-0">Kelola daftar barang atau bahan baku yang diminta oleh Kasir untuk dibeli.</p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <button type="button" class="btn btn-primary rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#adminBeliModal">
                <i class="bi bi-plus-circle me-1"></i> Catat Pembelian Baru
            </button>
        </div>
    </div>

    <!-- Modal Admin Catat Pembelian -->
    <div class="modal fade" id="adminBeliModal" tabindex="-1" aria-labelledby="adminBeliModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold" id="adminBeliModalLabel"><i class="bi bi-cart-plus me-2"></i>Catat Pembelian Bahan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.permintaan.store') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <p class="text-muted small mb-4">Catat daftar belanjaan Anda secara bebas di sini. Data ini hanya berfungsi sebagai riwayat belanja dan <strong>tidak memengaruhi stok sistem</strong>.</p>
                        
                        <div id="dynamicBahanContainer">
                            <div class="row bahan-row mb-3 align-items-end">
                                <div class="col-7">
                                    <label class="form-label fw-bold">Nama Barang <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="nama_barang[]" placeholder="Misal: Beras 5kg" required>
                                </div>
                                <div class="col-4">
                                    <label class="form-label fw-bold">Jumlah <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="jumlah_ditambah[]" placeholder="Jml" required>
                                </div>
                                <div class="col-1 text-end">
                                    <button type="button" class="btn btn-outline-danger btn-remove-row" style="display: none;"><i class="bi bi-trash"></i></button>
                                </div>
                            </div>
                        </div>

                        <button type="button" class="btn btn-sm btn-outline-primary mb-3" id="btnAddRow">
                            <i class="bi bi-plus"></i> Tambah Barang Lain
                        </button>

                        <div class="mb-2">
                            <label class="form-label fw-bold">Catatan Keseluruhan (Opsional)</label>
                            <textarea class="form-control" name="catatan" rows="2" placeholder="Misal: Belanja bulanan di pasar induk..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top-0 py-3">
                        <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">Simpan Catatan Belanja</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card admin-card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Tanggal</th>
                            <th>Kasir</th>
                            <th>Detail Barang</th>
                            <th>Diminta</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($permintaans as $req)
                            <tr class="{{ $req->status === 'menunggu' ? 'table-warning bg-opacity-10' : '' }}">
                                <td class="ps-4">
                                    <div class="fw-medium">{{ $req->created_at->format('d/m/Y') }}</div>
                                    <div class="small text-muted">{{ $req->created_at->format('H:i') }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary rounded-pill">{{ $req->user->name ?? 'Unknown' }}</span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $req->nama_barang }}</div>
                                    @if($req->sisa_stok)
                                        <div class="small text-danger"><i class="bi bi-exclamation-triangle me-1"></i>Sisa: {{ $req->sisa_stok }}</div>
                                    @endif
                                    @if($req->catatan)
                                        <div class="small text-muted mt-1 fst-italic">"{{ $req->catatan }}"</div>
                                    @endif
                                </td>
                                <td><span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-2 py-1">{{ $req->jumlah_diminta }}</span></td>
                                <td>
                                    @if($req->status === 'menunggu')
                                        <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i>Menunggu</span>
                                    @elseif($req->status === 'sudah_dibeli')
                                        <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Sudah Dibeli</span>
                                    @else
                                        <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Ditolak</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    @if($req->status === 'menunggu')
                                        <div class="d-flex justify-content-end gap-2">
                                            <form action="{{ route('admin.permintaan.update', $req->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="status" value="sudah_dibeli">
                                                <button type="submit" class="btn btn-sm btn-success" title="Tandai Sudah Dibeli" onclick="return confirm('Tandai barang ini sudah dibeli?')">
                                                    <i class="bi bi-check-lg"></i>
                                                </button>
                                            </form>

                                            <form action="{{ route('admin.permintaan.update', $req->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="status" value="ditolak">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Tolak Permintaan" onclick="return confirm('Tolak permintaan ini?')">
                                                    <i class="bi bi-x-lg"></i>
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-emoji-smile fs-1 d-block mb-3 opacity-50"></i>
                                    Tidak ada permintaan belanja dari kasir.
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
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('dynamicBahanContainer');
    const btnAdd = document.getElementById('btnAddRow');

    if (btnAdd && container) {
        btnAdd.addEventListener('click', function() {
            const rows = container.querySelectorAll('.bahan-row');
            if (rows.length > 0) {
                const newRow = rows[0].cloneNode(true);
                
                // Reset values
                const inputs = newRow.querySelectorAll('input[type="text"]');
                if (inputs.length >= 2) {
                    inputs[0].value = '';
                    inputs[1].value = '';
                }
                
                // Show delete button
                const btnRemove = newRow.querySelector('.btn-remove-row');
                if (btnRemove) {
                    btnRemove.style.display = 'block';
                    btnRemove.addEventListener('click', function() {
                        newRow.remove();
                    });
                }

                container.appendChild(newRow);
            }
        });
    }
});
</script>
@endpush
