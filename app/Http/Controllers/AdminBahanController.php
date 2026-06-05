<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bahan;

class AdminBahanController extends Controller
{
    public function index()
    {
        $bahans = Bahan::orderBy('nama_bahan')->paginate(20);
        return view('admin.stok.index', compact('bahans'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_bahan' => 'required|string|max:255',
            'stok' => 'required|integer|min:0',
            'satuan' => 'required|string|max:50',
            'harga_beli' => 'required|numeric|min:0',
        ]);
        Bahan::create($data);
        return back()->with('success', 'Bahan berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $bahan = Bahan::findOrFail($id);
        $data = $request->validate([
            'nama_bahan' => 'required|string|max:255',
            'stok' => 'required|integer|min:0',
            'satuan' => 'required|string|max:50',
            'harga_beli' => 'required|numeric|min:0',
        ]);
        $bahan->update($data);
        return back()->with('success', 'Bahan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        Bahan::findOrFail($id)->delete();
        return back()->with('success', 'Bahan berhasil dihapus.');
    }
}
