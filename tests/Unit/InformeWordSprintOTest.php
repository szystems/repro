<?php

namespace Tests\Unit;

use App\Models\Cuestionario;
use App\Models\CuestionarioRespuesta;
use App\Models\EvaluadorNota;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Support\InformePreempleo;
use App\Support\InformeWordBloquesEvaluador;
use App\Support\InformeWordExport;
use App\Support\InformeWordPreguntasPoligraficas;
use App\Support\InformeWordResultado;
use App\Support\InformeWordXml;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use ZipArchive;

/** Sprint O — observaciones 28-ago: complementaria, laboral socio, DI, layout económico. */
class InformeWordSprintOTest extends TestCase
{
    use RefreshDatabase;

    public function test_claves_complementaria_solo_preempleo_y_socio(): void
    {
        $this->assertArrayHasKey('complementaria', InformePreempleo::clavesTablas('preempleo'));
        $this->assertArrayHasKey('complementaria', InformePreempleo::clavesTablas('socioeconomico'));
        $this->assertArrayNotHasKey('complementaria', InformePreempleo::clavesTablas('periodica'));
        $this->assertArrayNotHasKey('complementaria', InformePreempleo::clavesTablas('especifica'));
        $this->assertArrayNotHasKey('labor_complementaria', InformePreempleo::clavesTablas('preempleo'));
    }

    public function test_socio_traslada_empleos_a_informacion_laboral(): void
    {
        $xml = $this->xmlSocioConEmpleos();
        $tabla = $this->tabla($xml, 'EMPLEOS:');
        $plano = InformeWordXml::textoTablaConcatenado($tabla);

        $this->assertStringContainsString('Corporacion Arlum SocioO', $plano);
        $this->assertStringContainsString('Auxiliar de Ruta', $plano);
        $this->assertStringContainsString('Envios Urgentes SocioO', $plano);
    }

    public function test_di_pinta_fila_en_rojo_y_llena_conclusion(): void
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_servicio' => 'poligrafo',
            'tipo_formulario' => 'preempleo',
            'resultado' => 'no_aprobado',
            'nombre' => 'Delvin',
            'apellidos' => 'Paxtor',
        ]);
        Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'preempleo',
            'seccion_actual' => 6,
            'total_secciones' => 6,
            'completado' => true,
        ]);
        InformeWordPreguntasPoligraficas::guardarDesdeRequest($evaluado->id, [
            ['pregunta' => '¿Cometió usted delitos en empleos anteriores?', 'respuesta' => 'No', 'resultado' => 'NDI', 'puntuacion' => '+3'],
            ['pregunta' => '¿Realizó usted robos mayores a Q.100.00 en empleos anteriores?', 'respuesta' => 'No', 'resultado' => 'DI', 'puntuacion' => '-3'],
            ['pregunta' => '¿En los últimos 6 meses ha consumido drogas ilegales?', 'respuesta' => 'No', 'resultado' => 'NDI', 'puntuacion' => '+3'],
            ['pregunta' => '¿Usted pertenece algún tipo de grupo delictivo?', 'respuesta' => 'No', 'resultado' => 'DI', 'puntuacion' => '-3'],
            ['pregunta' => '¿Está usted presentando algún documento o información falsa en este proceso de contratación?', 'respuesta' => 'No', 'resultado' => 'NDI', 'puntuacion' => '+3'],
        ], null);
        EvaluadorNota::guardarNota($evaluado->id, InformeWordResultado::NOTA_INDICACION_MENTIRA, '', 'R2 / R5 escrito a mano', null);

        $xml = $this->xmlDe($orden, $evaluado);
        $tabla = $this->tabla($xml, 'PREGUNTA RELEVANTE');
        $filas = InformeWordXml::filasTabla($tabla);
        $this->assertStringContainsString('w:val="FF0000"', $filas[2]);
        $this->assertStringNotContainsString('w:val="FF0000"', $filas[1]);
        $this->assertStringContainsString('w:val="FF0000"', $filas[4]);

        $plano = $this->textoPlano($xml);
        $this->assertStringContainsString('R2. ¿Realizó usted robos mayores a Q.100.00 en empleos anteriores?', $plano);
        $this->assertStringContainsString('R4. ¿Usted pertenece algún tipo de grupo delictivo?', $plano);
        $this->assertStringNotContainsString('R2 / R5 escrito a mano', $plano);
        $this->assertStringNotContainsString(InformeWordResultado::MARCADOR_PREGUNTAS_DI, $plano);
    }

    public function test_economico_usa_fuente_12_y_deja_espacio(): void
    {
        $tabla = '<w:tbl><w:tblPr><w:tblW w:w="5000" w:type="dxa"/></w:tblPr>'
            .'<w:tblGrid><w:gridCol w:w="5000"/></w:tblGrid>'
            .'<w:tr><w:tc><w:p><w:r><w:rPr><w:sz w:val="20"/><w:szCs w:val="20"/></w:rPr>'
            .'<w:t>ASPECTO ECONÓMICO:</w:t></w:r></w:p></w:tc></w:tr></w:tbl>';

        $out = InformeWordXml::forzarTamanoFuenteTabla($tabla, 24);
        $this->assertStringContainsString('w:sz w:val="24"', $out);
        $this->assertStringContainsString('w:szCs w:val="24"', $out);

        $xml = '<w:document><w:body>'
            .$tabla
            .'<w:tbl><w:tr><w:tc><w:p><w:r><w:t>ASPECTOS DE SALUD:</w:t></w:r></w:p></w:tc></w:tr></w:tbl>'
            .'</w:body></w:document>';
        $conEspacio = InformeWordXml::insertarFragmentoTrasTabla($xml, 'ASPECTO ECONÓMICO', InformeWordXml::parrafoEspacio());
        $this->assertGreaterThan(
            strpos($conEspacio, 'ASPECTO ECONÓMICO'),
            strpos($conEspacio, 'w:spacing')
        );
        $this->assertGreaterThan(
            strpos($conEspacio, 'w:spacing'),
            strpos($conEspacio, 'ASPECTOS DE SALUD')
        );
    }

    public function test_es_di_no_confunde_ndi(): void
    {
        $this->assertTrue(InformeWordPreguntasPoligraficas::esDi('DI'));
        $this->assertTrue(InformeWordPreguntasPoligraficas::esDi('DI: INDICACIÓN DE MENTIRA'));
        $this->assertFalse(InformeWordPreguntasPoligraficas::esDi('NDI'));
        $this->assertFalse(InformeWordPreguntasPoligraficas::esDi(''));
    }

    private function xmlSocioConEmpleos(): string
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_servicio' => 'socioeconomico',
            'tipo_formulario' => 'socioeconomico',
            'nombre' => 'UAT',
            'apellidos' => 'SocioO',
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
                'empresa' => 'Corporacion Arlum SocioO',
                'puesto' => 'Auxiliar de Ruta',
                'fechas_laboradas' => '07/2017 al 10/2018',
                'ultimo_salario' => '4800',
                'motivo_retiro' => 'Despido por reestructuracion',
            ],
            [
                'empresa' => 'Envios Urgentes SocioO',
                'puesto' => 'Secretaria',
                'fechas_laboradas' => '11/2020 al 03/2021',
                'ultimo_salario' => '4200',
                'motivo_retiro' => 'Despido por reestructuracion',
            ],
        ]);
        EvaluadorNota::guardarNota($evaluado->id, 'word_laboral', '', 'No debe salir en socio', null);
        EvaluadorNota::guardarNota($evaluado->id, InformeWordBloquesEvaluador::NOTA_RECOMENDACIONES, '', 'Rec socio O', null);

        return $this->xmlDe($orden, $evaluado);
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
