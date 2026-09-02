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

/**
 * Observaciones 17-08-2026 — usabilidad de fechas en el cuestionario del candidato.
 */
class CuestionarioFechasUsabilidadTest extends TestCase
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
            'token_unico' => 'token-fechas-usabilidad',
            'token_expira_at' => now()->addDays(30),
            'cuestionario_completado' => false,
            'dpi' => '1234567890101',
            'tipo_formulario' => 'preempleo',
            'tipo_servicio' => 'poligrafo',
        ]);
    }

    public function test_sigue_laborando_guarda_sin_fecha_fin(): void
    {
        $this->avanzarHastaSeccionLaboral();

        $response = $this->postSeccion3([
            'empresa' => 'La mansión',
            'puesto' => 'recepción',
            'fechas_laboradas_inicio' => '2018-01',
            'fechas_laboradas_fin' => '',
            'fechas_laboradas_actual' => '1',
            'ultimo_salario' => '5000',
            'motivo_retiro' => 'Sigue laborando',
        ]);

        $response->assertSessionHasNoErrors();

        $this->assertSame('01/2018 al Actual', $this->fechasLaboradasGuardadas());
    }

    public function test_fecha_fin_anterior_al_inicio_explica_el_motivo(): void
    {
        $this->avanzarHastaSeccionLaboral();

        $response = $this->postSeccion3([
            'empresa' => 'La mansión',
            'puesto' => 'recepción',
            'fechas_laboradas_inicio_mes' => '06',
            'fechas_laboradas_inicio_anio' => '2022',
            'fechas_laboradas_fin_mes' => '01',
            'fechas_laboradas_fin_anio' => '2018',
            'ultimo_salario' => '5000',
            'motivo_retiro' => 'Cambio de trabajo',
        ]);

        $response->assertSessionHasErrors([
            'empleos.0.fechas_laboradas' => 'La fecha de fin no puede ser anterior a la de inicio.',
        ]);
    }

    public function test_falta_solo_la_fecha_de_fin_lo_dice_explicitamente(): void
    {
        $this->avanzarHastaSeccionLaboral();

        $response = $this->postSeccion3([
            'empresa' => 'La mansión',
            'puesto' => 'recepción',
            'fechas_laboradas_inicio_mes' => '01',
            'fechas_laboradas_inicio_anio' => '2018',
            'ultimo_salario' => '5000',
            'motivo_retiro' => 'Cambio de trabajo',
        ]);

        $response->assertSessionHasErrors([
            'empleos.0.fechas_laboradas' => 'Seleccione el mes y año en que terminó. Si todavía trabaja ahí, marque «Sigue laborando».',
        ]);
    }

    public function test_fecha_fin_faltante_conserva_el_mes_de_inicio_en_el_formulario(): void
    {
        $this->avanzarHastaSeccionLaboral();

        $response = $this->postSeccion3([
            'empresa' => 'La mansión',
            'puesto' => 'recepción',
            'fechas_laboradas_inicio_mes' => '01',
            'fechas_laboradas_inicio_anio' => '2018',
            'fechas_laboradas_fin_mes' => '',
            'fechas_laboradas_fin_anio' => '',
            'ultimo_salario' => '5000',
            'motivo_retiro' => 'Cambio de trabajo',
        ]);

        $response->assertSessionHasErrors('empleos.0.fechas_laboradas');

        // El candidato no debe perder lo que ya seleccionó al volver el formulario.
        $formulario = $this->get(route('cuestionario.seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 3,
        ]));

        $formulario->assertOk();
        $formulario->assertSee('<option value="01" selected>Enero</option>', false);
        $formulario->assertSee('<option value="2018" selected>2018</option>', false);
    }

    public function test_fecha_nacimiento_usa_campo_texto_con_mascara(): void
    {
        $this->verificarIdentidadYFlujoPreSeccion($this->evaluado->token_unico, '1234567890101');

        $response = $this->get(route('cuestionario.seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 1,
        ]));

        $response->assertOk();
        $response->assertSee('data-fecha-nacimiento', false);
        $response->assertSee('placeholder="dd/mm/aaaa"', false);
        $response->assertSee('fecha-nacimiento-mask.js', false);
        $response->assertDontSee('type="date"', false);
    }

    public function test_fecha_nacimiento_acepta_formato_con_diagonales(): void
    {
        $this->verificarIdentidadYFlujoPreSeccion($this->evaluado->token_unico, '1234567890101');

        $datos = $this->datosSeccion1Preempleo(['fecha_nacimiento' => '10/12/1987']);

        $response = $this->post(route('cuestionario.guardar-seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 1,
        ]), $datos);

        $response->assertSessionHasNoErrors();

        $cuestionario = $this->evaluado->cuestionario()->first();
        $respuesta = CuestionarioRespuesta::where('cuestionario_id', $cuestionario->id)
            ->where('campo', 'fecha_nacimiento')
            ->first();

        $this->assertNotNull($respuesta);
        $this->assertSame('1987-12-10', $respuesta->valor);
    }

    public function test_fechas_laboradas_usan_selectores_mes_y_anio(): void
    {
        $this->avanzarHastaSeccionLaboral();

        $response = $this->get(route('cuestionario.seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 3,
        ]));

        $response->assertOk();
        $response->assertSee('name="empleos[0][fechas_laboradas_inicio_mes]"', false);
        $response->assertSee('name="empleos[0][fechas_laboradas_inicio_anio]"', false);
        $response->assertSee('name="empleos[0][fechas_laboradas_fin_mes]"', false);
        $response->assertSee('Sigue laborando');

        // Safari/iOS no soporta input[type=month]: no debe quedar ninguno.
        $response->assertDontSee('type="month"', false);
    }

    public function test_selectores_mes_y_anio_guardan_el_periodo(): void
    {
        $this->avanzarHastaSeccionLaboral();

        $response = $this->postSeccion3([
            'empresa' => 'La mansión',
            'puesto' => 'recepción',
            'fechas_laboradas_inicio_mes' => '01',
            'fechas_laboradas_inicio_anio' => '2018',
            'fechas_laboradas_fin_mes' => '06',
            'fechas_laboradas_fin_anio' => '2022',
            'ultimo_salario' => '5000',
            'motivo_retiro' => 'Cambio de trabajo',
        ]);

        $response->assertSessionHasNoErrors();

        $this->assertSame('01/2018 al 06/2022', $this->fechasLaboradasGuardadas());
    }

    public function test_sigue_laborando_con_selectores_mes_y_anio(): void
    {
        $this->avanzarHastaSeccionLaboral();

        $response = $this->postSeccion3([
            'empresa' => 'La mansión',
            'puesto' => 'recepción',
            'fechas_laboradas_inicio_mes' => '01',
            'fechas_laboradas_inicio_anio' => '2018',
            'fechas_laboradas_fin_mes' => '',
            'fechas_laboradas_fin_anio' => '',
            'fechas_laboradas_actual' => '1',
            'ultimo_salario' => '5000',
            'motivo_retiro' => 'Sigue laborando',
        ]);

        $response->assertSessionHasNoErrors();

        $this->assertSame('01/2018 al Actual', $this->fechasLaboradasGuardadas());
    }

    public function test_fechas_laboradas_caso_distribuidora_alinor_acepta_rango_completo(): void
    {
        $this->avanzarHastaSeccionLaboral();

        $response = $this->postSeccion3([
            'empresa' => 'Distribuidora Alinor SA',
            'puesto' => 'Ejecutivo de ventas al detalle y mayoreo',
            'fechas_laboradas_inicio_mes' => '04',
            'fechas_laboradas_inicio_anio' => '2021',
            'fechas_laboradas_fin_mes' => '05',
            'fechas_laboradas_fin_anio' => '2026',
            'ultimo_salario' => '5000',
            'motivo_retiro' => 'Cambio de trabajo',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertSame('04/2021 al 05/2026', $this->fechasLaboradasGuardadas());
    }

    public function test_fecha_nacimiento_acepta_solo_digitos_sin_diagonales(): void
    {
        $this->verificarIdentidadYFlujoPreSeccion($this->evaluado->token_unico, '1234567890101');

        $datos = $this->datosSeccion1Preempleo(['fecha_nacimiento' => '01122002']);

        $response = $this->post(route('cuestionario.guardar-seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 1,
        ]), $datos);

        $response->assertSessionHasNoErrors();

        $cuestionario = $this->evaluado->cuestionario()->first();
        $respuesta = CuestionarioRespuesta::where('cuestionario_id', $cuestionario->id)
            ->where('campo', 'fecha_nacimiento')
            ->first();

        $this->assertNotNull($respuesta);
        $this->assertSame('2002-12-01', $respuesta->valor);
    }

    public function test_error_de_fechas_laboradas_no_se_duplica(): void
    {
        $this->avanzarHastaSeccionLaboral();

        $this->postSeccion3([
            'empresa' => 'La mansión',
            'puesto' => 'recepción',
            'fechas_laboradas_inicio_mes' => '01',
            'fechas_laboradas_inicio_anio' => '2018',
            'ultimo_salario' => '5000',
            'motivo_retiro' => 'Cambio de trabajo',
        ]);

        $response = $this->get(route('cuestionario.seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 3,
        ]));

        $response->assertOk();

        $mensaje = 'Seleccione el mes y año en que terminó';

        // Una vez en el resumen superior y una vez junto al campo.
        $this->assertSame(
            2,
            substr_count($response->getContent(), $mensaje),
            'El mensaje de fechas laboradas se está repitiendo junto al campo.'
        );
    }

    /** @param array<string, mixed> $empleo */
    private function postSeccion3(array $empleo): \Illuminate\Testing\TestResponse
    {
        $datos = array_merge(
            $this->datosSeccion3Preempleo(),
            $this->datosFormacionAcademicaPreempleo(),
            [
                'experiencia_previa' => 'si',
                'empleos' => [$empleo],
            ]
        );

        return $this->post(route('cuestionario.guardar-seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 3,
        ]), $datos);
    }

    private function fechasLaboradasGuardadas(): ?string
    {
        $cuestionario = $this->evaluado->cuestionario()->first();

        $respuesta = CuestionarioRespuesta::where('cuestionario_id', $cuestionario->id)
            ->where('campo', 'empleos')
            ->first();

        return $respuesta?->getTabla()[0]['fechas_laboradas'] ?? null;
    }

    private function avanzarHastaSeccionLaboral(): void
    {
        $this->verificarIdentidadYFlujoPreSeccion($this->evaluado->token_unico, '1234567890101');

        $this->post(route('cuestionario.guardar-seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 1,
        ]), $this->datosSeccion1Preempleo());

        $this->post(route('cuestionario.guardar-seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 2,
        ]), $this->datosSeccion2Preempleo());
    }
}
