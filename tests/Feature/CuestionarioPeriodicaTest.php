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

class CuestionarioPeriodicaTest extends TestCase
{
    use RefreshDatabase, CompletaFlujoCuestionario;

    public function test_periodica_tiene_cinco_secciones(): void
    {
        $this->assertSame(5, Cuestionario::totalSeccionesParaTipo('periodica'));
        $this->assertSame('situacion_laboral', CuestionarioSecciones::slug(3, 'periodica'));
        $this->assertSame('situacion_economica', CuestionarioSecciones::slug(4, 'periodica'));
        $this->assertSame('antecedentes_recientes', CuestionarioSecciones::slug(5, 'periodica'));
    }

    public function test_historial_laboral_periodico_tiene_31_preguntas(): void
    {
        $this->assertCount(31, HistorialLaboralPeriodico::PREGUNTAS);
        $this->assertCount(31, array_unique(array_column(HistorialLaboralPeriodico::PREGUNTAS, 'key')));
        $this->assertContains('periodico_info_adicional', HistorialLaboralPeriodico::claves());
    }

    public function test_tabla_empleo_actual_periodica(): void
    {
        $columnas = TablaDinamica::camposPorSeccion(3, 'periodica');
        $this->assertArrayHasKey('empleo_actual', $columnas);
        $this->assertArrayHasKey('formacion_academica', $columnas);
        $this->assertCount(5, $columnas['empleo_actual']);
        $labels = array_column($columnas['empleo_actual'], 'label');
        $this->assertContains('Puesto Ocupado', $labels);
        $this->assertContains('Salario mensual', $labels);
        $this->assertContains('Fechas laboradas', $labels);
        $this->assertContains('Motivo de la prueba', $labels);
    }

    public function test_preguntas_periodicas_coinciden_con_pdf(): void
    {
        $this->assertSame(
            'Describa de forma detallada el motivo por el cual se está realizando está prueba:',
            HistorialLaboralPeriodico::PREGUNTAS[0]['label']
        );
        $this->assertSame(
            '¿Usted ha brindado información confidencial de la empresa?',
            HistorialLaboralPeriodico::PREGUNTAS[30]['label']
        );
        $this->assertSame(
            'Desea agregar alguna información laboral:',
            HistorialLaboralPeriodico::CAMPO_INFORMACION_ADICIONAL['label']
        );
    }

    public function test_documentos_periodica_solo_dpi(): void
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_formulario' => 'periodica',
            'tipo_servicio' => 'poligrafo',
        ]);

        $tipos = DocumentoEvaluado::tiposDocumentoParaEvaluado($evaluado);
        $this->assertArrayHasKey('dpi_archivo', $tipos);
        $this->assertArrayNotHasKey('antecedentes_penales', $tipos);
        $this->assertArrayNotHasKey('constancia_laboral', $tipos);
    }

    public function test_seccion_2_periodica_omite_hermanos_en_vista_y_validacion(): void
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_formulario' => 'periodica',
            'tipo_servicio' => 'poligrafo',
            'token_unico' => 'testperiodsec2token12345678901234',
            'token_expira_at' => now()->addDays(7),
            'cuestionario_completado' => false,
            'dpi' => '2405617300305',
        ]);

        Cuestionario::create(array_merge([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'periodica',
            'seccion_actual' => 2,
            'total_secciones' => 5,
        ], $this->atributosCuestionarioListoParaSecciones()));

        $this->get(route('cuestionario.seccion', [
            'token' => $evaluado->token_unico,
            'numero' => 2,
        ]))
            ->assertOk()
            ->assertDontSee('¿Tiene hermanos?', false)
            ->assertDontSee('Detalle de hermanos', false);

        $payload = $this->datosSeccion2Preempleo();
        unset($payload['tiene_hermanos'], $payload['hermanos']);

        $this->post(route('cuestionario.guardar-seccion', [
            'token' => $evaluado->token_unico,
            'numero' => 2,
        ]), array_merge($payload, ['action' => 'siguiente']))
            ->assertSessionDoesntHaveErrors(['tiene_hermanos', 'hermanos'])
            ->assertRedirect();
    }

    public function test_seccion_3_periodica_muestra_vista_laboral_propia(): void
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_formulario' => 'periodica',
            'tipo_servicio' => 'poligrafo',
            'token_unico' => 'testperiodtoken123456789012345678',
            'token_expira_at' => now()->addDays(7),
            'cuestionario_completado' => false,
            'dpi' => '2405617300305',
        ]);

        Cuestionario::create(array_merge([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'periodica',
            'seccion_actual' => 3,
            'total_secciones' => 5,
        ], $this->atributosCuestionarioListoParaSecciones()));

        $this->get(route('cuestionario.seccion', [
            'token' => $evaluado->token_unico,
            'numero' => 3,
        ]))
            ->assertOk()
            ->assertSee('Preguntas complementarias de su empleo actual', false)
            ->assertSee('Tabla de información laboral', false)
            ->assertSee('Formación académica', false)
            ->assertSee('Detalle por nivel académico', false)
            ->assertSee('Complete solo el último grado que seleccionó arriba', false)
            ->assertDontSee('desde primaria hasta el último nivel', false);
    }

    public function test_seccion_5_periodica_muestra_antecedentes_no_generica(): void
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_formulario' => 'periodica',
            'tipo_servicio' => 'poligrafo',
            'token_unico' => 'testperiodsec5token12345678901234',
            'token_expira_at' => now()->addDays(7),
            'cuestionario_completado' => false,
            'dpi' => '2405617300305',
        ]);

        Cuestionario::create(array_merge([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'periodica',
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
