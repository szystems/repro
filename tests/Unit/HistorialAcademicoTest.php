<?php

namespace Tests\Unit;

use App\Support\HistorialAcademico;
use PHPUnit\Framework\TestCase;

class HistorialAcademicoTest extends TestCase
{
    public function test_niveles_visibles_siguen_la_regla_de_preempleo(): void
    {
        $this->assertSame(['primaria'], HistorialAcademico::nivelesVisibles('primaria'));
        $this->assertSame(['primaria', 'basico'], HistorialAcademico::nivelesVisibles('basico'));
        $this->assertSame(['basico', 'diversificado'], HistorialAcademico::nivelesVisibles('diversificado'));
        $this->assertSame(['diversificado', 'tecnico'], HistorialAcademico::nivelesVisibles('tecnico'));
        $this->assertSame(['diversificado', 'universitario'], HistorialAcademico::nivelesVisibles('universitario'));
        $this->assertSame(
            ['diversificado', 'universitario', 'postgrado'],
            HistorialAcademico::nivelesVisibles('postgrado')
        );
        $this->assertSame([], HistorialAcademico::nivelesVisibles('ninguno'));
        $this->assertSame(['universitario'], HistorialAcademico::nivelesVisibles('universitario', 'periodica'));
        $this->assertSame(['diversificado'], HistorialAcademico::nivelesVisibles('diversificado', 'especifica'));
        $this->assertStringContainsString('diversificado', HistorialAcademico::textoAyudaFilas('preempleo'));
        $this->assertSame(
            'Complete solo el último grado que seleccionó arriba.',
            HistorialAcademico::textoAyudaFilas('periodica')
        );
    }

    public function test_filas_para_formulario_genera_una_por_nivel_visible(): void
    {
        $filas = HistorialAcademico::filasParaFormulario('tecnico', [
            ['nivel' => 'diversificado', 'estado' => 'completo', 'institucion' => 'Instituto A', 'anio' => '2010', 'respaldo' => 'si'],
        ]);

        $this->assertSame(['diversificado', 'tecnico'], array_column($filas, 'nivel'));
        $this->assertSame('Instituto A', $filas[0]['institucion']);
        $this->assertSame('', $filas[1]['institucion']);
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

        $this->assertSame(['diversificado', 'universitario'], array_column($guardadas, 'nivel'));
        $this->assertSame('Instituto B', $guardadas[0]['institucion']);
        $this->assertSame('Universidad B', $guardadas[1]['institucion']);
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
