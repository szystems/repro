<?php

namespace App\Support;

/**
 * Baja a oración normal el texto que viene entero en mayúsculas.
 * No inventa tildes. Una frase ya mezclada se queda igual.
 */
class TextoOracion
{
    /** @var list<string> */
    private const CLAVES = [
        'si', 'no', 'soltero', 'casado', 'union_libre', 'divorciado', 'viudo',
        'completo', 'incompleto', 'en_curso', 'ninguno', 'primaria', 'basico',
        'diversificado', 'universitario', 'tecnico', 'maestria', 'doctorado',
        'propia_pagada', 'propia_pagando', 'alquilada', 'prestada', 'familiar', 'otro',
    ];

    public static function aplicar(mixed $valor): mixed
    {
        if (is_array($valor)) {
            $salida = [];
            foreach ($valor as $clave => $item) {
                $salida[$clave] = self::aplicar($item);
            }

            return $salida;
        }

        if (! is_string($valor)) {
            return $valor;
        }

        $inicio = ltrim($valor);
        if ($inicio !== '' && ($inicio[0] === '[' || $inicio[0] === '{')) {
            return $valor;
        }

        $texto = trim($valor);
        if ($texto === '' || ! self::todoEnMayusculas($texto)) {
            return $valor;
        }

        $clave = str_replace(' ', '_', mb_strtolower($texto));
        if (in_array($clave, self::CLAVES, true)) {
            return $clave;
        }

        if (! str_contains($texto, ' ') && mb_strlen($texto) <= 5 && preg_match('/^\p{L}+$/u', $texto)) {
            return $valor;
        }

        $minusculas = mb_strtolower($texto);

        return mb_strtoupper(mb_substr($minusculas, 0, 1)).mb_substr($minusculas, 1);
    }

    /**
     * @param  list<array<string, mixed>>  $filas
     * @return list<array<string, mixed>>
     */
    public static function filas(array $filas): array
    {
        $salida = [];
        foreach ($filas as $fila) {
            if (! is_array($fila)) {
                $salida[] = $fila;
                continue;
            }
            $limpia = [];
            foreach ($fila as $clave => $valor) {
                $limpia[$clave] = self::aplicar($valor);
            }
            $salida[] = $limpia;
        }

        return $salida;
    }

    private static function todoEnMayusculas(string $texto): bool
    {
        if (! preg_match('/\p{L}/u', $texto)) {
            return false;
        }

        return mb_strtoupper($texto) === $texto && mb_strtolower($texto) !== $texto;
    }
}
