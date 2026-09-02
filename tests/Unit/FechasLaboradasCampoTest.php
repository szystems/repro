<?php

namespace Tests\Unit;

use App\Support\FechasLaboradasCampo;
use App\Support\TablaDinamica;
use Tests\TestCase;

class FechasLaboradasCampoTest extends TestCase
{
    public function test_parse_rango_iso(): void
    {
        $parsed = FechasLaboradasCampo::parse('2018-01-15 al 2022-06-30');

        $this->assertSame('2018-01', $parsed['inicio']);
        $this->assertSame('2022-06', $parsed['fin']);
        $this->assertFalse($parsed['actual']);
    }

    public function test_parse_rango_mes_anio(): void
    {
        $parsed = FechasLaboradasCampo::parse('01/2018 al 06/2022');

        $this->assertSame('2018-01', $parsed['inicio']);
        $this->assertSame('2022-06', $parsed['fin']);
    }

    public function test_parse_rango_dd_mm_yyyy(): void
    {
        $parsed = FechasLaboradasCampo::parse('10/12/2000 al 12/10/2001');

        $this->assertSame('2000-12', $parsed['inicio']);
        $this->assertSame('2001-10', $parsed['fin']);
    }

    public function test_parse_actual(): void
    {
        $parsed = FechasLaboradasCampo::parse('2019-03-01 al Actual');

        $this->assertSame('2019-03', $parsed['inicio']);
        $this->assertSame('', $parsed['fin']);
        $this->assertTrue($parsed['actual']);
    }

    public function test_combinar_desde_formulario_mes_anio(): void
    {
        $valor = FechasLaboradasCampo::combinarDesdeFormulario([
            'fechas_laboradas_inicio' => '2018-01',
            'fechas_laboradas_fin' => '2022-06',
        ]);

        $this->assertSame('01/2018 al 06/2022', $valor);
    }

    public function test_combinar_desde_formulario_rango_fecha_completa_legacy(): void
    {
        $valor = FechasLaboradasCampo::combinarDesdeFormulario([
            'fechas_laboradas_inicio' => '2018-01-15',
            'fechas_laboradas_fin' => '2022-06-30',
        ]);

        $this->assertSame('15/01/2018 al 30/06/2022', $valor);
    }

    public function test_combinar_desde_formulario_actual(): void
    {
        $valor = FechasLaboradasCampo::combinarDesdeFormulario([
            'fechas_laboradas_inicio' => '2019-03',
            'fechas_laboradas_actual' => '1',
        ]);

        $this->assertSame('03/2019 al Actual', $valor);
    }

    public function test_normalizar_filas_empleos_desde_selectores_mes(): void
    {
        $columnas = TablaDinamica::columnasEmpleosPreempleo();

        $filas = TablaDinamica::normalizarFilas([
            [
                'empresa' => 'Empresa S.A.',
                'puesto' => 'Analista',
                'fechas_laboradas_inicio' => '2000-12',
                'fechas_laboradas_fin' => '2001-10',
                'ultimo_salario' => '3000',
                'motivo_retiro' => 'Fin de contrato',
            ],
        ], $columnas);

        $this->assertSame('12/2000 al 10/2001', $filas[0]['fechas_laboradas']);
    }

    public function test_formatear_para_informe_convierte_guion_a_al(): void
    {
        $this->assertSame('01/2020 al 12/2022', FechasLaboradasCampo::formatearParaInforme('01/2020 - 12/2022'));
        $this->assertSame('03/2019 al Actual', FechasLaboradasCampo::formatearParaInforme('03/2019 al Actual'));
    }
}
