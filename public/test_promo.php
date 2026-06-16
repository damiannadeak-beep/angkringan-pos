<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $req = Illuminate\Http\Request::create('/admin/promo/2', 'PUT', [
        'title' => 'Test Promo Update',
        'type' => 'package',
        'discount_type' => 'nominal',
        'value' => 4000,
        'starts_at' => '2026-06-12T11:58',
        'ends_at' => '2026-06-12T10:58',
        'package_menus' => [1],
        'package_qty' => [1],
        'is_active' => 'on'
    ]);

    $controller = app()->make(\App\Http\Controllers\AdminPromoController::class);
    $response = $controller->update($req, 2);

    echo "Update Success! Redirects to: " . $response->getTargetUrl() . "\n";
} catch (\Illuminate\Validation\ValidationException $e) {
    echo "Validation Failed:\n";
    print_r($e->errors());
} catch (\Exception $e) {
    echo "Exception Thrown: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
