<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class EmpresaModulosTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $empresaUser;
    protected User $empresaSecondaryUser;
    protected Empresa $empresa;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear empresa
        $this->empresa = Empresa::factory()->create([
            'nombre' => 'Test Empresa',
            'estado' => 1,
        ]);

        // Crear usuario principal de empresa
        $this->empresaUser = User::factory()->create([
            'name' => 'Usuario Principal',
            'email' => 'principal@empresa.com',
            'password' => Hash::make('password'),
            'role_as' => 1, // empresa
            'empresa_id' => $this->empresa->id,
            'principal' => 1,
            'estado' => 1,
        ]);

        // Crear usuario secundario de empresa
        $this->empresaSecondaryUser = User::factory()->create([
            'name' => 'Usuario Secundario',
            'email' => 'secundario@empresa.com',
            'password' => Hash::make('password'),
            'role_as' => 1, // empresa
            'empresa_id' => $this->empresa->id,
            'principal' => 0,
            'estado' => 1,
        ]);
    }

    // ========================================
    // TESTS DE MI EMPRESA
    // ========================================

    public function test_empresa_user_can_view_mi_empresa(): void
    {
        $response = $this->actingAs($this->empresaUser)
            ->get(route('empresa.mi-empresa'));

        $response->assertStatus(200);
        $response->assertViewIs('empresa.mi-empresa.index');
        $response->assertSee($this->empresa->nombre);
    }

    public function test_empresa_user_sees_company_statistics(): void
    {
        // Crear algunas órdenes
        $orden = Orden::factory()->create([
            'empresa_id' => $this->empresa->id,
            'estado' => 'orden_recibida', // Usar estado válido del enum
        ]);

        EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'cuestionario_completado' => true,
        ]);

        $response = $this->actingAs($this->empresaUser)
            ->get(route('empresa.mi-empresa'));

        $response->assertStatus(200);
        $response->assertViewHas('stats');
    }

    public function test_only_principal_user_can_edit_empresa(): void
    {
        // Usuario principal puede ver formulario de edición
        $response = $this->actingAs($this->empresaUser)
            ->get(route('empresa.mi-empresa.edit'));
        $response->assertStatus(200);

        // Usuario secundario no puede ver formulario de edición
        $response = $this->actingAs($this->empresaSecondaryUser)
            ->get(route('empresa.mi-empresa.edit'));
        $response->assertRedirect();
    }

    public function test_principal_user_can_update_empresa(): void
    {
        $response = $this->actingAs($this->empresaUser)
            ->put(route('empresa.mi-empresa.update'), [
                'nombre' => 'Nuevo Nombre Empresa',
                'nit' => '123456789',
                'telefono' => '12345678',
            ]);

        $response->assertRedirect(route('empresa.mi-empresa'));
        $this->assertDatabaseHas('empresas', [
            'id' => $this->empresa->id,
            'nombre' => 'Nuevo Nombre Empresa',
        ]);
    }

    // ========================================
    // TESTS DE USUARIOS DE EMPRESA
    // ========================================

    public function test_only_principal_can_view_usuarios(): void
    {
        // Usuario principal puede ver listado
        $response = $this->actingAs($this->empresaUser)
            ->get(route('empresa.usuarios'));
        $response->assertStatus(200);

        // Usuario secundario no puede ver listado
        $response = $this->actingAs($this->empresaSecondaryUser)
            ->get(route('empresa.usuarios'));
        $response->assertRedirect();
    }

    public function test_principal_can_create_user(): void
    {
        $response = $this->actingAs($this->empresaUser)
            ->post(route('empresa.usuarios.store'), [
                'name' => 'Nuevo Usuario',
                'email' => 'nuevo@empresa.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'telefono' => '12345678',
                'cargo' => 'Analista',
            ]);

        $response->assertRedirect(route('empresa.usuarios'));
        $this->assertDatabaseHas('users', [
            'email' => 'nuevo@empresa.com',
            'empresa_id' => $this->empresa->id,
            'role_as' => 1,
            'principal' => 0,
        ]);
    }

    public function test_principal_can_update_secondary_user(): void
    {
        $response = $this->actingAs($this->empresaUser)
            ->put(route('empresa.usuarios.update', $this->empresaSecondaryUser), [
                'name' => 'Usuario Editado',
                'email' => 'editado@empresa.com',
                'estado' => 1,
            ]);

        $response->assertRedirect(route('empresa.usuarios'));
        $this->assertDatabaseHas('users', [
            'id' => $this->empresaSecondaryUser->id,
            'name' => 'Usuario Editado',
        ]);
    }

    public function test_principal_cannot_edit_himself_from_usuarios(): void
    {
        // El usuario principal no se puede editar desde la sección de usuarios
        $response = $this->actingAs($this->empresaUser)
            ->get(route('empresa.usuarios.edit', $this->empresaUser));

        $response->assertRedirect();
    }

    public function test_principal_can_delete_secondary_user(): void
    {
        $response = $this->actingAs($this->empresaUser)
            ->delete(route('empresa.usuarios.destroy', $this->empresaSecondaryUser));

        $response->assertRedirect(route('empresa.usuarios'));
        $this->assertDatabaseMissing('users', [
            'id' => $this->empresaSecondaryUser->id,
        ]);
    }

    public function test_principal_cannot_delete_himself(): void
    {
        $response = $this->actingAs($this->empresaUser)
            ->delete(route('empresa.usuarios.destroy', $this->empresaUser));

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $this->empresaUser->id,
        ]);
    }

    // ========================================
    // TESTS DE CUESTIONARIOS EMPRESA
    // ========================================

    public function test_empresa_can_view_cuestionarios(): void
    {
        $orden = Orden::factory()->create([
            'empresa_id' => $this->empresa->id,
        ]);

        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
        ]);

        $response = $this->actingAs($this->empresaUser)
            ->get(route('empresa.cuestionarios'));

        $response->assertStatus(200);
        $response->assertViewIs('empresa.cuestionarios.index');
        $response->assertSee($evaluado->nombre);
    }

    public function test_empresa_can_view_single_cuestionario(): void
    {
        $orden = Orden::factory()->create([
            'empresa_id' => $this->empresa->id,
        ]);

        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
        ]);

        $response = $this->actingAs($this->empresaUser)
            ->get(route('empresa.cuestionarios.show', $evaluado));

        $response->assertStatus(200);
        $response->assertViewIs('empresa.cuestionarios.show');
    }

    public function test_empresa_cannot_view_other_empresa_cuestionario(): void
    {
        // Crear otra empresa con su orden y evaluado
        $otraEmpresa = Empresa::factory()->create();
        $otraOrden = Orden::factory()->create([
            'empresa_id' => $otraEmpresa->id,
        ]);
        $otroEvaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $otraOrden->id,
        ]);

        // Intentar ver cuestionario de otra empresa
        $response = $this->actingAs($this->empresaUser)
            ->get(route('empresa.cuestionarios.show', $otroEvaluado));

        $response->assertStatus(403);
    }

    public function test_cuestionarios_can_be_filtered(): void
    {
        $orden = Orden::factory()->create([
            'empresa_id' => $this->empresa->id,
        ]);

        EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'cuestionario_completado' => true,
            'nombre' => 'Juan Completado',
        ]);

        EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'cuestionario_completado' => false,
            'nombre' => 'Pedro Pendiente',
        ]);

        // Filtrar solo completados
        $response = $this->actingAs($this->empresaUser)
            ->get(route('empresa.cuestionarios', ['estado' => 'completado']));

        $response->assertStatus(200);
        $response->assertSee('Juan Completado');
        $response->assertDontSee('Pedro Pendiente');
    }

    // ========================================
    // TESTS DE ACCESO (SEGURIDAD)
    // ========================================

    public function test_admin_cannot_access_empresa_routes(): void
    {
        $admin = User::factory()->create([
            'role_as' => 3, // admin
        ]);

        $response = $this->actingAs($admin)
            ->get(route('empresa.mi-empresa'));

        $response->assertStatus(403);
    }

    public function test_repro_cannot_access_empresa_routes(): void
    {
        $repro = User::factory()->create([
            'role_as' => 2, // repro
        ]);

        $response = $this->actingAs($repro)
            ->get(route('empresa.mi-empresa'));

        $response->assertStatus(403);
    }

    public function test_unauthenticated_cannot_access_empresa_routes(): void
    {
        $response = $this->get(route('empresa.mi-empresa'));
        $response->assertRedirect(route('login'));
    }
}
