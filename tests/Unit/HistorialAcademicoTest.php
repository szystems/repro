<?php

namespace Tests\Unit;

use App\Support\HistorialAcademico;
use PHPUnit\Framework\TestCase;

class HistorialAcademicoTest extends TestCase
{
    public function test_niveles_visibles_ultimos_segun_tabla_cliente(): void
    {
        $this->assertSame(['primaria'], HistorialAcademico::nivelesVisibles('primaria'));
        $this->assertSame(['basico'], HistorialAcademico::nivelesVisibles('basico'));
        $this->assertSame(['diversificado'], HistorialAcademico::nivelesVisibles('diversificado'));
        $this->assertSame(['tecnico'], HistorialAcademico::nivelesVisibles('tecnico'));
        $this->assertSame(['universitario'], HistorialAcademico::nivelesVisibles('universitario'));
        $this->assertSame(['postgrado'], HistorialAcademico::nivelesVisibles('postgrado'));
        $this->assertSame([], HistorialAcademico::nivelesVisibles('ninguno'));
        $this->assertSame(
            HistorialAcademico::mapaNivelesVisibles()['universitario'],
            HistorialAcademico::nivelesVisibles('universitario')
        );
    }

    public function test_filas_para_formulario_genera_una_por_nivel_visible(): void
    {
        $filas = HistorialAcademico::filasParaFormulario('tecnico', [
            ['nivel' => 'diversificado', 'estado' => 'completo', 'institucion' => 'Instituto A', 'anio' => '2010', 'respaldo' => 'si'],
        ]);

        $this->assertCount(1, $filas);
        $this->assertSame('tecnico', $filas[0]['nivel']);
        $this->assertSame('', $filas[0]['institucion']);
    }

    public function test_filas_para_almacenamiento_solo_guarda_completas_visibles(): void
    {
        $guardadas = HistorialAcademico::filasParaAlmacenamiento('universitario', [
            [
                'nivel' => 'primaria',
                'estado' => 'completo',
                'institucion' => 'Escuela A',
                'anio' => '2000',
                'respaldo' => 'si',
            ],
            [
                'nivel' => 'diversificado',
                'estado' => 'completo',
                'institucion' => 'Instituto B',
                'anio' => '2010',
                'respaldo' => 'si',
            ],
            [
                'nivel' => 'universitario',
                'estado' => 'completo',
                'institucion' => 'Universidad B',
                'anio' => '2015',
                'respaldo' => 'no',
            ],
        ]);

        $this->assertCount(1, $guardadas);
        $this->assertSame('universitario', $guardadas[0]['nivel']);
        $this->assertSame('Universidad B', $guardadas[0]['institucion']);
    }
}
