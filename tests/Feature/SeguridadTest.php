<?php

namespace Tests\Feature;

use App\Models\Empresa;
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
        $this->actingAs($this->usuarioRepro())
            ->get('/empresas')
            ->assertOk();
    }

    public function test_sedes_requiere_rol_admin_o_repro(): void
    {
        $this->actingAs($this->usuarioEmpresa())
            ->get('/sedes')
            ->assertForbidden();
    }

    public function test_sedes_accesible_para_repro(): void
    {
        $this->actingAs($this->usuarioRepro())
            ->get('/sedes')
            ->assertOk();
    }

    public function test_calendario_requiere_rol_admin_o_repro(): void
    {
        $this->actingAs($this->usuarioEmpresa())
            ->get('/calendario')
            ->assertForbidden();
    }

    public function test_calendario_accesible_para_repro(): void
    {
        $this->actingAs($this->usuarioRepro())
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
}
