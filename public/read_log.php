<?php
if (!isset($_GET['key']) || $_GET['key'] !== 'REPRO_DEPLOY_2026_SECURE_KEY') {
    http_response_code(403);
    die('Acceso denegado.');
}
header('Content-Type: text/plain; charset=utf-8');
$logPath = __DIR__ . '/../storage/logs/laravel.log';
if (!file_exists($logPath)) {
    echo "Log no encontrado\n";
    exit;
}
$lines = file($logPath, FILE_IGNORE_NEW_LINES);
echo implode("\n", array_slice($lines, -120));
