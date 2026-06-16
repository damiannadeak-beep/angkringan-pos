@extends('layouts.kasir')

@section('content')
<div class="container-fluid px-4">
<div class="row g-4 align-items-start">
        <!-- Kolom Kiri: Daftar Menu -->
        <div class="col-lg-8 col-md-7">
            <div class="kasir-card card bg-white">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-2 px-4">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <h5 class="mb-0 fw-bold text-accent"><i class="bi bi-grid-fill me-2"></i>Daftar Produk</h5>
                        
                        <!-- Search Bar -->
                        <div class="input-group" style="max-width: 250px;">
                            <span class="input-group-text bg-light border-end-0 rounded-start-pill"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" id="searchInput" class="form-control bg-light border-start-0 rounded-end-pill" placeholder="Cari menu..." onkeyup="searchMenu(this.value)">
                        </div>
                    </div>
                    
                    <!-- Kategori Pills -->
                    <div class="d-flex gap-2 mt-4 overflow-auto pb-2" style="white-space: nowrap;">
                        <button type="button" class="btn btn-soft rounded-pill px-4 active" onclick="filterCategory('semua', this)">Semua</button>
                        <button type="button" class="btn btn-soft rounded-pill px-4" onclick="filterCategory('makanan', this)">Makanan</button>
                        <button type="button" class="btn btn-soft rounded-pill px-4" onclick="filterCategory('minuman', this)">Minuman</button>
                    </div>
                </div>
                
                <div class="card-body px-4 pb-4">
                    <div class="row g-4" id="menu-container">
                        <!-- Menu items will be rendered here by JS -->
                    </div>
                    
                    <!-- Pagination Controls -->
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-5 pt-3 border-top gap-3">
                        <button class="btn btn-outline-secondary rounded-pill px-4" id="prevPage" onclick="changePage(-1)" disabled><i class="bi bi-chevron-left me-2"></i> Sebelumnya</button>
                        <span id="pageInfo" class="fw-bold text-muted small">Halaman 1 / 1</span>
                        <button class="btn btn-outline-secondary rounded-pill px-4" id="nextPage" onclick="changePage(1)" disabled>Selanjutnya <i class="bi bi-chevron-right ms-2"></i></button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Detail Pesanan (Keranjang) -->
        <div class="col-lg-4 col-md-5 sticky-top" style="top: 1.5rem; z-index: 1020;">
            <form id="order-form">
                @csrf
                <div class="kasir-card card bg-white">
                    <div class="card-header border-bottom-0 pt-4 pb-0 px-4">
                        <h5 class="mb-0 fw-bold text-accent"><i class="bi bi-cart3 me-2"></i>Detail Pesanan</h5>
                    </div>
                    <div class="card-body p-4">
                        
                        <div class="row g-3 mb-4 p-3 rounded-4 order-config-box">
                            <div class="col-12">
                                <label class="small fw-bold mb-1 text-accent">Nomor Meja</label>
                                <select name="id_meja" class="form-select border-0 shadow-sm" required>
                                    @foreach($mejas as $meja)
                                        <option value="{{ $meja->id }}">{{ $meja->nama_meja_atau_nomor }} {{ !$meja->is_available ? '(Terisi)' : '' }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="small fw-bold mb-1 text-accent">Tipe Pesanan</label>
                                <select name="tipe_pesanan" class="form-select border-0 shadow-sm" onchange="toggleMeja()">
                                    <option value="dine_in">Dine In (Makan di Tempat)</option>
                                    <option value="takeaway">Takeaway (Bawa Pulang)</option>
                                </select>
                            </div>
                            <div class="col-12 mt-2">
                                <label class="small fw-bold mb-1 text-accent">Promo Diskon</label>
                                <select name="promo_id" class="form-select border-0 shadow-sm" onchange="renderCart()">
                                    <option value="">-- Tanpa Promo --</option>
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
                        </div>

                        <!-- Daftar Item Keranjang -->
                        <div id="cart-list" class="cart-list mb-4 overflow-auto pe-2" style="max-height: 300px;">
                            <div class="d-flex flex-column justify-content-center align-items-center h-100 text-muted">
                                <i class="bi bi-basket2 text-opacity-25 text-accent" style="font-size: 3rem;"></i>
                                <p class="mt-2 mb-0">Keranjang masih kosong</p>
                            </div>
                        </div>

                        <hr style="border-color: #f0d0d6;">
                        
                        <!-- Ringkasan Harga -->
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-bold mb-0 text-muted">Total Tagihan</h5>
                            <h3 class="fw-bold mb-0 price text-accent" id="grand-total">Rp 0</h3>
                        </div>

                        <!-- Tombol Aksi -->
                        <div class="d-grid gap-2">
                            <button type="button" onclick="submitOrder(1, 'cash')" class="btn btn-success btn-lg fw-bold rounded-pill shadow-sm text-white">
                                <i class="bi bi-cash me-2"></i> BAYAR LUNAS (CASH)
                            </button>
                            <button type="button" onclick="showQrisModal()" class="btn btn-primary btn-lg fw-bold rounded-pill shadow-sm text-white">
                                <i class="bi bi-qr-code-scan me-2"></i> BAYAR LUNAS (QRIS)
                            </button>
                            <button type="button" onclick="submitOrder(0, 'pending')" class="btn btn-outline-warning btn-lg fw-bold rounded-pill text-dark border-2 shadow-sm">
                                <i class="bi bi-clock-history me-2"></i> SIMPAN PESANAN
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal QRIS -->
<div class="modal fade" id="qrisModal" tabindex="-1" aria-labelledby="qrisModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content text-center rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4 pb-4">
                <h5 class="fw-bold mb-3">Scan QRIS</h5>
                @php $qrisImage = \App\Models\Setting::getVal('qris_image'); @endphp
                @if($qrisImage)
                    <img src="{{ asset('storage/'.$qrisImage) }}" alt="QRIS" class="img-fluid rounded mb-3 border p-2">
                    <p class="small text-muted mb-4">Silakan arahkan pelanggan untuk scan Barcode di atas. Pastikan saldo sudah masuk sebelum menekan tombol Selesai.</p>
                    <button type="button" onclick="confirmQrisPayment()" class="btn btn-primary fw-bold w-100 rounded-pill">Selesai & Cetak Struk</button>
                @else
                    <div class="bg-light p-4 rounded mb-3">
                        <i class="bi bi-qr-code text-muted" style="font-size: 3rem;"></i>
                    </div>
                    <p class="text-danger small fw-bold mb-0">Admin belum mengatur gambar QRIS.</p>
                @endif
            </div>
        </div>
    </div>
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
                    <h5 class="fw-bold mb-0 text-accent" id="variantModalPrice">Rp 0</h5>
                    <button type="button" class="btn btn-primary fw-bold rounded-pill px-4" onclick="confirmVariantSelection()">Tambahkan ke Pesanan</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const allMenus = @json($menus);
    let filteredMenus = [...allMenus];
    let currentPage = 1;
    const itemsPerPage = 10;
    
    // Inisialisasi Cart dari Local Storage agar aman saat ter-refresh
    let cart = JSON.parse(localStorage.getItem('kasir_cart')) || [];

    document.addEventListener('DOMContentLoaded', () => {
        renderMenus();
        renderCart();
        toggleMeja(); // Init state
    });

    // --- LOGIKA TOGGLE MEJA ---
    function toggleMeja() {
        const tipePesanan = document.querySelector('select[name="tipe_pesanan"]').value;
        const mejaSelect = document.querySelector('select[name="id_meja"]');
        if (tipePesanan === 'takeaway') {
            mejaSelect.disabled = true;
        } else {
            mejaSelect.disabled = false;
        }
    }

    // --- LOGIKA FILTER DAN PAGINATION ---
    // --- LOGIKA PENCARIAN & FILTER ---
    function searchMenu(keyword) {
        keyword = keyword.toLowerCase();
        let currentCatBtn = document.querySelector('.btn-soft.active').innerText.toLowerCase();
        let baseMenus = currentCatBtn === 'semua' ? allMenus : allMenus.filter(m => m.kategori === currentCatBtn);
        
        filteredMenus = baseMenus.filter(menu => menu.nama_menu.toLowerCase().includes(keyword));
        currentPage = 1;
        renderMenus();
    }

    function filterCategory(category, btnElement) {
        document.querySelectorAll('.btn-soft').forEach(btn => btn.classList.remove('active', 'bg-white', 'shadow-sm'));
        btnElement.classList.add('active', 'bg-white', 'shadow-sm');
        
        // Bersihkan kotak pencarian saat pindah kategori
        document.getElementById('searchInput').value = '';

        if (category === 'semua') {
            filteredMenus = [...allMenus];
        } else {
            filteredMenus = allMenus.filter(menu => menu.kategori === category);
        }
        currentPage = 1;
        renderMenus();
    }

    function changePage(direction) {
        const maxPage = Math.ceil(filteredMenus.length / itemsPerPage);
        currentPage += direction;
        if (currentPage < 1) currentPage = 1;
        if (currentPage > maxPage) currentPage = maxPage;
        renderMenus();
    }

    function renderMenus() {
        const container = document.getElementById('menu-container');
        container.innerHTML = '';

        const maxPage = Math.ceil(filteredMenus.length / itemsPerPage) || 1;
        
        // Update Pagination Controls
        document.getElementById('pageInfo').innerText = `Halaman ${currentPage} / ${maxPage}`;
        document.getElementById('prevPage').disabled = currentPage === 1;
        document.getElementById('nextPage').disabled = currentPage === maxPage;

        const startIndex = (currentPage - 1) * itemsPerPage;
        const endIndex = startIndex + itemsPerPage;
        const menusToShow = filteredMenus.slice(startIndex, endIndex);

        if (menusToShow.length === 0) {
            container.innerHTML = `
                <div class="col-12 text-center text-muted py-5 mt-4">
                    <i class="bi bi-search" style="font-size: 3rem; opacity: 0.3;"></i>
                    <h5 class="mt-3">Produk tidak ditemukan</h5>
                    <p class="small">Coba gunakan kata kunci lain.</p>
                </div>`;
            return;
        }

        menusToShow.forEach(menu => {
            const imageHtml = menu.image 
                ? `<div class="bg-white text-center border-bottom" style="height: 150px;">
                     <img src="{{ asset('storage') }}/${menu.image}" alt="${menu.nama_menu}" style="object-fit: contain; width: 100%; height: 100%;">
                   </div>`
                : `<div class="card-img-top bg-light d-flex justify-content-center align-items-center" style="height: 150px;">
                       <i class="bi bi-image text-muted opacity-50" style="font-size: 2.5rem;"></i>
                   </div>`;
                   
            const badgeCat = menu.kategori === 'makanan' 
                ? `<span class="badge position-absolute top-0 start-0 m-3 px-3 py-2 rounded-pill shadow-sm bg-warning text-white" style="backdrop-filter: blur(4px);">Makanan</span>`
                : `<span class="badge position-absolute top-0 start-0 m-3 px-3 py-2 rounded-pill shadow-sm bg-info text-white" style="backdrop-filter: blur(4px);">Minuman</span>`;

            const stockAlertClass = ''; // Garis merah dimatikan sesuai permintaan user
            const stockBadge = menu.stok > 0 && menu.stok <= 5 && menu.is_available ? `<span class="badge bg-danger position-absolute top-0 end-0 m-3 px-2 py-1 rounded-pill shadow-sm" style="backdrop-filter: blur(4px);"><i class="bi bi-exclamation-circle me-1"></i>Sisa ${menu.stok}</span>` : '';
            
            const isHabis = menu.stok <= 0;
            const disabledStyle = (!menu.is_available || isHabis) ? 'opacity: 0.6; filter: grayscale(80%); pointer-events: none;' : 'cursor: pointer;';
            const textOverlay = !menu.is_available ? 'TIDAK TERSEDIA' : (isHabis ? 'STOK HABIS' : '');
            const habisOverlay = textOverlay !== '' ? `<div class="position-absolute top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center" style="background: rgba(0,0,0,0.5); z-index: 5;"><h4 class="text-white fw-bold border border-2 border-white p-2 rounded">${textOverlay}</h4></div>` : '';

            const html = `
                <div class="col-xl-3 col-lg-4 col-sm-6">
                    <div class="menu-card card h-100 position-relative overflow-hidden ${(menu.is_available && !isHabis) ? 'hover-lift' : ''} ${stockAlertClass}" 
                         onclick="${(menu.is_available && !isHabis) ? `openVariantModal(${menu.id})` : ''}"
                         style="${disabledStyle}">
                        ${habisOverlay}
                        ${badgeCat}
                        ${stockBadge}
                        ${imageHtml}
                        <div class="card-body text-center p-3">
                            <h6 class="fw-bold mb-1 text-truncate" title="${menu.nama_menu}">${menu.nama_menu}</h6>
                            <p class="price mb-0 fs-5 font-sans">Rp ${parseFloat(menu.harga).toLocaleString('id-ID')}</p>
                            <small class="text-muted d-block mt-1">Sisa Stok: ${menu.stok}</small>
                        </div>
                    </div>
                </div>
            `;
            container.innerHTML += html;
        });
    }

    // --- LOGIKA CART (KERANJANG) ---
    let currentSelectedMenu = null;

    function openVariantModal(id) {
        const menu = allMenus.find(m => m.id === id);
        if (!menu) return;

        let variants = [];
        if (menu.variants_json) {
            try { variants = JSON.parse(menu.variants_json); } catch(e) {}
        }

        if (variants.length === 0) {
            // Langsung tambah ke cart jika tidak ada varian
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

        // Validasi radio (harus pilih satu jika grup bertipe single)
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
        // Cek apakah item dengan menu_id dan varian yang SAMA persis sudah ada
        const variantsString = JSON.stringify(variants);
        let itemIndex = cart.findIndex(i => i.id_menu === id && JSON.stringify(i.variants) === variantsString);
        
        if (itemIndex !== -1) {
            cart[itemIndex].jumlah++;
        } else {
            cart.push({ id_menu: id, nama: name, harga: price, jumlah: 1, catatan: '', variants: variants });
        }
        saveCart();
    }

    function saveCart() {
        localStorage.setItem('kasir_cart', JSON.stringify(cart));
        renderCart();
    }

    function renderCart() {
        let html = '';
        let total = 0;
        cart.forEach((item, index) => {
            let subtotal = item.harga * item.jumlah;
            total += subtotal;
            
            let variantsHtml = '';
            if (item.variants && item.variants.length > 0) {
                const varText = item.variants.map(v => v.name).join(', ');
                variantsHtml = `<div class="small text-primary mb-1"><i class="bi bi-tags me-1"></i>${varText}</div>`;
            }

            html += `
                <div class="cart-item d-flex flex-column bg-white p-2 rounded mb-2 shadow-sm border">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <div style="flex: 1; padding-right: 10px;">
                            <span class="d-block fw-bold text-accent">${item.nama}</span>
                            ${variantsHtml}
                            <small class="text-muted font-sans">Rp ${item.harga.toLocaleString('id-ID')}</small>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div class="input-group input-group-sm" style="width: 90px;">
                                <button class="btn btn-outline-secondary px-2" type="button" onclick="updateQty(${index}, ${item.jumlah - 1})">-</button>
                                <input type="number" class="form-control text-center px-0 border-secondary fw-bold font-sans" value="${item.jumlah}" min="0" readonly>
                                <button class="btn btn-outline-secondary px-2" type="button" onclick="updateQty(${index}, ${item.jumlah + 1})">+</button>
                            </div>
                            <div class="text-end" style="width: 75px;">
                                <span class="small fw-bold price font-sans">Rp ${subtotal.toLocaleString('id-ID')}</span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <input type="text" class="form-control form-control-sm border-0 bg-light rounded-pill px-3" style="font-size: 0.8rem;" placeholder="Catatan: misal pedas, setengah matang..." value="${item.catatan || ''}" onchange="updateCatatan(${index}, this.value)">
                    </div>
                </div>
            `;
        });
        
        if (html === '') {
            html = `
            <div class="d-flex flex-column justify-content-center align-items-center h-100 text-muted py-5">
                <i class="bi bi-basket2 text-opacity-25 text-accent" style="font-size: 3rem;"></i>
                <p class="mt-2 mb-0">Keranjang masih kosong</p>
            </div>`;
        }
        
        let discount = 0;
        const promoSelect = document.querySelector('select[name="promo_id"]');
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
        
        document.getElementById('cart-list').innerHTML = html;
        let tagihanHtml = '<span class="font-sans">Rp ' + totalTagihan.toLocaleString('id-ID') + '</span>';
        if(discount > 0) {
            tagihanHtml = `<span class="text-decoration-line-through text-muted small font-sans">Rp ${total.toLocaleString('id-ID')}</span><br><span class="font-sans">Rp ${totalTagihan.toLocaleString('id-ID')}</span>`;
        }
        document.getElementById('grand-total').innerHTML = tagihanHtml;
    }

    function updateQty(index, val) {
        let qty = parseInt(val);
        if (qty <= 0) {
            cart.splice(index, 1); // Hapus item jika jumlah 0
        } else {
            cart[index].jumlah = qty;
        }
        saveCart();
    }

    function updateCatatan(index, val) {
        cart[index].catatan = val;
        saveCart();
    }

    function showQrisModal() {
        if (cart.length === 0) return alert('Keranjang masih kosong!');
        var qrisModal = new bootstrap.Modal(document.getElementById('qrisModal'));
        qrisModal.show();
    }

    function confirmQrisPayment() {
        var modalEl = document.getElementById('qrisModal');
        var modal = bootstrap.Modal.getInstance(modalEl);
        if(modal) modal.hide();
        
        submitOrder(1, 'qris');
    }

    function submitOrder(isLunas, method) {
        if (cart.length === 0) return alert('Keranjang masih kosong!');

        let formData = {
            _token: "{{ csrf_token() }}",
            id_meja: document.querySelector('select[name="id_meja"]').value,
            tipe_pesanan: document.querySelector('select[name="tipe_pesanan"]').value,
            promo_id: document.querySelector('select[name="promo_id"]').value,
            pembayaran_langsung: isLunas,
            metode_pembayaran: method,
            items: cart
        };

        fetch("{{ url('/kasir/manual-order') }}", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(formData)
        })
        .then(res => res.json())
        .then(data => {
            if (data.error) alert(data.error);
            else {
                if (isLunas) {
                    if (confirm('Pembayaran berhasil! Ingin cetak struk sekarang?')) {
                        @php $printerActive = \App\Models\Setting::getVal('printer_active') == '1'; @endphp
                        @if($printerActive)
                            if (confirm('Kirim langsung ke Mesin Printer Thermal? (Pilih Cancel untuk cetak lewat Browser)')) {
                                fetch(`/kasir/order/${data.id_pesanan}/print-thermal`, {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': "{{ csrf_token() }}" }
                                })
                                .then(res => res.json())
                                .then(resData => {
                                    if(resData.error) alert(resData.error);
                                    else alert(resData.message);
                                });
                            } else {
                                window.open(`/kasir/order/${data.id_pesanan}/receipt`, '_blank');
                            }
                        @else
                            window.open(`/kasir/order/${data.id_pesanan}/receipt`, '_blank');
                        @endif
                    }
                } else {
                    alert('Pesanan berhasil disimpan (Belum dibayar).');
                }
                localStorage.removeItem('kasir_cart'); // Kosongkan cart setelah berhasil
                location.reload();
            }
        });
    }

    function updateOrderStatus(idPesanan, newStatus) {
        if (!confirm(`Ubah status pesanan ke ${newStatus.toUpperCase()}?`)) return;

        fetch(`{{ url('/kasir/order') }}/${idPesanan}/status`, {
            method: "PUT",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ status: newStatus })
        })
        .then(res => res.json())
        .then(data => {
            if (data.error) {
                alert('Error: ' + data.error);
            } else {
                alert(data.message);
                location.reload();
            }
        })
        .catch(err => console.error(err));
    }

    function payOrder(idPesanan) {
        let method = prompt("Masukkan metode pembayaran untuk Pesanan #" + idPesanan + "\nKetik 'cash' atau 'qris':");
        if (!method) return;
        method = method.toLowerCase().trim();
        if (method !== 'cash' && method !== 'qris') {
            alert("Metode tidak valid. Harus 'cash' atau 'qris'.");
            return;
        }

        fetch(`{{ url('/kasir/order') }}/${idPesanan}/pay`, {
            method: "PUT",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ metode: method })
        })
        .then(res => res.json())
        .then(data => {
            if (data.error) {
                alert('Error: ' + data.error);
            } else {
                alert(data.message);
                location.reload();
            }
        })
        .catch(err => console.error(err));
    }
</script>

<style>
    .item-menu:hover { background-color: #F0E9DD; transform: translateY(-3px); border: 1px solid #3E2723 !important; }

    /* Hilangkan panah atas/bawah pada input number agar angka benar-benar di tengah */
    input[type=number]::-webkit-inner-spin-button, 
    input[type=number]::-webkit-outer-spin-button { 
        -webkit-appearance: none; 
        margin: 0; 
    }
    input[type=number] {
        -moz-appearance: textfield; /* Firefox */
    }
</style>
@endsection