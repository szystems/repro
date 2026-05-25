<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\Orden;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Sede;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests de seguridad — Fase 6: Verifica protección de rutas,
 * middleware de roles, y métodos HTTP correctos.
 */
class SeguridadTest extends TestCase
{
    use RefreshDatabase;

    private function usuarioAdmin(): User
    {
        return User::factory()->create(['role_as' => 3, 'estado' => 1]);
    }

    private function usuarioRepro(): User
    {
        return User::factory()->create(['role_as' => 2, 'estado' => 1]);
    }

    private function usuarioEmpresa(): User
    {
        return User::factory()->create([
            'role_as' => 1,
            'estado' => 1,
            'empresa_id' => Empresa::factory()->create()->id,
        ]);
    }

    // -------------------------------------------------------
    // 6.1 — Archivos y rutas de debug eliminados
    // -------------------------------------------------------

    public function test_test_db_no_existe(): void
    {
        $this->assertFileDoesNotExist(public_path('test-db.php'));
    }

    public function test_ruta_test_cuestionario_no_existe(): void
    {
        $this->get('/test-cuestionario/abc123')
            ->assertNotFound();
    }

    // -------------------------------------------------------
    // 6.2 — Rutas destructivas requieren POST/PATCH/DELETE
    // -------------------------------------------------------

    public function test_delete_user_via_get_no_permitido(): void
    {
        $admin = $this->usuarioAdmin();
        $user = User::factory()->create(['role_as' => 1, 'estado' => 1]);

        $this->actingAs($admin)
            ->get("/delete-user/{$user->id}")
            ->assertStatus(405); // Method Not Allowed
    }

    public function test_delete_user_via_delete_funciona(): void
    {
        $admin = $this->usuarioAdmin();
        $user = User::factory()->create(['role_as' => 1, 'estado' => 1, 'principal' => 0]);

        $this->actingAs($admin)
            ->delete("/delete-user/{$user->id}")
            ->assertRedirect();

        $this->assertDatabaseHas('users', ['id' => $user->id, 'estado' => 0]);
    }

    public function test_cambiar_estado_empresa_via_get_no_permitido(): void
    {
        $admin = $this->usuarioAdmin();
        $empresa = Empresa::factory()->create(['estado' => 1]);

        $this->actingAs($admin)
            ->get("/cambiar-estado-empresa/{$empresa->id}/0")
            ->assertStatus(405);
    }

    public function test_cambiar_estado_empresa_via_patch_funciona(): void
    {
        $admin = $this->usuarioAdmin();
        $empresa = Empresa::factory()->create(['estado' => 1]);

        $this->actingAs($admin)
            ->patch("/cambiar-estado-empresa/{$empresa->id}/0")
            ->assertRedirect();

        $this->assertDatabaseHas('empresas', ['id' => $empresa->id, 'estado' => 0]);
    }

    public function test_cambiar_estado_sede_via_get_no_permitido(): void
    {
        $admin = $this->usuarioAdmin();
        $sede = Sede::factory()->create(['estado' => 1]);

        $this->actingAs($admin)
            ->get("/cambiar-estado-sede/{$sede->id}/0")
            ->assertStatus(405);
    }

    public function test_cambiar_estado_sede_via_patch_funciona(): void
    {
        $admin = $this->usuarioAdmin();
        $sede = Sede::factory()->create(['estado' => 1]);

        $this->actingAs($admin)
            ->patch("/cambiar-estado-sede/{$sede->id}/0")
            ->assertRedirect();

        $this->assertDatabaseHas('sedes', ['id' => $sede->id, 'estado' => 0]);
    }

    // -------------------------------------------------------
    // 6.3 — Middleware de roles protege rutas admin
    // -------------------------------------------------------

    public function test_config_requiere_rol_admin(): void
    {
        $empresa = $this->usuarioEmpresa();

        $this->actingAs($empresa)
            ->get('/config')
            ->assertForbidden();
    }

    public function test_config_accesible_para_admin(): void
    {
        // Crear config necesaria para que la vista no falle
        \App\Models\Config::create([
            'nombre' => 'REPRO Test',
            'currency' => 'GTQ Q',
        ]);

        $this->actingAs($this->usuarioAdmin())
            ->get('/config')
            ->assertOk();
    }

    public function test_empresas_admin_requiere_rol_admin_o_repro(): void
    {
        $this->actingAs($this->usuarioEmpresa())
            ->get('/empresas')
            ->assertForbidden();
    }

    public function test_empresas_admin_accesible_para_repro(): void
    {
        $this->actingAs($this->usuarioReproConPermiso('empresas.ver'))
            ->get('/empresas')
            ->assertOk();
    }

    /** Crea un usuario repro (role_as=2) con el permiso indicado. */
    private function usuarioReproConPermiso(string $permiso): User
    {
        $user = User::factory()->create(['role_as' => 2, 'estado' => 1]);
        $permission = Permission::firstOrCreate(
            ['name' => $permiso],
            ['display_name' => $permiso, 'module' => 'test']
        );
        $role = Role::firstOrCreate(['name' => 'test_role_' . $permiso], ['display_name' => 'Test']);
        $role->givePermission($permission);
        $user->assignRole('test_role_' . $permiso);
        return $user;
    }

    public function test_sedes_requiere_permiso_o_admin(): void
    {
        $this->actingAs($this->usuarioEmpresa())
            ->get('/sedes')
            ->assertForbidden();

        $this->actingAs($this->usuarioRepro())
            ->get('/sedes')
            ->assertForbidden();
    }

    public function test_sedes_accesible_para_admin(): void
    {
        $this->actingAs($this->usuarioAdmin())
            ->get('/sedes')
            ->assertOk();
    }

    public function test_sedes_accesible_con_permiso_sedes_ver(): void
    {
        $this->actingAs($this->usuarioReproConPermiso('sedes.ver'))
            ->get('/sedes')
            ->assertOk();
    }

    public function test_calendario_requiere_permiso_o_admin(): void
    {
        $this->actingAs($this->usuarioEmpresa())
            ->get('/calendario')
            ->assertForbidden();

        $this->actingAs($this->usuarioRepro())
            ->get('/calendario')
            ->assertForbidden();
    }

    public function test_calendario_accesible_para_admin(): void
    {
        $this->actingAs($this->usuarioAdmin())
            ->get('/calendario')
            ->assertOk();
    }

    public function test_calendario_accesible_con_permiso_calendario_ver(): void
    {
        $this->actingAs($this->usuarioReproConPermiso('calendario.ver'))
            ->get('/calendario')
            ->assertOk();
    }

    public function test_roles_requiere_rol_admin(): void
    {
        // Repro no puede acceder a roles (solo admin)
        $this->actingAs($this->usuarioRepro())
            ->get('/admin/roles')
            ->assertForbidden();
    }

    public function test_roles_accesible_para_admin(): void
    {
        $this->actingAs($this->usuarioAdmin())
            ->get('/admin/roles')
            ->assertOk();
    }

    // -------------------------------------------------------
    // 6.4 — AdminMiddleware corregido
    // -------------------------------------------------------

    public function test_admin_middleware_permite_admin(): void
    {
        $middleware = new \App\Http\Middleware\AdminMiddleware();
        $request = \Illuminate\Http\Request::create('/test');
        $this->actingAs($this->usuarioAdmin());

        $response = $middleware->handle($request, function ($req) {
            return response('ok');
        });

        $this->assertEquals('ok', $response->getContent());
    }

    public function test_admin_middleware_bloquea_empresa(): void
    {
        $middleware = new \App\Http\Middleware\AdminMiddleware();
        $request = \Illuminate\Http\Request::create('/test');
        $this->actingAs($this->usuarioEmpresa());

        $response = $middleware->handle($request, function ($req) {
            return response('ok');
        });

        $this->assertEquals(302, $response->getStatusCode());
    }

    // -------------------------------------------------------
    // 6.5 — Permisos granulares de módulos
    // -------------------------------------------------------

    public function test_repro_sin_ordenes_crear_no_puede_crear_orden(): void
    {
        // usuarioRepro() no tiene rol asignado ni ordenes.crear
        $this->actingAs($this->usuarioRepro())
            ->get('/ordenes/create')
            ->assertForbidden();
    }

    public function test_repro_con_ordenes_crear_puede_ver_form_orden(): void
    {
        $this->actingAs($this->usuarioReproConPermiso('ordenes.crear'))
            ->get('/ordenes/create')
            ->assertOk();
    }

    public function test_repro_sin_ordenes_ver_no_puede_ver_lista_ordenes(): void
    {
        $this->actingAs($this->usuarioRepro())
            ->get('/ordenes')
            ->assertForbidden();
    }

    public function test_repro_con_ordenes_ver_puede_ver_lista_ordenes(): void
    {
        $this->actingAs($this->usuarioReproConPermiso('ordenes.ver'))
            ->get('/ordenes')
            ->assertOk();
    }

    public function test_repro_sin_empresas_crear_no_puede_crear_empresa(): void
    {
        $this->actingAs($this->usuarioRepro())
            ->get('/add-empresa')
            ->assertForbidden();
    }

    public function test_repro_con_empresas_crear_puede_ver_form_empresa(): void
    {
        $this->actingAs($this->usuarioReproConPermiso('empresas.crear'))
            ->get('/add-empresa')
            ->assertOk();
    }

    public function test_empresa_sin_ordenes_crear_no_puede_crear_orden(): void
    {
        // usuarioEmpresa() solo tiene role_as=1, sin permisos asignados vía rol
        $this->actingAs($this->usuarioEmpresa())
            ->get('/ordenes/create')
            ->assertForbidden();
    }

    // -------------------------------------------------------
    // Perfil propio: empresa y repro pueden editar su propio perfil
    // -------------------------------------------------------

    public function test_empresa_puede_ver_formulario_edicion_su_propio_perfil(): void
    {
        $empresa = $this->usuarioEmpresa();
        $this->actingAs($empresa)
            ->get(url('edit-user/' . $empresa->id))
            ->assertOk();
    }

    public function test_repro_puede_ver_formulario_edicion_su_propio_perfil(): void
    {
        $repro = $this->usuarioRepro();
        $this->actingAs($repro)
            ->get(url('edit-user/' . $repro->id))
            ->assertOk();
    }

    public function test_empresa_no_puede_editar_perfil_de_otro_usuario(): void
    {
        $empresa = $this->usuarioEmpresa();
        $otro = $this->usuarioAdmin();
        $this->actingAs($empresa)
            ->get(url('edit-user/' . $otro->id))
            ->assertForbidden();
    }

    public function test_repro_no_puede_editar_perfil_de_otro_usuario(): void
    {
        $repro = $this->usuarioRepro();
        $otro = $this->usuarioAdmin();
        $this->actingAs($repro)
            ->get(url('edit-user/' . $otro->id))
            ->assertForbidden();
    }
}
