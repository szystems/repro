<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\Orden;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesRolesAndPermissions;
use Tests\TestCase;

class UsuariosRolesArchivoTest extends TestCase
{
    use RefreshDatabase, CreatesRolesAndPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpRolesAndPermissions();
    }

    private function crearAdmin(): User
    {
        $admin = User::factory()->create(['role_as' => 3, 'estado' => 1]);
        $admin->assignRole('admin');

        return $admin;
    }

    /** @test */
    public function empleado_repro_con_permisos_individuales_puede_editar_orden(): void
    {
        $permisoEditar = Permission::where('name', 'ordenes.editar')->first();
        $this->assertNotNull($permisoEditar);

        $empleado = User::factory()->create(['role_as' => 2, 'estado' => 1]);
        $rolPersonal = Role::create([
            'name' => 'user_'.$empleado->id,
            'display_name' => 'Permisos de '.$empleado->name,
            'description' => 'Permisos individuales (interno)',
            'level' => 2,
        ]);
        $rolPersonal->givePermission($permisoEditar);
        $empleado->roles()->attach($rolPersonal->id);

        $this->assertFalse($empleado->hasRole('repro'));
        $this->assertTrue($empleado->hasPermission('ordenes.editar'));

        $orden = Orden::factory()->create(['estado' => 'orden_recibida']);

        $this->actingAs($empleado)
            ->get(route('ordenes.edit', $orden))
            ->assertOk();
    }

    /** @test */
    public function roles_personales_no_aparecen_en_gestion_de_roles(): void
    {
        $admin = $this->crearAdmin();
        Role::create([
            'name' => 'user_999',
            'display_name' => 'Permisos de Fantasma',
            'level' => 2,
        ]);

        $this->actingAs($admin)
            ->get(url('admin/roles'))
            ->assertOk()
            ->assertDontSee('Permisos de Fantasma')
            ->assertSee('Personal Repro');
    }

    /** @test */
    public function admin_puede_eliminar_usuario_principal_de_empresa(): void
    {
        $admin = $this->crearAdmin();
        $empresa = Empresa::factory()->create(['estado' => 1]);
        $titular = User::factory()->create([
            'role_as' => 1,
            'empresa_id' => $empresa->id,
            'principal' => 1,
            'estado' => 1,
            'email' => 'titular@empresa.test',
        ]);
        $titular->assignRole('empresa');

        $this->actingAs($admin)
            ->delete(route('users.destroy', $titular->id))
            ->assertRedirect('users');

        $titular->refresh();
        $this->assertSame(0, (int) $titular->estado);
        $this->assertStringContainsString('-Deleted', $titular->email);
    }

    /** @test */
    public function no_se_puede_eliminar_el_ultimo_administrador(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->delete(route('users.destroy', $admin->id))
            ->assertRedirect('users');

        $admin->refresh();
        $this->assertSame(1, (int) $admin->estado);
    }

    /** @test */
    public function evaluado_de_orden_archivada_no_aparece_en_gestion_de_cuestionarios(): void
    {
        $admin = $this->crearAdmin();
        $orden = Orden::factory()->create(['archivada' => true]);
        $evaluado = \App\Models\EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'nombre' => 'EvaArchivada',
            'apellidos' => 'Lista',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.cuestionarios.index'))
            ->assertOk()
            ->assertDontSee('EvaArchivada');

        $this->assertTrue($evaluado->exists);
    }
}
