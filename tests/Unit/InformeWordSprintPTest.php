<?php

namespace Tests\Unit;

use App\Models\Cuestionario;
use App\Models\CuestionarioRespuesta;
use App\Models\EvaluadorNota;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Support\InformePreempleo;
use App\Support\InformeWordExport;
use App\Support\InformeWordPreguntasPoligraficas;
use App\Support\InformeWordResultado;
use App\Support\InformeWordXml;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use ZipArchive;

/** Sprint P — UAT 28-ago noche: socio empleos, cuadro DI, deudas 11, tablas separadas. */
class InformeWordSprintPTest extends TestCase
{
    use RefreshDatabase;

    public function test_socio_prod_like_traslada_empleos_aunque_override_laboral_este_vacio(): void
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_servicio' => 'socioeconomico',
            'tipo_formulario' => 'preempleo',
            'nombre' => 'UAT',
            'apellidos' => 'SocioP',
        ]);
        $cuestionario = Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'socioeconomico',
            'seccion_actual' => 6,
            'total_secciones' => 6,
            'completado' => true,
        ]);
        CuestionarioRespuesta::guardarTabla($cuestionario->id, 'historial_laboral', 'empleos', [
            [
                'empresa' => 'Corporacion Arium SocioP',
                'puesto' => 'Auxiliar de Ruta',
                'fechas_laboradas' => '07/2017 al 10/2018',
                'ultimo_salario' => '4800',
                'motivo_retiro' => 'Despido por reestructuracion',
            ],
        ]);
        EvaluadorNota::guardarNota($evaluado->id, InformePreempleo::SECCION_NOTAS, 'laboral', '[{"empresa":"","puesto":"","jefe_inmediato":"-----"}]', null);
        EvaluadorNota::guardarNota($evaluado->id, 'word_laboral', '', 'Desempeno laboral UAT SocioP', null);

        $xml = $this->xmlDe($orden, $evaluado);
        $limites = InformeWordXml::limitesTablaTrasTexto($xml, 'INFORMACIÓN LABORAL')
            ?? InformeWordXml::limitesTablaPorMarcador($xml, 'EMPLEOS:');
        $this->assertNotNull($limites);
        $tabla = substr($xml, $limites[0], $limites[1] - $limites[0]);
        $plano = InformeWordXml::textoTablaConcatenado($tabla);

        $this->assertStringContainsString('Corporacion Arium SocioP', $plano);
        $this->assertStringContainsString('Auxiliar de Ruta', $plano);

        $aspecto = InformeWordXml::limitesTablaPorMarcador($xml, 'INFORMACIÓN COMPLEMENTARIA LABORAL');
        $this->assertNotNull($aspecto);
        $this->assertStringContainsString(
            'Desempeno laboral UAT SocioP',
            InformeWordXml::textoTablaConcatenado(substr($xml, $aspecto[0], $aspecto[1] - $aspecto[0]))
        );
    }

    public function test_no_aprobado_quita_tabla_vacia_tras_preguntas_di(): void
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_servicio' => 'poligrafo',
            'tipo_formulario' => 'preempleo',
            'resultado' => 'no_aprobado',
            'nombre' => 'Josue',
            'apellidos' => 'Acabal',
        ]);
        Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'preempleo',
            'seccion_actual' => 6,
            'total_secciones' => 6,
            'completado' => true,
        ]);
        InformeWordPreguntasPoligraficas::guardarDesdeRequest($evaluado->id, [
            ['pregunta' => '¿Cometió usted delitos en empleos anteriores?', 'respuesta' => 'No', 'resultado' => 'DI', 'puntuacion' => '-3'],
            ['pregunta' => '¿Realizó usted robos mayores a Q.100.00 en empleos anteriores?', 'respuesta' => 'No', 'resultado' => 'DI', 'puntuacion' => '-3'],
        ], null);

        $xml = $this->xmlDe($orden, $evaluado);
        $r1 = 'R1. ¿Cometió usted delitos en empleos anteriores?';
        $posR1 = strpos($xml, $r1);
        $posClasifica = strpos($xml, 'se clasifica');
        $this->assertNotFalse($posR1);
        $this->assertNotFalse($posClasifica);
        $this->assertGreaterThan($posR1, $posClasifica);
        $frase = strpos($xml, 'NO RESPONDIÓ CON VERACIDAD');
        $this->assertNotFalse($frase);
        $r1Conclusion = strpos($xml, $r1, $frase);
        $this->assertNotFalse($r1Conclusion);
        $entre = substr($xml, $r1Conclusion, $posClasifica - $r1Conclusion);
        $this->assertStringNotContainsString('<w:tbl', $entre);
        $this->assertStringNotContainsString(InformeWordResultado::MARCADOR_PREGUNTAS_DI, $this->textoPlano($xml));
    }

    public function test_p_r1_no_borra_tabla_si_el_r1_tambien_esta_antes_en_preguntas(): void
    {
        $xml = '<w:body>'
            .'<w:tbl><w:tr><w:tc><w:p><w:r><w:t>R1. ¿Cometió usted delitos en empleos anteriores?</w:t></w:r></w:p></w:tc></w:tr></w:tbl>'
            .'<w:p><w:r><w:t>NO RESPONDIÓ CON VERACIDAD A LAS SIGUIENTES PREGUNTAS RELEVANTES:</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>R1. ¿Cometió usted delitos en empleos anteriores?</w:t></w:r></w:p>'
            .'<w:tbl><w:tr><w:tc><w:p><w:r><w:t></w:t></w:r></w:p></w:tc></w:tr>'
            .'<w:tr><w:tc><w:p/></w:tc></w:tr><w:tr><w:tc><w:p/></w:tc></w:tr></w:tbl>'
            .'<w:p><w:r><w:t>se clasifica como NO APROBADO</w:t></w:r></w:p>'
            .'</w:body>';

        $out = InformeWordXml::eliminarTablaSiguienteTrasParrafo(
            $xml,
            'R1. ¿Cometió usted delitos en empleos anteriores?'
        );

        $this->assertStringContainsString('R1. ¿Cometió usted delitos en empleos anteriores?', $out);
        $frase = strpos($out, 'NO RESPONDIÓ CON VERACIDAD');
        $clasifica = strpos($out, 'se clasifica');
        $entre = substr($out, $frase, $clasifica - $frase);
        $this->assertStringNotContainsString('<w:tbl', $entre);
        $this->assertStringContainsString('<w:tbl>', $out);
    }

    public function test_deudas_ancha_queda_en_11_y_observaciones_en_12(): void
    {
        $deudas = '<w:tr><w:tc/><w:tc/><w:tc/><w:tc/><w:tc>'
            .'<w:p><w:r><w:rPr><w:sz w:val="20"/><w:szCs w:val="20"/></w:rPr>'
            .'<w:t>Crediopciones</w:t></w:r></w:p></w:tc></w:tr>';
        $obs = '<w:tr><w:tc><w:p><w:r><w:rPr><w:sz w:val="20"/><w:szCs w:val="20"/></w:rPr>'
            .'<w:t>Adicionalmente posee una tarjeta</w:t></w:r></w:p></w:tc></w:tr>';
        $tabla = '<w:tbl><w:tblPr></w:tblPr>'.$deudas.$obs.'</w:tbl>';

        $out = InformeWordXml::forzarTamanoFuenteFilasPorAncho($tabla, 24, 22, 5);
        $filas = InformeWordXml::filasTabla($out);

        $this->assertStringContainsString('w:sz w:val="22"', $filas[0]);
        $this->assertStringContainsString('w:sz w:val="24"', $filas[1]);
    }

    public function test_socio_llena_la_tabla_justo_bajo_informacion_laboral(): void
    {
        $xml = '<w:body>'
            .'<w:p><w:r><w:t>INFORMACIÓN LABORAL</w:t></w:r><w:r><w:t>:</w:t></w:r></w:p>'
            .'<w:tbl>'
            .'<w:tr><w:tc><w:p><w:r><w:t>EMPLEOS</w:t></w:r></w:p></w:tc></w:tr>'
            .'<w:tr><w:tc><w:p><w:r><w:t>Empresa:</w:t></w:r></w:p></w:tc>'
            .'<w:tc><w:p><w:r><w:t>Puesto Ocupado:</w:t></w:r></w:p></w:tc>'
            .'<w:tc><w:p><w:r><w:t>Fechas laboradas:</w:t></w:r></w:p></w:tc>'
            .'<w:tc><w:p><w:r><w:t>Salario mensual:</w:t></w:r></w:p></w:tc>'
            .'<w:tc><w:p><w:r><w:t>Motivo de retiro:</w:t></w:r></w:p></w:tc></w:tr>'
            .'<w:tr><w:tc><w:p><w:r><w:t></w:t></w:r></w:p></w:tc>'
            .'<w:tc><w:p><w:r><w:t></w:t></w:r></w:p></w:tc>'
            .'<w:tc><w:p><w:r><w:t></w:t></w:r></w:p></w:tc>'
            .'<w:tc><w:p><w:r><w:t></w:t></w:r></w:p></w:tc>'
            .'<w:tc><w:p><w:r><w:t></w:t></w:r></w:p></w:tc></w:tr>'
            .'</w:tbl>'
            .'<w:tbl><w:tr><w:tc><w:p><w:r><w:t>EMPLEOS: verificación</w:t></w:r></w:p></w:tc></w:tr></w:tbl>'
            .'</w:body>';

        $limites = InformeWordXml::limitesTablaTrasTexto($xml, 'INFORMACIÓN LABORAL');
        $this->assertNotNull($limites);
        $tabla = substr($xml, $limites[0], $limites[1] - $limites[0]);
        $this->assertStringContainsString('Puesto Ocupado', $tabla);
        $this->assertStringNotContainsString('verificación', $tabla);
    }

    public function test_detecta_grilla_empleos_anidada_bajo_el_titulo(): void
    {
        $tabla = '<w:tbl>'
            .'<w:tr><w:tc><w:p><w:r><w:t>EMPLEOS</w:t></w:r></w:p></w:tc></w:tr>'
            .'<w:tr><w:tc><w:tbl>'
            .'<w:tr><w:tc><w:p><w:r><w:t>Empresa</w:t></w:r></w:p></w:tc>'
            .'<w:tc><w:p><w:r><w:t>Puesto Ocupado</w:t></w:r></w:p></w:tc></w:tr>'
            .'<w:tr><w:tc><w:p><w:r><w:t></w:t></w:r></w:p></w:tc>'
            .'<w:tc><w:p><w:r><w:t></w:t></w:r></w:p></w:tc></w:tr>'
            .'</w:tbl></w:tc></w:tr>'
            .'</w:tbl>';

        $this->assertCount(2, InformeWordXml::filasTabla($tabla));
        $interna = InformeWordXml::limitesTablaInternaConTextos($tabla, 'Empresa', 'Puesto');
        $this->assertNotNull($interna);
        $grid = substr($tabla, $interna[0], $interna[1] - $interna[0]);
        $this->assertStringContainsString('Puesto Ocupado', $grid);
        $this->assertCount(2, InformeWordXml::filasTabla($grid));
    }

    public function test_titulo_core_usa_nombre_del_candidato(): void
    {
        $core = '<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/"><dc:title></dc:title></cp:coreProperties>';
        $out = InformeWordXml::actualizarTituloCore($core, 'Luis Fernando Ramirez - DINAMICA PERSONAL');
        $this->assertStringContainsString('Luis Fernando Ramirez - DINAMICA PERSONAL', $out);
    }

    public function test_filas_tabla_no_corta_filas_anidadas(): void
    {
        $xml = '<w:tbl>'
            .'<w:tr><w:tc><w:p><w:r><w:t>Titulo</w:t></w:r></w:p></w:tc></w:tr>'
            .'<w:tr><w:tc><w:tbl><w:tr><w:tc><w:p><w:r><w:t>Interna</w:t></w:r></w:p></w:tc></w:tr></w:tbl></w:tc></w:tr>'
            .'<w:tr><w:tc><w:p><w:r><w:t>Pie</w:t></w:r></w:p></w:tc></w:tr>'
            .'</w:tbl>';

        $filas = InformeWordXml::filasTabla($xml);
        $this->assertCount(3, $filas);
        $this->assertStringContainsString('Titulo', $filas[0]);
        $this->assertStringContainsString('Interna', $filas[1]);
        $this->assertStringContainsString('Pie', $filas[2]);
        $this->assertCount(1, InformeWordXml::celdasFila($filas[1]));
    }

    public function test_separa_tablas_contiguas(): void
    {
        $xml = '<w:document><w:body>'
            .'<w:tbl><w:tr><w:tc><w:p><w:r><w:t>A</w:t></w:r></w:p></w:tc></w:tr></w:tbl>'
            .'<w:tbl><w:tr><w:tc><w:p><w:r><w:t>B</w:t></w:r></w:p></w:tc></w:tr></w:tbl>'
            .'</w:body></w:document>';

        $out = InformeWordXml::separarTablasContiguas($xml);
        $this->assertStringContainsString('w:spacing', $out);
        $this->assertGreaterThan(strpos($out, '>A<'), strpos($out, 'w:spacing') === false ? 0 : 1);
        $this->assertFalse((bool) preg_match('/<\/w:tbl>\s*<w:tbl\b/', $out));
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

    private function tabla(string $xml, string $marcador): string
    {
        $limites = InformeWordXml::limitesTablaPorMarcador($xml, $marcador);
        $this->assertNotNull($limites, "Falta la tabla {$marcador}");

        return substr($xml, $limites[0], $limites[1] - $limites[0]);
    }

    private function textoPlano(string $xml): string
    {
        return html_entity_decode(preg_replace('/<[^>]+>/', '', $xml) ?? '', ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}
