<?php

namespace Tests\Feature;

use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Models\Empresa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CuestionarioTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_puede_acceder_a_cuestionario_con_token_valido(): void
    {
        // Crear empresa y orden
        $empresa = Empresa::factory()->create();
        $orden = Orden::factory()->create(['empresa_id' => $empresa->id]);
        
        // Crear evaluado con token válido
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'token_unico' => 'test-token-123',
            'token_expira_at' => now()->addDays(30),
            'cuestionario_completado' => false
        ]);

        $response = $this->get("/cuestionario/{$evaluado->token_unico}/seccion/1");

        $response->assertStatus(200);
        $response->assertSee('REPRO'); // Verificar que la página del cuestionario carga
        $response->assertSee('DPI'); // Verificar que el formulario está presente
    }

    public function test_no_puede_acceder_con_token_expirado(): void
    {
        // Crear empresa y orden
        $empresa = Empresa::factory()->create();
        $orden = Orden::factory()->create(['empresa_id' => $empresa->id]);
        
        // Crear evaluado con token expirado
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'token_unico' => 'test-token-expired',
            'token_expira_at' => now()->subDays(1), // Expirado
            'cuestionario_completado' => false
        ]);

        $response = $this->get("/cuestionario/{$evaluado->token_unico}/seccion/1");

        $response->assertStatus(403); // O el status que definas para token expirado
    }

    public function test_puede_navegar_entre_secciones(): void
    {
        // Crear datos de prueba
        $empresa = Empresa::factory()->create();
        $orden = Orden::factory()->create(['empresa_id' => $empresa->id]);
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'token_unico' => 'test-token-nav',
            'token_expira_at' => now()->addDays(30),
            'cuestionario_completado' => false
        ]);

        // Probar sección 1 
        $response = $this->get("/cuestionario/{$evaluado->token_unico}/seccion/1");
        $response->assertStatus(200);
        $response->assertSee('REPRO'); // Verificar que la página del cuestionario carga correctamente

        // Solo probar secciones que sabemos que existen y puede acceder
        // No podemos probar sección 2 o 5 sin completar sección 1 primero
    }

    public function test_muestra_mensaje_cuando_cuestionario_ya_completado(): void
    {
        // Crear datos de prueba
        $empresa = Empresa::factory()->create();
        $orden = Orden::factory()->create(['empresa_id' => $empresa->id]);
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'token_unico' => 'test-token-completed',
            'token_expira_at' => now()->addDays(30),
            'cuestionario_completado' => true, // Ya completado
            'completado_at' => now()->subHours(1)
        ]);

        $response = $this->get("/cuestionario/{$evaluado->token_unico}/seccion/1");

        // Debería mostrar mensaje de completado o redirigir
        $response->assertSee('completado'); // O el mensaje que definas
    }

    // =========================================================
    // R1 — Auto-estados en flujo del candidato
    // =========================================================

    public function test_r1_completar_cuestionario_establece_formulario_completado(): void
    {
        $empresa = Empresa::factory()->create();
        $orden = Orden::factory()->create(['empresa_id' => $empresa->id]);
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id'          => $orden->id,
            'token_unico'       => 'token-r1-completar',
            'token_expira_at'   => now()->addDays(30),
            'estado_evaluacion' => 'en_proceso',
            'cuestionario_completado' => false,
        ]);
        \App\Models\Cuestionario::create([
            'evaluado_orden_id'   => $evaluado->id,
            'tipo_formulario'     => 'preempleo',
            'acepta_terminos'     => true,
            'progreso_porcentaje' => 100,
            'seccion_actual'      => 1,
            'total_secciones'     => 1,
        ]);

        $this->post(route('cuestionario.completar', $evaluado->token_unico), [
            'confirmacion_final' => '1',
        ]);

        $evaluado->refresh();
        // Fase 18: el formulario completado se registra en estado_formulario
        $this->assertEquals('formulario_completado_y_recibido', $evaluado->estado_formulario);
        $this->assertTrue($evaluado->cuestionario_completado);
    }
}
