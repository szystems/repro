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

class CuestionarioAutosaveFeatureTest extends TestCase
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
            'token_unico' => 'token-autosave-e13',
            'token_expira_at' => now()->addDays(30),
            'cuestionario_completado' => false,
            'dpi' => '1234567890101',
            'tipo_formulario' => 'preempleo',
            'tipo_servicio' => 'poligrafo',
        ]);
    }

    public function test_autosave_guarda_datos_parciales_sin_avanzar_seccion(): void
    {
        $this->verificarIdentidadYFlujoPreSeccion($this->evaluado->token_unico, '1234567890101');

        $this->post(route('cuestionario.guardar-seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 1,
        ]), $this->datosSeccion1Preempleo());

        $cuestionario = $this->evaluado->cuestionario()->first();
        $this->assertEquals(2, $cuestionario->fresh()->seccion_actual);

        $response = $this->postJson(route('cuestionario.autosave-seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 2,
        ]), $this->datosSeccion2Preempleo([
            'estado_civil_detalle' => 'casado',
            'tiene_hijos' => 'no',
        ]));

        $response->assertOk();
        $response->assertJson(['success' => true, 'seccion' => 2]);

        $this->assertEquals(2, $cuestionario->fresh()->seccion_actual);

        $this->assertDatabaseHas('cuestionario_respuestas', [
            'cuestionario_id' => $cuestionario->id,
            'seccion' => 'informacion_familiar',
            'campo' => 'estado_civil_detalle',
            'valor' => 'casado',
        ]);
    }

    public function test_autosave_no_persiste_licencia_vacia(): void
    {
        $this->verificarIdentidadYFlujoPreSeccion($this->evaluado->token_unico, '1234567890101');

        $this->evaluado->cuestionario()->create([
            'tipo_formulario' => 'preempleo',
            'seccion_actual' => 1,
            'total_secciones' => 5,
        ]);

        $datos = $this->datosSeccion1Preempleo();
        unset($datos['licencia_conducir'], $datos['foto_candidato']);
        $datos['licencia_conducir'] = '';

        $response = $this->postJson(route('cuestionario.autosave-seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 1,
        ]), $datos);

        $response->assertOk();

        $this->assertDatabaseMissing('cuestionario_respuestas', [
            'cuestionario_id' => $this->evaluado->cuestionario->id,
            'campo' => 'licencia_conducir',
        ]);
    }

    public function test_borrador_acepta_datos_parciales_sin_validacion_completa(): void
    {
        $this->verificarIdentidadYFlujoPreSeccion($this->evaluado->token_unico, '1234567890101');

        $this->post(route('cuestionario.guardar-seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 1,
        ]), $this->datosSeccion1Preempleo());

        $response = $this->post(route('cuestionario.guardar-seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 2,
        ]), [
            'estado_civil_detalle' => 'soltero',
            'action' => 'borrador',
        ]);

        $response->assertRedirect(route('cuestionario.seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 2,
        ]));
        $response->assertSessionHasNoErrors();

        $cuestionario = $this->evaluado->cuestionario()->first();

        $this->assertDatabaseHas('cuestionario_respuestas', [
            'cuestionario_id' => $cuestionario->id,
            'campo' => 'estado_civil_detalle',
            'valor' => 'soltero',
        ]);

        $this->assertEquals(2, $cuestionario->fresh()->seccion_actual);
    }

    public function test_vista_seccion_incluye_script_autosave(): void
    {
        $this->evaluado->cuestionario()->create([
            'tipo_formulario' => 'preempleo',
            'seccion_actual' => 2,
            'total_secciones' => 5,
            'instrucciones_leidas_at' => now(),
            'acepta_terminos' => true,
        ]);

        $response = $this->get(route('cuestionario.seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 2,
        ]));

        $response->assertOk();
        $response->assertSee('cuestionario-autosave.js', false);
        $response->assertSee('data-autosave-url', false);
    }

}
