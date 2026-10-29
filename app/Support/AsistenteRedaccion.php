<?php

namespace App\Support;

use App\Models\Config;
use App\Models\EvaluadoOrden;

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

    public static function disponibleEn(string $seccion): bool
    {
        return self::activo() && AsistenteRedaccionSecciones::permite($seccion);
    }

    /**
     * @return array<string, mixed>
     */
    public static function paqueteSalud(EvaluadoOrden $evaluado): array
    {
        return AsistenteRedaccionSalud::paquete($evaluado);
    }
}
