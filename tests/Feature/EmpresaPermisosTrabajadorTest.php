<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\Orden;
use App\Models\Role;
use App\Models\User;
use App\Notifications\OrdenCreadaNotification;
use App\Support\EmpresaPermisosSupport;
use App\Support\EmpresaVisibilidadReclutadoresSupport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesRolesAndPermissions;
use Tests\TestCase;

/** Permisos portal empresa — spec cliente ago-2026 (PERMISOS_EMPRESA_CLIENTE.md). */
class EmpresaPermisosTrabajadorTest extends TestCase
{
    use RefreshDatabase, CreatesRolesAndPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpRolesAndPermissions();
    }

    public function test_trabajador_ignora_rol_spatie_y_usa_solo_json(): void
    {
        $empresa = Empresa::factory()->create();
        $trabajador = User::factory()->create([
            'empresa_id' => $empresa->id,
            'role_as' => 1,
            'principal' => 0,
            'permisos' => json_encode(EmpresaPermisosSupport::permisosDefaultTrabajador()),
        ]);
        $trabajador->roles()->attach(Role::where('name', 'empresa')->first());

        $this->assertTrue($trabajador->hasPermission('ordenes.crear'));
        $this->assertTrue($trabajador->hasPermission('reportes.ver'));
        $this->assertTrue($trabajador->hasPermission('documentos.subir'));
        $this->assertTrue($trabajador->hasPermission('ordenes.ver'));
        $this->assertFalse($trabajador->hasPermission('sedes.ver'));
    }

    public function test_usuario_principal_tiene_todos_los_permisos_del_mapa(): void
    {
        $empresa = Empresa::factory()->create();
        $principal = User::factory()->create([
            'empresa_id' => $empresa->id,
            'role_as' => 1,
            'principal' => 1,
        ]);

        $this->assertTrue($principal->hasPermission('ordenes.crear'));
        $this->assertTrue($principal->hasPermission('documentos.subir'));
        $this->assertFalse($principal->hasPermission('sedes.ver'));
    }

    public function test_trabajador_sin_ver_ordenes_abre_su_orden_desde_el_listado_empresa(): void
    {
        $empresa = Empresa::factory()->create();
        $trabajador = User::factory()->create([
            'empresa_id' => $empresa->id,
            'role_as' => '1',
            'principal' => 0,
            'permisos' => ['ver_resultados'],
        ]);
        $trabajador->roles()->attach(Role::where('name', 'empresa')->first());

        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $trabajador->id,
            'confidencial' => false,
        ]);

        $this->assertFalse($trabajador->hasPermission('ordenes.ver'));

        $this->actingAs($trabajador)
            ->get(route('ordenes.show', $orden))
            ->assertForbidden();

        $this->actingAs($trabajador)
            ->get(route('empresa.ordenes.show', $orden))
            ->assertOk();

        $this->actingAs($trabajador)
            ->get(route('empresa.ordenes.index'))
            ->assertOk()
            ->assertSee(route('empresa.ordenes.show', $orden), false);
    }

    public function test_notificacion_apunta_al_portal_empresa_si_role_as_viene_como_texto(): void
    {
        $empresa = Empresa::factory()->create();
        $user = User::factory()->create([
            'empresa_id' => $empresa->id,
            'role_as' => '1',
            'principal' => 1,
        ]);
        $orden = Orden::factory()->create(['empresa_id' => $empresa->id]);

        $data = (new OrdenCreadaNotification($orden))->toArray($user);

        $this->assertSame(route('empresa.ordenes.show', $orden), $data['url']);
    }

    public function test_empresa_sin_compania_no_lista_ordenes_de_otras_empresas(): void
    {
        $user = User::factory()->create([
            'role_as' => 1,
            'empresa_id' => null,
            'principal' => 1,
        ]);
        $orden = Orden::factory()->create();

        $ids = EmpresaVisibilidadReclutadoresSupport::filtrarQueryOrdenesEmpresa(Orden::query(), $user)->pluck('id');

        $this->assertFalse($ids->contains($orden->id));

        $this->actingAs($user)
            ->get(route('ordenes.index'))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('error');
    }
}
