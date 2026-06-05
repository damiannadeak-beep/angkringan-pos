@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Edit Kasir</h2>
        <a href="{{ route('admin.kasir.index') }}" class="btn btn-secondary">Kembali</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('admin.kasir.update', $kasir->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Nama</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $kasir->name) }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $kasir->email) }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">No. HP</label>
                    <input type="text" name="no_hp" class="form-control" value="{{ old('no_hp', $kasir->no_hp) }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Shift</label>
                    <select class="form-select" name="shift" required>
                        <option value="pagi" {{ old('shift', $kasir->shift) == 'pagi' ? 'selected' : '' }}>Pagi</option>
                        <option value="malam" {{ old('shift', $kasir->shift) == 'malam' ? 'selected' : '' }}>Malam</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password (kosongkan jika tidak ingin mengubah)</label>
                    <input type="password" name="password" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" class="form-control">
                </div>
                <button class="btn btn-primary">Simpan Perubahan</button>
            </form>
        </div>
    </div>
</div>
@endsection
