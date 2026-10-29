<?php

namespace Tests\Feature;

use App\Models\Config;
use App\Models\Role;
use App\Models\User;
use App\Support\AsistenteRedaccion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AsistenteRedaccionConfigTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Administrador']);
    }

    public function test_el_asistente_nace_apagado(): void
    {
        $config = Config::create(['currency' => 'GTQ Q']);
        $config->refresh();

        $this->assertFalse($config->asistente_redaccion_activo);
        $this->assertFalse(AsistenteRedaccion::activo());
    }

    public function test_solo_al_encenderlo_queda_activo(): void
    {
        Config::create(['currency' => 'GTQ Q']);
        $admin = User::factory()->create(['role_as' => 3]);
        $admin->roles()->attach(Role::where('name', 'admin')->first());

        $this->actingAs($admin)->put(route('config.update'), [
            'currency' => 'GTQ Q',
            'asistente_redaccion_activo' => '1',
        ])->assertRedirect();

        $this->assertTrue(AsistenteRedaccion::activo());

        $this->actingAs($admin)->put(route('config.update'), [
            'currency' => 'GTQ Q',
        ])->assertRedirect();

        $this->assertFalse(AsistenteRedaccion::activo());
    }
}
