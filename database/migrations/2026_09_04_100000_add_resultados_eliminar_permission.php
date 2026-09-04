<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Permiso para borrar archivos de informe. Solo admin + repro.
 * Idempotente. No toca roles personales user_{id} ni datos de órdenes.
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('permissions')->updateOrInsert(
            ['name' => 'resultados.eliminar'],
            [
                'display_name' => 'Eliminar informes (preliminar y final)',
                'module' => 'resultados',
                'description' => 'Quitar archivo preliminar o final. El final de una orden entregada sigue siendo solo admin.',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        if (DB::table('roles')->count() === 0) {
            return;
        }

        $permId = DB::table('permissions')->where('name', 'resultados.eliminar')->value('id');
        if (! $permId) {
            return;
        }

        foreach (['admin', 'repro'] as $roleName) {
            $roleId = DB::table('roles')->where('name', $roleName)->value('id');
            if (! $roleId) {
                continue;
            }

            DB::table('role_permission')->updateOrInsert(
                ['role_id' => $roleId, 'permission_id' => $permId],
                ['role_id' => $roleId, 'permission_id' => $permId, 'created_at' => $now, 'updated_at' => $now]
            );
        }
    }

    public function down(): void
    {
        $permId = DB::table('permissions')->where('name', 'resultados.eliminar')->value('id');
        if (! $permId) {
            return;
        }

        DB::table('role_permission')->where('permission_id', $permId)->delete();
        DB::table('permissions')->where('id', $permId)->delete();
    }
};
