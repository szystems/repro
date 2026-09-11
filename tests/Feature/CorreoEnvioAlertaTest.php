<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\Role;
use App\Models\User;
use App\Support\CorreoEnvioSupport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use RuntimeException;
use Tests\Feature\Concerns\CreatesRolesAndPermissions;
use Tests\TestCase;

class CorreoEnvioAlertaTest extends TestCase
{
    use RefreshDatabase, CreatesRolesAndPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpRolesAndPermissions();
        Cache::flush();
        config([
            'mail.default' => 'smtp',
            'mail.alerta.activa' => true,
            'mail.alerta.limite_diario' => 100,
            'mail.alerta.aviso_desde' => 90,
        ]);
    }

    public function test_detecta_error_de_tope_resend(): void
    {
        $this->assertTrue(CorreoEnvioSupport::esErrorTope(
            new RuntimeException('429 Too Many Requests: You have reached your daily email sending limit.')
        ));
        $this->assertFalse(CorreoEnvioSupport::esErrorTope(
            new RuntimeException('Connection timed out')
        ));
    }

    public function test_fallo_de_tope_activa_aviso_de_corte(): void
    {
        CorreoEnvioSupport::registrarFallo(
            new RuntimeException('Too many requests — daily limit reached'),
            'test'
        );

        $alerta = CorreoEnvioSupport::alertaActiva();
        $this->assertNotNull($alerta);
        $this->assertSame(CorreoEnvioSupport::NIVEL_CORTE, $alerta['nivel']);
        $this->assertStringContainsString('límite diario', $alerta['mensaje']);
    }

    public function test_contador_avisa_al_acercarse_al_tope(): void
    {
        Cache::put(
            'correo.enviados.'.now()->timezone(config('app.timezone'))->toDateString(),
            89,
            now()->endOfDay()
        );

        CorreoEnvioSupport::registrarEnvio();

        $alerta = CorreoEnvioSupport::alertaActiva();
        $this->assertNotNull($alerta);
        $this->assertSame(CorreoEnvioSupport::NIVEL_AVISO, $alerta['nivel']);
        $this->assertSame(90, CorreoEnvioSupport::enviadosHoy());
    }

    public function test_contador_corta_al_llegar_al_limite(): void
    {
        Cache::put(
            'correo.enviados.'.now()->timezone(config('app.timezone'))->toDateString(),
            99,
            now()->endOfDay()
        );

        CorreoEnvioSupport::registrarEnvio();

        $alerta = CorreoEnvioSupport::alertaActiva();
        $this->assertNotNull($alerta);
        $this->assertSame(CorreoEnvioSupport::NIVEL_CORTE, $alerta['nivel']);
    }

    public function test_se_puede_apagar_el_aviso(): void
    {
        config(['mail.alerta.activa' => false]);
        CorreoEnvioSupport::registrarFallo(new RuntimeException('daily limit'), 'test');

        $this->assertNull(CorreoEnvioSupport::alertaActiva());
    }

    public function test_repro_ve_el_aviso_en_el_portal(): void
    {
        CorreoEnvioSupport::registrarFallo(
            new RuntimeException('You have reached your daily email sending limit'),
            'test'
        );

        $admin = User::factory()->create(['role_as' => 3, 'estado' => 1]);
        $admin->roles()->attach(Role::where('name', 'admin')->first());

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Los correos automáticos se detuvieron', false);
    }

    public function test_empresa_no_ve_el_aviso_de_infra(): void
    {
        CorreoEnvioSupport::registrarFallo(
            new RuntimeException('You have reached your daily email sending limit'),
            'test'
        );

        $empresa = Empresa::factory()->create();
        $cliente = User::factory()->create([
            'role_as' => 1,
            'estado' => 1,
            'principal' => 1,
            'empresa_id' => $empresa->id,
        ]);
        $cliente->roles()->attach(Role::where('name', 'empresa')->first());

        $this->actingAs($cliente)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Los correos automáticos se detuvieron');
    }
}
