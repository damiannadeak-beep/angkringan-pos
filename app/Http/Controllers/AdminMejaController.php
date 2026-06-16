<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use App\Models\Meja;

class AdminMejaController extends Controller
{
    public function index()
    {
        $mejas = Meja::orderBy('nama_meja_atau_nomor')->get();
        return view('admin.meja.index', compact('mejas'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_meja_atau_nomor' => 'required|string|max:50|unique:meja',
        ]);

        Meja::create($data);
        return redirect()->route('admin.meja.index')->with('success', 'Meja berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'nama_meja_atau_nomor' => 'required|string|max:50|unique:meja,nama_meja_atau_nomor,' . $id,
        ]);

        Meja::findOrFail($id)->update($data);
        return redirect()->route('admin.meja.index')->with('success', 'Meja berhasil diupdate.');
    }

    public function destroy($id)
    {
        Meja::findOrFail($id)->delete();
        return redirect()->route('admin.meja.index')->with('success', 'Meja berhasil dihapus.');
    }

    public function printQr($id)
    {
        $meja = Meja::findOrFail($id);
        // FIX #4 (IDOR): Generate Signed URL agar ID meja tidak bisa dimanipulasi
        // URL berlaku tanpa batas waktu, namun memiliki signature kriptografi
        $url = URL::signedRoute('konsumen.menu.meja', ['id_meja' => $meja->id]);
        
        return view('admin.meja.print_qr', compact('meja', 'url'));
    }
}

