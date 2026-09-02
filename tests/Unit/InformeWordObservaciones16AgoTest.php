<?php

namespace Tests\Unit;

use App\Models\Cuestionario;
use App\Models\CuestionarioRespuesta;
use App\Models\DocumentoEvaluado;
use App\Models\EvaluadorNota;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Support\CuestionarioFotoCandidato;
use App\Support\InformeWordAnexosPapeleria;
use App\Support\InformeWordEconomico;
use App\Support\InformeWordExport;
use App\Support\InformeWordFoto;
use App\Support\InformeWordResultado;
use App\Support\InformeWordXml;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

/**
 * Observaciones del cliente 16-08-2026: Word que no abre al anexar papelería, foto sobre la tabla
 * en lugar de al costado, aspecto económico vacío y cuadro de resultado reducido a una fila.
 */
class InformeWordObservaciones16AgoTest extends TestCase
{
    use RefreshDatabase;

    private const PNG_1PX = 'iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAYAAACNMs+9AAAAFUlEQVR42mP8z8BQz0AEYBxVSF+FABJADveWkH6oAAAAAElFTkSuQmCC';

    public function test_papeleria_anexada_produce_docx_que_word_puede_abrir(): void
    {
        Storage::fake('local');

        $evaluado = $this->evaluadoConCuestionario();
        $this->agregarPapeleria($evaluado, ['dpi_archivo', 'antecedentes_penales']);

        $documentXml = $this->documentXmlGenerado($evaluado);

        $this->assertStringContainsString('DOCUMENTOS ADJUNTOS', $documentXml, 'La papelería seleccionada debe anexarse');
        $this->assertSame([], InformeWordXml::problemasEstructura($documentXml));
    }

    public function test_celda_de_papeleria_no_queda_anidada_dentro_de_otra_celda(): void
    {
        $celda = InformeWordXml::construirCeldaSimple(
            3600,
            InformeWordXml::establecerTextoCelda('<w:tc><w:tcPr><w:tcW w:w="10" w:type="dxa"/></w:tcPr><w:p/></w:tc>', 'Documento')
        );

        $this->assertSame(1, substr_count($celda, '<w:tc>'), 'Envolver una celda en otra rompe el esquema OOXML');
        $this->assertStringContainsString('w:w="3600"', $celda, 'Debe conservarse el ancho solicitado');
        $this->assertStringContainsString('Documento', $celda);
    }

    public function test_celda_de_papeleria_conserva_el_relleno_de_la_plantilla(): void
    {
        $celda = InformeWordXml::construirCeldaSimple(
            7200,
            InformeWordXml::establecerTextoCelda(
                '<w:tc><w:tcPr><w:tcW w:w="10" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="002060"/></w:tcPr><w:p/></w:tc>',
                'Descripción'
            )
        );

        $this->assertStringContainsString('w:fill="002060"', $celda);
        $this->assertStringContainsString('w:w="7200"', $celda);
    }

    public function test_estructura_detecta_celdas_anidadas(): void
    {
        $invalido = '<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
            . '<w:body><w:tbl><w:tr><w:tc><w:tc><w:p/></w:tc></w:tc></w:tr></w:tbl></w:body></w:document>';

        $this->assertTrue(InformeWordXml::esValido($invalido), 'El XML es well-formed pero fuera de esquema');
        $this->assertNotSame([], InformeWordXml::problemasEstructura($invalido));
    }

    public function test_foto_va_al_costado_de_datos_generales_y_no_encima(): void
    {
        Storage::fake('local');

        $evaluado = $this->evaluadoConCuestionario();
        $this->agregarFoto($evaluado);

        $documentXml = $this->documentXmlGenerado($evaluado);

        $limites = InformeWordXml::limitesTablaPorMarcador($documentXml, 'DATOS GENERALES');
        $this->assertNotNull($limites);

        $tablaDatos = substr($documentXml, $limites[0], $limites[1] - $limites[0]);
        $this->assertStringContainsString(
            'w:tblpX="4527"',
            $tablaDatos,
            'La tabla debe seguir posicionada a la derecha para dejar la foto a su costado'
        );

        $antesDeLaTabla = substr($documentXml, 0, $limites[0]);
        $this->assertStringContainsString('wp:anchor', $antesDeLaTabla, 'La foto usa el marco flotante de la plantilla');
        $this->assertStringNotContainsString(
            'wp:inline',
            $antesDeLaTabla,
            'La foto ya no debe insertarse centrada encima de la tabla'
        );
    }

    public function test_foto_reemplaza_la_silueta_de_la_plantilla(): void
    {
        Storage::fake('local');

        $evaluado = $this->evaluadoConCuestionario();
        $this->agregarFoto($evaluado);

        $path = InformeWordExport::generar($evaluado->orden, $evaluado->fresh(['cuestionario', 'orden']));

        $zip = new ZipArchive();
        $zip->open($path);
        $documentXml = (string) $zip->getFromName('word/document.xml');
        $relsXml = (string) $zip->getFromName('word/_rels/document.xml.rels');
        $zip->close();
        @unlink($path);

        $limites = InformeWordXml::limitesTablaPorMarcador($documentXml, 'DATOS GENERALES');
        $marco = InformeWordXml::parrafoMarcoFoto($documentXml, $limites[0]);
        $this->assertNotNull($marco);

        preg_match('/r:embed="(rId\d+)"/', $marco['xml'], $embed);
        $this->assertNotEmpty($embed);

        preg_match('/Id="' . $embed[1] . '"[^>]*Target="([^"]+)"/', $relsXml, $target);
        $this->assertNotEmpty($target, 'La relación de la imagen debe existir');
        $this->assertStringContainsString('foto_evaluado', $target[1], 'El marco debe apuntar a la foto del evaluado');
        $this->assertStringNotContainsString('a:srcRect', $marco['xml'], 'El recorte de la silueta desencuadraría la foto');
    }

    public function test_foto_conserva_su_proporcion_dentro_del_marco(): void
    {
        $marcoCx = 2981325;
        $marcoCy = 4413885;

        ['cx' => $cx, 'cy' => $cy] = InformeWordFoto::dimensionesEmuEnMarco(640, 480, $marcoCx, $marcoCy);

        $this->assertLessThanOrEqual($marcoCx, $cx);
        $this->assertLessThanOrEqual($marcoCy, $cy);
        $this->assertEqualsWithDelta(640 / 480, $cx / $cy, 0.001, 'La foto no debe deformarse');
    }

    public function test_aspecto_economico_usa_las_respuestas_del_cuestionario_cuando_no_hay_redaccion(): void
    {
        Storage::fake('local');

        $evaluado = $this->evaluadoConCuestionario();
        $this->agregarRespuestasEconomicas($evaluado->cuestionario);

        $documentXml = $this->documentXmlGenerado($evaluado);

        $limites = InformeWordXml::limitesTablaPorMarcador($documentXml, 'ASPECTO ECONÓMICO');
        $this->assertNotNull($limites);

        $tabla = substr($documentXml, $limites[0], $limites[1] - $limites[0]);
        $filas = InformeWordXml::filasTabla($tabla);

        $this->assertGreaterThan(1, count($filas), 'El apartado económico no debe quedar solo con el título');
        $this->assertStringContainsString('alquilada', $tabla);
        $this->assertStringContainsString('Pretensión salarial', $tabla);
        $this->assertStringContainsString('no ser fiador', $tabla);
    }

    public function test_narrativa_economica_resume_las_respuestas_declaradas(): void
    {
        $evaluado = $this->evaluadoConCuestionario();
        $this->agregarRespuestasEconomicas($evaluado->cuestionario);

        $narrativa = InformeWordEconomico::narrativa($evaluado->cuestionario->fresh());

        $this->assertStringContainsString('Vivienda: alquilada', $narrativa);
        $this->assertStringContainsString('Q. 1,500.00 de alquiler mensual', $narrativa);
        $this->assertStringContainsString('Indicó tener vehículo propio: Motocicleta 2019.', $narrativa);
        $this->assertStringContainsString('Indicó no tener deudas vigentes.', $narrativa);
    }

    public function test_redaccion_del_evaluador_tiene_prioridad_sobre_la_narrativa_automatica(): void
    {
        Storage::fake('local');

        $evaluado = $this->evaluadoConCuestionario();
        $this->agregarRespuestasEconomicas($evaluado->cuestionario);

        EvaluadorNota::guardarNota($evaluado->id, 'word_economico', '', 'Redacción propia del evaluador.', null);

        $documentXml = $this->documentXmlGenerado($evaluado);

        $this->assertStringContainsString('Redacción propia del evaluador.', $documentXml);
        $this->assertStringNotContainsString('Vivienda: alquilada', $documentXml);
    }

    public function test_cuadro_de_resultado_conserva_las_tres_opciones_de_la_plantilla(): void
    {
        $evaluado = $this->evaluadoConCuestionario(['resultado' => 'no_aprobado']);

        $documentXml = $this->documentXmlGenerado($evaluado);

        $limites = InformeWordXml::limitesTablaPorMarcador($documentXml, 'RESULTADO');
        $this->assertNotNull($limites);

        $tabla = substr($documentXml, $limites[0], $limites[1] - $limites[0]);
        $filas = InformeWordXml::filasTabla($tabla);

        $this->assertCount(2, $filas, 'N-R1: título + la opción elegida');
        $this->assertStringContainsString('NO APROBADO', $tabla);
        $this->assertStringNotContainsString('APROBADO CON EXCEPCION', mb_strtoupper(InformeWordXml::textoTablaConcatenado($tabla)));
    }

    public function test_cuadro_de_resultado_marca_solo_la_opcion_que_corresponde(): void
    {
        $evaluado = $this->evaluadoConCuestionario(['resultado' => 'no_aprobado']);

        $documentXml = $this->documentXmlGenerado($evaluado);
        $limites = InformeWordXml::limitesTablaPorMarcador($documentXml, 'RESULTADO');
        $tabla = substr($documentXml, $limites[0], $limites[1] - $limites[0]);

        $filasOpcion = [];
        foreach (InformeWordXml::filasTabla($tabla) as $fila) {
            $texto = InformeWordXml::textoFila($fila);
            if (InformeWordResultado::opcionDeTexto($texto) !== null) {
                $filasOpcion[] = $texto;
            }
        }

        $this->assertCount(1, $filasOpcion);
        $this->assertStringContainsString('NO APROBADO', $filasOpcion[0]);
        $this->assertStringNotContainsString(InformeWordResultado::MARCA, $tabla);
    }

    public function test_cuadro_de_resultado_incluye_el_detalle_escrito_por_el_evaluador(): void
    {
        $evaluado = $this->evaluadoConCuestionario(['resultado' => 'aprobado_excepcion']);

        EvaluadorNota::guardarNota(
            $evaluado->id,
            InformeWordResultado::NOTA_ASPECTO_EXCEPCION,
            '',
            'Faltante de producto no reportado.',
            null
        );

        $documentXml = $this->documentXmlGenerado($evaluado);
        $limites = InformeWordXml::limitesTablaPorMarcador($documentXml, 'RESULTADO');
        $tabla = substr($documentXml, $limites[0], $limites[1] - $limites[0]);

        $this->assertStringContainsString('Faltante de producto no reportado.', $tabla);
        $this->assertStringNotContainsString(InformeWordResultado::MARCA, $tabla);
    }

    public function test_resultado_pendiente_deja_el_cuadro_como_el_informe_original(): void
    {
        $evaluado = $this->evaluadoConCuestionario(['resultado' => 'pendiente']);

        $documentXml = $this->documentXmlGenerado($evaluado);
        $limites = InformeWordXml::limitesTablaPorMarcador($documentXml, 'RESULTADO');
        $tabla = substr($documentXml, $limites[0], $limites[1] - $limites[0]);

        $this->assertCount(4, InformeWordXml::filasTabla($tabla));
        $this->assertStringNotContainsString(InformeWordResultado::MARCA, $tabla);
    }

    /** @param array<string, mixed> $atributos */
    private function evaluadoConCuestionario(array $atributos = []): EvaluadoOrden
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create(array_merge([
            'orden_id' => $orden->id,
            'nombre' => 'Gerson',
            'apellidos' => 'Prueba Observaciones',
            'tipo_servicio' => 'poligrafo',
            'tipo_formulario' => 'preempleo',
        ], $atributos));

        Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'preempleo',
            'seccion_actual' => 5,
            'total_secciones' => 5,
            'progreso_porcentaje' => 100,
            'completado' => false,
            'bloqueado' => false,
            'terminos_aceptados' => true,
            'terminos_aceptados_at' => now(),
        ]);

        return $evaluado->fresh(['cuestionario', 'orden']);
    }

    private function documentXmlGenerado(EvaluadoOrden $evaluado): string
    {
        $evaluado = $evaluado->fresh(['cuestionario', 'orden', 'documentos']);
        $path = InformeWordExport::generar($evaluado->orden, $evaluado);

        $zip = new ZipArchive();
        $this->assertTrue($zip->open($path) === true, 'El .docx generado debe ser un ZIP legible');
        $documentXml = (string) $zip->getFromName('word/document.xml');
        $zip->close();
        @unlink($path);

        return $documentXml;
    }

    private function agregarFoto(EvaluadoOrden $evaluado): void
    {
        $cuestionario = $evaluado->cuestionario;
        $ruta = "cuestionarios/fotos/{$cuestionario->id}/foto_candidato.png";
        Storage::disk('local')->put($ruta, base64_decode(self::PNG_1PX));

        CuestionarioRespuesta::create([
            'cuestionario_id' => $cuestionario->id,
            'seccion' => 'datos_personales',
            'campo' => CuestionarioFotoCandidato::CAMPO,
            'valor' => $ruta,
            'tipo_campo' => 'file',
            'requerido' => true,
        ]);
    }

    /** @param list<string> $tipos */
    private function agregarPapeleria(EvaluadoOrden $evaluado, array $tipos): void
    {
        foreach ($tipos as $indice => $tipo) {
            $ruta = "documentos_evaluados/{$evaluado->id}/{$tipo}.png";
            Storage::disk('local')->put($ruta, base64_decode(self::PNG_1PX));

            DocumentoEvaluado::factory()->create([
                'evaluado_orden_id' => $evaluado->id,
                'tipo_documento' => $tipo,
                'ruta_archivo' => $ruta,
                'nombre_original' => $tipo . '.png',
                'mime_type' => 'image/png',
                'subido_por_tipo' => 'repro',
            ]);

            unset($indice);
        }

        InformeWordAnexosPapeleria::guardarSeleccion($evaluado->id, $tipos, null);
    }

    private function agregarRespuestasEconomicas(Cuestionario $cuestionario): void
    {
        $respuestas = [
            'tipo_vivienda' => 'alquilada',
            'monto_alquiler' => '1500',
            'personas_hogar' => '4',
            'personas_contribuyen_gastos' => '2',
            'dependientes_economicos' => '2',
            'econ_dependientes_detalle' => 'Dos hijos menores',
            'econ_ingresos_adicionales_detalle' => 'No posee ingresos adicionales',
            'econ_gastos_mensuales_aprox' => '3200',
            'econ_pretension_salarial' => '4500',
            'econ_posee_propiedades' => 'no',
            'econ_posee_vehiculos' => 'si',
            'econ_detalle_vehiculos' => 'Motocicleta 2019',
            'tiene_deudas' => 'no',
            'econ_es_fiador' => 'no',
            'econ_problemas_bancarios' => 'no',
            'econ_demandas_deudas' => 'no',
            'econ_problemas_sat' => 'no',
        ];

        foreach ($respuestas as $campo => $valor) {
            CuestionarioRespuesta::create([
                'cuestionario_id' => $cuestionario->id,
                'seccion' => 'situacion_economica',
                'campo' => $campo,
                'valor' => $valor,
                'tipo_campo' => 'text',
                'requerido' => false,
            ]);
        }
    }
}
