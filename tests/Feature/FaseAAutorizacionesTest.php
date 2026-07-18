<?php

namespace Tests\Feature;

use App\Models\Cuestionario;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesRolesAndPermissions;
use Tests\TestCase;

class FaseAAutorizacionesTest extends TestCase
{
    use RefreshDatabase, CreatesRolesAndPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpRolesAndPermissions();
    }

    private function evaluadoConCuestionario(array $attrs = []): EvaluadoOrden
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create(array_merge([
            'orden_id' => $orden->id,
            'tipo_servicio' => 'poligrafo',
            'tipo_formulario' => 'preempleo',
            'token_unico' => 'fasea-test-token-123456789012345',
            'token_expira_at' => now()->addDays(30),
        ], $attrs));

        Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => $evaluado->tipo_formulario,
            'seccion_actual' => 1,
            'total_secciones' => 5,
            'instrucciones_leidas_at' => now(),
        ]);

        return $evaluado;
    }

    public function test_terminos_muestra_espera_si_falta_motivo_en_periodica(): void
    {
        $evaluado = $this->evaluadoConCuestionario([
            'tipo_formulario' => 'periodica',
            'motivo_hecho_evaluacion' => null,
        ]);

        $response = $this->get(route('cuestionario.terminos', $evaluado->token_unico));

        $response->assertOk();
        $response->assertViewIs('cuestionario.espera-motivo');
    }

    public function test_aceptar_terminos_preempleo_redirige_a_infornet(): void
    {
        $evaluado = $this->evaluadoConCuestionario();

        $response = $this->post(route('cuestionario.aceptar-terminos', $evaluado->token_unico), [
            'acepta_terminos' => '1',
            'firma_digital' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
            'tipo_proceso' => 'poligrafo',
        ]);

        $response->assertRedirect(route('cuestionario.infornet', $evaluado->token_unico));

        $cuestionario = $evaluado->fresh()->cuestionario;
        $this->assertTrue($cuestionario->acepta_terminos);
        $this->assertNotEmpty($cuestionario->texto_autorizacion_html);
    }

    public function test_flujo_infornet_luego_seccion(): void
    {
        $evaluado = $this->evaluadoConCuestionario();
        $cuestionario = $evaluado->cuestionario;
        $cuestionario->update([
            'acepta_terminos' => true,
            'acepta_terminos_at' => now(),
            'firma_digital' => 'data:image/png;base64,test',
            'texto_autorizacion_html' => '<p>Auth</p>',
        ]);

        $response = $this->post(route('cuestionario.aceptar-infornet', $evaluado->token_unico), [
            'acepta_infornet' => '1',
        ]);

        $response->assertRedirect(route('cuestionario.seccion', ['token' => $evaluado->token_unico, 'numero' => 1]));

        $cuestionario->refresh();
        $this->assertTrue($cuestionario->acepta_infornet);
        $this->assertNotEmpty($cuestionario->texto_infornet_html);
    }

    public function test_repro_puede_actualizar_motivo_hecho(): void
    {
        $admin = User::factory()->create(['role_as' => 2]);
        $admin->roles()->attach(Role::where('name', 'repro')->first());

        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_formulario' => 'especifica',
        ]);

        $response = $this->actingAs($admin)->patch(route('evaluados.actualizar-motivo-hecho', $evaluado), [
            'motivo_hecho_evaluacion' => 'Investigación por faltante de inventario',
        ]);

        $response->assertRedirect();
        $this->assertSame('Investigación por faltante de inventario', $evaluado->fresh()->motivo_hecho_evaluacion);
    }
}
