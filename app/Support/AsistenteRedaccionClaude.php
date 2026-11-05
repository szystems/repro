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
            'max_tokens' => 1600,
            'temperature' => 0.2,
            'system' => [[
                'type' => 'text',
                'text' => $sistema,
                'cache_control' => ['type' => 'ephemeral'],
            ]],
            'messages' => [
                ['role' => 'user', 'content' => $usuario],
            ],
        ]);

        if (! $respuesta->successful()) {
            $codigo = $respuesta->status();
            if ($codigo === 429) {
                throw new RuntimeException('Claude está ocupado. Espere un momento e intente de nuevo.');
            }
            if (in_array($codigo, [408, 504, 529], true)) {
                throw new RuntimeException('Claude tardó demasiado. Intente de nuevo.');
            }

            throw new RuntimeException('No se pudo redactar. Intente de nuevo.');
        }

        if ($respuesta->json('stop_reason') === 'max_tokens') {
            throw new RuntimeException('La redacción quedó incompleta. Intente de nuevo.');
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
