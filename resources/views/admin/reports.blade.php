@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>Laporan Penjualan</h2>
            <p class="text-muted mb-0">Lihat grafik penjualan dan unduh ringkasan pendapatan.</p>
        </div>
    </div>

    <!-- Filter Rentang Waktu -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('admin.reports.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Tanggal Mulai</label>
                    <input type="date" name="start_date" class="form-control" value="{{ $startDate }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal Akhir</label>
                    <input type="date" name="end_date" class="form-control" value="{{ $endDate }}" required>
                </div>
                <div class="col-md-6 d-flex gap-2 flex-wrap align-items-end">
                    <button type="submit" class="btn btn-primary">Tampilkan Grafik</button>
                    <a href="{{ route('admin.reports.revenue', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn btn-success">
                        <i class="bi bi-file-earmark-spreadsheet me-1"></i> Download CSV
                    </a>
                    <a href="{{ route('admin.reports.pdf', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn btn-danger" target="_blank">
                        <i class="bi bi-file-earmark-pdf me-1"></i> Cetak PDF
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm mb-4">
                <div class="card-header fw-bold">
                    Grafik Total Penjualan ({{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }})
                </div>
                <div class="card-body">
                    <canvas id="salesReportChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Baris 2: Menu Terlaris & Metode Pembayaran -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card shadow-sm h-100">
                <div class="card-header fw-bold bg-white">
                    <i class="bi bi-trophy text-warning me-2"></i> Menu Terlaris (Top 10)
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Peringkat</th>
                                    <th>Nama Menu</th>
                                    <th>Total Terjual</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($bestSeller as $index => $item)
                                <tr>
                                    <td>
                                        @if($index == 0) <span class="badge bg-warning text-dark"><i class="bi bi-star-fill"></i> 1</span>
                                        @elseif($index == 1) <span class="badge bg-secondary"><i class="bi bi-star-fill"></i> 2</span>
                                        @elseif($index == 2) <span class="badge bg-danger"><i class="bi bi-star-fill"></i> 3</span>
                                        @else {{ $index + 1 }} @endif
                                    </td>
                                    <td class="fw-medium">{{ $item->nama_menu }}</td>
                                    <td><span class="badge bg-primary rounded-pill">{{ $item->total_terjual }} porsi</span></td>
                                </tr>
                                @empty
                                <tr><td colspan="3" class="text-center text-muted">Belum ada data penjualan.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-header fw-bold bg-white">
                    <i class="bi bi-pie-chart text-info me-2"></i> Metode Pembayaran
                </div>
                <div class="card-body d-flex justify-content-center align-items-center">
                    <canvas id="paymentMethodChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Baris 3: Kinerja Kasir & Penggunaan Stok -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header fw-bold bg-white">
                    <i class="bi bi-person-badge text-primary me-2"></i> Kinerja Kasir per Shift
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama Kasir</th>
                                    <th>Shift</th>
                                    <th>Transaksi</th>
                                    <th>Pendapatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($kasirPerformance as $kasir)
                                <tr>
                                    <td class="fw-medium">{{ $kasir->name }}</td>
                                    <td>
                                        @if($kasir->shift == 'pagi')
                                            <span class="badge bg-info text-dark"><i class="bi bi-brightness-high"></i> Pagi</span>
                                        @else
                                            <span class="badge bg-dark"><i class="bi bi-moon-stars"></i> Malam</span>
                                        @endif
                                    </td>
                                    <td>{{ $kasir->total_transaksi }}</td>
                                    <td class="text-success fw-bold">Rp {{ number_format($kasir->total_pendapatan, 0, ',', '.') }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center text-muted">Belum ada data kasir.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header fw-bold bg-white">
                    <i class="bi bi-box-seam text-success me-2"></i> Penggunaan Stok Bahan Baku
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Bahan Baku</th>
                                    <th>Total Terpakai</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stockUsage as $stok)
                                <tr>
                                    <td class="fw-medium">{{ $stok->nama_bahan }}</td>
                                    <td><span class="badge bg-danger">{{ $stok->total_penggunaan }} {{ $stok->satuan }}</span></td>
                                </tr>
                                @empty
                                <tr><td colspan="2" class="text-center text-muted">Belum ada data penggunaan bahan.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const labels = @json($chartLabels);
    const data = @json($chartData);

    const ctx = document.getElementById('salesReportChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Total Penjualan',
                    data: data,
                    fill: true,
                    backgroundColor: 'rgba(54, 162, 235, 0.12)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    tension: 0.35,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (context) => 'Rp ' + context.formattedValue.replace(/\B(?=(\d{3})+(?!\d))/g, '.')
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#495057' }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#e9ecef' },
                        ticks: {
                            color: '#495057',
                            callback: (value) => 'Rp ' + value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.')
                        }
                    }
                }
            }
        });
    }

    // Pie Chart Metode Pembayaran
    const paymentMethods = @json($paymentMethods);
    const pmLabels = paymentMethods.map(item => item.metode.toUpperCase());
    const pmData = paymentMethods.map(item => item.total);
    
    const pmCtx = document.getElementById('paymentMethodChart');
    if (pmCtx && pmLabels.length > 0) {
        new Chart(pmCtx, {
            type: 'doughnut',
            data: {
                labels: pmLabels,
                datasets: [{
                    data: pmData,
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.8)', // QRIS (biasanya biru)
                        'rgba(75, 192, 192, 0.8)', // Cash (hijau)
                        'rgba(255, 206, 86, 0.8)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: (context) => context.label + ': Rp ' + context.formattedValue.replace(/\B(?=(\d{3})+(?!\d))/g, '.')
                        }
                    }
                }
            }
        });
    } else if (pmCtx) {
        // Fallback jika kosong
        pmCtx.parentElement.innerHTML = '<p class="text-muted text-center w-100">Belum ada data pembayaran</p>';
    }
</script>
@endsection
