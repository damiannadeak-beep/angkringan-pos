<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

$req = Illuminate\Http\Request::create('/admin/promo/2', 'PUT', [
    'title' => 'Test',
    'type' => 'package',
    'discount_type' => 'nominal',
    'value' => 4000,
    'starts_at' => '2026-06-12T11:58',
    'ends_at' => '2026-06-12T10:58',
    'package_menus' => [1],
    'package_qty' => [1]
]);

$v = Illuminate\Support\Facades\Validator::make($req->all(), [
    'title' => 'required|string|max:255',
    'description' => 'nullable|string',
    'type' => 'required|in:discount,package',
    'discount_type' => 'nullable|in:percentage,nominal',
    'value' => 'required|numeric|min:0',
    'starts_at' => 'nullable|date',
    'ends_at' => 'nullable|date',
    'is_active' => 'nullable|boolean',
    'days' => 'nullable|array',
    'days.*' => 'string'
]);

if ($v->fails()) {
    print_r($v->errors()->all());
} else {
    echo "Validation Passed\n";
}
