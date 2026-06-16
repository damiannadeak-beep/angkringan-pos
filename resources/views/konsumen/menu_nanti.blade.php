@extends('layouts.app')

@section('content')
<div class="container pb-5 mb-5">
    <div class="alert alert-primary d-flex justify-content-between align-items-center shadow-sm">
        <div>
            <h5 class="mb-0 fw-bold"><i class="bi bi-geo-alt"></i> Makan di Tempat (Datang Nanti)</h5>
            <small>Pesan sekarang, pilih meja saat Anda tiba di lokasi</small>
        </div>
        <i class="bi bi-shop fs-1 text-primary opacity-50"></i>
    </div>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mt-4 mb-3 gap-2">
        <h5 class="fw-bold mb-0">Menu Tersedia</h5>
        <div class="btn-group shadow-sm" role="group">
            <button type="button" class="btn btn-outline-success active btn-filter" onclick="filterMenu('semua', this)">Semua</button>
            <button type="button" class="btn btn-outline-success btn-filter" onclick="filterMenu('makanan', this)">Makanan</button>
            <button type="button" class="btn btn-outline-success btn-filter" onclick="filterMenu('minuman', this)">Minuman</button>
        </div>
    </div>
    <div class="row g-4">
        @foreach($menus as $menu)
        <div class="col-12 col-md-6 col-lg-4 menu-item" data-kategori="{{ $menu->kategori }}">
            <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden bg-white" style="transition: transform 0.2s;">
                <div class="row g-0 h-100">
                    @if($menu->image)
                    <div class="col-4">
                        <img src="{{ asset('storage/'.$menu->image) }}" alt="{{ $menu->nama_menu }}" class="img-fluid h-100 w-100" style="object-fit: cover;">
                    </div>
                    @endif
                    <div class="{{ $menu->image ? 'col-8' : 'col-12' }}">
                        <div class="card-body d-flex flex-column h-100 p-3">
                            <h6 class="fw-bold text-dark mb-1">{{ $menu->nama_menu }}</h6>
                            <small class="text-muted mb-2 text-truncate" style="max-width: 100%; display: block;">{{ $menu->deskripsi ?? 'Tanpa deskripsi' }}</small>
                            <h6 class="text-success fw-bold mt-auto mb-3">Rp {{ number_format($menu->harga, 0, ',', '.') }}</h6>
                            
                            <div class="d-flex justify-content-end align-items-center mt-auto gap-3">
                                <button class="btn btn-outline-danger rounded-circle p-0 d-flex justify-content-center align-items-center shadow-sm" 
                                        onclick="removeFromCart({{ $menu->id }})"
                                        style="width: 32px; height: 32px; transition: all 0.2s;">
                                    <i class="bi bi-dash fs-5"></i>
                                </button>
                                <span id="qty-{{ $menu->id }}" class="fw-bold fs-5 mb-0" style="min-width: 15px; text-align: center;">0</span>
                                <button class="btn btn-success rounded-circle p-0 d-flex justify-content-center align-items-center shadow-sm" 
                                        onclick="addToCart({{ $menu->id }}, '{{ $menu->nama_menu }}', {{ $menu->harga }})"
                                        style="width: 32px; height: 32px; transition: all 0.2s;">
                                    <i class="bi bi-plus fs-5"></i>
                                </button>
                            </div>
                            
                            <div id="catatan-container-{{ $menu->id }}" class="mt-3" style="display: none;">
                                <input type="text" id="catatan-input-{{ $menu->id }}" class="form-control form-control-sm border-1 bg-light rounded-3 px-3" placeholder="Tambah catatan (opsional)..." onchange="updateCatatan({{ $menu->id }}, this.value)">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

<div class="fixed-bottom bg-white shadow-lg" style="z-index: 1030; border-radius: 24px 24px 0 0; border-top: 1px solid #eaeaea;">
    <div class="container px-3 py-3">
        <div class="mb-3">
            <select name="promo_id" id="promo_id" class="form-select form-select-sm border-success bg-success bg-opacity-10 text-success fw-bold rounded-pill px-3 py-2" onchange="updateCartUI()">
                <option value="">🎟️ Tambah Promo (Opsional)</option>
                @foreach($promos as $promo)
                    <option value="{{ $promo->id }}" data-type="{{ $promo->type }}" data-value="{{ $promo->value }}">
                        {{ $promo->title }} 
                        @if($promo->type == 'discount')
                            ({{ $promo->value <= 100 ? $promo->value.'%' : 'Rp '.number_format($promo->value,0,',','.') }})
                        @endif
                    </option>
                @endforeach
            </select>
        </div>
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <small class="text-muted fw-bold d-block mb-0" style="font-size: 0.75rem;">Total Tagihan</small>
                <div class="d-flex align-items-baseline gap-2">
                    <h4 class="fw-bold text-dark mb-0" id="cart-total">Rp 0</h4>
                    <span id="cart-qty" class="badge bg-success rounded-pill px-2">0 Item</span>
                </div>
            </div>
            <button onclick="submitCustomerOrder()" class="btn btn-success px-4 py-2 fw-bold rounded-pill shadow-sm" style="transition: transform 0.2s;">
                Pesan <i class="bi bi-cart-check-fill ms-1"></i>
            </button>
        </div>
    </div>
</div>

<script>
    let cart = [];

    function filterMenu(category, btn) {
        document.querySelectorAll('.btn-filter').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        document.querySelectorAll('.menu-item').forEach(item => {
            if (category === 'semua' || item.getAttribute('data-kategori') === category) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    }

    function addToCart(id, name, price) {
        let item = cart.find(i => i.id_menu === id);
        if (item) {
            item.jumlah++;
        } else {
            cart.push({ id_menu: id, nama: name, harga: price, jumlah: 1, catatan: '' });
        }
        updateCartUI();
    }

    function updateCatatan(id, val) {
        let item = cart.find(i => i.id_menu === id);
        if (item) {
            item.catatan = val;
        }
    }

    function removeFromCart(id) {
        let itemIndex = cart.findIndex(i => i.id_menu === id);
        if (itemIndex !== -1) {
            if (cart[itemIndex].jumlah > 1) {
                cart[itemIndex].jumlah--;
            } else {
                cart.splice(itemIndex, 1);
            }
            updateCartUI();
        }
    }

    function updateCartUI() {
        let total = 0;
        let qty = 0;
        
        // Reset all qty displays to 0
        document.querySelectorAll('[id^="qty-"]').forEach(el => el.innerText = '0');
        document.querySelectorAll('[id^="catatan-container-"]').forEach(el => el.style.display = 'none');

        cart.forEach(item => {
            total += (item.harga * item.jumlah);
            qty += item.jumlah;
            
            let qtyDisplay = document.getElementById('qty-' + item.id_menu);
            if(qtyDisplay) qtyDisplay.innerText = item.jumlah;

            let catatanContainer = document.getElementById('catatan-container-' + item.id_menu);
            if(catatanContainer) {
                catatanContainer.style.display = 'block';
                let input = document.getElementById('catatan-input-' + item.id_menu);
                if (input && input.value !== item.catatan) {
                    input.value = item.catatan || '';
                }
            }
        });
        
        let discount = 0;
        const promoSelect = document.getElementById('promo_id');
        if (promoSelect && promoSelect.value) {
            const option = promoSelect.options[promoSelect.selectedIndex];
            const pType = option.getAttribute('data-type');
            const pValue = parseFloat(option.getAttribute('data-value'));
            if (pType === 'discount') {
                if (pValue <= 100) {
                    discount = total * (pValue / 100);
                } else {
                    discount = pValue;
                }
            }
            if(discount > total) discount = total;
        }
        
        let totalTagihan = total - discount;
        
        let tagihanHtml = 'Rp ' + totalTagihan.toLocaleString('id-ID');
        if (discount > 0) {
            tagihanHtml = `<span class="text-decoration-line-through text-muted small fs-6">Rp ${total.toLocaleString('id-ID')}</span><br>Rp ${totalTagihan.toLocaleString('id-ID')}`;
        }
        document.getElementById('cart-total').innerHTML = tagihanHtml;
        document.getElementById('cart-qty').innerText = qty + ' Item';
    }

    function submitCustomerOrder() {
        if (cart.length === 0) return alert('Silakan pilih menu terlebih dahulu!');

        if (!confirm('Apakah pesanan Anda sudah benar?')) return;

        // Untuk pesan nanti, tipe_pesanan = dine_in, id_meja = null
        let formData = {
            _token: "{{ csrf_token() }}",
            tipe_pesanan: 'dine_in',
            id_meja: null,
            promo_id: document.getElementById('promo_id').value,
            items: cart
        };

        fetch("{{ url('/konsumen/order/add') }}", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(formData)
        })
        .then(res => res.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
            } else {
                window.location.href = "/konsumen/checkout/" + data.id_pesanan;
            }
        })
        .catch(err => console.error(err));
    }
</script>
@endsection
