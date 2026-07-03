<?php

namespace Tests\Feature;

use App\Models\CuestionarioRespuesta;
use App\Models\Empresa;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CompletaFlujoCuestionario;
use Tests\TestCase;

class CuestionarioPreempleoHijosTest extends TestCase
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
            'token_unico' => 'token-hijos-e24',
            'token_expira_at' => now()->addDays(30),
            'dpi' => '1234567890103',
            'tipo_formulario' => 'preempleo',
        ]);
    }

    public function test_seccion_2_muestra_tabla_hijos_condicional(): void
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
        $response->assertSee('Hijos');
        $response->assertSee('Detalle de hijos');
        $response->assertSee('name="hijos[0][nombre]"', false);
        $response->assertSee('data-condicional-trigger="tiene_hijos"', false);
    }

    public function test_sin_hijos_no_exige_tabla(): void
    {
        $this->verificarIdentidadYFlujoPreSeccion($this->evaluado->token_unico, '1234567890103');
        $this->post(route('cuestionario.guardar-seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 1,
        ]), $this->datosSeccion1Preempleo());

        $response = $this->post(route('cuestionario.guardar-seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 2,
        ]), $this->datosSeccion2Preempleo(['tiene_hijos' => 'no']));

        $response->assertSessionHasNoErrors();
    }

    public function test_con_hijos_requiere_tabla_y_numero(): void
    {
        $this->verificarIdentidadYFlujoPreSeccion($this->evaluado->token_unico, '1234567890103');
        $this->post(route('cuestionario.guardar-seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 1,
        ]), $this->datosSeccion1Preempleo());

        $datos = array_merge($this->datosSeccion2Preempleo(), $this->datosHijosPreempleo());
        unset($datos['hijos'], $datos['numero_hijos']);

        $response = $this->post(route('cuestionario.guardar-seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 2,
        ]), $datos);

        $response->assertSessionHasErrors(['hijos', 'numero_hijos']);
    }

    public function test_fila_hijo_requiere_nombre_y_edad(): void
    {
        $this->verificarIdentidadYFlujoPreSeccion($this->evaluado->token_unico, '1234567890103');
        $this->post(route('cuestionario.guardar-seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 1,
        ]), $this->datosSeccion1Preempleo());

        $datos = array_merge($this->datosSeccion2Preempleo(), $this->datosHijosPreempleo([
            'hijos' => [
                ['nombre' => '', 'edad' => '', 'vive_con_candidato' => '', 'ocupacion' => '', 'telefono' => ''],
            ],
        ]));

        $response = $this->post(route('cuestionario.guardar-seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 2,
        ]), $datos);

        $response->assertSessionHasErrors(['hijos.0.nombre', 'hijos.0.edad', 'hijos.0.vive_con_candidato']);
    }

    public function test_guarda_tabla_hijos_en_valor_json(): void
    {
        $this->verificarIdentidadYFlujoPreSeccion($this->evaluado->token_unico, '1234567890103');
        $this->post(route('cuestionario.guardar-seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 1,
        ]), $this->datosSeccion1Preempleo());

        $this->post(route('cuestionario.guardar-seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 2,
        ]), array_merge($this->datosSeccion2Preempleo(), $this->datosHijosPreempleo()));

        $cuestionario = $this->evaluado->cuestionario()->first();
        $respuestas = $cuestionario->obtenerRespuestasSeccion(2);
        $tablas = $cuestionario->getTablasPorNumeroSeccion(2);

        $this->assertSame('si', $respuestas['tiene_hijos'] ?? null);
        $this->assertSame('2', $respuestas['numero_hijos'] ?? null);
        $this->assertCount(2, $tablas['hijos'] ?? []);
        $this->assertSame('Sofía', $tablas['hijos'][0]['nombre'] ?? null);

        $respuesta = CuestionarioRespuesta::where('cuestionario_id', $cuestionario->id)
            ->where('campo', 'hijos')
            ->first();

        $this->assertNotNull($respuesta?->valor_json);
        $this->assertSame('tabla', $respuesta->metadata['tipo_logico'] ?? null);
    }
}
