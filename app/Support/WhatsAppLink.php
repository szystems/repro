<?php

namespace App\Support;

/** Arma el enlace wa.me igual que en Usuarios, aceptando 8 dígitos o +502. */
class WhatsAppLink
{
    public static function url(?string $numero): ?string
    {
        $digitos = preg_replace('/\D+/', '', (string) $numero) ?? '';
        if ($digitos === '') {
            return null;
        }

        if (! str_starts_with($digitos, '502') && strlen($digitos) <= 8) {
            $digitos = '502'.$digitos;
        }

        return 'https://wa.me/'.$digitos;
    }
}
