<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            RolesAndPermissionsSeeder::class, // Primero crear roles y permisos
            ConfigsTableSeeder::class,
            EmpresasTableSeeder::class, // Luego creamos las empresas
            UsersTableSeeder::class,    // Luego los usuarios que se vinculan a empresas
            RoleSeeder::class,          // Finalmente asignar roles a usuarios específicos
        ]);
    }
}
