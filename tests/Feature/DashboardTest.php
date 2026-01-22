<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test que el dashboard requiere autenticación.
     */
    public function test_dashboard_requires_authentication(): void
    {
        $response = $this->get(route('dashboard'));
        
        $response->assertRedirect(route('login'));
    }

    /**
     * Test que un usuario admin puede ver el dashboard con estadísticas.
     */
    public function test_admin_can_see_dashboard_with_statistics(): void
    {
        $admin = User::factory()->create([
            'role_as' => 3,
            'estado' => 1,
        ]);

        $empresa = Empresa::factory()->create(['estado' => 1]);
        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'estado' => 'solicitud',
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.index');
        $response->assertViewHas('totalOrdenes');
        $response->assertViewHas('totalEmpresas');
        $response->assertViewHas('totalEvaluados');
        $response->assertViewHas('ordenesPorEstado');
        $response->assertViewHas('ultimasOrdenes');
        $response->assertViewHas('topEmpresas');
        $response->assertSee('Panel de Control');
        $response->assertSee('Órdenes Totales');
    }

    /**
     * Test que un usuario REPRO puede ver el dashboard.
     */
    public function test_repro_user_can_see_dashboard(): void
    {
        $repro = User::factory()->create([
            'role_as' => 2,
            'estado' => 1,
        ]);

        $response = $this->actingAs($repro)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.index');
        $response->assertViewHas('totalOrdenes');
    }

    /**
     * Test que un usuario de empresa puede ver su dashboard específico.
     */
    public function test_empresa_user_can_see_their_dashboard(): void
    {
        $empresa = Empresa::factory()->create(['estado' => 1]);
        $user = User::factory()->create([
            'role_as' => 1,
            'estado' => 1,
            'empresa_id' => $empresa->id,
        ]);

        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'estado' => 'solicitud',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.index');
        $response->assertViewHas('empresa');
        $response->assertViewHas('totalOrdenes');
        $response->assertSee('Mis Órdenes');
    }

    /**
     * Test que las estadísticas del admin muestran datos correctos.
     */
    public function test_admin_statistics_show_correct_data(): void
    {
        $admin = User::factory()->create([
            'role_as' => 3,
            'estado' => 1,
        ]);

        // Crear empresas
        $empresas = Empresa::factory()->count(3)->create(['estado' => 1]);
        
        // Crear órdenes en diferentes estados
        Orden::factory()->create([
            'empresa_id' => $empresas[0]->id,
            'estado' => 'solicitud',
        ]);
        Orden::factory()->create([
            'empresa_id' => $empresas[1]->id,
            'estado' => 'en_proceso',
        ]);
        Orden::factory()->create([
            'empresa_id' => $empresas[0]->id,
            'estado' => 'entregado',
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('totalOrdenes', 3);
        $response->assertViewHas('totalEmpresas', 3);
    }

    /**
     * Test que el dashboard de empresa solo muestra sus propias órdenes.
     */
    public function test_empresa_dashboard_shows_only_their_orders(): void
    {
        $empresa1 = Empresa::factory()->create(['estado' => 1]);
        $empresa2 = Empresa::factory()->create(['estado' => 1]);

        $user = User::factory()->create([
            'role_as' => 1,
            'estado' => 1,
            'empresa_id' => $empresa1->id,
        ]);

        // Crear órdenes para empresa1
        Orden::factory()->count(2)->create(['empresa_id' => $empresa1->id]);
        // Crear órdenes para empresa2
        Orden::factory()->count(3)->create(['empresa_id' => $empresa2->id]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        // El usuario solo debe ver sus 2 órdenes
        $response->assertViewHas('totalOrdenes', 2);
    }
}
