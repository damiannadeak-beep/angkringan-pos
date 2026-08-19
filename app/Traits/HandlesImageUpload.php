<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;

trait HandlesImageUpload
{
    /**
     * Proses upload gambar: resize + compress jika library tersedia.
     * Simpan simultan ke storage/app/public, public/storage, dan ~/public_html/storage.
     *
     * @param  UploadedFile  $file
     * @param  string        $directory   Sub-directory di disk 'public'
     * @param  int           $maxWidth    Lebar maksimal setelah resize
     * @param  int           $quality     Kualitas JPEG (0-100)
     * @return string        Path relatif ke disk 'public'
     */
    protected function processImageUpload(
        UploadedFile $file,
        string $directory = 'menus',
        int $maxWidth = 800,
        int $quality = 80
    ): string {
        $extension = strtolower($file->extension() ?: 'jpg');
        $filename = time() . '_' . uniqid() . '.' . $extension;
        $path = $directory . '/' . $filename;
        $content = null;

        if (extension_loaded('gd') || extension_loaded('imagick')) {
            try {
                $manager = extension_loaded('gd') ? ImageManager::gd() : ImageManager::imagick();
                $img = $manager->read($file)->scaleDown($maxWidth)->toJpeg($quality);
                $content = (string) $img;
            } catch (\Throwable $e) {
                $content = file_get_contents($file->getRealPath());
            }
        } else {
            $content = file_get_contents($file->getRealPath());
        }

        // 1. Simpan ke Laravel Public Disk
        Storage::disk('public')->put($path, $content);

        // 2. Simpan langsung ke folder public/storage
        $publicStorageFile = public_path('storage/' . $path);
        @mkdir(dirname($publicStorageFile), 0755, true);
        @file_put_contents($publicStorageFile, $content);

        // 3. Simpan ke ~/public_html/storage jika berjalan di hosting cPanel
        $homeDir = env('HOME') ?: getenv('HOME');
        if ($homeDir && file_exists($homeDir . '/public_html')) {
            $cpanelStorageFile = $homeDir . '/public_html/storage/' . $path;
            @mkdir(dirname($cpanelStorageFile), 0755, true);
            @file_put_contents($cpanelStorageFile, $content);
        }

        return $path;
    }

    /**
     * Hapus gambar lama dari seluruh lokasi storage jika ada.
     */
    protected function deleteOldImage(?string $imagePath): void
    {
        if (!$imagePath) {
            return;
        }

        if (Storage::disk('public')->exists($imagePath)) {
            Storage::disk('public')->delete($imagePath);
        }

        $publicStorageFile = public_path('storage/' . $imagePath);
        if (file_exists($publicStorageFile)) {
            @unlink($publicStorageFile);
        }

        $homeDir = env('HOME') ?: getenv('HOME');
        if ($homeDir && file_exists($homeDir . '/public_html')) {
            $cpanelStorageFile = $homeDir . '/public_html/storage/' . $imagePath;
            if (file_exists($cpanelStorageFile)) {
                @unlink($cpanelStorageFile);
            }
        }
    }
}
