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
    <div class="row g-4">
        @foreach($menus as $menu)
        <div class="col-12 col-md-6 col-lg-4 menu-item" data-kategori="{{ $menu->kategori }}">
            <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden bg-white" style="transition: transform 0.2s;">
                @if($menu->image)
                <div style="height: 180px; width: 100%; overflow: hidden; background-color: #f8f9fa; display: flex; align-items: center; justify-content: center;">
                    <img src="{{ asset('storage/'.$menu->image) }}" alt="{{ $menu->nama_menu }}" style="width: 100%; height: 100%; object-fit: contain;">
                </div>
                @endif
                <div class="card-body d-flex flex-column p-3">
                    <h6 class="fw-bold text-dark mb-1">{{ $menu->nama_menu }}</h6>
                    <small class="text-muted mb-2" style="cursor: pointer; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;" onclick="this.style.webkitLineClamp = this.style.webkitLineClamp === '2' ? 'unset' : '2'" title="Klik untuk membaca selengkapnya">{{ $menu->deskripsi ?? 'Tanpa deskripsi' }}</small>
                    
                    <div class="d-flex justify-content-between align-items-center mt-auto mb-3">
                        <h6 class="text-success fw-bold mb-0">Rp {{ number_format($menu->harga, 0, ',', '.') }}</h6>
                        <small class="text-muted">Sisa: {{ $menu->stok }}</small>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-3">
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
                    </div>
                    
                    <div id="catatan-container-{{ $menu->id }}" class="mt-3" style="display: none;">
                        <input type="text" id="catatan-input-{{ $menu->id }}" class="form-control form-control-sm border-1 bg-light rounded-3 px-3" placeholder="Tambah catatan..." onchange="updateCatatan({{ $menu->id }}, this.value)">
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    
    <!-- Spacer untuk memberikan ruang kosong agar item terakhir tidak tertutup menu bawah -->
    <div style="height: 140px;"></div>
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

        proceedToCheckout();
    }

    function proceedToCheckout() {
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
