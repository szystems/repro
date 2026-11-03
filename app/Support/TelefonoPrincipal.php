<?php

namespace App\Support;

use Closure;

/**
 * Teléfono con el que REPRO y la sede contactan al candidato al generar la orden.
 * El alternativo sigue siendo opcional.
 */
class TelefonoPrincipal
{
    /**
     * @return list<string|Closure>
     */
    public static function reglas(): array
    {
        return ['required', 'string', 'max:20', self::digitos(...)];
    }

    public static function mensajeRequerido(): string
    {
        return 'El teléfono principal del evaluado es obligatorio.';
    }

    private static function digitos(string $attribute, mixed $value, Closure $fail): void
    {
        $digitos = preg_replace('/\D/', '', (string) $value) ?? '';
        $cantidad = strlen($digitos);

        if ($cantidad < 8 || $cantidad > 15) {
            $fail('El teléfono principal debe tener entre 8 y 15 dígitos.');
        }
    }
}
