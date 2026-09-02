<?php

namespace Tests\Feature;

use App\Models\Cuestionario;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Support\CamposInternosPreempleo;
use App\Support\CuestionarioPresentacionDashboard;
use App\Support\InformacionComplementaria;
use App\Support\SaludHabitosCampos;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CompletaFlujoCuestionario;
use Tests\TestCase;

/** M-F2/F3: alergias + embarazo en todos los formularios; peri/espe solo esas de salud. */
class CuestionarioSaludAlergiasEmbarazoTest extends TestCase
{
    use RefreshDatabase, CompletaFlujoCuestionario;

    public function test_labels_y_reglas_alergias_embarazo(): void
    {
        $this->assertSame('¿Padece alergias?', SaludHabitosCampos::LABEL_ALERGIAS);
        $this->assertSame('¿Está embarazada?', SaludHabitosCampos::LABEL_EMBARAZADA);

        $reglas = SaludHabitosCampos::reglasAlergiasEmbarazo();
        $this->assertSame('required|in:si,no', $reglas['salud_alergias']);
        $this->assertSame('required|in:si,no', $reglas['salud_embarazada']);
        $this->assertArrayHasKey('salud_detalle_alergias', SaludHabitosCampos::reglasValidacion());

        $this->assertTrue(CamposInternosPreempleo::esInterno('salud_alergias'));
        $this->assertTrue(CamposInternosPreempleo::esInterno('salud_embarazada'));
    }

    public function test_preempleo_y_socio_muestran_alergias_y_embarazo_en_salud_completa(): void
    {
        foreach (['preempleo' => 5, 'socioeconomico' => 6] as $tipo => $total) {
            $evaluado = $this->crearEvaluadoListo($tipo, $total);

            $this->get(route('cuestionario.seccion', [
                'token' => $evaluado->token_unico,
                'numero' => 5,
            ]))
                ->assertOk()
                ->assertSee(SaludHabitosCampos::LABEL_ALERGIAS, false)
                ->assertSee(SaludHabitosCampos::LABEL_EMBARAZADA, false)
                ->assertSee(SaludHabitosCampos::LABEL_PREOCUPACIONES, false)
                ->assertSee('Si no aplica, seleccione No.', false);
        }
    }

    public function test_periodica_y_especifica_muestran_bloque_salud_corto(): void
    {
        foreach (['periodica', 'especifica'] as $tipo) {
            $evaluado = $this->crearEvaluadoListo($tipo, 5);

            $this->get(route('cuestionario.seccion', [
                'token' => $evaluado->token_unico,
                'numero' => 5,
            ]))
                ->assertOk()
                ->assertSee(SaludHabitosCampos::TITULO_SALUD, false)
                ->assertSee(SaludHabitosCampos::LABEL_ALERGIAS, false)
                ->assertSee(SaludHabitosCampos::LABEL_EMBARAZADA, false)
                ->assertDontSee(SaludHabitosCampos::LABEL_PREOCUPACIONES, false)
                ->assertDontSee(InformacionComplementaria::TITULO_BLOQUE, false);
        }
    }

    public function test_periodica_exige_alergias_embarazo_y_detalle_si_padece(): void
    {
        $evaluado = $this->crearEvaluadoListo('periodica', 5);

        $this->post(route('cuestionario.guardar-seccion', [
            'token' => $evaluado->token_unico,
            'numero' => 5,
        ]), $this->datosSeccion5PeriodicaEspecifica([
            'salud_alergias' => '',
            'salud_embarazada' => '',
        ]))->assertSessionHasErrors(['salud_alergias', 'salud_embarazada']);

        $this->post(route('cuestionario.guardar-seccion', [
            'token' => $evaluado->token_unico,
            'numero' => 5,
        ]), $this->datosSeccion5PeriodicaEspecifica([
            'salud_alergias' => 'si',
            'salud_embarazada' => 'no',
        ]))->assertSessionHasErrors(['salud_detalle_alergias']);
    }

    public function test_periodica_guarda_alergias_con_detalle(): void
    {
        $evaluado = $this->crearEvaluadoListo('periodica', 5);

        $this->post(route('cuestionario.guardar-seccion', [
            'token' => $evaluado->token_unico,
            'numero' => 5,
        ]), $this->datosSeccion5PeriodicaEspecifica([
            'salud_alergias' => 'si',
            'salud_detalle_alergias' => 'Penicilina y polvo',
            'salud_embarazada' => 'no',
        ]))->assertSessionHasNoErrors();

        $resp = $evaluado->cuestionario()->first()->obtenerRespuestasSeccion(5);
        $this->assertSame('si', $resp['salud_alergias'] ?? null);
        $this->assertSame('Penicilina y polvo', $resp['salud_detalle_alergias'] ?? null);
        $this->assertSame('no', $resp['salud_embarazada'] ?? null);
    }

    public function test_preempleo_guarda_alergias_y_embarazo(): void
    {
        $evaluado = $this->crearEvaluadoListo('preempleo', 5);

        $this->post(route('cuestionario.guardar-seccion', [
            'token' => $evaluado->token_unico,
            'numero' => 5,
        ]), $this->datosSeccion5Preempleo([
            'salud_alergias' => 'si',
            'salud_detalle_alergias' => 'Mariscos',
            'salud_embarazada' => 'si',
        ]))->assertSessionHasNoErrors();

        $resp = $evaluado->cuestionario()->first()->obtenerRespuestasSeccion(5);
        $this->assertSame('si', $resp['salud_alergias'] ?? null);
        $this->assertSame('Mariscos', $resp['salud_detalle_alergias'] ?? null);
        $this->assertSame('si', $resp['salud_embarazada'] ?? null);
    }

    public function test_dashboard_peri_incluye_bloque_salud_corto(): void
    {
        $titulos = array_column(
            CuestionarioPresentacionDashboard::bloquesPreguntas(5, 'periodica'),
            'titulo'
        );
        $this->assertContains(SaludHabitosCampos::TITULO_SALUD, $titulos);

        $clavesPreempleo = array_column(
            CuestionarioPresentacionDashboard::bloquesPreguntas(5, 'preempleo')[0]['preguntas'] ?? [],
            'key'
        );
        $this->assertContains('salud_alergias', $clavesPreempleo);
        $this->assertContains('salud_embarazada', $clavesPreempleo);
    }

    private function crearEvaluadoListo(string $tipoFormulario, int $totalSecciones): EvaluadoOrden
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_formulario' => $tipoFormulario,
            'tipo_servicio' => $tipoFormulario === 'socioeconomico' ? 'socioeconomico' : 'poligrafo',
            'token_unico' => 'mf2'.$tipoFormulario.str_repeat('x', 20),
            'token_expira_at' => now()->addDays(7),
            'cuestionario_completado' => false,
            'dpi' => '2405617300505',
        ]);

        Cuestionario::create(array_merge([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => $tipoFormulario,
            'seccion_actual' => 5,
            'total_secciones' => $totalSecciones,
        ], $this->atributosCuestionarioListoParaSecciones()));

        return $evaluado;
    }
}
