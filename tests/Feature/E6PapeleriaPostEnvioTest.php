<?php

namespace Tests\Feature;

use App\Models\Cuestionario;
use App\Models\Empresa;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class E6PapeleriaPostEnvioTest extends TestCase
{
    use RefreshDatabase;

    private EvaluadoOrden $evaluado;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $empresa = Empresa::factory()->create();
        $orden = Orden::factory()->create(['empresa_id' => $empresa->id]);
        $this->evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_servicio' => 'socioeconomico',
            'tipo_formulario' => 'preempleo',
            'token_unico' => 'token-post-envio-e64',
            'token_expira_at' => now()->addDays(20),
            'cuestionario_completado' => true,
            'estado_formulario' => 'formulario_completado_y_recibido',
            'completado_at' => now()->subDay(),
        ]);

        Cuestionario::create([
            'evaluado_orden_id' => $this->evaluado->id,
            'tipo_formulario' => 'socioeconomico',
            'seccion_actual' => 6,
            'total_secciones' => 6,
            'completado' => true,
            'bloqueado' => true,
            'completado_at' => now()->subDay(),
        ]);
    }

    public function test_completado_muestra_formulario_subida_mientras_enlace_vigente(): void
    {
        $response = $this->get(route('cuestionario.completado', $this->evaluado->token_unico));

        $response->assertOk();
        $response->assertSee('Documentación adicional');
        $response->assertSee('Subir documento');
        $response->assertSee('Recibo de Luz');
        $response->assertSee('Tatuajes');
    }

    public function test_evaluado_puede_subir_documento_despues_de_completar(): void
    {
        $archivo = UploadedFile::fake()->create('recibo.pdf', 400, 'application/pdf');

        $response = $this->post(route('cuestionario.subir-documento', $this->evaluado->token_unico), [
            'tipo_documento' => 'recibo_luz',
            'archivo' => $archivo,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('documento_evaluados', [
            'evaluado_orden_id' => $this->evaluado->id,
            'tipo_documento' => 'recibo_luz',
            'subido_por_tipo' => 'evaluado',
        ]);
    }

    public function test_no_puede_subir_documento_si_enlace_expirado(): void
    {
        $this->evaluado->update(['token_expira_at' => now()->subDay()]);

        $archivo = UploadedFile::fake()->create('dpi.pdf', 200, 'application/pdf');

        $response = $this->post(route('cuestionario.subir-documento', $this->evaluado->token_unico), [
            'tipo_documento' => 'dpi_archivo',
            'archivo' => $archivo,
        ]);

        $response->assertNotFound();
    }

    public function test_rechaza_documento_mayor_a_10_mb_con_mensaje_claro(): void
    {
        $archivo = UploadedFile::fake()->create('grande.pdf', 11000, 'application/pdf');

        $response = $this->post(route('cuestionario.subir-documento', $this->evaluado->token_unico), [
            'tipo_documento' => 'dpi_archivo',
            'archivo' => $archivo,
        ]);

        $response->assertSessionHasErrors(['archivo']);
        $this->assertStringContainsString(
            '10 MB',
            session('errors')->first('archivo')
        );
    }

    public function test_completado_sin_formulario_subida_si_enlace_expirado(): void
    {
        $this->evaluado->update(['token_expira_at' => now()->subDay()]);

        $response = $this->get(route('cuestionario.completado', $this->evaluado->token_unico));

        $response->assertOk();
        $response->assertSee('plazo para subir documentos');
        $response->assertDontSee('Subir documento');
    }
}
