<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;

class AssignRolesBasedOnRoleAsSeeder extends Seeder
{
    /**
     * Asignar roles a usuarios basado en el campo role_as
     */
    public function run(): void
    {
        $this->command->info('Asignando roles basado en role_as...');

        // Mapeo de role_as a nombres de roles
        $roleMapping = [
            0 => 'evaluado',    // Usuario evaluado
            1 => 'empresa',     // Usuario empresa  
            2 => 'repro',       // Usuario repro
            3 => 'admin',       // Administrador
        ];

        $stats = [
            'asignados' => 0,
            'ya_tenian_rol' => 0,
            'sin_rol_encontrado' => 0
        ];

        // Obtener todos los usuarios
        $usuarios = User::with('roles')->get();

        foreach ($usuarios as $usuario) {
            // Si el usuario ya tiene roles asignados, saltarlo
            if ($usuario->roles->count() > 0) {
                $stats['ya_tenian_rol']++;
                continue;
            }

            // Buscar el rol correspondiente al role_as
            $roleName = $roleMapping[$usuario->role_as] ?? null;
            
            if (!$roleName) {
                $this->command->warn("Usuario {$usuario->email} tiene role_as inválido: {$usuario->role_as}");
                $stats['sin_rol_encontrado']++;
                continue;
            }

            $role = Role::where('name', $roleName)->first();
            
            if (!$role) {
                $this->command->warn("Rol '{$roleName}' no encontrado para usuario {$usuario->email}");
                $stats['sin_rol_encontrado']++;
                continue;
            }

            // Asignar el rol
            $usuario->roles()->attach($role->id);
            $stats['asignados']++;
            
            $this->command->info("✅ Rol '{$role->display_name}' asignado a {$usuario->name} ({$usuario->email})");
        }

        // Mostrar estadísticas
        $this->command->info("\n📊 ESTADÍSTICAS DE ASIGNACIÓN:");
        $this->command->info("Usuarios con roles asignados: {$stats['asignados']}");
        $this->command->info("Usuarios que ya tenían rol: {$stats['ya_tenian_rol']}");
        $this->command->info("Usuarios sin rol válido: {$stats['sin_rol_encontrado']}");
        
        $total = $stats['asignados'] + $stats['ya_tenian_rol'] + $stats['sin_rol_encontrado'];
        $this->command->info("Total de usuarios procesados: {$total}");
    }
}