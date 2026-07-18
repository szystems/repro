<?php

namespace Tests\Feature;

use App\Models\CuestionarioRespuesta;
use App\Models\Empresa;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CompletaFlujoCuestionario;
use Tests\Support\FakeImage;
use Tests\TestCase;

class CuestionarioCamposCondicionalesTest extends TestCase
{
    use RefreshDatabase, CompletaFlujoCuestionario;

    private EvaluadoOrden $evaluado;

    protected function setUp(): void
    {
        parent::setUp();

        $empresa = Empresa::factory()->create();
        $orden = Orden::factory()->create(['empresa_id' => $empresa->id]);

        $this->evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'token_unico' => 'token-campos-condicionales-e12',
            'token_expira_at' => now()->addDays(30),
            'cuestionario_completado' => false,
            'dpi' => '1234567890101',
            'tipo_formulario' => 'preempleo',
            'tipo_servicio' => 'poligrafo',
        ]);
    }

    public function test_seccion_2_incluye_motor_de_campos_condicionales(): void
    {
        $this->evaluado->cuestionario()->create([
            'tipo_formulario' => 'preempleo',
            'seccion_actual' => 2,
            'total_secciones' => 5,
            'instrucciones_leidas_at' => now(),
            'acepta_terminos' => true,
            'acepta_terminos_at' => now(),
            'acepta_infornet' => true,
            'acepta_infornet_at' => now(),
        ]);

        $response = $this->get(route('cuestionario.seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 2,
        ]));

        $response->assertOk();
        $response->assertSee('data-condicional', false);
        $response->assertSee('data-condicional-trigger="tiene_hijos"', false);
        $response->assertSee('campos-condicionales.js', false);
    }

    public function test_guardar_seccion_2_sin_hijos_omite_tabla_hijos(): void
    {
        $this->verificarIdentidadYFlujoPreSeccion($this->evaluado->token_unico, '1234567890101');

        $this->post(route('cuestionario.guardar-seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 1,
        ]), $this->datosSeccion1Preempleo());

        $response = $this->post(route('cuestionario.guardar-seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 2,
        ]), array_merge($this->datosSeccion2Preempleo(), [
            'personas_hogar' => 3,
            'dependientes_economicos' => 1,
            'action' => 'siguiente',
        ]));

        $response->assertRedirect(route('cuestionario.seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 3,
        ]));
        $response->assertSessionHasNoErrors();

        $cuestionario = $this->evaluado->cuestionario()->first();

        $respuestaHijos = CuestionarioRespuesta::where('cuestionario_id', $cuestionario->id)
            ->where('campo', 'hijos')
            ->first();

        $this->assertTrue(
            $respuestaHijos === null || $respuestaHijos->getTabla() === []
        );
    }

}
