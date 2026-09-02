<?php

namespace Tests\Feature;

use App\Models\CuestionarioRespuesta;
use App\Models\Empresa;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Support\CuestionarioPrecarga;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CompletaFlujoCuestionario;
use Tests\Support\FakeImage;
use Tests\TestCase;

/**
 * E1.9 — Cierre del motor base: migraciones aditivas + flujo integrado piloto (secciones 1–2).
 */
class CuestionarioMotorE1Test extends TestCase
{
    use RefreshDatabase, CompletaFlujoCuestionario;

    private EvaluadoOrden $evaluado;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $empresa = Empresa::factory()->create(['nombre' => 'Empresa Motor E1']);
        $orden = Orden::factory()->create(['empresa_id' => $empresa->id]);

        $this->evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'token_unico' => 'token-motor-e19',
            'token_expira_at' => now()->addDays(30),
            'cuestionario_completado' => false,
            'dpi' => '1234567890101',
            'nombre' => 'Motor',
            'apellidos' => 'Prueba Motor',
            'email' => 'motor@e1.test',
            'tipo_formulario' => 'preempleo',
            'tipo_servicio' => 'poligrafo',
        ]);
    }

    public function test_migraciones_e1_tienen_estructura_esperada(): void
    {
        $this->assertTrue(Schema::hasColumn('cuestionario_respuestas', 'valor_json'));
        $this->assertTrue(Schema::hasTable('evaluador_notas'));
        $this->assertTrue(Schema::hasTable('departamentos'));
        $this->assertTrue(Schema::hasTable('municipios'));
        $this->assertTrue(Schema::hasColumn('cuestionarios', 'instrucciones_leidas_at'));
        $this->assertTrue(Schema::hasColumn('cuestionarios', 'ip_instrucciones'));
        $this->assertTrue(Schema::hasColumn('cuestionarios', 'datos_precarga_json'));
    }

    public function test_assets_publicos_del_motor_existen(): void
    {
        $this->assertFileExists(public_path('js/tabla-dinamica.js'));
        $this->assertFileExists(public_path('js/campos-condicionales.js'));
        $this->assertFileExists(public_path('js/cuestionario-autosave.js'));
    }

    public function test_flujo_integrado_precarga_autosave_condicionales_y_tabla_dinamica(): void
    {
        $this->post(route('cuestionario.verificar', $this->evaluado->token_unico), [
            'dpi_ingresado' => '1234567890101',
        ]);

        $cuestionario = $this->evaluado->cuestionario()->first();
        $this->assertNotNull($cuestionario->datos_precarga_json);
        $this->assertSame('Motor', $cuestionario->datos_precarga_json['nombres_completos'] ?? null);

        $this->aceptarInstrucciones($this->evaluado->token_unico);
        $this->aceptarTerminosCuestionario($this->evaluado->token_unico);

        $this->assertNotNull($cuestionario->fresh()->instrucciones_leidas_at);

        $responseS1 = $this->post(route('cuestionario.guardar-seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 1,
        ]), $this->datosSeccion1Preempleo([
            'nombres_completos' => 'Motor',
            'apellidos_completos' => 'Prueba Motor',
            'email_personal' => 'motor@e1.test',
        ]));

        $responseS1->assertSessionHasNoErrors();
        $this->assertEquals(2, $cuestionario->fresh()->seccion_actual);

        $this->postJson(route('cuestionario.autosave-seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 2,
        ]), [
            'estado_civil_detalle' => 'casado',
            'tiene_hijos' => 'si',
        ])->assertOk();

        $response = $this->post(route('cuestionario.guardar-seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 2,
        ]), array_merge($this->datosSeccion2ConHijos(), ['action' => 'siguiente']));

        $response->assertRedirect(route('cuestionario.seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 3,
        ]));
        $response->assertSessionHasNoErrors();

        $cuestionario->refresh();
        $this->assertEquals(3, $cuestionario->seccion_actual);

        $respuestaHijos = CuestionarioRespuesta::where('cuestionario_id', $cuestionario->id)
            ->where('campo', 'hijos')
            ->first();

        $this->assertNotNull($respuestaHijos);
        $this->assertCount(1, $respuestaHijos->getTabla());
        $this->assertSame('Lucía', $respuestaHijos->getTabla()[0]['nombre']);

        $cambios = CuestionarioPrecarga::cambiosRegistrados($cuestionario);
        $this->assertIsArray($cambios);
    }

    /** @return array<string, mixed> */
    private function datosSeccion2ConHijos(): array
    {
        return array_merge($this->datosSeccion2Preempleo(), $this->datosParejaPreempleo([
            'estado_civil_detalle' => 'casado',
        ]), $this->datosHijosPreempleo([
            'numero_hijos' => 1,
            'hijos_menores' => 1,
            'hijos_dependientes' => 1,
            'hijos' => [
                [
                    'nombre' => 'Lucía',
                    'edad' => '9',
                    'vive_con_candidato' => 'si',
                    'ocupacion' => 'Estudiante',
                    'telefono' => '55552222',
                ],
            ],
        ]));
    }
}
