@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>{{ $pageTitle == 'Manajemen Menu' ? 'Manajemen Produk' : $pageTitle }}</h2>
            @if(!empty($showStockPage))
                <p class="text-muted mb-0">Kelola stok produk dan perbarui jumlah item yang tersedia.</p>
            @else
                <p class="text-muted mb-0">Tambah, edit, dan pantau ketersediaan produk.</p>
            @endif
        </div>
        <a href="{{ route('admin.menu.create') }}" class="btn btn-primary">Tambah Produk</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <div class="p-3 d-flex gap-2 align-items-center flex-wrap">
                    <span class="fw-bold me-2">Kategori:</span>
                    <div class="btn-group shadow-sm">
                        <a href="{{ request()->fullUrlWithQuery(['category' => null, 'page' => null]) }}" class="btn btn-sm {{ !request('category') ? 'btn-primary' : 'btn-outline-primary' }}">Semua</a>
                        <a href="{{ request()->fullUrlWithQuery(['category' => 'makanan', 'page' => null]) }}" class="btn btn-sm {{ request('category') == 'makanan' ? 'btn-primary' : 'btn-outline-primary' }}">Makanan</a>
                        <a href="{{ request()->fullUrlWithQuery(['category' => 'minuman', 'page' => null]) }}" class="btn btn-sm {{ request('category') == 'minuman' ? 'btn-primary' : 'btn-outline-primary' }}">Minuman</a>
                    </div>
                    
                    <span class="fw-bold ms-3 me-2">Stok:</span>
                    <a href="{{ request()->fullUrlWithQuery(['filter' => 'low', 'page' => null]) }}" class="btn btn-sm {{ request('filter') == 'low' ? 'btn-warning' : 'btn-outline-warning' }} shadow-sm">Tampilkan Stok Menipis</a>
                    
                    <a href="{{ url()->current() }}" class="btn btn-sm btn-outline-secondary ms-auto">Reset Filter</a>
                </div>
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Gambar</th>
                            <th>Nama Produk</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Tersedia</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($menus as $m)
                            <tr>
                                <td>
                                    @if($m->image)
                                        <div class="bg-white border rounded d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                            <img src="{{ asset('storage/'.$m->image) }}" alt="img" style="object-fit: contain; width: 100%; height: 100%; padding: 4px;">
                                        </div>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $m->nama_menu }}</td>
                                <td>{{ ucfirst($m->kategori) }}</td>
                                <td>Rp {{ number_format($m->harga,0,',','.') }}</td>
                                <td>
                                    {{ $m->stok }}
                                    <form class="d-inline ms-2" action="{{ route('admin.menu.stock', $m->id) }}" method="POST">
                                        @csrf
                                        <input type="number" name="stok" value="{{ $m->stok }}" min="0" style="width:80px; display:inline-block">
                                        <button class="btn btn-sm btn-outline-secondary">Update</button>
                                    </form>
                                </td>
                                <td>{{ $m->is_available ? 'Ya' : 'Tidak' }}</td>
                                <td>
                                    <div class="d-flex justify-content-center gap-1 flex-wrap flex-md-nowrap">
                                        <a href="{{ route('admin.menu.edit', $m->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="bi bi-pencil"></i> <span class="d-none d-md-inline">Edit</span>
                                        </a>
                                        <form action="{{ route('admin.menu.destroy', $m->id) }}" method="POST" onsubmit="return confirm('Hapus produk ini?');">
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
        <div class="card-footer">
            {{ $menus->links() }}
        </div>
    </div>
</div>
@endsection
