<?php

namespace Tests\Feature;

use App\Models\Config;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class OptimizacionTest extends TestCase
{
    use RefreshDatabase;

    // =============================================
    // 7.1 Named Routes
    // =============================================

    public function test_rutas_users_tienen_nombre(): void
    {
        $expectedNames = [
            'users.index', 'users.show', 'users.create', 'users.store',
            'users.edit', 'users.update', 'users.destroy', 'users.pdf', 'users.pdf.show',
        ];

        foreach ($expectedNames as $name) {
            $this->assertTrue(Route::has($name), "Ruta '{$name}' no encontrada");
        }
    }

    public function test_rutas_config_tienen_nombre(): void
    {
        $this->assertTrue(Route::has('config.index'));
        $this->assertTrue(Route::has('config.update'));
    }

    public function test_rutas_empresas_tienen_nombre(): void
    {
        $expectedNames = [
            'empresas.index', 'empresas.create', 'empresas.store',
            'empresas.edit', 'empresas.update', 'empresas.show',
            'empresas.cambiar-estado', 'empresas.pdf', 'empresas.pdf.show',
        ];

        foreach ($expectedNames as $name) {
            $this->assertTrue(Route::has($name), "Ruta '{$name}' no encontrada");
        }
    }

    // =============================================
    // 7.2 Índices de BD
    // =============================================

    public function test_indice_empresas_estado_existe(): void
    {
        $indexes = collect(
            \Illuminate\Support\Facades\DB::select("SHOW INDEX FROM empresas WHERE Key_name = 'empresas_estado_index'")
        );

        $this->assertTrue($indexes->isNotEmpty(), 'Índice empresas_estado_index no encontrado');
    }

    public function test_indice_sedes_estado_existe(): void
    {
        $indexes = collect(
            \Illuminate\Support\Facades\DB::select("SHOW INDEX FROM sedes WHERE Key_name = 'sedes_estado_index'")
        );

        $this->assertTrue($indexes->isNotEmpty(), 'Índice sedes_estado_index no encontrado');
    }

    // =============================================
    // 7.3 Imports limpios — verificado indirectamente
    // =============================================

    public function test_empresas_controller_pdf_funciona_con_facade_correcta(): void
    {
        $admin = User::factory()->create(['role_as' => 3, 'estado' => 1]);
        Empresa::factory()->create(['estado' => 1]);
        Config::create([
            'currency' => 'GTQ Q',
            'currency_simbol' => 'Q',
            'email' => 'test@repro.gt',
        ]);

        $response = $this->actingAs($admin)->get(route('empresas.pdf'));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
    }

    // =============================================
    // 7.4 Checks redundantes eliminados
    // =============================================

    public function test_empresas_accesible_para_repro_sin_check_inline(): void
    {
        $repro = User::factory()->create(['role_as' => 2, 'estado' => 1]);

        $response = $this->actingAs($repro)->get(route('empresas.index'));

        $response->assertOk();
    }

    public function test_empresas_create_accesible_para_repro(): void
    {
        $repro = User::factory()->create(['role_as' => 2, 'estado' => 1]);

        $response = $this->actingAs($repro)->get(route('empresas.create'));

        $response->assertOk();
    }

    public function test_empresas_show_accesible_para_repro(): void
    {
        $repro = User::factory()->create(['role_as' => 2, 'estado' => 1]);
        $empresa = Empresa::factory()->create(['estado' => 1]);

        $response = $this->actingAs($repro)->get(route('empresas.show', $empresa->id));

        $response->assertOk();
    }

    public function test_empresas_edit_accesible_para_repro(): void
    {
        $repro = User::factory()->create(['role_as' => 2, 'estado' => 1]);
        $empresa = Empresa::factory()->create(['estado' => 1]);

        $response = $this->actingAs($repro)->get(route('empresas.edit', $empresa->id));

        $response->assertOk();
    }

    // =============================================
    // 7.6 Query builders extraídos
    // =============================================

    public function test_empresas_index_filtra_por_busqueda(): void
    {
        $admin = User::factory()->create(['role_as' => 3, 'estado' => 1]);
        Empresa::factory()->create(['nombre' => 'Empresa Alpha', 'estado' => 1]);
        Empresa::factory()->create(['nombre' => 'Empresa Beta', 'estado' => 1]);

        $response = $this->actingAs($admin)->get(route('empresas.index', ['search' => 'Alpha']));

        $response->assertOk();
        $response->assertSee('Empresa Alpha');
        $response->assertDontSee('Empresa Beta');
    }

    public function test_users_index_filtra_por_rol(): void
    {
        $admin = User::factory()->create(['role_as' => 3, 'estado' => 1, 'name' => 'Admin User']);
        User::factory()->create(['role_as' => 2, 'estado' => 1, 'name' => 'Repro User']);
        User::factory()->create(['role_as' => 1, 'estado' => 1, 'name' => 'Empresa User', 'empresa_id' => Empresa::factory()->create()->id]);

        $response = $this->actingAs($admin)->get(route('users.index', ['role_filter' => '2']));

        $response->assertOk();
        $response->assertSee('Repro User');
    }

    // =============================================
    // 7.7 Eager loading — N+1
    // =============================================

    public function test_users_index_carga_relacion_empresa(): void
    {
        $admin = User::factory()->create(['role_as' => 3, 'estado' => 1]);
        $empresa = Empresa::factory()->create(['nombre' => 'Corp ABC', 'estado' => 1]);
        User::factory()->create([
            'role_as' => 1,
            'estado' => 1,
            'empresa_id' => $empresa->id,
            'name' => 'User de Corp ABC',
        ]);

        $response = $this->actingAs($admin)->get(route('users.index'));

        $response->assertOk();
    }
}
