<?php

namespace Database\Seeders;

use App\Models\Departamento;
use App\Models\Municipio;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartamentosMunicipiosSeeder extends Seeder
{
    public function run(): void
    {
        if (Departamento::exists()) {
            return;
        }

        $catalogo = require database_path('data/guatemala_catalogo.php');

        DB::transaction(function () use ($catalogo) {
            foreach ($catalogo as $orden => $deptoData) {
                $departamento = Departamento::create([
                    'codigo' => $deptoData['codigo'],
                    'nombre' => $deptoData['nombre'],
                    'orden' => (int) $deptoData['codigo'],
                ]);

                foreach ($deptoData['municipios'] as $muniData) {
                    Municipio::create([
                        'departamento_id' => $departamento->id,
                        'codigo' => $muniData['codigo'],
                        'nombre' => $muniData['nombre'],
                    ]);
                }
            }
        });
    }
}
