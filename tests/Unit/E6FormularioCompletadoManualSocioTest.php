<?php

namespace Tests\Unit;

use App\Models\EvaluadoOrden;
use App\Models\Orden;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class E6FormularioCompletadoManualSocioTest extends TestCase
{
    use RefreshDatabase;

    private function crearEvaluado(array $attrs): EvaluadoOrden
    {
        $orden = Orden::factory()->create();

        return EvaluadoOrden::factory()->create(array_merge([
            'orden_id' => $orden->id,
        ], $attrs));
    }

    public function test_poligrafo_no_puede_saltar_a_completado_desde_link_pendiente(): void
    {
        $evaluado = $this->crearEvaluado([
            'tipo_servicio' => 'poligrafo',
            'tipo_formulario' => 'preempleo',
            'estado_formulario' => 'link_pendiente',
        ]);

        $this->assertFalse($evaluado->puedeMarcarFormularioCompletadoManualSocio());
        $this->assertFalse($evaluado->puedeTransicionarEstadoFormulario('formulario_completado_y_recibido'));
        $this->assertFalse($evaluado->cambiarEstadoFormulario('formulario_completado_y_recibido'));
        $this->assertSame('link_pendiente', $evaluado->fresh()->estado_formulario);
    }

    public function test_socio_puede_marcar_completado_desde_link_pendiente(): void
    {
        $evaluado = $this->crearEvaluado([
            'tipo_servicio' => 'socioeconomico',
            'tipo_formulario' => 'preempleo',
            'estado_formulario' => 'link_pendiente',
            'cuestionario_completado' => false,
        ]);

        $this->assertTrue($evaluado->puedeMarcarFormularioCompletadoManualSocio());
        $this->assertContains(
            'formulario_completado_y_recibido',
            $evaluado->transicionesFormularioDisponibles()
        );
        $this->assertTrue($evaluado->cambiarEstadoFormulario('formulario_completado_y_recibido'));

        $evaluado->refresh();
        $this->assertSame('formulario_completado_y_recibido', $evaluado->estado_formulario);
        $this->assertTrue($evaluado->cuestionario_completado);
        $this->assertNotNull($evaluado->completado_at);
    }

    public function test_socio_puede_marcar_completado_desde_link_enviado(): void
    {
        $evaluado = $this->crearEvaluado([
            'tipo_servicio' => 'socioeconomico',
            'tipo_formulario' => 'preempleo',
            'estado_formulario' => 'link_enviado',
        ]);

        $this->assertTrue($evaluado->cambiarEstadoFormulario('formulario_completado_y_recibido', 'Recibido vía Jotform'));
        $this->assertSame('formulario_completado_y_recibido', $evaluado->fresh()->estado_formulario);
    }
}
