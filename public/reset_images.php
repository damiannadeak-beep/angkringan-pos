<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

header('Content-Type: application/json');

// Reset seluruh kolom image di database menjadi NULL agar tidak ada lagi error 404 merah di console
\App\Models\Menu::query()->update(['image' => null]);

echo json_encode([
    'status' => 'success',
    'message' => 'Seluruh data foto lama di database telah dibersihkan! Anda sekarang bisa upload foto baru dari HP/Laptop via Admin Panel secara bersih tanpa error 404.'
], JSON_PRETTY_PRINT);
