<?php

namespace Tests\Feature\Concerns;

use App\Models\Permission;
use App\Models\Role;

/**
 * Trait compartido para todos los tests que necesitan roles con permisos.
 *
 * Reemplaza el patrón repetitivo de Role::create() en setUp() sin asignar
 * permisos, que causaba 403 en rutas protegidas con permission: middleware.
 */
trait CreatesRolesAndPermissions
{
    /**
     * Crear roles y asignarles los permisos estándar del sistema.
     * Llamar desde setUp() en los tests que lo necesiten.
     */
    protected function setUpRolesAndPermissions(): void
    {
        $adminRole   = Role::firstOrCreate(['name' => 'admin'],   ['display_name' => 'Administrador',  'level' => 3]);
        $reproRole   = Role::firstOrCreate(['name' => 'repro'],   ['display_name' => 'Personal Repro', 'level' => 2]);
        $empresaRole = Role::firstOrCreate(['name' => 'empresa'], ['display_name' => 'Usuario Empresa','level' => 1]);
        Role::firstOrCreate(['name' => 'evaluado'], ['display_name' => 'Evaluado', 'level' => 0]);

        // Asegurar que el level esté actualizado aunque el rol ya existiera
        $adminRole->update(['level' => 3]);
        $reproRole->update(['level' => 2]);
        $empresaRole->update(['level' => 1]);

        $reproPermisos = [
            'ordenes.ver', 'ordenes.crear', 'ordenes.editar', 'ordenes.eliminar',
            'evaluaciones.ver', 'evaluaciones.crear', 'evaluaciones.editar', 'evaluaciones.realizar',
            'resultados.ver', 'resultados.descargar', 'resultados.editar',
            'cuestionarios.ver',
            'empresas.ver', 'empresas.crear', 'empresas.editar',
            'usuarios.ver',
            'reportes.ver', 'reportes.generar',
            'sedes.ver',
            'finanzas.ver',
            'calendario.ver', 'calendario.editar',
            'notificaciones.ver', 'notificaciones.gestionar',
            'documentos.ver', 'documentos.subir', 'documentos.verificar', 'documentos.eliminar',
            'informe_preliminar.ver', 'informe_preliminar.editar',
            'observacion.ver', 'observacion.editar',
            'historial_dpi.ver',
        ];

        $empresaPermisos = [
            'ordenes.ver', 'ordenes.crear',
            'evaluaciones.ver',
            'resultados.ver', 'resultados.descargar',
            'usuarios.ver', 'usuarios.crear', 'usuarios.editar',
            'sedes.ver', 'sedes.crear', 'sedes.editar',
            'notificaciones.ver',
            'documentos.ver', 'documentos.subir', 'documentos.eliminar',
            'informe_preliminar.ver',
            'observacion.editar',
            'ordenes.editar',
        ];

        $todosLosPermisos = array_unique(array_merge($reproPermisos, $empresaPermisos, [
            'usuarios.ver', 'usuarios.crear', 'usuarios.editar', 'usuarios.eliminar',
            'empresas.ver', 'empresas.crear', 'empresas.editar', 'empresas.eliminar',
            'sedes.ver', 'sedes.crear', 'sedes.editar', 'sedes.eliminar',
            'ordenes.ver', 'ordenes.crear', 'ordenes.editar', 'ordenes.eliminar',
            'empresas.exportar',
        ]));

        foreach ($reproPermisos as $nombre) {
            $perm = Permission::firstOrCreate(
                ['name' => $nombre],
                ['display_name' => $nombre, 'module' => explode('.', $nombre)[0]]
            );
            $reproRole->givePermission($perm);
        }

        foreach ($empresaPermisos as $nombre) {
            $perm = Permission::firstOrCreate(
                ['name' => $nombre],
                ['display_name' => $nombre, 'module' => explode('.', $nombre)[0]]
            );
            $empresaRole->givePermission($perm);
        }

        // Admin recibe todos los permisos (igual que el seeder de producción)
        foreach ($todosLosPermisos as $nombre) {
            $perm = Permission::firstOrCreate(
                ['name' => $nombre],
                ['display_name' => $nombre, 'module' => explode('.', $nombre)[0]]
            );
            $adminRole->givePermission($perm);
        }
    }
}
