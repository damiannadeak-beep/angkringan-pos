<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;

trait HandlesImageUpload
{
    /**
     * Proses upload gambar: resize + compress jika library tersedia.
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

        if (extension_loaded('gd') || extension_loaded('imagick')) {
            $manager = extension_loaded('gd') ? ImageManager::gd() : ImageManager::imagick();
            $img = $manager->read($file)->scaleDown($maxWidth)->toJpeg($quality);
            Storage::disk('public')->put($path, (string) $img);
        } else {
            Storage::disk('public')->putFileAs($directory, $file, $filename);
        }

        return $path;
    }

    /**
     * Hapus gambar lama dari storage jika ada.
     */
    protected function deleteOldImage(?string $imagePath): void
    {
        if ($imagePath && Storage::disk('public')->exists($imagePath)) {
            Storage::disk('public')->delete($imagePath);
        }
    }
}
