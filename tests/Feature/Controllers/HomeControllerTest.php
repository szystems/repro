<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_returns_successful_response()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_about_page_returns_successful_response()
    {
        $response = $this->get('/about');

        $response->assertStatus(200);
    }
}