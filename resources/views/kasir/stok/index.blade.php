@extends('layouts.kasir')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="fw-bold text-accent"><i class="bi bi-box-seam me-2"></i>Update Stok Fisik</h4>
            <p class="text-muted">Perbarui jumlah stok bahan baku dan produk jadi secara langsung. Perubahan akan disimpan dan dapat dilihat oleh admin.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('kasir.stok.update') }}" method="POST">
        @csrf
        <div class="row g-4">
            <!-- Kolom Bahan Baku -->
            <div class="col-md-6">
                <div class="card kasir-card h-100">
                    <div class="card-header bg-transparent border-bottom pt-4 pb-3 px-4">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-basket2-fill me-2 text-warning"></i>Stok Bahan Baku</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th class="ps-4">Nama Bahan</th>
                                        <th class="text-center" style="width: 150px;">Sisa Stok</th>
                                        <th class="pe-4 text-center">Satuan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($bahans as $bahan)
                                        <tr>
                                            <td class="ps-4 fw-medium">{{ $bahan->nama_bahan }}</td>
                                            <td class="text-center">
                                                <input type="number" name="bahan[{{ $bahan->id }}]" class="form-control text-center mx-auto" value="{{ $bahan->stok }}" min="0" style="width: 80px;">
                                            </td>
                                            <td class="pe-4 text-center text-muted">{{ $bahan->satuan }}</td>
                                        </tr>
                                    @endforeach
                                    @if($bahans->isEmpty())
                                        <tr><td colspan="3" class="text-center py-4 text-muted">Belum ada bahan baku.</td></tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kolom Menu (Produk Jadi) -->
            <div class="col-md-6">
                <div class="card kasir-card h-100">
                    <div class="card-header bg-transparent border-bottom pt-4 pb-3 px-4">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-cup-straw me-2 text-info"></i>Stok Produk Jadi (Menu)</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th class="ps-4">Nama Menu</th>
                                        <th class="text-center" style="width: 150px;">Sisa Stok</th>
                                        <th class="pe-4 text-center">Tersedia?</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($menus as $menu)
                                        <tr>
                                            <td class="ps-4 fw-medium">{{ $menu->nama_menu }}</td>
                                            <td class="text-center">
                                                <input type="number" id="stok-{{ $menu->id }}" name="menu[{{ $menu->id }}]" class="form-control text-center mx-auto" value="{{ $menu->stok }}" min="0" style="width: 80px;" onchange="checkAvailability({{ $menu->id }})" onkeyup="checkAvailability({{ $menu->id }})">
                                            </td>
                                            <td class="pe-4 text-center">
                                                <select id="avail-{{ $menu->id }}" name="menu_available[{{ $menu->id }}]" class="form-select form-select-sm text-center mx-auto" style="width: 80px; font-weight: bold; color: {{ $menu->is_available ? '#198754' : '#dc3545' }}" onchange="updateColor(this)">
                                                    <option value="1" {{ $menu->is_available ? 'selected' : '' }} class="text-success">Ya</option>
                                                    <option value="0" {{ !$menu->is_available ? 'selected' : '' }} class="text-danger">Tidak</option>
                                                </select>
                                            </td>
                                        </tr>
                                    @endforeach
                                    @if($menus->isEmpty())
                                        <tr><td colspan="3" class="text-center py-4 text-muted">Belum ada menu.</td></tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4 text-end">
            <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5 shadow-sm">
                <i class="bi bi-save me-2"></i> Simpan Pembaruan Stok
            </button>
        </div>
    </form>
</div>
</div>

<script>
    // Validasi otomatis saat input stok diubah
    function checkAvailability(id) {
        const stokInput = document.getElementById('stok-' + id);
        const availSelect = document.getElementById('avail-' + id);
        
        let stok = parseInt(stokInput.value) || 0;
        
        if (stok <= 0) {
            // Paksa dropdown menjadi Tidak dan warna merah
            availSelect.value = "0";
            availSelect.style.color = '#dc3545';
        } else {
            // Kembalikan menjadi Ya dan warna hijau jika stok > 0 dan sebelumnya habis
            if(availSelect.value === "0" && availSelect.dataset.wasZero) {
                availSelect.value = "1";
                availSelect.style.color = '#198754';
                availSelect.dataset.wasZero = '';
            }
        }
        
        if(stok <= 0) availSelect.dataset.wasZero = 'true';
    }

    function updateColor(select) {
        select.style.color = select.value === '1' ? '#198754' : '#dc3545';
        
        // Cek jika Kasir memaksa "Ya" tapi stok 0
        const id = select.id.split('-')[1];
        const stokInput = document.getElementById('stok-' + id);
        if (select.value === '1' && (parseInt(stokInput.value) || 0) <= 0) {
            alert('Stok masih kosong! Tidak dapat diatur menjadi Tersedia.');
            select.value = '0';
            select.style.color = '#dc3545';
        }
    }

    // Inisialisasi awal
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('input[id^="stok-"]').forEach(input => {
            const id = input.id.split('-')[1];
            checkAvailability(id);
        });
    });
</script>
@endsection
