<?php

namespace Tests\Unit;

use App\Models\Empresa;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Models\Sede;
use App\Support\CitaProgramadaContenido;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CitaProgramadaContenidoTest extends TestCase
{
    use RefreshDatabase;

    public function test_plantilla_sigue_el_tipo_de_servicio(): void
    {
        $this->assertSame('vsa', CitaProgramadaContenido::plantilla($this->evaluado(['tipo_servicio' => 'vsa'])));
        $this->assertSame('socioeconomico', CitaProgramadaContenido::plantilla($this->evaluado(['tipo_servicio' => 'socioeconomico'])));
        $this->assertSame('poligrafo', CitaProgramadaContenido::plantilla($this->evaluado(['tipo_servicio' => 'poligrafo'])));
        $desconocido = new EvaluadoOrden(['tipo_servicio' => 'otro']);
        $this->assertSame('poligrafo', CitaProgramadaContenido::plantilla($desconocido));
    }

    public function test_sede_solo_si_no_es_virtual(): void
    {
        $conSede = $this->evaluado(['modalidad' => 'presencial']);
        $virtual = $this->evaluado(['modalidad' => 'virtual', 'sede_id' => $conSede->sede_id]);

        $this->assertTrue(CitaProgramadaContenido::mostrarSedeYDireccion($conSede));
        $this->assertFalse(CitaProgramadaContenido::mostrarSedeYDireccion($virtual));
    }

    private function evaluado(array $attrs = []): EvaluadoOrden
    {
        $empresa = Empresa::factory()->create(['estado' => 1]);
        $sede = Sede::factory()->create(['estado' => 1]);
        $orden = Orden::factory()->create(['empresa_id' => $empresa->id]);

        return EvaluadoOrden::factory()->create(array_merge([
            'orden_id' => $orden->id,
            'sede_id' => $sede->id,
            'tipo_servicio' => 'poligrafo',
            'modalidad' => 'presencial',
        ], $attrs));
    }
}
