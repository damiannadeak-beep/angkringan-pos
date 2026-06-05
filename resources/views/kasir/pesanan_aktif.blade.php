@extends('layouts.kasir')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Pesanan Konsumen Aktif</h4>
        <button class="btn btn-outline-secondary btn-sm" onclick="location.reload()">
            <i class="bi bi-arrow-clockwise"></i> Segarkan Data
        </button>
    </div>

    <div class="row g-4">
        @forelse($orders as $order)
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm border-0 h-100 rounded-4">
                    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center rounded-top-4">
                        <div>
                            <h6 class="fw-bold mb-1">Pesanan #{{ $order->id }}</h6>
                            <small class="text-muted"><i class="bi bi-clock me-1"></i>{{ $order->created_at->format('H:i') }} WIB</small>
                        </div>
                        <div class="text-end">
                            @if($order->status === 'pending')
                                <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split"></i> PENDING</span>
                            @elseif($order->status === 'processing')
                                <span class="badge bg-primary"><i class="bi bi-fire"></i> DIMASAK</span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="card-body bg-light bg-opacity-50">
                        <div class="d-flex mb-3 align-items-center">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-2 me-3">
                                <i class="bi bi-geo-alt-fill"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold">{{ $order->tipe_pesanan == 'takeaway' ? 'Takeaway' : 'Dine-In' }}</h6>
                                <small class="text-muted">{{ $order->meja->nama_meja_atau_nomor ?? 'Bungkus' }}</small>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-sm table-borderless mb-0">
                                <tbody>
                                    @foreach($order->detail_pesanan as $item)
                                        <tr>
                                            <td class="text-muted" style="width: 30px;">{{ $item->jumlah }}x</td>
                                            <td class="fw-medium">{{ $item->menu->nama_menu ?? 'Menu tidak ditemukan' }}</td>
                                            <td class="text-end text-muted">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card-footer bg-white pt-3 pb-3 rounded-bottom-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted small">Total Tagihan</span>
                            <h6 class="fw-bold text-primary mb-0">Rp {{ number_format($order->total, 0, ',', '.') }}</h6>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted small">Status Bayar</span>
                            @if($order->pembayaran && $order->pembayaran->status === 'paid')
                                <span class="badge bg-success bg-opacity-10 text-success border border-success"><i class="bi bi-check-circle"></i> Lunas</span>
                            @else
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger"><i class="bi bi-x-circle"></i> Belum Lunas</span>
                            @endif
                        </div>

                        <div class="d-flex flex-wrap gap-2 mt-3 pt-2 border-top">
                            @if($order->status === 'pending')
                                <button class="btn btn-sm btn-primary flex-grow-1 fw-bold" onclick="updateOrderStatus({{ $order->id }}, 'processing')">
                                    <i class="bi bi-play-circle me-1"></i> Proses Masak
                                </button>
                            @endif
                            
                            @if($order->status === 'processing')
                                <a href="{{ route('kasir.order.kitchen', $order->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary flex-grow-1 fw-bold">
                                    <i class="bi bi-printer me-1"></i> Struk Dapur
                                </a>
                                <button class="btn btn-sm btn-success flex-grow-1 fw-bold" onclick="updateOrderStatus({{ $order->id }}, 'completed')">
                                    <i class="bi bi-check2-all me-1"></i> Selesai
                                </button>
                            @endif

                            @if(!$order->pembayaran || $order->pembayaran->status !== 'paid')
                                <button class="btn btn-sm btn-outline-danger flex-grow-1 fw-bold" onclick="payOrder({{ $order->id }})">
                                    <i class="bi bi-cash-stack me-1"></i> Terima Bayar
                                </button>
                                <button class="btn btn-sm btn-danger flex-grow-1 fw-bold" onclick="voidOrder({{ $order->id }})">
                                    <i class="bi bi-trash me-1"></i> Void
                                </button>
                                <div class="w-100 m-0 p-0"></div> <!-- Break line -->
                                <button class="btn btn-sm btn-outline-warning w-100 fw-bold mt-1" onclick="openSplitModal({{ $order->id }}, {{ htmlspecialchars(json_encode($order->detail_pesanan)) }})">
                                    <i class="bi bi-layout-split me-1"></i> Pisah Bon (Split Bill)
                                </button>
                            @else
                                <a href="{{ route('kasir.order.receipt', $order->id) }}" target="_blank" class="btn btn-sm btn-outline-primary flex-grow-1 fw-bold">
                                    <i class="bi bi-printer me-1"></i> Cetak Struk
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <div class="d-inline-block bg-light p-4 rounded-circle mb-3">
                    <i class="bi bi-receipt text-muted" style="font-size: 3rem;"></i>
                </div>
                <h5 class="fw-bold text-muted">Belum Ada Pesanan Masuk</h5>
                <p class="text-muted">Pesanan dari konsumen akan muncul di sini.</p>
            </div>
        @endforelse
    </div>
</div>

<!-- Modal Pilih Pembayaran -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-body text-center p-4">
                <h5 class="fw-bold mb-3">Pilih Metode Pembayaran</h5>
                <div class="mb-4 text-start">
                    <label class="form-label small text-muted fw-bold">Kirim E-Receipt ke Email (Opsional)</label>
                    <input type="email" id="email_pelanggan" class="form-control form-control-sm" placeholder="email@contoh.com">
                    <small class="text-muted" style="font-size: 11px;">Kosongkan jika pelanggan tidak butuh struk digital.</small>
                </div>
                <button type="button" class="btn btn-success w-100 fw-bold mb-2 py-2" onclick="processPayment('cash')">
                    <i class="bi bi-cash me-2"></i> Uang Tunai (Cash)
                </button>
                <button type="button" class="btn btn-primary w-100 fw-bold py-2" onclick="processPayment('qris')">
                    <i class="bi bi-qr-code-scan me-2"></i> QRIS
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal QRIS Scan -->
<div class="modal fade" id="qrisScanModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow text-center">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 pt-0">
                <h5 class="fw-bold mb-3">Scan QRIS</h5>
                @php $qrisImage = \App\Models\Setting::getVal('qris_image'); @endphp
                @if($qrisImage)
                    <img src="{{ asset('storage/'.$qrisImage) }}" alt="QRIS" class="img-fluid rounded border p-2 mb-3">
                    <p class="small text-muted mb-3">Minta pelanggan scan barcode ini.</p>
                    <button type="button" class="btn btn-primary w-100 fw-bold" onclick="executePayment('qris')">Sudah Dibayar & Selesai</button>
                @else
                    <p class="text-danger small fw-bold">Gambar QRIS belum diatur oleh Admin.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Pisah Bon -->
<div class="modal fade" id="splitModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold mb-0">Pisah Tagihan (Pesanan #<span id="split-order-id"></span>)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 pt-3">
                <p class="small text-muted mb-3">Pilih item mana dan jumlahnya yang ingin dipisah menjadi bon baru.</p>
                <div id="split-items-container" class="mb-3">
                    <!-- Checkboxes will be rendered here -->
                </div>
                <button type="button" class="btn btn-warning w-100 fw-bold" onclick="executeSplit()">Proses Pisah Bon</button>
            </div>
        </div>
    </div>
</div>

<script>
    // JS Logic untuk Update Status dan Pay Order (sama dengan di POS)
    function updateOrderStatus(id, status) {
        if(!confirm('Ubah status pesanan ini?')) return;
        
        fetch(`/kasir/order/${id}/status`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ status: status })
        })
        .then(res => res.json())
        .then(data => {
            if(data.error) alert(data.error);
            else {
                alert('Status diperbarui!');
                location.reload();
            }
        });
    }

    function voidOrder(id) {
        let alasan = prompt('Masukkan alasan mem-VOID pesanan ini (Wajib):');
        if(!alasan) return; // batalkan jika tidak mengisi alasan
        
        if(!confirm('Anda yakin ingin mem-VOID pesanan ini? Stok akan dikembalikan dan pesanan dibatalkan.')) return;
        
        fetch(`/kasir/order/${id}/void`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ alasan: alasan })
        })
        .then(res => res.json())
        .then(data => {
            if(data.error) alert(data.error);
            else {
                alert(data.message);
                location.reload();
            }
        });
    }

    let currentOrderId = null;
    let paymentModal = null;
    let qrisModal = null;
    let splitModal = null;
    let splitOrderId = null;
    let splitDetails = [];

    document.addEventListener('DOMContentLoaded', function() {
        paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));
        qrisModal = new bootstrap.Modal(document.getElementById('qrisScanModal'));
        splitModal = new bootstrap.Modal(document.getElementById('splitModal'));
    });

    function payOrder(id) {
        currentOrderId = id;
        document.getElementById('email_pelanggan').value = ''; // Reset input email
        paymentModal.show();
    }

    function processPayment(method) {
        paymentModal.hide();
        if (method === 'qris') {
            qrisModal.show();
        } else {
            executePayment('cash');
        }
    }

    function executePayment(method) {
        if(qrisModal) qrisModal.hide();
        let emailVal = document.getElementById('email_pelanggan').value;

        fetch(`/kasir/order/${currentOrderId}/pay`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ metode: method, email_pelanggan: emailVal })
        })
        .then(res => res.json())
        .then(data => {
            if(data.error) alert(data.error);
            else {
                if (confirm('Pembayaran berhasil! Cetak struk sekarang?')) {
                    window.open(`/kasir/order/${currentOrderId}/receipt`, '_blank');
                }
                location.reload();
            }
        });
    }

    function openSplitModal(orderId, details) {
        splitOrderId = orderId;
        splitDetails = details;
        document.getElementById('split-order-id').innerText = orderId;

        let html = '';
        details.forEach(item => {
            html += `
            <div class="d-flex align-items-center mb-2">
                <div class="form-check flex-grow-1">
                    <input class="form-check-input split-cb" type="checkbox" value="${item.id}" id="chk_${item.id}">
                    <label class="form-check-label" for="chk_${item.id}">
                        ${item.menu ? item.menu.nama_menu : 'Menu'} (Rp ${item.subtotal.toLocaleString('id-ID')})
                    </label>
                </div>
                <div style="width: 80px;">
                    <input type="number" class="form-control form-control-sm text-center split-qty" id="qty_${item.id}" value="1" min="1" max="${item.jumlah}" disabled>
                </div>
                <span class="ms-2 small text-muted">/ ${item.jumlah}</span>
            </div>
            `;
        });
        document.getElementById('split-items-container').innerHTML = html;

        // Add event listeners to checkboxes
        document.querySelectorAll('.split-cb').forEach(cb => {
            cb.addEventListener('change', function() {
                document.getElementById('qty_' + this.value).disabled = !this.checked;
            });
        });

        splitModal.show();
    }

    function executeSplit() {
        let itemsToSplit = [];
        document.querySelectorAll('.split-cb:checked').forEach(cb => {
            let idDetail = cb.value;
            let qty = document.getElementById('qty_' + idDetail).value;
            itemsToSplit.push({
                id_detail: idDetail,
                jumlah: parseInt(qty)
            });
        });

        if (itemsToSplit.length === 0) {
            alert('Pilih minimal 1 item untuk dipisah.');
            return;
        }

        // prevent splitting ALL items
        let isAll = true;
        splitDetails.forEach(sd => {
            let found = itemsToSplit.find(i => i.id_detail == sd.id);
            if (!found || found.jumlah < sd.jumlah) {
                isAll = false;
            }
        });

        if (isAll) {
            alert('Anda tidak bisa memisah semua item. Itu sama saja dengan memindahkan pesanan utuh.');
            return;
        }

        if(!confirm('Pisahkan item terpilih ke pesanan (Bon) baru? Diskon/Promo dari pesanan ini akan dihapus jika ada.')) return;

        fetch(`/kasir/order/${splitOrderId}/split`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ split_items: itemsToSplit })
        })
        .then(res => res.json())
        .then(data => {
            if(data.error) alert(data.error);
            else {
                alert(data.message);
                location.reload();
            }
        });
    }
</script>
@endsection
