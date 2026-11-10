<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Llama a GPT. La llave sale de OPENAI_API_KEY en el servidor.
 * No se guarda en el repositorio.
 */
class AsistenteRedaccionOpenAi implements AsistenteRedaccionMotor
{
    public function redactar(string $sistema, string $usuario): string
    {
        $llave = (string) config('services.openai.key');
        if ($llave === '') {
            throw new RuntimeException('Falta la llave de GPT en el servidor.');
        }

        $respuesta = Http::withToken($llave)->timeout(60)->post('https://api.openai.com/v1/chat/completions', [
            'model' => (string) config('services.openai.model'),
            'max_completion_tokens' => 1600,
            'temperature' => 0.2,
            'reasoning_effort' => 'none',
            'messages' => [
                ['role' => 'system', 'content' => $sistema],
                ['role' => 'user', 'content' => $usuario],
            ],
        ]);

        if (! $respuesta->successful()) {
            $codigo = $respuesta->status();
            $error = (string) $respuesta->json('error.code');
            if ($codigo === 429 && $error !== 'insufficient_quota') {
                throw new RuntimeException('GPT está ocupado. Espere un momento e intente de nuevo.');
            }
            if ($error === 'insufficient_quota' || $codigo === 402) {
                throw new RuntimeException('GPT no tiene crédito. Avise al administrador.');
            }
            if (in_array($codigo, [401, 403], true)) {
                throw new RuntimeException('La llave de GPT no fue aceptada. Avise al administrador.');
            }
            if (in_array($codigo, [408, 504], true)) {
                throw new RuntimeException('GPT tardó demasiado. Intente de nuevo.');
            }

            throw new RuntimeException('No se pudo redactar. Intente de nuevo.');
        }

        if ($respuesta->json('choices.0.finish_reason') === 'length') {
            throw new RuntimeException('La redacción quedó incompleta. Intente de nuevo.');
        }

        $texto = trim($this->texto($respuesta->json('choices.0.message.content')));
        if ($texto === '') {
            throw new RuntimeException('GPT devolvió un texto vacío.');
        }

        return $texto;
    }

    private function texto(mixed $contenido): string
    {
        if (is_string($contenido)) {
            return $contenido;
        }
        if (! is_array($contenido)) {
            return '';
        }

        $texto = '';
        foreach ($contenido as $parte) {
            if (is_string($parte)) {
                $texto .= $parte;
            } elseif (is_array($parte) && ($parte['type'] ?? '') === 'text') {
                $texto .= (string) ($parte['text'] ?? '');
            }
        }

        return $texto;
    }
}
