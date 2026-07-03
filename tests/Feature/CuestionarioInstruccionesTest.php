<?php

namespace Tests\Feature;

use App\Models\Cuestionario;
use App\Models\Empresa;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CompletaFlujoCuestionario;
use Tests\TestCase;

class CuestionarioInstruccionesTest extends TestCase
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
            'token_unico' => 'token-instrucciones-e16',
            'token_expira_at' => now()->addDays(30),
            'cuestionario_completado' => false,
            'dpi' => '1234567890101',
        ]);
    }

    public function test_verificar_dpi_redirige_a_instrucciones(): void
    {
        $response = $this->post(route('cuestionario.verificar', $this->evaluado->token_unico), [
            'dpi_ingresado' => '1234567890101',
        ]);

        $response->assertRedirect(route('cuestionario.instrucciones', $this->evaluado->token_unico));
    }

    public function test_pagina_instrucciones_se_muestra(): void
    {
        Cuestionario::create([
            'evaluado_orden_id' => $this->evaluado->id,
            'tipo_formulario' => 'preempleo',
            'seccion_actual' => 1,
            'total_secciones' => 5,
            'completado' => false,
            'bloqueado' => false,
        ]);

        $response = $this->get(route('cuestionario.instrucciones', $this->evaluado->token_unico));

        $response->assertOk();
        $response->assertViewIs('cuestionario.instrucciones');
        $response->assertSee(config('cuestionario_instrucciones.titulo'));
        $response->assertSee(config('cuestionario_instrucciones.boton'));
    }

    public function test_aceptar_instrucciones_guarda_datos_y_redirige_a_terminos(): void
    {
        Cuestionario::create([
            'evaluado_orden_id' => $this->evaluado->id,
            'tipo_formulario' => 'preempleo',
            'seccion_actual' => 1,
            'total_secciones' => 5,
            'completado' => false,
            'bloqueado' => false,
        ]);

        $response = $this->post(route('cuestionario.aceptar-instrucciones', $this->evaluado->token_unico), [
            'acepta_instrucciones' => '1',
        ]);

        $response->assertRedirect(route('cuestionario.terminos', $this->evaluado->token_unico));

        $cuestionario = $this->evaluado->cuestionario()->first();
        $this->assertNotNull($cuestionario->instrucciones_leidas_at);
        $this->assertNotNull($cuestionario->ip_instrucciones);
    }

    public function test_aceptar_instrucciones_requiere_checkbox(): void
    {
        Cuestionario::create([
            'evaluado_orden_id' => $this->evaluado->id,
            'tipo_formulario' => 'preempleo',
            'seccion_actual' => 1,
            'total_secciones' => 5,
            'completado' => false,
            'bloqueado' => false,
        ]);

        $response = $this->post(route('cuestionario.aceptar-instrucciones', $this->evaluado->token_unico), []);

        $response->assertSessionHasErrors('acepta_instrucciones');
    }

    public function test_terminos_redirige_a_instrucciones_si_no_acepto(): void
    {
        Cuestionario::create([
            'evaluado_orden_id' => $this->evaluado->id,
            'tipo_formulario' => 'preempleo',
            'seccion_actual' => 1,
            'total_secciones' => 5,
            'completado' => false,
            'bloqueado' => false,
            'acepta_terminos' => false,
        ]);

        $response = $this->get(route('cuestionario.terminos', $this->evaluado->token_unico));

        $response->assertRedirect(route('cuestionario.instrucciones', $this->evaluado->token_unico));
    }

    public function test_seccion_redirige_a_instrucciones_si_no_acepto(): void
    {
        Cuestionario::create([
            'evaluado_orden_id' => $this->evaluado->id,
            'tipo_formulario' => 'preempleo',
            'seccion_actual' => 1,
            'total_secciones' => 5,
            'completado' => false,
            'bloqueado' => false,
        ]);

        $response = $this->get(route('cuestionario.seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 1,
        ]));

        $response->assertRedirect(route('cuestionario.instrucciones', $this->evaluado->token_unico));
    }

    public function test_instrucciones_ya_leidas_redirige_a_terminos(): void
    {
        Cuestionario::create([
            'evaluado_orden_id' => $this->evaluado->id,
            'tipo_formulario' => 'preempleo',
            'seccion_actual' => 1,
            'total_secciones' => 5,
            'completado' => false,
            'bloqueado' => false,
            'instrucciones_leidas_at' => now(),
            'acepta_terminos' => false,
        ]);

        $response = $this->get(route('cuestionario.instrucciones', $this->evaluado->token_unico));

        $response->assertRedirect(route('cuestionario.terminos', $this->evaluado->token_unico));
    }
}
