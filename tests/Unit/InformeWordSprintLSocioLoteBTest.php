<?php

namespace Tests\Unit;

use App\Models\Cuestionario;
use App\Models\CuestionarioRespuesta;
use App\Models\EvaluadorNota;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Support\InformeWordBloquesEvaluador;
use App\Support\InformeWordExport;
use App\Support\InformeWordXml;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use ZipArchive;

/** Sprint L lote B — observaciones socio 24-ago (Carmen / PRUEBA 1). */
class InformeWordSprintLSocioLoteBTest extends TestCase
{
    use RefreshDatabase;

    public function test_lote_b_estudia_pareja_hijos_amistades_laboral_patrimonio_y_recomendaciones(): void
    {
        $xml = $this->xmlSocioLoteB();
        $plano = $this->textoPlano($xml);

        $tablaAcademica = $this->tabla($xml, 'DATOS ACADÉMICOS');
        $this->assertStringContainsString('Sí', $tablaAcademica);
        $this->assertStringContainsString('Contaduría Pública LoteB', $tablaAcademica);
        $this->assertStringContainsString('USAC LoteB', $tablaAcademica);
        $this->assertStringContainsString('martes y jueves LoteB', $tablaAcademica);

        $tablaEstado = $this->tabla($xml, 'ESTADO CIVIL');
        $this->assertStringContainsString('Laura Pareja LoteB', $tablaEstado);
        $this->assertStringContainsString('34 años', $tablaEstado);
        $this->assertStringContainsString('Contadora LoteB', $tablaEstado);

        $tablaHijos = $this->tabla($xml, 'HIJOS:');
        $this->assertStringContainsString('Mario Hijo LoteB', $tablaHijos);
        $this->assertStringNotContainsString('Con quién vive', $tablaHijos);
        $this->assertStringNotContainsString('Con quien vive', $tablaHijos);

        $tablaAmistades = $this->tabla($xml, 'AMISTADES:');
        $this->assertStringContainsString('Ana Amiga LoteB', $tablaAmistades);
        $this->assertStringContainsString('55550101', $tablaAmistades);
        $this->assertStringContainsString('Colegio LoteB', $tablaAmistades);
        $this->assertStringContainsString('12', $tablaAmistades);
        $this->assertStringContainsString('Años de conocerlo', $tablaAmistades);
        $this->assertStringNotContainsString('Dirección:', $tablaAmistades);
        $this->assertStringNotContainsString('Ocupación y lugar de trabajo', $tablaAmistades);

        $limitesAspecto = InformeWordXml::limitesTablaPorMarcador($xml, 'INFORMACIÓN COMPLEMENTARIA LABORAL');
        $this->assertNotNull($limitesAspecto);
        $this->assertStringContainsString(
            'Observación laboral REPRO LoteB',
            InformeWordXml::textoTablaConcatenado(substr($xml, $limitesAspecto[0], $limitesAspecto[1] - $limitesAspecto[0]))
        );
        $plano = $this->textoPlano($xml);
        $this->assertStringNotContainsString('faltante LoteB', $plano);

        $tablaPatrimonio = $this->tabla($xml, 'DETALLE PATRIMONIAL');
        $this->assertStringContainsString('Refrigeradora LoteB', $tablaPatrimonio);
        $this->assertStringContainsString('Lavadora LoteB', $tablaPatrimonio);
        $this->assertStringContainsString('Televisor LoteB', $tablaPatrimonio);
        $this->assertStringContainsString('Motocicleta LoteB', $tablaPatrimonio);
        $this->assertStringContainsString('Joyas LoteB', $tablaPatrimonio);
        $this->assertTrue(
            str_contains($tablaPatrimonio, 'Q 8,200.00')
            || str_contains($tablaPatrimonio, 'Q8,200.00')
            || str_contains($plano, '8200'),
            'El total patrimonial no se trasladó'
        );

        $tablaOtros = $this->tabla($xml, 'OTROS ASPECTOS');
        $this->assertStringNotContainsString('Bienes inmuebles', $tablaOtros);
        $this->assertStringNotContainsString('Vehículos propios', $tablaOtros);

        $tablaRecomendaciones = $this->tabla($xml, 'RECOMENDACIONES');
        $this->assertStringContainsString('Recomendación socio LoteB', $tablaRecomendaciones);
        $this->assertStringNotContainsString('RECOMENDABLE.', $tablaRecomendaciones);
    }

    public function test_eliminar_columnas_ajusta_grid_y_deja_el_titulo(): void
    {
        $tabla = '<w:tbl><w:tblGrid><w:gridCol w:w="1000"/><w:gridCol w:w="2000"/><w:gridCol w:w="3000"/><w:gridCol w:w="4000"/></w:tblGrid>'
            . '<w:tr><w:tc><w:tcPr><w:gridSpan w:val="4"/></w:tcPr><w:p><w:r><w:t>HIJOS:</w:t></w:r></w:p></w:tc></w:tr>'
            . '<w:tr>'
            . '<w:tc><w:p><w:r><w:t>Nombre:</w:t></w:r></w:p></w:tc>'
            . '<w:tc><w:p><w:r><w:t>Edad:</w:t></w:r></w:p></w:tc>'
            . '<w:tc><w:p><w:r><w:t>Ocupación:</w:t></w:r></w:p></w:tc>'
            . '<w:tc><w:p><w:r><w:t>Con quién vive:</w:t></w:r></w:p></w:tc>'
            . '</w:tr></w:tbl>';

        $indice = InformeWordXml::indiceColumnaPorTexto($tabla, 'Con quién vive');
        $this->assertSame(3, $indice);

        $sinColumna = InformeWordXml::eliminarColumnas($tabla, [$indice]);
        $this->assertStringNotContainsString('Con quién vive', $sinColumna);
        $this->assertStringContainsString('HIJOS:', $sinColumna);
        $this->assertStringContainsString('w:gridSpan w:val="3"', $sinColumna);
        $this->assertSame(3, substr_count($sinColumna, '<w:gridCol'));
    }

    private function xmlSocioLoteB(): string
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_servicio' => 'socioeconomico',
            'tipo_formulario' => 'preempleo',
            'nombre' => 'Carmen',
            'apellidos' => 'Lote B',
        ]);
        $cuestionario = Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'socioeconomico',
            'seccion_actual' => 6,
            'total_secciones' => 6,
            'completado' => true,
        ]);

        CuestionarioRespuesta::guardarRespuestas($cuestionario->id, 'informacion_familiar', [
            'vive_con_pareja' => 'si',
            'pareja_tipo_relacion' => 'casado',
            'pareja_nombre' => 'Laura Pareja LoteB',
            'pareja_edad' => '34',
            'pareja_telefono' => '55555666',
            'pareja_direccion' => 'Zona 1 LoteB',
            'pareja_ocupacion' => 'Contadora LoteB',
            'pareja_tiempo_relacion' => '8 años',
            'pareja_calidad_relacion' => 'buena',
            'tiene_hijos' => 'si',
        ]);
        CuestionarioRespuesta::guardarTabla($cuestionario->id, 'informacion_familiar', 'hijos', [[
            'nombre' => 'Mario Hijo LoteB',
            'edad' => '7',
            'vive_con_candidato' => 'si',
            'ocupacion' => 'Estudiante',
            'telefono' => '',
        ]]);
        CuestionarioRespuesta::guardarRespuestas($cuestionario->id, 'historial_laboral', [
            'estudia_actualmente' => 'si',
            'integridad_01' => 'El problema más serio fue un faltante LoteB y lo reporté.',
        ]);
        CuestionarioRespuesta::guardarTabla($cuestionario->id, 'historial_laboral', 'estudios_actuales', [[
            'que_estudia' => 'Contaduría Pública LoteB',
            'institucion' => 'USAC LoteB',
            'horario' => '18:30 a 21:30, martes y jueves LoteB',
        ]]);
        CuestionarioRespuesta::guardarTabla($cuestionario->id, 'informacion_socioeconomica_complementaria', 'referencias_personales', [[
            'nombre' => 'Ana Amiga LoteB',
            'relacion' => 'Colegio LoteB',
            'telefono' => '55550101',
            'anos_conocerlo' => '12',
        ]]);
        CuestionarioRespuesta::guardarTabla($cuestionario->id, 'informacion_socioeconomica_complementaria', 'bienes', [
            ['descripcion' => 'Refrigeradora LoteB', 'valor' => '2500'],
            ['descripcion' => 'Lavadora LoteB', 'valor' => '1800'],
            ['descripcion' => 'Televisor LoteB', 'valor' => '1200'],
            ['descripcion' => 'Motocicleta LoteB', 'valor' => '2000'],
            ['descripcion' => 'Joyas LoteB', 'valor' => '700'],
        ]);

        EvaluadorNota::guardarNota($evaluado->id, 'word_laboral', '', 'Observación laboral REPRO LoteB', null);
        EvaluadorNota::guardarNota(
            $evaluado->id,
            InformeWordBloquesEvaluador::NOTA_RECOMENDACIONES,
            '',
            'Recomendación socio LoteB',
            null
        );

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

    private function textoPlano(string $xml): string
    {
        return html_entity_decode(preg_replace('/<[^>]+>/', '', $xml) ?? '', ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}
