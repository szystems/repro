<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\Concerns\CreatesRolesAndPermissions;
use Tests\TestCase;

class Fase5FlujosYCierreTest extends TestCase
{
    use RefreshDatabase, WithFaker, CreatesRolesAndPermissions;

    protected User $admin;
    protected User $repro;
    protected User $empresa;
    protected Empresa $empresaModel;
    protected Orden $orden;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpRolesAndPermissions();

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
    // Flujo: pendiente_de_evaluacion → en_proceso → en_revision → resultado_preliminar → informe_final_enviado
    // ======================================================================

    public function test_transicion_evaluacion_pendiente_a_en_proceso(): void
    {
        // S4 + S5: formulario completo y haber sido programado son pre-condiciones
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id'            => $this->orden->id,
            'estado_evaluacion'   => 'pendiente_de_evaluacion',
            'estado_formulario'   => 'formulario_completado_y_recibido',
            'estado_programacion' => 'programado',
        ]);

        $this->assertTrue($evaluado->puedeTransicionarEstadoEvaluacion('en_proceso'));
        $this->assertTrue($evaluado->cambiarEstadoEvaluacion('en_proceso'));
        $this->assertEquals('en_proceso', $evaluado->fresh()->estado_evaluacion);
    }

    public function test_transicion_evaluacion_en_proceso_a_en_revision(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'estado_evaluacion' => 'en_proceso',
        ]);

        $this->assertTrue($evaluado->puedeTransicionarEstadoEvaluacion('en_revision'));
        $this->assertTrue($evaluado->cambiarEstadoEvaluacion('en_revision'));
        $this->assertEquals('en_revision', $evaluado->fresh()->estado_evaluacion);
    }

    public function test_transicion_evaluacion_en_revision_a_resultado_preliminar(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'estado_evaluacion' => 'en_revision',
        ]);

        $this->assertTrue($evaluado->cambiarEstadoEvaluacion('resultado_preliminar'));
        $this->assertEquals('resultado_preliminar', $evaluado->fresh()->estado_evaluacion);
    }

    public function test_transicion_evaluacion_en_proceso_a_completado(): void
    {
        // Renombrado: ahora prueba el flujo hasta informe_final_enviado
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'estado_evaluacion' => 'resultado_preliminar',
        ]);

        $this->assertTrue($evaluado->cambiarEstadoEvaluacion('informe_final_enviado'));
        $this->assertEquals('informe_final_enviado', $evaluado->fresh()->estado_evaluacion);
    }

    // Fase 18: inasistencia/reprogramado/desistio se manejan en estado_programacion
    public function test_transicion_evaluacion_programado_a_inasistencia(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'estado_programacion' => 'programado',
        ]);

        $this->assertTrue($evaluado->cambiarEstadoProgramacion('inasistencia'));
        $this->assertEquals('inasistencia', $evaluado->fresh()->estado_programacion);
    }

    public function test_transicion_evaluacion_inasistencia_a_reprogramado(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'estado_programacion' => 'inasistencia',
        ]);

        $this->assertTrue($evaluado->cambiarEstadoProgramacion('reprogramado'));
        $this->assertEquals('reprogramado', $evaluado->fresh()->estado_programacion);
    }

    public function test_transicion_evaluacion_pendiente_a_desistio(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'estado_programacion' => 'contactando',
        ]);

        $this->assertTrue($evaluado->cambiarEstadoProgramacion('desistio'));
        $this->assertEquals('desistio', $evaluado->fresh()->estado_programacion);
    }

    public function test_transicion_evaluacion_cancelado_a_pendiente_reactivar(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'estado_evaluacion' => 'cancelado',
        ]);

        $this->assertTrue($evaluado->cambiarEstadoEvaluacion('pendiente_de_evaluacion'));
        $this->assertEquals('pendiente_de_evaluacion', $evaluado->fresh()->estado_evaluacion);
    }

    public function test_transicion_evaluacion_invalida_completado_no_puede_cambiar(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'estado_evaluacion' => 'informe_final_enviado',
        ]);

        $this->assertFalse($evaluado->puedeTransicionarEstadoEvaluacion('en_proceso'));
        $this->assertFalse($evaluado->cambiarEstadoEvaluacion('en_proceso'));
        $this->assertEquals('informe_final_enviado', $evaluado->fresh()->estado_evaluacion);
    }

    public function test_transicion_evaluacion_invalida_informe_final_es_final(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'estado_evaluacion' => 'informe_final_enviado',
        ]);

        $this->assertFalse($evaluado->puedeTransicionarEstadoEvaluacion('pendiente_de_evaluacion'));
        $this->assertFalse($evaluado->puedeTransicionarEstadoEvaluacion('en_proceso'));
    }

    public function test_transicion_evaluacion_no_puede_al_mismo_estado(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'estado_evaluacion' => 'en_proceso',
        ]);

        $this->assertFalse($evaluado->puedeTransicionarEstadoEvaluacion('en_proceso'));
        $this->assertFalse($evaluado->cambiarEstadoEvaluacion('en_proceso'));
    }

    public function test_transicion_evaluacion_invalida_pendiente_a_informe_final(): void
    {
        // No se puede saltar directo a informe_final_enviado desde pendiente
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'estado_evaluacion' => 'pendiente_de_evaluacion',
        ]);

        $this->assertFalse($evaluado->puedeTransicionarEstadoEvaluacion('informe_final_enviado'));
        $this->assertFalse($evaluado->cambiarEstadoEvaluacion('informe_final_enviado'));
    }

    public function test_transicion_evaluacion_en_sede_a_docs_pendientes(): void
    {
        // Renombrado: equivalente moderno es en_proceso → en_revision
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'estado_evaluacion' => 'en_proceso',
        ]);

        $this->assertTrue($evaluado->cambiarEstadoEvaluacion('en_revision'));
        $this->assertEquals('en_revision', $evaluado->fresh()->estado_evaluacion);
    }

    // ======================================================================
    // TRANSICIONES DE ESTADO DE FORMULARIO (EvaluadoOrden)
    // ======================================================================

    // Fase 18: Tests actualizados a los 5 estados de formulario
    public function test_transicion_formulario_pendiente_a_link_enviado(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'estado_formulario' => 'link_pendiente',
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

        $this->assertTrue($evaluado->cambiarEstadoFormulario('pendiente_de_llenar'));
        $this->assertEquals('pendiente_de_llenar', $evaluado->fresh()->estado_formulario);
    }

    public function test_transicion_formulario_en_progreso_a_completado(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'estado_formulario' => 'pendiente_de_llenar',
        ]);

        $this->assertTrue($evaluado->cambiarEstadoFormulario('formulario_completado_y_recibido'));
        $evaluado->refresh();
        $this->assertEquals('formulario_completado_y_recibido', $evaluado->estado_formulario);
        $this->assertTrue($evaluado->cuestionario_completado);
    }

    // Fase 18: formulario_completado_y_recibido es estado FINAL (no rehabilitable por machine)
    public function test_transicion_formulario_completado_a_pendiente_rehabilitar(): void
    {
        $evaluado = EvaluadoOrden::factory()->completado()->create([
            'orden_id' => $this->orden->id,
        ]);

        // completado es estado final - no puede transicionar
        $this->assertFalse($evaluado->puedeTransicionarEstadoFormulario('link_pendiente'));
        $this->assertFalse($evaluado->cambiarEstadoFormulario('link_pendiente'));
        $this->assertEquals('formulario_completado_y_recibido', $evaluado->fresh()->estado_formulario);
        $this->assertTrue($evaluado->cuestionario_completado);
    }

    public function test_transicion_formulario_link_enviado_a_expirado(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'estado_formulario' => 'link_enviado',
        ]);

        $this->assertTrue($evaluado->cambiarEstadoFormulario('vencido'));
        $this->assertEquals('vencido', $evaluado->fresh()->estado_formulario);
    }

    // Fase 18: vencido es estado FINAL (no rehabilitable)
    public function test_transicion_formulario_expirado_a_pendiente(): void
    {
        $evaluado = EvaluadoOrden::factory()->expirado()->create([
            'orden_id' => $this->orden->id,
        ]);

        $this->assertFalse($evaluado->puedeTransicionarEstadoFormulario('link_pendiente'));
        $this->assertFalse($evaluado->cambiarEstadoFormulario('link_pendiente'));
        $this->assertEquals('vencido', $evaluado->fresh()->estado_formulario);
    }

    public function test_transicion_formulario_invalida_pendiente_a_completado_directo(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'estado_formulario' => 'link_pendiente',
        ]);

        $this->assertFalse($evaluado->puedeTransicionarEstadoFormulario('formulario_completado_y_recibido'));
        $this->assertFalse($evaluado->cambiarEstadoFormulario('formulario_completado_y_recibido'));
    }

    public function test_transicion_formulario_invalida_completado_a_en_progreso(): void
    {
        $evaluado = EvaluadoOrden::factory()->completado()->create([
            'orden_id' => $this->orden->id,
        ]);

        $this->assertFalse($evaluado->puedeTransicionarEstadoFormulario('en_progreso'));
    }

    // Fase 18: flujo link_pendiente → link_enviado → pendiente_de_llenar → formulario_completado_y_recibido
    public function test_transicion_formulario_sincroniza_cuestionario_completado(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'estado_formulario' => 'link_pendiente',
            'cuestionario_completado' => false,
        ]);

        $evaluado->cambiarEstadoFormulario('link_enviado');
        $this->assertFalse($evaluado->fresh()->cuestionario_completado);

        $evaluado->cambiarEstadoFormulario('pendiente_de_llenar');
        $this->assertFalse($evaluado->fresh()->cuestionario_completado);

        $evaluado->cambiarEstadoFormulario('formulario_completado_y_recibido');
        $this->assertTrue($evaluado->fresh()->cuestionario_completado);
    }

    // ======================================================================
    // TRANSICIONES DE ESTADO DE ORDEN
    // ======================================================================

    // Fase 18: flujo simplificado orden_recibida → en_proceso → entregado
    public function test_transicion_orden_solicitud_a_autorizacion(): void
    {
        $this->assertTrue($this->orden->puedeTransicionarA('en_proceso'));
        $this->assertTrue($this->orden->cambiarEstado('en_proceso'));
        $this->assertEquals('en_proceso', $this->orden->fresh()->estado);
    }

    public function test_transicion_orden_flujo_completo(): void
    {
        $flujo = ['en_proceso', 'entregado'];

        foreach ($flujo as $estado) {
            $this->assertTrue($this->orden->puedeTransicionarA($estado), "Orden no puede transicionar a {$estado} desde {$this->orden->estado}");
            $this->assertTrue($this->orden->cambiarEstado($estado));
        }

        $this->assertEquals('entregado', $this->orden->fresh()->estado);
    }

    public function test_transicion_orden_cancelar_desde_cualquier_estado(): void
    {
        // Fase 18: se puede cancelar desde orden_recibida y en_proceso
        $estadosIntermedios = ['orden_recibida', 'en_proceso'];

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

    // Fase 18: 4 estados simplificados
    public function test_orden_estados_disponibles_retorna_todos(): void
    {
        $estados = Orden::estadosDisponibles();

        $this->assertArrayHasKey('orden_recibida', $estados);
        $this->assertArrayHasKey('en_proceso', $estados);
        $this->assertArrayHasKey('entregado', $estados);
        $this->assertArrayHasKey('cancelado', $estados);
        $this->assertCount(4, $estados);
    }

    // ======================================================================
    // ACCESSORS (COLOR Y TEXTO)
    // ======================================================================

    // Fase 18: en_revision reemplaza a en_sede
    public function test_accessor_estado_evaluacion_texto(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'estado_evaluacion' => 'en_revision',
        ]);

        $this->assertEquals('En Revisión', $evaluado->estado_evaluacion_texto);
    }

    public function test_accessor_estado_evaluacion_color(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'estado_evaluacion' => 'informe_final_enviado',
        ]);

        $this->assertEquals('success', $evaluado->estado_evaluacion_color);
    }

    // Fase 18: pendiente_de_llenar reemplaza a en_progreso
    public function test_accessor_estado_formulario_color(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'estado_formulario' => 'pendiente_de_llenar',
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

    // Fase 18: 4 estados simplificados
    public function test_accessor_orden_estado_human(): void
    {
        $this->orden->update(['estado' => 'en_proceso']);
        $this->assertEquals('En Proceso', $this->orden->fresh()->estado_human);
    }

    public function test_accessor_orden_estado_color(): void
    {
        $this->orden->update(['estado' => 'entregado']);
        $this->assertEquals('success', $this->orden->fresh()->estado_color);
    }

    // ======================================================================
    // ENDPOINT: CAMBIAR ESTADO EVALUADO (HTTP)
    // ======================================================================

    // Fase 18: HTTP endpoints con valores de estado actualizados
    public function test_admin_puede_cambiar_estado_evaluacion_via_http(): void
    {
        // S4 + S5: formulario completo y estado_programacion programado son pre-condiciones
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id'            => $this->orden->id,
            'estado_evaluacion'   => 'pendiente_de_evaluacion',
            'estado_formulario'   => 'formulario_completado_y_recibido',
            'estado_programacion' => 'programado',
        ]);

        $response = $this->actingAs($this->admin)
            ->patch(route('evaluados.cambiar-estado', $evaluado->id), [
                'tipo_estado' => 'evaluacion',
                'nuevo_estado' => 'en_proceso',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertEquals('en_proceso', $evaluado->fresh()->estado_evaluacion);
    }

    public function test_repro_puede_cambiar_estado_evaluacion_via_http(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'estado_evaluacion' => 'en_proceso',
        ]);

        $response = $this->actingAs($this->repro)
            ->patch(route('evaluados.cambiar-estado', $evaluado->id), [
                'tipo_estado' => 'evaluacion',
                'nuevo_estado' => 'en_revision',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertEquals('en_revision', $evaluado->fresh()->estado_evaluacion);
    }

    public function test_empresa_no_puede_cambiar_estado_evaluacion(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'estado_evaluacion' => 'pendiente_de_evaluacion',
        ]);

        $response = $this->actingAs($this->empresa)
            ->patch(route('evaluados.cambiar-estado', $evaluado->id), [
                'tipo_estado' => 'evaluacion',
                'nuevo_estado' => 'en_proceso',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertEquals('pendiente_de_evaluacion', $evaluado->fresh()->estado_evaluacion);
    }

    public function test_cambiar_estado_evaluacion_invalido_retorna_error(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'estado_evaluacion' => 'pendiente_de_evaluacion',
        ]);

        $response = $this->actingAs($this->admin)
            ->patch(route('evaluados.cambiar-estado', $evaluado->id), [
                'tipo_estado' => 'evaluacion',
                'nuevo_estado' => 'informe_final_enviado', // no es transición válida desde pendiente
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertEquals('pendiente_de_evaluacion', $evaluado->fresh()->estado_evaluacion);
    }

    public function test_admin_puede_cambiar_estado_formulario_via_http(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'estado_formulario' => 'link_pendiente',
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
            'estado_formulario' => 'link_pendiente',
        ]);

        $response = $this->actingAs($this->admin)
            ->patch(route('evaluados.cambiar-estado', $evaluado->id), [
                'tipo_estado' => 'formulario',
                'nuevo_estado' => 'formulario_completado_y_recibido', // no es transición válida desde link_pendiente
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertEquals('link_pendiente', $evaluado->fresh()->estado_formulario);
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

    // Fase 18: HTTP endpoint con 4 estados de Orden
    public function test_admin_puede_cambiar_estado_orden_a_autorizacion(): void
    {
        $response = $this->actingAs($this->admin)
            ->patch(route('ordenes.cambiar-estado', $this->orden->id), [
                'nuevo_estado' => 'en_proceso',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertEquals('en_proceso', $this->orden->fresh()->estado);
    }

    public function test_cambiar_estado_orden_invalido_retorna_error(): void
    {
        // Desde orden_recibida no se puede ir directamente a entregado
        $response = $this->actingAs($this->admin)
            ->patch(route('ordenes.cambiar-estado', $this->orden->id), [
                'nuevo_estado' => 'entregado',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertEquals('orden_recibida', $this->orden->fresh()->estado);
    }

    // ======================================================================
    // ESTADO_FORMULARIO EN MODELO Y FACTORY
    // ======================================================================

    // Fase 18: valores de formulario actualizados
    public function test_evaluado_tiene_estado_formulario_por_defecto(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
        ]);

        $this->assertEquals('link_pendiente', $evaluado->estado_formulario);
    }

    public function test_factory_state_completado_tiene_estado_formulario_completado(): void
    {
        $evaluado = EvaluadoOrden::factory()->completado()->create([
            'orden_id' => $this->orden->id,
        ]);

        $this->assertEquals('formulario_completado_y_recibido', $evaluado->estado_formulario);
        $this->assertTrue($evaluado->cuestionario_completado);
    }

    public function test_factory_state_expirado_tiene_estado_formulario_expirado(): void
    {
        $evaluado = EvaluadoOrden::factory()->expirado()->create([
            'orden_id' => $this->orden->id,
        ]);

        $this->assertEquals('vencido', $evaluado->estado_formulario);
    }

    public function test_factory_state_en_progreso_tiene_estado_formulario_en_progreso(): void
    {
        $evaluado = EvaluadoOrden::factory()->enProgreso()->create([
            'orden_id' => $this->orden->id,
        ]);

        $this->assertEquals('pendiente_de_llenar', $evaluado->estado_formulario);
    }

    // ======================================================================
    // ESTADOS DISPONIBLES (LISTAS)
    // ======================================================================

    // Fase 18: 7 valores de evaluacion y 5 de formulario
    public function test_estados_evaluacion_disponibles_contiene_todos(): void
    {
        $estados = EvaluadoOrden::estadosEvaluacionDisponibles();

        $this->assertCount(7, $estados);
        $this->assertArrayHasKey('pendiente_de_evaluacion', $estados);
        $this->assertArrayHasKey('en_proceso', $estados);
        $this->assertArrayHasKey('en_revision', $estados);
        $this->assertArrayHasKey('resultado_preliminar', $estados);
        $this->assertArrayHasKey('informe_final_enviado', $estados);
        $this->assertArrayHasKey('cancelado', $estados);
        $this->assertArrayHasKey('desistio', $estados);
    }

    public function test_estados_formulario_disponibles_contiene_todos(): void
    {
        $estados = EvaluadoOrden::estadosFormularioDisponibles();

        $this->assertCount(5, $estados);
        $this->assertArrayHasKey('link_pendiente', $estados);
        $this->assertArrayHasKey('link_enviado', $estados);
        $this->assertArrayHasKey('pendiente_de_llenar', $estados);
        $this->assertArrayHasKey('formulario_completado_y_recibido', $estados);
        $this->assertArrayHasKey('vencido', $estados);
    }

    // ======================================================================
    // VISTA: SHOW ORDEN MUESTRA BADGES Y CONTROLES
    // ======================================================================

    // Fase 18: badges con valores de estado actualizados
    public function test_show_orden_muestra_badge_estado_evaluacion(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'estado_evaluacion' => 'en_revision',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('ordenes.show', $this->orden->id));

        $response->assertStatus(200);
        $response->assertSee('En Revisión');
    }

    public function test_show_orden_muestra_badge_estado_formulario(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'estado_formulario' => 'pendiente_de_llenar',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('ordenes.show', $this->orden->id));

        $response->assertStatus(200);
        $response->assertSee('Estado de Formulario');
        $response->assertSee('Pendiente de Llenar');
    }

    public function test_show_orden_muestra_dropdown_transicion_evaluacion(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'estado_evaluacion' => 'pendiente_de_evaluacion',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('ordenes.show', $this->orden->id));

        $response->assertStatus(200);
        $response->assertSee('Cambiar a...');
        $response->assertSee('Link Enviado');
    }

    public function test_show_orden_empresa_no_ve_dropdown_transicion(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'estado_evaluacion' => 'pendiente_de_evaluacion',
        ]);

        $response = $this->actingAs($this->empresa)
            ->get(route('ordenes.show', $this->orden->id));

        $response->assertStatus(200);
        $response->assertDontSee('tipo_estado');
    }
}
