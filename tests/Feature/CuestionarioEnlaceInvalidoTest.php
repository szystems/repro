<?php

namespace Tests\Feature;

use App\Models\Config;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CuestionarioEnlaceInvalidoTest extends TestCase
{
    use RefreshDatabase;

    public function test_token_invalido_muestra_vista_dedicada(): void
    {
        $response = $this->get('/cuestionario/token-que-no-existe');

        $response->assertNotFound();
        $response->assertSee('Enlace no válido');
        $response->assertSee('No pudimos encontrar un formulario');
        $response->assertDontSee('Página no encontrada');
    }

    public function test_token_expirado_muestra_vista_dedicada(): void
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'token_unico' => 'token-expirado-enlace-test',
            'token_expira_at' => now()->subHour(),
        ]);

        $response = $this->get("/cuestionario/{$evaluado->token_unico}");

        $response->assertNotFound();
        $response->assertSee('Enlace expirado');
        $response->assertSee('ya no está vigente');
    }

    public function test_dias_vigencia_cero_usa_minimo_un_dia(): void
    {
        Config::create(['currency' => 'GTQ Q', 'dias_vigencia_token' => 0]);

        $this->assertSame(30, Config::diasVigenciaTokenEnlace());
    }

    public function test_calcular_expiracion_token_respeta_configuracion(): void
    {
        Config::create(['currency' => 'GTQ Q', 'dias_vigencia_token' => 15]);

        $expira = EvaluadoOrden::calcularExpiracionToken();

        $this->assertTrue($expira->greaterThan(now()->addDays(14)));
        $this->assertTrue($expira->lessThanOrEqualTo(now()->addDays(15)->addMinute()));
    }

    public function test_get_url_cuestionario_usa_ruta_publica_correcta(): void
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'token_unico' => 'url-cuestionario-test-token',
        ]);

        $this->assertStringContainsString(
            '/cuestionario/url-cuestionario-test-token',
            $evaluado->getUrlCuestionario()
        );
    }
}
