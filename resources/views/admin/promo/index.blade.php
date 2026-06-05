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
                                <td>{{ $p->type }}</td>
                                <td>{{ $p->value }}</td>
                                <td>{{ $p->starts_at? $p->starts_at->format('d M Y') : '-' }} - {{ $p->ends_at? $p->ends_at->format('d M Y') : '-' }}</td>
                                <td>{{ $p->is_active ? 'Ya' : 'Tidak' }}</td>
                                <td>
                                    <a href="{{ route('admin.promo.edit', $p->id) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <form class="d-inline" action="{{ route('admin.promo.destroy', $p->id) }}" method="POST" onsubmit="return confirm('Hapus promo?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Hapus</button>
                                    </form>
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
