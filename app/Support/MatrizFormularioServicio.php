<?php

namespace App\Support;

/**
 * E6.1 — Matriz servicio + tipo de formulario (análisis §2.2 / PDF cliente).
 *
 * En la orden el enum de evaluado es preempleo|periodica|especifica.
 * Socioeconómico se registra como preempleo en BD y el cuestionario
 * se resuelve a tipo_formulario=socioeconomico vía EvaluadoOrden::tipoFormularioCuestionario().
 */
class MatrizFormularioServicio
{
    /** @return list<string> */
    public static function servicios(): array
    {
        return ['poligrafo', 'vsa', 'socioeconomico'];
    }

    /** @return list<string> */
    public static function tiposFormularioOrden(): array
    {
        return ['preempleo', 'periodica', 'especifica'];
    }

    /**
     * Tipos de formulario permitidos en la UI de orden según servicio.
     *
     * @return list<string>
     */
    public static function tiposFormularioPermitidos(string $tipoServicio): array
    {
        if ($tipoServicio === 'socioeconomico') {
            return ['preempleo'];
        }

        return self::tiposFormularioOrden();
    }

    public static function combinacionValida(string $tipoServicio, string $tipoFormulario): bool
    {
        return in_array($tipoFormulario, self::tiposFormularioPermitidos($tipoServicio), true);
    }

    /**
     * Valor a persistir en evaluados_orden.tipo_formulario.
     */
    public static function tipoFormularioParaOrden(string $tipoServicio, ?string $tipoFormularioSeleccionado): string
    {
        if ($tipoServicio === 'socioeconomico') {
            return 'preempleo';
        }

        $seleccionado = $tipoFormularioSeleccionado ?: 'preempleo';

        return in_array($seleccionado, self::tiposFormularioOrden(), true)
            ? $seleccionado
            : 'preempleo';
    }

    /**
     * Tipo de cuestionario que ve el candidato.
     */
    public static function tipoFormularioCuestionario(string $tipoServicio, ?string $tipoFormularioOrden): string
    {
        if ($tipoServicio === 'socioeconomico') {
            return 'socioeconomico';
        }

        return self::tipoFormularioParaOrden($tipoServicio, $tipoFormularioOrden);
    }

    /** Modalidad sugerida por servicio (UI). */
    public static function modalidadSugerida(string $tipoServicio): string
    {
        return match ($tipoServicio) {
            'vsa' => 'virtual',
            default => 'presencial',
        };
    }

    /** Etiqueta corta para UI. */
    public static function etiquetaFormularioOrden(string $tipoFormulario): string
    {
        return match ($tipoFormulario) {
            'periodica' => 'Periódica',
            'especifica' => 'Específica',
            default => 'Pre-empleo',
        };
    }

    public static function etiquetaServicio(string $tipoServicio): string
    {
        return match ($tipoServicio) {
            'vsa' => 'VSA',
            'socioeconomico' => 'Socioeconómico',
            default => 'Polígrafo',
        };
    }

    /**
     * Filas de la matriz para documentación / tests.
     *
     * @return list<array{servicio: string, formulario_orden: string, cuestionario: string, secciones: int}>
     */
    public static function matriz(): array
    {
        return [
            ['servicio' => 'poligrafo', 'formulario_orden' => 'preempleo', 'cuestionario' => 'preempleo', 'secciones' => 5],
            ['servicio' => 'poligrafo', 'formulario_orden' => 'periodica', 'cuestionario' => 'periodica', 'secciones' => 5],
            ['servicio' => 'poligrafo', 'formulario_orden' => 'especifica', 'cuestionario' => 'especifica', 'secciones' => 5],
            ['servicio' => 'vsa', 'formulario_orden' => 'preempleo', 'cuestionario' => 'preempleo', 'secciones' => 5],
            ['servicio' => 'vsa', 'formulario_orden' => 'periodica', 'cuestionario' => 'periodica', 'secciones' => 5],
            ['servicio' => 'vsa', 'formulario_orden' => 'especifica', 'cuestionario' => 'especifica', 'secciones' => 5],
            ['servicio' => 'socioeconomico', 'formulario_orden' => 'preempleo', 'cuestionario' => 'socioeconomico', 'secciones' => 6],
        ];
    }
}
