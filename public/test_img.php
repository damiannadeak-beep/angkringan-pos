<?php
header('Content-Type: application/json');
echo json_encode([
    'status' => 'OK',
    'time' => date('Y-m-d H:i:s'),
    'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'unknown',
    'script_filename' => $_SERVER['SCRIPT_FILENAME'] ?? 'unknown',
    'current_dir' => __DIR__,
    'is_cpanel_subdomain' => str_contains(__DIR__, 'angkringan.nadeak.net'),
    'files_in_dir' => array_slice(scandir(__DIR__), 0, 10)
], JSON_PRETTY_PRINT);
