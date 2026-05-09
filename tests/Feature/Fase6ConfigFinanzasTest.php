<?php

namespace Tests\Feature;

use App\Models\Config;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests para Fase 6: A8 (config subsecciones Identidad/Catálogos/Plantillas),
 * A8-fin (sección Finanzas próximamente).
 */
class Fase6ConfigFinanzasTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $repro;
    protected User $empresa;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'admin', 'display_name' => 'Administrador']);
        Role::create(['name' => 'empresa', 'display_name' => 'Empresa']);
        Role::create(['name' => 'repro', 'display_name' => 'Repro']);
        Role::create(['name' => 'evaluado', 'display_name' => 'Evaluado']);

        $this->admin  = User::factory()->create(['role_as' => 3]);
        $this->repro  = User::factory()->create(['role_as' => 2]);
        $this->empresa = User::factory()->create(['role_as' => 1]);

        Config::create([
            'logo'             => null,
            'email'            => 'info@repro.gt',
            'currency'         => 'GTQ Q',
            'currency_simbol'  => 'Q',
            'currency_iso'     => 'GTQ',
            'impuesto'         => 12.00,
            'descuento_maximo' => 0.00,
        ]);
    }

    // =========================================================
    // A8 — Config: subsecciones Identidad / Catálogos / Plantillas
    // =========================================================

    /** @test */
    public function a8_config_muestra_tabs_identidad_catalogos_plantillas(): void
    {
        $response = $this->actingAs($this->admin)->get(route('config.index'));

        $response->assertStatus(200);
        $response->assertSee('Identidad');
        $response->assertSee('Catálogos');
        $response->assertSee('Plantillas');
    }

    /** @test */
    public function a8_config_guarda_campos_identidad(): void
    {
        $response = $this->actingAs($this->admin)
            ->put(route('config.update'), [
                'currency'  => 'GTQ Q',
                'email'     => 'nuevo@repro.gt',
                'fb_link'   => 'https://facebook.com/repro',
                'inst_link' => null,
                'yt_link'   => null,
                'wapp_link' => 'https://wa.me/50299887766',
                'impuesto'  => 12.00,
            ]);

        $response->assertRedirect(url('config'));
        $this->assertDatabaseHas('configs', [
            'email'     => 'nuevo@repro.gt',
            'fb_link'   => 'https://facebook.com/repro',
            'wapp_link' => 'https://wa.me/50299887766',
        ]);
    }

    /** @test */
    public function a8_config_guarda_campos_catalogos(): void
    {
        $response = $this->actingAs($this->admin)
            ->put(route('config.update'), [
                'currency'         => 'USD $',
                'impuesto'         => 10.00,
                'descuento_maximo' => 15.00,
            ]);

        $response->assertRedirect(url('config'));
        $this->assertDatabaseHas('configs', [
            'currency'         => 'USD $',
            'impuesto'         => 10.00,
            'descuento_maximo' => 15.00,
        ]);
    }

    // =========================================================
    // A8-fin — Finanzas: sección "Próximamente"
    // =========================================================

    /** @test */
    public function a8fin_finanzas_muestra_pagina_proximamente_para_admin(): void
    {
        $response = $this->actingAs($this->admin)->get(route('finanzas.index'));

        $response->assertStatus(200);
        $response->assertSee('Finanzas');
        $response->assertSee('Próximamente');
    }

    /** @test */
    public function a8fin_finanzas_accesible_para_repro(): void
    {
        $response = $this->actingAs($this->repro)->get(route('finanzas.index'));

        $response->assertStatus(200);
    }

    /** @test */
    public function a8fin_finanzas_requiere_autenticacion(): void
    {
        $response = $this->get(route('finanzas.index'));

        $response->assertRedirect(route('login'));
    }
}
