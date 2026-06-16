@extends('layouts.kasir')

@section('content')
<div class="container-fluid px-4 py-4 d-print-none">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h4 class="mb-0 fw-bold text-accent">
                <i class="bi bi-calendar-check me-2"></i>Laporan Tutup Shift
            </h4>
            <div>
                <a href="{{ route('kasir.shift_report.pdf') }}" class="btn btn-danger rounded-pill me-2">
                    <i class="bi bi-file-pdf me-2"></i>Export PDF
                </a>
                <button onclick="window.print()" class="btn btn-primary rounded-pill">
                    <i class="bi bi-printer me-2"></i>Cetak Browser
                </button>
            </div>
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
            <div class="card shadow-sm border-0 rounded-4 gradient-card-primary">
                <div class="card-body p-4 text-center">
                    <div class="rounded-circle d-inline-flex justify-content-center align-items-center mb-3 bg-white icon-accent" style="width: 60px; height: 60px;">
                        <i class="bi bi-wallet2 fs-3"></i>
                    </div>
                    <h6 class="mb-2 text-white-50">Total Penjualan Shift Ini</h6>
                    <h3 class="fw-bold mb-0">Rp {{ number_format($totalSemua, 0, ',', '.') }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Transaction List (Web View Only) -->
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

<!-- Layout Print Browser -->
<div class="d-none d-print-block px-4 py-4" style="background: white; color: black !important;">
    <div class="text-center mb-4">
        <h2 class="fw-bold">Laporan Penjualan Shift</h2>
        <p>Kasir: {{ auth()->user()->name }} | Tanggal: {{ $shift->waktu_buka->format('Y-m-d') }}</p>
    </div>
    
    <table class="table table-bordered border-dark mb-4 text-dark">
        <thead>
            <tr style="background-color: #f8f9fa !important; -webkit-print-color-adjust: exact; print-color-adjust: exact;">
                <th>No</th>
                <th>Menu / Item Terjual</th>
                <th>Qty</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @forelse($rekapMenu as $nama => $data)
            <tr>
                <td>{{ $no++ }}</td>
                <td>{{ $nama }}</td>
                <td>{{ $data['jumlah'] }}</td>
                <td>Rp {{ number_format($data['subtotal'], 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center">Belum ada item terjual</td>
            </tr>
            @endforelse
            @if($totalItemTerjual > 0)
            <tr class="fw-bold" style="background-color: #f8f9fa !important; -webkit-print-color-adjust: exact; print-color-adjust: exact;">
                <td colspan="2" class="text-end">Total Keseluruhan Item</td>
                <td>{{ $totalItemTerjual }}</td>
                <td></td>
            </tr>
            @endif
        </tbody>
    </table>

    <div class="row justify-content-end">
        <div class="col-5">
            <table class="table table-borderless text-dark">
                <tr>
                    <td>Total Tunai:</td>
                    <td class="text-end">Rp {{ number_format($totalCash, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Total QRIS:</td>
                    <td class="text-end">Rp {{ number_format($totalQris, 0, ',', '.') }}</td>
                </tr>
                <tr class="fw-bold border-top border-dark">
                    <td>Total Keseluruhan:</td>
                    <td class="text-end">Rp {{ number_format($totalSemua, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>
    </div>
</div>

<style>
@media print {
    @page { size: auto; margin: 20mm; }
    body { background: white !important; }
}
</style>
@endsection
