<?php

namespace Tests\Feature;

use App\Models\Config;
use App\Models\Empresa;
use App\Models\Orden;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\Concerns\CreatesRolesAndPermissions;
use Tests\TestCase;

class BugFixesPhase8ATest extends TestCase
{
    use RefreshDatabase, WithFaker, CreatesRolesAndPermissions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpRolesAndPermissions();
    }

    // ─── B1: old() repopula evaluados al fallar validación ───

    public function test_crear_orden_con_error_conserva_datos_evaluados(): void
    {
        $admin = User::factory()->create(['role_as' => 3]);
        $admin->roles()->attach(Role::where('name', 'admin')->first());
        $empresa = Empresa::factory()->create();

        // Enviar datos incompletos (sin empresa_id) para forzar error de validación
        $response = $this->actingAs($admin)->post(route('ordenes.store'), [
            'empresa_id' => '',
            'evaluados' => [
                1 => [
                    'nombre' => 'María',
                    'apellidos' => 'García',
                    'dpi' => '1234567890123',
                    'email' => 'maria@test.com',
                    'tipo_servicio' => 'poligrafo',
                    'tipo_formulario' => 'preempleo',
                    'telefono' => '55551234',
                ],
            ],
        ]);

        $response->assertSessionHasErrors('empresa_id');
        // Los datos del evaluado deben conservarse en old()
        $this->assertNotEmpty(session()->getOldInput('evaluados'));
    }

    public function test_crear_orden_form_renderiza_old_evaluados(): void
    {
        $admin = User::factory()->create(['role_as' => 3]);
        $admin->roles()->attach(Role::where('name', 'admin')->first());
        Empresa::factory()->create();

        // Primero forzamos error para que se guarden los datos en sesión
        $this->actingAs($admin)->post(route('ordenes.store'), [
            'empresa_id' => '',
            'evaluados' => [
                1 => [
                    'nombre' => 'TestNombre',
                    'apellidos' => 'TestApellido',
                    'dpi' => '9876543210123',
                    'email' => 'test@evaluado.com',
                    'tipo_servicio' => 'vsa',
                    'tipo_formulario' => 'periodica',
                    'telefono' => '12345678',
                ],
            ],
        ]);

        // Al volver al formulario, el blade usa @json(old('evaluados', []))
        $response = $this->actingAs($admin)->get(route('ordenes.create'));
        $response->assertStatus(200);
        // La vista debe contener la variable evaluadosOld con datos JSON
        $response->assertSee('evaluadosOld');
    }

    // ─── B2: Empresa puede crear orden sin error ───

    public function test_empresa_puede_crear_orden_exitosamente(): void
    {
        $empresa = Empresa::factory()->create();
        $userEmpresa = User::factory()->create([
            'role_as' => 1,
            'empresa_id' => $empresa->id,
        ]);
        $userEmpresa->roles()->attach(Role::where('name', 'empresa')->first());

        $response = $this->actingAs($userEmpresa)->post(route('ordenes.store'), [
            'empresa_id' => $empresa->id,
            'evaluados' => [
                1 => [
                    'nombre' => 'Carlos',
                    'apellidos' => 'López',
                    'dpi' => '1111111111111',
                    'email' => 'carlos@empresa.com',
                    'tipo_servicio' => 'socioeconomico',
                    'tipo_formulario' => 'preempleo',
                ],
            ],
        ]);

        $response->assertStatus(302);
        $response->assertRedirectContains('/empresa/ordenes/');
        $this->assertDatabaseHas('ordenes', [
            'empresa_id' => $empresa->id,
            'tipo_creador' => 'empresa',
        ]);
    }

    public function test_empresa_sin_empresa_id_no_puede_crear_orden(): void
    {
        $userEmpresa = User::factory()->create([
            'role_as' => 1,
            'empresa_id' => null,
        ]);
        $userEmpresa->roles()->attach(Role::where('name', 'empresa')->first());

        $response = $this->actingAs($userEmpresa)->post(route('ordenes.store'), [
            'empresa_id' => '',
            'evaluados' => [
                1 => [
                    'nombre' => 'Test',
                    'apellidos' => 'Test',
                    'dpi' => '2222222222222',
                    'email' => 'test@test.com',
                    'tipo_servicio' => 'poligrafo',
                    'tipo_formulario' => 'preempleo',
                ],
            ],
        ]);

        // Should fail validation or throw error, but not a 500
        $this->assertTrue($response->isRedirection() || $response->isClientError());
        $this->assertDatabaseMissing('ordenes', ['empresa_id' => null]);
    }

    // ─── B3: Config currency no falla con formato inesperado ───

    public function test_config_currency_con_formato_correcto(): void
    {
        $admin = User::factory()->create(['role_as' => 3]);
        $admin->roles()->attach(Role::where('name', 'admin')->first());
        Config::create([
            'currency' => 'GTQ Quetzal',
            'currency_simbol' => 'Q',
            'email' => 'test@test.com',
        ]);

        $response = $this->actingAs($admin)->put(route('config.update'), [
            'currency' => 'USD Dólar',
            'impuesto' => 12,
            'email' => 'test@test.com',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('config');
        $this->assertDatabaseHas('configs', ['currency_simbol' => 'Dólar']);
    }

    public function test_config_currency_con_una_sola_palabra(): void
    {
        $admin = User::factory()->create(['role_as' => 3]);
        $admin->roles()->attach(Role::where('name', 'admin')->first());
        Config::create([
            'currency' => 'GTQ Quetzal',
            'currency_simbol' => 'Q',
            'email' => 'test@test.com',
        ]);

        // Una sola palabra no debe causar error
        $response = $this->actingAs($admin)->put(route('config.update'), [
            'currency' => 'Quetzal',
            'impuesto' => 12,
            'email' => 'test@test.com',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('configs', ['currency_simbol' => 'Quetzal']);
    }

    // ─── B4: Checkbox principal no está duplicado ───

    public function test_formulario_crear_usuario_no_tiene_principal_duplicado(): void
    {
        $admin = User::factory()->create(['role_as' => 3]);
        $admin->roles()->attach(Role::where('name', 'admin')->first());
        Empresa::factory()->create();

        $response = $this->actingAs($admin)->get(route('users.create'));
        $response->assertStatus(200);

        $content = $response->getContent();
        // Contar ocurrencias de name="principal" — debe ser exactamente 1
        $count = substr_count($content, 'name="principal"');
        $this->assertEquals(1, $count, "El campo name=\"principal\" aparece {$count} veces, debería ser 1");
    }

    // ─── B5: Copiar enlace tiene fallback ───

    public function test_vista_empresa_ordenes_tiene_funcion_copiar_con_fallback(): void
    {
        $empresa = Empresa::factory()->create();
        $userEmpresa = User::factory()->create([
            'role_as' => 1,
            'empresa_id' => $empresa->id,
        ]);
        $userEmpresa->roles()->attach(Role::where('name', 'empresa')->first());

        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $userEmpresa->id,
        ]);

        $response = $this->actingAs($userEmpresa)
                         ->get(route('empresa.ordenes.show', $orden));

        $response->assertStatus(200);
        // Verificar que existe la función fallback
        $response->assertSee('copiarFallback');
        $response->assertSee('isSecureContext');
    }
}
