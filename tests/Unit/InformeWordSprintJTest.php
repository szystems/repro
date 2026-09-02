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
use App\Support\InformeWordNarrativas;
use App\Support\InformeWordPlantillas;
use App\Support\InformeWordXml;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use ZipArchive;

/** Sprint J — observaciones Stephany 19-ago (socio + polígrafo/VSA). */
class InformeWordSprintJTest extends TestCase
{
    use RefreshDatabase;

    public function test_j1_columnas_socio_incluyen_presupuesto_bienes_y_refs_laborales(): void
    {
        $columnas = InformePreempleo::clavesTablas('socioeconomico');

        $this->assertArrayHasKey('presupuesto', $columnas);
        $this->assertArrayHasKey('bienes', $columnas);
        $this->assertArrayHasKey('referencias_laborales', $columnas);
        $this->assertArrayNotHasKey('labor_complementaria', $columnas);
        $this->assertArrayHasKey('complementaria', $columnas);
    }

    public function test_j3_compila_preguntas_laborales_de_integridad(): void
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
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

        CuestionarioRespuesta::guardarRespuestas($cuestionario->id, 'historial_laboral', [
            'integridad_01' => 'El problema más serio fue un faltante y lo reporté.',
            'experiencia_previa' => 'Sí, empleos formales e informales.',
        ]);

        $tablas = InformePreempleo::compilarTablas($cuestionario);
        $preguntas = array_column($tablas['labor_complementaria'], 'pregunta');
        $respuestas = array_column($tablas['labor_complementaria'], 'respuesta');

        $this->assertContains('El problema más serio fue un faltante y lo reporté.', $respuestas);
        $this->assertTrue(collect($preguntas)->contains(fn ($p) => str_contains((string) $p, 'problema más serio')));
    }

    public function test_j4_bloque_laboral_se_distingue_de_referencia_personal(): void
    {
        $tablaPersonal = InformeWordXml::textoTablaConcatenado(
            '<w:tbl>'.$this->fila('Información brindada por el candidato Empresa: Teléfonos:')
            .$this->fila('¿Desde hace cuánto tiempo lo conoce?').'</w:tbl>'
        );
        $tablaLaboral = InformeWordXml::textoTablaConcatenado(
            '<w:tbl>'.$this->fila('Información brindada por el candidato Empresa: Teléfonos:')
            .$this->fila('Puesto que ocupó: Dirección:')
            .$this->fila('Motivo de Retiro:').'</w:tbl>'
        );

        $this->assertTrue(str_contains($tablaPersonal, 'Desde hace cuánto tiempo lo conoce'));
        $this->assertTrue(str_contains($tablaLaboral, 'Puesto que ocupó'));
        $this->assertTrue(str_contains($tablaLaboral, 'Motivo de Retiro'));
        $this->assertFalse(str_contains($tablaPersonal, 'Puesto que ocupó'));
    }

    public function test_j6_aspectos_a_considerar_usa_observaciones_del_evaluador(): void
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_servicio' => 'socioeconomico',
            'tipo_formulario' => 'preempleo',
            'nombre' => 'Rebeca',
            'apellidos' => 'Mazariegos',
        ]);
        Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'socioeconomico',
            'seccion_actual' => 6,
            'total_secciones' => 6,
            'completado' => true,
        ]);
        EvaluadorNota::guardarNota(
            $evaluado->id,
            InformeWordBloquesEvaluador::NOTA_OBSERVACIONES,
            '',
            'Observación primera hoja polígrafo de prueba.',
            null
        );

        $path = InformeWordExport::generar($orden->fresh(), $evaluado->fresh(['cuestionario', 'orden.empresa', 'sede']));
        $zip = new ZipArchive();
        $this->assertTrue($zip->open($path) === true);
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();
        @unlink($path);

        $this->assertIsString($xml);
        $this->assertStringContainsString('Observación primera hoja polígrafo de prueba.', $xml);
    }

    public function test_j8_recomendaciones_no_copian_notas_internas_de_antecedentes(): void
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_servicio' => 'poligrafo',
            'tipo_formulario' => 'preempleo',
            'notas_poligrafo' => 'NOTA INTERNA JUDICIAL NO DEBE SALIR',
        ]);
        Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'preempleo',
            'seccion_actual' => 5,
            'total_secciones' => 5,
            'completado' => true,
        ]);
        EvaluadorNota::guardarNota($evaluado->id, 'antecedentes', '', 'Antecedente interno confidencial.', null);

        $narrativas = InformeWordNarrativas::compilar($orden, $evaluado->fresh(['cuestionario']), InformeWordPlantillas::VARIANTE_PREEMPLEO);

        $this->assertSame('', $narrativas['recomendaciones']);
        $this->assertStringNotContainsString('NOTA INTERNA JUDICIAL NO DEBE SALIR', $narrativas['recomendaciones']);
        $this->assertStringNotContainsString('Antecedente interno confidencial.', $narrativas['recomendaciones']);
    }

    public function test_j8_recomendaciones_usan_casilla_del_evaluador(): void
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_servicio' => 'poligrafo',
            'tipo_formulario' => 'preempleo',
            'notas_poligrafo' => 'NOTA INTERNA',
        ]);
        EvaluadorNota::guardarNota(
            $evaluado->id,
            InformeWordBloquesEvaluador::NOTA_RECOMENDACIONES,
            '',
            'Recomendación redactada para el Word.',
            null
        );

        $this->assertSame(
            'Recomendación redactada para el Word.',
            InformeWordBloquesEvaluador::recomendaciones($evaluado->id)
        );
        $this->assertNotContains(
            InformeWordBloquesEvaluador::NOTA_RECOMENDACIONES,
            InformeWordBloquesEvaluador::faltantes($evaluado->id),
            'Recomendaciones es opcional y no debe bloquear el cierre del informe.'
        );
    }

    public function test_j10_normaliza_pareja_tiene_desde_si_no(): void
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_formulario' => 'preempleo',
        ]);
        $cuestionario = Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'preempleo',
            'seccion_actual' => 5,
            'total_secciones' => 5,
            'completado' => true,
        ]);

        InformePreempleo::guardarDesdeRequest($evaluado->id, [
            'familiar' => [
                'pareja' => [
                    'tiene' => 'si',
                    'nombre' => 'Pareja Editada',
                    'tipo' => 'Unión libre',
                ],
                'expareja' => [
                    'aplica' => 'si',
                    'nombre' => 'Expareja Editada',
                ],
                'hijos' => [['nombre' => 'Hijo Uno', 'edad' => '8', 'ocupacion' => 'Estudiante']],
            ],
        ], [], null);

        $tablas = InformePreempleo::tablasParaAdmin($cuestionario);
        $this->assertTrue($tablas['familiar']['pareja']['tiene'] ?? false);
        $this->assertTrue($tablas['familiar']['expareja']['aplica'] ?? false);
        $this->assertSame('Pareja Editada', $tablas['familiar']['pareja']['nombre'] ?? null);
        $this->assertSame('Hijo Uno', $tablas['familiar']['hijos'][0]['nombre'] ?? null);
    }

    public function test_j14_salud_socio_queda_en_blanco_sin_redaccion_evaluador(): void
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_servicio' => 'socioeconomico',
            'tipo_formulario' => 'preempleo',
        ]);
        $cuestionario = Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'socioeconomico',
            'seccion_actual' => 6,
            'total_secciones' => 6,
            'completado' => true,
        ]);
        CuestionarioRespuesta::guardarRespuestas($cuestionario->id, 'antecedentes', [
            'salud_preocupaciones' => 'NO DEBE PASAR AL WORD SOCIO',
            'salud_estado_general' => 'bueno',
        ]);

        $narrativas = InformeWordNarrativas::compilar(
            $orden,
            $evaluado->fresh(['cuestionario']),
            InformeWordPlantillas::VARIANTE_SOCIO
        );

        $this->assertSame('', $narrativas['salud']);
        $this->assertStringNotContainsString('NO DEBE PASAR AL WORD SOCIO', $narrativas['salud']);
    }

    public function test_j13_encabezado_escribe_puesto_en_la_celda_siguiente(): void
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_servicio' => 'poligrafo',
            'tipo_formulario' => 'preempleo',
            'puesto_evaluar' => 'Cajera de piso',
        ]);
        Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'preempleo',
            'seccion_actual' => 5,
            'total_secciones' => 5,
            'completado' => true,
        ]);

        $path = InformeWordExport::generar($orden->fresh(), $evaluado->fresh(['cuestionario', 'orden.empresa', 'sede']));
        $zip = new ZipArchive();
        $this->assertTrue($zip->open($path) === true);
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();
        @unlink($path);

        $this->assertIsString($xml);
        $limites = InformeWordXml::limitesTablaPorMarcador($xml, 'Agencia/Sede:');
        $this->assertNotNull($limites);
        $tabla = substr($xml, $limites[0], $limites[1] - $limites[0]);
        $celdas = InformeWordXml::celdasFila(InformeWordXml::filasTabla($tabla)[1]);

        $this->assertStringContainsString('Puesto:', InformeWordXml::textoCelda($celdas[0]));
        $this->assertStringContainsString('Cajera de piso', InformeWordXml::textoCelda($celdas[1]));
        $this->assertStringContainsString('Fecha:', InformeWordXml::textoCelda($celdas[2]));
        $this->assertStringContainsString('BDD7EE', $tabla);
    }

    public function test_j16_override_pasa_estudios_extra_en_fila_con_nivel(): void
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_servicio' => 'poligrafo',
            'tipo_formulario' => 'preempleo',
        ]);
        $cuestionario = Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'preempleo',
            'seccion_actual' => 5,
            'total_secciones' => 5,
            'completado' => true,
        ]);
        CuestionarioRespuesta::guardarRespuestas($cuestionario->id, 'historial_laboral', [
            'ultimo_nivel_academico' => 'universitario',
        ]);
        InformePreempleo::guardarDesdeRequest($evaluado->id, [
            'academico' => [
                [
                    'nivel' => 'universitario',
                    'carrera' => 'Administración',
                    'institucion' => 'USAC',
                    'anio' => '2020',
                ],
                [
                    'nivel' => 'primaria',
                    'carrera' => 'Primaria',
                    'institucion' => 'Escuela Extra J16',
                    'anio' => '2008',
                ],
            ],
        ], [], null);

        $path = InformeWordExport::generar($orden->fresh(), $evaluado->fresh(['cuestionario', 'orden.empresa', 'sede']));
        $zip = new ZipArchive();
        $this->assertTrue($zip->open($path) === true);
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();
        @unlink($path);

        $this->assertIsString($xml);
        $this->assertTrue(str_contains($xml, 'USAC'), 'Falta institución universitario');
        $this->assertTrue(str_contains($xml, 'Escuela Extra J16'), 'Falta institución extra');
        $textoPlano = html_entity_decode(preg_replace('/<[^>]+>/', '', $xml) ?? '', ENT_QUOTES | ENT_XML1, 'UTF-8');
        $this->assertTrue(
            str_contains($textoPlano, 'Primario') || str_contains($textoPlano, 'Primaria'),
            'El extra de primaria debe ir en fila de nivel, no concatenado en Otros'
        );
    }

    public function test_j16_sin_override_h10_oculta_nivel_fuera_de_cobertura(): void
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_servicio' => 'poligrafo',
            'tipo_formulario' => 'preempleo',
        ]);
        $cuestionario = Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'preempleo',
            'seccion_actual' => 5,
            'total_secciones' => 5,
            'completado' => true,
        ]);
        CuestionarioRespuesta::guardarRespuestas($cuestionario->id, 'historial_laboral', [
            'ultimo_nivel_academico' => 'universitario',
        ]);
        CuestionarioRespuesta::guardarTabla($cuestionario->id, 'historial_laboral', 'formacion_academica', [
            [
                'nivel' => 'universitario',
                'carrera' => 'Derecho',
                'institucion' => 'USAC Visible',
                'anio' => '2020',
            ],
            [
                'nivel' => 'primaria',
                'carrera' => 'Primaria',
                'institucion' => 'Escuela No Debe Salir H10',
                'anio' => '2008',
            ],
        ]);

        $path = InformeWordExport::generar($orden->fresh(), $evaluado->fresh(['cuestionario', 'orden.empresa', 'sede']));
        $zip = new ZipArchive();
        $this->assertTrue($zip->open($path) === true);
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();
        @unlink($path);

        $this->assertIsString($xml);
        $this->assertStringContainsString('USAC Visible', $xml);
        $this->assertStringNotContainsString('Escuela No Debe Salir H10', $xml);
    }

    public function test_nivel_tecnico_extra_sale_en_fila_propia(): void
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_servicio' => 'socioeconomico',
            'tipo_formulario' => 'preempleo',
        ]);
        $cuestionario = Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'preempleo',
            'seccion_actual' => 6,
            'total_secciones' => 6,
            'completado' => true,
        ]);
        InformePreempleo::guardarDesdeRequest($evaluado->id, [
            'academico' => [
                [
                    'nivel' => 'diversificado',
                    'carrera' => 'Perito Contador',
                    'institucion' => 'Encod',
                    'anio' => '2017',
                ],
                [
                    'nivel' => 'tecnico',
                    'carrera' => 'Técnico en redes',
                    'institucion' => 'INTECAP Extra',
                    'anio' => '2025',
                ],
            ],
        ], [], null);

        $path = InformeWordExport::generar($orden->fresh(), $evaluado->fresh(['cuestionario', 'orden.empresa', 'sede']));
        $zip = new ZipArchive();
        $this->assertTrue($zip->open($path) === true);
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();
        @unlink($path);

        $this->assertIsString($xml);
        $this->assertTrue(str_contains($xml, 'INTECAP Extra'));
        $this->assertTrue(str_contains($xml, 'Técnico:'));
    }

    public function test_j5_titulo_fuera_de_tabla_no_rellena_la_tabla_anterior(): void
    {
        $xml = '<w:document><w:body>'
            . '<w:tbl>'.$this->fila('Nombre: ¿Desde hace cuánto tiempo lo conoce?').'</w:tbl>'
            . '<w:p><w:r><w:t>INFORMACIÓN LABORAL</w:t></w:r></w:p>'
            . '<w:tbl>'.$this->fila('EMPLEOS: Empresa: Puesto Ocupado: Motivo de retiro:').'</w:tbl>'
            . '</w:body></w:document>';

        $limites = InformeWordXml::limitesTablaPorMarcador($xml, 'INFORMACIÓN LABORAL');
        $this->assertNotNull($limites);
        $tabla = substr($xml, $limites[0], $limites[1] - $limites[0]);
        $this->assertStringContainsString('Puesto Ocupado', $tabla);
        $this->assertStringNotContainsString('Desde hace cuánto tiempo lo conoce', $tabla);
    }

    public function test_j9_inserta_despues_de_la_ultima_tabla_tatuajes(): void
    {
        $xml = '<w:document><w:body>'
            . '<w:tbl><w:tr><w:tc><w:p><w:r><w:t>TATUAJES</w:t></w:r></w:p></w:tc></w:tr></w:tbl>'
            . '<w:p><w:r><w:t>RESULTADOS DE EVALUACIÓN</w:t></w:r></w:p>'
            . '<w:tbl><w:tr><w:tc><w:p><w:r><w:t>TATUAJES</w:t></w:r></w:p></w:tc></w:tr></w:tbl>'
            . '</w:body></w:document>';

        $posicion = InformeWordXml::posicionFinTablaPorMarcador($xml, 'TATUAJES');
        $this->assertNotNull($posicion);
        $this->assertGreaterThan(strrpos($xml, 'RESULTADOS DE EVALUACIÓN'), $posicion);
    }

    private function fila(string $texto): string
    {
        return '<w:tr><w:tc><w:p><w:r><w:t>'.$texto.'</w:t></w:r></w:p></w:tc></w:tr>';
    }
}
