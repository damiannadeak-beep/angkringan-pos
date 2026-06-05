<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

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

// ================= AREA PUBLIK =================
// Halaman yang bisa diakses tanpa perlu login
Route::get('/', [PublicController::class, 'home']);
Route::get('/katalog', [PublicController::class, 'katalog']);
Route::get('/lokasi', [PublicController::class, 'lokasi']);
Route::get('/kontak', [PublicController::class, 'kontak']);

// Route Autentikasi bawaan Laravel UI (Login, Register, Logout)
Auth::routes();

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

    // Role: Pemilik (Admin)
    Route::middleware(['role:pemilik'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');
        Route::get('/laporan', [AdminController::class, 'reports'])->name('reports.index');
        Route::get('/reports/revenue', [AdminController::class, 'downloadRevenueReport'])->name('reports.revenue');
        Route::get('/reports/pdf', [AdminController::class, 'exportPdf'])->name('reports.pdf');
        Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
        Route::post('/settings/profile', [AdminController::class, 'updateStoreProfile'])->name('settings.profile');
        Route::post('/settings/security', [AdminController::class, 'updateSecurity'])->name('settings.security');
        Route::post('/settings/payment', [AdminController::class, 'updatePaymentSettings'])->name('settings.payment');
        Route::get('/reviews', [AdminController::class, 'reviews'])->name('reviews.index');
        Route::post('/reviews/{id}/reply', [AdminController::class, 'replyReview'])->name('reviews.reply');

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
        Route::get('/void-logs', [\App\Http\Controllers\AdminVoidLogController::class, 'index'])->name('admin.void_logs.index');
        Route::get('/menu/{id}/edit', [AdminMenuController::class, 'edit'])->name('menu.edit');
        Route::put('/menu/{id}', [AdminMenuController::class, 'update'])->name('menu.update');
        Route::delete('/menu/{id}', [AdminMenuController::class, 'destroy'])->name('menu.destroy');
        Route::post('/menu/{id}/stock', [AdminMenuController::class, 'updateStock'])->name('menu.stock');

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
    });

    // Role: Kasir
    Route::middleware(['role:kasir'])->prefix('kasir')->group(function () {
        Route::get('/pos', [PosController::class, 'index'])->name('kasir.pos');
        Route::get('/pesanan-aktif', [PosController::class, 'pesananAktif'])->name('kasir.pesanan_aktif');
        Route::post('/manual-order', [PosController::class, 'storeManualOrder']);
        Route::put('/order/{id_pesanan}/status', [PosController::class, 'updateOrderStatus']);
        Route::put('/order/{id_pesanan}/pay', [PosController::class, 'payOrder']);
        Route::put('/order/{id_pesanan}/void', [PosController::class, 'voidOrder'])->name('kasir.order.void');
        Route::post('/order/{id_pesanan}/split', [PosController::class, 'splitOrder'])->name('kasir.order.split');
        Route::get('/order/{id}/receipt', [PosController::class, 'printReceipt'])->name('kasir.order.receipt');
        Route::get('/order/{id}/kitchen-receipt', [PosController::class, 'printKitchenReceipt'])->name('kasir.order.kitchen');
        Route::get('/shift-report', [PosController::class, 'shiftReport'])->name('kasir.shift_report');
    });

    // Role: Konsumen
    Route::middleware(['role:konsumen'])->prefix('konsumen')->group(function () {
        // Fitur Pemesanan via QR / Konsumen login
        Route::get('/pilih-tipe', [OrderController::class, 'pilihTipePesanan'])->name('pilih_tipe');
        Route::get('/menu', [OrderController::class, 'pilihMeja'])->name('pilih_meja');
        Route::get('/menu-takeaway', [OrderController::class, 'menuTakeaway'])->name('menu_takeaway');
        Route::get('/menu/{id_meja}', [OrderController::class, 'showMenu']);
        Route::post('/order/add', [OrderController::class, 'tambahPesanan']);
        Route::post('/order/{id}/cancel', [OrderController::class, 'cancelOrder']);
        Route::get('/checkout/{id_pesanan}', [PaymentController::class, 'checkout']);
        
        // Fitur Baru: Profil, Riwayat & Rating
        Route::get('/profil', [KonsumenController::class, 'index']);
        Route::post('/profil/update', [KonsumenController::class, 'updateProfil']);
        Route::post('/rating/store', [KonsumenController::class, 'storeRating']);
    });
});