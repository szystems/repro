<?php

namespace Tests\Feature;

use App\Models\Cuestionario;
use App\Models\DocumentoEvaluado;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Support\CuestionarioSecciones;
use App\Support\HistorialLaboralPeriodico;
use App\Support\InformacionComplementaria;
use App\Support\SaludHabitosCampos;
use App\Support\TablaDinamica;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CompletaFlujoCuestionario;
use Tests\TestCase;

class CuestionarioEspecificaTest extends TestCase
{
    use RefreshDatabase, CompletaFlujoCuestionario;

    public function test_especifica_tiene_cinco_secciones_como_periodica(): void
    {
        $this->assertSame(5, Cuestionario::totalSeccionesParaTipo('especifica'));
        $this->assertSame('situacion_laboral', CuestionarioSecciones::slug(3, 'especifica'));
        $this->assertSame('situacion_economica', CuestionarioSecciones::slug(4, 'especifica'));
        $this->assertSame('antecedentes_relevantes', CuestionarioSecciones::slug(5, 'especifica'));
    }

    public function test_especifica_no_incluye_tabla_formacion_completa(): void
    {
        $tablas = TablaDinamica::camposPorSeccion(3, 'especifica');
        $this->assertArrayHasKey('empleo_actual', $tablas);
        $this->assertArrayNotHasKey('formacion_academica', $tablas);

        $tablasPeriodica = TablaDinamica::camposPorSeccion(3, 'periodica');
        $this->assertArrayHasKey('formacion_academica', $tablasPeriodica);
    }

    public function test_documentos_especifica_solo_dpi(): void
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_formulario' => 'especifica',
            'tipo_servicio' => 'poligrafo',
        ]);

        $tipos = DocumentoEvaluado::tiposDocumentoParaEvaluado($evaluado);
        $this->assertArrayHasKey('dpi_archivo', $tipos);
        $this->assertArrayNotHasKey('antecedentes_penales', $tipos);
    }

    public function test_pregunta_1_especifica_tiene_label_amplio_caso(): void
    {
        $this->assertStringContainsString(
            'Circunstancias, fechas, personas involucradas',
            HistorialLaboralPeriodico::labelPregunta1(true)
        );
        $this->assertSame(
            'required|string|min:20|max:8000',
            HistorialLaboralPeriodico::reglasValidacion(true)['periodico_01']
        );
    }

    public function test_seccion_3_especifica_muestra_caso_amplio_sin_historial_academico(): void
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_formulario' => 'especifica',
            'tipo_servicio' => 'poligrafo',
            'token_unico' => 'testespectoken12345678901234567890',
            'token_expira_at' => now()->addDays(7),
            'cuestionario_completado' => false,
            'dpi' => '2405617300405',
        ]);

        Cuestionario::create(array_merge([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'especifica',
            'seccion_actual' => 3,
            'total_secciones' => 5,
        ], $this->atributosCuestionarioListoParaSecciones()));

        $this->get(route('cuestionario.seccion', [
            'token' => $evaluado->token_unico,
            'numero' => 3,
        ]))
            ->assertOk()
            ->assertSee('Circunstancias, fechas, personas involucradas', false)
            ->assertDontSee('Detalle por nivel académico', false)
            ->assertDontSee('solo se solicita el último grado académico', false)
            ->assertDontSee('Sección no disponible', false);
    }

    public function test_seccion_5_especifica_muestra_solo_judicial(): void
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_formulario' => 'especifica',
            'tipo_servicio' => 'poligrafo',
            'token_unico' => 'testespecsec5token123456789012345',
            'token_expira_at' => now()->addDays(7),
            'cuestionario_completado' => false,
            'dpi' => '2405617300405',
        ]);

        Cuestionario::create(array_merge([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'especifica',
            'seccion_actual' => 5,
            'total_secciones' => 5,
        ], $this->atributosCuestionarioListoParaSecciones()));

        $this->get(route('cuestionario.seccion', [
            'token' => $evaluado->token_unico,
            'numero' => 5,
        ]))
            ->assertOk()
            ->assertSee('Aspecto judicial', false)
            ->assertSee('Antecedentes recientes', false)
            ->assertSee(SaludHabitosCampos::LABEL_TATUAJES_PERFORACIONES, false)
            ->assertSee(SaludHabitosCampos::TITULO_SALUD, false)
            ->assertSee(SaludHabitosCampos::LABEL_ALERGIAS, false)
            ->assertSee(SaludHabitosCampos::LABEL_EMBARAZADA, false)
            ->assertDontSee(SaludHabitosCampos::LABEL_PREOCUPACIONES, false)
            ->assertDontSee(InformacionComplementaria::TITULO_BLOQUE, false)
            ->assertDontSee('Sección no disponible', false);
    }
}
