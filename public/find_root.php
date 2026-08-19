<?php
header('Content-Type: text/plain');
echo "DOCUMENT_ROOT: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'unknown') . "\n";
echo "SCRIPT_FILENAME: " . ($_SERVER['SCRIPT_FILENAME'] ?? 'unknown') . "\n";
echo "CURRENT_DIR: " . __DIR__ . "\n\n";

$storageDir = __DIR__ . '/storage';
$menusDir = __DIR__ . '/storage/menus';

echo "Check " . $storageDir . ": " . (file_exists($storageDir) ? "EXISTS" : "NOT FOUND") . "\n";
echo "Check " . $menusDir . ": " . (file_exists($menusDir) ? "EXISTS" : "NOT FOUND") . "\n\n";

if (file_exists($menusDir)) {
    $files = array_diff(scandir($menusDir), ['.', '..']);
    echo "Files count in " . $menusDir . ": " . count($files) . "\n";
    print_r(array_slice($files, 0, 5));
} else {
    echo "Attempting auto-creation of storage directory...\n";
    @mkdir($menusDir, 0755, true);
    
    // Copy from parent storage/app/public/menus if available
    $source = __DIR__ . '/../storage/app/public/menus';
    if (!file_exists($source)) {
        $source = dirname(__DIR__) . '/storage/app/public/menus';
    }
    
    if (file_exists($source)) {
        echo "Source found at: " . $source . "\n";
        $sourceFiles = glob($source . '/*');
        $copied = 0;
        foreach ($sourceFiles as $sf) {
            if (is_file($sf)) {
                @copy($sf, $menusDir . '/' . basename($sf));
                $copied++;
            }
        }
        echo "Auto-copied " . $copied . " menu files to " . $menusDir . "\n";
    } else {
        echo "Source not found at: " . $source . "\n";
    }
}
