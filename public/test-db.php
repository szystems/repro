<?php
// Archivo temporal para probar conexión a BD - ELIMINAR DESPUÉS

require_once __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\User;

echo "<h2>Test de conexión a BD</h2>";

try {
    // Probar conexión
    DB::connection()->getPdo();
    echo "<p style='color:green'>✅ Conexión a BD exitosa</p>";

    // Mostrar info de BD
    echo "<p>Host: " . env('DB_HOST') . "</p>";
    echo "<p>Database: " . env('DB_DATABASE') . "</p>";

    // Contar usuarios
    $userCount = User::count();
    echo "<p>Total usuarios en BD: <strong>{$userCount}</strong></p>";

    // Listar usuarios (solo emails)
    echo "<h3>Usuarios existentes:</h3>";
    echo "<ul>";
    $users = User::select('id', 'email', 'name', 'role_as')->get();
    foreach ($users as $user) {
        $role = match($user->role_as) {
            3 => 'Admin',
            2 => 'Repro',
            1 => 'Empresa',
            default => 'Otro'
        };
        echo "<li>{$user->email} - {$user->name} ({$role})</li>";
    }
    echo "</ul>";

} catch (\Exception $e) {
    echo "<p style='color:red'>❌ Error de conexión: " . $e->getMessage() . "</p>";
}

echo "<hr><p style='color:orange'>⚠️ ELIMINA ESTE ARCHIVO DESPUÉS DE USARLO</p>";
