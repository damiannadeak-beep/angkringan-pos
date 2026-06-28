<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PermintaanBelanja;
use App\Models\Bahan;

class PermintaanBelanjaController extends Controller
{
    // ==========================================
    // SISI KASIR
    // ==========================================
    
    public function kasirIndex()
    {
        $permintaans = PermintaanBelanja::orderBy('created_at', 'desc')
                        ->paginate(10);
                        
        // Ambil daftar bahan baku untuk dropdown (opsional)
        $bahans = Bahan::orderBy('nama_bahan')->get();
                        
        return view('kasir.permintaan_belanja.index', compact('permintaans', 'bahans'));
    }

    public function kasirStore(Request $request)
    {
        $request->validate([
            'nama_barang' => 'required|string|max:255',
            'sisa_stok' => 'nullable|string|max:255',
            'jumlah_diminta' => 'required|string|max:255',
            'catatan' => 'nullable|string',
        ]);

        PermintaanBelanja::create([
            'user_id' => auth()->id(),
            'nama_barang' => $request->nama_barang,
            'sisa_stok' => $request->sisa_stok,
            'jumlah_diminta' => $request->jumlah_diminta,
            'catatan' => $request->catatan,
            'status' => 'menunggu'
        ]);

        return redirect()->route('kasir.permintaan.index')->with('success', 'Permintaan belanja berhasil dikirim.');
    }

    // ==========================================
    // SISI ADMIN
    // ==========================================

    public function adminIndex()
    {
        $permintaans = PermintaanBelanja::with('user')
                        ->orderByRaw("FIELD(status, 'menunggu', 'sudah_dibeli', 'ditolak')")
                        ->orderBy('created_at', 'desc')
                        ->paginate(15);
                        
        return view('admin.permintaan_belanja.index', compact('permintaans'));
    }

    public function adminUpdateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:menunggu,sudah_dibeli,ditolak'
        ]);

        $permintaan = PermintaanBelanja::findOrFail($id);
        $permintaan->update(['status' => $request->status]);

        return redirect()->back()->with('success', 'Status permintaan berhasil diperbarui.');
    }

    public function adminStore(Request $request)
    {
        $request->validate([
            'nama_barang' => 'required|array',
            'nama_barang.*' => 'required|string|max:255',
            'jumlah_ditambah' => 'required|array',
            'jumlah_ditambah.*' => 'required|string|max:255',
            'catatan' => 'nullable|string'
        ]);

        foreach ($request->nama_barang as $index => $nama) {
            $jumlah = $request->jumlah_ditambah[$index];

            PermintaanBelanja::create([
                'user_id' => auth()->id(),
                'nama_barang' => $nama,
                'sisa_stok' => null,
                'jumlah_diminta' => $jumlah,
                'catatan' => $request->catatan,
                'status' => 'sudah_dibeli'
            ]);
        }

        return redirect()->back()->with('success', 'Daftar belanja berhasil dicatat ke riwayat.');
    }
}
