<?php

namespace Tests\Unit;

use App\Models\DocumentoEvaluado;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Models\User;
use App\Support\InformeWordAnexosPapeleria;
use App\Support\InformeWordPreguntasPoligraficas;
use App\Support\InformeWordXml;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InformeWordSprintCTest extends TestCase
{
    use RefreshDatabase;

    private function evaluado(): EvaluadoOrden
    {
        $orden = Orden::factory()->create();

        return EvaluadoOrden::factory()->create(['orden_id' => $orden->id]);
    }

    private function userId(): int
    {
        return User::factory()->create(['role_as' => 3])->id;
    }

    public function test_anexos_papeleria_guarda_y_recupera_seleccion(): void
    {
        $evaluado = $this->evaluado();

        InformeWordAnexosPapeleria::guardarSeleccion(
            $evaluado->id,
            ['dpi_archivo', 'cv', 'tipo_invalido'],
            $this->userId()
        );

        $this->assertSame(['dpi_archivo', 'cv'], InformeWordAnexosPapeleria::tiposSeleccionados($evaluado->id));
    }

    public function test_anexos_papeleria_tipos_disponibles_solo_documentos_subidos(): void
    {
        $evaluado = $this->evaluado();

        DocumentoEvaluado::factory()->create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_documento' => 'dpi_archivo',
        ]);

        $disponibles = InformeWordAnexosPapeleria::tiposDisponibles($evaluado);

        $this->assertArrayHasKey('dpi_archivo', $disponibles);
        $this->assertArrayNotHasKey('cv', $disponibles);
    }

    public function test_anexos_papeleria_incluye_pdfs_seleccionados(): void
    {
        $evaluado = $this->evaluado();

        DocumentoEvaluado::factory()->create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_documento' => 'cv',
            'mime_type' => 'application/pdf',
        ]);

        InformeWordAnexosPapeleria::guardarSeleccion($evaluado->id, ['cv'], $this->userId());

        $docs = InformeWordAnexosPapeleria::documentosParaWord($evaluado->fresh());

        $this->assertCount(1, $docs);
        $this->assertTrue($docs->first()->es_pdf);
    }

    public function test_sin_seleccion_no_incluye_papeleria_aunque_el_candidato_tenga_archivos(): void
    {
        $evaluado = $this->evaluado();

        DocumentoEvaluado::factory()->create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_documento' => 'dpi_archivo',
            'mime_type' => 'image/jpeg',
        ]);
        DocumentoEvaluado::factory()->create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_documento' => 'cv',
            'mime_type' => 'application/pdf',
        ]);

        $this->assertSame([], InformeWordAnexosPapeleria::tiposSeleccionados($evaluado->id));
        $this->assertTrue(InformeWordAnexosPapeleria::documentosParaWord($evaluado->fresh())->isEmpty());
    }

    public function test_preguntas_poligraficas_aplica_a_poligrafo_y_vsa(): void
    {
        $poligrafo = $this->evaluado();
        $poligrafo->update(['tipo_servicio' => 'poligrafo']);
        $vsa = $this->evaluado();
        $vsa->update(['tipo_servicio' => 'vsa']);
        $socio = $this->evaluado();
        $socio->update(['tipo_servicio' => 'socioeconomico']);

        $this->assertTrue(InformeWordPreguntasPoligraficas::aplicaA($poligrafo->fresh()));
        $this->assertTrue(InformeWordPreguntasPoligraficas::aplicaA($vsa->fresh()));
        $this->assertFalse(InformeWordPreguntasPoligraficas::aplicaA($socio->fresh()));
    }

    public function test_preguntas_poligraficas_plantilla_marca_di_si_no_aprobado(): void
    {
        $evaluado = $this->evaluado();
        $evaluado->update(['tipo_servicio' => 'poligrafo', 'resultado' => 'no_aprobado']);

        $filas = InformeWordPreguntasPoligraficas::filas($evaluado->id, $evaluado);

        $this->assertCount(5, $filas);
        foreach ($filas as $fila) {
            $this->assertSame('DI', $fila['resultado']);
        }
    }

    public function test_preguntas_poligraficas_guardar_desde_request(): void
    {
        $evaluado = $this->evaluado();
        $evaluado->update(['tipo_servicio' => 'poligrafo']);

        InformeWordPreguntasPoligraficas::guardarDesdeRequest($evaluado->id, [
            ['pregunta' => '¿Pregunta demo?', 'respuesta' => 'No', 'resultado' => 'NDI', 'puntuacion' => '0.8'],
            ['pregunta' => '', 'respuesta' => 'Ignorada', 'resultado' => '', 'puntuacion' => ''],
        ], $this->userId());

        $filas = InformeWordPreguntasPoligraficas::filas($evaluado->id, $evaluado);

        $this->assertCount(1, $filas);
        $this->assertSame('¿Pregunta demo?', $filas[0]['pregunta']);
        $this->assertSame('No', $filas[0]['respuesta']);
        $this->assertSame('NDI', $filas[0]['resultado']);
    }

    public function test_establecer_texto_celda_no_corrompe_xml_con_texto_numerico(): void
    {
        $celda = '<w:tc><w:p><w:r><w:t>0</w:t></w:r></w:p></w:tc>';

        foreach (['1', '2', '10', '—'] as $texto) {
            $resultado = InformeWordXml::establecerTextoCelda($celda, $texto);
            $this->assertTrue(InformeWordXml::esValido($resultado), "Texto '{$texto}' produjo XML inválido");
            $this->assertStringContainsString('>' . htmlspecialchars($texto, ENT_XML1 | ENT_COMPAT, 'UTF-8') . '</w:t>', $resultado);
        }
    }
}
