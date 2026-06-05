@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>{{ $menu->exists ? 'Edit Produk' : 'Tambah Produk' }}</h2>
        <a href="{{ route('admin.menu.index') }}" class="btn btn-secondary">Kembali</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger">
                    <h5 class="alert-heading">Terjadi kesalahan saat menyimpan produk:</h5>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ $menu->exists ? route('admin.menu.update', $menu->id) : route('admin.menu.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @if($menu->exists)
                    @method('PUT')
                @endif

                <div class="mb-3">
                    <label class="form-label">Nama Produk</label>
                    <input type="text" name="nama_menu" class="form-control" value="{{ old('nama_menu', $menu->nama_menu) }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Kategori</label>
                    <select class="form-select" name="kategori" required>
                        <option value="makanan" {{ old('kategori', $menu->kategori) == 'makanan' ? 'selected' : '' }}>Makanan</option>
                        <option value="minuman" {{ old('kategori', $menu->kategori) == 'minuman' ? 'selected' : '' }}>Minuman</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Harga</label>
                    <input type="number" step="0.01" name="harga" class="form-control" value="{{ old('harga', $menu->harga) }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Stok</label>
                    <input type="number" name="stok" class="form-control" value="{{ old('stok', $menu->stok) }}" required>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" name="is_available" value="1" class="form-check-input" id="is_available" {{ old('is_available', $menu->is_available) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_available">Tersedia</label>
                </div>
                <div class="mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="deskripsi" class="form-control">{{ old('deskripsi', $menu->deskripsi) }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Gambar Produk</label>
                    @if($menu->image)
                        <div class="mb-2"><img src="{{ asset('storage/'.$menu->image) }}" alt="gambar" style="max-width:120px; height:auto;"></div>
                    @endif
                    <input type="file" name="image" accept="image/*" class="form-control">
                </div>

                <button class="btn btn-primary">Simpan</button>
            </form>
        </div>
    </div>
</div>
@endsection
