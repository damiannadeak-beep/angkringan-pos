@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>{{ $promo->exists ? 'Edit Promo' : 'Buat Promo' }}</h2>
        <a href="{{ route('admin.promo.index') }}" class="btn btn-secondary">Kembali</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ $promo->exists ? route('admin.promo.update', $promo->id) : route('admin.promo.store') }}" method="POST">
                @csrf
                @if($promo->exists) @method('PUT') @endif

                <div class="mb-3">
                    <label class="form-label">Judul</label>
                    <input type="text" name="title" class="form-control" value="{{ old('title', $promo->title) }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" class="form-control">{{ old('description', $promo->description) }}</textarea>
                </div>
                
                <!-- NEW: Filter Hari -->
                <div class="mb-3">
                    <label class="form-label d-block">Berlaku di Hari <small class="text-muted">(Pilih hari promo aktif. Kosongkan jika berlaku setiap hari)</small></label>
                    <div>
                        @php
                            $selectedDays = old('days', $promo->days ?? []);
                            $days = ['Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'];
                        @endphp
                        @foreach($days as $en => $id)
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="days[]" value="{{ $en }}" id="day_{{ $en }}" {{ is_array($selectedDays) && in_array($en, $selectedDays) ? 'checked' : '' }}>
                                <label class="form-check-label" for="day_{{ $en }}">{{ $id }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Tipe Promo</label>
                    <select name="type" class="form-control">
                        <option value="discount" {{ old('type', $promo->type) == 'discount' ? 'selected' : '' }}>Diskon Reguler (Persen/Nominal)</option>
                        <option value="package" {{ old('type', $promo->type) == 'package' ? 'selected' : '' }}>Paket (Gabungan Menu)</option>
                    </select>
                </div>
                
                <!-- NEW: Dynamic Form for Package -->
                <div id="paket-menu-container" class="mb-3 p-3 border rounded bg-light" style="{{ old('type', $promo->type) == 'package' ? '' : 'display: none;' }}">
                    <label class="form-label fw-bold"><i class="bi bi-box-seam me-1"></i> Daftar Menu dalam Paket ini</label>
                    <p class="small text-muted mb-2">Pilih menu-menu apa saja dan jumlahnya yang harus dibeli pelanggan untuk mendapatkan harga Paket ini.</p>
                    
                    <div id="paket-items">
                        @php
                            $existingMenus = [];
                            if ($promo->exists && $promo->type === 'package') {
                                $existingMenus = $promo->menus;
                            }
                            $allMenus = \App\Models\Menu::where('is_available', true)->get();
                        @endphp
                        @if(count($existingMenus) > 0)
                            @foreach($existingMenus as $em)
                            <div class="row mb-2 paket-item-row align-items-center">
                                <div class="col-sm-8 mb-2 mb-sm-0">
                                    <select name="package_menus[]" class="form-control">
                                        @foreach($allMenus as $m)
                                            <option value="{{ $m->id }}" {{ $m->id == $em->id ? 'selected' : '' }}>{{ $m->nama_menu }} (Rp {{ number_format($m->harga, 0, ',', '.') }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-sm-3 mb-2 mb-sm-0">
                                    <div class="input-group">
                                        <span class="input-group-text">Qty</span>
                                        <input type="number" name="package_qty[]" class="form-control text-center" value="{{ $em->pivot->jumlah }}" min="1">
                                    </div>
                                </div>
                                <div class="col-sm-1 text-end">
                                    <button type="button" class="btn btn-outline-danger remove-paket-row"><i class="bi bi-trash"></i></button>
                                </div>
                            </div>
                            @endforeach
                        @else
                            <div class="row mb-2 paket-item-row align-items-center">
                                <div class="col-sm-8 mb-2 mb-sm-0">
                                    <select name="package_menus[]" class="form-control">
                                        <option value="">-- Pilih Menu --</option>
                                        @foreach($allMenus as $m)
                                            <option value="{{ $m->id }}">{{ $m->nama_menu }} (Rp {{ number_format($m->harga, 0, ',', '.') }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-sm-3 mb-2 mb-sm-0">
                                    <div class="input-group">
                                        <span class="input-group-text">Qty</span>
                                        <input type="number" name="package_qty[]" class="form-control text-center" value="1" min="1">
                                    </div>
                                </div>
                                <div class="col-sm-1 text-end">
                                    <button type="button" class="btn btn-outline-danger remove-paket-row"><i class="bi bi-trash"></i></button>
                                </div>
                            </div>
                        @endif
                    </div>
                    <button type="button" id="add-paket-row" class="btn btn-sm btn-primary mt-2"><i class="bi bi-plus"></i> Tambah Menu Lain ke Paket</button>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tipe Diskon <small class="text-muted">(Abaikan jika Tipe = Paket)</small></label>
                        <select name="discount_type" class="form-control">
                            <option value="percentage" {{ old('discount_type', $promo->discount_type ?? 'percentage') == 'percentage' ? 'selected' : '' }}>Persentase (%)</option>
                            <option value="nominal" {{ old('discount_type', $promo->discount_type ?? 'percentage') == 'nominal' ? 'selected' : '' }}>Nominal (Rp)</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nilai <small class="text-muted">(Jika Tipe=Paket, ini adalah Harga Akhir Paket)</small></label>
                        <input type="number" step="0.01" name="value" class="form-control" value="{{ old('value', $promo->value ? ($promo->value + 0) : '') }}" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Mulai</label>
                        <input type="datetime-local" name="starts_at" class="form-control" value="{{ old('starts_at', $promo->starts_at? $promo->starts_at->format('Y-m-d\TH:i') : '') }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Berakhir</label>
                        <input type="datetime-local" name="ends_at" class="form-control" value="{{ old('ends_at', $promo->ends_at? $promo->ends_at->format('Y-m-d\TH:i') : '') }}">
                    </div>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" name="is_active" class="form-check-input" id="is_active" {{ old('is_active', $promo->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">Promo Sedang Aktif</label>
                </div>
                <button class="btn btn-success fw-bold px-4 py-2"><i class="bi bi-save me-2"></i>Simpan Promo</button>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const typeSelect = document.querySelector('select[name="type"]');
        const paketContainer = document.getElementById('paket-menu-container');
        const discountTypeSelect = document.querySelector('select[name="discount_type"]');
        
        typeSelect.addEventListener('change', function() {
            if(this.value === 'package') {
                paketContainer.style.display = 'block';
                // discount_type is ignored, but we can set it to nominal to be safe
                discountTypeSelect.value = 'nominal';
            } else {
                paketContainer.style.display = 'none';
            }
        });

        document.getElementById('add-paket-row').addEventListener('click', function() {
            let container = document.getElementById('paket-items');
            let firstRow = container.querySelector('.paket-item-row');
            if(firstRow) {
                let clone = firstRow.cloneNode(true);
                // reset values
                clone.querySelector('select').selectedIndex = 0;
                clone.querySelector('input[type="number"]').value = 1;
                container.appendChild(clone);
            }
        });

        document.getElementById('paket-items').addEventListener('click', function(e) {
            let btn = e.target.closest('.remove-paket-row');
            if(btn) {
                let rows = document.querySelectorAll('.paket-item-row');
                if(rows.length > 1) {
                    btn.closest('.paket-item-row').remove();
                } else {
                    alert('Minimal 1 menu harus ada dalam paket.');
                }
            }
        });
    });
</script>
@endsection
