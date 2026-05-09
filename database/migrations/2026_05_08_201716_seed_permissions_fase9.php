<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Registra los 19 permisos granulares de Fases 1-8 y los asigna a los roles
 * repro y empresa. Es idempotente (updateOrInsert / INSERT IGNORE).
 *
 * NOTA: La asignacion de permisos a roles se omite si los roles aun no tienen
 * datos (p.ej. en migrate:fresh sobre BD de testing vacia). En ese caso el
 * seeder RolesAndPermissionsSeeder se encarga de la asignacion completa.
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $permisos = [
            ['name' => 'sedes.ver',                 'display_name' => 'Ver Sedes',                 'module' => 'sedes'],
            ['name' => 'sedes.crear',               'display_name' => 'Crear Sedes',               'module' => 'sedes'],
            ['name' => 'sedes.editar',              'display_name' => 'Editar Sedes',              'module' => 'sedes'],
            ['name' => 'sedes.eliminar',            'display_name' => 'Eliminar Sedes',            'module' => 'sedes'],
            ['name' => 'finanzas.ver',              'display_name' => 'Ver Finanzas',              'module' => 'finanzas'],
            ['name' => 'finanzas.editar',           'display_name' => 'Editar Finanzas',           'module' => 'finanzas'],
            ['name' => 'calendario.ver',            'display_name' => 'Ver Calendario',            'module' => 'calendario'],
            ['name' => 'calendario.editar',         'display_name' => 'Editar Calendario',         'module' => 'calendario'],
            ['name' => 'notificaciones.ver',        'display_name' => 'Ver Notificaciones',        'module' => 'notificaciones'],
            ['name' => 'notificaciones.gestionar',  'display_name' => 'Gestionar Notificaciones',  'module' => 'notificaciones'],
            ['name' => 'documentos.ver',            'display_name' => 'Ver Documentos',            'module' => 'documentos'],
            ['name' => 'documentos.subir',          'display_name' => 'Subir Documentos',          'module' => 'documentos'],
            ['name' => 'documentos.verificar',      'display_name' => 'Verificar Documentos',      'module' => 'documentos'],
            ['name' => 'documentos.eliminar',       'display_name' => 'Eliminar Documentos',       'module' => 'documentos'],
            ['name' => 'informe_preliminar.ver',    'display_name' => 'Ver Informe Preliminar',    'module' => 'informe_preliminar'],
            ['name' => 'informe_preliminar.editar', 'display_name' => 'Editar Informe Preliminar', 'module' => 'informe_preliminar'],
            ['name' => 'observacion.ver',           'display_name' => 'Ver Observaciones',         'module' => 'observacion'],
            ['name' => 'observacion.editar',        'display_name' => 'Editar Observaciones',      'module' => 'observacion'],
            ['name' => 'historial_dpi.ver',         'display_name' => 'Ver Historial DPI',         'module' => 'historial_dpi'],
        ];

        foreach ($permisos as $permiso) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $permiso['name']],
                array_merge($permiso, ['description' => null, 'created_at' => $now, 'updated_at' => $now])
            );
        }

        // Omitir asignacion a roles si no existen aun (migrate:fresh en testing)
        if (DB::table('roles')->count() === 0) {
            return;
        }

        $adminId = DB::table('roles')->where('name', 'admin')->value('id');
        if ($adminId) {
            foreach (DB::table('permissions')->pluck('id') as $permId) {
                DB::table('role_permission')->updateOrInsert(
                    ['role_id' => $adminId, 'permission_id' => $permId],
                    ['role_id' => $adminId, 'permission_id' => $permId, 'created_at' => $now, 'updated_at' => $now]
                );
            }
        }

        $reproId = DB::table('roles')->where('name', 'repro')->value('id');
        if ($reproId) {
            $this->assignByName($reproId, [
                'sedes.ver', 'finanzas.ver', 'calendario.ver', 'calendario.editar',
                'notificaciones.ver', 'documentos.ver', 'documentos.subir', 'documentos.verificar',
                'informe_preliminar.ver', 'informe_preliminar.editar',
                'observacion.ver', 'observacion.editar', 'historial_dpi.ver',
            ], $now);
        }

        $empresaId = DB::table('roles')->where('name', 'empresa')->value('id');
        if ($empresaId) {
            $this->assignByName($empresaId, [
                'sedes.ver', 'sedes.crear', 'sedes.editar',
                'calendario.ver', 'notificaciones.ver',
                'documentos.ver', 'documentos.subir',
                'informe_preliminar.ver',
            ], $now);
        }
    }

    public function down(): void
    {
        $names = [
            'sedes.ver', 'sedes.crear', 'sedes.editar', 'sedes.eliminar',
            'finanzas.ver', 'finanzas.editar',
            'calendario.ver', 'calendario.editar',
            'notificaciones.ver', 'notificaciones.gestionar',
            'documentos.ver', 'documentos.subir', 'documentos.verificar', 'documentos.eliminar',
            'informe_preliminar.ver', 'informe_preliminar.editar',
            'observacion.ver', 'observacion.editar',
            'historial_dpi.ver',
        ];
        $ids = DB::table('permissions')->whereIn('name', $names)->pluck('id');
        DB::table('role_permission')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('name', $names)->delete();
    }

    private function assignByName(int $roleId, array $names, \Illuminate\Support\Carbon $now): void
    {
        foreach ($names as $name) {
            $permId = DB::table('permissions')->where('name', $name)->value('id');
            if ($permId) {
                DB::table('role_permission')->updateOrInsert(
                    ['role_id' => $roleId, 'permission_id' => $permId],
                    ['role_id' => $roleId, 'permission_id' => $permId, 'created_at' => $now, 'updated_at' => $now]
                );
            }
        }
    }
};
