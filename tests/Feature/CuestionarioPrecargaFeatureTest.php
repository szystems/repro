<?php

namespace Tests\Feature;

use App\Models\Cuestionario;
use App\Models\CuestionarioRespuesta;
use App\Models\Empresa;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Models\User;
use App\Support\CuestionarioPrecarga;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CompletaFlujoCuestionario;
use Tests\Support\FakeImage;
use Tests\TestCase;

class CuestionarioPrecargaFeatureTest extends TestCase
{
    use RefreshDatabase, CompletaFlujoCuestionario;

    private EvaluadoOrden $evaluado;

    protected function setUp(): void
    {
        parent::setUp();

        $empresa = Empresa::factory()->create(['nombre' => 'REPRO Cliente Test']);
        $orden = Orden::factory()->create(['empresa_id' => $empresa->id]);

        $this->evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'token_unico' => 'token-precarga-e17',
            'token_expira_at' => now()->addDays(30),
            'cuestionario_completado' => false,
            'dpi' => '1234567890101',
            'nombre' => 'Ana',
            'apellidos' => 'Martínez',
            'email' => 'ana@orden.com',
            'telefono' => '44445555',
            'direccion' => 'Dirección desde orden',
            'puesto_evaluar' => 'Supervisor',
            'sede_region_empresa' => 'Guatemala',
        ]);
    }

    public function test_verificar_dpi_captura_snapshot_precarga(): void
    {
        $this->post(route('cuestionario.verificar', $this->evaluado->token_unico), [
            'dpi_ingresado' => '1234567890101',
        ]);

        $cuestionario = $this->evaluado->cuestionario()->first();
        $this->assertNotNull($cuestionario->datos_precarga_json);
        $this->assertSame('Ana', $cuestionario->datos_precarga_json['nombres_completos']);
        $this->assertSame('REPRO Cliente Test', $cuestionario->datos_precarga_json['empresa_solicitante']);
    }

    public function test_seccion_1_muestra_datos_precargados(): void
    {
        $this->verificarIdentidadYFlujoPreSeccion($this->evaluado->token_unico, '1234567890101');

        $response = $this->get(route('cuestionario.seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 1,
        ]));

        $response->assertOk();
        $response->assertSee('REPRO Cliente Test');
        $response->assertSee('Supervisor');
        $response->assertSee('value="Ana"', false);
        $response->assertSee('Dirección desde orden');
    }

    public function test_guardar_cambio_registra_trazabilidad_y_admin_la_ve(): void
    {
        $this->verificarIdentidadYFlujoPreSeccion($this->evaluado->token_unico, '1234567890101');

        $datos = array_merge($this->datosSeccion1Preempleo(), [
            'nombres_completos' => 'Ana María',
            'apellidos_completos' => 'Martínez',
            'direccion_residencia' => 'Nueva dirección del candidato',
            'telefono_personal' => '44445555',
            'email_personal' => 'nuevo@correo.com',
        ]);

        $this->post(route('cuestionario.guardar-seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 1,
        ]), $datos);

        $cuestionario = $this->evaluado->cuestionario()->with('respuestas')->first();
        $cambios = CuestionarioPrecarga::cambiosRegistrados($cuestionario);

        $camposModificados = array_column($cambios, 'campo');
        $this->assertContains('nombres_completos', $camposModificados);
        $this->assertContains('direccion_residencia', $camposModificados);
        $this->assertContains('email_personal', $camposModificados);

        $admin = User::factory()->create(['role_as' => 3]);

        $response = $this->actingAs($admin)->get(route('admin.cuestionarios.show', $cuestionario->id));
        $response->assertOk();
        $response->assertSee('Cambios respecto a la orden');
        $response->assertSee('ana@orden.com');
        $response->assertSee('nuevo@correo.com');
    }
}
