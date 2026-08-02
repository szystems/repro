<?php

namespace Tests\Unit;

use App\Models\Cuestionario;
use App\Models\CuestionarioRespuesta;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Models\User;
use App\Support\CuestionarioFotoCandidato;
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
            'notas_poligrafo' => 'Observación de prueba para Word.',
        ]);

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
        $this->assertStringContainsString('Carlos Demo Prueba', $xml);
        $this->assertStringContainsString('Analista de prueba', $xml);
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

        $this->assertStringContainsString('Prueba de Polígrafo Pre-empleo', $xml);
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

        $this->assertStringContainsString('Prueba de Polígrafo', $xml);
        $this->assertStringContainsString('Ana Periodica Test', $xml);
        $this->assertStringNotContainsString('HERMANOS:', $xml);
    }

    public function test_reemplaza_foto_del_evaluado_en_plantilla(): void
    {
        Storage::fake('local');

        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
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

        $plantillaZip = new ZipArchive();
        $plantillaZip->open(InformeWordExport::rutaPlantilla());
        $headerPlantilla = $plantillaZip->getFromName('word/header2.xml');
        $marcoPlantilla = $plantillaZip->getFromName('word/media/image4.png');
        $firmaPlantilla = $plantillaZip->getFromName('word/media/image2.png');
        $plantillaZip->close();

        $evaluado = $evaluado->fresh(['cuestionario']);
        $path = InformeWordExport::generar($orden, $evaluado);

        $zip = new ZipArchive();
        $zip->open($path);
        $headerXml = $zip->getFromName('word/header2.xml');
        $documentXml = $zip->getFromName('word/document.xml');
        $fotoGenerada = $zip->getFromName('word/media/foto_evaluado.png');
        if ($fotoGenerada === false) {
            $fotoGenerada = $zip->getFromName('word/media/foto_evaluado.jpg');
        }
        $marcoIntacto = $zip->getFromName('word/media/image4.png');
        $fotoFirma = $zip->getFromName('word/media/image2.png');
        $zip->close();
        @unlink($path);

        $this->assertSame($headerPlantilla, $headerXml, 'El encabezado no debe modificarse');
        $this->assertStringContainsString('wp:inline', $documentXml, 'La foto debe insertarse en el cuerpo del documento');
        $this->assertStringContainsString('Foto evaluado', $documentXml);
        $posFoto = strpos($documentXml, 'wp:inline');
        $posProceso = strpos($documentXml, 'Proceso:');
        $this->assertNotFalse($posFoto);
        $this->assertNotFalse($posProceso);
        $this->assertLessThan($posProceso, $posFoto, 'La foto debe ir inmediatamente antes de la tabla Proceso');
        $this->assertNotEmpty($fotoGenerada);
        $this->assertSame($marcoPlantilla, $marcoIntacto, 'El marco image4 de la plantilla no debe modificarse');
        $this->assertSame($firmaPlantilla, $fotoFirma, 'La firma del evaluador no debe ser reemplazada');
    }

    public function test_inserta_foto_jpeg_sin_gd_jpeg(): void
    {
        Storage::fake('local');

        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
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

        $this->assertStringContainsString('wp:inline', $documentXml);
        $this->assertNotEmpty($fotoGenerada);
        if (str_contains((string) $contentTypes, 'foto_evaluado.jpg') || $fotoGenerada !== false) {
            $this->assertStringContainsString('Extension="jpg" ContentType="image/jpeg"', (string) $contentTypes);
        }
    }

    public function test_dimensiones_foto_respetan_maximo_alto_sin_deformar(): void
    {
        ['cx' => $cx, 'cy' => $cy] = InformeWordFoto::dimensionesEmu(640, 480, 420, 230);

        $this->assertLessThanOrEqual(230 * 9525, $cy);
        $this->assertLessThanOrEqual(420 * 9525, $cx);
        $this->assertEqualsWithDelta(640 / 480, $cx / $cy, 0.001);
    }

    public function test_compacta_espacio_antes_de_tabla_proceso(): void
    {
        Storage::fake('local');

        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
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

        $posProceso = strpos($documentXml, 'Proceso:');
        $inicioTabla = strrpos(substr($documentXml, 0, $posProceso), '<w:tbl>');
        $antes = substr($documentXml, 0, $inicioTabla);
        $posFoto = strrpos($antes, 'wp:inline');
        $this->assertNotFalse($posFoto);

        $bloque = substr($antes, $posFoto);
        $this->assertStringNotContainsString('wp:anchor', $bloque, 'No debe quedar el marco flotante legacy');
        $this->assertLessThan(3500, strlen($bloque), 'Entre foto y tabla Proceso no debe haber bloques grandes de XML vacío');
        $this->assertMatchesRegularExpression('/<w:p\b[^>]*>.*?w:before="240".*?w:after="240".*?wp:inline/s', $antes);
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
            'notas_poligrafo' => 'Recomendación demo: candidato idóneo para el puesto.',
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
        $this->assertStringContainsString('Carlos Narrativas Demo', $xml);
        $this->assertStringContainsString('Polígrafo Demo Test', $xml);
        $this->assertStringContainsString('Portal de empleos.', $xml);
        $this->assertStringNotContainsString('xxxxx', $xml);
        $this->assertStringNotContainsString('xzxxx', $xml);
        $this->assertStringNotContainsString('XXXXXXXX', $xml);
        $this->assertStringNotContainsString('Stefanie9245 Rodrigo12871', $xml);
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

        $this->assertGreaterThan(1, substr_count($xml, '>DI<'));
    }
}
