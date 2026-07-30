<?php

namespace App\Support;

use Carbon\Carbon;

/**
 * E2.1 — Datos generales Pre-empleo (sección 1).
 */
class DatosPersonalesCampos
{
    /** @var array<string, string> */
    public const TIPOS_IDENTIFICACION = [
        'dpi' => 'DPI',
        'pasaporte' => 'Pasaporte',
        'documento_extranjero' => 'Documento de identidad extranjero',
        'otro' => 'Otro',
    ];

    /** @var array<string, string> */
    public const LICENCIA_CONDUCIR = [
        'si' => 'Sí, vigente',
        'no' => 'No',
        'no_aplica' => 'No aplica',
    ];

    public static function calcularEdad(?string $fechaNacimiento): ?int
    {
        if ($fechaNacimiento === null || $fechaNacimiento === '') {
            return null;
        }

        try {
            return Carbon::parse($fechaNacimiento)->age;
        } catch (\Throwable) {
            return null;
        }
    }

    public static function etiquetaTipoIdentificacion(?string $tipo): string
    {
        return self::TIPOS_IDENTIFICACION[$tipo ?? ''] ?? (string) $tipo;
    }

    public static function etiquetaLicencia(?string $valor): string
    {
        return self::LICENCIA_CONDUCIR[$valor ?? ''] ?? (string) $valor;
    }
}
