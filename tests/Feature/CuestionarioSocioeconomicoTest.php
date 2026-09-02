<?php

namespace Tests\Feature;

use App\Models\Cuestionario;
use App\Models\CuestionarioRespuesta;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Support\CuestionarioSecciones;
use App\Support\SocioeconomicoComplementariaCampos;
use App\Support\TablaDinamica;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CompletaFlujoCuestionario;
use Tests\TestCase;

class CuestionarioSocioeconomicoTest extends TestCase
{
    use CompletaFlujoCuestionario, RefreshDatabase;

    public function test_socioeconomico_tiene_seis_secciones(): void
    {
        $this->assertSame(6, Cuestionario::totalSeccionesParaTipo('socioeconomico'));
        $this->assertSame(
            'informacion_socioeconomica_complementaria',
            CuestionarioSecciones::slug(6, 'socioeconomico')
        );
    }

    public function test_evaluado_socio_resuelve_tipo_formulario_cuestionario(): void
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_servicio' => 'socioeconomico',
            'tipo_formulario' => 'preempleo',
        ]);

        $this->assertSame('socioeconomico', $evaluado->tipoFormularioCuestionario());
    }

    public function test_socioeconomico_sincroniza_cuestionario_desalineado_y_muestra_hermanos(): void
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_servicio' => 'socioeconomico',
            'tipo_formulario' => 'preempleo',
            'token_unico' => 'testsociosyncmishtoken12345678901',
            'token_expira_at' => now()->addDays(7),
            'cuestionario_completado' => false,
            'dpi' => '2405617300205',
        ]);

        Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'periodica',
            'seccion_actual' => 2,
            'total_secciones' => 5,
            'instrucciones_leidas_at' => now(),
            'acepta_terminos' => true,
            'acepta_terminos_at' => now(),
            'acepta_infornet' => true,
            'acepta_infornet_at' => now(),
        ]);

        $this->get(route('cuestionario.seccion', [
            'token' => $evaluado->token_unico,
            'numero' => 2,
        ]))
            ->assertOk()
            ->assertSee('¿Tiene hermanos?', false);

        $evaluado->refresh();
        $this->assertSame('socioeconomico', $evaluado->cuestionario->tipo_formulario);
        $this->assertSame(6, $evaluado->cuestionario->total_secciones);
    }

    public function test_guardar_seccion_6_persiste_tablas_y_totales(): void
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_servicio' => 'socioeconomico',
            'tipo_formulario' => 'preempleo',
            'token_unico' => 'testsociotoken123456789012345678',
            'token_expira_at' => now()->addDays(7),
            'cuestionario_completado' => false,
        ]);

        $cuestionario = Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'socioeconomico',
            'seccion_actual' => 6,
            'total_secciones' => 6,
            'progreso_porcentaje' => 80,
            'estado' => 'en_progreso',
            'completado' => false,
            'acepta_terminos' => true,
            'acepta_terminos_at' => now(),
            'acepta_infornet' => true,
            'acepta_infornet_at' => now(),
            'instrucciones_leidas_at' => now(),
        ]);

        $payload = [
            'referencias_familiares' => [
                ['nombre' => 'Ana Pérez', 'parentesco' => 'Madre', 'telefono' => '50211111111', 'direccion' => 'Zona 1', 'lugar_trabajo' => 'N/A'],
                ['nombre' => 'Luis Pérez', 'parentesco' => 'Padre', 'telefono' => '50222222222', 'direccion' => 'Zona 2', 'lugar_trabajo' => 'N/A'],
            ],
            'referencias_personales' => [
                ['nombre' => 'María López', 'relacion' => 'Amiga', 'telefono' => '50233333333', 'anos_conocerlo' => '5'],
                ['nombre' => 'Carlos Ruiz', 'relacion' => 'Compañero', 'telefono' => '50244444444', 'anos_conocerlo' => '3'],
            ],
            'bienes' => [
                ['descripcion' => 'Motocicleta', 'valor' => '15000'],
            ],
            'presupuesto' => array_map(
                fn (array $fila) => ['concepto' => $fila['concepto'], 'monto' => '100'],
                SocioeconomicoComplementariaCampos::filasPresupuestoIniciales()
            ),
            'viv_tiempo_residencia' => '4 años',
            'viv_propietario' => 'Juan Dueño',
            'viv_habitantes_detalle' => 'Cuatro personas: padres y dos hijos',
            'viv_refs_ubicacion' => 'Frente al parque central',
            'viv_zona_riesgo' => 'no',
            'comp_ha_laborado_empresa' => 'No he laborado para esta empresa.',
            'action' => 'borrador',
        ];

        $this->post(route('cuestionario.guardar-seccion', [
            'token' => $evaluado->token_unico,
            'numero' => 6,
        ]), $payload)->assertRedirect();

        $slug = CuestionarioSecciones::slug(6, 'socioeconomico');
        $cuestionario->refresh();

        $tablas = $cuestionario->getTablasPorSeccion($slug);
        $this->assertCount(2, $tablas['referencias_familiares'] ?? []);
        $this->assertCount(1, $tablas['bienes'] ?? []);

        $respuestas = $cuestionario->getRespuestasPorSeccion($slug);
        $this->assertSame(1000.0, (float) ($respuestas['presupuesto_total'] ?? 0));

        $totales = SocioeconomicoComplementariaCampos::calcularTotales(
            $tablas['bienes'] ?? [],
            $tablas['presupuesto'] ?? []
        );
        $this->assertSame(15000.0, $totales['bienes_total']);
        $this->assertSame(1000.0, $totales['presupuesto_total']);
    }

    public function test_seccion_6_ignora_filas_vacias_en_tablas(): void
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_servicio' => 'socioeconomico',
            'tipo_formulario' => 'preempleo',
            'token_unico' => 'testsociotokenfilasvacias123456789',
            'token_expira_at' => now()->addDays(7),
            'cuestionario_completado' => false,
        ]);

        Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'socioeconomico',
            'seccion_actual' => 6,
            'total_secciones' => 6,
            'progreso_porcentaje' => 80,
            'estado' => 'en_progreso',
            'completado' => false,
            'acepta_terminos' => true,
            'acepta_terminos_at' => now(),
            'acepta_infornet' => true,
            'acepta_infornet_at' => now(),
            'instrucciones_leidas_at' => now(),
        ]);

        $payload = [
            'referencias_familiares' => [
                ['nombre' => 'Ana Pérez', 'parentesco' => 'Madre', 'telefono' => '50211111111', 'direccion' => 'Zona 1'],
                ['nombre' => 'Luis Pérez', 'parentesco' => 'Padre', 'telefono' => '50222222222', 'direccion' => 'Zona 2'],
                ['nombre' => '', 'parentesco' => '', 'telefono' => '', 'direccion' => ''],
            ],
            'referencias_personales' => [
                ['nombre' => 'María López', 'relacion' => 'Amiga', 'telefono' => '50233333333', 'anos_conocerlo' => '5'],
                ['nombre' => 'Carlos Ruiz', 'relacion' => 'Compañero', 'telefono' => '50244444444', 'anos_conocerlo' => '3'],
            ],
            'presupuesto' => array_map(
                fn (array $fila) => ['concepto' => $fila['concepto'], 'monto' => '50'],
                SocioeconomicoComplementariaCampos::filasPresupuestoIniciales()
            ),
            'viv_tiempo_residencia' => '4 años',
            'viv_habitantes_detalle' => 'Cuatro personas',
            'viv_refs_ubicacion' => 'Cerca del mercado',
            'viv_zona_riesgo' => 'no',
            'comp_ha_laborado_empresa' => 'No he laborado para esta empresa.',
            'action' => 'finalizar',
        ];

        $response = $this->post(route('cuestionario.guardar-seccion', [
            'token' => $evaluado->token_unico,
            'numero' => 6,
        ]), $payload);

        $response->assertRedirect(route('cuestionario.finalizar', ['token' => $evaluado->token_unico]));
        $response->assertSessionHasNoErrors();
    }

    public function test_socioeconomico_empleos_validan_fechas_laboradas_como_preempleo(): void
    {
        $columnas = TablaDinamica::camposPorSeccion(3, 'socioeconomico')['empleos'] ?? [];

        $this->assertSame('fechas_laboradas', $columnas[2]['key'] ?? null);
        $this->assertSame('date_range', $columnas[2]['type'] ?? null);
    }

    public function test_socioeconomico_guarda_fechas_laboradas_con_selectores_mes_anio(): void
    {
        Storage::fake('local');

        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_servicio' => 'socioeconomico',
            'tipo_formulario' => 'preempleo',
            'token_unico' => 'testsociofechaslaboradastoken1',
            'token_expira_at' => now()->addDays(7),
            'cuestionario_completado' => false,
            'dpi' => '1234567890101',
        ]);

        $this->verificarIdentidadYFlujoPreSeccion($evaluado->token_unico, '1234567890101');

        $this->post(route('cuestionario.guardar-seccion', [
            'token' => $evaluado->token_unico,
            'numero' => 1,
        ]), $this->datosSeccion1Preempleo());

        $this->post(route('cuestionario.guardar-seccion', [
            'token' => $evaluado->token_unico,
            'numero' => 2,
        ]), $this->datosSeccion2Preempleo());

        $response = $this->post(route('cuestionario.guardar-seccion', [
            'token' => $evaluado->token_unico,
            'numero' => 3,
        ]), array_merge(
            $this->datosSeccion3Preempleo(),
            $this->datosFormacionAcademicaPreempleo(),
            [
                'experiencia_previa' => 'si',
                'empleos' => [[
                    'empresa' => 'Distribuidora Alinor',
                    'puesto' => 'Ventas',
                    'fechas_laboradas_inicio_mes' => '04',
                    'fechas_laboradas_inicio_anio' => '2021',
                    'fechas_laboradas_fin_mes' => '05',
                    'fechas_laboradas_fin_anio' => '2026',
                    'ultimo_salario' => '4500',
                    'motivo_retiro' => 'Cambio de trabajo',
                ]],
            ]
        ));

        $response->assertSessionHasNoErrors();

        $cuestionario = $evaluado->cuestionario()->firstOrFail();
        $respuesta = CuestionarioRespuesta::where('cuestionario_id', $cuestionario->id)
            ->where('campo', 'empleos')
            ->first();

        $this->assertNotNull($respuesta);
        $this->assertSame('04/2021 al 05/2026', $respuesta->getTabla()[0]['fechas_laboradas'] ?? null);
    }

    public function test_documentos_socio_incluyen_recibo_luz(): void
    {
        $tipos = \App\Models\DocumentoEvaluado::tiposDocumentoParaServicio('socioeconomico');
        $this->assertArrayHasKey('recibo_luz', $tipos);
        $this->assertArrayHasKey('constancia_laboral', $tipos);
        $this->assertArrayHasKey('foto_tatuaje', $tipos);
        $this->assertSame('Tatuajes', $tipos['foto_tatuaje']);
    }
}
