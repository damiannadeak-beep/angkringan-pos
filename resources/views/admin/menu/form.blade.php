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

                <hr class="my-4">
                <h5 class="fw-bold mb-3"><i class="bi bi-list-stars text-primary me-2"></i>Resep / Komposisi Bahan Baku (Opsional)</h5>
                <p class="text-muted small mb-3">Tambahkan bahan baku di sini agar stok bahan otomatis berkurang saat produk ini dipesan.</p>
                
                <div id="recipe-container">
                    @if($menu->exists && $menu->bahans->count() > 0)
                        @foreach($menu->bahans as $index => $bahan)
                            <div class="row g-2 mb-2 recipe-row">
                                <div class="col-7">
                                    <select name="bahans[]" class="form-select">
                                        <option value="">-- Pilih Bahan Baku --</option>
                                        @foreach($bahans as $b)
                                            <option value="{{ $b->id }}" {{ $bahan->id == $b->id ? 'selected' : '' }}>
                                                {{ $b->nama_bahan }} (Stok: {{ $b->stok }} {{ $b->satuan }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-4">
                                    <div class="input-group">
                                        <input type="number" name="jumlah_dibutuhkan[]" class="form-control" value="{{ $bahan->pivot->jumlah_dibutuhkan }}" placeholder="Jumlah" min="1">
                                        <span class="input-group-text">Satuan</span>
                                    </div>
                                </div>
                                <div class="col-1 text-end">
                                    <button type="button" class="btn btn-outline-danger remove-recipe"><i class="bi bi-trash"></i></button>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>

                <button type="button" class="btn btn-sm btn-outline-primary mb-4" id="add-recipe">
                    <i class="bi bi-plus-circle me-1"></i> Tambah Bahan Baku
                </button>
                <hr class="my-4">

                <!-- VARIAN & TOPING -->
                <h5 class="fw-bold mb-3"><i class="bi bi-tags text-success me-2"></i>Varian & Toping (Add-ons)</h5>
                <p class="text-muted small mb-3">Atur pilihan seperti "Level Pedas" atau tambahan "Toping" yang memiliki harga tersendiri.</p>
                
                <input type="hidden" name="variants_json" id="variants_json_input" value="{{ old('variants_json', $menu->variants_json ?? '[]') }}">
                
                <div id="variants-container"></div>
                
                <button type="button" class="btn btn-sm btn-outline-success mb-4" id="add-variant-group">
                    <i class="bi bi-plus-circle me-1"></i> Tambah Grup Varian (Misal: Level Pedas)
                </button>
                <hr class="my-4">
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('recipe-container');
    const btnAdd = document.getElementById('add-recipe');
    
    // Template for new row
    const template = `
        <div class="row g-2 mb-2 recipe-row">
            <div class="col-7">
                <select name="bahans[]" class="form-select" required>
                    <option value="">-- Pilih Bahan Baku --</option>
                    @if(isset($bahans))
                        @foreach($bahans as $b)
                            <option value="{{ $b->id }}">{{ $b->nama_bahan }} (Stok: {{ $b->stok }} {{ $b->satuan }})</option>
                        @endforeach
                    @endif
                </select>
            </div>
            <div class="col-4">
                <div class="input-group">
                    <input type="number" name="jumlah_dibutuhkan[]" class="form-control" value="1" placeholder="Jumlah" min="1" required>
                    <span class="input-group-text">Satuan</span>
                </div>
            </div>
            <div class="col-1 text-end">
                <button type="button" class="btn btn-outline-danger remove-recipe"><i class="bi bi-trash"></i></button>
            </div>
        </div>
    `;

    btnAdd.addEventListener('click', function() {
        container.insertAdjacentHTML('beforeend', template);
    });

    container.addEventListener('click', function(e) {
        if(e.target.closest('.remove-recipe')) {
            e.target.closest('.recipe-row').remove();
        }
    });

    // --- VARIANTS LOGIC ---
    let variants = JSON.parse(document.getElementById('variants_json_input').value || '[]');
    const variantsContainer = document.getElementById('variants-container');
    const inputVariants = document.getElementById('variants_json_input');

    function renderVariants() {
        variantsContainer.innerHTML = '';
        variants.forEach((group, gIndex) => {
            let optionsHtml = '';
            group.options.forEach((opt, oIndex) => {
                optionsHtml += `
                    <div class="row g-2 align-items-center mb-2">
                        <div class="col-6">
                            <input type="text" class="form-control form-control-sm var-opt-name" data-g="${gIndex}" data-o="${oIndex}" value="${opt.name}" placeholder="Nama Opsi (Cth: Level 1)">
                        </div>
                        <div class="col-5">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">+ Rp</span>
                                <input type="number" class="form-control var-opt-price" data-g="${gIndex}" data-o="${oIndex}" value="${opt.price}" placeholder="0" min="0">
                            </div>
                        </div>
                        <div class="col-1 text-end">
                            <button type="button" class="btn btn-sm btn-outline-danger remove-opt" data-g="${gIndex}" data-o="${oIndex}"><i class="bi bi-x"></i></button>
                        </div>
                    </div>
                `;
            });

            const html = `
                <div class="card mb-3 border-success border-opacity-50">
                    <div class="card-header bg-success bg-opacity-10 d-flex justify-content-between align-items-center py-2">
                        <div class="fw-bold text-success">Grup Varian</div>
                        <button type="button" class="btn btn-sm btn-danger remove-group" data-g="${gIndex}">Hapus Grup</button>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small">Nama Grup</label>
                                <input type="text" class="form-control form-control-sm var-group-name" data-g="${gIndex}" value="${group.group_name}" placeholder="Cth: Level Pedas, Toping">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">Tipe Pilihan</label>
                                <select class="form-select form-select-sm var-group-type" data-g="${gIndex}">
                                    <option value="single" ${group.type === 'single' ? 'selected' : ''}>Pilih Satu (Radio)</option>
                                    <option value="multiple" ${group.type === 'multiple' ? 'selected' : ''}>Bisa Pilih Banyak (Checkbox)</option>
                                </select>
                            </div>
                        </div>
                        <hr class="text-muted">
                        <div class="options-container">
                            ${optionsHtml}
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary mt-2 add-opt" data-g="${gIndex}"><i class="bi bi-plus"></i> Tambah Opsi</button>
                    </div>
                </div>
            `;
            variantsContainer.insertAdjacentHTML('beforeend', html);
        });
        
        inputVariants.value = JSON.stringify(variants);
    }

    document.getElementById('add-variant-group').addEventListener('click', function() {
        variants.push({ group_name: '', type: 'single', options: [{ name: '', price: 0 }] });
        renderVariants();
    });

    variantsContainer.addEventListener('input', function(e) {
        if(e.target.classList.contains('var-group-name')) {
            variants[e.target.dataset.g].group_name = e.target.value;
        } else if(e.target.classList.contains('var-group-type')) {
            variants[e.target.dataset.g].type = e.target.value;
        } else if(e.target.classList.contains('var-opt-name')) {
            variants[e.target.dataset.g].options[e.target.dataset.o].name = e.target.value;
        } else if(e.target.classList.contains('var-opt-price')) {
            variants[e.target.dataset.g].options[e.target.dataset.o].price = parseInt(e.target.value || 0);
        }
        inputVariants.value = JSON.stringify(variants);
    });

    variantsContainer.addEventListener('click', function(e) {
        if(e.target.closest('.add-opt')) {
            const gIndex = e.target.closest('.add-opt').dataset.g;
            variants[gIndex].options.push({ name: '', price: 0 });
            renderVariants();
        } else if(e.target.closest('.remove-opt')) {
            const btn = e.target.closest('.remove-opt');
            variants[btn.dataset.g].options.splice(btn.dataset.o, 1);
            renderVariants();
        } else if(e.target.closest('.remove-group')) {
            variants.splice(e.target.closest('.remove-group').dataset.g, 1);
            renderVariants();
        }
    });

    // Initial render
    renderVariants();
});
</script>
@endpush
@endsection
