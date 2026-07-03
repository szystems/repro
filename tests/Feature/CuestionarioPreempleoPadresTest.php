<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CompletaFlujoCuestionario;
use Tests\TestCase;

class CuestionarioPreempleoPadresTest extends TestCase
{
    use RefreshDatabase, CompletaFlujoCuestionario;

    private EvaluadoOrden $evaluado;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $empresa = Empresa::factory()->create();
        $orden = Orden::factory()->create(['empresa_id' => $empresa->id]);
        $this->evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'token_unico' => 'token-padres-e22',
            'token_expira_at' => now()->addDays(30),
            'dpi' => '1234567890101',
            'tipo_formulario' => 'preempleo',
        ]);
    }

    public function test_seccion_2_muestra_bloque_padres_y_condicionales(): void
    {
        $this->evaluado->cuestionario()->create(array_merge([
            'tipo_formulario' => 'preempleo',
            'seccion_actual' => 2,
            'total_secciones' => 5,
        ], $this->atributosCuestionarioListoParaSecciones()));

        $response = $this->get(route('cuestionario.seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 2,
        ]));

        $response->assertOk();
        $response->assertSee('¿Con quién vive actualmente?');
        $response->assertSee('name="padre_nombre"', false);
        $response->assertSee('name="madre_vive"', false);
        $response->assertSee('data-condicional-trigger="padre_vive"', false);
    }

    public function test_padre_vive_no_no_exige_detalles(): void
    {
        $this->verificarIdentidadYFlujoPreSeccion($this->evaluado->token_unico, '1234567890101');
        $this->post(route('cuestionario.guardar-seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 1,
        ]), $this->datosSeccion1Preempleo());

        $response = $this->post(route('cuestionario.guardar-seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 2,
        ]), $this->datosSeccion2Preempleo([
            'padre_vive' => 'no',
            'madre_vive' => 'no',
        ]));

        $response->assertSessionHasNoErrors();
    }

    public function test_padre_vive_si_requiere_detalles(): void
    {
        $this->verificarIdentidadYFlujoPreSeccion($this->evaluado->token_unico, '1234567890101');
        $this->post(route('cuestionario.guardar-seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 1,
        ]), $this->datosSeccion1Preempleo());

        $datos = $this->datosSeccion2Preempleo([
            'padre_vive' => 'si',
            'madre_vive' => 'no',
        ]);
        unset($datos['padre_edad'], $datos['padre_direccion'], $datos['padre_ocupacion'], $datos['padre_telefono']);

        $response = $this->post(route('cuestionario.guardar-seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 2,
        ]), $datos);

        $response->assertSessionHasErrors(['padre_edad', 'padre_direccion']);
    }

    public function test_guarda_datos_padres_y_convive_con(): void
    {
        $this->verificarIdentidadYFlujoPreSeccion($this->evaluado->token_unico, '1234567890101');
        $this->post(route('cuestionario.guardar-seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 1,
        ]), $this->datosSeccion1Preempleo());

        $this->post(route('cuestionario.guardar-seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 2,
        ]), $this->datosSeccion2Preempleo());

        $cuestionario = $this->evaluado->cuestionario()->first();
        $respuestas = $cuestionario->obtenerRespuestasSeccion(2);

        $this->assertSame('madre,solo', $respuestas['convive_con'] ?? null);
        $this->assertSame('Carlos Pérez', $respuestas['padre_nombre'] ?? null);
        $this->assertSame('no', $respuestas['padre_vive'] ?? null);
        $this->assertSame('María García', $respuestas['madre_nombre'] ?? null);
        $this->assertSame('58', $respuestas['madre_edad'] ?? null);
    }
}
