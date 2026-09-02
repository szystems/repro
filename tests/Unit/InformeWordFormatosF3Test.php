<?php

namespace Tests\Unit;

use App\Models\Cuestionario;
use App\Models\CuestionarioRespuesta;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Support\InformeWordExport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use ZipArchive;

/** Sprint F3 — reglas FORMATOS.pdf (periódica/específica). */
class InformeWordFormatosF3Test extends TestCase
{
    use RefreshDatabase;

    public function test_periodica_rellena_motivo_y_ultimo_grado_sin_pareja(): void
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'nombre' => 'Luis',
            'apellidos' => 'Periodico F3',
            'tipo_servicio' => 'poligrafo',
            'tipo_formulario' => 'periodica',
            'motivo_hecho_evaluacion' => 'Evaluación de confianza anual 2026',
        ]);

        $cuestionario = Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'periodica',
            'seccion_actual' => 5,
            'total_secciones' => 5,
            'progreso_porcentaje' => 100,
            'completado' => true,
        ]);

        CuestionarioRespuesta::guardarRespuestas($cuestionario->id, 'cambios_familiares', [
            'padre_nombre' => 'Padre Periodico',
            'padre_vive' => 'si',
            'padre_edad' => '60',
            'vive_con_pareja' => 'si',
            'pareja_nombre' => 'Pareja No Debe Aparecer',
            'pareja_edad' => '30',
            'pareja_telefono' => '55550000',
            'pareja_direccion' => 'Zona 1',
            'pareja_ocupacion' => 'Comercio',
            'pareja_tipo_relacion' => 'casado',
            'pareja_calidad_relacion' => 'buena',
            'pareja_tiempo_relacion' => '5 años',
            'pareja_trabaja' => 'si',
        ]);

        CuestionarioRespuesta::guardarTabla($cuestionario->id, 'situacion_laboral', 'formacion_academica', [
            [
                'nivel' => 'diversificado',
                'estado' => 'completo',
                'carrera' => 'Bachillerato',
                'institucion' => 'Instituto Demo',
                'anio' => '2015',
                'respaldo' => 'si',
            ],
            [
                'nivel' => 'universitario',
                'estado' => 'completo',
                'carrera' => 'Ingeniería',
                'institucion' => 'USAC',
                'anio' => '2020',
                'respaldo' => 'si',
            ],
        ]);

        CuestionarioRespuesta::guardarTabla($cuestionario->id, 'situacion_laboral', 'empleo_actual', [[
            'empresa' => 'Empresa Actual SA',
            'puesto' => 'Supervisor',
            'fechas_laboradas' => '01/2022 - Actual',
            'salario_actual' => '7500',
        ]]);

        $path = InformeWordExport::generar(
            $orden->fresh(),
            $evaluado->fresh(['cuestionario', 'orden.empresa', 'sede'])
        );

        $zip = new ZipArchive();
        $this->assertTrue($zip->open($path) === true);
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();
        @unlink($path);

        $this->assertIsString($xml);
        $this->assertStringContainsString('Evaluación de confianza anual 2026', $xml);
        $this->assertStringContainsString('USAC', $xml);
        $this->assertStringContainsString('Empresa Actual SA', $xml);
        $this->assertStringNotContainsString('Pareja No Debe Aparecer', $xml);
        $this->assertStringNotContainsString('Instituto Demo', $xml);
    }

    public function test_vsa_especifica_usa_plantilla_vsa(): void
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'nombre' => 'Ana',
            'apellidos' => 'Vsa Especifica',
            'tipo_servicio' => 'vsa',
            'tipo_formulario' => 'especifica',
            'motivo_hecho_evaluacion' => 'Investigación interna caso 15',
        ]);

        $path = InformeWordExport::generar($orden, $evaluado);
        $zip = new ZipArchive();
        $this->assertTrue($zip->open($path) === true);
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();
        @unlink($path);

        $this->assertStringContainsString('VOICE STRESS', $xml);
        $this->assertStringContainsString('Investigación interna caso 15', $xml);
        $this->assertStringContainsString('Ana', $xml);
        $this->assertStringNotContainsString('PUNTUACIÓN', mb_strtoupper($xml));
        $this->assertStringNotContainsString('PUNTUACION', mb_strtoupper($xml));
    }

    public function test_socio_rellena_refs_familiares_y_laborales(): void
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'nombre' => 'Mario',
            'apellidos' => 'Socio F3',
            'tipo_servicio' => 'socioeconomico',
            'tipo_formulario' => 'preempleo',
        ]);

        $cuestionario = Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'socioeconomico',
            'seccion_actual' => 6,
            'total_secciones' => 6,
            'progreso_porcentaje' => 100,
            'completado' => true,
        ]);

        $seccionSocio = 'informacion_socioeconomica_complementaria';
        CuestionarioRespuesta::guardarTabla($cuestionario->id, $seccionSocio, 'referencias_familiares', [[
            'nombre' => 'Tia Rosa Familiar',
            'direccion' => 'Zona 7',
            'telefono' => '55551111',
            'lugar_trabajo' => 'Comercio',
            'parentesco' => 'Tía',
        ]]);

        CuestionarioRespuesta::guardarTabla($cuestionario->id, $seccionSocio, 'referencias_laborales', [[
            'empresa' => 'Empresa Ref Laboral SA',
            'contacto' => 'Ing. Perez',
            'telefono' => '55552222',
            'puesto' => 'Analista',
        ]]);

        CuestionarioRespuesta::guardarTabla($cuestionario->id, $seccionSocio, 'presupuesto', [[
            'concepto' => 'Alimentación',
            'monto' => '1200',
        ]]);

        $path = InformeWordExport::generar(
            $orden->fresh(),
            $evaluado->fresh(['cuestionario', 'orden.empresa', 'sede'])
        );

        $zip = new ZipArchive();
        $this->assertTrue($zip->open($path) === true);
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();
        @unlink($path);

        $this->assertIsString($xml);
        $this->assertStringContainsString('Tia Rosa Familiar', $xml);
        $this->assertStringNotContainsString('Empresa Ref Laboral SA', $xml);
        $this->assertStringNotContainsString('Ing. Perez', $xml);
        $this->assertStringContainsString('1200', $xml);
    }
}
