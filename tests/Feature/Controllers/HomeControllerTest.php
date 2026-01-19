<?php

namespace Tests\Feature\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_redirects_when_not_authenticated()
    {
        $response = $this->get('/');

        // Sin autenticación, redirige al login
        $response->assertStatus(302);
    }

    public function test_login_page_is_accessible()
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }
}