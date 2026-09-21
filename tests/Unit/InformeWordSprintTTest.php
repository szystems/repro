<?php

namespace Tests\Unit;

use App\Models\Cuestionario;
use App\Models\CuestionarioRespuesta;
use App\Models\EvaluadorNota;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Support\InformeWordExport;
use App\Support\InformeWordXml;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use ZipArchive;

/** Sprint T — Stephany 20-sep-2026 (Word preempleo + preliminar). */
class InformeWordSprintTTest extends TestCase
{
    use RefreshDatabase;

    public function test_hijos_vacios_muestran_no_tiene(): void
    {
        $xml = $this->xmlPreempleoPoligrafo([], [
            ['nivel' => 'universitario', 'carrera' => 'Ing.', 'institucion' => 'USAC', 'anio' => '2020', 'tiene_constancia' => 'si'],
        ]);
        $tabla = $this->tabla($xml, 'HIJOS:');
        $this->assertStringContainsString('No tiene', $tabla);
        $this->assertStringNotContainsString('No aplica', $tabla);
    }

    public function test_ampliacion_laboral_renombra_y_queda_tras_informacion_laboral(): void
    {
        $xml = $this->xmlPreempleoPoligrafo([], [], 'Texto ampliación laboral Sprint T');
        $this->assertStringContainsString('AMPLIACIÓN DE INFORMACIÓN LABORAL', $xml);
        $this->assertStringNotContainsString('ASPECTO LABORAL:', $xml);

        $posLaboral = strpos($xml, 'INFORMACIÓN LABORAL');
        $posAmpliacion = strpos($xml, 'AMPLIACIÓN DE INFORMACIÓN LABORAL');
        $posComplementaria = strpos($xml, 'INFORMACIÓN COMPLEMENTARIA');
        $this->assertNotFalse($posLaboral);
        $this->assertNotFalse($posAmpliacion);
        $this->assertNotFalse($posComplementaria);
        $this->assertLessThan($posAmpliacion, $posLaboral);
        $this->assertLessThan($posComplementaria, $posAmpliacion);

        $limites = InformeWordXml::limitesTablaPorMarcador($xml, 'AMPLIACIÓN DE INFORMACIÓN LABORAL');
        $this->assertNotNull($limites);
        $this->assertStringContainsString(
            'Texto ampliación laboral Sprint T',
            InformeWordXml::textoTablaConcatenado(substr($xml, $limites[0], $limites[1] - $limites[0]))
        );
    }

    public function test_judicial_deja_espacio_antes_de_informacion_complementaria(): void
    {
        $xml = $this->xmlPreempleoPoligrafo([], [], '—', 'Narrativa judicial Sprint T');
        $limitesJudicial = InformeWordXml::limitesTablaPorMarcador($xml, 'ASPECTOS JUDICIALES');
        $limitesComplementaria = InformeWordXml::limitesTablaPorMarcador($xml, 'INFORMACIÓN COMPLEMENTARIA');
        $this->assertNotNull($limitesJudicial);
        $this->assertNotNull($limitesComplementaria);
        $this->assertLessThan($limitesComplementaria[0], $limitesJudicial[1]);
        $entre = substr($xml, $limitesJudicial[1], $limitesComplementaria[0] - $limitesJudicial[1]);
        $this->assertStringContainsString('w:spacing', $entre);
    }

    public function test_vsa_preempleo_tambien_usa_ampliacion_laboral(): void
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_servicio' => 'vsa',
            'tipo_formulario' => 'preempleo',
        ]);
        Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'preempleo',
            'seccion_actual' => 5,
            'total_secciones' => 5,
            'completado' => true,
        ]);
        EvaluadorNota::guardarNota($evaluado->id, 'word_laboral', '', 'VSA ampliación T', null);
        $path = InformeWordExport::generar($orden->fresh(), $evaluado->fresh(['cuestionario', 'orden.empresa', 'sede']));
        $zip = new ZipArchive();
        $this->assertTrue($zip->open($path) === true);
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();
        @unlink($path);
        $this->assertIsString($xml);
        $this->assertStringContainsString('AMPLIACIÓN DE INFORMACIÓN LABORAL', $xml);
        $this->assertStringContainsString('VSA ampliación T', $xml);
    }

    public function test_validacion_constancia_estudios_se_rellena_y_conserva_fila(): void
    {
        $xml = $this->xmlPreempleoPoligrafo([], [
            ['nivel' => 'universitario', 'carrera' => 'Derecho', 'institucion' => 'URL', 'anio' => '2019', 'tiene_constancia' => 'no'],
        ]);
        $tabla = $this->tabla($xml, 'NIVEL ACADÉMICO') ?: $this->tabla($xml, 'DATOS ACADÉMICOS');
        $this->assertStringContainsString('Validación de constancia', $tabla);
        $this->assertStringContainsString('No', $tabla);
    }

    /**
     * @param  list<array<string, string>>  $hijos
     * @param  list<array<string, string>>  $academico
     */
    private function xmlPreempleoPoligrafo(
        array $hijos,
        array $academico = [],
        string $wordLaboral = '—',
        string $wordJudicial = '—'
    ): string {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_servicio' => 'poligrafo',
            'tipo_formulario' => 'preempleo',
            'nombre' => 'Sprint',
            'apellidos' => 'T',
        ]);
        $cuestionario = Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'preempleo',
            'seccion_actual' => 5,
            'total_secciones' => 5,
            'completado' => true,
        ]);
        CuestionarioRespuesta::guardarTabla($cuestionario->id, 'informacion_familiar', 'hijos', $hijos);
        if ($academico !== []) {
            CuestionarioRespuesta::guardarTabla($cuestionario->id, 'historial_laboral', 'formacion_academica', $academico);
            CuestionarioRespuesta::guardarRespuestas($cuestionario->id, 'historial_laboral', [
                'ultimo_nivel_academico' => 'universitario',
            ]);
        }
        EvaluadorNota::guardarNota($evaluado->id, 'word_laboral', '', $wordLaboral, null);
        EvaluadorNota::guardarNota($evaluado->id, 'word_judicial', '', $wordJudicial, null);

        $path = InformeWordExport::generar($orden->fresh(), $evaluado->fresh(['cuestionario', 'orden.empresa', 'sede']));
        $zip = new ZipArchive();
        $this->assertTrue($zip->open($path) === true);
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();
        @unlink($path);
        $this->assertIsString($xml);

        return $xml;
    }

    private function tabla(string $xml, string $marcador): string
    {
        $limites = InformeWordXml::limitesTablaPorMarcador($xml, $marcador);
        $this->assertNotNull($limites, 'Tabla no encontrada: ' . $marcador);

        return substr($xml, $limites[0], $limites[1] - $limites[0]);
    }
}
