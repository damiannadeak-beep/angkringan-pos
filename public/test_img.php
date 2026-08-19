<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

header('Content-Type: application/json');

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
    'app_url' => config('app.url'),
    'public_path' => public_path(),
    'storage_path' => storage_path(),
    'menus' => $results
], JSON_PRETTY_PRINT);
