<?php

namespace Tests\Feature;

use App\Models\Cuestionario;
use App\Models\CuestionarioRespuesta;
use App\Models\Empresa;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Support\CuestionarioFotoCandidato;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CompletaFlujoCuestionario;
use Tests\Support\FakeImage;
use Tests\TestCase;

class CuestionarioFotoCandidatoTest extends TestCase
{
    use RefreshDatabase, CompletaFlujoCuestionario;

    private function crearEvaluadoConCuestionario(): EvaluadoOrden
    {
        $empresa = Empresa::factory()->create();
        $orden = Orden::factory()->create(['empresa_id' => $empresa->id]);

        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_servicio' => 'poligrafo',
            'tipo_formulario' => 'preempleo',
            'token_unico' => 'test-foto-token',
            'token_expira_at' => now()->addDays(30),
            'cuestionario_completado' => false,
            'dpi' => '1234567890101',
        ]);

        Cuestionario::create(array_merge([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'preempleo',
            'seccion_actual' => 1,
            'total_secciones' => 5,
            'progreso_porcentaje' => 0,
            'completado' => false,
            'bloqueado' => false,
        ], $this->atributosCuestionarioListoParaSecciones()));

        return $evaluado->fresh(['cuestionario']);
    }

    private function datosPersonalesBase(): array
    {
        return collect($this->datosSeccion1Preempleo())->except('foto_candidato')->all();
    }

    public function test_rechaza_seccion_1_sin_foto(): void
    {
        $evaluado = $this->crearEvaluadoConCuestionario();

        $response = $this->post(
            "/cuestionario/{$evaluado->token_unico}/seccion/1",
            $this->datosPersonalesBase()
        );

        $response->assertSessionHasErrors(['foto_candidato']);
    }

    public function test_guarda_foto_y_permite_verla_con_token(): void
    {
        Storage::fake('local');
        $evaluado = $this->crearEvaluadoConCuestionario();
        $foto = FakeImage::jpeg('candidato.jpg');

        $response = $this->post(
            "/cuestionario/{$evaluado->token_unico}/seccion/1",
            array_merge($this->datosPersonalesBase(), ['foto_candidato' => $foto])
        );

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $cuestionario = $evaluado->cuestionario()->first();
        $ruta = CuestionarioFotoCandidato::obtenerRuta($cuestionario->id, 'datos_personales');

        $this->assertNotNull($ruta);
        Storage::disk('local')->assertExists($ruta);

        $this->assertDatabaseHas('cuestionario_respuestas', [
            'cuestionario_id' => $cuestionario->id,
            'seccion' => 'datos_personales',
            'campo' => 'foto_candidato',
            'tipo_campo' => 'file',
        ]);

        $preview = $this->get("/cuestionario/{$evaluado->token_unico}/foto-candidato");
        $preview->assertOk();
    }

    public function test_acepta_reenvio_sin_nueva_foto_si_ya_existe(): void
    {
        Storage::fake('local');
        $evaluado = $this->crearEvaluadoConCuestionario();
        $cuestionario = $evaluado->cuestionario;

        $ruta = 'cuestionarios/fotos/' . $cuestionario->id . '/foto_candidato.jpg';
        Storage::disk('local')->put($ruta, 'fake-image-content');

        CuestionarioRespuesta::create([
            'cuestionario_id' => $cuestionario->id,
            'seccion' => 'datos_personales',
            'campo' => 'foto_candidato',
            'valor' => $ruta,
            'tipo_campo' => 'file',
        ]);

        $response = $this->post(
            "/cuestionario/{$evaluado->token_unico}/seccion/1",
            array_merge($this->datosPersonalesBase(), ['foto_candidato_existente' => '1'])
        );

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
    }
}
