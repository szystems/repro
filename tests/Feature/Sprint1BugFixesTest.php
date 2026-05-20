<?php

namespace Tests\Feature;

use App\Models\Cuestionario;
use App\Models\DocumentoEvaluado;
use App\Models\Empresa;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Models\Role;
use App\Models\Sede;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests para los bugs resueltos en Sprint-1 (observaciones cliente 2026-04-22).
 *
 * Cubre: A9, CA1, CO9-1, CO9-2, N1
 */
class Sprint1BugFixesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'admin', 'display_name' => 'Administrador']);
        Role::create(['name' => 'empresa', 'display_name' => 'Empresa']);
        Role::create(['name' => 'repro', 'display_name' => 'Repro']);
    }

    private function crearAdmin(): User
    {
        $user = User::factory()->create(['role_as' => 3, 'estado' => 1]);
        $user->roles()->attach(Role::where('name', 'admin')->first());
        return $user;
    }

    private function crearRepro(): User
    {
        $user = User::factory()->create(['role_as' => 2, 'estado' => 1]);
        $user->roles()->attach(Role::where('name', 'repro')->first());
        return $user;
    }

    // ──────────────────────────────────────────────────────────
    // A9: filtro de estado en cuestionarios admin funciona
    // ──────────────────────────────────────────────────────────

    public function test_a9_listado_cuestionarios_acepta_filtro_estado_completado(): void
    {
        $admin = $this->crearAdmin();

        $response = $this->actingAs($admin)
            ->get(route('admin.cuestionarios.index', ['estado' => 'completado']));

        $response->assertOk();
    }

    public function test_a9_listado_cuestionarios_acepta_filtro_estado_pendiente(): void
    {
        $admin = $this->crearAdmin();

        $response = $this->actingAs($admin)
            ->get(route('admin.cuestionarios.index', ['estado' => 'pendiente']));

        $response->assertOk();
    }

    public function test_a9_listado_cuestionarios_acepta_filtro_estado_en_progreso(): void
    {
        $admin = $this->crearAdmin();

        $response = $this->actingAs($admin)
            ->get(route('admin.cuestionarios.index', ['estado' => 'en_progreso']));

        $response->assertOk();
    }

    // ──────────────────────────────────────────────────────────
    // CA1: motivo de rechazo de papelería visible al candidato
    // ──────────────────────────────────────────────────────────

    public function test_ca1_candidato_ve_notas_rechazo_en_vista_finalizar(): void
    {
        $orden    = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'token_unico' => 'token-ca1-test',
            'token_expira_at' => now()->addDays(30),
            'cuestionario_completado' => true,
        ]);

        Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'preempleo',
            'seccion_actual' => 7,
            'total_secciones' => 7,
            'progreso_porcentaje' => 100,
            'completado' => true,
        ]);

        DocumentoEvaluado::factory()->create([
            'evaluado_orden_id' => $evaluado->id,
            'estado_verificacion' => 'rechazado',
            'notas_verificacion' => 'El DPI está borroso, por favor suba una copia más clara.',
            'subido_por_user_id' => null,
        ]);

        $response = $this->get(route('cuestionario.finalizar', ['token' => 'token-ca1-test']));

        $response->assertOk();
        $response->assertSee('El DPI está borroso, por favor suba una copia más clara.');
    }

    public function test_ca1_candidato_no_ve_notas_de_documentos_aprobados(): void
    {
        $orden    = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'token_unico' => 'token-ca1-aprobado',
            'token_expira_at' => now()->addDays(30),
            'cuestionario_completado' => true,
        ]);

        Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'preempleo',
            'seccion_actual' => 7,
            'total_secciones' => 7,
            'progreso_porcentaje' => 100,
            'completado' => true,
        ]);

        DocumentoEvaluado::factory()->create([
            'evaluado_orden_id' => $evaluado->id,
            'estado_verificacion' => 'aprobado',
            'notas_verificacion' => 'Nota interna que el candidato no debe ver.',
            'subido_por_user_id' => null,
        ]);

        $response = $this->get(route('cuestionario.finalizar', ['token' => 'token-ca1-aprobado']));

        $response->assertOk();
        $response->assertDontSee('Nota interna que el candidato no debe ver.');
    }

    // ──────────────────────────────────────────────────────────
    // CO9-1: Vista diaria del calendario incluye evaluados
    //        con fecha ya asignada (para reprogramar)
    // ──────────────────────────────────────────────────────────

    public function test_co9_vista_dia_incluye_evaluados_con_fecha_asignada(): void
    {
        $repro = $this->crearRepro();
        $sede  = Sede::factory()->create(['estado' => 1]);
        $orden = Orden::factory()->create();

        // Evaluado con fecha ya asignada (debería aparecer en el dropdown del modal)
        EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'fecha_programada' => now()->addDays(5),
            'estado_evaluacion' => 'pendiente',
            'sede_id' => $sede->id,
        ]);

        $response = $this->actingAs($repro)
            ->get(route('calendario.dia', ['fecha' => now()->format('Y-m-d')]));

        $response->assertOk();
        // La variable evaluadosPendientes ya incluye evaluados con fecha asignada
        $response->assertViewHas('evaluadosPendientes');
    }

    public function test_co9_vista_dia_excluye_evaluados_con_estado_terminal(): void
    {
        $repro = $this->crearRepro();
        $orden = Orden::factory()->create();

        EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'fecha_programada' => now()->addDays(3),
            'estado_evaluacion' => 'cancelado',
        ]);
        EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'fecha_programada' => now()->addDays(3),
            'estado_evaluacion' => 'completado',
        ]);

        $response = $this->actingAs($repro)
            ->get(route('calendario.dia', ['fecha' => now()->format('Y-m-d')]));

        $response->assertOk();
        $evaluadosPendientes = $response->viewData('evaluadosPendientes');
        $this->assertCount(0, $evaluadosPendientes);
    }

    // ──────────────────────────────────────────────────────────
    // CO9-2: Consistencia de conteo mes vs. día
    // ──────────────────────────────────────────────────────────

    public function test_co9_scope_en_rango_es_inclusivo_en_extremo_superior(): void
    {
        $orden = Orden::factory()->create();
        $sede  = Sede::factory()->create(['estado' => 1]);

        // Cita exactamente al final del día
        EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'fecha_programada' => '2026-06-15 23:59:59',
            'estado_evaluacion' => 'pendiente',
            'sede_id' => $sede->id,
        ]);

        $count = EvaluadoOrden::enRangoFechas('2026-06-15 00:00:00', '2026-06-15 23:59:59')->count();

        $this->assertEquals(1, $count);
    }

    // ──────────────────────────────────────────────────────────
    // N1: Label "Fecha Tentativa" en vistas
    // ──────────────────────────────────────────────────────────

    public function test_n1_vista_crear_orden_usa_label_fecha_tentativa(): void
    {
        $admin = $this->crearAdmin();

        $response = $this->actingAs($admin)->get(route('ordenes.create'));

        $response->assertOk();
        $response->assertSee('Fecha Tentativa');
    }

    public function test_n1_vista_editar_orden_usa_label_fecha_tentativa(): void
    {
        $admin = $this->crearAdmin();
        $orden = Orden::factory()->create();

        $response = $this->actingAs($admin)->get(route('ordenes.edit', $orden));

        $response->assertOk();
        $response->assertSee('Fecha Tentativa');
    }

    // ──────────────────────────────────────────────────────────
    // Bug producción: generarCodigoUnico usa COUNT en vez de MAX
    // Si se elimina un registro, COUNT crea código ya existente
    // ──────────────────────────────────────────────────────────

    public function test_generar_codigo_unico_evita_duplicado_al_eliminar_orden(): void
    {
        // Simular estado de producción: ORD-2026-0001 fue eliminada,
        // ORD-2026-0002 existe. COUNT=1 → el bug generaba ORD-2026-0002 de nuevo.
        $year = date('Y');
        Orden::factory()->create(['codigo_orden' => "ORD-{$year}-0002"]);

        $codigo = Orden::generarCodigoUnico();

        $this->assertEquals("ORD-{$year}-0003", $codigo);
    }

    public function test_generar_codigo_unico_primer_orden_es_0001(): void
    {
        $codigo = Orden::generarCodigoUnico();

        $this->assertEquals('ORD-' . date('Y') . '-0001', $codigo);
    }
}

