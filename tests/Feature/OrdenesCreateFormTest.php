<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Empresa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrdenesCreateFormTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear roles
        Role::create(['name' => 'admin', 'display_name' => 'Administrador']);
        Role::create(['name' => 'repro', 'display_name' => 'REPRO']);
        Role::create(['name' => 'empresa', 'display_name' => 'Empresa']);
    }

    public function test_admin_puede_ver_formulario_creacion_con_empresas()
    {
        // Crear admin
        $admin = User::factory()->create();
        $adminRole = Role::where('name', 'admin')->first();
        $admin->roles()->attach($adminRole->id);

        // Crear empresas
        $empresa1 = Empresa::factory()->create(['nombre' => 'Empresa Test 1', 'estado' => 1]);
        $empresa2 = Empresa::factory()->create(['nombre' => 'Empresa Test 2', 'estado' => 1]);

        // Acceder al formulario
        $response = $this->actingAs($admin)
                         ->get(route('ordenes.create'));

        $response->assertStatus(200);
        $response->assertSee('Nueva Orden de Evaluación');
        $response->assertSee('Empresa Test 1');
        $response->assertSee('Empresa Test 2');
        $response->assertSee('Seleccionar empresa...');
    }

    public function test_usuario_empresa_ve_su_empresa_en_formulario()
    {
        // Crear empresa y usuario empresa
        $empresa = Empresa::factory()->create(['nombre' => 'Mi Empresa', 'estado' => 1]);
        $userEmpresa = User::factory()->create(['empresa_id' => $empresa->id]);
        $empresaRole = Role::where('name', 'empresa')->first();
        $userEmpresa->roles()->attach($empresaRole->id);

        // Acceder al formulario
        $response = $this->actingAs($userEmpresa)
                         ->get(route('ordenes.create'));

        $response->assertStatus(200);
        $response->assertSee('Nueva Orden de Evaluación');
        $response->assertSee('Mi Empresa');
        // No debe ver el select, sino un input readonly
        $response->assertSee('readonly');
    }
}