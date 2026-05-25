<?php
if (!isset($_GET['key']) || $_GET['key'] !== 'REPRO_DEPLOY_2026_SECURE_KEY') {
    http_response_code(403); die('Acceso denegado.');
}
header('Content-Type: text/plain; charset=utf-8');
$envContent = file_get_contents(__DIR__ . '/../.env');
function envVal($key, $content) {
    if (preg_match('/^' . $key . '\s*=\s*(.+)$/m', $content, $m)) {
        return trim(trim($m[1]), '"\'');
    }
    return '';
}
$host = envVal('DB_HOST', $envContent);
$port = envVal('DB_PORT', $envContent) ?: '3306';
$db   = envVal('DB_DATABASE', $envContent);
$user = envVal('DB_USERNAME', $envContent);
$pass = envVal('DB_PASSWORD', $envContent);
try {
    $pdo = new PDO("mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (Exception $e) {
    echo "ERROR BD: " . $e->getMessage() . "\n";
    echo "host={$host} db={$db} user={$user}\n";
    @unlink(__FILE__); exit;
}
echo "=== MIGRACIONES EN PRODUCCION ===\n\n";
$stmt = $pdo->query("SELECT migration, batch FROM migrations ORDER BY batch, migration");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
    echo "  batch {$r['batch']} | {$r['migration']}\n";
}
echo "\n=== COLUMNAS ===\n\n";
foreach ([['evaluados_orden','sede_region_empresa'],['evaluados_orden','texto_informe_preliminar'],['configs','dias_vigencia_token'],['configs','nombre_empresa']] as [$t,$c]) {
    $s = $pdo->query("SHOW COLUMNS FROM `{$t}` LIKE '{$c}'");
    $r = $s->fetch();
    echo str_pad("  {$t}.{$c}", 50) . ($r ? "OK ({$r['Type']})" : "!!! FALTA") . "\n";
}
echo "\n[auto-eliminado]\n";
@unlink(__FILE__);
