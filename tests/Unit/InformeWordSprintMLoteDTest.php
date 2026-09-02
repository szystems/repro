<?php

namespace Tests\Unit;

use App\Models\Cuestionario;
use App\Models\EvaluadorNota;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Support\InformeWordExport;
use App\Support\InformeWordXml;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use ZipArchive;

/** Sprint M M-P6 — quitar información complementaria laboral; dejar historial. */
class InformeWordSprintMLoteDTest extends TestCase
{
    use RefreshDatabase;

    public function test_eliminar_tabla_por_marcador_deja_el_resto(): void
    {
        $xml = '<w:document><w:body>'
            .'<w:tbl><w:tr><w:tc><w:p><w:r><w:t>INFORMACIÓN LABORAL:</w:t></w:r></w:p></w:tc></w:tr></w:tbl>'
            .'<w:tbl><w:tr><w:tc><w:p><w:r><w:t>INFORMACIÓN LABORAL COMPLEMENTARIA:</w:t></w:r></w:p></w:tc></w:tr></w:tbl>'
            .'<w:tbl><w:tr><w:tc><w:p><w:r><w:t>ASPECTO ECONÓMICO:</w:t></w:r></w:p></w:tc></w:tr></w:tbl>'
            .'</w:body></w:document>';

        $sin = InformeWordXml::eliminarTablaPorMarcador($xml, 'INFORMACIÓN LABORAL COMPLEMENTARIA');

        $this->assertStringContainsString('INFORMACIÓN LABORAL:', $sin);
        $this->assertStringContainsString('ASPECTO ECONÓMICO:', $sin);
        $this->assertStringNotContainsString('INFORMACIÓN LABORAL COMPLEMENTARIA', $sin);
        $this->assertSame(2, substr_count($sin, '<w:tbl>'));
    }

    public function test_preempleo_quita_laboral_complementaria_y_conserva_historial_y_licencia(): void
    {
        $xml = $this->xmlDe('poligrafo', 'preempleo', 'Observación laboral no debe salir M-P6');

        $this->assertNotNull(InformeWordXml::limitesTablaPorMarcador($xml, 'INFORMACIÓN LABORAL'));
        $this->assertNull(InformeWordXml::limitesTablaPorMarcador($xml, 'INFORMACIÓN LABORAL COMPLEMENTARIA'));
        $limitesQa = InformeWordXml::limitesTablaPorMarcador($xml, 'INFORMACIÓN COMPLEMENTARIA:');
        $this->assertNotNull($limitesQa);
        $this->assertStringContainsString(
            'Licencia de conducir',
            InformeWordXml::textoTablaConcatenado(substr($xml, $limitesQa[0], $limitesQa[1] - $limitesQa[0]))
        );
        $limitesAspecto = InformeWordXml::limitesTablaPorMarcador($xml, 'ASPECTO LABORAL');
        $this->assertNotNull($limitesAspecto);
        $this->assertStringContainsString(
            'Observación laboral no debe salir M-P6',
            InformeWordXml::textoTablaConcatenado(substr($xml, $limitesAspecto[0], $limitesAspecto[1] - $limitesAspecto[0]))
        );
    }

    public function test_periodica_vuelca_word_laboral_en_informacion_complementaria(): void
    {
        $xml = $this->xmlDe('vsa', 'periodica', 'Borrador laboral peri M-P6');

        $this->assertNotNull(InformeWordXml::limitesTablaPorMarcador($xml, 'INFORMACIÓN LABORAL'));
        $this->assertNotNull(InformeWordXml::limitesTablaPorMarcador($xml, 'INFORMACIÓN COMPLEMENTARIA'));
        $this->assertStringContainsString('Borrador laboral peri M-P6', InformeWordXml::textoTablaConcatenado($xml));
    }

    public function test_socio_vuelca_aspecto_laboral_en_recuadro_bajo_empleos(): void
    {
        $xml = $this->xmlDe('socioeconomico', 'preempleo', 'Borrador laboral socio M-P6');

        $this->assertNotNull(InformeWordXml::limitesTablaPorMarcador($xml, 'EMPLEOS:'));
        $limites = InformeWordXml::limitesTablaPorMarcador($xml, 'INFORMACIÓN COMPLEMENTARIA LABORAL');
        $this->assertNotNull($limites);
        $tabla = substr($xml, $limites[0], $limites[1] - $limites[0]);
        $plano = InformeWordXml::textoTablaConcatenado($tabla);
        $this->assertStringContainsString('Borrador laboral socio M-P6', $plano);
        $this->assertStringNotContainsString('Licencia de conducir', $plano);
    }

    private function xmlDe(string $servicio, string $formulario, string $wordLaboral): string
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_servicio' => $servicio,
            'tipo_formulario' => $formulario,
            'nombre' => 'UAT',
            'apellidos' => 'LoteD',
        ]);
        $tipoCuestionario = $servicio === 'socioeconomico' ? 'socioeconomico' : $formulario;
        Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => $tipoCuestionario,
            'seccion_actual' => $tipoCuestionario === 'socioeconomico' ? 6 : 5,
            'total_secciones' => $tipoCuestionario === 'socioeconomico' ? 6 : 5,
            'completado' => true,
        ]);
        EvaluadorNota::guardarNota($evaluado->id, 'word_laboral', '', $wordLaboral, null);

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
