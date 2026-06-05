@extends('layouts.admin')

@section('content')
@php $users = $users ?? collect(); @endphp
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>Dashboard Pemilik</h2>
            <p class="text-muted mb-0">Kelola penjualan, stok, dan pantau performa warung.</p>
        </div>
        <span class="badge bg-secondary fs-6 px-3 py-2">Hari Ini: {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</span>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
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

    <!-- Row 1: Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card bg-white border-0 shadow-sm h-100 border-start border-primary border-4">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h6 class="text-muted mb-0 text-truncate" style="max-width: 80%;">Pendapatan Bulan Ini</h6>
                        <div class="text-primary bg-primary bg-opacity-10 p-2 rounded">
                            <i class="bi bi-wallet2 fs-5"></i>
                        </div>
                    </div>
                    <h4 class="fw-bold mb-0 text-truncate">Rp {{ number_format($totalPenjualanBulan ?? 0, 0, ',', '.') }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card bg-white border-0 shadow-sm h-100 border-start border-warning border-4">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h6 class="text-muted mb-0 text-truncate" style="max-width: 80%;">Total HPP (Modal)</h6>
                        <div class="text-warning bg-warning bg-opacity-10 p-2 rounded">
                            <i class="bi bi-box-seam fs-5"></i>
                        </div>
                    </div>
                    <h4 class="fw-bold mb-0 text-truncate">Rp {{ number_format($totalHppBulan ?? 0, 0, ',', '.') }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card bg-white border-0 shadow-sm h-100 border-start border-danger border-4">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h6 class="text-muted mb-0 text-truncate" style="max-width: 80%;">Pengeluaran Lain</h6>
                        <div class="text-danger bg-danger bg-opacity-10 p-2 rounded">
                            <i class="bi bi-cart-dash fs-5"></i>
                        </div>
                    </div>
                    <h4 class="fw-bold mb-0 text-truncate">Rp {{ number_format($totalPengeluaranBulan ?? 0, 0, ',', '.') }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card bg-white border-0 shadow-sm h-100 border-start border-success border-4">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h6 class="text-muted mb-0 text-truncate" style="max-width: 80%;">Laba Bersih Murni</h6>
                        <div class="text-success bg-success bg-opacity-10 p-2 rounded">
                            <i class="bi bi-cash-coin fs-5"></i>
                        </div>
                    </div>
                    <h4 class="fw-bold mb-0 text-truncate">Rp {{ number_format($labaBersihBulan ?? 0, 0, ',', '.') }}</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 1.5: Secondary Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4 col-sm-6">
            <div class="card bg-white border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h6 class="text-muted mb-0">Penjualan Hari Ini</h6>
                        <div class="text-success bg-success bg-opacity-10 p-2 rounded">
                            <i class="bi bi-graph-up-arrow fs-5"></i>
                        </div>
                    </div>
                    <h4 class="fw-bold mb-0">Rp {{ number_format($totalPenjualanHariIni ?? 0, 0, ',', '.') }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6">
            <div class="card bg-white border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h6 class="text-muted mb-0">Pendapatan Cash</h6>
                        <div class="text-warning bg-warning bg-opacity-10 p-2 rounded">
                            <i class="bi bi-cash-stack fs-5"></i>
                        </div>
                    </div>
                    <h4 class="fw-bold mb-0">Rp {{ number_format($totalCash ?? 0, 0, ',', '.') }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6">
            <div class="card bg-white border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h6 class="text-muted mb-0">Pendapatan QRIS</h6>
                        <div class="text-info bg-info bg-opacity-10 p-2 rounded">
                            <i class="bi bi-qr-code-scan fs-5"></i>
                        </div>
                    </div>
                    <h4 class="fw-bold mb-0">Rp {{ number_format($totalQris ?? 0, 0, ',', '.') }}</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 2: Charts -->
    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0">Grafik Penjualan Harian (Bulan Ini)</h6>
                </div>
                <div class="card-body">
                    <canvas id="dailySalesChart" height="250"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0">Grafik Penjualan Bulanan (Tahun Ini)</h6>
                </div>
                <div class="card-body">
                    <canvas id="monthlySalesChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 3: Alerts & Reviews side-by-side -->
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100 border-top border-danger border-3">
                <div class="card-header bg-white pt-3 pb-2">
                    <h6 class="fw-bold text-danger mb-0"><i class="bi bi-exclamation-triangle-fill me-2"></i> Peringatan Stok Produk Menipis</h6>
                </div>
                <div class="card-body p-0">
                    @if($stokMenipis->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Nama Produk</th>
                                        <th>Sisa Stok</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($stokMenipis as $menu)
                                    <tr>
                                        <td class="ps-4">{{ $menu->nama_menu }}</td>
                                        <td class="text-danger fw-bold fs-5">{{ $menu->stok }}</td>
                                        <td><span class="badge bg-warning text-dark">Perlu Restock</span></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="p-5 text-center">
                            <div class="text-success mb-2"><i class="bi bi-check-circle fs-1"></i></div>
                            <h6 class="text-muted mb-0">Semua stok produk dalam kondisi aman.</h6>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white pt-3 pb-2 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0"><i class="bi bi-chat-left-text me-2"></i> Ulasan Terbaru</h6>
                    <a href="{{ route('admin.reviews.index') }}" class="btn btn-sm btn-link text-decoration-none">Lihat Semua</a>
                </div>
                <div class="card-body">
                    @if(($latestReviews ?? collect())->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($latestReviews as $r)
                                <div class="list-group-item px-0 py-3">
                                    <div class="d-flex justify-content-between w-100 mb-1">
                                        <h6 class="mb-0 fw-bold">{{ $r->konsumen->name ?? 'Konsumen' }}</h6>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($r->tanggal)->diffForHumans() }}</small>
                                    </div>
                                    <div class="text-warning mb-1">
                                        @for($i=1; $i<=5; $i++)
                                            <i class="bi bi-star{{ $i <= $r->rating ? '-fill' : '' }}"></i>
                                        @endfor
                                    </div>
                                    <p class="mb-0 text-muted small">{{ \Illuminate\Support\Str::limit($r->komentar, 120) }}</p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-5 text-center">
                            <div class="text-muted mb-2"><i class="bi bi-chat-square text-opacity-50 fs-1"></i></div>
                            <h6 class="text-muted mb-0">Belum ada ulasan baru.</h6>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const dailyLabels = @json($chartDailyLabels);
    const dailyData = @json($chartDailyData);
    const monthlyLabels = @json($chartMonthlyLabels);
    const monthlyData = @json($chartMonthlyData);

    const createSalesChart = (elementId, labels, data, label, color) => {
        const ctx = document.getElementById(elementId);
        if (!ctx) return;
        new Chart(ctx, {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label,
                    data,
                    fill: true,
                    backgroundColor: `rgba(${color}, 0.12)`,
                    borderColor: `rgba(${color}, 1)`,
                    tension: 0.35,
                    pointRadius: 3,
                    pointHoverRadius: 5,
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
    };

    createSalesChart('dailySalesChart', dailyLabels, dailyData, 'Penjualan Harian', '13, 110, 253'); // Blue
    createSalesChart('monthlySalesChart', monthlyLabels, monthlyData, 'Penjualan Bulanan', '25, 135, 84'); // Green
</script>
@endsection