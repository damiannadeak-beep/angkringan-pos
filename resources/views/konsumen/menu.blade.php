@extends('layouts.app')

@section('content')
<div class="container pb-5 mb-5">
    <div class="alert alert-primary d-flex justify-content-between align-items-center shadow-sm">
        <div>
            <h5 class="mb-0 fw-bold">Meja: {{ $meja->nama_meja_atau_nomor }}</h5>
            @if($pesananAktif)
                <small class="text-danger fw-bold">Ada Tagihan Belum Dibayar (Open Bill)</small>
            @else
                <small>Silakan pilih menu Anda</small>
            @endif
        </div>
        <i class="bi bi-shop fs-1 text-primary opacity-50"></i>
    </div>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mt-4 mb-3 gap-2">
        <h5 class="fw-bold mb-0">Menu Tersedia</h5>
        <div class="btn-group shadow-sm" role="group">
            <button type="button" class="btn btn-outline-primary active btn-filter" onclick="filterMenu('semua', this)">Semua</button>
            <button type="button" class="btn btn-outline-primary btn-filter" onclick="filterMenu('makanan', this)">Makanan</button>
            <button type="button" class="btn btn-outline-primary btn-filter" onclick="filterMenu('minuman', this)">Minuman</button>
        </div>
    </div>
    <div class="row g-4">
        @foreach($menus as $menu)
        <div class="col-6 col-md-4 col-lg-3 menu-item" data-kategori="{{ $menu->kategori }}">
            <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden bg-white" style="transition: transform 0.2s;">
                @if($menu->image)
                <div style="aspect-ratio: 1/1; max-height: 140px; width: 100%; overflow: hidden; background-color: #f8f9fa; display: flex; align-items: center; justify-content: center;">
                    <img src="{{ asset('storage/'.$menu->image) }}" alt="{{ $menu->nama_menu }}" style="width: 100%; height: 100%; object-fit: contain;">
                </div>
                @endif
                <div class="card-body d-flex flex-column p-3">
                    <h6 class="fw-bold text-dark mb-1">{{ $menu->nama_menu }}</h6>
                    <small class="text-muted mb-2" style="cursor: pointer; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;" onclick="this.style.webkitLineClamp = this.style.webkitLineClamp === '2' ? 'unset' : '2'" title="Klik untuk membaca selengkapnya">{{ $menu->deskripsi ?? 'Tanpa deskripsi' }}</small>
                    <h6 class="text-primary fw-bold mb-3 mt-auto">Rp {{ number_format($menu->harga, 0, ',', '.') }}</h6>
                    
                    <div class="d-flex justify-content-between align-items-center mt-auto">
                        <div class="d-flex align-items-center gap-3">
                            <button class="btn btn-outline-danger rounded-circle p-0 d-flex justify-content-center align-items-center shadow-sm" 
                                    onclick="removeFromCart({{ $menu->id }})"
                                    style="width: 32px; height: 32px; transition: all 0.2s;">
                                <i class="bi bi-dash fs-5"></i>
                            </button>
                            <span id="qty-{{ $menu->id }}" class="fw-bold fs-5 mb-0" style="min-width: 15px; text-align: center;">0</span>
                            <button class="btn btn-primary rounded-circle p-0 d-flex justify-content-center align-items-center shadow-sm" 
                                    onclick="openVariantModal({{ $menu->id }})"
                                    style="width: 32px; height: 32px; transition: all 0.2s;">
                                <i class="bi bi-plus fs-5"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div id="catatan-container-{{ $menu->id }}" class="mt-3 text-primary small" style="display: none;">
                        <!-- variants shown here by JS -->
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    
    <!-- Spacer untuk memberikan ruang kosong agar item terakhir tidak tertutup menu bawah -->
    <div style="height: 140px;"></div>
</div>

<!-- Modal Pilih Varian -->
<div class="modal fade" id="variantModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold mb-0" id="variantModalTitle">Pilih Varian</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4 pb-4">
                <div id="variantModalContent"></div>
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <h5 class="fw-bold mb-0 text-primary" id="variantModalPrice">Rp 0</h5>
                    <button type="button" class="btn btn-primary fw-bold rounded-pill px-4" onclick="confirmVariantSelection()">Tambahkan</button>
                </div>
            </div>
        </div>
    </div>
</div>



<div class="fixed-bottom bg-white shadow-lg" style="z-index: 1030; border-radius: 24px 24px 0 0; border-top: 1px solid #eaeaea;">
    <div class="container px-3 py-3">
        <div class="mb-3">
            <select name="promo_id" id="promo_id" class="form-select form-select-sm border-primary bg-primary bg-opacity-10 text-primary fw-bold rounded-pill px-3 py-2" onchange="updateCartUI()">
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
                    <span id="cart-qty" class="badge bg-primary rounded-pill px-2">0 Item</span>
                </div>
            </div>
            <button onclick="submitCustomerOrder()" class="btn btn-primary px-4 py-2 fw-bold rounded-pill shadow-sm" style="transition: transform 0.2s;">
                Pesan <i class="bi bi-cart-check-fill ms-1"></i>
            </button>
        </div>
    </div>
</div>

<script>
    const allMenus = @json($menus);
    let cart = [];
    let currentSelectedMenu = null;

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

    function openVariantModal(id) {
        const menu = allMenus.find(m => m.id === id);
        if (!menu) return;

        let variants = [];
        if (menu.variants_json) {
            try { variants = JSON.parse(menu.variants_json); } catch(e) {}
        }

        if (variants.length === 0) {
            addToCart(menu.id, menu.nama_menu, menu.harga, []);
            return;
        }

        currentSelectedMenu = menu;
        document.getElementById('variantModalTitle').innerText = menu.nama_menu;
        
        let html = '';
        variants.forEach((group, gIndex) => {
            html += `<div class="mb-3">
                        <label class="fw-bold d-block mb-2">${group.group_name}</label>`;
            
            group.options.forEach((opt, oIndex) => {
                const inputType = group.type === 'multiple' ? 'checkbox' : 'radio';
                const inputName = `var_group_${gIndex}`;
                const inputId = `var_${gIndex}_${oIndex}`;
                const priceText = opt.price > 0 ? `(+Rp ${opt.price.toLocaleString('id-ID')})` : '';
                
                html += `
                    <div class="form-check mb-1">
                        <input class="form-check-input var-option-input" type="${inputType}" name="${inputName}" id="${inputId}" 
                               data-gname="${group.group_name}" data-oname="${opt.name}" data-price="${opt.price}" onchange="calculateVariantPrice()">
                        <label class="form-check-label d-flex justify-content-between" for="${inputId}">
                            <span>${opt.name}</span>
                            <span class="text-muted small">${priceText}</span>
                        </label>
                    </div>
                `;
            });
            html += `</div>`;
        });

        document.getElementById('variantModalContent').innerHTML = html;
        calculateVariantPrice();
        
        var vModal = new bootstrap.Modal(document.getElementById('variantModal'));
        vModal.show();
    }

    function calculateVariantPrice() {
        if (!currentSelectedMenu) return;
        let total = parseFloat(currentSelectedMenu.harga);
        
        document.querySelectorAll('.var-option-input:checked').forEach(input => {
            total += parseFloat(input.dataset.price);
        });

        document.getElementById('variantModalPrice').innerText = 'Rp ' + total.toLocaleString('id-ID');
        return total;
    }

    function confirmVariantSelection() {
        if (!currentSelectedMenu) return;

        let selectedVariants = [];
        document.querySelectorAll('.var-option-input:checked').forEach(input => {
            selectedVariants.push({
                group: input.dataset.gname,
                name: input.dataset.oname,
                price: parseFloat(input.dataset.price)
            });
        });

        let variantsDef = JSON.parse(currentSelectedMenu.variants_json || '[]');
        for (let i = 0; i < variantsDef.length; i++) {
            if (variantsDef[i].type === 'single') {
                const hasSelected = selectedVariants.find(sv => sv.group === variantsDef[i].group_name);
                if (!hasSelected) {
                    alert(`Silakan pilih salah satu opsi dari ${variantsDef[i].group_name}!`);
                    return;
                }
            }
        }

        const finalPrice = calculateVariantPrice();
        addToCart(currentSelectedMenu.id, currentSelectedMenu.nama_menu, finalPrice, selectedVariants);
        
        bootstrap.Modal.getInstance(document.getElementById('variantModal')).hide();
    }

    function addToCart(id, name, price, variants = []) {
        const variantsString = JSON.stringify(variants);
        let itemIndex = cart.findIndex(i => i.id_menu === id && JSON.stringify(i.variants) === variantsString);
        if (itemIndex !== -1) {
            cart[itemIndex].jumlah++;
        } else {
            cart.push({ id_menu: id, nama: name, harga: price, jumlah: 1, catatan: '', variants: variants });
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

        // Since cart can have multiple identical menu_ids with different variants, 
        // we aggregate qty per menu_id for the UI display on the menu list.
        let aggregatedQty = {};
        let aggregatedVariantsHtml = {};

        cart.forEach(item => {
            total += (item.harga * item.jumlah);
            qty += item.jumlah;
            
            if(!aggregatedQty[item.id_menu]) {
                aggregatedQty[item.id_menu] = 0;
                aggregatedVariantsHtml[item.id_menu] = '';
            }
            aggregatedQty[item.id_menu] += item.jumlah;

            if (item.variants && item.variants.length > 0) {
                const varText = item.variants.map(v => v.name).join(', ');
                aggregatedVariantsHtml[item.id_menu] += `<div class="mb-1"><i class="bi bi-tags me-1"></i>${item.jumlah}x: ${varText}</div>`;
            }
        });

        // Update UI
        Object.keys(aggregatedQty).forEach(menuId => {
            let qtyDisplay = document.getElementById('qty-' + menuId);
            if(qtyDisplay) qtyDisplay.innerText = aggregatedQty[menuId];

            let catatanContainer = document.getElementById('catatan-container-' + menuId);
            if(catatanContainer && aggregatedVariantsHtml[menuId] !== '') {
                catatanContainer.style.display = 'block';
                catatanContainer.innerHTML = aggregatedVariantsHtml[menuId];
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

        // Gunakan konfirmasi agar tidak terjadi pesanan tidak sengaja (Fat Finger)
        if (!confirm('Apakah pesanan Anda sudah benar?')) return;

        proceedToCheckout();
    }

    function proceedToCheckout() {
        let formData = {
            _token: "{{ csrf_token() }}",
            id_meja: "{{ $meja->id }}",
            promo_id: document.getElementById('promo_id').value,
            items: cart
        };

        // Memanggil fungsi tambahPesanan di OrderController
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
                // Langsung arahkan konsumen ke halaman Checkout/Pembayaran Midtrans
                window.location.href = "/konsumen/checkout/" + data.id_pesanan;
            }
        })
        .catch(err => console.error(err));
    }
</script>
@endsection