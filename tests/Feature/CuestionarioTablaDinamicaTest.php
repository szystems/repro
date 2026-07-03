<?php

namespace Tests\Feature;

use App\Models\Cuestionario;
use App\Models\CuestionarioRespuesta;
use App\Models\Empresa;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CompletaFlujoCuestionario;
use Tests\Support\FakeImage;
use Tests\TestCase;

class CuestionarioTablaDinamicaTest extends TestCase
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
            'token_unico' => 'token-tabla-dinamica-e11',
            'token_expira_at' => now()->addDays(30),
            'cuestionario_completado' => false,
            'dpi' => '1234567890101',
            'tipo_formulario' => 'preempleo',
            'tipo_servicio' => 'poligrafo',
        ]);
    }

    public function test_guarda_tabla_hijos_en_valor_json(): void
    {
        $this->verificarIdentidadYFlujoPreSeccion($this->evaluado->token_unico, '1234567890101');

        $this->post(route('cuestionario.guardar-seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 1,
        ]), $this->datosSeccion1Preempleo());

        $response = $this->post(route('cuestionario.guardar-seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 2,
        ]), $this->datosSeccion2ConHijos());

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $cuestionario = $this->evaluado->cuestionario()->first();

        $this->assertDatabaseHas('cuestionario_respuestas', [
            'cuestionario_id' => $cuestionario->id,
            'seccion' => 'informacion_familiar',
            'campo' => 'hijos',
        ]);

        $respuesta = CuestionarioRespuesta::where('cuestionario_id', $cuestionario->id)
            ->where('campo', 'hijos')
            ->first();

        $this->assertNotNull($respuesta->valor_json);
        $this->assertCount(2, $respuesta->getTabla());
        $this->assertSame('Sofía', $respuesta->getTabla()[0]['nombre']);
        $this->assertSame('tabla', $respuesta->metadata['tipo_logico'] ?? null);
    }

    public function test_seccion_2_muestra_filas_guardadas(): void
    {
        $this->verificarIdentidadYFlujoPreSeccion($this->evaluado->token_unico, '1234567890101');

        $cuestionario = $this->evaluado->cuestionario()->first();
        $cuestionario->update([
            'instrucciones_leidas_at' => now(),
            'acepta_terminos' => true,
            'seccion_actual' => 2,
        ]);

        CuestionarioRespuesta::guardarTabla($cuestionario->id, 'informacion_familiar', 'hijos', [
            ['nombre' => 'Pedro', 'edad' => '12', 'vive_con_candidato' => 'si', 'ocupacion' => 'Estudiante', 'telefono' => '55550000'],
        ]);

        $response = $this->get(route('cuestionario.seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 2,
        ]));

        $response->assertOk();
        $response->assertSee('Detalle de hijos');
        $response->assertSee('value="Pedro"', false);
    }

    public function test_requiere_tabla_hijos_cuando_tiene_hijos_es_si(): void
    {
        $this->verificarIdentidadYFlujoPreSeccion($this->evaluado->token_unico, '1234567890101');

        $this->post(route('cuestionario.guardar-seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 1,
        ]), $this->datosSeccion1Preempleo());

        $datos = $this->datosSeccion2ConHijos();
        unset($datos['hijos']);

        $response = $this->post(route('cuestionario.guardar-seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 2,
        ]), $datos);

        $response->assertSessionHasErrors('hijos');
    }

    public function test_guardar_y_continuar_redirige_a_siguiente_seccion(): void
    {
        $this->verificarIdentidadYFlujoPreSeccion($this->evaluado->token_unico, '1234567890101');

        $response = $this->post(route('cuestionario.guardar-seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 1,
        ]), array_merge($this->datosSeccion1Preempleo(), ['action' => 'siguiente']));

        $response->assertRedirect(route('cuestionario.seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 2,
        ]));

        $cuestionario = $this->evaluado->cuestionario()->first();
        $this->assertEquals(2, $cuestionario->seccion_actual);
    }

    public function test_guardar_borrador_permanece_en_misma_seccion(): void
    {
        $this->verificarIdentidadYFlujoPreSeccion($this->evaluado->token_unico, '1234567890101');

        $this->post(route('cuestionario.guardar-seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 1,
        ]), array_merge($this->datosSeccion1Preempleo(), ['action' => 'borrador']));

        $cuestionario = $this->evaluado->cuestionario()->first();
        $this->assertEquals(1, $cuestionario->seccion_actual);

        $response = $this->get(route('cuestionario.seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 1,
        ]));

        $response->assertOk();
        $response->assertSee('Borrador guardado correctamente');
    }

    public function test_seccion_2_sin_hijos_redirige_a_seccion_3(): void
    {
        $this->verificarIdentidadYFlujoPreSeccion($this->evaluado->token_unico, '1234567890101');

        $this->post(route('cuestionario.guardar-seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 1,
        ]), $this->datosSeccion1Preempleo());

        $response = $this->post(route('cuestionario.guardar-seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 2,
        ]), array_merge($this->datosSeccion2SinHijos(), ['action' => 'siguiente']));

        $response->assertRedirect(route('cuestionario.seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 3,
        ]));
        $response->assertSessionHasNoErrors();

        $cuestionario = $this->evaluado->cuestionario()->first();
        $this->assertEquals(3, $cuestionario->seccion_actual);
    }

    /** @return array<string, mixed> */
    private function datosSeccion2ConHijos(): array
    {
        return array_merge($this->datosSeccion2Preempleo(), $this->datosParejaPreempleo(), $this->datosHijosPreempleo([
            'estado_civil_detalle' => 'casado',
            'personas_hogar' => 4,
            'dependientes_economicos' => 2,
            'tipo_vivienda' => 'propia_pagada',
            'personas_contribuyen_gastos' => 2,
        ]));
    }

    /** @return array<string, mixed> */
    private function datosSeccion2SinHijos(): array
    {
        return $this->datosSeccion2Preempleo();
    }
}
