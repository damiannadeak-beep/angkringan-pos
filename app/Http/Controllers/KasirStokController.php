<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bahan;
use App\Models\Menu;

class KasirStokController extends Controller
{
    /**
     * Tampilkan halaman update stok kasir
     */
    public function index()
    {
        // Ambil semua data bahan baku
        $bahans = Bahan::orderBy('nama_bahan')->get();
        
        // Ambil semua data menu
        $menus = Menu::orderBy('nama_menu')->get();

        return view('kasir.stok.index', compact('bahans', 'menus'));
    }

    /**
     * Update stok bahan dan menu berdasarkan input form
     */
    public function update(Request $request)
    {
        $request->validate([
            'bahan' => 'nullable|array',
            'bahan.*' => 'integer|min:0',
            'menu' => 'nullable|array',
            'menu.*' => 'integer|min:0',
        ]);

        // Update stok Bahan
        if ($request->has('bahan')) {
            foreach ($request->bahan as $id => $stok) {
                Bahan::where('id', $id)->update(['stok' => $stok]);
            }
        }

        // Update stok Menu
        if ($request->has('menu')) {
            foreach ($request->menu as $id => $stok) {
                $is_available = isset($request->menu_available[$id]) && $request->menu_available[$id] == '1' ? true : false;
                
                if ($stok <= 0) {
                    $is_available = false;
                }

                Menu::where('id', $id)->update([
                    'stok' => $stok,
                    'is_available' => $is_available
                ]);
            }
        }

        return redirect()->back()->with('success', 'Stok berhasil diperbarui.');
    }
}
