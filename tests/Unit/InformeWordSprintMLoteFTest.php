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

/** Sprint M lote F — cosmético M-P7 espacio económico, M-S4 salud combinada, M-S5 negrita amistades. */
class InformeWordSprintMLoteFTest extends TestCase
{
    use RefreshDatabase;

    public function test_expandir_no_engorda_la_columna_fantasma(): void
    {
        $tabla = '<w:tbl><w:tblPr><w:tblW w:w="5000" w:type="dxa"/></w:tblPr>'
            .'<w:tblGrid><w:gridCol w:w="2000"/><w:gridCol w:w="2000"/><w:gridCol w:w="22"/></w:tblGrid>'
            .'<w:tr><w:tc><w:p><w:r><w:t>ASPECTO ECONÓMICO:</w:t></w:r></w:p></w:tc></w:tr></w:tbl>';

        $out = InformeWordXml::expandirTablaAnchoPagina($tabla, 10000);
        preg_match('/<w:tblGrid>(.*?)<\/w:tblGrid>/s', $out, $m);
        preg_match_all('/w:w="(\d+)"/', $m[1] ?? '', $anchos);
        $cols = array_map('intval', $anchos[1] ?? []);

        $this->assertCount(3, $cols);
        $this->assertSame(22, $cols[2], 'La col. fantasma debe quedar en 22 dxa');
        $this->assertSame(10000, array_sum($cols));
        $this->assertGreaterThan(4000, $cols[0]);
        $this->assertGreaterThan(4000, $cols[1]);
    }

    public function test_extender_filas_de_una_celda_cubre_todas_las_columnas(): void
    {
        $tabla = '<w:tbl><w:tblGrid><w:gridCol w:w="1000"/><w:gridCol w:w="1000"/><w:gridCol w:w="22"/></w:tblGrid>'
            .'<w:tr><w:tc><w:tcPr><w:gridSpan w:val="8"/><w:tcW w:w="2000" w:type="dxa"/></w:tcPr>'
            .'<w:p><w:r><w:t>xxxxx</w:t></w:r></w:p></w:tc></w:tr></w:tbl>';

        $out = InformeWordXml::extenderFilasDeUnaCeldaAlGrid($tabla);

        $this->assertStringContainsString('w:gridSpan w:val="3"', $out);
        $this->assertStringContainsString('w:w="2022"', $out);
    }

    public function test_salud_socio_usa_fila_combinada(): void
    {
        $xml = $this->xmlSocio();
        $tabla = $this->tabla($xml, 'ASPECTOS DE SALUD');
        $filas = InformeWordXml::filasTabla($tabla);
        $ultima = $filas[array_key_last($filas)] ?? '';

        $this->assertStringContainsString('Observaciones:', $ultima);
        $this->assertStringContainsString('Narrativa salud LoteF', $ultima);
        $this->assertStringContainsString('gridSpan', $ultima);
        $this->assertSame(1, count(InformeWordXml::celdasFila($ultima)));
        $this->assertStringContainsString('Estado general de salud', $tabla);
    }

    public function test_encabezado_amistades_queda_en_negrita(): void
    {
        $xml = $this->xmlSocio();
        $tabla = $this->tabla($xml, 'AMISTADES:');
        $filas = InformeWordXml::filasTabla($tabla);
        $this->assertGreaterThanOrEqual(2, count($filas));
        $encabezado = $filas[1];
        $this->assertStringContainsString('Nombre:', $encabezado);
        $this->assertStringContainsString('Años de conocerlo', $encabezado);
        $this->assertTrue(
            str_contains($encabezado, '<w:b/>') || str_contains($encabezado, '<w:b '),
            'El encabezado de amistades debe ir en negrita'
        );
    }

    public function test_poli_economico_no_engorda_columna_fantasma(): void
    {
        $xml = $this->xmlPoli();
        $tabla = $this->tabla($xml, 'ASPECTO ECONÓMICO');
        preg_match('/<w:tblGrid>(.*?)<\/w:tblGrid>/s', $tabla, $m);
        preg_match_all('/w:w="(\d+)"/', $m[1] ?? '', $anchos);
        $cols = array_map('intval', $anchos[1] ?? []);
        $this->assertNotEmpty($cols);
        $ultima = $cols[array_key_last($cols)];
        $this->assertLessThan(50, $ultima, 'La col. fantasma de 22 dxa no debe absorber el ancho');
        $this->assertStringContainsString('ASPECTOS DE SALUD', $xml);
    }

    private function xmlSocio(): string
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_servicio' => 'socioeconomico',
            'tipo_formulario' => 'preempleo',
            'nombre' => 'Carmen',
            'apellidos' => 'LoteF',
        ]);
        $cuestionario = Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'socioeconomico',
            'seccion_actual' => 6,
            'total_secciones' => 6,
            'completado' => true,
        ]);
        CuestionarioRespuesta::guardarTabla($cuestionario->id, 'informacion_socioeconomica_complementaria', 'referencias_personales', [[
            'nombre' => 'Ana Amiga LoteF',
            'relacion' => 'Colegio LoteF',
            'telefono' => '55550101',
            'anos_conocerlo' => '12',
        ]]);
        EvaluadorNota::guardarNota($evaluado->id, 'word_salud', '', 'Narrativa salud LoteF', null);

        return $this->xmlDe($orden, $evaluado);
    }

    private function xmlPoli(): string
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_servicio' => 'poligrafo',
            'tipo_formulario' => 'preempleo',
            'nombre' => 'Ericka',
            'apellidos' => 'LoteF',
        ]);
        Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'preempleo',
            'seccion_actual' => 6,
            'total_secciones' => 6,
            'completado' => true,
        ]);
        EvaluadorNota::guardarNota($evaluado->id, 'word_economico', '', 'Narrativa económica LoteF', null);

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
