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
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg');
        $filename = time() . '_' . uniqid() . '.' . $extension;
        $path = $directory . '/' . $filename;

        // Ambil data biner mentah secara langsung via Laravel UploadedFile get()
        $rawBytes = $file->get();
        $content = $rawBytes;

        if (extension_loaded('gd') || extension_loaded('imagick')) {
            try {
                $manager = extension_loaded('gd') ? ImageManager::gd() : ImageManager::imagick();
                $img = $manager->read($rawBytes);
                if (method_exists($img, 'width') && $img->width() > $maxWidth) {
                    $img->scaleDown(width: $maxWidth);
                }
                if ($extension === 'png') {
                    $encoded = $img->toPng();
                } else if ($extension === 'webp') {
                    $encoded = $img->toWebp($quality);
                } else if ($extension === 'gif') {
                    $encoded = $img->toGif();
                } else {
                    $encoded = $img->toJpeg($quality);
                }
                $content = (string) $encoded;
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Intervention Image failed, saving raw uploaded bytes: ' . $e->getMessage());
                $content = $rawBytes;
            }
        }

        if (!$content) {
            throw new \RuntimeException('Gagal membaca data gambar yang diunggah.');
        }

        // Tulis secara fisik ke seluruh lokasi storage cPanel
        $destinations = [
            public_path('storage/' . $path),
            storage_path('app/public/' . $path),
        ];

        if (isset($_SERVER['DOCUMENT_ROOT'])) {
            $destinations[] = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/storage/' . $path;
            $destinations[] = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/public/storage/' . $path;
        }

        $homeDir = env('HOME') ?: getenv('HOME') ?: '/home/nadp3189';
        if ($homeDir) {
            // Folder utama di home (DOCUMENT ROOT AKTIF)
            $destinations[] = $homeDir . '/angkringan.nadeak.net/public/storage/' . $path;
            $destinations[] = $homeDir . '/angkringan.nadeak.net/storage/app/public/' . $path;
            $destinations[] = $homeDir . '/angkringan.nadeak.net/storage/' . $path;
            // Folder public_html
            $destinations[] = $homeDir . '/public_html/storage/' . $path;
            $destinations[] = $homeDir . '/public_html/public/storage/' . $path;
            $destinations[] = $homeDir . '/public_html/storage/app/public/' . $path;
            // Folder public_html/angkringan.nadeak.net
            $destinations[] = $homeDir . '/public_html/angkringan.nadeak.net/storage/' . $path;
            $destinations[] = $homeDir . '/public_html/angkringan.nadeak.net/public/storage/' . $path;
            $destinations[] = $homeDir . '/public_html/angkringan.nadeak.net/storage/app/public/' . $path;
        }

        $savedPaths = [];
        $failedPaths = [];

        foreach (array_unique($destinations) as $dest) {
            try {
                $dir = dirname($dest);
                if (!is_dir($dir)) {
                    @mkdir($dir, 0777, true);
                }
                $res = @file_put_contents($dest, $content);
                @chmod($dir, 0777);
                @chmod($dest, 0777);
                if ($res !== false) {
                    $savedPaths[] = $dest;
                } else {
                    $failedPaths[] = $dest;
                }
            } catch (\Throwable $e) {
                $failedPaths[] = $dest . ' (' . $e->getMessage() . ')';
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
