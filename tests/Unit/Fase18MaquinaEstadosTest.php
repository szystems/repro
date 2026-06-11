<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Models\Empresa;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Fase 18 - Tests para las 3 máquinas de estado independientes
 * 
 * Valida transiciones permitidas y bloqueadas para:
 * - estado_formulario (5 valores)
 * - estado_programacion (8 valores)
 * - estado_evaluacion (7 valores)
 */
class Fase18MaquinaEstadosTest extends TestCase
{
    use RefreshDatabase;

    private Orden $orden;
    private EvaluadoOrden $evaluado;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear orden y evaluado de prueba
        $this->orden = Orden::factory()->create();
        $this->evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'estado_formulario' => 'link_pendiente',
            'estado_programacion' => 'contactando',
            'estado_evaluacion' => 'pendiente_de_evaluacion',
        ]);
    }

    // ========================================
    // Tests: estado_formulario (5 valores)
    // ========================================

    public function test_formulario_link_pendiente_puede_transicionar_a_link_enviado(): void
    {
        $this->evaluado->estado_formulario = 'link_pendiente';
        $this->evaluado->save();

        $this->assertTrue($this->evaluado->puedeTransicionarEstadoFormulario('link_enviado'));
        $this->assertTrue($this->evaluado->cambiarEstadoFormulario('link_enviado'));
        $this->assertEquals('link_enviado', $this->evaluado->fresh()->estado_formulario);
    }

    public function test_formulario_link_enviado_puede_transicionar_a_pendiente_de_llenar(): void
    {
        $this->evaluado->estado_formulario = 'link_enviado';
        $this->evaluado->save();

        $this->assertTrue($this->evaluado->puedeTransicionarEstadoFormulario('pendiente_de_llenar'));
    }

    public function test_formulario_link_enviado_puede_transicionar_a_vencido(): void
    {
        $this->evaluado->estado_formulario = 'link_enviado';
        $this->evaluado->save();

        $this->assertTrue($this->evaluado->puedeTransicionarEstadoFormulario('vencido'));
    }

    public function test_formulario_pendiente_de_llenar_puede_transicionar_a_completado(): void
    {
        $this->evaluado->estado_formulario = 'pendiente_de_llenar';
        $this->evaluado->save();

        $this->assertTrue($this->evaluado->puedeTransicionarEstadoFormulario('formulario_completado_y_recibido'));
    }

    public function test_formulario_completado_es_estado_final(): void
    {
        $this->evaluado->estado_formulario = 'formulario_completado_y_recibido';
        $this->evaluado->save();

        $this->assertFalse($this->evaluado->puedeTransicionarEstadoFormulario('vencido'));
        $this->assertFalse($this->evaluado->puedeTransicionarEstadoFormulario('link_pendiente'));
    }

    public function test_formulario_vencido_es_estado_final(): void
    {
        $this->evaluado->estado_formulario = 'vencido';
        $this->evaluado->save();

        $this->assertFalse($this->evaluado->puedeTransicionarEstadoFormulario('pendiente_de_llenar'));
    }

    // ========================================
    // Tests: estado_programacion (8 valores)
    // ========================================

    public function test_programacion_contactando_puede_transicionar_a_contactado(): void
    {
        $this->evaluado->estado_programacion = 'contactando';
        $this->evaluado->save();

        $this->assertTrue($this->evaluado->puedeTransicionarEstadoProgramacion('contactado'));
    }

    public function test_programacion_contactado_puede_transicionar_a_programado(): void
    {
        $this->evaluado->estado_programacion = 'contactado';
        $this->evaluado->save();

        $this->assertTrue($this->evaluado->puedeTransicionarEstadoProgramacion('programado'));
    }

    public function test_programacion_programado_puede_transicionar_a_proceso_realizado(): void
    {
        $this->evaluado->estado_programacion = 'programado';
        $this->evaluado->save();

        $this->assertTrue($this->evaluado->puedeTransicionarEstadoProgramacion('proceso_realizado'));
    }

    public function test_programacion_programado_puede_transicionar_a_inasistencia(): void
    {
        $this->evaluado->estado_programacion = 'programado';
        $this->evaluado->save();

        $this->assertTrue($this->evaluado->puedeTransicionarEstadoProgramacion('inasistencia'));
    }

    public function test_programacion_inasistencia_puede_transicionar_a_reprogramado(): void
    {
        $this->evaluado->estado_programacion = 'inasistencia';
        $this->evaluado->save();

        $this->assertTrue($this->evaluado->puedeTransicionarEstadoProgramacion('reprogramado'));
    }

    public function test_programacion_reprogramado_puede_volver_a_contactando(): void
    {
        $this->evaluado->estado_programacion = 'reprogramado';
        $this->evaluado->save();

        $this->assertTrue($this->evaluado->puedeTransicionarEstadoProgramacion('contactando'));
    }

    public function test_programacion_desistio_puede_reactivarse_a_contactando(): void
    {
        // Respuesta cliente #8: Desistió es reactivable
        $this->evaluado->estado_programacion = 'desistio';
        $this->evaluado->save();

        $this->assertTrue($this->evaluado->puedeTransicionarEstadoProgramacion('contactando'));
    }

    public function test_programacion_proceso_realizado_es_estado_final(): void
    {
        $this->evaluado->estado_programacion = 'proceso_realizado';
        $this->evaluado->save();

        $this->assertFalse($this->evaluado->puedeTransicionarEstadoProgramacion('programado'));
    }

    public function test_programacion_cancelado_es_estado_final(): void
    {
        $this->evaluado->estado_programacion = 'cancelado';
        $this->evaluado->save();

        $this->assertFalse($this->evaluado->puedeTransicionarEstadoProgramacion('contactando'));
    }

    // ========================================
    // Tests: estado_evaluacion (7 valores)
    // PDF cliente p.2: Pendiente de evaluación → En proceso → En revisión → Resultado Preliminar → Informe final enviado
    // Cancelado/Desistió solo desde pendiente_de_evaluacion
    // ========================================

    public function test_evaluacion_pendiente_puede_transicionar_a_en_proceso(): void
    {
        $this->evaluado->estado_evaluacion = 'pendiente_de_evaluacion';
        $this->evaluado->save();

        $this->assertTrue($this->evaluado->puedeTransicionarEstadoEvaluacion('en_proceso'));
    }

    public function test_evaluacion_pendiente_puede_transicionar_a_cancelado(): void
    {
        $this->evaluado->estado_evaluacion = 'pendiente_de_evaluacion';
        $this->evaluado->save();

        $this->assertTrue($this->evaluado->puedeTransicionarEstadoEvaluacion('cancelado'));
    }

    public function test_evaluacion_pendiente_puede_transicionar_a_desistio(): void
    {
        $this->evaluado->estado_evaluacion = 'pendiente_de_evaluacion';
        $this->evaluado->save();

        $this->assertTrue($this->evaluado->puedeTransicionarEstadoEvaluacion('desistio'));
    }

    public function test_evaluacion_en_proceso_solo_manual_no_automatico(): void
    {
        // Respuesta cliente #2: "En Proceso" es 100% manual
        $this->evaluado->estado_evaluacion = 'pendiente_de_evaluacion';
        $this->evaluado->save();

        // Simular subida de preliminar SIN cambiar estado
        $this->evaluado->archivo_resultado_preliminar = 'ruta/archivo.pdf';
        $this->evaluado->save();

        // Estado evaluacion NO debe cambiar automáticamente
        $this->assertEquals('pendiente_de_evaluacion', $this->evaluado->fresh()->estado_evaluacion);
    }

    public function test_evaluacion_en_proceso_puede_transicionar_a_en_revision(): void
    {
        $this->evaluado->estado_evaluacion = 'en_proceso';
        $this->evaluado->save();

        $this->assertTrue($this->evaluado->puedeTransicionarEstadoEvaluacion('en_revision'));
    }

    public function test_evaluacion_en_proceso_no_puede_cancelarse(): void
    {
        // PDF p.2: Cancelado solo desde pendiente_de_evaluacion
        $this->evaluado->estado_evaluacion = 'en_proceso';
        $this->evaluado->save();

        $this->assertFalse($this->evaluado->puedeTransicionarEstadoEvaluacion('cancelado'));
    }

    public function test_evaluacion_en_revision_puede_transicionar_a_resultado_preliminar(): void
    {
        $this->evaluado->estado_evaluacion = 'en_revision';
        $this->evaluado->save();

        $this->assertTrue($this->evaluado->puedeTransicionarEstadoEvaluacion('resultado_preliminar'));
    }

    public function test_evaluacion_resultado_preliminar_puede_transicionar_a_informe_final(): void
    {
        $this->evaluado->estado_evaluacion = 'resultado_preliminar';
        $this->evaluado->save();

        $this->assertTrue($this->evaluado->puedeTransicionarEstadoEvaluacion('informe_final_enviado'));
    }

    public function test_evaluacion_informe_final_es_estado_final(): void
    {
        $this->evaluado->estado_evaluacion = 'informe_final_enviado';
        $this->evaluado->save();

        $this->assertFalse($this->evaluado->puedeTransicionarEstadoEvaluacion('en_revision'));
        $this->assertFalse($this->evaluado->puedeTransicionarEstadoEvaluacion('cancelado'));
    }

    public function test_evaluacion_cancelado_puede_reactivarse(): void
    {
        $this->evaluado->estado_evaluacion = 'cancelado';
        $this->evaluado->save();

        $this->assertTrue($this->evaluado->puedeTransicionarEstadoEvaluacion('pendiente_de_evaluacion'));
    }

    public function test_evaluacion_desistio_puede_reactivarse(): void
    {
        // Respuesta cliente #8: Desistió también es reactivable
        $this->evaluado->estado_evaluacion = 'desistio';
        $this->evaluado->save();

        $this->assertTrue($this->evaluado->puedeTransicionarEstadoEvaluacion('pendiente_de_evaluacion'));
    }

    // ========================================
    // Tests: Independencia entre estados
    // ========================================

    public function test_cambiar_formulario_no_afecta_programacion(): void
    {
        $this->evaluado->estado_formulario = 'link_enviado';
        $this->evaluado->estado_programacion = 'contactando';
        $this->evaluado->save();

        $this->evaluado->cambiarEstadoFormulario('pendiente_de_llenar');

        $this->assertEquals('pendiente_de_llenar', $this->evaluado->fresh()->estado_formulario);
        $this->assertEquals('contactando', $this->evaluado->fresh()->estado_programacion);
    }

    public function test_cambiar_programacion_no_afecta_evaluacion(): void
    {
        $this->evaluado->estado_programacion = 'contactado';
        $this->evaluado->estado_evaluacion = 'pendiente_de_evaluacion';
        $this->evaluado->save();

        $this->evaluado->cambiarEstadoProgramacion('programado');

        $this->assertEquals('programado', $this->evaluado->fresh()->estado_programacion);
        $this->assertEquals('pendiente_de_evaluacion', $this->evaluado->fresh()->estado_evaluacion);
    }

    // ========================================
    // Tests: Orden.recalcularEstado()
    // ========================================

    public function test_orden_recalcula_a_entregado_cuando_todos_con_informe_final(): void
    {
        $this->evaluado->estado_evaluacion = 'informe_final_enviado';
        $this->evaluado->save();

        EvaluadoOrden::factory()->count(3)->create([
            'orden_id' => $this->orden->id,
            'estado_evaluacion' => 'informe_final_enviado',
        ]);

        $this->orden->recalcularEstado();

        $this->assertEquals('entregado', $this->orden->fresh()->estado);
    }

    public function test_orden_recalcula_a_entregado_con_mezcla_de_estados_terminales(): void
    {
        // PDF p.1: Entregado cuando todos están en informe_final_enviado, cancelado o desistio
        $this->evaluado->estado_evaluacion = 'informe_final_enviado';
        $this->evaluado->save();

        EvaluadoOrden::factory()->create(['orden_id' => $this->orden->id, 'estado_evaluacion' => 'cancelado']);
        EvaluadoOrden::factory()->create(['orden_id' => $this->orden->id, 'estado_evaluacion' => 'desistio']);

        $this->orden->recalcularEstado();

        $this->assertEquals('entregado', $this->orden->fresh()->estado);
    }

    public function test_orden_recalcula_a_en_proceso_cuando_alguno_en_proceso(): void
    {
        EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'estado_evaluacion' => 'en_proceso',
        ]);
        EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'estado_evaluacion' => 'pendiente_de_evaluacion',
        ]);

        $this->orden->recalcularEstado();

        $this->assertEquals('en_proceso', $this->orden->fresh()->estado);
    }

    public function test_orden_recalcula_a_cancelado_cuando_todos_cancelados(): void
    {
        $this->evaluado->estado_evaluacion = 'cancelado';
        $this->evaluado->save();

        EvaluadoOrden::factory()->count(2)->create([
            'orden_id' => $this->orden->id,
            'estado_evaluacion' => 'cancelado',
        ]);

        $this->orden->recalcularEstado();

        $this->assertEquals('cancelado', $this->orden->fresh()->estado);
    }

    public function test_orden_recalcula_a_cancelado_cuando_todos_cancelados_o_desistieron(): void
    {
        // PDF p.1: Cancelado cuando todos están en cancelado o desistio
        $this->evaluado->estado_evaluacion = 'cancelado';
        $this->evaluado->save();

        EvaluadoOrden::factory()->create(['orden_id' => $this->orden->id, 'estado_evaluacion' => 'desistio']);

        $this->orden->recalcularEstado();

        $this->assertEquals('cancelado', $this->orden->fresh()->estado);
    }

    public function test_orden_mantiene_orden_recibida_cuando_todos_iniciales(): void
    {
        EvaluadoOrden::factory()->count(2)->create([
            'orden_id' => $this->orden->id,
            'estado_evaluacion' => 'pendiente_de_evaluacion',
        ]);

        $this->orden->estado = 'orden_recibida';
        $this->orden->save();

        $this->orden->recalcularEstado();

        $this->assertEquals('orden_recibida', $this->orden->fresh()->estado);
    }
}
