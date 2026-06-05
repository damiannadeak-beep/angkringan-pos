@extends('layouts.kasir')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h4 class="mb-0 fw-bold" style="color: #6a3a45;">
                <i class="bi bi-calendar-check me-2"></i>Laporan Tutup Shift
            </h4>
            <button onclick="window.print()" class="btn btn-primary rounded-pill">
                <i class="bi bi-printer me-2"></i>Cetak Laporan
            </button>
        </div>
    </div>

    <div class="row g-4">
        <!-- Summary Cards -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-4 text-center">
                    <div class="rounded-circle d-inline-flex justify-content-center align-items-center mb-3" style="width: 60px; height: 60px; background: #e8f5e9; color: #2e7d32;">
                        <i class="bi bi-cash-stack fs-3"></i>
                    </div>
                    <h6 class="text-muted mb-2">Total Tunai (Cash in Drawer)</h6>
                    <h3 class="fw-bold mb-0 text-success">Rp {{ number_format($totalCash, 0, ',', '.') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-4 text-center">
                    <div class="rounded-circle d-inline-flex justify-content-center align-items-center mb-3" style="width: 60px; height: 60px; background: #e3f2fd; color: #1565c0;">
                        <i class="bi bi-qr-code-scan fs-3"></i>
                    </div>
                    <h6 class="text-muted mb-2">Total QRIS</h6>
                    <h3 class="fw-bold mb-0 text-primary">Rp {{ number_format($totalQris, 0, ',', '.') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-4" style="background: linear-gradient(135deg, #a94f64, #6a3a45); color: white;">
                <div class="card-body p-4 text-center">
                    <div class="rounded-circle d-inline-flex justify-content-center align-items-center mb-3 bg-white" style="width: 60px; height: 60px; color: #6a3a45;">
                        <i class="bi bi-wallet2 fs-3"></i>
                    </div>
                    <h6 class="mb-2 text-white-50">Total Penjualan Shift Ini</h6>
                    <h3 class="fw-bold mb-0">Rp {{ number_format($totalSemua, 0, ',', '.') }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Transaction List -->
    <div class="card shadow-sm border-0 mt-4 rounded-4">
        <div class="card-body p-4">
            <h5 class="fw-bold mb-4">Daftar Transaksi Selesai Hari Ini</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Waktu</th>
                            <th>No. Pesanan</th>
                            <th>Tipe</th>
                            <th>Metode</th>
                            <th class="text-end">Total Bayar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pembayarans as $p)
                        <tr>
                            <td>{{ $p->tanggal ? \Carbon\Carbon::parse($p->tanggal)->format('H:i') : '-' }}</td>
                            <td><span class="badge bg-secondary">#{{ str_pad($p->id_pesanan, 4, '0', STR_PAD_LEFT) }}</span></td>
                            <td>{{ ucfirst(str_replace('_', ' ', $p->pesanan->tipe_pesanan)) }}</td>
                            <td>
                                @if($p->metode == 'cash')
                                    <span class="badge bg-success">Tunai</span>
                                @elseif($p->metode == 'qris')
                                    <span class="badge bg-primary">QRIS</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-end fw-bold">Rp {{ number_format($p->total_bayar, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">Belum ada transaksi selesai di shift ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<style>
@media print {
    body * { visibility: hidden; }
    .container-fluid, .container-fluid * { visibility: visible; }
    .container-fluid { position: absolute; left: 0; top: 0; width: 100%; }
    .btn, .navbar { display: none !important; }
    .card { border: none !important; box-shadow: none !important; }
}
</style>
@endsection
