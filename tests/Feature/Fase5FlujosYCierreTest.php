<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class Fase5FlujosYCierreTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $admin;
    protected User $repro;
    protected User $empresa;
    protected Empresa $empresaModel;
    protected Orden $orden;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'admin', 'display_name' => 'Administrador']);
        Role::create(['name' => 'empresa', 'display_name' => 'Empresa']);
        Role::create(['name' => 'repro', 'display_name' => 'Polígrafo']);

        $this->admin = User::factory()->create(['role_as' => 3]);
        $this->admin->roles()->attach(Role::where('name', 'admin')->first());

        $this->repro = User::factory()->create(['role_as' => 2]);
        $this->repro->roles()->attach(Role::where('name', 'repro')->first());

        $this->empresaModel = Empresa::factory()->create();

        $this->empresa = User::factory()->create([
            'role_as' => 1,
            'empresa_id' => $this->empresaModel->id,
        ]);
        $this->empresa->roles()->attach(Role::where('name', 'empresa')->first());

        $this->orden = Orden::factory()->create([
            'empresa_id' => $this->empresaModel->id,
            'creado_por' => $this->admin->id,
        ]);
    }

    // ======================================================================
    // TRANSICIONES DE ESTADO DE EVALUACIÓN (EvaluadoOrden)
    // ======================================================================

    public function test_transicion_evaluacion_pendiente_a_contactando(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'estado_evaluacion' => 'pendiente',
        ]);

        $this->assertTrue($evaluado->puedeTransicionarEstadoEvaluacion('contactando'));
        $this->assertTrue($evaluado->cambiarEstadoEvaluacion('contactando'));
        $this->assertEquals('contactando', $evaluado->fresh()->estado_evaluacion);
    }

    public function test_transicion_evaluacion_pendiente_a_programado(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'estado_evaluacion' => 'pendiente',
        ]);

        $this->assertTrue($evaluado->puedeTransicionarEstadoEvaluacion('programado'));
        $this->assertTrue($evaluado->cambiarEstadoEvaluacion('programado'));
        $this->assertEquals('programado', $evaluado->fresh()->estado_evaluacion);
    }

    public function test_transicion_evaluacion_programado_a_en_sede(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'estado_evaluacion' => 'programado',
        ]);

        $this->assertTrue($evaluado->cambiarEstadoEvaluacion('en_sede'));
        $this->assertEquals('en_sede', $evaluado->fresh()->estado_evaluacion);
    }

    public function test_transicion_evaluacion_en_sede_a_en_proceso(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'estado_evaluacion' => 'en_sede',
        ]);

        $this->assertTrue($evaluado->cambiarEstadoEvaluacion('en_proceso'));
        $this->assertEquals('en_proceso', $evaluado->fresh()->estado_evaluacion);
    }

    public function test_transicion_evaluacion_en_proceso_a_completado(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'estado_evaluacion' => 'en_proceso',
        ]);

        $this->assertTrue($evaluado->cambiarEstadoEvaluacion('completado'));
        $this->assertEquals('completado', $evaluado->fresh()->estado_evaluacion);
    }

    public function test_transicion_evaluacion_programado_a_inasistencia(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'estado_evaluacion' => 'programado',
        ]);

        $this->assertTrue($evaluado->cambiarEstadoEvaluacion('inasistencia'));
        $this->assertEquals('inasistencia', $evaluado->fresh()->estado_evaluacion);
    }

    public function test_transicion_evaluacion_inasistencia_a_reprogramado(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'estado_evaluacion' => 'inasistencia',
        ]);

        $this->assertTrue($evaluado->cambiarEstadoEvaluacion('reprogramado'));
        $this->assertEquals('reprogramado', $evaluado->fresh()->estado_evaluacion);
    }

    public function test_transicion_evaluacion_pendiente_a_desistio(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'estado_evaluacion' => 'pendiente',
        ]);

        $this->assertTrue($evaluado->cambiarEstadoEvaluacion('desistio'));
        $this->assertEquals('desistio', $evaluado->fresh()->estado_evaluacion);
    }

    public function test_transicion_evaluacion_cancelado_a_pendiente_reactivar(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'estado_evaluacion' => 'cancelado',
        ]);

        $this->assertTrue($evaluado->cambiarEstadoEvaluacion('pendiente'));
        $this->assertEquals('pendiente', $evaluado->fresh()->estado_evaluacion);
    }

    public function test_transicion_evaluacion_invalida_completado_no_puede_cambiar(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'estado_evaluacion' => 'completado',
        ]);

        $this->assertFalse($evaluado->puedeTransicionarEstadoEvaluacion('pendiente'));
        $this->assertFalse($evaluado->cambiarEstadoEvaluacion('pendiente'));
        $this->assertEquals('completado', $evaluado->fresh()->estado_evaluacion);
    }

    public function test_transicion_evaluacion_invalida_desistio_es_final(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'estado_evaluacion' => 'desistio',
        ]);

        $this->assertFalse($evaluado->puedeTransicionarEstadoEvaluacion('pendiente'));
        $this->assertFalse($evaluado->puedeTransicionarEstadoEvaluacion('programado'));
    }

    public function test_transicion_evaluacion_no_puede_al_mismo_estado(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'estado_evaluacion' => 'programado',
        ]);

        $this->assertFalse($evaluado->puedeTransicionarEstadoEvaluacion('programado'));
        $this->assertFalse($evaluado->cambiarEstadoEvaluacion('programado'));
    }

    public function test_transicion_evaluacion_invalida_pendiente_a_completado(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'estado_evaluacion' => 'pendiente',
        ]);

        $this->assertFalse($evaluado->puedeTransicionarEstadoEvaluacion('completado'));
        $this->assertFalse($evaluado->cambiarEstadoEvaluacion('completado'));
    }

    public function test_transicion_evaluacion_en_sede_a_docs_pendientes(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'estado_evaluacion' => 'en_sede',
        ]);

        $this->assertTrue($evaluado->cambiarEstadoEvaluacion('docs_pendientes'));
        $this->assertEquals('docs_pendientes', $evaluado->fresh()->estado_evaluacion);
    }

    // ======================================================================
    // TRANSICIONES DE ESTADO DE FORMULARIO (EvaluadoOrden)
    // ======================================================================

    public function test_transicion_formulario_pendiente_a_link_enviado(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'estado_formulario' => 'pendiente',
        ]);

        $this->assertTrue($evaluado->puedeTransicionarEstadoFormulario('link_enviado'));
        $this->assertTrue($evaluado->cambiarEstadoFormulario('link_enviado'));
        $this->assertEquals('link_enviado', $evaluado->fresh()->estado_formulario);
    }

    public function test_transicion_formulario_link_enviado_a_en_progreso(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'estado_formulario' => 'link_enviado',
        ]);

        $this->assertTrue($evaluado->cambiarEstadoFormulario('en_progreso'));
        $this->assertEquals('en_progreso', $evaluado->fresh()->estado_formulario);
    }

    public function test_transicion_formulario_en_progreso_a_completado(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'estado_formulario' => 'en_progreso',
        ]);

        $this->assertTrue($evaluado->cambiarEstadoFormulario('completado'));
        $evaluado->refresh();
        $this->assertEquals('completado', $evaluado->estado_formulario);
        $this->assertTrue($evaluado->cuestionario_completado);
    }

    public function test_transicion_formulario_completado_a_pendiente_rehabilitar(): void
    {
        $evaluado = EvaluadoOrden::factory()->completado()->create([
            'orden_id' => $this->orden->id,
        ]);

        $this->assertTrue($evaluado->cambiarEstadoFormulario('pendiente'));
        $evaluado->refresh();
        $this->assertEquals('pendiente', $evaluado->estado_formulario);
        $this->assertFalse($evaluado->cuestionario_completado);
    }

    public function test_transicion_formulario_link_enviado_a_expirado(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'estado_formulario' => 'link_enviado',
        ]);

        $this->assertTrue($evaluado->cambiarEstadoFormulario('expirado'));
        $this->assertEquals('expirado', $evaluado->fresh()->estado_formulario);
    }

    public function test_transicion_formulario_expirado_a_pendiente(): void
    {
        $evaluado = EvaluadoOrden::factory()->expirado()->create([
            'orden_id' => $this->orden->id,
        ]);

        $this->assertTrue($evaluado->cambiarEstadoFormulario('pendiente'));
        $this->assertEquals('pendiente', $evaluado->fresh()->estado_formulario);
    }

    public function test_transicion_formulario_invalida_pendiente_a_completado_directo(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'estado_formulario' => 'pendiente',
        ]);

        $this->assertFalse($evaluado->puedeTransicionarEstadoFormulario('completado'));
        $this->assertFalse($evaluado->cambiarEstadoFormulario('completado'));
    }

    public function test_transicion_formulario_invalida_completado_a_en_progreso(): void
    {
        $evaluado = EvaluadoOrden::factory()->completado()->create([
            'orden_id' => $this->orden->id,
        ]);

        $this->assertFalse($evaluado->puedeTransicionarEstadoFormulario('en_progreso'));
    }

    public function test_transicion_formulario_sincroniza_cuestionario_completado(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'estado_formulario' => 'pendiente',
            'cuestionario_completado' => false,
        ]);

        // Pendiente → link_enviado → en_progreso → completado
        $evaluado->cambiarEstadoFormulario('link_enviado');
        $this->assertFalse($evaluado->fresh()->cuestionario_completado);

        $evaluado->cambiarEstadoFormulario('en_progreso');
        $this->assertFalse($evaluado->fresh()->cuestionario_completado);

        $evaluado->cambiarEstadoFormulario('completado');
        $this->assertTrue($evaluado->fresh()->cuestionario_completado);

        // Rehabilitar
        $evaluado->cambiarEstadoFormulario('pendiente');
        $this->assertFalse($evaluado->fresh()->cuestionario_completado);
    }

    // ======================================================================
    // TRANSICIONES DE ESTADO DE ORDEN
    // ======================================================================

    public function test_transicion_orden_solicitud_a_autorizacion(): void
    {
        $this->assertNotNull($this->orden->puedeTransicionarA('autorizacion'));
        $this->assertTrue($this->orden->cambiarEstado('autorizacion'));
        $this->assertEquals('autorizacion', $this->orden->fresh()->estado);
    }

    public function test_transicion_orden_flujo_completo(): void
    {
        $flujo = ['autorizacion', 'requisito', 'programacion', 'en_proceso', 'preliminar', 'final', 'entregado'];

        foreach ($flujo as $estado) {
            $this->assertTrue($this->orden->puedeTransicionarA($estado), "Orden no puede transicionar a {$estado} desde {$this->orden->estado}");
            $this->assertTrue($this->orden->cambiarEstado($estado));
        }

        $this->assertEquals('entregado', $this->orden->fresh()->estado);
    }

    public function test_transicion_orden_cancelar_desde_cualquier_estado(): void
    {
        $estadosIntermedios = ['solicitud', 'autorizacion', 'requisito', 'programacion', 'en_proceso'];

        foreach ($estadosIntermedios as $estado) {
            $orden = Orden::factory()->create([
                'empresa_id' => $this->empresaModel->id,
                'creado_por' => $this->admin->id,
                'estado' => $estado,
            ]);

            $this->assertTrue(
                $orden->puedeTransicionarA('cancelado'),
                "No se puede cancelar desde '{$estado}'"
            );
        }
    }

    public function test_transicion_orden_entregado_no_puede_cambiar(): void
    {
        $this->orden->update(['estado' => 'entregado']);

        $this->assertFalse($this->orden->puedeTransicionarA('solicitud'));
        $this->assertFalse($this->orden->puedeTransicionarA('en_proceso'));
    }

    public function test_transicion_orden_no_puede_al_mismo_estado(): void
    {
        $this->assertFalse($this->orden->puedeTransicionarA('solicitud'));
    }

    public function test_orden_estados_disponibles_retorna_todos(): void
    {
        $estados = Orden::estadosDisponibles();

        $this->assertArrayHasKey('solicitud', $estados);
        $this->assertArrayHasKey('autorizacion', $estados);
        $this->assertArrayHasKey('requisito', $estados);
        $this->assertArrayHasKey('programacion', $estados);
        $this->assertArrayHasKey('en_proceso', $estados);
        $this->assertArrayHasKey('preliminar', $estados);
        $this->assertArrayHasKey('final', $estados);
        $this->assertArrayHasKey('entregado', $estados);
        $this->assertArrayHasKey('cancelado', $estados);
        $this->assertCount(9, $estados);
    }

    // ======================================================================
    // ACCESSORS (COLOR Y TEXTO)
    // ======================================================================

    public function test_accessor_estado_evaluacion_texto(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'estado_evaluacion' => 'en_sede',
        ]);

        $this->assertEquals('En Sede', $evaluado->estado_evaluacion_texto);
    }

    public function test_accessor_estado_evaluacion_color(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'estado_evaluacion' => 'completado',
        ]);

        $this->assertEquals('success', $evaluado->estado_evaluacion_color);
    }

    public function test_accessor_estado_formulario_color(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'estado_formulario' => 'en_progreso',
        ]);

        $this->assertEquals('warning', $evaluado->estado_formulario_color);
    }

    public function test_accessor_estado_evaluacion_color_todos_los_estados(): void
    {
        $estados = EvaluadoOrden::estadosEvaluacionDisponibles();

        foreach (array_keys($estados) as $estado) {
            $evaluado = EvaluadoOrden::factory()->create([
                'orden_id' => $this->orden->id,
                'estado_evaluacion' => $estado,
            ]);

            $this->assertNotEmpty(
                $evaluado->estado_evaluacion_color,
                "Color vacío para estado_evaluacion '{$estado}'"
            );
        }
    }

    public function test_accessor_estado_formulario_color_todos_los_estados(): void
    {
        $estados = EvaluadoOrden::estadosFormularioDisponibles();

        foreach (array_keys($estados) as $estado) {
            $evaluado = EvaluadoOrden::factory()->create([
                'orden_id' => $this->orden->id,
                'estado_formulario' => $estado,
            ]);

            $this->assertNotEmpty(
                $evaluado->estado_formulario_color,
                "Color vacío para estado_formulario '{$estado}'"
            );
        }
    }

    public function test_accessor_orden_estado_human(): void
    {
        $this->orden->update(['estado' => 'en_proceso']);
        $this->assertEquals('Realización de la Prueba', $this->orden->fresh()->estado_human);
    }

    public function test_accessor_orden_estado_color(): void
    {
        $this->orden->update(['estado' => 'preliminar']);
        $this->assertEquals('purple', $this->orden->fresh()->estado_color);
    }

    // ======================================================================
    // ENDPOINT: CAMBIAR ESTADO EVALUADO (HTTP)
    // ======================================================================

    public function test_admin_puede_cambiar_estado_evaluacion_via_http(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'estado_evaluacion' => 'pendiente',
        ]);

        $response = $this->actingAs($this->admin)
            ->patch(route('evaluados.cambiar-estado', $evaluado->id), [
                'tipo_estado' => 'evaluacion',
                'nuevo_estado' => 'contactando',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertEquals('contactando', $evaluado->fresh()->estado_evaluacion);
    }

    public function test_repro_puede_cambiar_estado_evaluacion_via_http(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'estado_evaluacion' => 'programado',
        ]);

        $response = $this->actingAs($this->repro)
            ->patch(route('evaluados.cambiar-estado', $evaluado->id), [
                'tipo_estado' => 'evaluacion',
                'nuevo_estado' => 'en_sede',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertEquals('en_sede', $evaluado->fresh()->estado_evaluacion);
    }

    public function test_empresa_no_puede_cambiar_estado_evaluacion(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'estado_evaluacion' => 'pendiente',
        ]);

        $response = $this->actingAs($this->empresa)
            ->patch(route('evaluados.cambiar-estado', $evaluado->id), [
                'tipo_estado' => 'evaluacion',
                'nuevo_estado' => 'contactando',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertEquals('pendiente', $evaluado->fresh()->estado_evaluacion);
    }

    public function test_cambiar_estado_evaluacion_invalido_retorna_error(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'estado_evaluacion' => 'pendiente',
        ]);

        $response = $this->actingAs($this->admin)
            ->patch(route('evaluados.cambiar-estado', $evaluado->id), [
                'tipo_estado' => 'evaluacion',
                'nuevo_estado' => 'completado',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertEquals('pendiente', $evaluado->fresh()->estado_evaluacion);
    }

    public function test_admin_puede_cambiar_estado_formulario_via_http(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'estado_formulario' => 'pendiente',
        ]);

        $response = $this->actingAs($this->admin)
            ->patch(route('evaluados.cambiar-estado', $evaluado->id), [
                'tipo_estado' => 'formulario',
                'nuevo_estado' => 'link_enviado',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertEquals('link_enviado', $evaluado->fresh()->estado_formulario);
    }

    public function test_cambiar_estado_formulario_invalido_retorna_error(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'estado_formulario' => 'pendiente',
        ]);

        $response = $this->actingAs($this->admin)
            ->patch(route('evaluados.cambiar-estado', $evaluado->id), [
                'tipo_estado' => 'formulario',
                'nuevo_estado' => 'completado',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertEquals('pendiente', $evaluado->fresh()->estado_formulario);
    }

    public function test_tipo_estado_invalido_retorna_error_validacion(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->patch(route('evaluados.cambiar-estado', $evaluado->id), [
                'tipo_estado' => 'invalido',
                'nuevo_estado' => 'contactando',
            ]);

        $response->assertSessionHasErrors('tipo_estado');
    }

    // ======================================================================
    // ENDPOINT: CAMBIAR ESTADO ORDEN (HTTP)
    // ======================================================================

    public function test_admin_puede_cambiar_estado_orden_a_autorizacion(): void
    {
        $response = $this->actingAs($this->admin)
            ->patch(route('ordenes.cambiar-estado', $this->orden->id), [
                'nuevo_estado' => 'autorizacion',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertEquals('autorizacion', $this->orden->fresh()->estado);
    }

    public function test_cambiar_estado_orden_invalido_retorna_error(): void
    {
        $response = $this->actingAs($this->admin)
            ->patch(route('ordenes.cambiar-estado', $this->orden->id), [
                'nuevo_estado' => 'entregado',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertEquals('solicitud', $this->orden->fresh()->estado);
    }

    // ======================================================================
    // ESTADO_FORMULARIO EN MODELO Y FACTORY
    // ======================================================================

    public function test_evaluado_tiene_estado_formulario_por_defecto(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
        ]);

        $this->assertEquals('pendiente', $evaluado->estado_formulario);
    }

    public function test_factory_state_completado_tiene_estado_formulario_completado(): void
    {
        $evaluado = EvaluadoOrden::factory()->completado()->create([
            'orden_id' => $this->orden->id,
        ]);

        $this->assertEquals('completado', $evaluado->estado_formulario);
        $this->assertTrue($evaluado->cuestionario_completado);
    }

    public function test_factory_state_expirado_tiene_estado_formulario_expirado(): void
    {
        $evaluado = EvaluadoOrden::factory()->expirado()->create([
            'orden_id' => $this->orden->id,
        ]);

        $this->assertEquals('expirado', $evaluado->estado_formulario);
    }

    public function test_factory_state_en_progreso_tiene_estado_formulario_en_progreso(): void
    {
        $evaluado = EvaluadoOrden::factory()->enProgreso()->create([
            'orden_id' => $this->orden->id,
        ]);

        $this->assertEquals('en_progreso', $evaluado->estado_formulario);
    }

    // ======================================================================
    // ESTADOS DISPONIBLES (LISTAS)
    // ======================================================================

    public function test_estados_evaluacion_disponibles_contiene_todos(): void
    {
        $estados = EvaluadoOrden::estadosEvaluacionDisponibles();

        $this->assertCount(14, $estados);
        $this->assertArrayHasKey('pendiente', $estados);
        $this->assertArrayHasKey('contactando', $estados);
        $this->assertArrayHasKey('contactado', $estados);
        $this->assertArrayHasKey('link_enviado', $estados);
        $this->assertArrayHasKey('confirmado', $estados);
        $this->assertArrayHasKey('programado', $estados);
        $this->assertArrayHasKey('en_sede', $estados);
        $this->assertArrayHasKey('docs_pendientes', $estados);
        $this->assertArrayHasKey('en_proceso', $estados);
        $this->assertArrayHasKey('completado', $estados);
        $this->assertArrayHasKey('inasistencia', $estados);
        $this->assertArrayHasKey('reprogramado', $estados);
        $this->assertArrayHasKey('cancelado', $estados);
        $this->assertArrayHasKey('desistio', $estados);
    }

    public function test_estados_formulario_disponibles_contiene_todos(): void
    {
        $estados = EvaluadoOrden::estadosFormularioDisponibles();

        $this->assertCount(5, $estados);
        $this->assertArrayHasKey('pendiente', $estados);
        $this->assertArrayHasKey('link_enviado', $estados);
        $this->assertArrayHasKey('en_progreso', $estados);
        $this->assertArrayHasKey('completado', $estados);
        $this->assertArrayHasKey('expirado', $estados);
    }

    // ======================================================================
    // VISTA: SHOW ORDEN MUESTRA BADGES Y CONTROLES
    // ======================================================================

    public function test_show_orden_muestra_badge_estado_evaluacion(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'estado_evaluacion' => 'en_sede',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('ordenes.show', $this->orden->id));

        $response->assertStatus(200);
        $response->assertSee('En Sede');
    }

    public function test_show_orden_muestra_badge_estado_formulario(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'estado_formulario' => 'en_progreso',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('ordenes.show', $this->orden->id));

        $response->assertStatus(200);
        $response->assertSee('Estado Formulario');
        $response->assertSee('En Progreso');
    }

    public function test_show_orden_muestra_dropdown_transicion_evaluacion(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'estado_evaluacion' => 'pendiente',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('ordenes.show', $this->orden->id));

        $response->assertStatus(200);
        $response->assertSee('Cambiar a...');
        $response->assertSee('Contactando');
    }

    public function test_show_orden_empresa_no_ve_dropdown_transicion(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'estado_evaluacion' => 'pendiente',
        ]);

        $response = $this->actingAs($this->empresa)
            ->get(route('ordenes.show', $this->orden->id));

        $response->assertStatus(200);
        $response->assertDontSee('tipo_estado');
    }
}
