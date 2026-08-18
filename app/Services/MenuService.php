<?php

namespace App\Services;

use App\Models\Menu;
use App\Traits\HandlesImageUpload;

class MenuService
{
    use HandlesImageUpload;

    /**
     * Ambil list menu terpaginasi dengan filter stok dan kategori.
     */
    public function getPaginatedMenus(?string $filter = null, ?string $category = null, int $perPage = 10)
    {
        $query = Menu::query();

        if ($filter === 'low') {
            $query->where('stok', '<', 10);
        }

        if ($category) {
            $query->where('kategori', $category);
        }

        return $query->orderBy('nama_menu')->paginate($perPage)->withQueryString();
    }

    /**
     * Buat menu baru + upload gambar + sync bahan baku.
     */
    public function createMenu(array $data, array $requestData): Menu
    {
        $data['is_available'] = !empty($requestData['is_available']);

        if (isset($requestData['image']) && $requestData['image'] instanceof \Illuminate\Http\UploadedFile) {
            $data['image'] = $this->processImageUpload($requestData['image']);
        }

        $menu = Menu::create($data);

        $this->syncBahans($menu, $requestData);

        return $menu;
    }

    /**
     * Update menu + upload gambar baru (jika ada) + sync bahan baku.
     */
    public function updateMenu(Menu $menu, array $data, array $requestData): Menu
    {
        $data['is_available'] = !empty($requestData['is_available']);

        if (isset($requestData['image']) && $requestData['image'] instanceof \Illuminate\Http\UploadedFile) {
            $this->deleteOldImage($menu->image);
            $data['image'] = $this->processImageUpload($requestData['image']);
        }

        $menu->update($data);

        $this->syncBahans($menu, $requestData);

        return $menu;
    }

    /**
     * Sync relasi bahan baku (resep) ke menu.
     */
    private function syncBahans(Menu $menu, array $requestData): void
    {
        if (isset($requestData['bahans']) && is_array($requestData['bahans'])) {
            $syncData = [];
            foreach ($requestData['bahans'] as $index => $bahanId) {
                if (!empty($bahanId)) {
                    $qty = $requestData['jumlah_dibutuhkan'][$index] ?? 1;
                    $syncData[$bahanId] = ['jumlah_dibutuhkan' => $qty];
                }
            }
            $menu->bahans()->sync($syncData);
        } else {
            $menu->bahans()->detach();
        }
    }

    /**
     * Update stok menu saja.
     */
    public function updateStock(Menu $menu, int $stok): void
    {
        $menu->stok = $stok;
        $menu->save();
    }
}
