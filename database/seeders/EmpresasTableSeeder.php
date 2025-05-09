<?php

namespace Database\Seeders;

use App\Models\Empresa;
use Illuminate\Database\Seeder;

class EmpresasTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Crear empresas con datos específicos
        $empresas = [
            [
                'nombre' => 'Corporación ABC',
                'nit' => '1234567-8',
                'direccion' => 'Zona 10, Ciudad de Guatemala',
                'telefono' => '2233-4455',
                'email' => 'info@corporacionabc.com',
                'sitio_web' => 'https://www.corporacionabc.com',
                'descripcion' => 'Empresa líder en servicios financieros y corporativos',
                'contacto_nombre' => 'Juan Pérez',
                'contacto_cargo' => 'Gerente de RRHH',
                'contacto_telefono' => '5555-1234',
                'contacto_email' => 'jperez@corporacionabc.com',
                'estado' => 1,
            ],
            [
                'nombre' => 'Industrias XYZ',
                'nit' => '8765432-1',
                'direccion' => 'Zona 4, Guatemala City',
                'telefono' => '2456-7890',
                'email' => 'contacto@industriasxyz.com',
                'sitio_web' => 'https://www.industriasxyz.com',
                'descripcion' => 'Fabricación y distribución de productos industriales',
                'contacto_nombre' => 'María García',
                'contacto_cargo' => 'Directora de Personal',
                'contacto_telefono' => '5678-9012',
                'contacto_email' => 'mgarcia@industriasxyz.com',
                'estado' => 1,
            ],
            [
                'nombre' => 'Servicios Corporativos GT',
                'nit' => '5432167-9',
                'direccion' => 'Zona 14, Guatemala',
                'telefono' => '2345-6789',
                'email' => 'info@serviciosgt.com',
                'sitio_web' => 'https://www.serviciosgt.com',
                'descripcion' => 'Ofrecemos soluciones integrales para empresas',
                'contacto_nombre' => 'Roberto López',
                'contacto_cargo' => 'Gerente General',
                'contacto_telefono' => '4321-8765',
                'contacto_email' => 'rlopez@serviciosgt.com',
                'estado' => 1,
            ],
        ];

        foreach ($empresas as $empresa) {
            Empresa::create($empresa);
        }

        // Crear empresas adicionales con factory
        Empresa::factory()->count(7)->create();
    }
}
