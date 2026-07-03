<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CompletaFlujoCuestionario;
use Tests\TestCase;

class CuestionarioPreempleoParejaTest extends TestCase
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
            'token_unico' => 'token-pareja-e23',
            'token_expira_at' => now()->addDays(30),
            'dpi' => '1234567890102',
            'tipo_formulario' => 'preempleo',
        ]);
    }

    public function test_seccion_2_muestra_bloque_pareja_y_condicionales(): void
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
        $response->assertSee('Pareja actual');
        $response->assertSee('¿Tiene pareja actual?');
        $response->assertSee('name="pareja_tipo_relacion"', false);
        $response->assertSee('data-condicional-trigger="vive_con_pareja"', false);
    }

    public function test_sin_pareja_no_exige_campos_pareja(): void
    {
        $this->verificarIdentidadYFlujoPreSeccion($this->evaluado->token_unico, '1234567890102');
        $this->post(route('cuestionario.guardar-seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 1,
        ]), $this->datosSeccion1Preempleo());

        $response = $this->post(route('cuestionario.guardar-seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 2,
        ]), $this->datosSeccion2Preempleo(['vive_con_pareja' => 'no']));

        $response->assertSessionHasNoErrors();
    }

    public function test_con_pareja_requiere_campos_obligatorios(): void
    {
        $this->verificarIdentidadYFlujoPreSeccion($this->evaluado->token_unico, '1234567890102');
        $this->post(route('cuestionario.guardar-seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 1,
        ]), $this->datosSeccion1Preempleo());

        $datos = array_merge(
            $this->datosSeccion2Preempleo(),
            $this->datosParejaPreempleo(['vive_con_pareja' => 'si'])
        );
        unset(
            $datos['pareja_nombre'],
            $datos['pareja_edad'],
            $datos['pareja_tipo_relacion'],
            $datos['pareja_trabaja']
        );

        $response = $this->post(route('cuestionario.guardar-seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 2,
        ]), $datos);

        $response->assertSessionHasErrors([
            'pareja_tipo_relacion',
            'pareja_nombre',
            'pareja_edad',
            'pareja_trabaja',
        ]);
    }

    public function test_guarda_datos_pareja_completos(): void
    {
        $this->verificarIdentidadYFlujoPreSeccion($this->evaluado->token_unico, '1234567890102');
        $this->post(route('cuestionario.guardar-seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 1,
        ]), $this->datosSeccion1Preempleo());

        $this->post(route('cuestionario.guardar-seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 2,
        ]), array_merge($this->datosSeccion2Preempleo(), $this->datosParejaPreempleo()));

        $cuestionario = $this->evaluado->cuestionario()->first();
        $respuestas = $cuestionario->obtenerRespuestasSeccion(2);

        $this->assertSame('si', $respuestas['vive_con_pareja'] ?? null);
        $this->assertSame('casado', $respuestas['pareja_tipo_relacion'] ?? null);
        $this->assertSame('Laura Méndez', $respuestas['pareja_nombre'] ?? null);
        $this->assertSame('32', $respuestas['pareja_edad'] ?? null);
        $this->assertSame('buena', $respuestas['pareja_calidad_relacion'] ?? null);
        $this->assertSame('si', $respuestas['pareja_trabaja'] ?? null);
    }
}
