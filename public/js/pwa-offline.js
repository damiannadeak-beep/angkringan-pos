/**
 * Angkringan POS - PWA Offline-First & Background Sync Engine
 */

// 1. Register Service Worker
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js')
            .then(reg => console.log('PWA ServiceWorker registered with scope:', reg.scope))
            .catch(err => console.warn('PWA ServiceWorker registration failed:', err));
    });
}

// 2. Network Status Indicator Banner
document.addEventListener('DOMContentLoaded', () => {
    const bannerHtml = `
        <div id="pwa-network-banner" class="position-fixed top-0 start-50 translate-middle-x mt-2 z-3 d-none" style="z-index: 9999;">
            <div id="pwa-network-alert" class="alert d-flex align-items-center shadow-lg px-4 py-2 rounded-pill text-white fw-bold small">
                <i id="pwa-network-icon" class="bi bi-wifi-off me-2 fs-5"></i>
                <span id="pwa-network-text">Modus Offline PWA Aktif</span>
            </div>
        </div>
    `;
    document.body.insertAdjacentHTML('afterbegin', bannerHtml);

    function updateNetworkStatus() {
        const banner = document.getElementById('pwa-network-banner');
        const alert = document.getElementById('pwa-network-alert');
        const icon = document.getElementById('pwa-network-icon');
        const text = document.getElementById('pwa-network-text');

        if (!banner || !alert) return;

        if (navigator.onLine) {
            alert.className = 'alert bg-success d-flex align-items-center shadow-lg px-4 py-2 rounded-pill text-white fw-bold small';
            icon.className = 'bi bi-wifi me-2 fs-5';
            text.textContent = 'Koneksi Terhubung Kembali. Sinkronisasi Data...';
            banner.classList.remove('d-none');
            
            // Auto sync offline orders
            syncOfflineOrders();

            setTimeout(() => {
                banner.classList.add('d-none');
            }, 3000);
        } else {
            alert.className = 'alert bg-danger d-flex align-items-center shadow-lg px-4 py-2 rounded-pill text-white fw-bold small';
            icon.className = 'bi bi-wifi-off me-2 fs-5';
            text.textContent = 'Koneksi Terputus! Modus Offline PWA Aktif (Pesanan Tersimpan Lokal)';
            banner.classList.remove('d-none');
        }
    }

    window.addEventListener('online', updateNetworkStatus);
    window.addEventListener('offline', updateNetworkStatus);

    if (!navigator.onLine) {
        updateNetworkStatus();
    }
});

// 3. IndexedDB Storage Engine for Offline Queue
const DB_NAME = 'AngkringanPosOfflineDB';
const DB_VERSION = 1;
const STORE_NAME = 'offline_orders';

function openIndexedDB() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open(DB_NAME, DB_VERSION);
        request.onupgradeneeded = (e) => {
            const db = e.target.result;
            if (!db.objectStoreNames.contains(STORE_NAME)) {
                db.createObjectStore(STORE_NAME, { keyPath: 'id', autoIncrement: true });
            }
        };
        request.onsuccess = (e) => resolve(e.target.result);
        request.onerror = (e) => reject(e.target.error);
    });
}

/**
 * Simpan pesanan ke antrean IndexedDB saat offline.
 */
async function saveOfflineOrder(orderPayload) {
    const db = await openIndexedDB();
    return new Promise((resolve, reject) => {
        const tx = db.transaction(STORE_NAME, 'readwrite');
        const store = tx.objectStore(STORE_NAME);
        const request = store.add({
            payload: orderPayload,
            timestamp: new Date().toISOString()
        });
        request.onsuccess = () => resolve(request.result);
        request.onerror = (e) => reject(e.target.error);
    });
}

/**
 * Sinkronisasi seluruh pesanan offline ke server ketika online.
 */
async function syncOfflineOrders() {
    try {
        const db = await openIndexedDB();
        const tx = db.transaction(STORE_NAME, 'readonly');
        const store = tx.objectStore(STORE_NAME);
        const getAllReq = store.getAll();

        getAllReq.onsuccess = async () => {
            const orders = getAllReq.result;
            if (orders.length === 0) return;

            console.log(`Mengirim ${orders.length} pesanan offline ke server...`);
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

            for (const item of orders) {
                try {
                    const res = await fetch('/kasir/pos/manual-order', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(item.payload)
                    });

                    if (res.ok) {
                        // Hapus dari antrean lokal jika sukses
                        const delTx = db.transaction(STORE_NAME, 'readwrite');
                        delTx.objectStore(STORE_NAME).delete(item.id);
                    }
                } catch (err) {
                    console.warn('Gagal sinkronisasi item:', item, err);
                }
            }
        };
    } catch (e) {
        console.warn('Sync IndexedDB error:', e);
    }
}
