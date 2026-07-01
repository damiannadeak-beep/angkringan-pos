<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\SocialAuthController;

// Import semua controller yang dibutuhkan
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminMenuController;
use App\Http\Controllers\AdminKasirController;
use App\Http\Controllers\AdminPromoController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\KonsumenController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AdminBahanController;
use App\Http\Controllers\AdminPengeluaranController;
use App\Http\Controllers\AdminMejaController;
use App\Http\Controllers\KasirPengeluaranController;
use App\Http\Controllers\PushSubscriptionController;
use App\Http\Controllers\KasirMejaController;

// ================= AREA PUBLIK =================
// Halaman yang bisa diakses tanpa perlu login
Route::get('/', [PublicController::class, 'home']);
Route::get('/katalog', [PublicController::class, 'katalog']);
Route::get('/lokasi', [PublicController::class, 'lokasi']);
Route::get('/kontak', [PublicController::class, 'kontak']);

// Route Bantuan (Clear Cache tanpa Terminal)
Route::get('/clear-cache', function() {
    \Illuminate\Support\Facades\Artisan::call('optimize:clear');
    return 'Cache Laravel berhasil dibersihkan! Silakan kembali dan coba lagi.';
});

// Route Socialite (Google Login)
Route::get('/auth/google', [SocialAuthController::class, 'redirectToGoogle'])->name('google.login');
Route::get('/auth/google/callback', [SocialAuthController::class, 'handleGoogleCallback']);

// Route Autentikasi bawaan Laravel UI (Login, Register, Logout, Verify)
Auth::routes(['verify' => true, 'middleware' => ['throttle:10,1']]);

// Override logout: Kasir 1 (pemilik laci) tidak bisa logout biasa, harus Tutup Shift dulu
Route::post('/logout', function (\Illuminate\Http\Request $request) {
    $user = auth()->user();
    if ($user && $user->hasRole('kasir')) {
        $hasOpenShift = \App\Models\KasirShift::where('user_id', $user->id)->where('status', 'open')->exists();
        if ($hasOpenShift) {
            return redirect()->route('kasir.shift.tutup')->with('error', 'Anda adalah penanggung jawab laci kas. Silakan Tutup Shift dan hitung uang terlebih dahulu sebelum logout.');
        }
    }
    auth()->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/login');
})->name('logout')->middleware('auth');

// ================= AREA AUTENTIKASI =================
// Semua route di dalam grup ini wajib login
Route::middleware(['auth'])->group(function () {
    
    // Halaman Redirect Default setelah login (jika user mengakses /home secara manual)
    Route::get('/home', function () {
        // Jika yang login adalah konsumen, arahkan ke beranda
        if (auth()->user()->hasRole('konsumen')) {
            return redirect('/');
        }
        // Jika bukan konsumen, kembalikan ke root
        return redirect('/');
    });

    // Endpoint Web Push Subscription
    Route::post('/push-subscriptions', [PushSubscriptionController::class, 'update']);

    // Role: Pemilik (Admin)
    Route::middleware(['role:pemilik'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');
        Route::get('/api/ai/sales-analysis', [AdminController::class, 'aiSalesAnalysis'])->name('ai_sales_analysis');
        Route::get('/laporan', [AdminController::class, 'reports'])->name('reports.index');
        Route::get('/reports/revenue', [AdminController::class, 'downloadRevenueReport'])->name('reports.revenue');
        Route::get('/reports/pdf', [AdminController::class, 'exportPdf'])->name('reports.pdf');
        Route::get('/reports/csv', [AdminController::class, 'exportCsv'])->name('reports.csv');
        Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
        Route::post('/settings/profile', [AdminController::class, 'updateStoreProfile'])->name('settings.profile');
        Route::post('/settings/security', [AdminController::class, 'updateSecurity'])->name('settings.security');
        Route::post('/settings/payment', [AdminController::class, 'updatePaymentSettings'])->name('settings.payment');
        Route::post('/settings/printer', [AdminController::class, 'updatePrinterSettings'])->name('settings.printer');
        Route::post('/settings/absensi', [AdminController::class, 'updateAbsensiSettings'])->name('settings.absensi');
        Route::post('/settings/lokasi', [AdminController::class, 'updateLokasiSettings'])->name('settings.lokasi');
        Route::post('/settings/kontak', [AdminController::class, 'updateKontakSettings'])->name('settings.kontak');
        Route::get('/reviews', [AdminController::class, 'reviews'])->name('reviews.index');
        Route::post('/reviews/{id}/reply', [AdminController::class, 'replyReview'])->name('reviews.reply');
        
        Route::get('/backup', [AdminController::class, 'backupDatabase'])->name('backup');
        
        // Laporan Absensi
        Route::get('/absensi', [AdminController::class, 'absensiReport'])->name('absensi.index');
        Route::put('/absensi/{id}', [\App\Http\Controllers\AbsensiController::class, 'updateAdmin'])->name('absensi.update');

        // Log Void Pesanan
        Route::get('/void-logs', [\App\Http\Controllers\AdminVoidLogController::class, 'index'])->name('void_logs.index');
        
        // Log Aktivitas
        Route::get('/activity-logs', [AdminController::class, 'activityLogs'])->name('activity_logs.index');

        // Menu management
        Route::get('/menu', [AdminMenuController::class, 'index'])->name('menu.index');
        
        // Stok Bahan Baku
        Route::get('/stok', [AdminBahanController::class, 'index'])->name('stok.index');
        Route::post('/stok', [AdminBahanController::class, 'store'])->name('stok.store');
        Route::put('/stok/{id}', [AdminBahanController::class, 'update'])->name('stok.update');
        Route::delete('/stok/{id}', [AdminBahanController::class, 'destroy'])->name('stok.destroy');

        // Pengeluaran
        Route::get('/pengeluaran', [AdminPengeluaranController::class, 'index'])->name('pengeluaran.index');
        Route::post('/pengeluaran', [AdminPengeluaranController::class, 'store'])->name('pengeluaran.store');
        Route::delete('/pengeluaran/{id}', [AdminPengeluaranController::class, 'destroy'])->name('pengeluaran.destroy');

        // Meja
        Route::get('/meja', [AdminMejaController::class, 'index'])->name('meja.index');
        Route::post('/meja', [AdminMejaController::class, 'store'])->name('meja.store');
        Route::put('/meja/{id}', [AdminMejaController::class, 'update'])->name('meja.update');
        Route::delete('/meja/{id}', [AdminMejaController::class, 'destroy'])->name('meja.destroy');
        Route::get('/meja/{id}/qr', [AdminMejaController::class, 'printQr'])->name('meja.print_qr');

        Route::get('/menu/create', [AdminMenuController::class, 'create'])->name('menu.create');
        Route::post('/menu', [AdminMenuController::class, 'store'])->name('menu.store');
        Route::get('/menu/{id}/edit', [AdminMenuController::class, 'edit'])->name('menu.edit');
        Route::put('/menu/{id}', [AdminMenuController::class, 'update'])->name('menu.update');
        Route::delete('/menu/{id}', [AdminMenuController::class, 'destroy'])->name('menu.destroy');
        Route::post('/menu/{id}/stock', [AdminMenuController::class, 'updateStock'])->name('menu.stock');
        Route::post('/menu/ai-description', [AdminMenuController::class, 'generateAiDescription'])->name('menu.ai_description');

        // Kasir management
        Route::get('/kasir/manage', [AdminKasirController::class, 'index'])->name('kasir.index');
        Route::get('/kasir/{id}/edit', [AdminKasirController::class, 'edit'])->name('kasir.edit');
        Route::put('/kasir/{id}', [AdminKasirController::class, 'update'])->name('kasir.update');
        Route::delete('/kasir/{id}', [AdminKasirController::class, 'destroy'])->name('kasir.destroy');

        // Promo management
        Route::get('/promo', [AdminPromoController::class, 'index'])->name('promo.index');
        Route::get('/promo/create', [AdminPromoController::class, 'create'])->name('promo.create');
        Route::post('/promo', [AdminPromoController::class, 'store'])->name('promo.store');
        Route::get('/promo/{id}/edit', [AdminPromoController::class, 'edit'])->name('promo.edit');
        Route::put('/promo/{id}', [AdminPromoController::class, 'update'])->name('promo.update');
        Route::delete('/promo/{id}', [AdminPromoController::class, 'destroy'])->name('promo.destroy');
        Route::post('/kasir', [AdminController::class, 'storeKasir'])->name('kasir.store');
        // User management
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        // Permintaan Belanja (Admin)
        Route::get('/permintaan-belanja', [\App\Http\Controllers\PermintaanBelanjaController::class, 'adminIndex'])->name('permintaan.index');
        Route::post('/permintaan-belanja', [\App\Http\Controllers\PermintaanBelanjaController::class, 'adminStore'])->name('permintaan.store');
        Route::put('/permintaan-belanja/{id}', [\App\Http\Controllers\PermintaanBelanjaController::class, 'adminUpdateStatus'])->name('permintaan.update');

    });

    // Role: Kasir
    Route::middleware(['role:kasir', 'ensure_shift_open'])->prefix('kasir')->group(function () {
        Route::get('/pos', [PosController::class, 'index'])->name('kasir.pos');
        Route::get('/pesanan-aktif', [PosController::class, 'pesananAktif'])->name('kasir.pesanan_aktif');
        Route::post('/manual-order', [PosController::class, 'storeManualOrder']);
        Route::put('/order/{id_pesanan}/status', [PosController::class, 'updateOrderStatus']);
        Route::put('/order/{id_pesanan}/pay', [PosController::class, 'payOrder']);
        Route::put('/order/{id_pesanan}/void', [PosController::class, 'voidOrder'])->name('kasir.order.void');
        Route::post('/order/{id_pesanan}/split', [PosController::class, 'splitOrder'])->name('kasir.order.split');
        Route::get('/order/{id}/receipt', [PosController::class, 'printReceipt'])->name('kasir.order.receipt');
        Route::post('/order/{id}/print-thermal', [PosController::class, 'printThermalReceipt'])->name('kasir.order.thermal');
        Route::get('/order/{id}/kitchen-receipt', [PosController::class, 'printKitchenReceipt'])->name('kasir.order.kitchen');
        Route::get('/shift-report', [PosController::class, 'shiftReport'])->name('kasir.shift_report');
        Route::get('/shift-report/pdf', [PosController::class, 'exportShiftReportPdf'])->name('kasir.shift_report.pdf');
        Route::get('/api/active-orders-count', [PosController::class, 'activeOrdersCount'])->name('kasir.active_orders_count');
        Route::get('/api/notifications', [PosController::class, 'getNotifications']);
        Route::post('/api/notifications/{id}/read', [PosController::class, 'readNotification']);

        // Pengeluaran Kasir
        Route::get('/pengeluaran', [KasirPengeluaranController::class, 'index'])->name('kasir.pengeluaran.index');
        Route::post('/pengeluaran', [KasirPengeluaranController::class, 'store'])->name('kasir.pengeluaran.store');

        // Manajemen Meja Kasir
        Route::get('/meja', [KasirMejaController::class, 'index'])->name('kasir.meja.index');
        Route::put('/meja/{id}/toggle', [KasirMejaController::class, 'toggle'])->name('kasir.meja.toggle');

        // Stok Opname Kasir
        Route::get('/stok', [\App\Http\Controllers\KasirStokController::class, 'index'])->name('kasir.stok.index');
        Route::post('/stok', [\App\Http\Controllers\KasirStokController::class, 'update'])->name('kasir.stok.update');

        // Absensi Geolocation
        Route::get('/absensi', [\App\Http\Controllers\AbsensiController::class, 'index'])->name('kasir.absensi.index');
        Route::post('/absensi', [\App\Http\Controllers\AbsensiController::class, 'store'])->name('kasir.absensi.store');
        // Permintaan Belanja (Kasir)
        Route::get('/permintaan-belanja', [\App\Http\Controllers\PermintaanBelanjaController::class, 'kasirIndex'])->name('kasir.permintaan.index');
        Route::post('/permintaan-belanja', [\App\Http\Controllers\PermintaanBelanjaController::class, 'kasirStore'])->name('kasir.permintaan.store');

        // Shift Kasir
        Route::get('/shift/buka', [\App\Http\Controllers\ShiftController::class, 'bukaShift'])->name('kasir.shift.buka');
        Route::post('/shift/buka', [\App\Http\Controllers\ShiftController::class, 'storeBukaShift'])->name('kasir.shift.storeBuka');
        Route::get('/shift/tutup', [\App\Http\Controllers\ShiftController::class, 'tutupShift'])->name('kasir.shift.tutup');
        Route::post('/shift/tutup', [\App\Http\Controllers\ShiftController::class, 'storeTutupShift'])->name('kasir.shift.storeTutup');
    });

    // Role: Konsumen
    Route::middleware(['role:konsumen', 'verified'])->prefix('konsumen')->group(function () {
        // Fitur Pemesanan via QR / Konsumen login
        Route::get('/pilih-tipe', [OrderController::class, 'pilihTipePesanan'])->name('pilih_tipe');
        Route::get('/menu', [OrderController::class, 'pilihMeja'])->name('pilih_meja');
        Route::get('/menu-takeaway', [OrderController::class, 'menuTakeaway'])->name('menu_takeaway');
        Route::get('/menu-nanti', [OrderController::class, 'menuNanti'])->name('menu_nanti');
        Route::get('/menu/{id_meja}', [OrderController::class, 'showMenu'])->name('konsumen.menu.meja')->middleware('signed');
        Route::post('/order/add', [OrderController::class, 'tambahPesanan'])->middleware('throttle:30,1');
        Route::post('/order/{id}/cancel', [OrderController::class, 'cancelOrder']);
        Route::get('/checkout/{id_pesanan}', [PaymentController::class, 'checkout']);
        Route::post('/call-bell', [OrderController::class, 'callBell'])->middleware('throttle:5,1');
        
        // Fitur Baru: Profil, Riwayat & Rating
        Route::get('/profil', [KonsumenController::class, 'index']);
        Route::post('/profil/update', [KonsumenController::class, 'updateProfil']);
        Route::post('/rating/store', [KonsumenController::class, 'storeRating']);
    });
});

Route::get('/debug-menu', function() {
    return App\Models\Menu::all();
});