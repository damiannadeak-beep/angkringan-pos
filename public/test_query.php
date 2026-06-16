<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$promos = \App\Models\Promo::where('is_active', true)
    ->where(function($q) {
        $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
    })
    ->where(function($q) {
        $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
    })
    ->get()->toArray();

dump($promos);
