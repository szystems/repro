<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Registra los 19 permisos granulares correspondientes a las funcionalidades
 * nuevas de Fases 1-8 (sedes, finanzas, calendario, notificaciones, documentos,
 * informe_preliminar, observacion, historial_dpi) y los asigna a los roles
 * repro y empresa segun su alcance funcional.
 *
 * Es idempotente: usa updateOrInsert / INSERT IGNORE para no duplicar.
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        // --- Nuevos permisos ---
        $permisos = [
            ['name' => 'sedes.ver',                   'display_name' => 'Ver Sedes',                     'module' => 'sedes'],
            ['name' => 'sedes.crear',                 'display_name' => 'Crear Sedes',                   'module' => 'sedes'],
            ['name' => 'sedes.editar',                'display_name' => 'Editar Sedes',                  'module' => 'sedes'],
            ['name' => 'sedes.eliminar',              'display_name' => 'Eliminar Sedes',                'module' => 'sedes'],
            ['name' => 'finanzas.ver',                'display_name' => 'Ver Finanzas',                  'module' => 'finanzas'],
            ['name' => 'finanzas.editar',             'display_name' => 'Editar Finanzas',               'module' => 'finanzas'],
            ['name' => 'calendario.ver',              'display_name' => 'Ver Calendario',                'module' => 'calendario'],
            ['name' => 'calendario.editar',           'display_name' => 'Editar Calendario',             'module' => 'calendario'],
            ['name' => 'notificaciones.ver',          'display_name' => 'Ver Notificaciones',            'module' => 'notificaciones'],
            ['name' => 'notificaciones.gestionar',    'display_name' => 'Gestionar Notificaciones',      'module' => 'notificaciones'],
            ['name' => 'documentos.ver',              'display_name' => 'Ver Documentos',                'module' => 'documentos'],
            ['name' => 'documentos.subir',            'display_name' => 'Subir Documentos',              'module' => 'documentos'],
            ['name' => 'documentos.verificar',        'display_name' => 'Verificar Documentos',          'module' => 'documentos'],
            ['name' => 'documentos.eliminar',         'display_name' => 'Eliminar Documentos',           'module' => 'documentos'],
            ['name' => 'informe_preliminar.ver',      'display_name' => 'Ver Informe Preliminar',        'module' => 'informe_preliminar'],
            ['name' => 'informe_preliminar.editar',   'display_name' => 'Editar Informe Preliminar',     'module' => 'informe_preliminar'],
            ['name' => 'observacion.ver',             'display_name' => 'Ver Observaciones',             'module' => 'observacion'],
            ['name' => 'observacion.editar',          'display_name' => 'Editar Observaciones',          'module' => 'observacion'],
            ['name' => 'historial_dpi.ver',           'display_name' => 'Ver Historial DPI',             'module' => 'historial_dpi'],
        ];

        foreach ($permisos as $permiso) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $permiso['name']],
                array_merge($permiso, ['description' => null, 'created_at' => $now, 'updated_at' => $now])
            );
        }

        // --- Asignar admin: todos los permisos (incluye los nuevos) ---
        $adminId = DB::table('roles')->where('name', 'admin')->value('id');
        $allPermIds = DB::table('permissions')->pluck('id');
        foreach ($allPermIds as $permId) {
            DB::table('role_permission')->updateOrInsert(
                ['role_id' => $adminId, 'permission_id' => $permId],
                ['role_id' => $adminId, 'permission_id' => $permId, 'created_at' => $now, 'updated_at' => $now]
            );
        }

        // --- Asignar repro ---
        $reproId = DB::table('roles')->where('name', 'repro')->value('id');
        $reproPerms = [
            'sedes.ver', 'finanzas.ver', 'calendario.ver', 'calendario.editar',
            'notificaciones.ver', 'documentos.ver', 'documentos.subir', 'documentos.verificar',
            'informe_preliminar.ver', 'informe_preliminar.editar',
            'observacion.ver', 'observacion.editar', 'historial_dpi.ver',
        ];
        $this->assignByName($reproId, $reproPerms, $now);

        // --- Asignar empresa ---
        $empresaId = DB::table('roles')->where('name', 'empresa')->value('id');
        $empresaPerms = [
            'sedes.ver', 'sedes.crear', 'sedes.editar',
            'calendario.ver', 'notificaciones.ver',
            'documentos.ver', 'documentos.subir',
            'informe_preliminar.ver',
        ];
        $this->assignByName($empresaId, $empresaPerms, $now);
    }

    public function down(): void
    {
        // Solo elimina los permisos nuevos; las asignaciones en role_permission
        // se eliminan en cascada si hay FK, o se limpian manualmente.
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
