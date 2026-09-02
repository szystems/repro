<?php

namespace Tests\Unit;

use App\Models\Cuestionario;
use App\Models\EvaluadorNota;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Support\InformeWordBloquesEvaluador;
use App\Support\InformeWordExport;
use App\Support\InformeWordXml;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use ZipArchive;

/** Sprint N N-L1 — peri/espe: aspecto laboral bajo historial; recomendaciones en observaciones adicionales. */
class InformeWordSprintNLoteLTest extends TestCase
{
    use RefreshDatabase;

    public function test_periodica_aspecto_laboral_bajo_historial_y_recomendaciones_al_final(): void
    {
        $xml = $this->xmlDe('vsa', 'periodica');

        $complementaria = $this->tabla($xml, 'INFORMACIÓN COMPLEMENTARIA');
        $observaciones = $this->tabla($xml, 'OBSERVACIONES ADICIONALES');
        $laboral = $this->tabla($xml, 'INFORMACIÓN LABORAL');

        $this->assertStringContainsString('PRUEBA DE ASPECTO LABORAL N-L1', $complementaria);
        $this->assertStringNotContainsString('RECOMENDACIONES PERIODICA N-L1', $complementaria);
        $this->assertStringContainsString('RECOMENDACIONES PERIODICA N-L1', $observaciones);
        $this->assertStringNotContainsString('PRUEBA DE ASPECTO LABORAL N-L1', $observaciones);
        $this->assertStringNotContainsString('PRUEBA DE ASPECTO LABORAL N-L1', $laboral);
        $this->assertNull(InformeWordXml::limitesTablaPorMarcador($xml, 'RECOMENDACIONES:'));
        $this->assertStringNotContainsString('RECOMENDACIONES PERIODICA N-L1', $laboral);
    }

    public function test_especifica_usa_el_mismo_mapeo_que_periodica(): void
    {
        $xml = $this->xmlDe('poligrafo', 'especifica');

        $this->assertStringContainsString(
            'PRUEBA DE ASPECTO LABORAL N-L1',
            $this->tabla($xml, 'INFORMACIÓN COMPLEMENTARIA')
        );
        $this->assertStringContainsString(
            'RECOMENDACIONES PERIODICA N-L1',
            $this->tabla($xml, 'OBSERVACIONES ADICIONALES')
        );
        $this->assertNull(InformeWordXml::limitesTablaPorMarcador($xml, 'RECOMENDACIONES:'));
    }

    public function test_preempleo_vuelca_word_laboral_en_aspecto_laboral_y_conserva_qa_complementaria(): void
    {
        $xml = $this->xmlDe('poligrafo', 'preempleo');
        $aspecto = $this->tabla($xml, 'ASPECTO LABORAL');
        $complementaria = $this->tabla($xml, 'INFORMACIÓN COMPLEMENTARIA');

        $this->assertStringContainsString('PRUEBA DE ASPECTO LABORAL N-L1', $aspecto);
        $this->assertStringNotContainsString('PRUEBA DE ASPECTO LABORAL N-L1', $complementaria);
        $this->assertStringContainsString('Licencia de conducir', $complementaria);
        $posLaboral = strpos($xml, 'INFORMACIÓN LABORAL');
        $posAspecto = strpos($xml, 'ASPECTO LABORAL');
        $posComplementaria = strpos($xml, 'INFORMACIÓN COMPLEMENTARIA');
        $posEconomico = strpos($xml, 'ASPECTO ECON');
        $this->assertNotFalse($posLaboral);
        $this->assertNotFalse($posAspecto);
        $this->assertNotFalse($posComplementaria);
        $this->assertNotFalse($posEconomico);
        $this->assertGreaterThan($posLaboral, $posAspecto);
        $this->assertGreaterThan($posAspecto, $posEconomico);
        $this->assertStringContainsString(
            'RECOMENDACIONES PERIODICA N-L1',
            $this->tabla($xml, 'RECOMENDACIONES')
        );
        $this->assertNull(InformeWordXml::limitesTablaPorMarcador($xml, 'OBSERVACIONES ADICIONALES'));
    }

    private function xmlDe(string $servicio, string $formulario): string
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_servicio' => $servicio,
            'tipo_formulario' => $formulario,
            'nombre' => 'UAT',
            'apellidos' => 'LoteL',
        ]);
        $tipoCuestionario = $formulario;
        $secciones = in_array($formulario, ['periodica', 'especifica'], true) ? 5 : 6;
        Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => $tipoCuestionario,
            'seccion_actual' => $secciones,
            'total_secciones' => $secciones,
            'completado' => true,
        ]);
        EvaluadorNota::guardarNota($evaluado->id, 'word_laboral', '', 'PRUEBA DE ASPECTO LABORAL N-L1', null);
        EvaluadorNota::guardarNota(
            $evaluado->id,
            InformeWordBloquesEvaluador::NOTA_RECOMENDACIONES,
            '',
            'RECOMENDACIONES PERIODICA N-L1',
            null
        );

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
        $this->assertNotNull($limites, "Falta la tabla {$marcador}");

        return InformeWordXml::textoTablaConcatenado(substr($xml, $limites[0], $limites[1] - $limites[0]));
    }
}
