<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     * La página principal redirige al login si no está autenticado
     *
     * @return void
     */
    public function test_homepage_redirects_to_login_when_not_authenticated()
    {
        $response = $this->get('/');

        // Sin autenticación, redirige al login
        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }
}
