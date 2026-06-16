<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AbsensiController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $hariIni = \Carbon\Carbon::today();

        $absensi = \App\Models\Absensi::where('user_id', $user->id)
            ->whereDate('tanggal', $hariIni)
            ->first();

        $settings = \App\Models\Setting::pluck('value', 'key')->toArray();

        return view('kasir.absensi.index', compact('absensi', 'settings'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $user = auth()->user();
        $hariIni = \Carbon\Carbon::today();
        $sekarang = \Carbon\Carbon::now()->format('H:i:s');

        // Mengambil Setting
        $settings = \App\Models\Setting::pluck('value', 'key')->toArray();
        $warungLat = $settings['warung_latitude'] ?? null;
        $warungLng = $settings['warung_longitude'] ?? null;
        $radiusMax = $settings['absensi_radius_meter'] ?? 5;

        if (!$warungLat || !$warungLng) {
            return back()->with('error', 'Koordinat warung belum diatur oleh Admin. Anda tidak bisa melakukan absensi.');
        }

        // Kalkulasi Jarak (Haversine Formula)
        $earthRadius = 6371000; // Radius bumi dalam meter
        $latFrom = deg2rad($warungLat);
        $lonFrom = deg2rad($warungLng);
        $latTo = deg2rad($request->latitude);
        $lonTo = deg2rad($request->longitude);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos($latFrom) * cos($latTo) *
             sin($lonDelta / 2) * sin($lonDelta / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        $jarakMeter = $earthRadius * $c;

        if ($jarakMeter > $radiusMax) {
            return back()->with('error', 'Jarak Anda (' . round($jarakMeter, 2) . 'm) melebihi batas radius ' . $radiusMax . 'm dari warung. Silakan mendekat ke kasir.');
        }

        $absensi = \App\Models\Absensi::where('user_id', $user->id)
            ->whereDate('tanggal', $hariIni)
            ->first();

        if (!$absensi) {
            // Kalkulasi Keterlambatan
            $shift = $user->shift ?? 'pagi';
            $jamMulaiShift = $shift === 'malam' 
                ? ($settings['shift_malam_start'] ?? '16:00') 
                : ($settings['shift_pagi_start'] ?? '08:00');
            $toleransiMenit = $settings['toleransi_terlambat'] ?? 15;
            
            $batasMasuk = \Carbon\Carbon::createFromFormat('H:i', $jamMulaiShift)->addMinutes((int)$toleransiMenit)->format('H:i:s');
            
            $statusKehadiran = $sekarang > $batasMasuk ? 'terlambat' : 'hadir';

            // Absen Masuk
            \App\Models\Absensi::create([
                'user_id' => $user->id,
                'shift' => $shift,
                'tanggal' => $hariIni,
                'jam_masuk' => $sekarang,
                'status' => $statusKehadiran,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'jarak_meter' => round($jarakMeter, 2),
            ]);
            
            $pesanSukses = $statusKehadiran === 'terlambat' 
                ? 'Absen Masuk berhasil direkam (Status: Terlambat).' 
                : 'Absen Masuk berhasil direkam!';
                
            return back()->with('success', $pesanSukses);
        } else {
            // Jika sudah absen masuk tapi belum keluar
            if (!$absensi->jam_keluar) {
                $absensi->update([
                    'jam_keluar' => $sekarang,
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                    'jarak_meter' => round($jarakMeter, 2),
                ]);
                return back()->with('success', 'Absen Keluar berhasil direkam! Selamat beristirahat.');
            } else {
                return back()->with('error', 'Anda sudah melakukan Absen Keluar hari ini.');
            }
        }
    }

    public function updateAdmin(Request $request, $id)
    {
        $request->validate([
            'jam_masuk' => 'required',
            'jam_keluar' => 'nullable',
            'status' => 'required|in:hadir,terlambat,izin,sakit,alpa',
        ]);

        $absensi = \App\Models\Absensi::findOrFail($id);
        
        $absensi->update([
            'jam_masuk' => $request->jam_masuk,
            'jam_keluar' => $request->jam_keluar,
            'status' => $request->status,
        ]);

        return back()->with('success', 'Data absensi berhasil diperbarui oleh Admin.');
    }
}
