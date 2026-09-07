<?php

namespace Tests\Unit;

use App\Models\Empresa;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Models\User;
use App\Support\EvaluadorNotasSupport;
use App\Support\InformePreliminarDesdeWord;
use App\Support\InformeWordBloquesEvaluador;
use App\Support\InformeWordResultado;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InformePreliminarDesdeWordTest extends TestCase
{
    use RefreshDatabase;

    private function evaluadoPoli(array $attrs = []): EvaluadoOrden
    {
        $empresa = Empresa::factory()->create();
        $orden = Orden::factory()->create(['empresa_id' => $empresa->id]);

        return EvaluadoOrden::factory()->create(array_merge([
            'orden_id' => $orden->id,
            'tipo_servicio' => 'poligrafo',
            'texto_informe_preliminar' => null,
        ], $attrs));
    }

    public function test_copia_tabla_cuando_preliminar_esta_vacio(): void
    {
        $evaluado = $this->evaluadoPoli(['resultado' => 'no_aprobado']);
        $autor = User::factory()->create();
        EvaluadorNotasSupport::guardarDesdeRequest($evaluado->id, [
            InformeWordResultado::NOTA_INDICACION_MENTIRA => 'preguntas 1 y 7',
            InformeWordBloquesEvaluador::NOTA_OBSERVACIONES => 'vocabulario despectivo',
        ], $autor->id);

        InformePreliminarDesdeWord::copiarTablaSiPreliminarVacio($evaluado);

        $html = $evaluado->fresh()->texto_informe_preliminar;
        $this->assertStringContainsString('<table', $html);
        $this->assertStringContainsString('Resultado:', $html);
        $this->assertStringContainsString('Observaciones:', $html);
        $this->assertStringContainsString('No aprobado', $html);
        $this->assertStringContainsString('preguntas 1 y 7', $html);
        $this->assertStringContainsString('vocabulario despectivo', $html);
    }

    public function test_no_pisa_un_preliminar_ya_escrito(): void
    {
        $evaluado = $this->evaluadoPoli([
            'resultado' => 'aprobado',
            'texto_informe_preliminar' => '<p>Ya redactado a mano</p>',
        ]);

        InformePreliminarDesdeWord::copiarTablaSiPreliminarVacio($evaluado);

        $this->assertSame('<p>Ya redactado a mano</p>', $evaluado->fresh()->texto_informe_preliminar);
    }
}
