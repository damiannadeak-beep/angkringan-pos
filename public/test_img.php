<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

header('Content-Type: application/json');

// Auto-sync all existing images in storage/app/public/ to public/storage/ and ~/public_html/storage/
$homeDir = env('HOME') ?: getenv('HOME');
$cpanelPath = ($homeDir && file_exists($homeDir . '/public_html')) ? $homeDir . '/public_html/storage' : null;

$dirs = ['menus', 'qris', 'images'];
$syncedCount = 0;

foreach ($dirs as $dir) {
    $sourceDir = storage_path('app/public/' . $dir);
    $targetPublicDir = public_path('storage/' . $dir);
    
    if (file_exists($sourceDir)) {
        @mkdir($targetPublicDir, 0755, true);
        if ($cpanelPath) {
            @mkdir($cpanelPath . '/' . $dir, 0755, true);
        }

        $files = glob($sourceDir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                $base = basename($file);
                @copy($file, $targetPublicDir . '/' . $base);
                if ($cpanelPath) {
                    @copy($file, $cpanelPath . '/' . $dir . '/' . $base);
                }
                $syncedCount++;
            }
        }
    }
}

$menus = \App\Models\Menu::select('id', 'nama_menu', 'image')->get();

$results = [];
foreach ($menus as $m) {
    $path = $m->image;
    $fullPathPublic = public_path('storage/' . $path);
    $fullPathStorage = storage_path('app/public/' . $path);
    $results[] = [
        'id' => $m->id,
        'nama_menu' => $m->nama_menu,
        'image_in_db' => $path,
        'url_generated' => asset('storage/' . $path),
        'public_file_exists' => file_exists($fullPathPublic),
        'storage_file_exists' => file_exists($fullPathStorage),
    ];
}

echo json_encode([
    'status' => 'success',
    'synced_images_count' => $syncedCount,
    'app_url' => config('app.url'),
    'public_path' => public_path(),
    'storage_path' => storage_path(),
    'cpanel_path' => $cpanelPath,
    'menus' => $results
], JSON_PRETTY_PRINT);
