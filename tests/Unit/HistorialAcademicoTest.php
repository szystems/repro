<?php

namespace Tests\Unit;

use App\Support\HistorialAcademico;
use PHPUnit\Framework\TestCase;

class HistorialAcademicoTest extends TestCase
{
    public function test_niveles_visibles_incluyen_los_anteriores_hasta_el_ultimo(): void
    {
        $this->assertSame(['primaria'], HistorialAcademico::nivelesVisibles('primaria'));
        $this->assertSame(['primaria', 'basico'], HistorialAcademico::nivelesVisibles('basico'));
        $this->assertSame(
            ['primaria', 'basico', 'diversificado'],
            HistorialAcademico::nivelesVisibles('diversificado')
        );
        $this->assertSame(
            ['primaria', 'basico', 'diversificado', 'tecnico'],
            HistorialAcademico::nivelesVisibles('tecnico')
        );
        $this->assertSame(
            ['primaria', 'basico', 'diversificado', 'tecnico', 'universitario'],
            HistorialAcademico::nivelesVisibles('universitario')
        );
        $this->assertSame(array_keys(HistorialAcademico::NIVELES), HistorialAcademico::nivelesVisibles('postgrado'));
        $this->assertSame([], HistorialAcademico::nivelesVisibles('ninguno'));
        $this->assertSame(
            HistorialAcademico::mapaNivelesVisibles()['universitario'],
            HistorialAcademico::nivelesVisibles('universitario')
        );
        $this->assertStringContainsString('diversificado', HistorialAcademico::textoAyudaFilas());
    }

    public function test_filas_para_formulario_genera_una_por_nivel_visible(): void
    {
        $filas = HistorialAcademico::filasParaFormulario('tecnico', [
            ['nivel' => 'diversificado', 'estado' => 'completo', 'institucion' => 'Instituto A', 'anio' => '2010', 'respaldo' => 'si'],
        ]);

        $this->assertSame(['primaria', 'basico', 'diversificado', 'tecnico'], array_column($filas, 'nivel'));
        $this->assertSame('Instituto A', $filas[2]['institucion']);
        $this->assertSame('', $filas[3]['institucion']);
    }

    public function test_filas_para_almacenamiento_guarda_cada_nivel_completo(): void
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
                'nivel' => 'basico',
                'estado' => '',
                'institucion' => '',
                'anio' => '',
                'respaldo' => '',
            ],
            [
                'nivel' => 'universitario',
                'estado' => 'completo',
                'institucion' => 'Universidad B',
                'anio' => '2015',
                'respaldo' => 'no',
            ],
        ]);

        $this->assertSame(['primaria', 'diversificado', 'universitario'], array_column($guardadas, 'nivel'));
        $this->assertSame('Instituto B', $guardadas[1]['institucion']);
        $this->assertSame('Universidad B', $guardadas[2]['institucion']);
    }

    public function test_validacion_no_exige_niveles_anteriores_en_blanco(): void
    {
        $filas = HistorialAcademico::filasParaValidacion('universitario', [
            [
                'nivel' => 'diversificado',
                'estado' => 'completo',
                'carrera' => 'Bachillerato',
                'institucion' => 'Instituto B',
                'anio' => '2010',
                'respaldo' => 'si',
            ],
            [
                'nivel' => 'universitario',
                'estado' => 'completo',
                'carrera' => 'Administración',
                'institucion' => 'Universidad B',
                'anio' => '2015',
                'respaldo' => 'si',
            ],
        ]);

        $this->assertSame(['diversificado', 'universitario'], array_column($filas, 'nivel'));

        $soloUltimo = HistorialAcademico::filasParaValidacion('universitario', [
            [
                'nivel' => 'universitario',
                'estado' => 'completo',
                'institucion' => 'Universidad B',
                'anio' => '2015',
                'respaldo' => 'si',
            ],
        ]);

        $this->assertSame(['universitario'], array_column($soloUltimo, 'nivel'));
    }
}
