<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesRolesAndPermissions;
use Tests\TestCase;

class PapeleriaCamaraTatuajesTest extends TestCase
{
    use RefreshDatabase, CreatesRolesAndPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpRolesAndPermissions();
    }

    public function test_repro_ve_tatuajes_y_camara_en_papeleria(): void
    {
        $admin = User::factory()->create(['role_as' => 3, 'estado' => 1]);
        $admin->roles()->attach(Role::where('name', 'admin')->first());
        $orden = Orden::factory()->create(['creado_por' => $admin->id]);
        EvaluadoOrden::factory()->create(['orden_id' => $orden->id]);

        $this->actingAs($admin)
            ->get(route('ordenes.show', $orden))
            ->assertOk()
            ->assertSee('Tatuajes')
            ->assertSee('Tomar foto')
            ->assertSee('Ctrl+V')
            ->assertSee('image/*', false)
            ->assertSee('capture="environment"', false);
    }

    public function test_cliente_ve_tatuajes_y_camara_en_papeleria(): void
    {
        $empresa = Empresa::factory()->create();
        $cliente = User::factory()->create([
            'role_as' => 1,
            'estado' => 1,
            'empresa_id' => $empresa->id,
            'principal' => 1,
        ]);
        $cliente->roles()->attach(Role::where('name', 'empresa')->first());
        $orden = Orden::factory()->create(['empresa_id' => $empresa->id]);
        EvaluadoOrden::factory()->create(['orden_id' => $orden->id]);

        $this->actingAs($cliente)
            ->get(route('empresa.ordenes.show', $orden))
            ->assertOk()
            ->assertSee('Tatuajes')
            ->assertSee('Tomar foto')
            ->assertSee('Ctrl+V')
            ->assertSee('image/*', false)
            ->assertSee('capture="environment"', false);
    }
}
