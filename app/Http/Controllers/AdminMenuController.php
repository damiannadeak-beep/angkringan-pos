<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Menu;
use App\Models\Setting;
use App\Services\MenuService;
use App\Http\Requests\Admin\{StoreMenuRequest, UpdateMenuRequest};
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AdminMenuController extends Controller
{
    public function index(Request $request, MenuService $menuService)
    {
        $menus = $menuService->getPaginatedMenus(
            $request->query('filter'),
            $request->query('category')
        );
        $pageTitle = 'Manajemen Menu';
        $showStockPage = false;

        return view('admin.menu.index', compact('menus', 'pageTitle', 'showStockPage'));
    }

    public function stok(Request $request, MenuService $menuService)
    {
        $menus = $menuService->getPaginatedMenus(
            $request->query('filter'),
            $request->query('category')
        );
        $pageTitle = 'Manajemen Stok';
        $showStockPage = true;

        return view('admin.menu.index', compact('menus', 'pageTitle', 'showStockPage'));
    }

    public function create()
    {
        $bahans = \App\Models\Bahan::all();
        return view('admin.menu.form', ['menu' => new Menu(), 'bahans' => $bahans]);
    }

    public function store(StoreMenuRequest $request, MenuService $menuService)
    {
        $menuService->createMenu($request->validated(), $request->all());
        return redirect()->route('admin.menu.index')->with('success', 'Menu berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $menu = Menu::with('bahans')->findOrFail($id);
        $bahans = \App\Models\Bahan::all();
        return view('admin.menu.form', compact('menu', 'bahans'));
    }

    public function update(UpdateMenuRequest $request, $id, MenuService $menuService)
    {
        $menu = Menu::findOrFail($id);
        $menuService->updateMenu($menu, $request->validated(), $request->all());
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

    public function updateStock(Request $request, $id, MenuService $menuService)
    {
        $data = $request->validate(['stok' => 'required|integer|min:0']);
        $menu = Menu::findOrFail($id);
        $menuService->updateStock($menu, (int) $data['stok']);
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

            $errorMsg = $response->json('error.message') ?? 'Unknown error';
            Log::error('Gemini API Error: ' . $response->body());
            return response()->json(['error' => 'Gagal menghubungi server AI. ' . $errorMsg], 500);
        } catch (\Exception $e) {
            Log::error('Gemini API Exception: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan sistem: ' . $e->getMessage()], 500);
        }
    }
}
