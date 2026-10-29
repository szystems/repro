<?php

namespace Tests\Unit;

use App\Support\AsistenteRedaccionClaude;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class AsistenteRedaccionClaudeTest extends TestCase
{
    public function test_sin_llave_no_llama_a_claude(): void
    {
        config(['services.anthropic.key' => '']);
        Http::preventStrayRequests();

        $this->expectException(RuntimeException::class);

        (new AsistenteRedaccionClaude)->redactar('sistema', 'usuario');
    }

    public function test_toma_el_parrafo_y_no_guarda_la_llave_en_el_cuerpo(): void
    {
        config([
            'services.anthropic.key' => 'llave-de-prueba',
            'services.anthropic.model' => 'claude-sonnet-4-5',
        ]);
        Http::fake([
            'api.anthropic.com/*' => Http::response([
                'content' => [
                    ['type' => 'text', 'text' => 'Refiere buen estado de salud.'],
                ],
            ]),
        ]);

        $texto = (new AsistenteRedaccionClaude)->redactar('criterio', 'datos de salud');

        $this->assertSame('Refiere buen estado de salud.', $texto);
        Http::assertSent(function ($request): bool {
            $cuerpo = json_encode($request->data());

            return $request->url() === 'https://api.anthropic.com/v1/messages'
                && $request->hasHeader('x-api-key', 'llave-de-prueba')
                && $request['system'] === 'criterio'
                && is_string($cuerpo)
                && ! str_contains($cuerpo, 'llave-de-prueba');
        });
    }
}
