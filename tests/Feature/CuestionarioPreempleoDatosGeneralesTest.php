<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CompletaFlujoCuestionario;
use Tests\TestCase;

class CuestionarioPreempleoDatosGeneralesTest extends TestCase
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
            'token_unico' => 'token-preempleo-21',
            'token_expira_at' => now()->addDays(30),
            'dpi' => '1234567890101',
            'tipo_documento' => 'dpi',
            'tipo_formulario' => 'preempleo',
        ]);
    }

    public function test_seccion_1_muestra_campos_datos_generales_2_1(): void
    {
        $this->evaluado->cuestionario()->create(array_merge([
            'tipo_formulario' => 'preempleo',
            'seccion_actual' => 1,
            'total_secciones' => 5,
        ], $this->atributosCuestionarioListoParaSecciones()));

        $response = $this->get(route('cuestionario.seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 1,
        ]));

        $response->assertOk();
        $response->assertSee('Tipo de identificación');
        $response->assertSee('Departamento de nacimiento');
        $response->assertSee('Licencia de conducir');
        $response->assertSee('Teléfono de emergencia');
        $response->assertDontSee('name="genero"', false);
        $response->assertDontSee('name="profesion_oficio"', false);
        $response->assertDontSee('name="nivel_educativo"', false);
    }

    public function test_guarda_campos_nuevos_y_edad_calculada(): void
    {
        $this->verificarIdentidadYFlujoPreSeccion($this->evaluado->token_unico, '1234567890101');

        $response = $this->post(route('cuestionario.guardar-seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 1,
        ]), $this->datosSeccion1Preempleo([
            'igss' => '123456789',
            'nit' => '9876543-2',
            'licencia_conducir' => 'si',
            'departamento_nacimiento' => 'Quetzaltenango',
            'municipio_nacimiento' => 'Quetzaltenango',
        ]));

        $response->assertSessionHasNoErrors();

        $cuestionario = $this->evaluado->cuestionario()->first();
        $respuestas = $cuestionario->obtenerRespuestasSeccion(1);

        $this->assertSame('123456789', $respuestas['igss'] ?? null);
        $this->assertSame('9876543-2', $respuestas['nit'] ?? null);
        $this->assertSame('si', $respuestas['licencia_conducir'] ?? null);
        $this->assertSame('Quetzaltenango', $respuestas['departamento_nacimiento'] ?? null);
        $this->assertSame((string) \Carbon\Carbon::parse('1990-05-15')->age, $respuestas['edad'] ?? null);
    }
}
