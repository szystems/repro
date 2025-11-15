<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Empresa;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Crear usuario administrador principal
        User::create([
            'name' => 'Otto Szarata',
            'email' => 'szystems@hotmail.com',
            'password' => Hash::make('SPP7007aaa@@@'),
            'role_as' => '3', // Ahora es administrador (3)
            'principal' => '1',
            'estado' => '1',
            'fecha_nacimiento' => '1985-05-15',
            'telefono' => '12345678',
            'celular' => '87654321',
            'direccion' => 'Ciudad de Guatemala',
            'cargo' => 'Administrador del Sistema',
        ]);
        
        // Crear otro usuario administrador
        User::create([
            'name' => 'Admin Repro',
            'email' => 'admin@repro.com',
            'password' => Hash::make('admin1234'),
            'role_as' => '3', // Administrador
            'principal' => '0',
            'estado' => '1',
            'fecha_nacimiento' => '1990-01-01',
            'telefono' => '22334455',
            'celular' => '55443322',
            'direccion' => 'Guatemala',
            'cargo' => 'Administrador de Oficina',
        ]);
        
        // Crear usuarios de Repro
        User::factory()->repro()->count(5)->create();
        
        // Crear usuarios de empresas
        User::factory()->empresa()->count(10)->create();
        
        // NOTA: Los usuarios evaluados NO se crean como usuarios del sistema
        // Los evaluados acceden vía token único en la tabla 'evaluados_orden'
        
        // Marcar algunos usuarios de empresa como principales
        $empresasIds = Empresa::pluck('id')->toArray();
        foreach ($empresasIds as $empresaId) {
            // Seleccionar un usuario de esta empresa
            $user = User::where('role_as', '1')
                ->where('empresa_id', $empresaId)
                ->inRandomOrder()
                ->first();
            
            if ($user) {
                $user->principal = 1;
                $user->save();
            }
        }
    }
}
