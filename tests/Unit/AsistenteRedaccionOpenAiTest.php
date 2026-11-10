<?php

namespace Tests\Unit;

use App\Support\AsistenteRedaccionOpenAi;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class AsistenteRedaccionOpenAiTest extends TestCase
{
    public function test_sin_llave_no_llama_a_gpt(): void
    {
        config(['services.openai.key' => '']);
        Http::preventStrayRequests();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Falta la llave de GPT');

        (new AsistenteRedaccionOpenAi)->redactar('sistema', 'usuario');
    }

    public function test_toma_el_parrafo_y_no_guarda_la_llave_en_el_cuerpo(): void
    {
        config([
            'services.openai.key' => 'llave-de-prueba',
            'services.openai.model' => 'gpt-6-luna',
        ]);
        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [[
                    'finish_reason' => 'stop',
                    'message' => ['content' => 'Refiere buen estado de salud.'],
                ]],
            ]),
        ]);

        $texto = (new AsistenteRedaccionOpenAi)->redactar('criterio', 'datos de salud');

        $this->assertSame('Refiere buen estado de salud.', $texto);
        Http::assertSent(function ($request): bool {
            $cuerpo = json_encode($request->data());

            return $request->url() === 'https://api.openai.com/v1/chat/completions'
                && $request->data()['model'] === 'gpt-6-luna'
                && $request->data()['temperature'] === 0.2
                && $request->data()['max_completion_tokens'] === 1600
                && $request->data()['reasoning_effort'] === 'none'
                && $request->hasHeader('Authorization', 'Bearer llave-de-prueba')
                && ! str_contains((string) $cuerpo, 'llave-de-prueba');
        });
    }

    public function test_si_gpt_esta_ocupado_lo_dice(): void
    {
        config([
            'services.openai.key' => 'llave-de-prueba',
            'services.openai.model' => 'gpt-6-luna',
        ]);
        Http::fake([
            'api.openai.com/*' => Http::response(['error' => ['code' => 'rate_limit_exceeded']], 429),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('GPT está ocupado.');

        (new AsistenteRedaccionOpenAi)->redactar('criterio', 'texto');
    }

    public function test_si_no_hay_credito_lo_dice(): void
    {
        config([
            'services.openai.key' => 'llave-de-prueba',
            'services.openai.model' => 'gpt-6-luna',
        ]);
        Http::fake([
            'api.openai.com/*' => Http::response(['error' => ['code' => 'insufficient_quota']], 429),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('GPT no tiene crédito.');

        (new AsistenteRedaccionOpenAi)->redactar('criterio', 'texto');
    }
}
