<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;

trait HandlesImageUpload
{
    /**
     * Proses upload gambar: simpan simultan ke seluruh lokasi cPanel yang memungkinkan.
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

        // 1. Simpan ke Laravel Public Storage
        Storage::disk('public')->put($path, $content);

        // 2. Daftar seluruh lokasi target di cPanel
        $homeDir = env('HOME') ?: getenv('HOME');
        $docRoot = $_SERVER['DOCUMENT_ROOT'] ?? null;

        $targetPaths = [
            public_path('storage/' . $path),
            storage_path('app/public/' . $path),
        ];

        if ($docRoot) {
            $targetPaths[] = rtrim($docRoot, '/') . '/storage/' . $path;
            $targetPaths[] = rtrim($docRoot, '/') . '/public/storage/' . $path;
            $targetPaths[] = dirname(rtrim($docRoot, '/')) . '/storage/' . $path;
            $targetPaths[] = dirname(rtrim($docRoot, '/')) . '/public/storage/' . $path;
        }

        if ($homeDir) {
            $targetPaths[] = $homeDir . '/public_html/storage/' . $path;
            $targetPaths[] = $homeDir . '/public_html/public/storage/' . $path;
            $targetPaths[] = $homeDir . '/public_html/angkringan.nadeak.net/storage/' . $path;
            $targetPaths[] = $homeDir . '/public_html/angkringan.nadeak.net/public/storage/' . $path;
            $targetPaths[] = $homeDir . '/angkringan.nadeak.net/storage/' . $path;
            $targetPaths[] = $homeDir . '/angkringan.nadeak.net/public/storage/' . $path;
        }

        foreach ($targetPaths as $targetFile) {
            try {
                @mkdir(dirname($targetFile), 0755, true);
                @file_put_contents($targetFile, $content);
                @chmod($targetFile, 0755);
            } catch (\Throwable $e) {
                // Ignore permissions errors
            }
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

        $homeDir = env('HOME') ?: getenv('HOME');
        $docRoot = $_SERVER['DOCUMENT_ROOT'] ?? null;

        $targetPaths = [
            public_path('storage/' . $imagePath),
            storage_path('app/public/' . $imagePath),
        ];

        if ($docRoot) {
            $targetPaths[] = rtrim($docRoot, '/') . '/storage/' . $imagePath;
            $targetPaths[] = rtrim($docRoot, '/') . '/public/storage/' . $imagePath;
        }

        if ($homeDir) {
            $targetPaths[] = $homeDir . '/public_html/storage/' . $imagePath;
            $targetPaths[] = $homeDir . '/public_html/public/storage/' . $imagePath;
            $targetPaths[] = $homeDir . '/public_html/angkringan.nadeak.net/storage/' . $imagePath;
            $targetPaths[] = $homeDir . '/public_html/angkringan.nadeak.net/public/storage/' . $imagePath;
        }

        foreach ($targetPaths as $targetFile) {
            if (file_exists($targetFile)) {
                @unlink($targetFile);
            }
        }
    }
}
