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
            [
                'name' => 'evaluado',
                'display_name' => 'Persona Evaluada',
                'description' => 'Persona que completa cuestionario y realiza prueba'
            ],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->insert(array_merge($role, [
                'created_at' => now(),
                'updated_at' => now()
            ]));
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
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->insert(array_merge($permission, [
                'description' => null,
                'created_at' => now(),
                'updated_at' => now()
            ]));
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
            DB::table('role_permission')->insert([
                'role_id' => $adminRole->id,
                'permission_id' => $permission->id,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        // Repro: gestión de evaluaciones, resultados, ver empresas
        $reproRole = DB::table('roles')->where('name', 'repro')->first();
        $reproPermissions = [
            'ordenes.ver',
            'evaluaciones.ver', 'evaluaciones.crear', 'evaluaciones.editar', 'evaluaciones.realizar',
            'resultados.ver', 'resultados.descargar', 'resultados.editar',
            'cuestionarios.ver',
            'empresas.ver',
            'usuarios.ver',
            'reportes.ver', 'reportes.generar'
        ];
        
        $this->assignPermissionsByName($reproRole->id, $reproPermissions);

        // Empresa: crear órdenes, ver resultados propios
        $empresaRole = DB::table('roles')->where('name', 'empresa')->first();
        $empresaPermissions = [
            'ordenes.ver', 'ordenes.crear',
            'evaluaciones.ver',
            'resultados.ver', 'resultados.descargar',
            'usuarios.ver', 'usuarios.crear', 'usuarios.editar' // Sus propios usuarios
        ];
        
        $this->assignPermissionsByName($empresaRole->id, $empresaPermissions);

        // Evaluado: solo completar cuestionario
        $evaluadoRole = DB::table('roles')->where('name', 'evaluado')->first();
        $evaluadoPermissions = [
            'cuestionarios.completar'
        ];
        
        $this->assignPermissionsByName($evaluadoRole->id, $evaluadoPermissions);
    }

    /**
     * Asignar permisos por nombre a un rol
     */
    private function assignPermissionsByName(int $roleId, array $permissionNames): void
    {
        foreach ($permissionNames as $permissionName) {
            $permission = DB::table('permissions')->where('name', $permissionName)->first();
            
            if ($permission) {
                DB::table('role_permission')->insert([
                    'role_id' => $roleId,
                    'permission_id' => $permission->id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }
}
