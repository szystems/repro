<?php

namespace Tests\Unit;

use App\Models\Cuestionario;
use App\Models\EvaluadorNota;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Support\InformeWordExport;
use App\Support\InformeWordResultado;
use App\Support\InformeWordXml;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use ZipArchive;

/** Sprint M lote B — resultado único 1ª+última hoja (M-P3) y clasificación socio (M-S3). */
class InformeWordSprintMLoteBTest extends TestCase
{
    use RefreshDatabase;

    public function test_aprobado_marca_primera_y_ultima_hoja_y_cambia_veracidad(): void
    {
        $xml = $this->xmlPoli(['resultado' => 'aprobado']);
        $primera = $this->tabla($xml, 'RESULTADO DE EVALUACIÓN');
        $ultima = $this->tabla($xml, 'NO HAY INDICACIÓN DE MENTIRA/ APROBADO');
        $plano = $this->textoPlano($xml);

        $this->assertMismaMarca($primera, 'APROBADO');
        $this->assertMismaMarca($ultima, 'APROBADO');
        $this->assertStringContainsString(InformeWordResultado::FRASE_SI_VERACIDAD, $plano);
        $this->assertStringNotContainsString('NO RESPONDIÓ CON VERACIDAD', $plano);
        $this->assertStringNotContainsString(InformeWordResultado::MARCADOR_PREGUNTAS_DI, $plano);
    }

    public function test_no_aprobado_replica_detalle_de_mentira_en_ultima_hoja(): void
    {
        $xml = $this->xmlPoli(['resultado' => 'no_aprobado'], [
            InformeWordResultado::NOTA_INDICACION_MENTIRA => 'preguntas R2 y R3',
        ]);
        $primera = $this->tabla($xml, 'RESULTADO DE EVALUACIÓN');
        $ultima = $this->tabla($xml, 'DI: INDICACIÓN DE MENTIRA');
        $plano = $this->textoPlano($xml);

        $this->assertMismaMarca($primera, 'NO APROBADO');
        $this->assertMismaMarca($ultima, 'NO APROBADO');
        $this->assertStringContainsString('preguntas R2 y R3', $primera);
        $this->assertStringContainsString('preguntas R2 y R3', $plano);
        $this->assertStringContainsString(InformeWordResultado::FRASE_NO_VERACIDAD, $plano);
        $this->assertStringNotContainsString(InformeWordResultado::MARCADOR_PREGUNTAS_DI, $plano);
        $this->assertStringNotContainsString(InformeWordResultado::FRASE_NO_VERACIDAD_PLANTILLA, $plano);
    }

    public function test_excepcion_replica_aspecto_en_ultima_hoja(): void
    {
        $xml = $this->xmlPoli(['resultado' => 'aprobado_excepcion'], [
            InformeWordResultado::NOTA_ASPECTO_EXCEPCION => 'faltante no reportado SprintM',
        ]);
        $primera = $this->tabla($xml, 'RESULTADO DE EVALUACIÓN');
        $ultima = $this->tabla($xml, InformeWordResultado::MARCADOR_ULTIMA_HOJA);
        $plano = $this->textoPlano($xml);

        $this->assertMismaMarca($primera, 'EXCEPCION');
        $this->assertMismaMarca($ultima, 'CONFIABLE CON EXCEPCION');
        $this->assertStringContainsString('faltante no reportado SprintM', $primera);
        $this->assertStringContainsString('faltante no reportado SprintM', $ultima);
        $this->assertStringContainsString(InformeWordResultado::FRASE_SI_VERACIDAD, $plano);
        $this->assertStringNotContainsString(InformeWordResultado::FRASE_EXCEPCION_VERACIDAD, $plano);
        $this->assertStringNotContainsString(InformeWordResultado::MARCADOR_PREGUNTAS_DI, $plano);
        $this->assertStringNotContainsString(InformeWordResultado::MARCA, $primera);
        $this->assertStringNotContainsString(InformeWordResultado::MARCA, $ultima);
    }

    public function test_vsa_periodica_tambien_marca_ultima_hoja(): void
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_servicio' => 'vsa',
            'tipo_formulario' => 'periodica',
            'nombre' => 'Briyith',
            'apellidos' => 'LoteB',
            'resultado' => 'no_aprobado',
        ]);
        Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'periodica',
            'seccion_actual' => 5,
            'total_secciones' => 5,
            'completado' => true,
        ]);

        $xml = $this->xmlDe($orden, $evaluado);
        $ultima = $this->tabla($xml, 'DI: INDICACIÓN DE MENTIRA');
        $this->assertMismaMarca($ultima, 'NO APROBADO');
    }

    public function test_socio_marca_clasificacion_en_primera_y_conclusiones(): void
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_servicio' => 'socioeconomico',
            'tipo_formulario' => 'preempleo',
            'nombre' => 'Carmen',
            'apellidos' => 'LoteB',
            'resultado' => 'tipo_c',
        ]);
        Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'socioeconomico',
            'seccion_actual' => 6,
            'total_secciones' => 6,
            'completado' => true,
        ]);

        $xml = $this->xmlDe($orden, $evaluado);
        $primera = $this->tabla($xml, InformeWordResultado::MARCADOR_CLASIFICACION_SOCIO);
        $ultima = $this->tabla($xml, InformeWordResultado::MARCADOR_CONCLUSIONES_SOCIO);

        $this->assertMismaMarca($primera, 'Tipo C');
        $this->assertMismaMarca($ultima, 'Tipo C');
        $this->assertStringNotContainsString('NO APROBADO', $primera);
    }

    public function test_pendiente_no_marca_cuadros(): void
    {
        $xml = $this->xmlPoli(['resultado' => 'pendiente']);
        $primera = $this->tabla($xml, 'RESULTADO DE EVALUACIÓN');
        $ultima = $this->tabla($xml, InformeWordResultado::MARCADOR_ULTIMA_HOJA);

        $this->assertStringNotContainsString(InformeWordResultado::MARCA, $primera);
        $this->assertStringNotContainsString(InformeWordResultado::MARCA, $ultima);
    }

    /** @param array<string, mixed> $atributos */
    /** @param array<string, string> $notas */
    private function xmlPoli(array $atributos, array $notas = []): string
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create(array_merge([
            'orden_id' => $orden->id,
            'tipo_servicio' => 'poligrafo',
            'tipo_formulario' => 'preempleo',
            'nombre' => 'Ericka',
            'apellidos' => 'LoteB',
        ], $atributos));
        Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'preempleo',
            'seccion_actual' => 6,
            'total_secciones' => 6,
            'completado' => true,
        ]);
        foreach ($notas as $seccion => $texto) {
            EvaluadorNota::guardarNota($evaluado->id, $seccion, '', $texto, null);
        }

        return $this->xmlDe($orden, $evaluado);
    }

    private function assertMismaMarca(string $tabla, string $etiquetaEsperada, bool $conX = false): void
    {
        $opciones = [];
        foreach (InformeWordXml::filasTabla($tabla) as $fila) {
            $celdas = InformeWordXml::celdasFila($fila);
            $texto = $celdas !== []
                ? InformeWordXml::textoCelda($celdas[0])
                : InformeWordXml::textoFila($fila);
            if (str_contains(mb_strtoupper($texto), 'ASPECTO QUE ORIGINA')) {
                continue;
            }
            $esOpcion = InformeWordResultado::opcionDeTexto($texto) !== null
                || InformeWordResultado::opcionDeTextoUltimaHoja($texto) !== null
                || InformeWordResultado::opcionDeTextoSocio($texto) !== null;
            if ($esOpcion) {
                $opciones[] = $texto;
            }
        }

        $this->assertCount(1, $opciones, 'Debe quedar una sola opción en el cuadro: '.$tabla);
        $this->assertStringContainsString($etiquetaEsperada, $opciones[0]);
        if ($conX) {
            $this->assertStringContainsString(InformeWordResultado::MARCA, $opciones[0]);
        } else {
            $this->assertStringNotContainsString(InformeWordResultado::MARCA, $tabla);
        }
    }

    private function tabla(string $xml, string $marcador): string
    {
        $limites = InformeWordXml::limitesTablaPorMarcador($xml, $marcador);
        $this->assertNotNull($limites, "Falta la tabla {$marcador}");

        return substr($xml, $limites[0], $limites[1] - $limites[0]);
    }

    private function xmlDe(Orden $orden, EvaluadoOrden $evaluado): string
    {
        $path = InformeWordExport::generar($orden->fresh(), $evaluado->fresh(['cuestionario', 'orden.empresa', 'sede', 'poligrafista', 'responsable']));
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
