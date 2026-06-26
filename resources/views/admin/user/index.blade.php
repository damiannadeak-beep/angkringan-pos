@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manajemen User</h2>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-header fw-bold">Daftar Semua User</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Status Email</th>
                            <th>Role</th>
                            <th>No. HP</th>
                            <th>Terdaftar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @if($user->email_verified_at)
                                        <span class="badge bg-success"><i class="bi bi-check-circle-fill"></i> Terverifikasi</span>
                                    @else
                                        <span class="badge bg-danger"><i class="bi bi-x-circle-fill"></i> Palsu/Belum Verifikasi</span>
                                    @endif
                                </td>
                                <td>
                                    @foreach($user->getRoleNames() as $role)
                                        <span class="badge bg-secondary text-uppercase">{{ $role }}</span>
                                    @endforeach
                                </td>
                                <td>{{ $user->no_hp ?: '-' }}</td>
                                <td>{{ $user->created_at->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">Belum ada data user.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection
