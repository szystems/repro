<?php

namespace Tests\Unit;

use App\Models\Empresa;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Models\Role;
use App\Models\User;
use App\Support\FormularioAutoTransiciones;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\Feature\Concerns\CreatesRolesAndPermissions;
use Tests\TestCase;

/**
 * Fase 18 — Semana 2: Tests del comando auto-transiciones de estado_formulario.
 *
 * Reglas cubiertas:
 *  R1: link_enviado +24h sin abrir → pendiente_de_llenar
 *  R2: cualquier estado incompleto +30 días → vencido
 *  R3: formulario_completado_y_recibido nunca se toca
 *  R4: dry-run no aplica cambios
 */
class Fase18AutoTransicionesTest extends TestCase
{
    use RefreshDatabase, CreatesRolesAndPermissions;

    private Orden $orden;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpRolesAndPermissions();
        Cache::flush();
        $empresa = Empresa::factory()->create();
        $admin   = User::factory()->create(['role_as' => 3]);
        $this->orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $admin->id,
        ]);
    }

    // ── R1: link_enviado +24h → pendiente_de_llenar ──────────────────────────

    public function test_link_enviado_mas_24h_transiciona_a_pendiente_de_llenar(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id'          => $this->orden->id,
            'estado_formulario' => 'link_enviado',
            'token_expira_at'   => now()->addDays(25), // generado hace > 24h (< 29d): suficiente margen para timezone
        ]);

        $this->artisan('formulario:auto-transiciones')->assertSuccessful();

        $this->assertEquals('pendiente_de_llenar', $evaluado->fresh()->estado_formulario);
    }

    public function test_link_enviado_menos_24h_no_transiciona(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id'          => $this->orden->id,
            'estado_formulario' => 'link_enviado',
            'token_expira_at'   => now()->addDays(30), // generado recién (token_expira_at = now+30d = < 24h): no debe transicionar
        ]);

        $this->artisan('formulario:auto-transiciones')->assertSuccessful();

        $this->assertEquals('link_enviado', $evaluado->fresh()->estado_formulario);
    }

    // ── R2: +30 días → vencido ────────────────────────────────────────────────

    public function test_link_pendiente_expirado_transiciona_a_vencido(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id'          => $this->orden->id,
            'estado_formulario' => 'link_pendiente',
            'token_expira_at'   => now()->subDays(60),
        ]);

        $this->artisan('formulario:auto-transiciones')->assertSuccessful();

        $this->assertEquals('vencido', $evaluado->fresh()->estado_formulario);
    }

    public function test_link_enviado_expirado_transiciona_a_vencido(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id'          => $this->orden->id,
            'estado_formulario' => 'link_enviado',
            'token_expira_at'   => now()->subDays(60), // suficientemente pasado para evitar issues de timezone
        ]);

        $this->artisan('formulario:auto-transiciones')->assertSuccessful();

        $this->assertEquals('vencido', $evaluado->fresh()->estado_formulario);
    }

    public function test_pendiente_de_llenar_expirado_transiciona_a_vencido(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id'          => $this->orden->id,
            'estado_formulario' => 'pendiente_de_llenar',
            'token_expira_at'   => now()->subDays(60),
        ]);

        $this->artisan('formulario:auto-transiciones')->assertSuccessful();

        $this->assertEquals('vencido', $evaluado->fresh()->estado_formulario);
    }

    // ── R3: estados finales no se tocan ──────────────────────────────────────

    public function test_formulario_completado_nunca_se_vence(): void
    {
        $evaluado = EvaluadoOrden::factory()->completado()->create([
            'orden_id'        => $this->orden->id,
            'token_expira_at' => now()->subDays(90),
        ]);

        $this->artisan('formulario:auto-transiciones')->assertSuccessful();

        $this->assertEquals('formulario_completado_y_recibido', $evaluado->fresh()->estado_formulario);
    }

    public function test_vencido_no_se_vuelve_a_procesar(): void
    {
        $evaluado = EvaluadoOrden::factory()->expirado()->create([
            'orden_id'        => $this->orden->id,
            'token_expira_at' => now()->subDays(90),
        ]);

        $this->artisan('formulario:auto-transiciones')->assertSuccessful();

        $this->assertEquals('vencido', $evaluado->fresh()->estado_formulario);
    }

    // ── R4: dry-run no aplica cambios ────────────────────────────────────────

    public function test_dry_run_no_aplica_cambios(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id'          => $this->orden->id,
            'estado_formulario' => 'link_enviado',
            'token_expira_at'   => now()->subDays(60),
        ]);

        $this->artisan('formulario:auto-transiciones --dry-run')->assertSuccessful();

        $this->assertEquals('link_enviado', $evaluado->fresh()->estado_formulario);
    }

    public function test_on_access_aplica_24h_al_abrir_listado_de_ordenes(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'estado_formulario' => 'link_enviado',
            'token_expira_at' => now()->addDays(25),
        ]);

        $admin = User::factory()->create(['role_as' => 3, 'estado' => 1]);
        $admin->roles()->attach(Role::where('name', 'admin')->first());

        $this->actingAs($admin)->get(route('ordenes.index'))->assertOk();

        $this->assertEquals('pendiente_de_llenar', $evaluado->fresh()->estado_formulario);
    }

    public function test_on_access_no_repite_dentro_de_5_minutos(): void
    {
        $primero = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'estado_formulario' => 'link_enviado',
            'token_expira_at' => now()->addDays(25),
        ]);
        FormularioAutoTransiciones::aplicarAlAcceder();
        $this->assertEquals('pendiente_de_llenar', $primero->fresh()->estado_formulario);

        $segundo = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'estado_formulario' => 'link_enviado',
            'token_expira_at' => now()->addDays(25),
        ]);
        FormularioAutoTransiciones::aplicarAlAcceder();
        $this->assertEquals('link_enviado', $segundo->fresh()->estado_formulario);

        Cache::flush();
        FormularioAutoTransiciones::aplicarAlAcceder();
        $this->assertEquals('pendiente_de_llenar', $segundo->fresh()->estado_formulario);
    }
}
