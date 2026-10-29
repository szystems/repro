<?php

namespace App\Support;

use App\Models\Config;

/**
 * Extra E. El interruptor nace apagado: el personal no ve el asistente
 * hasta que un administrador lo enciende en Configuración.
 */
class AsistenteRedaccion
{
    public const SECCION_PILOTO = 'word_salud';

    public static function activo(): bool
    {
        $config = Config::query()->first();

        return $config ? (bool) $config->asistente_redaccion_activo : false;
    }
}
