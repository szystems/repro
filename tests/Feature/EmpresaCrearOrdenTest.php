<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\Orden;
use App\Models\Role;
use App\Models\Sede;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmpresaCrearOrdenTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'admin',   'display_name' => 'Administrador']);
        Role::create(['name' => 'empresa', 'display_name' => 'Empresa']);
        Role::create(['name' => 'repro',   'display_name' => 'Repro']);
    }

    private function crearEmpresaUser(): array
    {
        $empresa = Empresa::factory()->create();
        $user = User::factory()->create([
            'role_as'    => 1,
            'estado'     => 1,
            'empresa_id' => $empresa->id,
        ]);
        $user->roles()->attach(Role::where('name', 'empresa')->first());
        return [$user, $empresa];
    }

    private function payloadEvaluado(): array
    {
        return [
            'nombre'          => 'María',
            'apellidos'       => 'González',
            'dpi'             => '1234567890123',
            'email'           => 'maria@empresa.com',
            'telefono'        => '55551234',
            'tipo_servicio'   => 'poligrafo',
            'tipo_formulario' => 'preempleo',
        ];
    }

    // ──────────────────────────────────────────────────────────
    // Empresa puede ver el formulario de nueva orden
    // ──────────────────────────────────────────────────────────

    public function test_empresa_puede_ver_formulario_crear_orden(): void
    {
        [$user] = $this->crearEmpresaUser();

        $response = $this->actingAs($user)->get(route('ordenes.create'));

        $response->assertOk();
        $response->assertSee('Nueva Orden');
    }

    // ──────────────────────────────────────────────────────────
    // Empresa puede crear una orden con evaluados
    // ──────────────────────────────────────────────────────────

    public function test_empresa_puede_crear_orden_con_evaluados(): void
    {
        [$user, $empresa] = $this->crearEmpresaUser();

        $response = $this->actingAs($user)->post(route('ordenes.store'), [
            'empresa_id' => $empresa->id,
            'evaluados'  => [$this->payloadEvaluado()],
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $orden = Orden::where('empresa_id', $empresa->id)->first();
        $this->assertNotNull($orden);
        $this->assertEquals('solicitud', $orden->estado);
        $this->assertEquals('empresa', $orden->tipo_creador);
        $this->assertEquals($user->id, $orden->creado_por);
        $this->assertCount(1, $orden->evaluados);
    }

    // ──────────────────────────────────────────────────────────
    // La orden creada por empresa no hereda campos de REPRO
    // ──────────────────────────────────────────────────────────

    public function test_empresa_no_puede_asignar_prioridad_ni_observaciones(): void
    {
        [$user, $empresa] = $this->crearEmpresaUser();

        $this->actingAs($user)->post(route('ordenes.store'), [
            'empresa_id'             => $empresa->id,
            'prioridad'              => 'urgente',
            'observaciones_internas' => 'secreto',
            'evaluados'              => [$this->payloadEvaluado()],
        ]);

        $orden = Orden::where('empresa_id', $empresa->id)->first();
        $this->assertNotNull($orden);
        // Los campos exclusivos de REPRO no deben grabarse
        $this->assertNotEquals('urgente', $orden->prioridad);
        $this->assertNull($orden->observaciones_internas);
    }

    // ──────────────────────────────────────────────────────────
    // Empresa ve el botón "Nueva Solicitud" en su listado
    // ──────────────────────────────────────────────────────────

    public function test_empresa_ve_boton_nueva_solicitud_en_listado(): void
    {
        [$user] = $this->crearEmpresaUser();

        $response = $this->actingAs($user)->get(route('empresa.ordenes.index'));

        $response->assertOk();
        $response->assertSee('Nueva Solicitud');
        $response->assertSee(route('ordenes.create'));
    }

    // ──────────────────────────────────────────────────────────
    // Usuario sin empresa_id recibe error amigable, no 500
    // ──────────────────────────────────────────────────────────

    public function test_empresa_sin_empresa_id_recibe_error_amigable(): void
    {
        $user = User::factory()->create(['role_as' => 1, 'estado' => 1, 'empresa_id' => null]);
        $user->roles()->attach(Role::where('name', 'empresa')->first());

        $response = $this->actingAs($user)->post(route('ordenes.store'), [
            'empresa_id' => 999,
            'evaluados'  => [$this->payloadEvaluado()],
        ]);

        // Debe redirigir de vuelta con errores de validación (no 500)
        $response->assertRedirect();
        $response->assertSessionHasErrors();
    }
}
