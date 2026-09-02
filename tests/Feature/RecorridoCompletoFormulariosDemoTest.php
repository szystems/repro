<?php

namespace Tests\Feature;

use App\Models\Cuestionario;
use App\Models\EvaluadoOrden;
use App\Models\User;
use App\Support\HistorialLaboralPeriodico;
use App\Support\SocioeconomicoComplementariaCampos;
use Database\Seeders\DemoPruebaManualE1Seeder;
use Database\Seeders\DemoPruebaManualE4Seeder;
use Database\Seeders\DemoPruebaManualE5EspecificaSeeder;
use Database\Seeders\DemoPruebaManualE5PeriodicaSeeder;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CompletaFlujoCuestionario;
use Tests\TestCase;

/**
 * Recorrido de punta a punta (verificar → secciones → completar) sobre tokens demo.
 *
 * Ejecutar seeders demo antes o dejar que cada test los invoque.
 */
class RecorridoCompletoFormulariosDemoTest extends TestCase
{
    use CompletaFlujoCuestionario;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        if (! User::query()->where('role_as', '>=', 2)->where('estado', 1)->exists()) {
            User::factory()->create(['role_as' => 3, 'estado' => 1]);
        }
    }

    public function test_recorrido_completo_preempleo_demo(): void
    {
        $this->seed(DemoPruebaManualE1Seeder::class);

        $this->completarRecorridoDemo(
            DemoPruebaManualE1Seeder::TOKEN,
            DemoPruebaManualE1Seeder::DPI,
            5,
            fn (int $numero) => match ($numero) {
                1 => $this->datosSeccion1Preempleo(['dpi' => DemoPruebaManualE1Seeder::DPI]),
                2 => $this->datosSeccion2Preempleo(),
                3 => $this->datosSeccion3Preempleo(),
                4 => $this->datosSeccion4Preempleo(),
                5 => $this->datosSeccion5Preempleo(),
                default => [],
            }
        );
    }

    public function test_recorrido_completo_socioeconomico_demo(): void
    {
        $this->seed(DemoPruebaManualE4Seeder::class);

        $this->completarRecorridoDemo(
            DemoPruebaManualE4Seeder::TOKEN,
            DemoPruebaManualE4Seeder::DPI,
            6,
            fn (int $numero) => match ($numero) {
                1 => $this->datosSeccion1Preempleo(['dpi' => DemoPruebaManualE4Seeder::DPI]),
                2 => $this->datosSeccion2Preempleo(),
                3 => $this->datosSeccion3Preempleo(),
                4 => $this->datosSeccion4Socioeconomico(),
                5 => $this->datosSeccion5Preempleo(),
                6 => $this->datosSeccion6Socioeconomico(),
                default => [],
            }
        );
    }

    public function test_recorrido_completo_periodica_demo(): void
    {
        $this->seed(DemoPruebaManualE5PeriodicaSeeder::class);
        $this->asegurarMotivoHechoDemo(DemoPruebaManualE5PeriodicaSeeder::TOKEN);

        $this->completarRecorridoDemo(
            DemoPruebaManualE5PeriodicaSeeder::TOKEN,
            DemoPruebaManualE5PeriodicaSeeder::DPI,
            5,
            fn (int $numero) => match ($numero) {
                1 => $this->datosSeccion1Preempleo(['dpi' => DemoPruebaManualE5PeriodicaSeeder::DPI]),
                2 => $this->datosSeccion2Periodica(),
                3 => $this->datosSeccion3Periodica(),
                4 => $this->datosSeccion4Preempleo(),
                5 => $this->datosSeccion5PeriodicaEspecifica(),
                default => [],
            }
        );
    }

    public function test_recorrido_completo_especifica_demo(): void
    {
        $this->seed(DemoPruebaManualE5EspecificaSeeder::class);
        $this->asegurarMotivoHechoDemo(DemoPruebaManualE5EspecificaSeeder::TOKEN);

        $this->completarRecorridoDemo(
            DemoPruebaManualE5EspecificaSeeder::TOKEN,
            DemoPruebaManualE5EspecificaSeeder::DPI,
            5,
            fn (int $numero) => match ($numero) {
                1 => $this->datosSeccion1Preempleo(['dpi' => DemoPruebaManualE5EspecificaSeeder::DPI]),
                2 => $this->datosSeccion2Periodica(),
                3 => $this->datosSeccion3Especifica(),
                4 => $this->datosSeccion4Preempleo(),
                5 => $this->datosSeccion5PeriodicaEspecifica(),
                default => [],
            }
        );
    }

    /**
     * @param  callable(int): array<string, mixed>  $payloadPorSeccion
     */
    private function completarRecorridoDemo(string $token, string $dpi, int $totalSecciones, callable $payloadPorSeccion): void
    {
        $this->verificarIdentidadYFlujoPreSeccion($token, $dpi);

        $cuestionario = Cuestionario::whereHas('evaluadoOrden', fn ($q) => $q->where('token_unico', $token))->firstOrFail();
        $this->assertTrue($cuestionario->acepta_terminos, 'Debe aceptar términos antes de las secciones');

        for ($numero = 1; $numero <= $totalSecciones; $numero++) {
            $response = $this->post(route('cuestionario.guardar-seccion', [
                'token' => $token,
                'numero' => $numero,
            ]), array_merge($payloadPorSeccion($numero), ['action' => 'siguiente']));

            $response->assertSessionDoesntHaveErrors();
            $response->assertRedirect();

            if ($numero < $totalSecciones) {
                $response->assertRedirect(route('cuestionario.seccion', [
                    'token' => $token,
                    'numero' => $numero + 1,
                ]));
            } else {
                $response->assertRedirect(route('cuestionario.finalizar', ['token' => $token]));
            }
        }

        $this->get(route('cuestionario.finalizar', ['token' => $token]))
            ->assertOk();

        $this->post(route('cuestionario.completar', ['token' => $token]), [
            'confirmacion_final' => '1',
        ])
            ->assertRedirect(route('cuestionario.completado', ['token' => $token]));

        $evaluado = EvaluadoOrden::where('token_unico', $token)->firstOrFail();
        $evaluado->refresh();

        $this->assertTrue($evaluado->cuestionario_completado);
        $this->assertNotNull($evaluado->cuestionario_completado_at);

        $cuestionario = Cuestionario::where('evaluado_orden_id', $evaluado->id)->firstOrFail();
        $this->assertTrue($cuestionario->completado);
        $this->assertTrue($cuestionario->bloqueado);
        $this->assertEquals(100, (float) $cuestionario->progreso_porcentaje);
    }

    private function asegurarMotivoHechoDemo(string $token): void
    {
        EvaluadoOrden::where('token_unico', $token)->update([
            'motivo_hecho_evaluacion' => 'Motivo demo registrado por REPRO para prueba de recorrido completo.',
        ]);
    }

    /** @return array<string, mixed> */
    private function datosSeccion2Periodica(): array
    {
        $payload = $this->datosSeccion2Preempleo();
        unset($payload['tiene_hermanos'], $payload['hermanos']);

        return $payload;
    }

    /** @return array<string, mixed> */
    private function datosSeccion3Periodica(): array
    {
        return array_merge(
            [
                'ultimo_nivel_academico' => 'universitario',
                'tiene_empleo_actual' => 'si',
                'formacion_academica' => $this->datosFormacionAcademicaPreempleo()['formacion_academica'],
                'estudia_actualmente' => 'no',
                'empleo_actual' => [
                    [
                        'empresa' => 'Empresa Demo S.A.',
                        'puesto' => 'Supervisor',
                        'fechas_laboradas' => '2020-01 al 2026-07',
                        'salario_actual' => '8500',
                        'motivo_prueba' => 'Evaluación periódica programada',
                    ],
                ],
            ],
            $this->respuestasPeriodicas()
        );
    }

    /** @return array<string, mixed> */
    private function datosSeccion3Especifica(): array
    {
        return array_merge(
            [
                'ultimo_nivel_academico' => 'universitario',
                'tiene_empleo_actual' => 'si',
                'empleo_actual' => [
                    [
                        'empresa' => 'Empresa Demo S.A.',
                        'puesto' => 'Analista',
                        'fechas_laboradas' => '2019-03 al 2026-07',
                        'salario_actual' => '7200',
                        'motivo_prueba' => 'Investigación por hecho específico',
                    ],
                ],
            ],
            $this->respuestasPeriodicas(
                'No aplica al caso.',
                'Hecho bajo investigación ocurrido en marzo 2026 con participación de dos compañeros y un supervisor; se reportó a RRHH el 15/03/2026.'
            )
        );
    }

    /** @return array<string, mixed> */
    private function datosSeccion4Socioeconomico(): array
    {
        return array_merge($this->datosSeccion4Preempleo(), [
            'econ_patrimonio_aprox' => 'Q 150,000 en bienes y ahorros',
        ]);
    }

    /** @return array<string, mixed> */
    private function datosSeccion6Socioeconomico(): array
    {
        return [
            'referencias_familiares' => [
                ['nombre' => 'Ana Pérez', 'parentesco' => 'Madre', 'telefono' => '50211111111', 'direccion' => 'Zona 1', 'lugar_trabajo' => 'N/A'],
                ['nombre' => 'Luis Pérez', 'parentesco' => 'Padre', 'telefono' => '50222222222', 'direccion' => 'Zona 2', 'lugar_trabajo' => 'N/A'],
            ],
            'referencias_personales' => [
                ['nombre' => 'María López', 'relacion' => 'Amiga', 'telefono' => '50233333333', 'anos_conocerlo' => '5'],
                ['nombre' => 'Carlos Ruiz', 'relacion' => 'Compañero', 'telefono' => '50244444444', 'anos_conocerlo' => '3'],
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
        ];
    }

    /** @return array<string, string> */
    private function respuestasPeriodicas(string $respuesta = 'N/A', ?string $respuestaPrimera = null): array
    {
        $datos = [];
        foreach (HistorialLaboralPeriodico::preguntasVisibles() as $i => $pregunta) {
            $datos[$pregunta['key']] = ($i === 0 && $respuestaPrimera !== null)
                ? $respuestaPrimera
                : $respuesta;
        }

        $datos[HistorialLaboralPeriodico::CAMPO_INFORMACION_ADICIONAL['key']] = 'N/A';

        return $datos;
    }
}
