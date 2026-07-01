<?php
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPcache berhasil direset!";
} else {
    echo "OPcache tidak aktif.";
}
// Also try to clear views programmatically via the web server to ensure it runs under the web PHP process
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->boot();
\Illuminate\Support\Facades\Artisan::call('view:clear');
\Illuminate\Support\Facades\Artisan::call('cache:clear');
echo " View dan Cache Laravel berhasil dihapus.";
