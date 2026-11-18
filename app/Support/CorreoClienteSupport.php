<?php

namespace App\Support;

use App\Models\Empresa;

/**
 * Correos del proceso de una empresa: enlace y recordatorio del candidato,
 * cita y resultados. No incluye la clave de acceso ni el aviso a la sede REPRO.
 */
class CorreoClienteSupport
{
    public static function permite(?Empresa $empresa): bool
    {
        if ($empresa === null) {
            return true;
        }

        return $empresa->recibeCorreosAutomaticos();
    }

    public static function permiteComentarios(?Empresa $empresa): bool
    {
        if ($empresa === null || ! self::permite($empresa)) {
            return false;
        }

        return $empresa->recibeCorreosComentarios();
    }
}
