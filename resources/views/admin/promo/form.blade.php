@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>{{ $promo->exists ? 'Edit Promo' : 'Buat Promo' }}</h2>
        <a href="{{ route('admin.promo.index') }}" class="btn btn-secondary">Kembali</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
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
                <div class="mb-3">
                    <label class="form-label">Tipe</label>
                    <select name="type" class="form-control">
                        <option value="discount" {{ old('type', $promo->type) == 'discount' ? 'selected' : '' }}>Diskon</option>
                        <option value="package" {{ old('type', $promo->type) == 'package' ? 'selected' : '' }}>Paket</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Nilai</label>
                    <input type="number" step="0.01" name="value" class="form-control" value="{{ old('value', $promo->value) }}" required>
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
                    <label class="form-check-label" for="is_active">Aktif</label>
                </div>
                <button class="btn btn-primary">Simpan</button>
            </form>
        </div>
    </div>
</div>
@endsection
