<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Models\Empresa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesRolesAndPermissions;
use Tests\TestCase;

class UsersFormRolesTest extends TestCase
{
    use RefreshDatabase, CreatesRolesAndPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpRolesAndPermissions();
    }

    /** @test */
    public function test_formulario_crear_usuario_muestra_roles_desde_bd(): void
    {
        $admin = User::factory()->create(['role_as' => 3]);
        $adminRole = Role::where('name', 'admin')->first();
        $admin->roles()->attach($adminRole->id);

        $response = $this->actingAs($admin)->get(route('users.create'));

        $response->assertStatus(200);
        // Verifica que los display_name reales de la BD aparecen en el select
        $response->assertSee('Administrador');
        $response->assertSee('Personal Repro');
        $response->assertSee('Usuario Empresa');
    }

    /** @test */
    public function test_formulario_crear_usuario_muestra_roles_custom_tambien(): void
    {
        // Crear un rol personalizado con level=1 por defecto (empresa level)
        Role::create(['name' => 'supervisor_especial', 'display_name' => 'Supervisor Especial', 'description' => 'Rol de prueba']);

        $admin = User::factory()->create(['role_as' => 3]);
        $adminRole = Role::where('name', 'admin')->first();
        $admin->roles()->attach($adminRole->id);

        $response = $this->actingAs($admin)->get(route('users.create'));

        $response->assertStatus(200);
        // Los roles custom con level=1 sí deben aparecer (level defecto es 1 = empresa)
        $response->assertSee('Supervisor Especial');
        // Los roles principales también siguen apareciendo
        $response->assertSee('Administrador');
        $response->assertSee('Usuario Empresa');
    }

    /** @test */
    public function test_formulario_editar_usuario_muestra_roles_desde_bd(): void
    {
        $admin = User::factory()->create(['role_as' => 3]);
        $adminRole = Role::where('name', 'admin')->first();
        $admin->roles()->attach($adminRole->id);

        $empresa = Empresa::factory()->create(['estado' => 1]);
        $usuarioEmpresa = User::factory()->create(['role_as' => 1, 'empresa_id' => $empresa->id]);
        $empresaRole = Role::where('name', 'empresa')->first();
        $usuarioEmpresa->roles()->attach($empresaRole->id);

        $response = $this->actingAs($admin)->get(route('users.edit', $usuarioEmpresa->id));

        $response->assertStatus(200);
        // Verifica que los display_name reales de la BD aparecen en el select de edición
        $response->assertSee('Administrador');
        $response->assertSee('Personal Repro');
        $response->assertSee('Usuario Empresa');
    }

    /** @test */
    public function test_valor_seleccionado_en_edicion_corresponde_al_role_as_del_usuario(): void
    {
        $admin = User::factory()->create(['role_as' => 3]);
        $adminRole = Role::where('name', 'admin')->first();
        $admin->roles()->attach($adminRole->id);

        // Usar empresa_id=999 para evitar colisión con los IDs del role select
        $empresa = Empresa::factory()->create(['id' => 999, 'estado' => 1]);
        $usuarioEmpresa = User::factory()->create(['role_as' => 1, 'empresa_id' => 999]);
        $empresaRole = Role::where('name', 'empresa')->first();
        $usuarioEmpresa->roles()->attach($empresaRole->id);

        $response = $this->actingAs($admin)->get(route('users.edit', $usuarioEmpresa->id));

        $response->assertStatus(200);
        // El select ahora usa el ID del rol como value
        $html = $response->getContent();
        preg_match('/<select[^>]+id="role_as"[^>]*>(.*?)<\/select>/s', $html, $matches);
        $roleAsSelectHtml = $matches[1] ?? '';
        // La opción con el ID del rol empresa debe estar seleccionada
        $this->assertStringContainsString('value="' . $empresaRole->id . '" selected', $roleAsSelectHtml);
        // El rol admin no debe estar seleccionado
        $this->assertStringNotContainsString('value="' . $adminRole->id . '" selected', $roleAsSelectHtml);
    }
}

