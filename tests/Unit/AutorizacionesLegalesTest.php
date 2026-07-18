<?php

namespace Tests\Unit;

use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Support\AutorizacionesLegales;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutorizacionesLegalesTest extends TestCase
{
    use RefreshDatabase;

    public function test_clave_plantilla_poligrafo_periodica(): void
    {
        $evaluado = EvaluadoOrden::factory()->make([
            'tipo_servicio' => 'poligrafo',
            'tipo_formulario' => 'periodica',
        ]);

        $this->assertSame('poligrafo_periodica', AutorizacionesLegales::clavePlantilla($evaluado));
    }

    public function test_socioeconomico_siempre_preempleo(): void
    {
        $evaluado = EvaluadoOrden::factory()->make([
            'tipo_servicio' => 'socioeconomico',
            'tipo_formulario' => 'periodica',
        ]);

        $this->assertSame('socioeconomico_preempleo', AutorizacionesLegales::clavePlantilla($evaluado));
    }

    public function test_requiere_infornet_solo_preempleo(): void
    {
        $pre = EvaluadoOrden::factory()->make(['tipo_formulario' => 'preempleo']);
        $periodica = EvaluadoOrden::factory()->make(['tipo_formulario' => 'periodica']);

        $this->assertTrue(AutorizacionesLegales::requiereInfornet($pre));
        $this->assertFalse(AutorizacionesLegales::requiereInfornet($periodica));
    }

    public function test_render_incluye_motivo_hecho_en_periodica(): void
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_servicio' => 'poligrafo',
            'tipo_formulario' => 'periodica',
            'motivo_hecho_evaluacion' => 'Ascenso a jefe de área',
        ]);

        $html = AutorizacionesLegales::renderHtml($evaluado);

        $this->assertStringContainsString('Ascenso a jefe de área', $html);
        $this->assertStringContainsString('PERIÓDICA', $html);
    }
}
