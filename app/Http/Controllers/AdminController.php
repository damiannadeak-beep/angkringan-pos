<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\{DashboardService, ReportService, BackupService, SettingsService};
use App\Models\{User, Rating, Setting, Absensi, ActivityLog};
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function index(DashboardService $dashboardService)
    {
        $metrics = $dashboardService->getDashboardMetrics();
        $users = User::with('roles')->orderBy('created_at', 'desc')->get();
        $activeKasirs = User::whereHas('roles', fn($q) => $q->where('name', 'kasir'))->orderBy('name')->get();
        $latestReviews = Rating::with('konsumen')->orderBy('tanggal', 'desc')->take(5)->get();

        return view('admin.dashboard', array_merge($metrics, compact('users', 'activeKasirs', 'latestReviews')));
    }

    public function reports(Request $request, ReportService $reportService)
    {
        $start = $request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $end = $request->query('end_date', Carbon::now()->endOfMonth()->toDateString());

        return view('admin.reports', $reportService->getReportsData($start, $end));
    }

    public function exportPdf(Request $request, ReportService $reportService)
    {
        $start = $request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $end = $request->query('end_date', Carbon::now()->endOfMonth()->toDateString());

        return $reportService->generatePdfReport($start, $end);
    }

    public function downloadRevenueReport(Request $request, ReportService $reportService)
    {
        $start = $request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $end = $request->query('end_date', Carbon::now()->endOfMonth()->toDateString());

        return $reportService->generateRevenueCsv($start, $end);
    }

    public function exportCsv(Request $request, ReportService $reportService)
    {
        $start = $request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $end = $request->query('end_date', Carbon::now()->endOfMonth()->toDateString());

        return $reportService->generateFullExcelReport($start, $end);
    }

    public function backupDatabase(BackupService $backupService)
    {
        return $backupService->runMysqldump();
    }

    public function settings(SettingsService $settingsService)
    {
        $settings = $settingsService->getAllSettings();
        return view('admin.settings', compact('settings'));
    }

    public function updateStoreProfile(Request $request, SettingsService $settingsService)
    {
        $data = $request->validate([
            'store_name' => 'required|string|max:255',
            'store_address' => 'required|string',
            'store_phone' => 'required|string|max:20',
            'receipt_footer' => 'nullable|string',
        ]);

        $settingsService->updateSettings($data);

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

    public function updatePaymentSettings(Request $request, SettingsService $settingsService)
    {
        $request->validate([
            'qris_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'gemini_api_key' => 'nullable|string',
        ]);

        if ($request->hasFile('qris_image')) {
            $oldImage = Setting::getVal('qris_image');
            if ($oldImage) {
                Storage::disk('public')->delete($oldImage);
                @unlink(public_path('storage/' . $oldImage));
            }
            $file = $request->file('qris_image');
            $extension = strtolower($file->extension() ?: 'jpg');
            $filename = time() . '_' . uniqid() . '.' . $extension;
            $path = 'qris/' . $filename;
            $content = file_get_contents($file->getRealPath());

            Storage::disk('public')->put($path, $content);

            $publicStorageFile = public_path('storage/' . $path);
            @mkdir(dirname($publicStorageFile), 0755, true);
            @file_put_contents($publicStorageFile, $content);

            $homeDir = env('HOME') ?: getenv('HOME');
            if ($homeDir && file_exists($homeDir . '/public_html')) {
                $cpanelStorageFile = $homeDir . '/public_html/storage/' . $path;
                @mkdir(dirname($cpanelStorageFile), 0755, true);
                @file_put_contents($cpanelStorageFile, $content);
            }

            Setting::updateOrCreate(['key' => 'qris_image'], ['value' => $path]);
        }

        $settingsService->updateSettings(['gemini_api_key' => $request->gemini_api_key]);

        return redirect()->route('admin.settings')->with('success', 'Pengaturan pembayaran berhasil diperbarui!');
    }

    public function updatePrinterSettings(Request $request, SettingsService $settingsService)
    {
        $request->validate([
            'printer_ip' => 'nullable|string|max:100',
            'printer_port' => 'nullable|string|max:10',
            'printer_active' => 'nullable|boolean',
        ]);

        $settingsService->updateSettings([
            'printer_ip' => $request->printer_ip,
            'printer_port' => $request->printer_port ?? '9100',
            'printer_active' => $request->has('printer_active') ? '1' : '0',
        ]);

        return redirect()->route('admin.settings')->with('success', 'Pengaturan Printer Thermal berhasil diperbarui!');
    }

    public function updateAbsensiSettings(Request $request, SettingsService $settingsService)
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

        $settingsService->updateSettings([
            'warung_latitude' => $request->warung_latitude,
            'warung_longitude' => $request->warung_longitude,
            'absensi_radius_meter' => $request->absensi_radius_meter ?? '5',
            'shift_pagi_start' => $request->shift_pagi_start ?? '08:00',
            'shift_pagi_end' => $request->shift_pagi_end ?? '17:00',
            'shift_malam_start' => $request->shift_malam_start ?? '16:00',
            'shift_malam_end' => $request->shift_malam_end ?? '00:00',
            'toleransi_terlambat' => $request->toleransi_terlambat ?? '15',
        ]);

        return redirect()->route('admin.settings')->with('success', 'Pengaturan Absensi & Shift berhasil diperbarui!');
    }

    public function updateLokasiSettings(Request $request, SettingsService $settingsService)
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

        $settingsService->updateSettings([
            'lokasi_judul' => $request->lokasi_judul,
            'lokasi_deskripsi' => $request->lokasi_deskripsi,
            'lokasi_utama_nama' => $request->lokasi_utama_nama,
            'lokasi_utama_alamat' => $request->lokasi_utama_alamat,
            'lokasi_jam_operasional' => $request->lokasi_jam_operasional,
            'lokasi_panduan' => $request->lokasi_panduan,
            'lokasi_gmaps_url' => $request->lokasi_gmaps_url,
        ]);

        return redirect()->route('admin.settings')->with('success', 'Pengaturan halaman lokasi berhasil diperbarui!');
    }

    public function updateKontakSettings(Request $request, SettingsService $settingsService)
    {
        $request->validate([
            'kontak_wa' => 'nullable|string|max:50',
            'kontak_email' => 'nullable|email|max:255',
            'sosmed.platform.*' => 'nullable|string',
            'sosmed.label.*' => 'nullable|string',
            'sosmed.url.*' => 'nullable|string',
            'sosmed.icon.*' => 'nullable|string',
        ]);

        $settingsService->updateSettings([
            'kontak_wa' => $request->kontak_wa,
            'kontak_email' => $request->kontak_email,
        ]);

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

        $settingsService->updateSettings(['kontak_sosmed_dynamic' => json_encode($sosmedList)]);

        return redirect()->route('admin.settings')->with('success', 'Pengaturan kontak berhasil diperbarui!');
    }

    public function reviews()
    {
        $reviews = Rating::with('konsumen', 'pesanan')->orderBy('tanggal', 'desc')->paginate(15);
        return view('admin.reviews', compact('reviews'));
    }

    public function replyReview(Request $request, $id)
    {
        $request->validate(['balasan_admin' => 'required|string|max:1000']);

        $rating = Rating::findOrFail($id);
        $rating->balasan_admin = $request->balasan_admin;
        $rating->save();

        return redirect()->route('admin.reviews.index')->with('success', 'Balasan tersimpan.');
    }

    public function absensiReport(Request $request)
    {
        $startDate = $request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', Carbon::now()->endOfMonth()->toDateString());

        $absensis = Absensi::with('user')
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
                    $masuk = Carbon::parse($absen->jam_masuk);
                    $keluar = Carbon::parse($absen->jam_keluar);
                    if ($keluar->lessThan($masuk)) {
                        $keluar->addDay();
                    }
                    $rekapAbsensi[$userId]['total_menit'] += $masuk->diffInMinutes($keluar);
                }
            }
        }

        foreach ($rekapAbsensi as $userId => $rekap) {
            $hours = floor($rekap['total_menit'] / 60);
            $minutes = $rekap['total_menit'] % 60;
            $rekapAbsensi[$userId]['format_jam'] = $hours . ' Jam ' . $minutes . ' Menit';
        }

        return view('admin.absensi.index', compact('absensis', 'startDate', 'endDate', 'rekapAbsensi'));
    }

    public function activityLogs()
    {
        $logs = ActivityLog::with('user')->orderBy('created_at', 'desc')->paginate(20);
        return view('admin.activity_logs', compact('logs'));
    }

    public function aiSalesAnalysis(ReportService $reportService)
    {
        $apiKey = Setting::getVal('gemini_api_key');
        if (!$apiKey) {
            return response()->json(['error' => 'API Key Gemini belum dikonfigurasi.'], 400);
        }

        $startDate = Carbon::now()->subDays(6)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        $data = $reportService->getReportsData($startDate->toDateString(), $endDate->toDateString());

        $totalPendapatanStr = "Rp " . number_format($data['totalPendapatan'], 0, ',', '.');
        $labaBersihStr = "Rp " . number_format($data['labaBersih'], 0, ',', '.');

        $topMenus = collect($data['bestSeller'])->map(fn($m) => "- {$m->nama_menu} ({$m->total_terjual} porsi)")->implode("\n");

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
                return response()->json(['analysis' => nl2br(trim($text))]);
            }

            return response()->json(['error' => 'Gagal mendapatkan analisis dari AI.'], 500);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan sistem.'], 500);
        }
    }
}