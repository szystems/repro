<?php

namespace Tests\Feature;

use App\Models\CuestionarioRespuesta;
use App\Models\Empresa;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Support\ResumenFamiliar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CompletaFlujoCuestionario;
use Tests\TestCase;

class CuestionarioPreempleoSeccion2ExtendidaTest extends TestCase
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
            'token_unico' => 'token-e25-e27',
            'token_expira_at' => now()->addDays(30),
            'dpi' => '1234567890104',
            'tipo_formulario' => 'preempleo',
        ]);
    }

    private function prepararSeccion1(): void
    {
        $this->verificarIdentidadYFlujoPreSeccion($this->evaluado->token_unico, '1234567890104');
        $this->post(route('cuestionario.guardar-seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 1,
        ]), $this->datosSeccion1Preempleo());
    }

    public function test_hermanos_gate_y_tabla_valor_json(): void
    {
        $this->prepararSeccion1();

        $response = $this->post(route('cuestionario.guardar-seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 2,
        ]), array_merge($this->datosSeccion2Preempleo(), $this->datosHermanosPreempleo()));

        $response->assertSessionHasNoErrors();

        $cuestionario = $this->evaluado->cuestionario()->first();
        $tablas = $cuestionario->getTablasPorNumeroSeccion(2);

        $this->assertSame('si', $cuestionario->obtenerRespuestasSeccion(2)['tiene_hermanos'] ?? null);
        $this->assertCount(1, $tablas['hermanos'] ?? []);
        $this->assertSame('Ana Pérez', $tablas['hermanos'][0]['nombre'] ?? null);

        $respuesta = CuestionarioRespuesta::where('cuestionario_id', $cuestionario->id)
            ->where('campo', 'hermanos')
            ->first();

        $this->assertSame('tabla', $respuesta?->metadata['tipo_logico'] ?? null);
    }

    public function test_sin_hermanos_limpia_tabla(): void
    {
        $this->prepararSeccion1();

        $this->post(route('cuestionario.guardar-seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 2,
        ]), array_merge($this->datosSeccion2Preempleo(), $this->datosHermanosPreempleo()));

        $this->post(route('cuestionario.guardar-seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 2,
        ]), $this->datosSeccion2Preempleo(['tiene_hermanos' => 'no']));

        $cuestionario = $this->evaluado->cuestionario()->first();
        $tablas = $cuestionario->getTablasPorNumeroSeccion(2);

        $this->assertSame([], $tablas['hermanos'] ?? []);
    }

    public function test_expareja_condicional_requerida(): void
    {
        $this->prepararSeccion1();

        $datos = array_merge($this->datosSeccion2Preempleo(), [
            'tuvo_matrimonio_union_hijos' => 'si',
        ]);

        $response = $this->post(route('cuestionario.guardar-seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 2,
        ]), $datos);

        $response->assertSessionHasErrors(['expareja_nombre', 'expareja_tipo_relacion']);
    }

    public function test_expareja_guarda_datos(): void
    {
        $this->prepararSeccion1();

        $response = $this->post(route('cuestionario.guardar-seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 2,
        ]), array_merge($this->datosSeccion2Preempleo(), $this->datosExparejaPreempleo()));

        $response->assertSessionHasNoErrors();

        $cuestionario = $this->evaluado->cuestionario()->first();
        $respuestas = $cuestionario->obtenerRespuestasSeccion(2);

        $this->assertSame('Ana López', $respuestas['expareja_nombre'] ?? null);
        $this->assertSame('matrimonio', $respuestas['expareja_tipo_relacion'] ?? null);
    }

    public function test_resumen_familiar_compila_tablas(): void
    {
        $this->prepararSeccion1();

        $this->post(route('cuestionario.guardar-seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 2,
        ]), array_merge(
            $this->datosSeccion2Preempleo(),
            $this->datosHermanosPreempleo(),
            $this->datosExparejaPreempleo()
        ));

        $cuestionario = $this->evaluado->cuestionario()->first();
        $resumen = ResumenFamiliar::compilar($cuestionario);

        $this->assertNotEmpty($resumen['convive_con']);
        $this->assertTrue($resumen['expareja']['aplica'] ?? false);
        $this->assertCount(1, $resumen['hermanos'] ?? []);
    }
}
