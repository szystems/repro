<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

/**
 * El autoguardado y el botón Guardar pueden llegar desordenados.
 * Una secuencia por usuario y cuestionario descarta el pedido más viejo.
 */
class CuestionarioGuardadoSecuencia
{
    public static function esObsoleta(int $cuestionarioId, int $userId, mixed $seq): bool
    {
        $incoming = self::normalizar($seq);
        if ($incoming === null) {
            return false;
        }

        return $incoming < (int) Cache::get(self::clave($cuestionarioId, $userId), 0);
    }

    public static function actual(int $cuestionarioId, int $userId): int
    {
        return (int) Cache::get(self::clave($cuestionarioId, $userId), 0);
    }

    public static function registrar(int $cuestionarioId, int $userId, mixed $seq): void
    {
        $incoming = self::normalizar($seq);
        if ($incoming === null) {
            return;
        }

        $clave = self::clave($cuestionarioId, $userId);
        $stored = (int) Cache::get($clave, 0);
        if ($incoming > $stored) {
            Cache::put($clave, $incoming, now()->addHours(12));
        }
    }

    public static function clave(int $cuestionarioId, int $userId): string
    {
        return 'cuestionario-save-seq:'.$cuestionarioId.':'.$userId;
    }

    private static function normalizar(mixed $seq): ?int
    {
        if ($seq === null || $seq === '' || ! is_numeric($seq)) {
            return null;
        }

        $incoming = (int) $seq;

        return $incoming >= 1 ? $incoming : null;
    }
}
