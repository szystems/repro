<?php

namespace Tests\Unit;

use App\Support\TablaDinamica;
use Tests\TestCase;

class TablaDinamicaTest extends TestCase
{
    public function test_normalizar_filas_ignora_filas_vacias(): void
    {
        $columnas = TablaDinamica::columnasHijos();

        $filas = TablaDinamica::normalizarFilas([
            ['nombre' => 'Ana', 'edad' => '10', 'vive_con_candidato' => 'si', 'ocupacion' => '', 'telefono' => ''],
            ['nombre' => '', 'edad' => '', 'vive_con_candidato' => '', 'ocupacion' => '', 'telefono' => ''],
        ], $columnas);

        $this->assertCount(1, $filas);
        $this->assertSame('Ana', $filas[0]['nombre']);
    }

    public function test_extraer_tablas_separa_campos_simples(): void
    {
        $datos = [
            'tiene_hijos' => 'si',
            'hijos' => [
                ['nombre' => 'Luis', 'edad' => '8', 'vive_con_candidato' => 'si'],
            ],
        ];

        $tablas = TablaDinamica::extraerTablas($datos, 2, 'preempleo');

        $this->assertArrayHasKey('hijos', $tablas);
        $this->assertArrayNotHasKey('hijos', $datos);
        $this->assertSame('si', $datos['tiene_hijos']);
    }

    public function test_reglas_validacion_incluyen_hijos_cuando_aplica(): void
    {
        $reglas = TablaDinamica::reglasValidacion(2, 'preempleo');

        $this->assertArrayHasKey('hijos', $reglas);
        $this->assertArrayHasKey('hijos.*.nombre', $reglas);
    }
}
