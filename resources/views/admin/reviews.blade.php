@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Ulasan Pelanggan</h2>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Kembali ke Dashboard</a>
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
                            <th>#</th>
                            <th>Konsumen</th>
                            <th>Rating</th>
                            <th>Komentar</th>
                            <th>Balasan Admin</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reviews as $r)
                            <tr>
                                <td>{{ $r->id }}</td>
                                <td>{{ $r->konsumen->name ?? 'Tidak Diketahui' }}</td>
                                <td>{{ $r->rating }}</td>
                                <td>{{ $r->komentar }}</td>
                                <td>{{ $r->balasan_admin ?? '-' }}</td>
                                <td>{{ \Carbon\Carbon::parse($r->tanggal)->format('d M Y H:i') }}</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#reply-{{ $r->id }}">Balas</button>
                                </td>
                            </tr>
                            <tr class="collapse" id="reply-{{ $r->id }}">
                                <td colspan="7">
                                    <form action="{{ route('admin.reviews.reply', $r->id) }}" method="POST">
                                        @csrf
                                        <div class="mb-2">
                                            <textarea name="balasan_admin" class="form-control" rows="3" placeholder="Tulis balasan...">{{ old('balasan_admin', $r->balasan_admin) }}</textarea>
                                        </div>
                                        <div class="d-flex justify-content-end">
                                            <button class="btn btn-primary btn-sm">Kirim Balasan</button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            {{ $reviews->links() }}
        </div>
    </div>
</div>
@endsection
