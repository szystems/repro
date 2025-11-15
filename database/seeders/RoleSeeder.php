<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;
use App\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear roles básicos
        $roles = [
            [
                'name' => 'admin', 
                'display_name' => 'Administrador', 
                'description' => 'Administrador del sistema con acceso completo'
            ],
            [
                'name' => 'repro', 
                'display_name' => 'REPRO', 
                'description' => 'Usuario de REPRO Guatemala - Polígrafo'
            ],
            [
                'name' => 'empresa', 
                'display_name' => 'Empresa', 
                'description' => 'Usuario de empresa cliente'
            ]
            // Rol 'evaluado' eliminado - Los evaluados acceden via token, no como usuarios
        ];

        foreach ($roles as $roleData) {
            Role::updateOrCreate(
                ['name' => $roleData['name']], 
                $roleData
            );
            $this->command->info("Rol creado/actualizado: {$roleData['display_name']}");
        }

        // Asignar roles a usuarios existentes
        $this->assignRolesToUsers();
    }

    /**
     * Asignar roles a usuarios específicos
     */
    private function assignRolesToUsers(): void
    {
        // Buscar usuarios por email y asignar roles
        $userRoleAssignments = [
            'szystems@hotmail.com' => 'admin',
            'admin@repro.com' => 'repro',
        ];

        foreach ($userRoleAssignments as $email => $roleName) {
            $user = User::where('email', $email)->first();
            $role = Role::where('name', $roleName)->first();
            
            if ($user && $role) {
                $user->roles()->syncWithoutDetaching([$role->id]);
                $this->command->info("Rol {$roleName} asignado a usuario: {$user->name}");
            }
        }

        // Asignar rol empresa a usuarios que tienen empresa_id
        $empresaRole = Role::where('name', 'empresa')->first();
        if ($empresaRole) {
            $usuariosEmpresa = User::whereNotNull('empresa_id')->whereDoesntHave('roles')->get();
            foreach ($usuariosEmpresa as $user) {
                $user->roles()->syncWithoutDetaching([$empresaRole->id]);
                $this->command->info("Rol empresa asignado a usuario: {$user->name}");
            }
        }

        // Crear un usuario admin adicional si no existe
        $adminUser = User::where('email', 'admin@sistema.com')->first();
        if (!$adminUser) {
            $adminUser = User::create([
                'name' => 'Sistema Admin',
                'email' => 'admin@sistema.com',
                'password' => bcrypt('password123'),
                'role_as' => 3,
                'estado' => 1,
            ]);

            $adminRole = Role::where('name', 'admin')->first();
            if ($adminRole) {
                $adminUser->roles()->attach($adminRole->id);
                $this->command->info("Usuario admin del sistema creado y rol asignado");
            }
        }
    }
}
