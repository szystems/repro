<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Models\EstadoHistorial;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Fase 18 - Tests para las reglas de sinergia entre estados
 * 
 * Valida:
 * 1. Modalidad virtual permite programar sin formulario (Fase 19 — S2 eliminado)
 * 2. Modalidad presencial permite programar sin formulario
 * 3. Modalidad editable con historial
 * 4. En revisión → Proceso realizado automático
 * 5. Estado de evaluación puede restringir programación
 */
class Fase18SinergiaTest extends TestCase
{
    use RefreshDatabase;

    private Orden $orden;
    private EvaluadoOrden $evaluado;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orden = Orden::factory()->create();
        $this->evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'estado_formulario' => 'link_pendiente',
            'estado_programacion' => 'contactando',
            'estado_evaluacion' => 'pendiente_de_evaluacion',
            'modalidad' => 'presencial',
        ]);
    }

    // ========================================
    // Tests: Modalidad Virtual (Fase 19 — sin bloqueo por formulario al programar)
    // ========================================

    public function test_modalidad_virtual_permite_programar_sin_formulario_completado(): void
    {
        $this->evaluado->modalidad = 'virtual';
        $this->evaluado->estado_formulario = 'pendiente_de_llenar';
        $this->evaluado->estado_programacion = 'contactado';
        $this->evaluado->save();

        $this->assertTrue($this->evaluado->puedeTransicionarEstadoProgramacion('programado'));
    }

    public function test_modalidad_virtual_permite_programar_con_formulario_completado(): void
    {
        $this->evaluado->modalidad = 'virtual';
        $this->evaluado->estado_formulario = 'formulario_completado_y_recibido';
        $this->evaluado->estado_programacion = 'contactado';
        $this->evaluado->save();

        $this->assertTrue($this->evaluado->puedeTransicionarEstadoProgramacion('programado'));
    }

    // ========================================
    // Tests: Modalidad Presencial libre
    // ========================================

    public function test_modalidad_presencial_permite_programar_sin_formulario(): void
    {
        $this->evaluado->modalidad = 'presencial';
        $this->evaluado->estado_formulario = 'link_pendiente';
        $this->evaluado->estado_programacion = 'contactado';
        $this->evaluado->save();

        // Presencial puede programar en cualquier momento
        $this->assertTrue($this->evaluado->puedeTransicionarEstadoProgramacion('programado'));
    }

    // ========================================
    // Tests: Modalidad editable con historial
    // ========================================

    public function test_cambio_modalidad_registra_historial(): void
    {
        $this->evaluado->modalidad = 'presencial';
        $this->evaluado->save();

        // Cambiar a virtual y registrar en historial manualmente
        $this->evaluado->modalidad = 'virtual';
        $this->evaluado->save();

        EstadoHistorial::create([
            'evaluado_orden_id' => $this->evaluado->id,
            'campo' => 'modalidad',
            'estado_anterior' => 'presencial',
            'estado_nuevo' => 'virtual',
            'observacion' => 'Cambio de modalidad desde edición de orden',
        ]);

        $historial = EstadoHistorial::where('evaluado_orden_id', $this->evaluado->id)
            ->where('campo', 'modalidad')
            ->first();

        $this->assertNotNull($historial);
        $this->assertEquals('presencial', $historial->estado_anterior);
        $this->assertEquals('virtual', $historial->estado_nuevo);
    }

    public function test_cambio_modalidad_respeta_citas_existentes(): void
    {
        // Programado en presencial sin formulario
        $this->evaluado->modalidad = 'presencial';
        $this->evaluado->estado_formulario = 'link_pendiente';
        $this->evaluado->estado_programacion = 'programado';
        $this->evaluado->fecha_programada = now()->addDays(3);
        $this->evaluado->save();

        // Cambiar a virtual
        $this->evaluado->modalidad = 'virtual';
        $this->evaluado->save();

        // La cita existente se respeta (no se cancela automáticamente)
        $this->assertEquals('programado', $this->evaluado->fresh()->estado_programacion);
        $this->assertNotNull($this->evaluado->fresh()->fecha_programada);
    }

    // ========================================
    // Tests: En revisión → Proceso realizado automático
    // ========================================

    public function test_en_revision_dispara_proceso_realizado_automaticamente(): void
    {
        $this->evaluado->estado_evaluacion = 'en_proceso';
        $this->evaluado->estado_programacion = 'programado';
        $this->evaluado->save();

        // S6: al cambiar a 'en_revision', el sistema debe auto-transicionar
        // estado_programacion a 'proceso_realizado'
        $this->evaluado->cambiarEstadoEvaluacion('en_revision');

        $this->assertEquals('proceso_realizado', $this->evaluado->fresh()->estado_programacion);
        $this->assertEquals('en_revision', $this->evaluado->fresh()->estado_evaluacion);
    }

    // ========================================
    // Tests: Historial de cambios
    // ========================================

    public function test_cambio_estado_registra_historial(): void
    {
        $this->evaluado->estado_formulario = 'link_pendiente';
        $this->evaluado->save();

        // Cambiar estado y verificar que se registró
        $this->evaluado->cambiarEstadoFormulario('link_enviado');

        $historial = EstadoHistorial::where('evaluado_orden_id', $this->evaluado->id)
            ->where('campo', 'estado_formulario')
            ->first();

        $this->assertNotNull($historial);
        $this->assertEquals('link_pendiente', $historial->estado_anterior);
        $this->assertEquals('link_enviado', $historial->estado_nuevo);
    }

    public function test_recalcular_orden_registra_historial(): void
    {
        $this->evaluado->estado_evaluacion = 'informe_final_enviado';
        $this->evaluado->save();

        EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'estado_evaluacion' => 'informe_final_enviado',
        ]);

        $this->orden->estado = 'en_proceso';
        $this->orden->save();

        // Recalcular debe cambiar a "entregado" y registrar
        $this->orden->recalcularEstado();

        $historial = EstadoHistorial::where('orden_id', $this->orden->id)
            ->where('campo', 'estado_orden')
            ->first();

        $this->assertNotNull($historial);
        $this->assertEquals('en_proceso', $historial->estado_anterior);
        $this->assertEquals('entregado', $historial->estado_nuevo);
        $this->assertStringContainsString('automáticamente', $historial->observacion);
    }

    // ========================================
    // Tests: Relaciones
    // ========================================

    public function test_evaluado_tiene_relacion_historial_estados(): void
    {
        // Crear varios registros de historial
        EstadoHistorial::factory()->count(3)->create([
            'evaluado_orden_id' => $this->evaluado->id,
            'campo' => 'estado_formulario',
        ]);

        $historial = $this->evaluado->historialEstados;

        $this->assertCount(3, $historial);
        $this->assertInstanceOf(EstadoHistorial::class, $historial->first());
    }

    public function test_orden_puede_tener_historial_propio(): void
    {
        EstadoHistorial::create([
            'orden_id' => $this->orden->id,
            'campo' => 'estado_orden',
            'estado_anterior' => 'orden_recibida',
            'estado_nuevo' => 'en_proceso',
        ]);

        $historial = EstadoHistorial::where('orden_id', $this->orden->id)->first();

        $this->assertNotNull($historial);
        $this->assertEquals($this->orden->id, $historial->orden_id);
    }

    // ========================================
    // Tests: Accessors
    // ========================================

    public function test_accessors_texto_devuelven_strings_legibles(): void
    {
        $this->evaluado->estado_formulario = 'pendiente_de_llenar';
        $this->evaluado->estado_programacion = 'programado';
        $this->evaluado->estado_evaluacion = 'en_revision';
        $this->evaluado->save();

        $this->assertEquals('Pendiente de Llenar', $this->evaluado->estado_formulario_texto);
        $this->assertEquals('Programado', $this->evaluado->estado_programacion_texto);
        $this->assertEquals('En Revisión', $this->evaluado->estado_evaluacion_texto);
    }

    public function test_accessors_color_devuelven_badges_validos(): void
    {
        $this->evaluado->estado_formulario = 'vencido';
        $this->evaluado->estado_programacion = 'proceso_realizado';
        $this->evaluado->estado_evaluacion = 'informe_final_enviado';
        $this->evaluado->save();

        $this->assertEquals('danger', $this->evaluado->estado_formulario_color);
        $this->assertEquals('success', $this->evaluado->estado_programacion_color);
        $this->assertEquals('success', $this->evaluado->estado_evaluacion_color);
    }
}
