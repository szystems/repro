<?php

namespace Tests\Feature;

use App\Models\Config;
use App\Models\Empresa;
use App\Models\EstadoHistorial;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Fase19Sprint3Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Administrador']);
        Role::firstOrCreate(['name' => 'empresa'], ['display_name' => 'Empresa']);

        $adminRole = Role::where('name', 'admin')->first();
        $permVer = Permission::firstOrCreate(
            ['name' => 'ordenes.ver'],
            ['display_name' => 'Ver Órdenes', 'module' => 'ordenes']
        );
        $adminRole->givePermission($permVer);
    }

    private function crearAdmin(): User
    {
        $admin = User::factory()->create(['role_as' => 3]);
        $admin->roles()->attach(Role::where('name', 'admin')->first());

        return $admin;
    }

    private function crearEmpresaUser(): array
    {
        $empresa = Empresa::factory()->create();
        $user = User::factory()->create([
            'role_as' => 1,
            'empresa_id' => $empresa->id,
            'principal' => 1,
        ]);
        $user->roles()->attach(Role::where('name', 'empresa')->first());

        return [$user, $empresa];
    }

    public function test_config_historial_visible_empresa_default_true(): void
    {
        $config = Config::create(['currency' => 'GTQ Q']);
        $config->refresh();

        $this->assertTrue($config->historial_visible_empresa);
        $this->assertTrue(Config::historialVisibleParaEmpresa());
    }

    public function test_admin_puede_desactivar_historial_visible_empresa(): void
    {
        Config::create(['currency' => 'GTQ Q', 'historial_visible_empresa' => true]);
        $admin = $this->crearAdmin();

        $this->actingAs($admin)->put(route('config.update'), [
            'currency' => 'GTQ Q',
            'historial_visible_empresa' => '0',
        ])->assertRedirect();

        $this->assertFalse(Config::first()->historial_visible_empresa);
    }

    public function test_empresa_ve_historial_cuando_config_activo(): void
    {
        Config::create(['currency' => 'GTQ Q', 'historial_visible_empresa' => true]);
        [$user, $empresa] = $this->crearEmpresaUser();
        $orden = Orden::factory()->create(['empresa_id' => $empresa->id, 'creado_por' => $user->id]);
        $evaluado = EvaluadoOrden::factory()->create(['orden_id' => $orden->id]);

        EstadoHistorial::create([
            'evaluado_orden_id' => $evaluado->id,
            'orden_id' => $orden->id,
            'campo' => 'estado_evaluacion',
            'estado_anterior' => 'pendiente_de_evaluacion',
            'estado_nuevo' => 'en_proceso',
            'observacion' => 'Inicio de evaluación',
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('empresa.ordenes.show', $orden));

        $response->assertOk();
        $response->assertSee('Historial de cambios');
        $response->assertSee('Inicio de evaluación');
        $response->assertSee('Estado de Evaluación');
    }

    public function test_empresa_no_ve_historial_cuando_config_inactivo(): void
    {
        Config::create(['currency' => 'GTQ Q', 'historial_visible_empresa' => false]);
        [$user, $empresa] = $this->crearEmpresaUser();
        $orden = Orden::factory()->create(['empresa_id' => $empresa->id, 'creado_por' => $user->id]);
        $evaluado = EvaluadoOrden::factory()->create(['orden_id' => $orden->id]);

        EstadoHistorial::create([
            'evaluado_orden_id' => $evaluado->id,
            'orden_id' => $orden->id,
            'campo' => 'estado_evaluacion',
            'estado_anterior' => 'pendiente_de_evaluacion',
            'estado_nuevo' => 'en_proceso',
            'observacion' => 'No debe verse',
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('empresa.ordenes.show', $orden));

        $response->assertOk();
        $response->assertDontSee('Historial de cambios');
        $response->assertDontSee('No debe verse');
    }

    public function test_historial_dpi_busca_por_nombre(): void
    {
        $admin = $this->crearAdmin();
        $empresa = Empresa::factory()->create();
        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $admin->id,
        ]);
        EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'nombre' => 'PRUEBA',
            'apellidos' => 'DUPLICACION',
            'dpi' => '1234567890123',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.cuestionarios.historial-dpi', ['buscar' => 'PRUEBA']));

        $response->assertOk();
        $response->assertSee('PRUEBA');
        $response->assertSee('DUPLICACION');
    }

    public function test_historial_dpi_sigue_buscando_por_dpi(): void
    {
        $admin = $this->crearAdmin();
        $empresa = Empresa::factory()->create();
        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $admin->id,
        ]);
        EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'nombre' => 'Carlos',
            'apellidos' => 'López',
            'dpi' => '9876543210123',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.cuestionarios.historial-dpi', ['buscar' => '9876543210123']));

        $response->assertOk();
        $response->assertSee('Carlos');
        $response->assertSee('López');
    }

    public function test_admin_puede_archivar_orden_en_proceso(): void
    {
        $admin = $this->crearAdmin();
        $orden = Orden::factory()->create(['estado' => 'en_proceso']);
        $evaluado = EvaluadoOrden::factory()->create(['orden_id' => $orden->id]);

        $response = $this->actingAs($admin)
            ->patch(route('ordenes.archivar', $orden));

        $response->assertRedirect(route('ordenes.index'));
        $orden->refresh();
        $this->assertTrue($orden->archivada);
        $this->assertEquals($admin->id, $orden->archivada_por);
        $this->assertDatabaseHas('evaluados_orden', ['id' => $evaluado->id]);
    }

    public function test_orden_archivada_no_aparece_en_listado_normal(): void
    {
        $admin = $this->crearAdmin();
        $orden = Orden::factory()->create(['estado' => 'orden_recibida', 'archivada' => true]);

        $response = $this->actingAs($admin)->get(route('ordenes.index'));

        $response->assertOk();
        $response->assertDontSee($orden->codigo_orden);
    }

    public function test_orden_archivada_visible_con_filtro_archivadas(): void
    {
        $admin = $this->crearAdmin();
        $orden = Orden::factory()->create(['estado' => 'orden_recibida', 'archivada' => true]);

        $response = $this->actingAs($admin)->get(route('ordenes.index', ['archivadas' => 1]));

        $response->assertOk();
        $response->assertSee($orden->codigo_orden);
    }

    public function test_empresa_no_ve_orden_archivada(): void
    {
        [$user, $empresa] = $this->crearEmpresaUser();
        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'archivada' => true,
        ]);

        $this->actingAs($user)
            ->get(route('empresa.ordenes.show', $orden))
            ->assertNotFound();
    }

    public function test_destroy_deshabilitado_para_todos(): void
    {
        $admin = $this->crearAdmin();
        $orden = Orden::factory()->create();

        $this->actingAs($admin)
            ->delete(route('ordenes.destroy', $orden))
            ->assertForbidden();

        $this->assertDatabaseHas('ordenes', ['id' => $orden->id, 'archivada' => false]);
    }

    public function test_empresa_dashboard_busca_candidato_por_nombre(): void
    {
        [$user, $empresa] = $this->crearEmpresaUser();
        $orden = Orden::factory()->create(['empresa_id' => $empresa->id, 'creado_por' => $user->id]);
        EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'nombre' => 'Carlos',
            'apellidos' => 'Pérez',
            'dpi' => '1234567890123',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard', ['buscar' => 'Carlos']));

        $response->assertOk();
        $response->assertSee('Carlos');
        $response->assertSee('Pérez');
        $response->assertSee($orden->codigo_orden);
    }

    public function test_empresa_dashboard_busca_candidato_por_dpi(): void
    {
        [$user, $empresa] = $this->crearEmpresaUser();
        $orden = Orden::factory()->create(['empresa_id' => $empresa->id, 'creado_por' => $user->id]);
        EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'nombre' => 'Ana',
            'apellidos' => 'López',
            'dpi' => '9876543210123',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard', ['buscar' => '9876543210123']));

        $response->assertOk();
        $response->assertSee('Ana');
        $response->assertSee('9876543210123');
    }

    public function test_empresa_dashboard_no_muestra_candidatos_de_otra_empresa(): void
    {
        [$user, $empresa] = $this->crearEmpresaUser();
        $otraEmpresa = Empresa::factory()->create();
        $ordenOtra = Orden::factory()->create(['empresa_id' => $otraEmpresa->id]);
        EvaluadoOrden::factory()->create([
            'orden_id' => $ordenOtra->id,
            'nombre' => 'Externo',
            'apellidos' => 'Ajeno',
            'dpi' => '1111111111111',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard', ['buscar' => 'Externo']));

        $response->assertOk();
        $response->assertDontSee('Externo Ajeno');
        $response->assertSee('No se encontraron candidatos');
    }
}
