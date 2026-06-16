<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$apiKey = trim(\App\Models\Setting::getVal('gemini_api_key'));

$res = Illuminate\Support\Facades\Http::get('https://generativelanguage.googleapis.com/v1beta/models?key=' . $apiKey);
$data = $res->json();
foreach ($data['models'] as $model) {
    if (in_array('generateContent', $model['supportedGenerationMethods'] ?? [])) {
        echo $model['name'] . "\n";
    }
}
if (isset($data['nextPageToken'])) {
    $res2 = Illuminate\Support\Facades\Http::get('https://generativelanguage.googleapis.com/v1beta/models?key=' . $apiKey . '&pageToken=' . $data['nextPageToken']);
    $data2 = $res2->json();
    foreach ($data2['models'] as $model) {
        if (in_array('generateContent', $model['supportedGenerationMethods'] ?? [])) {
            echo $model['name'] . "\n";
        }
    }
}
