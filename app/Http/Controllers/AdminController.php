<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\{Pembayaran, Menu, User, Rating, DetailPesanan, Bahan, Pengeluaran, Setting};
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    public function index()
    {
        $hariIni = Carbon::today();

        // 1. Total Penjualan Hari Ini (Hanya yang lunas / 'paid')
        $totalPenjualanHariIni = Pembayaran::whereDate('tanggal', $hariIni)
            ->where('status', 'paid')
            ->sum('total_bayar');

        // 2. Total Pendapatan berdasarkan Metode (Cash vs QRIS)
        // Menghindari 2 query terpisah, kita group langsung menggunakan query builder
        $pendapatanPerMetode = Pembayaran::selectRaw('metode, sum(total_bayar) as total')
            ->whereDate('tanggal', $hariIni)
            ->where('status', 'paid')
            ->groupBy('metode')
            ->pluck('total', 'metode'); // Hasil: ['cash' => 50000, 'qris' => 150000]

        $totalCash = $pendapatanPerMetode->get('cash', 0);
        $totalQris = $pendapatanPerMetode->get('qris', 0);

        // 3. Peringatan Stok (Menu dengan stok di bawah 10)
        $stokMenipis = Menu::where('stok', '<', 10)
            ->where('is_available', true)
            ->orderBy('stok', 'asc')
            ->get();

        $startBulan = Carbon::now()->startOfMonth();
        $endBulan = Carbon::now()->endOfMonth();
        $totalPenjualanBulan = Pembayaran::whereBetween('tanggal', [$startBulan, $endBulan])
            ->where('status', 'paid')
            ->sum('total_bayar');

        // Total HPP Bulanan (dari pesanan yang lunas)
        $totalHppBulan = DB::table('pesanan')
            ->join('pembayaran', 'pesanan.id', '=', 'pembayaran.id_pesanan')
            ->whereBetween('pembayaran.tanggal', [$startBulan, $endBulan])
            ->where('pembayaran.status', 'paid')
            ->sum('pesanan.total_hpp');

        // Pengeluaran Bulanan
        $totalPengeluaranBulan = Pengeluaran::whereBetween('tanggal', [$startBulan, $endBulan])
            ->sum('nominal');

        // Laba Kotor
        $labaKotorBulan = $totalPenjualanBulan - $totalHppBulan;

        // Laba Bersih Murni
        $labaBersihBulan = $labaKotorBulan - $totalPengeluaranBulan;

        // Grafik Penjualan Harian untuk bulan berjalan
        $dailySalesQuery = Pembayaran::selectRaw('DATE(tanggal) AS day, SUM(total_bayar) AS total')
            ->whereMonth('tanggal', $hariIni->month)
            ->whereYear('tanggal', $hariIni->year)
            ->where('status', 'paid')
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $dailySalesByDate = $dailySalesQuery->keyBy('day');
        $daysInMonth = $hariIni->daysInMonth;
        $chartDailyLabels = [];
        $chartDailyData = [];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dateString = $hariIni->copy()->day($day)->toDateString();
            $chartDailyLabels[] = $hariIni->copy()->day($day)->format('d');
            $chartDailyData[] = (float) ($dailySalesByDate[$dateString]->total ?? 0);
        }

        // Grafik Penjualan Bulanan untuk tahun berjalan
        $monthlySalesQuery = Pembayaran::selectRaw('MONTH(tanggal) AS month, SUM(total_bayar) AS total')
            ->whereYear('tanggal', $hariIni->year)
            ->where('status', 'paid')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $monthlySalesByMonth = $monthlySalesQuery->keyBy('month');
        $chartMonthlyLabels = [];
        $chartMonthlyData = [];

        for ($month = 1; $month <= 12; $month++) {
            $chartMonthlyLabels[] = Carbon::create($hariIni->year, $month, 1)->translatedFormat('M');
            $chartMonthlyData[] = (float) ($monthlySalesByMonth[$month]->total ?? 0);
        }

        // Active kasir list
        $activeKasirs = User::whereHas('roles', function($q){ $q->where('name','kasir'); })->orderBy('name')->get();

        // Latest customer reviews
        $latestReviews = Rating::with('konsumen')->orderBy('tanggal', 'desc')->take(5)->get();

        $users = User::with('roles')->orderBy('created_at', 'desc')->get();

        return view('admin.dashboard', compact(
            'totalPenjualanHariIni',
            'totalPenjualanBulan',
            'totalHppBulan',
            'labaKotorBulan',
            'totalPengeluaranBulan',
            'labaBersihBulan',
            'totalCash',
            'totalQris',
            'stokMenipis',
            'users',
            'activeKasirs',
            'latestReviews',
            'chartDailyLabels',
            'chartDailyData',
            'chartMonthlyLabels',
            'chartMonthlyData'
        ));
    }

    public function downloadRevenueReport(Request $request)
    {
        $start = $request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $end = $request->query('end_date', Carbon::now()->endOfMonth()->toDateString());

        $rows = Pembayaran::whereBetween('tanggal', [$start, $end])
            ->where('status', 'paid')
            ->orderBy('tanggal')
            ->get(['tanggal','metode','total_bayar']);

        $filename = 'laporan_pendapatan_' . now()->format('Ymd_His') . '.csv';
        $handle = fopen('php://memory','r+');
        fputcsv($handle, ['Tanggal','Metode','Total Bayar']);
        foreach($rows as $r){
            fputcsv($handle, [
                $r->tanggal,
                $r->metode,
                $r->total_bayar
            ]);
        }
        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return response($content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function storeKasir(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'shift' => 'required|in:pagi,malam',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'shift' => $data['shift'],
            'password' => Hash::make($data['password']),
        ]);

        $user->assignRole('kasir');

        return redirect()->route('admin.kasir.index')->with('success', 'Akun kasir berhasil dibuat.');
    }

    public function getReportsData($startDate, $endDate)
    {
        // Grafik Penjualan Harian untuk rentang waktu
        $salesQuery = Pembayaran::selectRaw('DATE(tanggal) AS day, SUM(total_bayar) AS total')
            ->whereBetween('tanggal', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->where('status', 'paid')
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $salesByDate = $salesQuery->keyBy('day');
        
        $chartLabels = [];
        $chartData = [];

        $currentDate = Carbon::parse($startDate);
        $lastDate = Carbon::parse($endDate);

        while ($currentDate <= $lastDate) {
            $dateString = $currentDate->toDateString();
            $chartLabels[] = $currentDate->format('d M Y');
            $chartData[] = (float) ($salesByDate[$dateString]->total ?? 0);
            $currentDate->addDay();
        }

        // 1. Menu Terlaris (Best Seller)
        $bestSeller = DetailPesanan::join('pesanan', 'detail_pesanan.id_pesanan', '=', 'pesanan.id')
            ->join('menu', 'detail_pesanan.id_menu', '=', 'menu.id')
            ->join('pembayaran', 'pesanan.id', '=', 'pembayaran.id_pesanan')
            ->whereBetween('pembayaran.tanggal', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->where('pembayaran.status', 'paid')
            ->selectRaw('menu.nama_menu, SUM(detail_pesanan.jumlah) as total_terjual')
            ->groupBy('menu.id', 'menu.nama_menu')
            ->orderByDesc('total_terjual')
            ->limit(10)
            ->get();

        // 2. Kinerja Kasir per Shift
        $kasirPerformance = Pembayaran::join('pesanan', 'pembayaran.id_pesanan', '=', 'pesanan.id')
            ->join('users', 'pesanan.id_kasir', '=', 'users.id')
            ->whereBetween('pembayaran.tanggal', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->where('pembayaran.status', 'paid')
            ->selectRaw('users.name, users.shift, SUM(pembayaran.total_bayar) as total_pendapatan, COUNT(pembayaran.id) as total_transaksi')
            ->groupBy('users.id', 'users.name', 'users.shift')
            ->orderByDesc('total_pendapatan')
            ->get();

        // 3. Penggunaan Stok Bahan Baku
        // Sum of (detail_pesanan.jumlah * bahan_menu.jumlah_dibutuhkan)
        $stockUsage = DB::table('detail_pesanan')
            ->join('pesanan', 'detail_pesanan.id_pesanan', '=', 'pesanan.id')
            ->join('pembayaran', 'pesanan.id', '=', 'pembayaran.id_pesanan')
            ->join('bahan_menu', 'detail_pesanan.id_menu', '=', 'bahan_menu.menu_id')
            ->join('bahans', 'bahan_menu.bahan_id', '=', 'bahans.id')
            ->whereBetween('pembayaran.tanggal', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->where('pembayaran.status', 'paid')
            ->selectRaw('bahans.nama_bahan, bahans.satuan, SUM(detail_pesanan.jumlah * bahan_menu.jumlah_dibutuhkan) as total_penggunaan')
            ->groupBy('bahans.id', 'bahans.nama_bahan', 'bahans.satuan')
            ->orderByDesc('total_penggunaan')
            ->get();

        // 4. Metode Pembayaran (Cash vs QRIS)
        $paymentMethods = Pembayaran::selectRaw('metode, count(id) as total_transaksi, sum(total_bayar) as total')
            ->whereBetween('tanggal', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->where('status', 'paid')
            ->groupBy('metode')
            ->get();

        // 5. Total Pendapatan, Total Pengeluaran & Laba Bersih
        $totalPendapatan = Pembayaran::whereBetween('tanggal', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->where('status', 'paid')
            ->sum('total_bayar');
            
        $totalHpp = DB::table('pesanan')
            ->join('pembayaran', 'pesanan.id', '=', 'pembayaran.id_pesanan')
            ->whereBetween('pembayaran.tanggal', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->where('pembayaran.status', 'paid')
            ->sum('pesanan.total_hpp');
            
        $totalPengeluaran = Pengeluaran::whereBetween('tanggal', [$startDate, $endDate])
            ->sum('nominal');
            
        $labaKotor = $totalPendapatan - $totalHpp;
        $labaBersih = $labaKotor - $totalPengeluaran;

        return compact('startDate', 'endDate', 'chartLabels', 'chartData', 'bestSeller', 'kasirPerformance', 'stockUsage', 'paymentMethods', 'totalPendapatan', 'totalHpp', 'labaKotor', 'totalPengeluaran', 'labaBersih');
    }

    public function reports(Request $request)
    {
        $startDate = $request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', Carbon::now()->endOfMonth()->toDateString());

        $data = $this->getReportsData($startDate, $endDate);

        return view('admin.reports', $data);
    }

    public function exportPdf(Request $request)
    {
        $startDate = $request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', Carbon::now()->endOfMonth()->toDateString());

        $data = $this->getReportsData($startDate, $endDate);

        $pdf = Pdf::loadView('admin.reports_pdf', $data);
        $filename = 'Laporan_Angkringan_' . $startDate . '_sd_' . $endDate . '.pdf';
        
        return $pdf->download($filename);
    }

    public function settings(Request $request)
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        return view('admin.settings', compact('settings'));
    }

    public function updateStoreProfile(Request $request)
    {
        $request->validate([
            'store_name' => 'required|string|max:255',
            'store_address' => 'required|string',
            'store_phone' => 'required|string|max:20',
            'receipt_footer' => 'nullable|string',
        ]);

        Setting::updateOrCreate(['key' => 'store_name'], ['value' => $request->store_name]);
        Setting::updateOrCreate(['key' => 'store_address'], ['value' => $request->store_address]);
        Setting::updateOrCreate(['key' => 'store_phone'], ['value' => $request->store_phone]);
        Setting::updateOrCreate(['key' => 'receipt_footer'], ['value' => $request->receipt_footer]);

        return redirect()->route('admin.settings')->with('success', 'Profil warung berhasil diperbarui!');
    }

    public function updateSecurity(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . auth()->id(),
            'current_password' => 'nullable|string',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $user = auth()->user();
        $user->name = $request->name;
        $user->email = $request->email;

        if ($request->filled('password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Password saat ini tidak cocok.']);
            }
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('admin.settings')->with('success', 'Keamanan akun berhasil diperbarui!');
    }

    public function updatePaymentSettings(Request $request)
    {
        $request->validate([
            'qris_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'midtrans_server_key' => 'nullable|string',
            'midtrans_client_key' => 'nullable|string',
            'midtrans_is_production' => 'nullable|boolean',
        ]);

        if ($request->hasFile('qris_image')) {
            $oldImage = Setting::getVal('qris_image');
            if ($oldImage && Storage::disk('public')->exists($oldImage)) {
                Storage::disk('public')->delete($oldImage);
            }
            $path = $request->file('qris_image')->store('qris', 'public');
            Setting::updateOrCreate(['key' => 'qris_image'], ['value' => $path]);
        }

        Setting::updateOrCreate(['key' => 'midtrans_server_key'], ['value' => $request->midtrans_server_key]);
        Setting::updateOrCreate(['key' => 'midtrans_client_key'], ['value' => $request->midtrans_client_key]);
        Setting::updateOrCreate(['key' => 'midtrans_is_production'], ['value' => $request->has('midtrans_is_production') ? '1' : '0']);

        return redirect()->route('admin.settings')->with('success', 'Pengaturan pembayaran berhasil diperbarui!');
    }

    // Halaman Ulasan: tampilkan semua rating dan komentar
    public function reviews(Request $request)
    {
        $reviews = Rating::with('konsumen', 'pesanan')
            ->orderBy('tanggal', 'desc')
            ->paginate(15);

        return view('admin.reviews', compact('reviews'));
    }

    // Balas komentar untuk sebuah rating
    public function replyReview(Request $request, $id)
    {
        $request->validate([
            'balasan_admin' => 'required|string|max:1000',
        ]);

        $rating = Rating::findOrFail($id);
        $rating->balasan_admin = $request->balasan_admin;
        $rating->save();

        return redirect()->route('admin.reviews.index')->with('success', 'Balasan tersimpan.');
    }
}