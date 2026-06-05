@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manajemen Kasir</h2>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row">
        <!-- Kolom Kiri: Tabel Data Kasir -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>No. HP</th>
                            <th>Shift</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($kasirs as $k)
                            <tr>
                                <td>{{ $k->name }}</td>
                                <td>{{ $k->email }}</td>
                                <td>{{ $k->no_hp ?: '-' }}</td>
                                <td>
                                    @if($k->shift == 'pagi')
                                        <span class="badge bg-info">Pagi</span>
                                    @elseif($k->shift == 'malam')
                                        <span class="badge bg-dark">Malam</span>
                                    @else
                                        <span class="badge bg-secondary">-</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.kasir.edit', $k->id) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <form class="d-inline" action="{{ route('admin.kasir.destroy', $k->id) }}" method="POST" onsubmit="return confirm('Hapus akun kasir?');">
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
        <div class="card-footer">
            {{ $kasirs->links() }}
        </div>
    </div>
</div>

<!-- Kolom Kanan: Form Tambah Kasir -->
<div class="col-lg-4 mb-4">
    <div class="card shadow-sm h-100">
        <div class="card-header fw-bold">Tambah Akun Kasir</div>
        <div class="card-body">
            <form action="{{ route('admin.kasir.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-12 mb-3">
                        <label for="name" class="form-label">Nama Kasir</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                    </div>
                    <div class="col-12 mb-3">
                        <label for="email" class="form-label">Email Kasir</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required>
                    </div>
                    <div class="col-12 mb-3">
                        <label for="shift" class="form-label">Shift</label>
                        <select class="form-select" id="shift" name="shift" required>
                            <option value="pagi">Pagi</option>
                            <option value="malam">Malam</option>
                        </select>
                    </div>
                    <div class="col-12 mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="col-12 mb-3">
                        <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100">Buat Akun Kasir</button>
            </form>
        </div>
    </div>
</div>
    </div>
</div>
@endsection
