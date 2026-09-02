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

    /**
     * Valor listo para mostrar en el formulario (dd/mm/aaaa).
     * Acepta ISO (aaaa-mm-dd), dd/mm/aaaa o solo dígitos ddmmaaaa.
     */
    public static function formatoFormulario(mixed $valor): string
    {
        if (! is_string($valor)) {
            return '';
        }

        $valor = trim($valor);

        if ($valor === '') {
            return '';
        }

        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $valor, $partes)) {
            return sprintf('%02d/%02d/%04d', (int) $partes[3], (int) $partes[2], (int) $partes[1]);
        }

        if (preg_match('/^(\d{1,2})[\/\-.](\d{1,2})[\/\-.](\d{4})$/', $valor, $partes)) {
            return sprintf('%02d/%02d/%04d', (int) $partes[1], (int) $partes[2], (int) $partes[3]);
        }

        if (preg_match('/^(\d{2})(\d{2})(\d{4})$/', $valor, $partes)) {
            return sprintf('%02d/%02d/%04d', (int) $partes[1], (int) $partes[2], (int) $partes[3]);
        }

        return $valor;
    }

    /**
     * Convierte a «aaaa-mm-dd» lo que el candidato pudo enviar: el formato nativo,
     * «dd/mm/aaaa» de versiones anteriores del formulario, o solo dígitos «ddmmaaaa»
     * (el teclado numérico de iOS no ofrece la diagonal).
     */
    public static function normalizarFechaNacimiento(mixed $valor): string
    {
        if (! is_string($valor)) {
            return '';
        }

        $valor = trim($valor);

        if ($valor === '' || preg_match('/^\d{4}-\d{2}-\d{2}$/', $valor)) {
            return $valor;
        }

        $patrones = [
            '/^(\d{1,2})[\/\-.](\d{1,2})[\/\-.](\d{4})$/',
            '/^(\d{2})(\d{2})(\d{4})$/',
        ];

        foreach ($patrones as $patron) {
            if (! preg_match($patron, $valor, $partes)) {
                continue;
            }

            $dia = (int) $partes[1];
            $mes = (int) $partes[2];
            $anio = (int) $partes[3];

            // Una fecha imposible se deja tal cual para que la validación la reporte.
            if (checkdate($mes, $dia, $anio)) {
                return sprintf('%04d-%02d-%02d', $anio, $mes, $dia);
            }
        }

        return $valor;
    }

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
