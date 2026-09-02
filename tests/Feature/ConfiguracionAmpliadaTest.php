<?php

namespace Tests\Feature;

use App\Models\Config;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConfiguracionAmpliadaTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role_as' => 3]);
    }

    // =========================================================
    // R3 — Configuración ampliada in-app
    // =========================================================

    public function test_r3_config_tiene_campos_nuevos(): void
    {
        $config = Config::create([
            'currency'            => 'GTQ Q',
            'nombre_empresa'      => 'REPRO Guatemala Test',
            'dias_vigencia_token' => 45,
        ]);

        $this->assertEquals('REPRO Guatemala Test', $config->nombre_empresa);
        $this->assertEquals(45, $config->dias_vigencia_token);
    }

    public function test_r3_actualizar_nombre_empresa_y_dias_vigencia(): void
    {
        $config = Config::create(['currency' => 'GTQ Q']);
        $admin  = $this->admin();

        $this->actingAs($admin)->put(route('config.update'), [
            'currency'            => 'GTQ Q',
            'nombre_empresa'      => 'Mi Empresa SA',
            'dias_vigencia_token' => 60,
        ])->assertRedirect();

        $config->refresh();
        $this->assertEquals('Mi Empresa SA', $config->nombre_empresa);
        $this->assertEquals(60, $config->dias_vigencia_token);
    }

    public function test_r3_dias_vigencia_token_debe_ser_entero_entre_30_y_365(): void
    {
        Config::create(['currency' => 'GTQ Q']);
        $admin = $this->admin();

        $this->actingAs($admin)->put(route('config.update'), [
            'currency'            => 'GTQ Q',
            'dias_vigencia_token' => 29,
        ])->assertSessionHasErrors('dias_vigencia_token');

        $this->actingAs($admin)->put(route('config.update'), [
            'currency'            => 'GTQ Q',
            'dias_vigencia_token' => 0,
        ])->assertSessionHasErrors('dias_vigencia_token');

        $this->actingAs($admin)->put(route('config.update'), [
            'currency'            => 'GTQ Q',
            'dias_vigencia_token' => 400,
        ])->assertSessionHasErrors('dias_vigencia_token');
    }

    public function test_r3_nombre_empresa_nullable(): void
    {
        $config = Config::create(['currency' => 'GTQ Q', 'nombre_empresa' => 'Original']);
        $admin  = $this->admin();

        $this->actingAs($admin)->put(route('config.update'), [
            'currency'       => 'GTQ Q',
            'nombre_empresa' => null,
        ])->assertRedirect();

        $config->refresh();
        $this->assertNull($config->nombre_empresa);
    }
}

