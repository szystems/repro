<?php

namespace Tests\Unit;

use App\Models\EvaluadorNota;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Support\InformeWordBloquesEvaluador;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InformeWordBloquesEvaluadorTest extends TestCase
{
    use RefreshDatabase;

    public function test_detecta_bloques_faltantes(): void
    {
        $evaluado = EvaluadoOrden::factory()->create(['orden_id' => Orden::factory()->create()->id]);

        EvaluadorNota::guardarNota($evaluado->id, 'word_salud', '', 'Texto salud', null);

        $this->assertFalse(InformeWordBloquesEvaluador::completos($evaluado->id));
        $this->assertContains('word_habitos', InformeWordBloquesEvaluador::faltantes($evaluado->id));
    }

    public function test_todos_los_bloques_completos(): void
    {
        $evaluado = EvaluadoOrden::factory()->create(['orden_id' => Orden::factory()->create()->id]);

        foreach (InformeWordBloquesEvaluador::BLOQUES as $bloque) {
            EvaluadorNota::guardarNota($evaluado->id, $bloque['slug'], '', 'Contenido '.$bloque['slug'], null);
        }

        $this->assertTrue(InformeWordBloquesEvaluador::completos($evaluado->id));
        $this->assertSame([], InformeWordBloquesEvaluador::faltantes($evaluado->id));
    }
}
