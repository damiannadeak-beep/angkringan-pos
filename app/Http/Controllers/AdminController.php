<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\{Pembayaran, Menu, User, Rating, DetailPesanan, Bahan, Pengeluaran, Setting};
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

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

        // HPP Harian
        $dailyHppQuery = DB::table('pesanan')
            ->join('pembayaran', 'pesanan.id', '=', 'pembayaran.id_pesanan')
            ->whereMonth('pembayaran.tanggal', $hariIni->month)
            ->whereYear('pembayaran.tanggal', $hariIni->year)
            ->where('pembayaran.status', 'paid')
            ->selectRaw('DATE(pembayaran.tanggal) AS day, SUM(pesanan.total_hpp) AS total')
            ->groupBy('day')
            ->get()->keyBy('day');

        // Pengeluaran Harian
        $dailyPengeluaranQuery = Pengeluaran::selectRaw('DATE(tanggal) AS day, SUM(nominal) AS total')
            ->whereMonth('tanggal', $hariIni->month)
            ->whereYear('tanggal', $hariIni->year)
            ->groupBy('day')
            ->get()->keyBy('day');

        $dailySalesByDate = $dailySalesQuery->keyBy('day');
        $daysInMonth = $hariIni->daysInMonth;
        $chartDailyLabels = [];
        $chartDailyData = [];
        $chartDailyLaba = [];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dateString = $hariIni->copy()->day($day)->toDateString();
            $chartDailyLabels[] = $hariIni->copy()->day($day)->format('d');
            
            $rev = (float) ($dailySalesByDate[$dateString]->total ?? 0);
            $hpp = (float) ($dailyHppQuery[$dateString]->total ?? 0);
            $pengeluaran = (float) ($dailyPengeluaranQuery[$dateString]->total ?? 0);
            
            $chartDailyData[] = $rev;
            $chartDailyLaba[] = $rev - $hpp - $pengeluaran;
        }

        // Grafik Penjualan Bulanan untuk tahun berjalan
        $monthlySalesQuery = Pembayaran::selectRaw('MONTH(tanggal) AS month, SUM(total_bayar) AS total')
            ->whereYear('tanggal', $hariIni->year)
            ->where('status', 'paid')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // HPP Bulanan
        $monthlyHppQuery = DB::table('pesanan')
            ->join('pembayaran', 'pesanan.id', '=', 'pembayaran.id_pesanan')
            ->whereYear('pembayaran.tanggal', $hariIni->year)
            ->where('pembayaran.status', 'paid')
            ->selectRaw('MONTH(pembayaran.tanggal) AS month, SUM(pesanan.total_hpp) AS total')
            ->groupBy('month')
            ->get()->keyBy('month');

        // Pengeluaran Bulanan
        $monthlyPengeluaranQuery = Pengeluaran::selectRaw('MONTH(tanggal) AS month, SUM(nominal) AS total')
            ->whereYear('tanggal', $hariIni->year)
            ->groupBy('month')
            ->get()->keyBy('month');

        $monthlySalesByMonth = $monthlySalesQuery->keyBy('month');
        $chartMonthlyLabels = [];
        $chartMonthlyData = [];
        $chartMonthlyLaba = [];

        for ($month = 1; $month <= 12; $month++) {
            $chartMonthlyLabels[] = Carbon::create($hariIni->year, $month, 1)->translatedFormat('M');
            
            $rev = (float) ($monthlySalesByMonth[$month]->total ?? 0);
            $hpp = (float) ($monthlyHppQuery[$month]->total ?? 0);
            $pengeluaran = (float) ($monthlyPengeluaranQuery[$month]->total ?? 0);
            
            $chartMonthlyData[] = $rev;
            $chartMonthlyLaba[] = $rev - $hpp - $pengeluaran;
        }

        // Top 5 Menu Terlaris Bulan Ini
        $topMenus = DetailPesanan::join('pesanan', 'detail_pesanan.id_pesanan', '=', 'pesanan.id')
            ->join('menu', 'detail_pesanan.id_menu', '=', 'menu.id')
            ->join('pembayaran', 'pesanan.id', '=', 'pembayaran.id_pesanan')
            ->whereMonth('pembayaran.tanggal', $hariIni->month)
            ->whereYear('pembayaran.tanggal', $hariIni->year)
            ->where('pembayaran.status', 'paid')
            ->selectRaw('menu.nama_menu, menu.image, SUM(detail_pesanan.jumlah) as total_terjual')
            ->groupBy('menu.id', 'menu.nama_menu', 'menu.image')
            ->orderByDesc('total_terjual')
            ->limit(5)
            ->get();

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
            'topMenus',
            'chartDailyLabels',
            'chartDailyData',
            'chartDailyLaba',
            'chartMonthlyLabels',
            'chartMonthlyData',
            'chartMonthlyLaba'
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

    public function exportCsv(Request $request)
    {
        $start = $request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $end = $request->query('end_date', Carbon::now()->endOfMonth()->toDateString());

        $reportsData = $this->getReportsData($start, $end);
        $bestSeller = $reportsData['bestSeller'];
        $kasirPerformance = $reportsData['kasirPerformance'];
        $stockUsage = $reportsData['stockUsage'];

        $filename = 'laporan_lengkap_' . now()->format('Ymd_His') . '.xls';

        $html = '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
        $html .= '<head><meta charset="utf-8"><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Laporan</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head>';
        $html .= '<body>';
        $html .= '<table border="1" cellpadding="5" cellspacing="0" style="font-family: Arial, sans-serif;">';
        
        // Header
        $html .= '<tr><td colspan="4" style="background-color: #4CAF50; color: white; font-size: 16px; font-weight: bold; text-align: center;">LAPORAN LENGKAP ANGKRINGAN POS</td></tr>';
        $html .= '<tr><td colspan="4" style="text-align: center; font-weight: bold;">Periode: ' . $start . ' s/d ' . $end . '</td></tr>';
        $html .= '<tr><td colspan="4"></td></tr>';

        // Best Seller
        $html .= '<tr><td colspan="4" style="background-color: #f2f2f2; font-weight: bold;">--- MENU TERLARIS ---</td></tr>';
        $html .= '<tr style="font-weight: bold; background-color: #e0e0e0;"><td>No</td><td colspan="2">Nama Menu</td><td>Total Terjual (Porsi)</td></tr>';
        $no = 1;
        foreach($bestSeller as $item){
            $html .= '<tr><td>'.$no++.'</td><td colspan="2">'.$item->nama_menu.'</td><td>'.$item->total_terjual.'</td></tr>';
        }
        $html .= '<tr><td colspan="4"></td></tr>';

        // Kinerja Kasir
        $html .= '<tr><td colspan="4" style="background-color: #f2f2f2; font-weight: bold;">--- KINERJA KASIR ---</td></tr>';
        $html .= '<tr style="font-weight: bold; background-color: #e0e0e0;"><td>Nama Kasir</td><td>Shift</td><td>Total Transaksi</td><td>Total Pendapatan (Rp)</td></tr>';
        foreach($kasirPerformance as $kasir){
            $html .= '<tr><td>'.$kasir->name.'</td><td>'.$kasir->shift.'</td><td>'.$kasir->total_transaksi.'</td><td>Rp '.number_format($kasir->total_pendapatan, 0, ',', '.').'</td></tr>';
        }
        $html .= '<tr><td colspan="4"></td></tr>';

        // Penggunaan Stok
        $html .= '<tr><td colspan="4" style="background-color: #f2f2f2; font-weight: bold;">--- PENGGUNAAN STOK BAHAN BAKU ---</td></tr>';
        $html .= '<tr style="font-weight: bold; background-color: #e0e0e0;"><td>No</td><td colspan="2">Nama Bahan</td><td>Total Terpakai</td></tr>';
        $no = 1;
        foreach($stockUsage as $stok){
            $html .= '<tr><td>'.$no++.'</td><td colspan="2">'.$stok->nama_bahan.'</td><td>'.$stok->total_penggunaan . ' ' . $stok->satuan.'</td></tr>';
        }

        $html .= '</table></body></html>';

        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function backupDatabase()
    {
        $filename = 'backup_angkringan_' . date('Y_m_d_His') . '.sql';
        $filepath = storage_path('app/' . $filename);

        $dbHost = env('DB_HOST', '127.0.0.1');
        $dbPort = env('DB_PORT', '3306');
        $dbUser = env('DB_USERNAME', 'root');
        $dbPass = env('DB_PASSWORD', '');
        $dbName = env('DB_DATABASE', 'angkringan_pos');

        $mysqldumpPath = 'C:\\xampp\\mysql\\bin\\mysqldump.exe';
        if (!file_exists($mysqldumpPath)) {
            $mysqldumpPath = 'mysqldump';
        }

        $passwordParam = empty($dbPass) ? '' : "-p" . escapeshellarg($dbPass);
        
        $command = escapeshellarg($mysqldumpPath) . " -h " . escapeshellarg($dbHost) . " -P " . escapeshellarg($dbPort) . " -u " . escapeshellarg($dbUser) . " {$passwordParam} " . escapeshellarg($dbName) . " > " . escapeshellarg($filepath);
        
        $output = [];
        $returnVar = NULL;
        exec($command, $output, $returnVar);

        if ($returnVar === 0 && file_exists($filepath)) {
            return response()->download($filepath)->deleteFileAfterSend(true);
        }

        return back()->withErrors(['msg' => 'Gagal membuat backup database. Pastikan mysqldump tersedia di ' . $mysqldumpPath]);
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

        $kasirShifts = \App\Models\KasirShift::with('user')
            ->whereBetween('waktu_buka', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderBy('waktu_buka', 'desc')
            ->get();

        return compact('startDate', 'endDate', 'chartLabels', 'chartData', 'bestSeller', 'kasirPerformance', 'stockUsage', 'paymentMethods', 'totalPendapatan', 'totalHpp', 'labaKotor', 'totalPengeluaran', 'labaBersih', 'kasirShifts');
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
            'gemini_api_key' => 'nullable|string',
        ]);

        if ($request->hasFile('qris_image')) {
            $oldImage = Setting::getVal('qris_image');
            if ($oldImage && Storage::disk('public')->exists($oldImage)) {
                Storage::disk('public')->delete($oldImage);
            }
            $path = $request->file('qris_image')->store('qris', 'public');
            Setting::updateOrCreate(['key' => 'qris_image'], ['value' => $path]);
        }

        Setting::updateOrCreate(['key' => 'gemini_api_key'], ['value' => $request->gemini_api_key]);

        return redirect()->route('admin.settings')->with('success', 'Pengaturan pembayaran berhasil diperbarui!');
    }

    public function updatePrinterSettings(Request $request)
    {
        $request->validate([
            'printer_ip' => 'nullable|string|max:100',
            'printer_port' => 'nullable|string|max:10',
            'printer_active' => 'nullable|boolean',
        ]);

        Setting::updateOrCreate(['key' => 'printer_ip'], ['value' => $request->printer_ip]);
        Setting::updateOrCreate(['key' => 'printer_port'], ['value' => $request->printer_port ?? '9100']);
        Setting::updateOrCreate(['key' => 'printer_active'], ['value' => $request->has('printer_active') ? '1' : '0']);

        return redirect()->route('admin.settings')->with('success', 'Pengaturan Printer Thermal berhasil diperbarui!');
    }

    public function updateAbsensiSettings(Request $request)
    {
        $request->validate([
            'warung_latitude' => 'nullable|string',
            'warung_longitude' => 'nullable|string',
            'absensi_radius_meter' => 'nullable|numeric',
            'shift_pagi_start' => 'nullable|string',
            'shift_pagi_end' => 'nullable|string',
            'shift_malam_start' => 'nullable|string',
            'shift_malam_end' => 'nullable|string',
            'toleransi_terlambat' => 'nullable|numeric',
        ]);

        Setting::updateOrCreate(['key' => 'warung_latitude'], ['value' => $request->warung_latitude]);
        Setting::updateOrCreate(['key' => 'warung_longitude'], ['value' => $request->warung_longitude]);
        Setting::updateOrCreate(['key' => 'absensi_radius_meter'], ['value' => $request->absensi_radius_meter ?? '5']);
        Setting::updateOrCreate(['key' => 'shift_pagi_start'], ['value' => $request->shift_pagi_start ?? '08:00']);
        Setting::updateOrCreate(['key' => 'shift_pagi_end'], ['value' => $request->shift_pagi_end ?? '17:00']);
        Setting::updateOrCreate(['key' => 'shift_malam_start'], ['value' => $request->shift_malam_start ?? '16:00']);
        Setting::updateOrCreate(['key' => 'shift_malam_end'], ['value' => $request->shift_malam_end ?? '00:00']);
        Setting::updateOrCreate(['key' => 'toleransi_terlambat'], ['value' => $request->toleransi_terlambat ?? '15']);

        return redirect()->route('admin.settings')->with('success', 'Pengaturan Absensi & Shift berhasil diperbarui!');
    }

    public function updateLokasiSettings(Request $request)
    {
        $request->validate([
            'lokasi_judul' => 'nullable|string|max:255',
            'lokasi_deskripsi' => 'nullable|string',
            'lokasi_utama_nama' => 'nullable|string|max:255',
            'lokasi_utama_alamat' => 'nullable|string',
            'lokasi_jam_operasional' => 'nullable|string',
            'lokasi_panduan' => 'nullable|string',
            'lokasi_gmaps_url' => 'nullable|url',
        ]);

        Setting::updateOrCreate(['key' => 'lokasi_judul'], ['value' => $request->lokasi_judul]);
        Setting::updateOrCreate(['key' => 'lokasi_deskripsi'], ['value' => $request->lokasi_deskripsi]);
        Setting::updateOrCreate(['key' => 'lokasi_utama_nama'], ['value' => $request->lokasi_utama_nama]);
        Setting::updateOrCreate(['key' => 'lokasi_utama_alamat'], ['value' => $request->lokasi_utama_alamat]);
        Setting::updateOrCreate(['key' => 'lokasi_jam_operasional'], ['value' => $request->lokasi_jam_operasional]);
        Setting::updateOrCreate(['key' => 'lokasi_panduan'], ['value' => $request->lokasi_panduan]);
        Setting::updateOrCreate(['key' => 'lokasi_gmaps_url'], ['value' => $request->lokasi_gmaps_url]);

        return redirect()->route('admin.settings')->with('success', 'Pengaturan halaman lokasi berhasil diperbarui!');
    }

    public function updateKontakSettings(Request $request)
    {
        $request->validate([
            'kontak_wa' => 'nullable|string|max:50',
            'kontak_email' => 'nullable|email|max:255',
            'sosmed.platform.*' => 'nullable|string',
            'sosmed.label.*' => 'nullable|string',
            'sosmed.url.*' => 'nullable|string',
            'sosmed.icon.*' => 'nullable|string',
        ]);

        Setting::updateOrCreate(['key' => 'kontak_wa'], ['value' => $request->kontak_wa]);
        Setting::updateOrCreate(['key' => 'kontak_email'], ['value' => $request->kontak_email]);

        $sosmedList = [];
        if ($request->has('sosmed.platform')) {
            $platforms = $request->input('sosmed.platform');
            $labels = $request->input('sosmed.label');
            $urls = $request->input('sosmed.url');
            $icons = $request->input('sosmed.icon');

            foreach ($platforms as $index => $platform) {
                if (!empty($platform) && !empty($labels[$index]) && !empty($urls[$index])) {
                    $sosmedList[] = [
                        'platform' => $platform,
                        'label' => $labels[$index],
                        'url' => $urls[$index],
                        'icon' => $icons[$index] ?? 'bi-link-45deg',
                    ];
                }
            }
        }
        
        Setting::updateOrCreate(['key' => 'kontak_sosmed_dynamic'], ['value' => json_encode($sosmedList)]);

        return redirect()->route('admin.settings')->with('success', 'Pengaturan kontak berhasil diperbarui!');
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

    public function absensiReport(Request $request)
    {
        $startDate = $request->query('start_date', \Carbon\Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', \Carbon\Carbon::now()->endOfMonth()->toDateString());

        $absensis = \App\Models\Absensi::with('user')
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->orderBy('tanggal', 'desc')
            ->orderBy('shift', 'asc')
            ->get();

        $rekapAbsensi = [];
        foreach ($absensis as $absen) {
            $userId = $absen->user_id;
            if (!isset($rekapAbsensi[$userId])) {
                $rekapAbsensi[$userId] = [
                    'nama' => $absen->user->name ?? 'User Dihapus',
                    'total_hadir' => 0,
                    'total_menit' => 0,
                ];
            }
            if (strtolower($absen->status) == 'hadir') {
                $rekapAbsensi[$userId]['total_hadir']++;
                
                if ($absen->jam_masuk && $absen->jam_keluar) {
                    $masuk = \Carbon\Carbon::parse($absen->jam_masuk);
                    $keluar = \Carbon\Carbon::parse($absen->jam_keluar);
                    
                    if ($keluar->lessThan($masuk)) {
                        $keluar->addDay();
                    }
                    
                    $rekapAbsensi[$userId]['total_menit'] += $masuk->diffInMinutes($keluar);
                }
            }
        }

        // Format the total minutes into hours and minutes
        foreach ($rekapAbsensi as $userId => $rekap) {
            $hours = floor($rekap['total_menit'] / 60);
            $minutes = $rekap['total_menit'] % 60;
            $rekapAbsensi[$userId]['format_jam'] = $hours . ' Jam ' . $minutes . ' Menit';
        }

        return view('admin.absensi.index', compact('absensis', 'startDate', 'endDate', 'rekapAbsensi'));
    }

    public function activityLogs()
    {
        $logs = \App\Models\ActivityLog::with('user')->orderBy('created_at', 'desc')->paginate(20);
        return view('admin.activity_logs', compact('logs'));
    }

    public function aiSalesAnalysis()
    {
        $apiKey = Setting::getVal('gemini_api_key');
        if (!$apiKey) {
            return response()->json(['error' => 'API Key Gemini belum dikonfigurasi.'], 400);
        }

        // Ambil data penjualan 7 hari terakhir
        $startDate = Carbon::now()->subDays(6)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        $data = $this->getReportsData($startDate->toDateString(), $endDate->toDateString());

        // Siapkan summary untuk AI
        $totalPendapatanStr = "Rp " . number_format($data['totalPendapatan'], 0, ',', '.');
        $labaBersihStr = "Rp " . number_format($data['labaBersih'], 0, ',', '.');
        
        $topMenus = collect($data['bestSeller'])->map(function($m) {
            return "- {$m->nama_menu} ({$m->total_terjual} porsi)";
        })->implode("\n");

        $prompt = "Berikut adalah data ringkasan penjualan angkringan saya selama 7 hari terakhir:\n";
        $prompt .= "- Total Pendapatan Kotor: {$totalPendapatanStr}\n";
        $prompt .= "- Laba Bersih: {$labaBersihStr}\n";
        $prompt .= "- Menu Paling Laris:\n{$topMenus}\n\n";
        $prompt .= "Sebagai asisten restoran AI (namamu: Gemini), tolong buatkan paragraf singkat (maksimal 3 paragraf) dalam bahasa Indonesia santai (bahasa bos dan asisten) yang berisi: 1. Kesimpulan apakah minggu ini bagus. 2. Sorotan produk apa yang paling laris. 3. Saran bisnis praktis untuk besok/minggu depan (misalnya stok atau promo). Jangan gunakan format markdown (seperti bintang tebal dll), cukup teks paragraf biasa.";

        try {
            $response = Http::post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-lite:generateContent?key=" . $apiKey, [
                'contents' => [
                    ['parts' => [['text' => $prompt]]]
                ]
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                $text = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? '';
                // Mengganti newline ganda menjadi <br><br> agar rapi di HTML
                $htmlText = nl2br(trim($text));
                return response()->json(['analysis' => $htmlText]);
            }

            return response()->json(['error' => 'Gagal mendapatkan analisis dari AI.'], 500);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan sistem.'], 500);
        }
    }
}