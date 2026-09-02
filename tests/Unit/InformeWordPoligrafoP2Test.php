<?php

namespace Tests\Unit;

use App\Models\Cuestionario;
use App\Models\CuestionarioRespuesta;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Support\InformeWordExport;
use App\Support\InformeWordXml;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use ZipArchive;

/** Polígrafo 21-ago: expareja en texto, hermanos extra, tatuajes sin borrar encabezado. */
class InformeWordPoligrafoP2Test extends TestCase
{
    use RefreshDatabase;

    public function test_poligrafo_traslada_expareja_hermanos_extra_y_todos_los_tatuajes(): void
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

        CuestionarioRespuesta::guardarRespuestas($cuestionario->id, 'informacion_familiar', [
            'tuvo_matrimonio_union_hijos' => 'si',
            'tiene_hermanos' => 'si',
            'expareja_nombre' => 'Mario Expareja P2',
            'expareja_tipo_relacion' => 'union_libre',
            'expareja_tiempo_relacion' => '3 años',
            'expareja_hijos_comun' => 'no',
            'expareja_problemas_legales' => 'no',
        ]);
        $hermanos = [];
        for ($i = 1; $i <= 6; $i++) {
            $hermanos[] = [
                'nombre' => 'Hermano Extra '.$i,
                'edad' => (string) (18 + $i),
                'telefono' => '5555000'.$i,
                'direccion' => 'Zona '.$i,
                'ocupacion' => 'Oficio '.$i,
            ];
        }
        CuestionarioRespuesta::guardarTabla($cuestionario->id, 'informacion_familiar', 'hermanos', $hermanos);
        CuestionarioRespuesta::guardarRespuestas($cuestionario->id, 'antecedentes', [
            'tiene_tatuajes' => 'si',
        ]);
        CuestionarioRespuesta::guardarTabla($cuestionario->id, 'antecedentes', 'tatuajes', [
            ['ubicacion' => 'Brazo derecho', 'tamano' => '15 cms', 'descripcion' => 'Anime 1', 'tiempo' => '4 años', 'visible_uniforme' => 'si', 'significado' => 'Dibujo'],
            ['ubicacion' => 'Brazo izquierdo', 'tamano' => '15 cms', 'descripcion' => 'Anime 2', 'tiempo' => '4 años', 'visible_uniforme' => 'si', 'significado' => ''],
            ['ubicacion' => 'Antebrazo izquierdo', 'tamano' => '20 cms', 'descripcion' => 'Power Rangers', 'tiempo' => '4 años', 'visible_uniforme' => 'si', 'significado' => ''],
            ['ubicacion' => 'Muñeca izquierda', 'tamano' => '10 cms', 'descripcion' => 'Superman', 'tiempo' => '4 años', 'visible_uniforme' => 'si', 'significado' => ''],
            ['ubicacion' => 'Hombro derecho', 'tamano' => '20 cms', 'descripcion' => 'Camus', 'tiempo' => '4 años', 'visible_uniforme' => 'no', 'significado' => ''],
        ]);

        $path = InformeWordExport::generar($orden->fresh(), $evaluado->fresh(['cuestionario', 'orden.empresa', 'sede']));
        $zip = new ZipArchive();
        $this->assertTrue($zip->open($path) === true);
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();
        @unlink($path);
        $this->assertIsString($xml);

        $plano = html_entity_decode(preg_replace('/<[^>]+>/', '', $xml) ?? '', ENT_QUOTES | ENT_XML1, 'UTF-8');

        $this->assertTrue(str_contains($plano, 'Mario Expareja P2'), 'Falta expareja en polígrafo');
        for ($i = 1; $i <= 6; $i++) {
            $this->assertTrue(str_contains($plano, 'Hermano Extra '.$i), "Falta hermano {$i}");
        }
        $this->assertTrue(str_contains($plano, 'Antebrazo izquierdo'));
        $this->assertTrue(str_contains($plano, 'Muñeca izquierda'));
        $this->assertTrue(str_contains($plano, 'Hombro derecho'));

        $lim = InformeWordXml::limitesTablaPorMarcador($xml, 'TATUAJES');
        $this->assertNotNull($lim);
        $tabla = substr($xml, $lim[0], $lim[1] - $lim[0]);
        $this->assertTrue(str_contains($tabla, 'Ubicación'));
        $this->assertTrue(str_contains($tabla, 'Nivel de riesgo'));
        $this->assertTrue(str_contains($tabla, 'Brazo derecho'));
        $this->assertSame(1, substr_count($tabla, 'Nivel de riesgo'));
    }
}
