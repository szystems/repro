<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CompletaFlujoCuestionario;
use Tests\TestCase;

class CuestionarioPreempleoSeccionDuplicadosTest extends TestCase
{
    use RefreshDatabase, CompletaFlujoCuestionario;

    private EvaluadoOrden $evaluado;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $empresa = Empresa::factory()->create();
        $orden = Orden::factory()->create(['empresa_id' => $empresa->id]);
        $this->evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'token_unico' => 'token-i6-duplicados',
            'token_expira_at' => now()->addDays(30),
            'dpi' => '1234567890101',
            'tipo_formulario' => 'preempleo',
        ]);
    }

    public function test_i6_informacion_familiar_no_incluye_campos_hogar(): void
    {
        $html = file_get_contents(resource_path('views/cuestionario/secciones/informacion-familiar.blade.php'));

        $this->assertStringNotContainsString('name="personas_hogar"', $html);
        $this->assertStringNotContainsString('name="dependientes_economicos"', $html);
    }

    public function test_i6_situacion_economica_incluye_campos_hogar(): void
    {
        $html = file_get_contents(resource_path('views/cuestionario/secciones/situacion-economica.blade.php'));

        $this->assertStringContainsString('name="personas_hogar"', $html);
        $this->assertStringContainsString('name="dependientes_economicos"', $html);
        $this->assertStringContainsString('Vivienda y gastos del hogar', $html);
    }

    public function test_i6_seccion_2_no_muestra_campos_hogar_en_vista(): void
    {
        $this->evaluado->cuestionario()->create(array_merge([
            'tipo_formulario' => 'preempleo',
            'seccion_actual' => 2,
            'total_secciones' => 5,
        ], $this->atributosCuestionarioListoParaSecciones()));

        $response = $this->get(route('cuestionario.seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 2,
        ]));

        $response->assertOk();
        $response->assertDontSee('name="personas_hogar"', false);
        $response->assertDontSee('Número de personas en el hogar', false);
    }

    public function test_i6_seccion_4_precarga_desde_seccion_2_legacy(): void
    {
        $cuestionario = $this->evaluado->cuestionario()->create(array_merge([
            'tipo_formulario' => 'preempleo',
            'seccion_actual' => 4,
            'total_secciones' => 5,
        ], $this->atributosCuestionarioListoParaSecciones()));

        $cuestionario->guardarRespuestasSeccion('informacion-familiar', [
            'personas_hogar' => '5',
            'dependientes_economicos' => '2',
        ], []);

        $response = $this->get(route('cuestionario.seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 4,
        ]));

        $response->assertOk();
        $response->assertSee('value="5"', false);
        $response->assertSee('value="2"', false);
        $response->assertSee('Vivienda y gastos del hogar', false);
    }
}
