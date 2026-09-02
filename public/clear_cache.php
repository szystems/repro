<?php
if (!isset($_GET['key']) || $_GET['key'] !== 'REPRO_DEPLOY_2026_SECURE_KEY') {
    http_response_code(403); die('Acceso denegado.');
}
header('Content-Type: text/plain; charset=utf-8');

$viewsCache = __DIR__ . '/../storage/framework/views/';
$count = 0;
foreach (glob($viewsCache . '*.php') as $f) {
    unlink($f);
    $count++;
}
foreach (glob(__DIR__ . '/../bootstrap/cache/*.php') ?: [] as $f) {
    $base = basename($f);
    if (in_array($base, ['packages.php', 'services.php', 'events.php'], true)) {
        continue;
    }
    @unlink($f);
}
if (function_exists('opcache_reset')) { opcache_reset(); }
echo "Vistas eliminadas: $count\nOPcache: limpiado\n[Script auto-eliminado]\n";
@unlink(__FILE__);
