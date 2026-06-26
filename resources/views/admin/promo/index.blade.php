@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manajemen Promo</h2>
        <a href="{{ route('admin.promo.create') }}" class="btn btn-primary">Buat Promo</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Judul</th>
                            <th>Tipe</th>
                            <th>Nilai</th>
                            <th>Periode</th>
                            <th>Aktif</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($promos as $p)
                            <tr>
                                <td>{{ $p->title }}</td>
                                <td>
                                    @if($p->type === 'discount')
                                        <span class="badge bg-info text-dark">Diskon</span>
                                    @else
                                        <span class="badge bg-primary">Paket</span>
                                    @endif
                                </td>
                                <td class="fw-bold">
                                    @if($p->type === 'discount' && $p->discount_type === 'percentage')
                                        {{ number_format($p->value, 0, ',', '.') }}%
                                    @else
                                        Rp {{ number_format($p->value, 0, ',', '.') }}
                                    @endif
                                </td>
                                <td>{{ $p->starts_at ? $p->starts_at->format('d M Y') : '-' }} - {{ $p->ends_at ? $p->ends_at->format('d M Y') : '-' }}</td>
                                <td>
                                    @if(!$p->is_active)
                                        <span class="badge bg-danger">Tidak Aktif</span>
                                    @elseif($p->ends_at && $p->ends_at < now())
                                        <span class="badge bg-secondary">Kadaluarsa</span>
                                    @elseif($p->starts_at && $p->starts_at > now())
                                        <span class="badge bg-warning text-dark">Belum Mulai</span>
                                    @else
                                        <span class="badge bg-success">Berjalan</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex justify-content-start gap-1 flex-wrap flex-md-nowrap">
                                        <a href="{{ route('admin.promo.edit', $p->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="bi bi-pencil"></i> <span class="d-none d-md-inline">Edit</span>
                                        </a>
                                        <form action="{{ route('admin.promo.destroy', $p->id) }}" method="POST" onsubmit="return confirm('Hapus promo?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                                <i class="bi bi-trash"></i> <span class="d-none d-md-inline">Hapus</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">{{ $promos->links() }}</div>
    </div>
</div>
@endsection
