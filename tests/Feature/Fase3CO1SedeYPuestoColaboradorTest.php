<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Sede;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Fase3CO1SedeYPuestoColaboradorTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'admin', 'display_name' => 'Administrador', 'level' => 3]);
        Role::create(['name' => 'empresa', 'display_name' => 'Empresa', 'level' => 1]);
        Role::create(['name' => 'repro', 'display_name' => 'REPRO', 'level' => 2]);

        $this->adminUser = User::factory()->create(['role_as' => 3, 'estado' => 1]);
        $this->adminUser->roles()->attach(Role::where('name', 'admin')->first());
    }

    public function test_admin_puede_crear_colaborador_con_sede_y_cargo(): void
    {
        $sede = Sede::factory()->create(['estado' => 1]);

        $reproRole = Role::where('name', 'repro')->first();

        $response = $this->actingAs($this->adminUser)->post(route('users.store'), [
            'name'              => 'Colaborador Test',
            'email'             => 'colab@test.com',
            'role_id'           => $reproRole->id,
            'cargo'             => 'Poligrafista',
            'sede_id'           => $sede->id,
            'telefono'          => '55551234',
            'celular'           => '55559876',
            'direccion'         => 'Dir test',
            'fecha_nacimiento'  => '1990-01-01',
        ]);

        $response->assertRedirect();
        $usuario = User::where('email', 'colab@test.com')->first();
        $this->assertNotNull($usuario);
        $this->assertEquals('Poligrafista', $usuario->cargo);
        $this->assertEquals($sede->id, $usuario->sede_id);
    }

    public function test_admin_puede_editar_sede_y_cargo_de_colaborador(): void
    {
        $sede = Sede::factory()->create(['estado' => 1]);
        $colaborador = User::factory()->create(['role_as' => 2, 'estado' => 1]);
        $colaborador->roles()->attach(Role::where('name', 'repro')->first());

        $response = $this->actingAs($this->adminUser)
            ->put(route('users.update', $colaborador->id), [
                'name'              => $colaborador->name,
                'email'             => $colaborador->email,
                'role_as'           => 2,
                'cargo'             => 'Coordinador',
                'sede_id'           => $sede->id,
                'telefono'          => '55551234',
                'celular'           => '55559876',
                'direccion'         => 'Dir test',
                'fecha_nacimiento'  => '1990-01-01',
            ]);

        $response->assertRedirect();
        $colaborador->refresh();
        $this->assertEquals('Coordinador', $colaborador->cargo);
        $this->assertEquals($sede->id, $colaborador->sede_id);
    }

    public function test_formulario_crear_colaborador_muestra_campos_sede_y_cargo(): void
    {
        $sede = Sede::factory()->create(['estado' => 1]);

        $response = $this->actingAs($this->adminUser)->get(route('users.create'));

        $response->assertStatus(200);
        $response->assertSee('sede_id');
        $response->assertSee('cargo');
    }
}
