<?php

namespace App\Support;

use App\Models\Cuestionario;
use App\Models\EvaluadoOrden;

/** Textos de presentación del cuestionario en el portal del candidato. */
class CuestionarioPresentacionCandidato
{
    public static function resolverTipo(?EvaluadoOrden $evaluado = null, ?Cuestionario $cuestionario = null): string
    {
        if ($cuestionario?->tipo_formulario) {
            return $cuestionario->tipo_formulario;
        }

        if ($evaluado) {
            return $evaluado->tipoFormularioCuestionario();
        }

        return 'preempleo';
    }

    public static function tituloNavbar(string $tipoFormulario): string
    {
        return match ($tipoFormulario) {
            'socioeconomico' => 'Cuestionario Socioeconómico',
            'periodica' => 'Cuestionario Periódico',
            'especifica' => 'Cuestionario Específico',
            default => 'Cuestionario de Pre-empleo',
        };
    }

    public static function tituloDocumento(string $tipoFormulario): string
    {
        return self::tituloNavbar($tipoFormulario).' - REPRO';
    }

    /** Etiqueta corta para frases («cuestionario …»). */
    public static function etiquetaTipo(string $tipoFormulario): string
    {
        return match ($tipoFormulario) {
            'socioeconomico' => 'socioeconómico',
            'periodica' => 'periódico',
            'especifica' => 'específico',
            default => 'de pre-empleo',
        };
    }

    /**
     * Textos de la pantalla pública de verificación de identidad según tipo de documento del evaluado.
     *
     * @return array{
     *     documento: string,
     *     instruccion_html: string,
     *     label: string,
     *     placeholder: string,
     *     ayuda: string,
     *     alert_js: string,
     *     required: string,
     *     size: string,
     *     regex: string,
     *     mismatch: string
     * }
     */
    public static function textosVerificacionIdentidad(?string $tipoDocumento): array
    {
        return match ($tipoDocumento ?? 'dpi') {
            'pasaporte' => [
                'documento' => 'pasaporte',
                'instruccion_html' => 'Por favor ingrese el <strong>número de pasaporte</strong> registrado en su solicitud.',
                'label' => 'Número de pasaporte',
                'placeholder' => '0000000000000',
                'ayuda' => 'Ingrese los 13 dígitos de su pasaporte sin espacios ni guiones',
                'alert_js' => 'Por favor ingrese un número de pasaporte válido de 13 dígitos.',
                'required' => 'Debe ingresar su número de pasaporte.',
                'size' => 'El pasaporte debe tener exactamente 13 dígitos.',
                'regex' => 'El pasaporte solo puede contener números.',
                'mismatch' => 'El número de pasaporte ingresado no coincide con nuestros registros.',
            ],
            'cedula' => [
                'documento' => 'cédula',
                'instruccion_html' => 'Por favor ingrese el <strong>número de cédula</strong> registrado en su solicitud.',
                'label' => 'Número de cédula',
                'placeholder' => '0000000000000',
                'ayuda' => 'Ingrese los 13 dígitos de su cédula sin espacios ni guiones',
                'alert_js' => 'Por favor ingrese un número de cédula válido de 13 dígitos.',
                'required' => 'Debe ingresar su número de cédula.',
                'size' => 'La cédula debe tener exactamente 13 dígitos.',
                'regex' => 'La cédula solo puede contener números.',
                'mismatch' => 'El número de cédula ingresado no coincide con nuestros registros.',
            ],
            default => [
                'documento' => 'DPI',
                'instruccion_html' => 'Por favor ingrese su <strong>Documento Personal de Identificación (DPI)</strong>.',
                'label' => 'Número de DPI',
                'placeholder' => '0000000000000',
                'ayuda' => 'Ingrese los 13 dígitos de su DPI sin espacios ni guiones',
                'alert_js' => 'Por favor ingrese un DPI válido de 13 dígitos.',
                'required' => 'Debe ingresar su DPI.',
                'size' => 'El DPI debe tener exactamente 13 dígitos.',
                'regex' => 'El DPI solo puede contener números.',
                'mismatch' => 'El DPI ingresado no coincide con nuestros registros.',
            ],
        };
    }
}
