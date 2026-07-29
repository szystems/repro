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
}
