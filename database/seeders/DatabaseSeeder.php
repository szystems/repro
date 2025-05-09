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
            ConfigsTableSeeder::class,
            EmpresasTableSeeder::class, // Primero creamos las empresas
            UsersTableSeeder::class,    // Luego los usuarios que se vinculan a empresas
        ]);
    }
}
