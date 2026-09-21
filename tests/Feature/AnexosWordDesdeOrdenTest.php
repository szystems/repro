<?php

namespace Tests\Feature;

use App\Models\DocumentoEvaluado;
use App\Models\Empresa;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Models\User;
use App\Support\InformeWordAnexosPapeleria;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnexosWordDesdeOrdenTest extends TestCase
{
    use RefreshDatabase;

    public function test_repro_marca_una_imagen_y_el_word_no_lleva_la_otra_ni_el_pdf(): void
    {
        [$admin, $evaluado] = $this->orden();
        $validacion = $this->imagen($evaluado, 'antecedentes_penales', 'validacion.jpg');
        $original = $this->imagen($evaluado, 'antecedentes_penales', 'candidato.jpg');
        $pdf = DocumentoEvaluado::factory()->create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_documento' => 'constancia_estudios',
            'mime_type' => 'application/pdf',
            'nombre_original' => 'constancia.pdf',
        ]);
        $ajena = $this->imagen($this->orden()[1], 'cv', 'otro.jpg');

        $this->actingAs($admin)
            ->postJson(route('evaluados.anexos-word', $evaluado), [
                'ids' => [$validacion->id, $pdf->id, $ajena->id],
            ])
            ->assertOk()
            ->assertJson(['ok' => true, 'incluidos' => 1]);

        $docs = InformeWordAnexosPapeleria::documentosParaWord($evaluado->fresh());
        $this->assertCount(1, $docs);
        $this->assertSame($validacion->id, $docs->first()->id);
        $this->assertNotSame($original->id, $docs->first()->id);
    }

    public function test_desmarcar_todas_deja_el_word_sin_papeleria(): void
    {
        [$admin, $evaluado] = $this->orden();
        $imagen = $this->imagen($evaluado, 'dpi_archivo', 'dpi.jpg');
        InformeWordAnexosPapeleria::guardarSeleccion($evaluado->id, [$imagen->id], $admin->id);

        $this->actingAs($admin)
            ->postJson(route('evaluados.anexos-word', $evaluado), ['ids' => []])
            ->assertOk()
            ->assertJson(['incluidos' => 0]);

        $this->assertTrue(InformeWordAnexosPapeleria::documentosParaWord($evaluado->fresh())->isEmpty());
    }

    public function test_la_empresa_no_puede_marcar_anexos_del_word(): void
    {
        [, $evaluado] = $this->orden();
        $empresa = User::factory()->create(['role_as' => 1, 'estado' => 1, 'principal' => 1]);

        $this->actingAs($empresa)
            ->postJson(route('evaluados.anexos-word', $evaluado), ['ids' => []])
            ->assertForbidden();
    }

    public function test_la_ficha_muestra_la_casilla_en_la_imagen_y_pdf_sin_casilla(): void
    {
        [$admin, $evaluado] = $this->orden();
        $this->imagen($evaluado, 'cv', 'hoja.jpg');
        DocumentoEvaluado::factory()->create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_documento' => 'cv',
            'mime_type' => 'application/pdf',
            'nombre_original' => 'hoja.pdf',
        ]);

        $html = $this->actingAs($admin)
            ->view('admin.ordenes._documentos_evaluado', [
                'evaluado' => $evaluado->fresh('documentos'),
            ]);

        $html->assertSee('En el Word', false);
        $html->assertSee('anexo-word-check', false);
        $html->assertSee('Los PDF no se pegan', false);
    }

    /** @return array{0: User, 1: EvaluadoOrden} */
    private function orden(): array
    {
        $admin = User::factory()->create(['role_as' => 3, 'estado' => 1]);
        $empresa = Empresa::factory()->create();
        $orden = Orden::factory()->create(['empresa_id' => $empresa->id]);
        $evaluado = EvaluadoOrden::factory()->create(['orden_id' => $orden->id]);

        return [$admin, $evaluado];
    }

    private function imagen(EvaluadoOrden $evaluado, string $tipo, string $nombre): DocumentoEvaluado
    {
        return DocumentoEvaluado::factory()->create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_documento' => $tipo,
            'mime_type' => 'image/jpeg',
            'nombre_original' => $nombre,
        ]);
    }
}
