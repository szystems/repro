<?php

namespace App\Support;

/**
 * Ruta de la ficha de orden según quién mira.
 *
 * El portal empresa vive en /empresa/ordenes/{id} (rol empresa + visibilidad
 * de la compañía). /ordenes/{id} exige el permiso ordenes.ver y, para un
 * trabajador sin esa casilla, responde 403 aunque la orden sea suya.
 */
class OrdenPortalSupport
{
    public static function ruta(object $user, string $accion): string
    {
        $empresa = (int) ($user->role_as ?? 0) === 1;

        return match ($accion) {
            'index' => $empresa ? 'empresa.ordenes.index' : 'ordenes.index',
            'show' => $empresa ? 'empresa.ordenes.show' : 'ordenes.show',
            default => throw new \InvalidArgumentException('Acción de orden no soportada: '.$accion),
        };
    }

    public static function urlDetalle(object $user, mixed $orden, string $ancla = ''): string
    {
        $url = route(self::ruta($user, 'show'), $orden);

        return $ancla === '' ? $url : $url.$ancla;
    }
}
