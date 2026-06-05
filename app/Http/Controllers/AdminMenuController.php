<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Menu;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;

class AdminMenuController extends Controller
{
    public function index()
    {
        $query = Menu::query();
        // filter stok rendah
        if(request()->query('filter') == 'low'){
            $query->where('stok', '<', 10);
        }
        // filter kategori
        if(request()->query('category')){
            $query->where('kategori', request()->query('category'));
        }
        $menus = $query->orderBy('nama_menu')->paginate(10)->withQueryString();
        return view('admin.menu.index', compact('menus'))->with([ 'pageTitle' => 'Manajemen Menu', 'showStockPage' => false ]);
    }

    public function stok()
    {
        $query = Menu::query();
        if(request()->query('filter') == 'low'){
            $query->where('stok', '<', 10);
        }
        if(request()->query('category')){
            $query->where('kategori', request()->query('category'));
        }
        $menus = $query->orderBy('nama_menu')->paginate(10)->withQueryString();
        return view('admin.menu.index', compact('menus'))->with([ 'pageTitle' => 'Manajemen Stok', 'showStockPage' => true ]);
    }

    public function create()
    {
        return view('admin.menu.form', ['menu' => new Menu()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_menu' => 'required|string|max:255',
            'harga' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0',
            'kategori' => 'required|in:makanan,minuman',
            'is_available' => 'nullable|boolean',
            'deskripsi' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:6000',
        ]);
        $data['is_available'] = $request->has('is_available');

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $extension = strtolower($file->extension() ?: 'jpg');
            $filename = time() . '_' . uniqid() . '.' . $extension;
            $path = 'menus/' . $filename;

            if (extension_loaded('gd') || extension_loaded('imagick')) {
                $manager = extension_loaded('gd') ? ImageManager::gd() : ImageManager::imagick();
                $img = $manager->read($file)
                    ->resize(800, null, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    })
                    ->encode('jpg', 80);

                Storage::disk('public')->put($path, (string) $img);
            } else {
                Storage::disk('public')->putFileAs('menus', $file, $filename);
            }

            $data['image'] = $path;
        }

        Menu::create($data);
        return redirect()->route('admin.menu.index')->with('success', 'Menu berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $menu = Menu::findOrFail($id);
        return view('admin.menu.form', compact('menu'));
    }

    public function update(Request $request, $id)
    {
        $menu = Menu::findOrFail($id);
        $data = $request->validate([
            'nama_menu' => 'required|string|max:255',
            'harga' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0',
            'kategori' => 'required|in:makanan,minuman',
            'is_available' => 'nullable|boolean',
            'deskripsi' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:6000',
        ]);
        $data['is_available'] = $request->has('is_available');

        if ($request->hasFile('image')) {
            // delete old image if exists
            if ($menu->image && Storage::disk('public')->exists($menu->image)) {
                Storage::disk('public')->delete($menu->image);
            }
            $file = $request->file('image');
            $extension = strtolower($file->extension() ?: 'jpg');
            $filename = time() . '_' . uniqid() . '.' . $extension;
            $path = 'menus/' . $filename;

            if (extension_loaded('gd') || extension_loaded('imagick')) {
                $manager = extension_loaded('gd') ? ImageManager::gd() : ImageManager::imagick();
                $img = $manager->read($file)
                    ->resize(800, null, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    })
                    ->encode('jpg', 80);

                Storage::disk('public')->put($path, (string) $img);
            } else {
                Storage::disk('public')->putFileAs('menus', $file, $filename);
            }

            $data['image'] = $path;
        }

        $menu->update($data);
        return redirect()->route('admin.menu.index')->with('success', 'Menu berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $menu = Menu::findOrFail($id);
        $menu->delete();
        return redirect()->route('admin.menu.index')->with('success', 'Menu berhasil dihapus.');
    }

    // Update stock endpoint (AJAX or form)
    public function updateStock(Request $request, $id)
    {
        $menu = Menu::findOrFail($id);
        $data = $request->validate(['stok' => 'required|integer|min:0']);
        $menu->stok = $data['stok'];
        $menu->save();
        return back()->with('success', 'Stok berhasil diperbarui.');
    }
}
