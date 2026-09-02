<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesRolesAndPermissions;
use Tests\TestCase;

class EmpresaCreadorTest extends TestCase
{
    use RefreshDatabase;
    use CreatesRolesAndPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpRolesAndPermissions();
    }

    public function test_al_crear_empresa_queda_quien_la_registro(): void
    {
        $admin = User::factory()->create([
            'name' => 'Stephany Admin O',
            'role_as' => 3,
            'estado' => 1,
        ]);
        $admin->roles()->attach(Role::where('name', 'admin')->first());

        $this->actingAs($admin)
            ->post(route('empresas.store'), [
                'nombre' => 'Empresa Duplicada O',
                'nit' => '1234567-8',
            ])
            ->assertRedirect('empresas');

        $empresa = Empresa::query()->where('nombre', 'Empresa Duplicada O')->first();
        $this->assertNotNull($empresa);
        $this->assertSame($admin->id, $empresa->created_by);
        $this->assertSame('Stephany Admin O', $empresa->nombreCreador());

        $this->actingAs($admin)
            ->get(route('empresas.show', $empresa->id))
            ->assertOk()
            ->assertSee('Creada por:')
            ->assertSee('Stephany Admin O');
    }

    public function test_empresa_antigua_muestra_sin_registro(): void
    {
        $empresa = Empresa::factory()->create(['nombre' => 'Empresa Vieja O', 'created_by' => null]);
        $this->assertSame('Sin registro', $empresa->nombreCreador());
    }
}
