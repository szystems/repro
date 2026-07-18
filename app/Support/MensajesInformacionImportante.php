<?php

namespace App\Support;

/**
 * E6.2 — Textos "Información Importante" según tipo de cuestionario.
 */
class MensajesInformacionImportante
{
    /**
     * Párrafo único (alertas de sección final).
     */
    public static function parrafo(string $tipoFormulario): string
    {
        return match ($tipoFormulario) {
            'periodica', 'especifica' => 'Toda la información es confidencial y será utilizada únicamente para fines de evaluación. Solo se requiere adjuntar su DPI en la pantalla final si aún no lo ha hecho.',
            'socioeconomico' => 'Toda la información es confidencial y será utilizada únicamente para fines de evaluación. Si tiene documentos sugeridos (constancia laboral, recibo de luz), podrá subirlos en la pantalla final.',
            default => 'Toda la información es confidencial. Puede completar documentación pendiente (DPI, antecedentes, constancias) en la pantalla final dentro de los 30 días del enlace.',
        };
    }

    /**
     * Viñetas de la pantalla de completado.
     *
     * @return list<string> HTML-safe plain text lines (sin tags)
     */
    public static function viñetasCompletado(string $tipoFormulario): array
    {
        $base = [
            'Confidencialidad: Su información será tratada de manera confidencial.',
            'Proceso: REPRO revisará sus respuestas como parte de la evaluación.',
            'Contacto: Si hay alguna consulta, se comunicarán con usted.',
            'Resultado: Los resultados serán comunicados por la empresa solicitante.',
        ];

        $extra = match ($tipoFormulario) {
            'periodica', 'especifica' => 'Documentación: Solo se solicita el DPI (si aún no lo adjuntó). No se requiere papelería adicional.',
            'socioeconomico' => 'Documentación: Puede adjuntar constancia laboral y recibo de luz si los tiene disponibles (opcionales).',
            default => 'Documentación: Puede completar papelería pendiente (DPI, antecedentes, constancias) con el mismo enlace durante 30 días.',
        };

        $base[] = $extra;

        return $base;
    }
}
