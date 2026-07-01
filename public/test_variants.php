<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->boot();

header('Content-Type: application/json');
echo json_encode(\App\Models\Menu::all());
