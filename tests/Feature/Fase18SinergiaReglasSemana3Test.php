<?php

namespace Tests\Feature;

use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Models\Sede;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\Feature\Concerns\CreatesRolesAndPermissions;
use Tests\TestCase;

/**
 * Tests de sinergia Fase 18 — Semana 3
 *
 * Cubre:
 * - S2: Gating Virtual (formulario completo antes de programar)
 * - S4: Gating En Proceso (formulario completo)
 * - S5: Gating En Proceso (haber sido programado)
 * - S6: Auto proceso_realizado al pasar a en_revision
 * - Modalidad: selector, persistencia, registro historial
 */
class Fase18SinergiaReglasSemana3Test extends TestCase
{
    use RefreshDatabase, CreatesRolesAndPermissions;

    private EvaluadoOrden $evaluado;
    private User $admin;
    private User $repro;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpRolesAndPermissions();
        $this->admin = User::factory()->create(['email' => 'admin@test.com']);
        $this->admin->assignRole('admin');
        $this->repro = User::factory()->create(['email' => 'repro@test.com', 'role_as' => '2']);
        $this->repro->assignRole('repro');

        $orden = Orden::factory()->create(['estado' => 'orden_recibida']);
        $this->evaluado = EvaluadoOrden::factory()->create([
            'orden_id'            => $orden->id,
            'estado_formulario'   => 'link_pendiente',
            'estado_programacion' => 'contactando',
            'estado_evaluacion'   => 'pendiente_de_evaluacion',
            'modalidad'           => 'presencial',
            'fecha_programada'    => null,
            'fecha_hora_fin'      => null,
        ]);
    }

    // ========================================
    // Modalidad: selector y persistencia
    // ========================================

    public function test_evaluado_tiene_modalidad_presencial_por_defecto(): void
    {
        $this->assertEquals('presencial', $this->evaluado->modalidad);
    }

    public function test_modalidad_se_puede_cambiar_a_virtual(): void
    {
        $this->evaluado->modalidad = 'virtual';
        $this->evaluado->save();

        $this->assertEquals('virtual', $this->evaluado->fresh()->modalidad);
    }

    public function test_crear_orden_persiste_modalidad_del_formulario(): void
    {
        $this->actingAs($this->repro);

        $empresa = \App\Models\Empresa::factory()->create();
        $sede = Sede::factory()->create();

        $response = $this->post(route('ordenes.store'), [
            'empresa_id'  => $empresa->id,
            'descripcion' => 'Test modalidad',
            'evaluados'   => [
                1 => [
                    'nombre'          => 'Juan',
                    'apellidos'       => 'Perez',
                    'dpi'             => '1234567890123',
                    'email'           => 'juan@test.com',
                    'tipo_servicio'   => 'poligrafo',
                    'tipo_formulario' => 'preempleo',
                    'modalidad'       => 'virtual',
                ],
            ],
        ]);

        $evaluadoCreado = EvaluadoOrden::where('dpi', '1234567890123')->first();
        $this->assertNotNull($evaluadoCreado);
        $this->assertEquals('virtual', $evaluadoCreado->modalidad);
    }

    // ========================================
    // S2: Gating Virtual al programar
    // ========================================

    public function test_s2_virtual_sin_formulario_completo_bloquea_programar(): void
    {
        $this->actingAs($this->repro);

        $this->evaluado->modalidad = 'virtual';
        $this->evaluado->estado_formulario = 'link_enviado'; // no completado
        $this->evaluado->save();

        $sede = Sede::factory()->create();

        $response = $this->post(route('calendario.programar'), [
            'evaluado_orden_id' => $this->evaluado->id,
            'sede_id'           => $sede->id,
            'poligrafista_id'   => $this->repro->id,
            'fecha'             => now()->addDay()->format('Y-m-d'),
            'hora_inicio'       => '09:00',
            'hora_fin'          => '10:00',
            'modalidad'         => 'virtual',
        ]);

        $response->assertSessionHasErrors('modalidad');
        $this->assertNull($this->evaluado->fresh()->fecha_programada);
    }

    public function test_s2_virtual_con_formulario_completo_permite_programar(): void
    {
        $this->actingAs($this->repro);

        $this->evaluado->modalidad = 'virtual';
        $this->evaluado->estado_formulario = 'formulario_completado_y_recibido';
        $this->evaluado->estado_programacion = 'contactado';
        $this->evaluado->save();

        $sede = Sede::factory()->create();

        $response = $this->post(route('calendario.programar'), [
            'evaluado_orden_id' => $this->evaluado->id,
            'sede_id'           => $sede->id,
            'poligrafista_id'   => $this->repro->id,
            'fecha'             => now()->addDay()->format('Y-m-d'),
            'hora_inicio'       => '09:00',
            'hora_fin'          => '10:00',
            'modalidad'         => 'virtual',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertNotNull($this->evaluado->fresh()->fecha_programada);
    }

    public function test_s2_presencial_sin_formulario_permite_programar(): void
    {
        $this->actingAs($this->repro);

        $this->evaluado->modalidad = 'presencial';
        $this->evaluado->estado_formulario = 'link_pendiente'; // sin completar
        $this->evaluado->estado_programacion = 'contactado';
        $this->evaluado->save();

        $sede = Sede::factory()->create();

        $response = $this->post(route('calendario.programar'), [
            'evaluado_orden_id' => $this->evaluado->id,
            'sede_id'           => $sede->id,
            'poligrafista_id'   => $this->repro->id,
            'fecha'             => now()->addDay()->format('Y-m-d'),
            'hora_inicio'       => '09:00',
            'hora_fin'          => '10:00',
            'modalidad'         => 'presencial',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertNotNull($this->evaluado->fresh()->fecha_programada);
    }

    public function test_s2_reprogramar_virtual_sin_formulario_no_bloquea(): void
    {
        $this->actingAs($this->repro);

        // Cita previa ya agendada en modalidad virtual
        $this->evaluado->modalidad = 'virtual';
        $this->evaluado->estado_formulario = 'link_enviado'; // sin completar
        $this->evaluado->estado_programacion = 'programado';
        $this->evaluado->fecha_programada = now()->addDay()->toDateTimeString();
        $this->evaluado->fecha_hora_fin = now()->addDay()->addHour()->toDateTimeString();
        $this->evaluado->save();

        $sede = Sede::factory()->create();

        // Reprogramar: la cita ya existía, no debe bloquear
        $response = $this->patch(route('calendario.reprogramar', $this->evaluado), [
            'sede_id'         => $sede->id,
            'poligrafista_id' => $this->repro->id,
            'fecha'           => now()->addDays(2)->format('Y-m-d'),
            'hora_inicio'     => '10:00',
            'hora_fin'        => '11:00',
            'modalidad'       => 'virtual',
        ]);

        $response->assertSessionHasNoErrors();
    }

    // ========================================
    // S4: Gating En Proceso (formulario completo)
    // ========================================

    public function test_s4_en_proceso_requiere_formulario_completo(): void
    {
        $this->evaluado->estado_evaluacion = 'pendiente_de_evaluacion';
        $this->evaluado->estado_formulario = 'link_enviado'; // no completado
        $this->evaluado->estado_programacion = 'programado';
        $this->evaluado->save();

        $this->expectException(ValidationException::class);
        $this->evaluado->cambiarEstadoEvaluacion('en_proceso');
    }

    public function test_s4_en_proceso_con_formulario_completo_permite_transicion(): void
    {
        $this->evaluado->estado_evaluacion = 'pendiente_de_evaluacion';
        $this->evaluado->estado_formulario = 'formulario_completado_y_recibido';
        $this->evaluado->estado_programacion = 'programado';
        $this->evaluado->save();

        $resultado = $this->evaluado->cambiarEstadoEvaluacion('en_proceso');

        $this->assertTrue($resultado);
        $this->assertEquals('en_proceso', $this->evaluado->fresh()->estado_evaluacion);
    }

    // ========================================
    // S5: Gating En Proceso (debe haber sido programado)
    // ========================================

    public function test_s5_en_proceso_requiere_haber_sido_programado(): void
    {
        $this->evaluado->estado_evaluacion = 'pendiente_de_evaluacion';
        $this->evaluado->estado_formulario = 'formulario_completado_y_recibido';
        $this->evaluado->estado_programacion = 'contactando'; // nunca fue programado
        $this->evaluado->save();

        $this->expectException(ValidationException::class);
        $this->evaluado->cambiarEstadoEvaluacion('en_proceso');
    }

    public function test_s5_en_proceso_con_proceso_realizado_en_programacion_tambien_permite(): void
    {
        $this->evaluado->estado_evaluacion = 'pendiente_de_evaluacion';
        $this->evaluado->estado_formulario = 'formulario_completado_y_recibido';
        $this->evaluado->estado_programacion = 'proceso_realizado';
        $this->evaluado->save();

        $resultado = $this->evaluado->cambiarEstadoEvaluacion('en_proceso');

        $this->assertTrue($resultado);
        $this->assertEquals('en_proceso', $this->evaluado->fresh()->estado_evaluacion);
    }

    // ========================================
    // S6: Auto proceso_realizado al pasar a en_revision
    // ========================================

    public function test_s6_en_revision_dispara_proceso_realizado_en_programacion(): void
    {
        $this->evaluado->estado_evaluacion = 'en_proceso';
        $this->evaluado->estado_programacion = 'programado';
        $this->evaluado->save();

        $this->evaluado->cambiarEstadoEvaluacion('en_revision');

        $evaluadoFresh = $this->evaluado->fresh();
        $this->assertEquals('en_revision', $evaluadoFresh->estado_evaluacion);
        $this->assertEquals('proceso_realizado', $evaluadoFresh->estado_programacion);
    }

    public function test_s6_no_dispara_si_programacion_ya_es_proceso_realizado(): void
    {
        $this->evaluado->estado_evaluacion = 'en_proceso';
        $this->evaluado->estado_programacion = 'proceso_realizado'; // ya está
        $this->evaluado->save();

        // No debe fallar ni crear duplicados
        $this->evaluado->cambiarEstadoEvaluacion('en_revision');

        $evaluadoFresh = $this->evaluado->fresh();
        $this->assertEquals('en_revision', $evaluadoFresh->estado_evaluacion);
        $this->assertEquals('proceso_realizado', $evaluadoFresh->estado_programacion);
    }

    public function test_s6_registra_historial_de_programacion_al_auto_disparar(): void
    {
        $this->evaluado->estado_evaluacion = 'en_proceso';
        $this->evaluado->estado_programacion = 'programado';
        $this->evaluado->save();

        $this->evaluado->cambiarEstadoEvaluacion('en_revision');

        $historial = $this->evaluado->historialEstados()
            ->where('campo', 'estado_programacion')
            ->where('estado_nuevo', 'proceso_realizado')
            ->first();

        $this->assertNotNull($historial, 'Debe haber un registro de historial para la auto-transición S6');
        $this->assertEquals('programado', $historial->estado_anterior);
    }

    // ========================================
    // programarEvaluacion registra historial
    // ========================================

    public function test_programar_evaluacion_registra_historial_estado_programacion(): void
    {
        $poligrafista = User::factory()->create();
        $poligrafista->assignRole('repro');

        $this->evaluado->estado_programacion = 'contactado';
        $this->evaluado->save();

        $this->evaluado->programarEvaluacion(
            now()->addDay()->format('Y-m-d H:i:s'),
            now()->addDay()->addHour()->format('Y-m-d H:i:s'),
            $poligrafista->id
        );

        $historial = $this->evaluado->historialEstados()
            ->where('campo', 'estado_programacion')
            ->where('estado_nuevo', 'programado')
            ->first();

        $this->assertNotNull($historial);
        $this->assertEquals('contactado', $historial->estado_anterior);
    }

    public function test_reprogramar_evaluacion_registra_historial_estado_programacion(): void
    {
        $poligrafista = User::factory()->create();
        $poligrafista->assignRole('repro');

        $this->evaluado->estado_programacion = 'programado';
        $this->evaluado->fecha_programada = now()->addDay()->format('Y-m-d H:i:s');
        $this->evaluado->save();

        $this->evaluado->reprogramarEvaluacion(
            now()->addDays(2)->format('Y-m-d H:i:s'),
            now()->addDays(2)->addHour()->format('Y-m-d H:i:s'),
            $poligrafista->id
        );

        $historial = $this->evaluado->historialEstados()
            ->where('campo', 'estado_programacion')
            ->where('estado_nuevo', 'reprogramado')
            ->first();

        $this->assertNotNull($historial);
        $this->assertEquals('programado', $historial->estado_anterior);
    }
}
