<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\Role;
use App\Models\User;
use App\Support\CorreoEnvioSupport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
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
        $this->assertStringContainsString('límite', $alerta['mensaje']);
        $this->assertStringContainsString('18:00', $alerta['mensaje']);
        $this->assertStringNotContainsString('Van ', $alerta['mensaje']);
        $this->assertStringNotContainsString('mensual', $alerta['mensaje']);
    }

    public function test_el_aviso_de_corte_se_borra_al_reinicio_utc(): void
    {
        try {
            Carbon::setTestNow(Carbon::parse('2026-10-16 11:21:00', 'America/Guatemala'));

            CorreoEnvioSupport::registrarFallo(
                new RuntimeException('550 daily email sending quota'),
                'test'
            );
            $this->assertNotNull(CorreoEnvioSupport::alertaActiva());

            Carbon::setTestNow(Carbon::parse('2026-10-16 18:01:00', 'America/Guatemala'));

            $this->assertNull(CorreoEnvioSupport::alertaActiva());
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_cupo_mensual_no_promete_reanudar_a_las_18(): void
    {
        CorreoEnvioSupport::registrarFallo(
            new RuntimeException('550 You have reached your monthly email sending quota.'),
            'test'
        );

        $alerta = CorreoEnvioSupport::alertaActiva();
        $this->assertNotNull($alerta);
        $this->assertStringContainsString('cupo mensual', $alerta['mensaje']);
        $this->assertStringNotContainsString('18:00', $alerta['mensaje']);
    }

    public function test_un_envio_exitoso_quita_el_aviso_pegado(): void
    {
        CorreoEnvioSupport::registrarFallo(
            new RuntimeException('550 You have reached your monthly email sending quota.'),
            'test'
        );
        $this->assertNotNull(CorreoEnvioSupport::alertaActiva());

        CorreoEnvioSupport::registrarEnvio();

        $this->assertNull(CorreoEnvioSupport::alertaActiva());
    }

    public function test_contador_no_muestra_banner_solo_por_acercarse_al_tope(): void
    {
        Cache::put(
            'correo.enviados.'.now()->timezone('UTC')->toDateString(),
            89,
            now()->endOfDay()
        );

        CorreoEnvioSupport::registrarEnvio();

        $this->assertNull(CorreoEnvioSupport::alertaActiva());
        $this->assertSame(90, CorreoEnvioSupport::enviadosHoy());
    }

    public function test_fallo_puntual_no_deja_banner_global(): void
    {
        CorreoEnvioSupport::registrarFallo(
            new RuntimeException('Connection timed out'),
            'test'
        );

        $this->assertNull(CorreoEnvioSupport::alertaActiva());
    }

    public function test_contador_corta_al_llegar_al_limite(): void
    {
        Cache::put(
            'correo.enviados.'.now()->timezone('UTC')->toDateString(),
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
