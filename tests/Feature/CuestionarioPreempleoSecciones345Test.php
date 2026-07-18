<?php

namespace Tests\Feature;

use App\Models\CuestionarioRespuesta;
use App\Models\Empresa;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Support\CamposInternosPreempleo;
use App\Support\HistorialLaboralIntegridad;
use App\Support\InformacionComplementaria;
use App\Support\SaludHabitosCampos;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CompletaFlujoCuestionario;
use Tests\TestCase;

class CuestionarioPreempleoSecciones345Test extends TestCase
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
            'token_unico' => 'token-e28-e21',
            'token_expira_at' => now()->addDays(30),
            'dpi' => '1234567890105',
            'tipo_servicio' => 'poligrafo',
            'tipo_formulario' => 'preempleo',
        ]);
    }

    private function avanzarHastaSeccion(int $numero): void
    {
        $this->verificarIdentidadYFlujoPreSeccion($this->evaluado->token_unico, '1234567890105');

        $this->post(route('cuestionario.guardar-seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 1,
        ]), $this->datosSeccion1Preempleo());

        if ($numero >= 2) {
            $this->post(route('cuestionario.guardar-seccion', [
                'token' => $this->evaluado->token_unico,
                'numero' => 2,
            ]), $this->datosSeccion2Preempleo());
        }

        if ($numero >= 3) {
            $this->post(route('cuestionario.guardar-seccion', [
                'token' => $this->evaluado->token_unico,
                'numero' => 3,
            ]), $this->datosSeccion3Preempleo());
        }

        if ($numero >= 4) {
            $this->post(route('cuestionario.guardar-seccion', [
                'token' => $this->evaluado->token_unico,
                'numero' => 4,
            ]), $this->datosSeccion4Preempleo());
        }
    }

    public function test_formacion_academica_y_empleos_en_valor_json(): void
    {
        $this->avanzarHastaSeccion(2);

        $response = $this->post(route('cuestionario.guardar-seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 3,
        ]), array_merge($this->datosSeccion3Preempleo(), $this->datosFormacionAcademicaPreempleo(), $this->datosEmpleosPreempleo()));

        $response->assertSessionHasNoErrors();

        $cuestionario = $this->evaluado->cuestionario()->first();
        $tablas = $cuestionario->getTablasPorNumeroSeccion(3);

        $this->assertCount(5, $tablas['formacion_academica'] ?? []);
        $this->assertCount(1, $tablas['empleos'] ?? []);
        $this->assertSame('Universidad de San Carlos', $tablas['formacion_academica'][4]['institucion'] ?? null);
    }

    public function test_formacion_academica_requiere_todos_los_niveles_visibles(): void
    {
        $this->avanzarHastaSeccion(2);

        $response = $this->post(route('cuestionario.guardar-seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 3,
        ]), array_merge($this->datosSeccion3Preempleo(), [
            'ultimo_nivel_academico' => 'universitario',
            'formacion_academica' => [
                [
                    'nivel' => 'universitario',
                    'estado' => 'completo',
                    'carrera' => 'Administración',
                    'institucion' => 'Universidad de San Carlos',
                    'anio' => '2015',
                    'respaldo' => 'si',
                ],
            ],
        ], $this->datosEmpleosPreempleo()));

        $response->assertSessionHasErrors();
    }

    public function test_integridad_laboral_marcada_como_interna(): void
    {
        $this->avanzarHastaSeccion(2);

        $this->post(route('cuestionario.guardar-seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 3,
        ]), $this->datosSeccion3Preempleo());

        $cuestionario = $this->evaluado->cuestionario()->first();
        $respuesta = CuestionarioRespuesta::where('cuestionario_id', $cuestionario->id)
            ->where('campo', 'integridad_01')
            ->first();

        $this->assertNotNull($respuesta);
        $this->assertSame('interno', $respuesta->metadata['tipo_logico'] ?? null);
        $this->assertTrue(CamposInternosPreempleo::esInterno('integridad_01'));
        $this->assertCount(19, HistorialLaboralIntegridad::claves());
    }

    public function test_deudas_tabla_y_gate(): void
    {
        $this->avanzarHastaSeccion(3);

        $response = $this->post(route('cuestionario.guardar-seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 4,
        ]), array_merge($this->datosSeccion4Preempleo(), $this->datosDeudasPreempleo()));

        $response->assertSessionHasNoErrors();

        $cuestionario = $this->evaluado->cuestionario()->first();
        $tablas = $cuestionario->getTablasPorNumeroSeccion(4);

        $this->assertCount(1, $tablas['deudas'] ?? []);
        $this->assertSame('Banco Industrial', $tablas['deudas'][0]['entidad'] ?? null);
    }

    public function test_situacion_economica_requiere_detalle_vehiculos(): void
    {
        $this->avanzarHastaSeccion(3);

        $response = $this->post(route('cuestionario.guardar-seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 4,
        ]), array_merge($this->datosSeccion4Preempleo(), [
            'econ_posee_vehiculos' => 'si',
        ]));

        $response->assertSessionHasErrors(['econ_detalle_vehiculos']);
    }

    public function test_situacion_economica_guarda_detalle_vehiculos(): void
    {
        $this->avanzarHastaSeccion(3);

        $response = $this->post(route('cuestionario.guardar-seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 4,
        ]), array_merge($this->datosSeccion4Preempleo(), [
            'econ_posee_vehiculos' => 'si',
            'econ_detalle_vehiculos' => 'Toyota Corolla 2018, placa P-123ABC',
            'econ_tiene_fiador' => 'si',
            'econ_detalle_fiador' => 'Juan Pérez, hermano',
        ]));

        $response->assertSessionHasNoErrors();

        $cuestionario = $this->evaluado->cuestionario()->first();
        $resp = $cuestionario->obtenerRespuestasSeccion(4);

        $this->assertSame('si', $resp['econ_posee_vehiculos'] ?? null);
        $this->assertSame('Toyota Corolla 2018, placa P-123ABC', $resp['econ_detalle_vehiculos'] ?? null);
    }

    public function test_sustancias_usadas_se_almacena_como_texto(): void
    {
        $this->avanzarHastaSeccion(4);

        $this->post(route('cuestionario.guardar-seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 5,
        ]), $this->datosSeccion5Preempleo());

        $cuestionario = $this->evaluado->cuestionario()->first();
        $valor = $cuestionario->obtenerRespuestasSeccion(5)['sustancias_usadas'] ?? null;

        $this->assertSame(['ninguna'], SaludHabitosCampos::sustanciasDesdeAlmacenamiento($valor));
    }

    public function test_tatuajes_tabla_condicional(): void
    {
        $this->avanzarHastaSeccion(4);

        $response = $this->post(route('cuestionario.guardar-seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 5,
        ]), array_merge($this->datosSeccion5Preempleo(), [
            'tiene_tatuajes' => 'si',
            'tatuajes' => [
                [
                    'ubicacion' => 'Brazo derecho',
                    'tamano' => 'Mediano',
                    'descripcion' => 'Símbolo tribal',
                    'tiempo' => '5 años',
                    'visible_uniforme' => 'no',
                    'significado' => 'Recuerdo familiar',
                ],
            ],
        ]));

        $response->assertSessionHasNoErrors();

        $cuestionario = $this->evaluado->cuestionario()->first();
        $tablas = $cuestionario->getTablasPorNumeroSeccion(5);

        $this->assertCount(1, $tablas['tatuajes'] ?? []);
    }

    public function test_salud_requiere_detalles_cuando_responde_si(): void
    {
        $this->avanzarHastaSeccion(4);

        $response = $this->post(route('cuestionario.guardar-seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 5,
        ]), array_merge($this->datosSeccion5Preempleo(), [
            'salud_tratamiento_medico' => 'si',
            'salud_hospitalizaciones' => 'si',
            'salud_ausencias_enfermedad' => 'si',
            'salud_ideacion_dano' => 'si',
        ]));

        $response->assertSessionHasErrors([
            'salud_detalle_tratamiento',
            'salud_detalle_hospitalizaciones',
            'salud_detalle_ausencias',
            'salud_detalle_ideacion',
        ]);
    }

    public function test_salud_guarda_detalles_condicionales(): void
    {
        $this->avanzarHastaSeccion(4);

        $response = $this->post(route('cuestionario.guardar-seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 5,
        ]), array_merge($this->datosSeccion5Preempleo(), [
            'salud_tratamiento_medico' => 'si',
            'salud_detalle_tratamiento' => 'Control de presión con losartán',
            'salud_hospitalizaciones' => 'no',
            'salud_ausencias_enfermedad' => 'no',
            'salud_ideacion_dano' => 'no',
        ]));

        $response->assertSessionHasNoErrors();

        $cuestionario = $this->evaluado->cuestionario()->first();
        $resp = $cuestionario->obtenerRespuestasSeccion(5);

        $this->assertSame('Control de presión con losartán', $resp['salud_detalle_tratamiento'] ?? null);
    }

    public function test_complementaria_no_es_campo_interno(): void
    {
        foreach (array_column(InformacionComplementaria::PREGUNTAS, 'key') as $clave) {
            $this->assertFalse(CamposInternosPreempleo::esInterno($clave), "No debe ser interno: {$clave}");
        }
    }

    public function test_seccion_5_muestra_mensajes_finales(): void
    {
        $this->evaluado->cuestionario()->create(array_merge([
            'tipo_formulario' => 'preempleo',
            'seccion_actual' => 5,
            'total_secciones' => 5,
        ], $this->atributosCuestionarioListoParaSecciones()));

        $response = $this->get(route('cuestionario.seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 5,
        ]));

        $response->assertOk();
        $response->assertSee('Información importante');
        $response->assertSee('informacion_adicional_final', false);
        $response->assertSee('Aspecto judicial');
        $response->assertSee('Información complementaria');
    }
}
