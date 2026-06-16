<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{User, Pesanan, Rating};

class KonsumenController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // 1. Ambil Pesanan Aktif (Pending / Processing)
        $pesananAktif = Pesanan::with(['detail_pesanan.menu', 'pembayaran'])
            ->where('id_konsumen', $user->id)
            ->whereIn('status', ['pending', 'processing'])
            ->orderBy('created_at', 'desc')
            ->get();

        // 2. Ambil Riwayat Pesanan (Completed / Cancelled) beserta data Ratingnya
        $riwayat = Pesanan::with(['detail_pesanan.menu', 'pembayaran', 'rating'])
            ->where('id_konsumen', $user->id)
            ->whereIn('status', ['completed', 'cancelled'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('konsumen.profil', compact('user', 'pesananAktif', 'riwayat'));
    }

    public function updateProfil(Request $request)
    {
        $user = User::findOrFail(auth()->id());

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'no_hp' => 'nullable|string|max:15',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'no_hp' => $request->no_hp,
        ];

        if ($request->hasFile('foto')) {
            // Hapus foto lama jika ada
            if ($user->foto && file_exists(public_path('uploads/profil/' . $user->foto))) {
                unlink(public_path('uploads/profil/' . $user->foto));
            }
            
            $file = $request->file('foto');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/profil'), $filename);
            $data['foto'] = $filename;
        }

        $user->update($data);

        return back()->with('success', 'Profil berhasil diperbarui!');
    }

    public function storeRating(Request $request)
    {
        $request->validate([
            'id_pesanan' => 'required|exists:pesanan,id',
            'rating' => 'required|integer|min:1|max:5',
            'komentar' => 'nullable|string'
        ]);

        // FIX #10: Validasi ownership — pastikan pesanan ini milik konsumen yang login
        $pesanan = \App\Models\Pesanan::findOrFail($request->id_pesanan);
        if ($pesanan->id_konsumen !== auth()->id()) {
            return back()->withErrors(['id_pesanan' => 'Anda tidak berhak memberikan rating untuk pesanan ini.']);
        }

        Rating::updateOrCreate(
            ['id_pesanan' => $request->id_pesanan],
            [
                'id_konsumen' => auth()->id(),
                'rating' => $request->rating,
                'komentar' => $request->komentar,
                'tanggal' => now(),
            ]
        );

        return back()->with('success', 'Terima kasih atas ulasan Anda!');
    }
}