<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Meja;

class KasirMejaController extends Controller
{
    /**
     * Menampilkan daftar meja untuk dikelola Kasir
     */
    public function index()
    {
        $mejas = Meja::all();
        return view('kasir.meja.index', compact('mejas'));
    }

    /**
     * Mengubah status ketersediaan meja (Tersedia / Terisi)
     */
    public function toggle($id)
    {
        try {
            $meja = Meja::findOrFail($id);
            $meja->update([
                'is_available' => !$meja->is_available
            ]);

            $statusName = $meja->is_available ? 'Tersedia' : 'Terisi';
            return response()->json([
                'message' => 'Status meja berhasil diubah menjadi ' . $statusName,
                'is_available' => $meja->is_available
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
