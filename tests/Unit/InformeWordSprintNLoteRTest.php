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

/** Sprint N N-R2 — sin [ X ] en poli/VSA; conclusión distinto si aprobado o no. */
class InformeWordSprintNLoteRTest extends TestCase
{
    use RefreshDatabase;

    public function test_no_aprobado_une_frase_y_lista_preguntas_sin_x(): void
    {
        $xml = $this->xmlPoli('no_aprobado', [
            InformeWordResultado::NOTA_INDICACION_MENTIRA => 'PREGUNTA R5-R4 PERIODICAS',
        ]);
        $plano = $this->textoPlano($xml);
        $primera = $this->tabla($xml, 'RESULTADO DE EVALUACIÓN');

        $this->assertStringContainsString('NO APROBADO', $primera);
        $this->assertStringNotContainsString(InformeWordResultado::MARCA, $primera);
        $this->assertStringContainsString(InformeWordResultado::FRASE_NO_VERACIDAD, $plano);
        $this->assertStringContainsString('PREGUNTA R5-R4 PERIODICAS', $plano);
        $this->assertStringNotContainsString(InformeWordResultado::MARCADOR_PREGUNTAS_DI, $plano);
        $this->assertStringNotContainsString(InformeWordResultado::FRASE_NO_VERACIDAD_PLANTILLA, $plano);
        $this->assertStringNotContainsString(InformeWordResultado::MARCA, $this->tabla($xml, 'DI: INDICACIÓN DE MENTIRA'));
        $this->assertRunNegroNegrita($xml, InformeWordResultado::FRASE_NO_VERACIDAD);
    }

    public function test_aprobado_frase_en_negro_sin_preguntas_ni_tabla_di(): void
    {
        $xml = $this->xmlPoli('aprobado');
        $plano = $this->textoPlano($xml);

        $this->assertStringContainsString(InformeWordResultado::FRASE_SI_VERACIDAD, $plano);
        $this->assertStringNotContainsString(InformeWordResultado::MARCADOR_PREGUNTAS_DI, $plano);
        $this->assertStringNotContainsString(InformeWordResultado::MARCA, $this->tabla($xml, 'RESULTADO DE EVALUACIÓN'));
        $this->assertRunNegroNegrita($xml, InformeWordResultado::FRASE_SI_VERACIDAD);
    }

    public function test_excepcion_usa_frase_de_aprobado_sin_preguntas_y_sin_x(): void
    {
        $xml = $this->xmlPoli('aprobado_excepcion', [
            InformeWordResultado::NOTA_ASPECTO_EXCEPCION => 'PREUBA DE RESULTADO DE EXCEPCION',
        ]);
        $plano = $this->textoPlano($xml);
        $primera = $this->tabla($xml, 'RESULTADO DE EVALUACIÓN');
        $ultima = $this->tabla($xml, InformeWordResultado::MARCADOR_ULTIMA_HOJA);

        $this->assertStringContainsString(InformeWordResultado::FRASE_SI_VERACIDAD, $plano);
        $this->assertStringNotContainsString(InformeWordResultado::FRASE_EXCEPCION_VERACIDAD, $plano);
        $this->assertStringNotContainsString(InformeWordResultado::MARCADOR_PREGUNTAS_DI, $plano);
        $this->assertStringContainsString('PREUBA DE RESULTADO DE EXCEPCION', $ultima);
        $this->assertStringContainsString('ASPECTO QUE ORIGINA', mb_strtoupper($this->textoPlano($ultima)));
        $this->assertStringNotContainsString(InformeWordResultado::MARCA, $primera);
        $this->assertStringNotContainsString(InformeWordResultado::MARCA, $ultima);
        $this->assertRunNegroNegrita($xml, InformeWordResultado::FRASE_SI_VERACIDAD);
    }

    public function test_clasifica_al_evaluado_fijo_sin_a_la_ni_como(): void
    {
        $xml = $this->xmlPoli('aprobado');
        $plano = $this->textoPlano($xml);

        $this->assertStringContainsString(InformeWordResultado::FRASE_CLASIFICA, $plano);
        $this->assertStringNotContainsString('a la evaluado', $plano);
        $this->assertStringNotContainsString('evaluado(a) como', $plano);
        $this->assertRunNegroNegrita($xml, InformeWordResultado::FRASE_CLASIFICA);
    }

    /** @param array<string, string> $notas */
    private function xmlPoli(string $resultado, array $notas = []): string
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_servicio' => 'poligrafo',
            'tipo_formulario' => 'especifica',
            'nombre' => 'PRUEBA DE ESPECIFICA',
            'apellidos' => 'ESPECIFICA212',
            'resultado' => $resultado,
        ]);
        Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'especifica',
            'seccion_actual' => 5,
            'total_secciones' => 5,
            'completado' => true,
        ]);
        foreach ($notas as $seccion => $texto) {
            EvaluadorNota::guardarNota($evaluado->id, $seccion, '', $texto, null);
        }

        $path = InformeWordExport::generar($orden->fresh(), $evaluado->fresh(['cuestionario', 'orden.empresa', 'sede']));
        $zip = new ZipArchive();
        $this->assertTrue($zip->open($path) === true);
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();
        @unlink($path);
        $this->assertIsString($xml);

        return $xml;
    }

    private function assertRunNegroNegrita(string $xml, string $frase): void
    {
        $pos = strpos($xml, $frase);
        $this->assertNotFalse($pos);
        $antes = substr($xml, 0, $pos);
        preg_match_all('/<w:r(?:\s|>)/', $antes, $coincidencias, PREG_OFFSET_CAPTURE);
        $inicioRun = (int) $coincidencias[0][array_key_last($coincidencias[0])][1];
        $run = substr($xml, $inicioRun, 500);
        $this->assertStringContainsString('w:val="000000"', $run);
        $this->assertStringNotContainsString('FF0000', $run);
        $this->assertStringNotContainsString('w:highlight', $run);
        $this->assertStringContainsString('<w:b', $run);
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
