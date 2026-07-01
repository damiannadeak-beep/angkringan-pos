<?php
$envPath = __DIR__.'/../.env';
if (file_exists($envPath)) {
    $envContent = file_get_contents($envPath);
    preg_match('/DB_HOST=(.*)/', $envContent, $host);
    preg_match('/DB_DATABASE=(.*)/', $envContent, $db);
    preg_match('/DB_USERNAME=(.*)/', $envContent, $user);
    preg_match('/DB_PASSWORD=(.*)/', $envContent, $pass);
    
    $host = trim($host[1] ?? '127.0.0.1');
    $db = trim($db[1] ?? '');
    $user = trim($user[1] ?? '');
    $pass = trim($pass[1] ?? '');
    
    // Remove quotes if they exist
    $pass = trim($pass, '"\'');

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
        $stmt = $pdo->query('SELECT id, nama_menu, variants_json, stok FROM menu WHERE is_available = 1');
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        header('Content-Type: application/json');
        echo json_encode($result);
    } catch (Exception $e) {
        echo "PDO Error: " . $e->getMessage();
    }
} else {
    echo "No .env file found.";
}
