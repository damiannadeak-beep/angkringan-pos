<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pengeluaran;

class AdminPengeluaranController extends Controller
{
    public function index(Request $request)
    {
        $pengeluarans = Pengeluaran::orderBy('tanggal', 'desc')->paginate(10);
        return view('admin.pengeluaran.index', compact('pengeluarans'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tanggal' => 'required|date',
            'deskripsi' => 'required|string|max:255',
            'nominal' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string',
        ]);

        $data['user_id'] = auth()->id();

        Pengeluaran::create($data);
        return redirect()->route('admin.pengeluaran.index')->with('success', 'Data pengeluaran berhasil ditambahkan.');
    }

    public function destroy($id)
    {
        Pengeluaran::findOrFail($id)->delete();
        return redirect()->route('admin.pengeluaran.index')->with('success', 'Data pengeluaran berhasil dihapus.');
    }
}
