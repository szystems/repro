<?php

namespace Tests\Unit;

use App\Models\Cuestionario;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Support\InformeWordExport;
use App\Support\InformeWordPreguntasPoligraficas;
use App\Support\InformeWordXml;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use ZipArchive;

/** Sprint M M-P4 — columna Respuesta (No / Sí) de preguntas VSA/polígrafo. */
class InformeWordSprintMLoteETest extends TestCase
{
    use RefreshDatabase;

    public function test_normaliza_si_no_y_vacio_no_es_guion(): void
    {
        $this->assertSame('No', InformeWordPreguntasPoligraficas::respuestaParaWord(''));
        $this->assertSame('No', InformeWordPreguntasPoligraficas::respuestaParaWord('—'));
        $this->assertSame('No', InformeWordPreguntasPoligraficas::respuestaParaWord('NO'));
        $this->assertSame('Sí', InformeWordPreguntasPoligraficas::respuestaParaWord('SI'));
        $this->assertSame('Sí', InformeWordPreguntasPoligraficas::respuestaParaWord('si'));
        $this->assertSame('a veces', InformeWordPreguntasPoligraficas::respuestaParaWord('a veces'));
    }

    public function test_plantilla_y_peri_arrancan_respuesta_no(): void
    {
        $pre = $this->evaluado('poligrafo', 'preempleo');
        $peri = $this->evaluado('vsa', 'periodica');

        foreach (InformeWordPreguntasPoligraficas::filas($pre->id, $pre) as $fila) {
            $this->assertSame('No', $fila['respuesta']);
        }
        foreach (InformeWordPreguntasPoligraficas::filas($peri->id, $peri) as $fila) {
            $this->assertSame('No', $fila['respuesta']);
        }
    }

    public function test_vsa_periodica_vuelca_si_y_no_en_la_columna_respuesta(): void
    {
        $evaluado = $this->evaluado('vsa', 'periodica');
        InformeWordPreguntasPoligraficas::guardarDesdeRequest($evaluado->id, [
            ['pregunta' => '¿Usted ha realizado robos de productos en su empleo actual?', 'respuesta' => 'NO', 'resultado' => 'NDI'],
            ['pregunta' => '¿Usted ha realizado robos de dinero en su empleo actual?', 'respuesta' => 'SI', 'resultado' => 'NDI'],
            ['pregunta' => '¿Ha realizado alguna actividad ilegal para generar sobrantes en su unidad?', 'respuesta' => '', 'resultado' => 'NDI'],
        ], null);

        $xml = $this->xmlDe($evaluado);
        $tabla = $this->tabla($xml, 'PREGUNTA RELEVANTE');
        $filas = InformeWordXml::filasTabla($tabla);
        $this->assertGreaterThanOrEqual(4, count($filas));

        $r1 = array_map([InformeWordXml::class, 'textoCelda'], InformeWordXml::celdasFila($filas[1]));
        $r2 = array_map([InformeWordXml::class, 'textoCelda'], InformeWordXml::celdasFila($filas[2]));
        $r3 = array_map([InformeWordXml::class, 'textoCelda'], InformeWordXml::celdasFila($filas[3]));

        $this->assertSame('No', $r1[2] ?? null);
        $this->assertSame('Sí', $r2[2] ?? null);
        $this->assertSame('No', $r3[2] ?? null, 'Vacío debe volcar No de plantilla, no un guión');
        $this->assertStringNotContainsString('—', $r1[2] ?? '');
        $this->assertStringNotContainsString('—', $r2[2] ?? '');
        $this->assertStringNotContainsString('—', $r3[2] ?? '');
    }

    public function test_poligrafo_preempleo_tambien_vuelca_respuesta(): void
    {
        $evaluado = $this->evaluado('poligrafo', 'preempleo');
        InformeWordPreguntasPoligraficas::guardarDesdeRequest($evaluado->id, [
            ['pregunta' => '¿Cometió usted delitos en empleos anteriores?', 'respuesta' => 'Sí', 'resultado' => 'DI', 'puntuacion' => ''],
        ], null);

        $xml = $this->xmlDe($evaluado);
        $tabla = $this->tabla($xml, 'PREGUNTA RELEVANTE');
        $filas = InformeWordXml::filasTabla($tabla);
        $celdas = array_map([InformeWordXml::class, 'textoCelda'], InformeWordXml::celdasFila($filas[1]));

        $this->assertSame('Sí', $celdas[2] ?? null);
        $this->assertSame('DI', $celdas[3] ?? null);
    }

    private function evaluado(string $servicio, string $formulario): EvaluadoOrden
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_servicio' => $servicio,
            'tipo_formulario' => $formulario,
            'nombre' => 'UAT',
            'apellidos' => 'LoteE',
        ]);
        $tipoCuestionario = $servicio === 'socioeconomico' ? 'socioeconomico' : $formulario;
        Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => $tipoCuestionario,
            'seccion_actual' => $tipoCuestionario === 'socioeconomico' ? 6 : 5,
            'total_secciones' => $tipoCuestionario === 'socioeconomico' ? 6 : 5,
            'completado' => true,
        ]);

        return $evaluado->fresh();
    }

    private function xmlDe(EvaluadoOrden $evaluado): string
    {
        $path = InformeWordExport::generar(
            $evaluado->orden,
            $evaluado->fresh(['cuestionario', 'orden.empresa', 'sede'])
        );
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
}
