<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Llama a Claude. La llave sale de ANTHROPIC_API_KEY en el servidor.
 * No se guarda en el repositorio.
 */
class AsistenteRedaccionClaude implements AsistenteRedaccionMotor
{
    public function redactar(string $sistema, string $usuario): string
    {
        $llave = (string) config('services.anthropic.key');
        if ($llave === '') {
            throw new RuntimeException('Falta la llave de Claude en el servidor.');
        }

        $respuesta = Http::withHeaders([
            'x-api-key' => $llave,
            'anthropic-version' => '2023-06-01',
        ])->timeout(60)->post('https://api.anthropic.com/v1/messages', [
            'model' => (string) config('services.anthropic.model'),
            'max_tokens' => 1200,
            'system' => $sistema,
            'messages' => [
                ['role' => 'user', 'content' => $usuario],
            ],
        ]);

        if (! $respuesta->successful()) {
            throw new RuntimeException('Claude no respondió.');
        }

        $texto = '';
        foreach ($respuesta->json('content') ?? [] as $bloque) {
            if (is_array($bloque) && ($bloque['type'] ?? '') === 'text') {
                $texto .= (string) ($bloque['text'] ?? '');
            }
        }

        $texto = trim($texto);
        if ($texto === '') {
            throw new RuntimeException('Claude devolvió un texto vacío.');
        }

        return $texto;
    }
}
