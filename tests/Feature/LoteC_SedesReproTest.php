<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Sede;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests Lote C — Sedes REPRO para rol empresa (Fase 16).
 *
 * Cubre:
 * - Empresa puede acceder a la vista de sedes
 * - La vista muestra las sedes activas con sus datos
 * - Las sedes inactivas no aparecen
 * - Usuarios sin rol empresa (admin, repro) no pueden acceder por esa ruta
 * - Ítem "Sedes REPRO" aparece en el sidebar empresa
 */
class LoteC_SedesReproTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'admin', 'display_name' => 'Administrador']);
        Role::create(['name' => 'repro', 'display_name' => 'Repro']);
        Role::create(['name' => 'empresa', 'display_name' => 'Empresa']);
    }

    private function crearEmpresa(): User
    {
        $user = User::factory()->create(['role_as' => 1, 'estado' => 1]);
        $user->roles()->attach(Role::where('name', 'empresa')->first());
        return $user;
    }

    private function crearAdmin(): User
    {
        $user = User::factory()->create(['role_as' => 3, 'estado' => 1]);
        $user->roles()->attach(Role::where('name', 'admin')->first());
        return $user;
    }

    // ─────────────────────────────────────────────────────
    // Acceso y visibilidad
    // ─────────────────────────────────────────────────────

    public function test_empresa_puede_acceder_a_sedes_repro(): void
    {
        $user = $this->crearEmpresa();

        $response = $this->actingAs($user)->get('/empresa/sedes-repro');

        $response->assertOk();
        $response->assertViewIs('empresa.sedes.index');
    }

    public function test_vista_muestra_sedes_activas(): void
    {
        $user = $this->crearEmpresa();
        $sede = Sede::factory()->create([
            'nombre'    => 'Sede Central',
            'direccion' => 'Av. Reforma 100',
            'telefono'  => '2222-3333',
            'estado'    => 1,
        ]);

        $response = $this->actingAs($user)->get('/empresa/sedes-repro');

        $response->assertSee('Sede Central');
        $response->assertSee('Av. Reforma 100');
        $response->assertSee('2222-3333');
    }

    public function test_vista_no_muestra_sedes_inactivas(): void
    {
        $user = $this->crearEmpresa();
        Sede::factory()->create([
            'nombre'  => 'Sede Cerrada',
            'estado'  => 0,
        ]);

        $response = $this->actingAs($user)->get('/empresa/sedes-repro');

        $response->assertDontSee('Sede Cerrada');
    }

    public function test_vista_muestra_enlace_whatsapp_cuando_existe(): void
    {
        $user = $this->crearEmpresa();
        Sede::factory()->create([
            'nombre'    => 'Sede Norte',
            'whatsapp'  => '50212345678',
            'estado'    => 1,
        ]);

        $response = $this->actingAs($user)->get('/empresa/sedes-repro');

        $response->assertSee('wa.me/50212345678');
    }

    public function test_vista_muestra_enlace_mapa_cuando_existe(): void
    {
        $user = $this->crearEmpresa();
        Sede::factory()->create([
            'nombre'      => 'Sede Sur',
            'enlace_maps' => 'https://maps.google.com/?q=123',
            'estado'      => 1,
        ]);

        $response = $this->actingAs($user)->get('/empresa/sedes-repro');

        $response->assertSee('https://maps.google.com/?q=123');
        $response->assertSee('Ver en mapa');
    }

    public function test_admin_no_puede_usar_ruta_empresa_sedes(): void
    {
        $admin = $this->crearAdmin();

        $response = $this->actingAs($admin)->get('/empresa/sedes-repro');

        // La ruta está protegida con middleware role:empresa; admin recibe 403
        $response->assertStatus(403);
    }

    public function test_usuario_no_autenticado_es_redirigido(): void
    {
        $response = $this->get('/empresa/sedes-repro');

        $response->assertRedirect('/login');
    }

    // ─────────────────────────────────────────────────────
    // Sidebar
    // ─────────────────────────────────────────────────────

    public function test_sidebar_empresa_muestra_enlace_sedes_repro(): void
    {
        $user = $this->crearEmpresa();

        $response = $this->actingAs($user)->get('/empresa/sedes-repro');

        $response->assertSee('Sedes REPRO');
        $response->assertSee(route('empresa.sedes-repro'));
    }
}
