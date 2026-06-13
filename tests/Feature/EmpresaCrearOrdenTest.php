<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\Orden;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Sede;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesRolesAndPermissions;
use Tests\TestCase;

class EmpresaCrearOrdenTest extends TestCase
{
    use RefreshDatabase, CreatesRolesAndPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpRolesAndPermissions();
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
        $this->assertEquals('orden_recibida', $orden->estado);
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

    // ──────────────────────────────────────────────────────────
    // C2-sede: empresa puede crear orden con sede por evaluado
    // ──────────────────────────────────────────────────────────

    public function test_empresa_puede_crear_orden_con_sede_por_evaluado(): void
    {
        [$user, $empresa] = $this->crearEmpresaUser();
        $sede = Sede::factory()->create(['estado' => 1]);

        $payload = $this->payloadEvaluado();
        $payload['sede_id'] = $sede->id;
        $payload['puesto_evaluar'] = 'Gerente de Ventas';

        $response = $this->actingAs($user)->post(route('ordenes.store'), [
            'empresa_id' => $empresa->id,
            'evaluados'  => [$payload],
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $orden = Orden::where('empresa_id', $empresa->id)->first();
        $evaluado = $orden->evaluados->first();

        $this->assertEquals($sede->id, $evaluado->sede_id);
        $this->assertEquals('Gerente de Ventas', $evaluado->puesto_evaluar);
    }

    // ──────────────────────────────────────────────────────────
    // Botón editar visible en detalle de orden (estado solicitud)
    // ──────────────────────────────────────────────────────────

    public function test_empresa_con_permiso_editar_ve_boton_editar_en_show(): void
    {
        [$user, $empresa] = $this->crearEmpresaUser();
        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $user->id,
            'estado'     => 'orden_recibida',
        ]);

        $response = $this->actingAs($user)->get(route('empresa.ordenes.show', $orden));

        $response->assertOk();
        $response->assertSee(route('ordenes.edit', $orden));
    }

    // ──────────────────────────────────────────────────────────
    // Botón editar visible en listado de órdenes
    // ──────────────────────────────────────────────────────────

    public function test_empresa_con_permiso_editar_ve_boton_editar_en_index(): void
    {
        [$user, $empresa] = $this->crearEmpresaUser();
        Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $user->id,
            'estado'     => 'orden_recibida',
        ]);

        $response = $this->actingAs($user)->get(route('empresa.ordenes.index'));

        $response->assertOk();
        $response->assertSee('bi-pencil', false);
    }

    // ──────────────────────────────────────────────────────────
    // Botón editar NO visible para orden entregada
    // ──────────────────────────────────────────────────────────

    public function test_empresa_no_ve_boton_editar_en_orden_entregada(): void
    {
        [$user, $empresa] = $this->crearEmpresaUser();
        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $user->id,
            'estado'     => 'entregado',
        ]);

        $response = $this->actingAs($user)->get(route('empresa.ordenes.show', $orden));

        $response->assertOk();
        $response->assertDontSee(route('ordenes.edit', $orden));
    }

    // ──────────────────────────────────────────────────────────
    // Usuario empresa sin permiso editar no ve el botón
    // ──────────────────────────────────────────────────────────

    public function test_empresa_sin_permiso_editar_no_ve_boton_editar(): void
    {
        $empresa = Empresa::factory()->create();
        $user = User::factory()->create([
            'role_as'    => 1,
            'estado'     => 1,
            'empresa_id' => $empresa->id,
        ]);

        // Rol empresa sin ordenes.editar
        $rolSinEditar = Role::create([
            'name'         => 'empresa_sin_editar',
            'display_name' => 'Empresa Sin Editar',
            'level'        => 1,
        ]);
        $permVer = Permission::where('name', 'ordenes.ver')->first();
        if ($permVer) {
            $rolSinEditar->givePermission($permVer);
        }
        $user->roles()->attach($rolSinEditar);

        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $user->id,
            'estado'     => 'orden_recibida',
        ]);

        $response = $this->actingAs($user)->get(route('empresa.ordenes.show', $orden));

        $response->assertOk();
        $response->assertDontSee(route('ordenes.edit', $orden));
    }

    // ──────────────────────────────────────────────────────────
    // Fase 19: empresa ya no elimina órdenes (solo admin archiva)
    // ──────────────────────────────────────────────────────────

    public function test_empresa_no_ve_boton_eliminar_en_show(): void
    {
        [$user, $empresa] = $this->crearEmpresaUser();
        $permEliminar = Permission::firstOrCreate(
            ['name' => 'ordenes.eliminar'],
            ['display_name' => 'Eliminar Órdenes', 'module' => 'ordenes']
        );
        $user->roles()->first()->givePermission($permEliminar);

        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $user->id,
            'estado'     => 'orden_recibida',
        ]);

        $response = $this->actingAs($user)->get(route('empresa.ordenes.show', $orden));

        $response->assertOk();
        $response->assertDontSee('bi-trash', false);
        $response->assertDontSee('Eliminar', false);
    }

    // ──────────────────────────────────────────────────────────
    // Botón eliminar visible en index con permiso y estado deletable
    // ──────────────────────────────────────────────────────────

    public function test_empresa_con_permiso_eliminar_ve_boton_eliminar_en_index(): void
    {
        [$user, $empresa] = $this->crearEmpresaUser();
        $permEliminar = Permission::firstOrCreate(
            ['name' => 'ordenes.eliminar'],
            ['display_name' => 'Eliminar Órdenes', 'module' => 'ordenes']
        );
        $user->roles()->first()->givePermission($permEliminar);

        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $user->id,
            'estado'     => 'orden_recibida',
        ]);

        $response = $this->actingAs($user)->get(route('empresa.ordenes.index'));

        $response->assertOk();
        $response->assertSee('bi-trash', false);
    }

    // ──────────────────────────────────────────────────────────
    // Botón eliminar NO visible en orden en proceso
    // ──────────────────────────────────────────────────────────

    public function test_empresa_no_ve_boton_eliminar_en_orden_en_proceso(): void
    {
        [$user, $empresa] = $this->crearEmpresaUser();
        $permEliminar = Permission::firstOrCreate(
            ['name' => 'ordenes.eliminar'],
            ['display_name' => 'Eliminar Órdenes', 'module' => 'ordenes']
        );
        $user->roles()->first()->givePermission($permEliminar);

        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $user->id,
            'estado'     => 'en_proceso',
        ]);

        $response = $this->actingAs($user)->get(route('empresa.ordenes.show', $orden));

        $response->assertOk();
        $response->assertDontSee('bi-trash', false);
    }

    // ──────────────────────────────────────────────────────────
    // Empresa no puede eliminar orden de otra empresa
    // ──────────────────────────────────────────────────────────

    public function test_empresa_no_puede_eliminar_orden_de_otra_empresa(): void
    {
        [$user, $empresa] = $this->crearEmpresaUser();
        $permEliminar = Permission::firstOrCreate(
            ['name' => 'ordenes.eliminar'],
            ['display_name' => 'Eliminar Órdenes', 'module' => 'ordenes']
        );
        $user->roles()->first()->givePermission($permEliminar);

        $otraEmpresa = Empresa::factory()->create();
        $orden = Orden::factory()->create([
            'empresa_id' => $otraEmpresa->id,
            'estado'     => 'orden_recibida',
        ]);

        $response = $this->actingAs($user)->delete(route('ordenes.destroy', $orden));

        $response->assertForbidden();
    }
}
