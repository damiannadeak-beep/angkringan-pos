@extends('layouts.kasir')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold text-accent"><i class="bi bi-grid-3x3-gap-fill me-2"></i>Manajemen Meja</h4>
            <p class="text-muted mb-0">Kelola status ketersediaan meja untuk pengunjung</p>
        </div>
    </div>

    <div class="row g-4">
        @forelse($mejas as $meja)
            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
                <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden {{ !$meja->is_available ? 'bg-light' : 'bg-white' }}" id="card-meja-{{ $meja->id }}">
                    <div class="card-body p-4 text-center">
                        <div class="mb-3 d-flex justify-content-center">
                            <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm" 
                                 id="icon-meja-{{ $meja->id }}"
                                 style="width: 80px; height: 80px; background-color: {{ $meja->is_available ? '#e8f5e9' : '#ffebee' }}; color: {{ $meja->is_available ? '#2e7d32' : '#c62828' }};">
                                <i class="bi bi-shop" style="font-size: 2.5rem;"></i>
                            </div>
                        </div>
                        <h5 class="fw-bold mb-1">{{ $meja->nama_meja_atau_nomor }}</h5>
                        <p class="text-muted small mb-3">{{ $meja->keterangan ?? 'Meja Pelanggan' }}</p>
                        
                        <div class="d-flex align-items-center justify-content-center gap-2">
                            <span class="small fw-bold {{ $meja->is_available ? 'text-success' : 'text-muted' }}" id="label-on-{{ $meja->id }}">Tersedia</span>
                            <div class="form-check form-switch fs-4 mb-0">
                                <input class="form-check-input" type="checkbox" role="switch" 
                                       id="switch-meja-{{ $meja->id }}" 
                                       onchange="toggleMeja({{ $meja->id }})" 
                                       {{ $meja->is_available ? 'checked' : '' }}
                                       style="cursor: pointer;">
                            </div>
                        </div>
                        <div class="mt-2">
                            <span class="badge rounded-pill {{ $meja->is_available ? 'bg-success' : 'bg-danger' }}" id="badge-meja-{{ $meja->id }}">
                                {{ $meja->is_available ? 'Tersedia' : 'Terisi' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-warning border-0 shadow-sm rounded-4 text-center py-5">
                    <i class="bi bi-info-circle" style="font-size: 3rem;"></i>
                    <h5 class="mt-3 fw-bold">Belum Ada Meja</h5>
                    <p class="mb-0">Data meja belum ditambahkan oleh Admin.</p>
                </div>
            </div>
        @endforelse
    </div>
</div>

<script>
    function toggleMeja(id) {
        const switchBtn = document.getElementById(`switch-meja-${id}`);
        const card = document.getElementById(`card-meja-${id}`);
        const icon = document.getElementById(`icon-meja-${id}`);
        const labelOn = document.getElementById(`label-on-${id}`);
        const badge = document.getElementById(`badge-meja-${id}`);
        
        // Optimistic UI Update
        const isNowAvailable = switchBtn.checked;
        updateUI(id, isNowAvailable);

        fetch(`/kasir/meja/${id}/toggle`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert('Gagal: ' + data.error);
                // Revert UI
                switchBtn.checked = !isNowAvailable;
                updateUI(id, !isNowAvailable);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan jaringan.');
            // Revert UI
            switchBtn.checked = !isNowAvailable;
            updateUI(id, !isNowAvailable);
        });
    }

    function updateUI(id, isAvailable) {
        const card = document.getElementById(`card-meja-${id}`);
        const icon = document.getElementById(`icon-meja-${id}`);
        const labelOn = document.getElementById(`label-on-${id}`);
        const badge = document.getElementById(`badge-meja-${id}`);

        if (isAvailable) {
            card.classList.remove('bg-light');
            card.classList.add('bg-white');
            icon.style.backgroundColor = '#e8f5e9';
            icon.style.color = '#2e7d32';
            labelOn.classList.remove('text-muted');
            labelOn.classList.add('text-success');
            badge.classList.remove('bg-danger');
            badge.classList.add('bg-success');
            badge.innerText = 'Tersedia';
        } else {
            card.classList.remove('bg-white');
            card.classList.add('bg-light');
            icon.style.backgroundColor = '#ffebee';
            icon.style.color = '#c62828';
            labelOn.classList.remove('text-success');
            labelOn.classList.add('text-muted');
            badge.classList.remove('bg-success');
            badge.classList.add('bg-danger');
            badge.innerText = 'Terisi';
        }
    }
</script>
@endsection
