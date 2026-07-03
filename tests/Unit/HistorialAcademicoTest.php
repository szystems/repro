<?php

namespace Tests\Unit;

use App\Support\HistorialAcademico;
use PHPUnit\Framework\TestCase;

class HistorialAcademicoTest extends TestCase
{
    public function test_niveles_visibles_hasta_universitario(): void
    {
        $this->assertSame(
            ['primaria', 'basico', 'diversificado', 'tecnico', 'universitario'],
            HistorialAcademico::nivelesVisibles('universitario')
        );
    }

    public function test_filas_para_formulario_genera_una_por_nivel_visible(): void
    {
        $filas = HistorialAcademico::filasParaFormulario('tecnico', [
            ['nivel' => 'primaria', 'estado' => 'completo', 'institucion' => 'Escuela A', 'anio' => '2000', 'respaldo' => 'si'],
        ]);

        $this->assertCount(4, $filas);
        $this->assertSame('primaria', $filas[0]['nivel']);
        $this->assertSame('Escuela A', $filas[0]['institucion']);
        $this->assertSame('tecnico', $filas[3]['nivel']);
    }

    public function test_filas_para_almacenamiento_solo_guarda_completas(): void
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
                'nivel' => 'universitario',
                'estado' => 'completo',
                'institucion' => 'Universidad B',
                'anio' => '2015',
                'respaldo' => 'no',
            ],
        ]);

        $this->assertCount(2, $guardadas);
        $this->assertSame('primaria', $guardadas[0]['nivel']);
        $this->assertSame('universitario', $guardadas[1]['nivel']);
    }
}
