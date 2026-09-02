<?php

namespace Tests\Unit;

use App\Models\Cuestionario;
use App\Models\CuestionarioRespuesta;
use App\Models\DocumentoEvaluado;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Models\User;
use App\Support\CuestionarioFotoCandidato;
use App\Support\InformeWordAnexosPapeleria;
use App\Support\InformeWordExport;
use App\Support\InformeWordFoto;
use App\Support\InformeWordXml;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class InformeWordExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_usa_plantilla_oficial_y_reemplaza_encabezado(): void
    {
        $this->assertFileExists(InformeWordExport::rutaPlantilla());

        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'nombre' => 'Carlos',
            'apellidos' => 'Demo Prueba',
            'dpi' => '2405617300105',
            'puesto_evaluar' => 'Analista de prueba',
            'resultado' => 'aprobado',
        ]);

        \App\Models\EvaluadorNota::guardarNota(
            $evaluado->id,
            \App\Support\InformeWordBloquesEvaluador::NOTA_OBSERVACIONES,
            '',
            'Observación de prueba para Word.',
            null
        );

        $path = InformeWordExport::generar($orden, $evaluado);

        $this->assertFileExists($path);
        $this->assertStringEndsWith('.docx', $path);

        $zip = new ZipArchive();
        $this->assertTrue($zip->open($path) === true);
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();
        @unlink($path);

        $this->assertIsString($xml);
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $this->assertTrue($dom->loadXML($xml), 'document.xml debe ser XML válido para abrir en Word');
        libxml_clear_errors();
        $this->assertStringContainsString('Carlos', $xml);
        $this->assertStringContainsString('Demo Prueba', $xml);
        $this->assertStringContainsString('Observación de prueba para Word.', $xml);
        $this->assertStringNotContainsString('Jorge Luis Martínez Alvarado', $xml);
        $this->assertStringNotContainsString('Víctor Manuel Martínez', $xml);
        $this->assertStringNotContainsString('JORGE LUIS MARTINEZ ALVARADO', $xml);
    }

    public function test_etiqueta_proceso_preempleo_poligrafo(): void
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_servicio' => 'poligrafo',
            'tipo_formulario' => 'preempleo',
        ]);

        $path = InformeWordExport::generar($orden, $evaluado);

        $zip = new ZipArchive();
        $zip->open($path);
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();
        @unlink($path);

        $this->assertStringContainsString('INFORME POLIGR', $xml);
        $this->assertStringContainsString('DATOS GENERALES', $xml);
        $this->assertStringContainsString('DATOS FAMILIARES', $xml);
        $this->assertStringNotContainsString('Víctor Manuel Martínez', $xml);
    }

    public function test_usa_plantilla_periodica_poligrafo(): void
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_servicio' => 'poligrafo',
            'tipo_formulario' => 'periodica',
            'nombre' => 'Ana',
            'apellidos' => 'Periodica Test',
        ]);

        $path = InformeWordExport::generar($orden, $evaluado);

        $zip = new ZipArchive();
        $zip->open($path);
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();
        @unlink($path);

        $this->assertStringContainsString('INFORME POLIGR', $xml);
        $this->assertStringContainsString('PERI', $xml);
        $this->assertStringContainsString('Ana', $xml);
        $this->assertStringContainsString('Periodica Test', $xml);
        $this->assertStringNotContainsString('HERMANOS:', $xml);
    }

    public function test_reemplaza_foto_del_evaluado_en_plantilla(): void
    {
        Storage::fake('local');

        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_servicio' => 'poligrafo',
            'tipo_formulario' => 'preempleo',
        ]);

        $cuestionario = Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'preempleo',
            'seccion_actual' => 1,
            'total_secciones' => 5,
            'progreso_porcentaje' => 20,
            'completado' => false,
            'bloqueado' => false,
            'terminos_aceptados' => true,
            'terminos_aceptados_at' => now(),
        ]);

        $fotoBytes = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAYAAACNMs+9AAAAFUlEQVR42mP8z8BQz0AEYBxVSF+FABJADveWkH6oAAAAAElFTkSuQmCC'
        );
        $rutaFoto = "cuestionarios/fotos/{$cuestionario->id}/foto_candidato.png";
        Storage::disk('local')->put($rutaFoto, $fotoBytes);

        CuestionarioRespuesta::create([
            'cuestionario_id' => $cuestionario->id,
            'seccion' => 'datos_personales',
            'campo' => CuestionarioFotoCandidato::CAMPO,
            'valor' => $rutaFoto,
            'tipo_campo' => 'file',
            'requerido' => true,
        ]);

        $evaluado = $evaluado->fresh(['cuestionario']);
        $path = InformeWordExport::generar($orden, $evaluado);

        $zip = new ZipArchive();
        $zip->open($path);
        $documentXml = $zip->getFromName('word/document.xml');
        $fotoGenerada = $zip->getFromName('word/media/foto_evaluado.png');
        if ($fotoGenerada === false) {
            $fotoGenerada = $zip->getFromName('word/media/foto_evaluado.jpg');
        }
        $zip->close();
        @unlink($path);

        $limites = InformeWordXml::limitesTablaPorMarcador($documentXml, 'DATOS GENERALES');
        $this->assertNotNull($limites);

        $antesDeLaTabla = substr($documentXml, 0, $limites[0]);
        $this->assertStringContainsString('wp:anchor', $antesDeLaTabla, 'La foto usa el marco anclado de la plantilla');
        $this->assertStringNotContainsString('wp:inline', $antesDeLaTabla, 'No debe insertarse inline encima del encabezado');
        $this->assertNotEmpty($fotoGenerada);
    }

    public function test_inserta_foto_jpeg_sin_gd_jpeg(): void
    {
        Storage::fake('local');

        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_servicio' => 'poligrafo',
            'tipo_formulario' => 'preempleo',
        ]);

        $cuestionario = Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'preempleo',
            'seccion_actual' => 1,
            'total_secciones' => 5,
            'progreso_porcentaje' => 20,
            'completado' => false,
            'bloqueado' => false,
            'terminos_aceptados' => true,
            'terminos_aceptados_at' => now(),
        ]);

        $jpeg = base64_decode('/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////2wBDAf//////////////////////////////////////////////////////////////////////////////////////wAARCAABAAEDAREAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAn/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwCwAA8A/9k=');
        $rutaFoto = "cuestionarios/fotos/{$cuestionario->id}/foto_candidato.jpg";
        Storage::disk('local')->put($rutaFoto, $jpeg);

        CuestionarioRespuesta::create([
            'cuestionario_id' => $cuestionario->id,
            'seccion' => 'datos_personales',
            'campo' => CuestionarioFotoCandidato::CAMPO,
            'valor' => $rutaFoto,
            'tipo_campo' => 'file',
            'requerido' => true,
        ]);

        $evaluado = $evaluado->fresh(['cuestionario']);
        $path = InformeWordExport::generar($orden, $evaluado);

        $zip = new ZipArchive();
        $zip->open($path);
        $documentXml = $zip->getFromName('word/document.xml');
        $fotoGenerada = $zip->getFromName('word/media/foto_evaluado.jpg');
        $contentTypes = $zip->getFromName('[Content_Types].xml');
        if ($fotoGenerada === false) {
            $fotoGenerada = $zip->getFromName('word/media/foto_evaluado.png');
        }
        $zip->close();
        @unlink($path);

        $limites = InformeWordXml::limitesTablaPorMarcador($documentXml, 'DATOS GENERALES');
        $this->assertNotNull($limites);
        $antesDeLaTabla = substr($documentXml, 0, $limites[0]);
        $this->assertStringContainsString('wp:anchor', $antesDeLaTabla);
        $this->assertNotEmpty($fotoGenerada);
        if (str_contains((string) $contentTypes, 'foto_evaluado.jpg') || $fotoGenerada !== false) {
            $this->assertStringContainsString('Extension="jpg" ContentType="image/jpeg"', (string) $contentTypes);
        }
    }

    public function test_dimensiones_foto_respetan_maximo_alto_sin_deformar(): void
    {
        ['cx' => $cx, 'cy' => $cy] = InformeWordFoto::dimensionesEmu(640, 480, 240, 300);

        $this->assertLessThanOrEqual(300 * 9525, $cy);
        $this->assertLessThanOrEqual(240 * 9525, $cx);
        $this->assertEqualsWithDelta(640 / 480, $cx / $cy, 0.001);
    }

    public function test_compacta_espacio_antes_de_tabla_encabezado_v2(): void
    {
        Storage::fake('local');

        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_servicio' => 'poligrafo',
            'tipo_formulario' => 'preempleo',
        ]);

        $cuestionario = Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'preempleo',
            'seccion_actual' => 1,
            'total_secciones' => 5,
            'progreso_porcentaje' => 20,
            'completado' => false,
            'bloqueado' => false,
            'terminos_aceptados' => true,
            'terminos_aceptados_at' => now(),
        ]);

        $fotoBytes = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAYAAACNMs+9AAAAFUlEQVR42mP8z8BQz0AEYBxVSF+FABJADveWkH6oAAAAAElFTkSuQmCC'
        );
        $rutaFoto = "cuestionarios/fotos/{$cuestionario->id}/foto_candidato.png";
        Storage::disk('local')->put($rutaFoto, $fotoBytes);

        CuestionarioRespuesta::create([
            'cuestionario_id' => $cuestionario->id,
            'seccion' => 'datos_personales',
            'campo' => CuestionarioFotoCandidato::CAMPO,
            'valor' => $rutaFoto,
            'tipo_campo' => 'file',
            'requerido' => true,
        ]);

        $evaluado = $evaluado->fresh(['cuestionario']);
        $path = InformeWordExport::generar($orden, $evaluado);

        $zip = new ZipArchive();
        $zip->open($path);
        $documentXml = $zip->getFromName('word/document.xml');
        $zip->close();
        @unlink($path);

        $limites = InformeWordXml::limitesTablaPorMarcador($documentXml, 'DATOS GENERALES');
        $this->assertNotNull($limites);

        $tablaDatos = substr($documentXml, $limites[0], $limites[1] - $limites[0]);
        $this->assertStringContainsString('w:tblpX="4527"', $tablaDatos, 'La tabla queda a la derecha para la foto al costado');

        $antesDeLaTabla = substr($documentXml, 0, $limites[0]);
        $this->assertStringContainsString('wp:anchor', $antesDeLaTabla);
        $this->assertStringNotContainsString('wp:inline', $antesDeLaTabla);
    }

    public function test_v2_sin_foto_perfil_ni_anchors_y_datos_ancho_completo(): void
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'nombre' => 'Sin',
            'apellidos' => 'Foto Test',
            'tipo_servicio' => 'poligrafo',
            'tipo_formulario' => 'preempleo',
            'resultado' => 'aprobado',
        ]);

        $path = InformeWordExport::generar($orden, $evaluado);
        $zip = new ZipArchive();
        $zip->open($path);
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();
        @unlink($path);

        $lim = InformeWordXml::limitesTablaPorMarcador($xml, 'DATOS GENERALES');
        $this->assertNotNull($lim);
        $antesDatos = substr($xml, 0, $lim[0]);
        $this->assertSame(0, substr_count($antesDatos, 'wp:anchor'), 'No debe quedar foto de perfil flotante en cabecera');
        $this->assertStringNotContainsString('wp:inline', $antesDatos, 'Sin foto candidato no debe insertar imagen arriba');

        $tabla = substr($xml, $lim[0], $lim[1] - $lim[0]);
        $this->assertStringNotContainsString('w:tblpPr', $tabla);
        $this->assertStringContainsString('w:w="10915"', $tabla);
    }

    public function test_v2_resultado_deja_solo_la_opcion_seleccionada(): void
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_servicio' => 'poligrafo',
            'tipo_formulario' => 'preempleo',
            'resultado' => 'aprobado',
        ]);

        $path = InformeWordExport::generar($orden, $evaluado);
        $zip = new ZipArchive();
        $zip->open($path);
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();
        @unlink($path);

        $lim = InformeWordXml::limitesTablaPorMarcador($xml, 'RESULTADO');
        $this->assertNotNull($lim);
        $tabla = substr($xml, $lim[0], $lim[1] - $lim[0]);
        $filas = InformeWordXml::filasTabla($tabla);
        $this->assertCount(2, $filas, 'Título + la opción marcada');
        $this->assertStringNotContainsString('Indicación de mentira', $tabla);
        $this->assertStringNotContainsString('Aspecto que origina la excepción', $tabla);
        $this->assertStringContainsString('[ X ]', $tabla);
        $this->assertStringContainsString('APROBADO', InformeWordXml::textoCelda(InformeWordXml::celdasFila($filas[1])[0]));
        $this->assertStringNotContainsString('NO APROBADO', $tabla);
    }

    public function test_layout_secciones_periodica_sin_huecos_y_con_keep_next(): void
    {
        $zip = new ZipArchive();
        $zip->open(resource_path('templates/informe-poligrafo-periodica.docx'));
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        $pos = strpos($xml, 'Proceso:');
        $inicio = strrpos(substr($xml, 0, $pos), '<w:tbl>');
        $antes = substr($xml, 0, $inicio);
        $antes = InformeWordXml::reemplazarParrafosAncladosPorTexto($antes);
        $antes = InformeWordXml::quitarParrafosVacios($antes);
        $this->assertStringContainsString('INFORMACIÓN CONFIDENCIAL', $antes);
        $this->assertSame(0, substr_count($antes, 'wp:anchor'));

        $posLabor = strpos($xml, 'INFORMACIÓN LABORAL');
        $xml = InformeWordXml::compactarEntreTablasPorMarcadores($xml, 'INFORMACIÓN LABORAL', ' LABORAL COMPLEMENTARIA');
        $finLabor = strpos($xml, '</w:tbl>', $posLabor) + 8;
        $posComplementaria = strpos($xml, ' LABORAL COMPLEMENTARIA', $posLabor);
        $inicioComplementaria = strrpos(substr($xml, 0, $posComplementaria), '<w:tbl>');
        $entre = substr($xml, $finLabor, $inicioComplementaria - $finLabor);
        $this->assertLessThan(50, strlen($entre));

        $xml = InformeWordXml::reemplazarTablaPorMarcador($xml, ' LABORAL COMPLEMENTARIA', function (string $tabla): string {
            return InformeWordXml::aplicarKeepNextFilaTitulo($tabla, 0);
        });
        $posTitulo = strpos($xml, ' LABORAL COMPLEMENTARIA');
        $inicioTabla = strrpos(substr($xml, 0, $posTitulo), '<w:tbl>');
        $finTabla = strpos($xml, '</w:tbl>', $posTitulo) + 8;
        $tabla = substr($xml, $inicioTabla, $finTabla - $inicioTabla);
        $this->assertStringContainsString('w:keepNext', $tabla);
        $this->assertStringContainsString('w:cantSplit', $tabla);
    }

    public function test_calcula_totales_de_deudas(): void
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_servicio' => 'poligrafo',
            'tipo_formulario' => 'periodica',
        ]);

        $cuestionario = Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'periodica',
            'seccion_actual' => 5,
            'total_secciones' => 5,
            'progreso_porcentaje' => 100,
            'completado' => true,
            'bloqueado' => false,
            'terminos_aceptados' => true,
            'terminos_aceptados_at' => now(),
        ]);

        CuestionarioRespuesta::guardarTabla($cuestionario->id, 'situacion_economica', 'deudas', [[
            'entidad' => 'Banco Industrial',
            'monto' => '45000',
            'saldo' => '28000',
            'cuota' => '1150',
            'motivo' => 'Préstamo personal',
            'antiguedad' => '3 años',
            'estatus' => 'al_dia',
            'meses_atraso' => '',
        ]]);

        $evaluado = $evaluado->fresh(['cuestionario']);
        $path = InformeWordExport::generar($orden, $evaluado);

        $zip = new ZipArchive();
        $zip->open($path);
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();
        @unlink($path);

        $this->assertStringContainsString('Q.' . "\xc2\xa0" . '45,000.00', $xml);
        $this->assertStringContainsString('Q.' . "\xc2\xa0" . '28,000.00', $xml);
        $this->assertStringContainsString('Q.' . "\xc2\xa0" . '1,150.00', $xml);
        $this->assertStringContainsString('w:w="1900"', $xml);
    }

    public function test_rellena_narrativas_y_quita_placeholders_preempleo(): void
    {
        $poligrafista = User::factory()->create(['name' => 'Polígrafo Demo Test', 'role_as' => 2]);

        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_servicio' => 'poligrafo',
            'tipo_formulario' => 'preempleo',
            'nombre' => 'Carlos',
            'apellidos' => 'Narrativas Demo',
            'poligrafista_id' => $poligrafista->id,
            'resultado' => 'aprobado',
        ]);

        $cuestionario = Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'preempleo',
            'seccion_actual' => 5,
            'total_secciones' => 5,
            'progreso_porcentaje' => 100,
            'completado' => true,
            'bloqueado' => false,
            'terminos_aceptados' => true,
            'terminos_aceptados_at' => now(),
        ]);

        CuestionarioRespuesta::guardarRespuestas($cuestionario->id, 'datos_personales', [
            'licencia_conducir' => 'si',
        ]);

        CuestionarioRespuesta::guardarRespuestas($cuestionario->id, 'antecedentes', [
            'salud_preocupaciones' => 'Ninguna',
            'salud_estado_general' => 'bueno',
            'salud_atencion_psicologica' => 'no',
            'habito_tiempo_libre' => 'Lectura',
            'habito_alcohol_frecuencia' => 'nunca',
            'habito_tabaco' => 'no',
            'sustancias_usadas' => ['ninguna'],
            'judicial_01' => 'No, sin antecedentes penales.',
            'comp_sindicato' => 'No pertenezco a sindicatos.',
            'comp_familiar_empresa' => 'No tengo familiares en la empresa.',
            'comp_como_se_entero' => 'Portal de empleos.',
            'comp_licencia_conducir' => 'Tipo B vigente.',
            'comp_condiciones_laborales' => 'De acuerdo con las condiciones.',
            'comp_metas' => 'Crecimiento profesional.',
            'comp_cualidades_defectos' => 'Responsable y puntual.',
            'comp_redes_usuario' => '@demo_test',
        ]);

        \App\Models\EvaluadorNota::guardarNota(
            $evaluado->id,
            \App\Support\InformeWordBloquesEvaluador::NOTA_RECOMENDACIONES,
            '',
            'Recomendación demo: candidato idóneo para el puesto.',
            null
        );

        $evaluado = $evaluado->fresh(['cuestionario', 'poligrafista']);
        $path = InformeWordExport::generar($orden, $evaluado);

        $zip = new ZipArchive();
        $zip->open($path);
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();
        @unlink($path);

        $this->assertStringContainsString('Estado general: Bueno', $xml);
        $this->assertStringContainsString('Tiempo libre: Lectura', $xml);
        $this->assertStringContainsString('Sustancias declaradas: Ninguna', $xml);
        $this->assertStringContainsString('antecedentes penales y policiacos', $xml);
        $this->assertStringContainsString('Recomendación demo: candidato idóneo para el puesto.', $xml);
        $this->assertStringContainsString('Carlos', $xml);
        $this->assertStringContainsString('Narrativas Demo', $xml);
        $this->assertStringNotContainsString('Polígrafo Demo Test', $xml);
        $this->assertStringContainsString('Portal de empleos.', $xml);
        $this->assertStringNotContainsString('xxxxx', $xml);
        $this->assertStringNotContainsString('xzxxx', $xml);
        $this->assertStringNotContainsString('XXXXXXXX', $xml);
        $this->assertStringContainsString('Stefanie', $xml);
        $this->assertStringContainsString('9245', $xml);
    }

    public function test_resultado_poligrafico_no_aprobado_marca_di(): void
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_servicio' => 'poligrafo',
            'tipo_formulario' => 'preempleo',
            'resultado' => 'no_aprobado',
        ]);

        $path = InformeWordExport::generar($orden, $evaluado);

        $zip = new ZipArchive();
        $zip->open($path);
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();
        @unlink($path);

        // Plantilla v2 parte "PREGUNTA RELEVANTE" en varios runs; validar texto concatenado.
        preg_match_all('/<w:t(?:\s+xml:space="preserve")?>(.*?)<\/w:t>/s', $xml, $m);
        $texto = html_entity_decode(implode('', $m[1] ?? []), ENT_QUOTES | ENT_XML1, 'UTF-8');
        $this->assertStringContainsString('PREGUNTA RELEVANTE', $texto);
        $this->assertGreaterThanOrEqual(1, substr_count($texto, 'DI'));
    }

    public function test_pdf_de_papeleria_seleccionado_se_lista_por_nombre_sin_embeber_paginas(): void
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_servicio' => 'socioeconomico',
            'tipo_formulario' => 'preempleo',
        ]);

        DocumentoEvaluado::factory()->create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_documento' => 'cv',
            'nombre_original' => 'curriculum-pesado.pdf',
            'mime_type' => 'application/pdf',
            'tamano' => 8_000_000,
        ]);

        $userId = User::factory()->create(['role_as' => 3])->id;
        InformeWordAnexosPapeleria::guardarSeleccion($evaluado->id, ['cv'], $userId);

        $path = InformeWordExport::generar($orden, $evaluado->fresh());

        $zip = new ZipArchive();
        $this->assertTrue($zip->open($path) === true);
        $xml = $zip->getFromName('word/document.xml');
        $nombres = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $nombres[] = $zip->getNameIndex($i);
        }
        $zip->close();
        @unlink($path);

        $this->assertIsString($xml);
        $this->assertStringContainsString('curriculum-pesado.pdf', $xml);
        $this->assertStringContainsString('[PDF]', $xml);
        $this->assertEmpty(array_filter(
            $nombres,
            static fn ($nombre): bool => is_string($nombre) && str_contains($nombre, 'anexo_papeleria_pdf_')
        ));
    }

    public function test_preparar_media_rechaza_archivo_de_mas_de_cinco_mb(): void
    {
        $grande = sys_get_temp_dir() . '/repro_anexo_pesado_' . uniqid() . '.jpg';
        file_put_contents($grande, str_repeat('a', 5_000_001));
        $this->assertNull(InformeWordFoto::prepararMedia($grande));
        @unlink($grande);

        $chica = sys_get_temp_dir() . '/repro_anexo_chica_' . uniqid() . '.png';
        $canvas = imagecreatetruecolor(200, 160);
        $this->assertNotFalse($canvas);
        imagepng($canvas, $chica, 6);
        imagedestroy($canvas);

        $media = InformeWordFoto::prepararMedia($chica);
        @unlink($chica);

        $this->assertIsArray($media);
        $this->assertNotSame('', $media['bytes'] ?? '');
    }

    public function test_preparar_media_acepta_jpeg_de_muchos_mp_si_pesa_poco(): void
    {
        $ruta = sys_get_temp_dir() . '/repro_mp_' . uniqid() . '.jpg';
        $canvas = imagecreatetruecolor(4000, 2500);
        $this->assertNotFalse($canvas);
        imagejpeg($canvas, $ruta, 40);
        imagedestroy($canvas);

        $this->assertLessThan(5_000_000, (int) filesize($ruta));
        $media = InformeWordFoto::prepararMedia($ruta);
        @unlink($ruta);

        $this->assertIsArray($media);
        $this->assertNotSame('', $media['bytes'] ?? '');
    }
}

