<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Menu;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
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
        $bahans = \App\Models\Bahan::all();
        return view('admin.menu.form', ['menu' => new Menu(), 'bahans' => $bahans]);
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
            'variants_json' => 'nullable|json',
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

        $menu = Menu::create($data);

        // Sync Bahans (Recipe)
        if ($request->has('bahans')) {
            $syncData = [];
            foreach ($request->bahans as $index => $bahanId) {
                if (!empty($bahanId)) {
                    $qty = $request->jumlah_dibutuhkan[$index] ?? 1;
                    $syncData[$bahanId] = ['jumlah_dibutuhkan' => $qty];
                }
            }
            $menu->bahans()->sync($syncData);
        }

        return redirect()->route('admin.menu.index')->with('success', 'Menu berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $menu = Menu::with('bahans')->findOrFail($id);
        $bahans = \App\Models\Bahan::all();
        return view('admin.menu.form', compact('menu', 'bahans'));
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
            'variants_json' => 'nullable|json',
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

        // Sync Bahans (Recipe)
        if ($request->has('bahans')) {
            $syncData = [];
            foreach ($request->bahans as $index => $bahanId) {
                if (!empty($bahanId)) {
                    $qty = $request->jumlah_dibutuhkan[$index] ?? 1;
                    $syncData[$bahanId] = ['jumlah_dibutuhkan' => $qty];
                }
            }
            $menu->bahans()->sync($syncData);
        } else {
            $menu->bahans()->detach();
        }

        return redirect()->route('admin.menu.index')->with('success', 'Menu berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $menu = Menu::findOrFail($id);
        try {
            $menu->delete();
            return redirect()->route('admin.menu.index')->with('success', 'Menu berhasil dihapus.');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) {
                return redirect()->route('admin.menu.index')->with('error', 'Tidak dapat menghapus produk ini karena sudah pernah dipesan. Silakan "Edit" produk ini jika ingin mengganti gambar atau menonaktifkannya.');
            }
            throw $e;
        }
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

    public function generateAiDescription(Request $request)
    {
        $request->validate(['nama_menu' => 'required|string']);
        $namaMenu = $request->nama_menu;

        $apiKey = Setting::getVal('gemini_api_key');
        if (!$apiKey) {
            return response()->json(['error' => 'API Key Gemini belum dikonfigurasi di Pengaturan.'], 400);
        }

        $prompt = "Buatkan deskripsi makanan/minuman yang menggugah selera untuk menu bernama '{$namaMenu}'. Deskripsi harus singkat, maksimal 2 kalimat, gaya bahasa santai khas Indonesia yang cocok untuk jualan restoran/angkringan. Jangan gunakan kata-kata yang terlalu kaku.";

        try {
            $response = Http::post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-lite:generateContent?key=" . $apiKey, [
                'contents' => [
                    ['parts' => [['text' => $prompt]]]
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
                return response()->json(['description' => trim($text)]);
            }

            return response()->json(['error' => 'Gagal menghubungi server AI.'], 500);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan sistem.'], 500);
        }
    }
}
