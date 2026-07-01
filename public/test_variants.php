<?php
$env = parse_ini_file(__DIR__.'/../.env');
try {
    $pdo = new PDO('mysql:host='.$env['DB_HOST'].';dbname='.$env['DB_DATABASE'], $env['DB_USERNAME'], $env['DB_PASSWORD']);
    $stmt = $pdo->query('SELECT id, nama_menu, variants_json, stok FROM menu WHERE is_available = 1');
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
