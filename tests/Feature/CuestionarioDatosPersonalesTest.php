<?php

namespace Tests\Feature;

use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Models\Empresa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CompletaFlujoCuestionario;
use Tests\Support\FakeImage;
use Tests\TestCase;

class CuestionarioDatosPersonalesTest extends TestCase
{
    use RefreshDatabase, WithFaker, CompletaFlujoCuestionario;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    private function crearEvaluadoValido()
    {
        $empresa = Empresa::factory()->create();
        $orden = Orden::factory()->create(['empresa_id' => $empresa->id]);
        
        return EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'token_unico' => 'test-token-validacion',
            'token_expira_at' => now()->addDays(30),
            'cuestionario_completado' => false,
            'dpi' => '1234567890101' // DPI de prueba
        ]);
    }

    private function fotoValida(): UploadedFile
    {
        return FakeImage::jpeg('candidato.jpg');
    }

    private function datosCompletos(array $extra = []): array
    {
        return $this->datosSeccion1Preempleo($extra);
    }

    public function test_puede_enviar_formulario_con_datos_validos(): void
    {
        $evaluado = $this->crearEvaluadoValido();

        // Primero verificar identidad y flujo previo
        $this->verificarIdentidadYFlujoPreSeccion($evaluado->token_unico, '1234567890101');

        $datosFormulario = $this->datosCompletos();

        $response = $this->post("/cuestionario/{$evaluado->token_unico}/seccion/1", $datosFormulario);

        $response->assertStatus(302); // Redirección exitosa
        $response->assertSessionHasNoErrors();
    }

    public function test_falla_validacion_con_campos_faltantes(): void
    {
        $evaluado = $this->crearEvaluadoValido();
        $this->verificarIdentidadYFlujoPreSeccion($evaluado->token_unico, '1234567890101');

        $datosIncompletos = [
            'nombres_completos' => 'Juan Carlos',
            // Faltan apellidos_completos, dpi, etc.
        ];

        $response = $this->post("/cuestionario/{$evaluado->token_unico}/seccion/1", $datosIncompletos);

        $response->assertStatus(302); // Redirección por errores de validación
        $response->assertSessionHasErrors(['apellidos_completos', 'dpi', 'fecha_nacimiento']);
    }

    public function test_valida_dpi_coincida_con_evaluado(): void
    {
        $evaluado = $this->crearEvaluadoValido();
        $this->verificarIdentidadYFlujoPreSeccion($evaluado->token_unico, '1234567890101');

        $datosFormulario = $this->datosCompletos(['dpi' => '9999999999999']);

        $response = $this->post("/cuestionario/{$evaluado->token_unico}/seccion/1", $datosFormulario);

        $response->assertStatus(302); // Redirección por error de validación
        $response->assertSessionHasErrors(['dpi']);
    }

    public function test_acepta_dpi_correcto_del_evaluado(): void
    {
        $evaluado = $this->crearEvaluadoValido();
        
        // Primero verificar el DPI y completar flujo previo
        $this->verificarIdentidadYFlujoPreSeccion($evaluado->token_unico, $evaluado->dpi);

        $datosFormulario = $this->datosCompletos(['dpi' => $evaluado->dpi]);

        $response = $this->post("/cuestionario/{$evaluado->token_unico}/seccion/1", $datosFormulario);

        $response->assertStatus(302); // Redirección exitosa
        $response->assertSessionHasNoErrors();
    }
}
