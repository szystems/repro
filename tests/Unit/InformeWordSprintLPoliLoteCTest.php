<?php

namespace Tests\Unit;

use App\Models\Cuestionario;
use App\Models\CuestionarioRespuesta;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Support\InformePreempleo;
use App\Support\InformeWordExport;
use App\Support\InformeWordXml;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use ZipArchive;

/** Sprint L lote C — polígrafo específico/periódica (Angeles / PRUEBA 1). */
class InformeWordSprintLPoliLoteCTest extends TestCase
{
    use RefreshDatabase;

    public function test_tablas_informe_aplican_a_especifica_y_periodica(): void
    {
        $this->assertTrue(InformePreempleo::aplicaATipo('especifica'));
        $this->assertTrue(InformePreempleo::aplicaATipo('periodica'));
        $this->assertArrayHasKey('tatuajes', InformePreempleo::clavesTablas('especifica'));
        $this->assertArrayNotHasKey('labor_complementaria', InformePreempleo::clavesTablas('especifica'));
        $this->assertArrayHasKey('estudios_actuales', InformePreempleo::clavesTablas('periodica'));
        $this->assertArrayNotHasKey('estudios_actuales', InformePreempleo::clavesTablas('especifica'));
    }

    public function test_especifica_traslada_estado_civil_academico_laboral_y_complementaria(): void
    {
        $xml = $this->xmlEspecificaLoteC();

        $tablaEstado = $this->tabla($xml, 'ESTADO CIVIL');
        $this->assertStringContainsString('Mario Pareja LoteC', $tablaEstado);
        $this->assertStringContainsString('35 años', $tablaEstado);

        $tablaAcademica = $this->tabla($xml, 'NIVEL ACADÉMICO');
        $this->assertStringContainsString('Universitario', $tablaAcademica);
        $filas = InformeWordXml::filasTabla($tablaAcademica);
        $this->assertNotEmpty($filas[1] ?? null);
        $celdas = InformeWordXml::celdasFila($filas[1]);
        $this->assertGreaterThanOrEqual(2, count($celdas));
        $this->assertStringContainsString('w:gridSpan w:val="3"', $celdas[1] ?? '');

        $tablaLaboral = $this->tabla($xml, 'INFORMACIÓN LABORAL');
        $this->assertStringContainsString('REPRO LoteC', $tablaLaboral);
        $this->assertStringContainsString('Cajero LoteC', $tablaLaboral);

        $this->assertNotNull(
            InformeWordXml::limitesTablaPorMarcador($xml, 'INFORMACIÓN COMPLEMENTARIA'),
            'N-L1: peri/espe conservan el recuadro para aspecto laboral'
        );

        $tablaTatuajes = $this->tabla($xml, 'TATUAJES');
        $this->assertStringContainsString('Brazo LoteC', $tablaTatuajes);
    }

    public function test_periodica_tambien_traslada_pareja_nivel_y_complementaria(): void
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_servicio' => 'poligrafo',
            'tipo_formulario' => 'periodica',
            'nombre' => 'Angeles',
            'apellidos' => 'Periodica LoteC',
        ]);
        $cuestionario = Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'periodica',
            'seccion_actual' => 5,
            'total_secciones' => 5,
            'completado' => true,
        ]);

        CuestionarioRespuesta::guardarRespuestas($cuestionario->id, 'cambios_familiares', [
            'vive_con_pareja' => 'si',
            'pareja_nombre' => 'Pareja Periodica LoteC',
            'pareja_edad' => '40',
        ]);
        CuestionarioRespuesta::guardarRespuestas($cuestionario->id, 'situacion_laboral', [
            'ultimo_nivel_academico' => 'diversificado',
            'periodico_01' => 'Motivo periódico LoteC prueba',
        ]);

        $xml = $this->xmlDe($orden, $evaluado);
        $this->assertStringContainsString('Pareja Periodica LoteC', $this->tabla($xml, 'ESTADO CIVIL'));
        $this->assertStringContainsString('Diversificado', $this->tabla($xml, 'NIVEL ACADÉMICO'));
        $this->assertNotNull(InformeWordXml::limitesTablaPorMarcador($xml, 'INFORMACIÓN COMPLEMENTARIA'));
        $this->assertStringNotContainsString('Motivo periódico LoteC prueba', InformeWordXml::textoTablaConcatenado($this->tabla($xml, 'INFORMACIÓN LABORAL')));
    }

    public function test_combinar_celdas_deja_el_texto_en_la_primera(): void
    {
        $fila = '<w:tr>'
            .'<w:tc><w:p><w:r><w:t>Ultimo grado cursado:</w:t></w:r></w:p></w:tc>'
            .'<w:tc><w:p><w:r><w:t></w:t></w:r></w:p></w:tc>'
            .'<w:tc><w:p><w:r><w:t></w:t></w:r></w:p></w:tc>'
            .'<w:tc><w:p><w:r><w:t></w:t></w:r></w:p></w:tc>'
            .'</w:tr>';

        $combinada = InformeWordXml::combinarCeldasFila($fila, 1, 3, ['Universitario']);
        $celdas = InformeWordXml::celdasFila($combinada);
        $this->assertCount(2, $celdas);
        $this->assertStringContainsString('Universitario', InformeWordXml::textoCelda($celdas[1]));
        $this->assertStringContainsString('w:gridSpan w:val="3"', $celdas[1]);
    }

    private function xmlEspecificaLoteC(): string
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_servicio' => 'poligrafo',
            'tipo_formulario' => 'especifica',
            'nombre' => 'Angeles',
            'apellidos' => 'Villagrán',
            'motivo_hecho_evaluacion' => 'Investigación por desaparición de Q1000',
        ]);
        $cuestionario = Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'especifica',
            'seccion_actual' => 5,
            'total_secciones' => 5,
            'completado' => true,
        ]);

        CuestionarioRespuesta::guardarRespuestas($cuestionario->id, 'informacion_familiar', [
            'vive_con_pareja' => 'si',
            'pareja_tipo_relacion' => 'casado',
            'pareja_nombre' => 'Mario Pareja LoteC',
            'pareja_edad' => '35',
            'pareja_telefono' => '42073047',
            'pareja_direccion' => 'Zona 6',
            'pareja_ocupacion' => 'Medico',
            'pareja_tiempo_relacion' => '8 años',
            'pareja_calidad_relacion' => 'buena',
        ]);
        CuestionarioRespuesta::guardarRespuestas($cuestionario->id, 'situacion_laboral', [
            'ultimo_nivel_academico' => 'universitario',
            'tiene_empleo_actual' => 'si',
            'periodico_01' => 'Desaparición de Q1000 LoteC',
        ]);
        CuestionarioRespuesta::guardarTabla($cuestionario->id, 'situacion_laboral', 'empleo_actual', [[
            'empresa' => 'REPRO LoteC',
            'puesto' => 'Cajero LoteC',
            'fechas_laboradas' => '01/2026 al Actual',
            'salario_actual' => '5000',
        ]]);
        CuestionarioRespuesta::guardarRespuestas($cuestionario->id, 'antecedentes_relevantes', [
            'tiene_tatuajes' => 'si',
        ]);
        CuestionarioRespuesta::guardarTabla($cuestionario->id, 'antecedentes_relevantes', 'tatuajes', [[
            'ubicacion' => 'Brazo LoteC',
            'tamano' => '10 cm',
            'descripcion' => 'Flor',
            'tiempo' => '2 años',
            'visible_uniforme' => 'no',
            'significado' => '',
        ]]);

        return $this->xmlDe($orden, $evaluado);
    }

    private function tabla(string $xml, string $marcador): string
    {
        $limites = InformeWordXml::limitesTablaPorMarcador($xml, $marcador);
        $this->assertNotNull($limites, "Falta la tabla {$marcador}");

        return substr($xml, $limites[0], $limites[1] - $limites[0]);
    }

    private function xmlDe(Orden $orden, EvaluadoOrden $evaluado): string
    {
        $path = InformeWordExport::generar($orden->fresh(), $evaluado->fresh(['cuestionario', 'orden.empresa', 'sede']));
        $zip = new ZipArchive();
        $this->assertTrue($zip->open($path) === true);
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();
        @unlink($path);
        $this->assertIsString($xml);

        return $xml;
    }
}
