<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear roles
        $roles = [
            [
                'name' => 'admin',
                'display_name' => 'Administrador',
                'description' => 'Acceso total al sistema'
            ],
            [
                'name' => 'repro',
                'display_name' => 'Personal Repro',
                'description' => 'Personal interno de Repro que realiza las pruebas'
            ],
            [
                'name' => 'empresa',
                'display_name' => 'Usuario Empresa',
                'description' => 'Usuario de empresa cliente que solicita pruebas'
            ],
            // NOTA: Los evaluados NO son usuarios del sistema
            // Acceden vía token único en la tabla 'evaluados_orden'
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                ['name' => $role['name']], // Condición de búsqueda
                array_merge($role, [
                    'created_at' => now(),
                    'updated_at' => now()
                ])
            );
        }

        // Crear permisos organizados por módulos
        $permissions = [
            // Módulo: Ordenes
            ['name' => 'ordenes.ver', 'display_name' => 'Ver Órdenes', 'module' => 'ordenes'],
            ['name' => 'ordenes.crear', 'display_name' => 'Crear Órdenes', 'module' => 'ordenes'],
            ['name' => 'ordenes.editar', 'display_name' => 'Editar Órdenes', 'module' => 'ordenes'],
            ['name' => 'ordenes.eliminar', 'display_name' => 'Eliminar Órdenes', 'module' => 'ordenes'],
            
            // Módulo: Evaluaciones
            ['name' => 'evaluaciones.ver', 'display_name' => 'Ver Evaluaciones', 'module' => 'evaluaciones'],
            ['name' => 'evaluaciones.crear', 'display_name' => 'Crear Evaluaciones', 'module' => 'evaluaciones'],
            ['name' => 'evaluaciones.editar', 'display_name' => 'Editar Evaluaciones', 'module' => 'evaluaciones'],
            ['name' => 'evaluaciones.realizar', 'display_name' => 'Realizar Pruebas', 'module' => 'evaluaciones'],
            
            // Módulo: Resultados
            ['name' => 'resultados.ver', 'display_name' => 'Ver Resultados', 'module' => 'resultados'],
            ['name' => 'resultados.descargar', 'display_name' => 'Descargar Resultados', 'module' => 'resultados'],
            ['name' => 'resultados.editar', 'display_name' => 'Editar Resultados', 'module' => 'resultados'],
            
            // Módulo: Cuestionarios
            ['name' => 'cuestionarios.ver', 'display_name' => 'Ver Cuestionarios', 'module' => 'cuestionarios'],
            ['name' => 'cuestionarios.completar', 'display_name' => 'Completar Cuestionario', 'module' => 'cuestionarios'],
            
            // Módulo: Empresas
            ['name' => 'empresas.ver', 'display_name' => 'Ver Empresas', 'module' => 'empresas'],
            ['name' => 'empresas.crear', 'display_name' => 'Crear Empresas', 'module' => 'empresas'],
            ['name' => 'empresas.editar', 'display_name' => 'Editar Empresas', 'module' => 'empresas'],
            ['name' => 'empresas.eliminar', 'display_name' => 'Eliminar Empresas', 'module' => 'empresas'],
            
            // Módulo: Usuarios
            ['name' => 'usuarios.ver', 'display_name' => 'Ver Usuarios', 'module' => 'usuarios'],
            ['name' => 'usuarios.crear', 'display_name' => 'Crear Usuarios', 'module' => 'usuarios'],
            ['name' => 'usuarios.editar', 'display_name' => 'Editar Usuarios', 'module' => 'usuarios'],
            ['name' => 'usuarios.eliminar', 'display_name' => 'Eliminar Usuarios', 'module' => 'usuarios'],
            
            // Módulo: Reportes
            ['name' => 'reportes.ver', 'display_name' => 'Ver Reportes', 'module' => 'reportes'],
            ['name' => 'reportes.generar', 'display_name' => 'Generar Reportes', 'module' => 'reportes'],
            
            // Módulo: Configuración
            ['name' => 'config.ver', 'display_name' => 'Ver Configuración', 'module' => 'config'],
            ['name' => 'config.editar', 'display_name' => 'Editar Configuración', 'module' => 'config'],

            // Módulo: Sedes (Fase 4)
            ['name' => 'sedes.ver', 'display_name' => 'Ver Sedes', 'module' => 'sedes'],
            ['name' => 'sedes.crear', 'display_name' => 'Crear Sedes', 'module' => 'sedes'],
            ['name' => 'sedes.editar', 'display_name' => 'Editar Sedes', 'module' => 'sedes'],
            ['name' => 'sedes.eliminar', 'display_name' => 'Eliminar Sedes', 'module' => 'sedes'],

            // Módulo: Finanzas (Fase 5)
            ['name' => 'finanzas.ver', 'display_name' => 'Ver Finanzas', 'module' => 'finanzas'],
            ['name' => 'finanzas.editar', 'display_name' => 'Editar Finanzas', 'module' => 'finanzas'],

            // Módulo: Calendario (Fase 6)
            ['name' => 'calendario.ver', 'display_name' => 'Ver Calendario', 'module' => 'calendario'],
            ['name' => 'calendario.editar', 'display_name' => 'Editar Calendario', 'module' => 'calendario'],

            // Módulo: Notificaciones (Fase 7)
            ['name' => 'notificaciones.ver', 'display_name' => 'Ver Notificaciones', 'module' => 'notificaciones'],
            ['name' => 'notificaciones.gestionar', 'display_name' => 'Gestionar Notificaciones', 'module' => 'notificaciones'],

            // Módulo: Documentos (Fase 8)
            ['name' => 'documentos.ver', 'display_name' => 'Ver Documentos', 'module' => 'documentos'],
            ['name' => 'documentos.subir', 'display_name' => 'Subir Documentos', 'module' => 'documentos'],
            ['name' => 'documentos.verificar', 'display_name' => 'Verificar Documentos', 'module' => 'documentos'],
            ['name' => 'documentos.eliminar', 'display_name' => 'Eliminar Documentos', 'module' => 'documentos'],

            // Módulo: Informe Preliminar (Fase 8)
            ['name' => 'informe_preliminar.ver', 'display_name' => 'Ver Informe Preliminar', 'module' => 'informe_preliminar'],
            ['name' => 'informe_preliminar.editar', 'display_name' => 'Editar Informe Preliminar', 'module' => 'informe_preliminar'],

            // Módulo: Observaciones (Fase 8)
            ['name' => 'observacion.ver', 'display_name' => 'Ver Observaciones', 'module' => 'observacion'],
            ['name' => 'observacion.editar', 'display_name' => 'Editar Observaciones', 'module' => 'observacion'],

            // Módulo: Historial DPI (Fase 8)
            ['name' => 'historial_dpi.ver', 'display_name' => 'Ver Historial DPI', 'module' => 'historial_dpi'],
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $permission['name']], // Condición de búsqueda
                array_merge($permission, [
                    'description' => null,
                    'created_at' => now(),
                    'updated_at' => now()
                ])
            );
        }

        // Asignar permisos a roles
        $this->assignPermissionsToRoles();
    }

    /**
     * Asignar permisos a cada rol
     */
    private function assignPermissionsToRoles(): void
    {
        // Admin: todos los permisos
        $adminRole = DB::table('roles')->where('name', 'admin')->first();
        $allPermissions = DB::table('permissions')->get();
        
        foreach ($allPermissions as $permission) {
            DB::table('role_permission')->updateOrInsert(
                [
                    'role_id' => $adminRole->id,
                    'permission_id' => $permission->id
                ],
                [
                    'role_id' => $adminRole->id,
                    'permission_id' => $permission->id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }

        // Repro: gestión de evaluaciones, resultados, ver empresas
        $reproRole = DB::table('roles')->where('name', 'repro')->first();
        $reproPermissions = [
            'ordenes.ver', 'ordenes.crear', 'ordenes.editar', 'ordenes.eliminar',
            'evaluaciones.ver', 'evaluaciones.crear', 'evaluaciones.editar', 'evaluaciones.realizar',
            'resultados.ver', 'resultados.descargar', 'resultados.editar',
            'cuestionarios.ver',
            'empresas.ver',
            'usuarios.ver',
            'reportes.ver', 'reportes.generar',
            'sedes.ver',
            'finanzas.ver',
            'calendario.ver', 'calendario.editar',
            'notificaciones.ver',
            'documentos.ver', 'documentos.subir', 'documentos.verificar',
            'informe_preliminar.ver', 'informe_preliminar.editar',
            'observacion.ver', 'observacion.editar',
            'historial_dpi.ver',
        ];
        
        $this->assignPermissionsByName($reproRole->id, $reproPermissions);

        // Empresa: crear órdenes, ver resultados propios
        $empresaRole = DB::table('roles')->where('name', 'empresa')->first();
        $empresaPermissions = [
            'ordenes.ver', 'ordenes.crear',
            'evaluaciones.ver',
            'resultados.ver', 'resultados.descargar',
            'usuarios.ver', 'usuarios.crear', 'usuarios.editar', // Sus propios usuarios
            'sedes.ver', 'sedes.crear', 'sedes.editar',
            'calendario.ver',
            'notificaciones.ver',
            'documentos.ver', 'documentos.subir',
            'informe_preliminar.ver',
        ];
        
        $this->assignPermissionsByName($empresaRole->id, $empresaPermissions);

        // NOTA: El rol 'evaluado' se elimina ya que los evaluados
        // no son usuarios del sistema, acceden vía token único
    }

    /**
     * Asignar permisos por nombre a un rol
     */
    private function assignPermissionsByName(int $roleId, array $permissionNames): void
    {
        foreach ($permissionNames as $permissionName) {
            $permission = DB::table('permissions')->where('name', $permissionName)->first();
            
            if ($permission) {
                DB::table('role_permission')->updateOrInsert(
                    [
                        'role_id' => $roleId,
                        'permission_id' => $permission->id
                    ],
                    [
                        'role_id' => $roleId,
                        'permission_id' => $permission->id,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                );
            }
        }
    }
}
