@extends('layouts.app')

@section('content')
<div class="container pb-5 mb-5">
    <div class="alert alert-success d-flex justify-content-between align-items-center shadow-sm">
        <div>
            <h5 class="mb-0 fw-bold"><i class="fas fa-bag-shopping"></i> Pesanan Dibawa Pulang</h5>
            <small>Silakan pilih menu Anda</small>
        </div>
        <i class="bi bi-shop fs-1 text-success opacity-50"></i>
    </div>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mt-4 mb-3 gap-2">
        <h5 class="fw-bold mb-0">Menu Tersedia</h5>
        <div class="btn-group shadow-sm" role="group">
            <button type="button" class="btn btn-outline-success active btn-filter" onclick="filterMenu('semua', this)">Semua</button>
            <button type="button" class="btn btn-outline-success btn-filter" onclick="filterMenu('makanan', this)">Makanan</button>
            <button type="button" class="btn btn-outline-success btn-filter" onclick="filterMenu('minuman', this)">Minuman</button>
        </div>
    </div>
    <div class="row g-3">
        @foreach($menus as $menu)
        <div class="col-12 col-md-6 menu-item" data-kategori="{{ $menu->kategori }}">
            <div class="card shadow-sm border-0 flex-row">
                @if($menu->image)
                    <img src="{{ asset('storage/'.$menu->image) }}" alt="{{ $menu->nama_menu }}" class="img-fluid" style="width: 120px; object-fit: cover;">
                @endif
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="fw-bold mb-1">{{ $menu->nama_menu }}</h6>
                            <p class="text-success fw-bold mb-0">Rp {{ number_format($menu->harga, 0, ',', '.') }}</p>
                            <small class="text-muted">{{ $menu->deskripsi }}</small>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button class="btn btn-sm btn-outline-danger rounded-circle" 
                                    onclick="removeFromCart({{ $menu->id }})"
                                    style="width: 35px; height: 35px;">
                                <span class="fw-bold fs-5" style="line-height: 0;">-</span>
                            </button>
                            <span id="qty-{{ $menu->id }}" class="fw-bold">0</span>
                            <button class="btn btn-sm btn-outline-success rounded-circle" 
                                    onclick="addToCart({{ $menu->id }}, '{{ $menu->nama_menu }}', {{ $menu->harga }})"
                                    style="width: 35px; height: 35px;">
                                <span class="fw-bold fs-5" style="line-height: 0;">+</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

<div class="fixed-bottom bg-white border-top shadow" style="z-index: 1030;">
    <div class="p-2 border-bottom">
        <select name="promo_id" id="promo_id" class="form-select form-select-sm border-0 shadow-sm" style="background-color: #f8f9fa;" onchange="updateCartUI()">
            <option value="">-- Pilih Promo (Opsional) --</option>
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
    <div class="p-3 d-flex justify-content-between align-items-center">
    <div>
        <small class="text-muted d-block">Total Pesanan</small>
        <h5 class="fw-bold text-success mb-0" id="cart-total">Rp 0</h5>
        <small id="cart-qty" class="text-danger fw-bold">0 Item</small>
    </div>
        <button onclick="submitCustomerOrder()" class="btn btn-success px-4 fw-bold rounded-pill shadow-sm">
            Pesan Sekarang <i class="bi bi-arrow-right"></i>
        </button>
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
            cart.push({ id_menu: id, nama: name, harga: price, jumlah: 1 });
        }
        updateCartUI();
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

        cart.forEach(item => {
            total += (item.harga * item.jumlah);
            qty += item.jumlah;
            
            let qtyDisplay = document.getElementById('qty-' + item.id_menu);
            if(qtyDisplay) qtyDisplay.innerText = item.jumlah;
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

        // Untuk takeaway, jangan kirim id_meja
        let formData = {
            _token: "{{ csrf_token() }}",
            tipe_pesanan: 'takeaway',
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
