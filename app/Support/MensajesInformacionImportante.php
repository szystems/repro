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
            default => 'Toda la información es confidencial y será utilizada únicamente para fines de evaluación. Si no cuenta con toda la papelería al completar el formulario, podrá usar el mismo enlace durante los próximos 30 días para adjuntar documentación pendiente (DPI, antecedentes, constancias).',
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
            'Confidencialidad: Toda la información y documentación proporcionada será tratada de forma confidencial y utilizada únicamente para fines relacionados con el proceso de evaluación solicitado por la empresa contratante.',
            'Proceso: Una vez enviado el formulario, REPRO revisará la información recibida y dará seguimiento al proceso correspondiente según el servicio solicitado para usted. Nuestro equipo se comunicará para coordinar los siguientes pasos.',
        ];

        if (! in_array($tipoFormulario, ['periodica', 'especifica'], true)) {
            $base[] = 'Papelería pendiente: Si no cuenta con toda la documentación al momento de completar el formulario, podrá utilizar este mismo enlace posteriormente para adjuntar la papelería restante y complementar su expediente.';
        }

        $base[] = 'Contacto: Si tiene alguna duda o necesita apoyo durante el proceso, no dude en comunicarse con nuestro equipo de atención.';
        $base[] = 'Resultados: Los resultados de la evaluación serán enviados únicamente a la empresa solicitante. REPRO no proporciona resultados ni información del proceso directamente a los candidatos evaluados.';

        if ($tipoFormulario === 'periodica' || $tipoFormulario === 'especifica') {
            $base[] = 'Documentación: Solo se solicita el DPI (si aún no lo adjuntó). No se requiere papelería adicional.';
        } elseif ($tipoFormulario === 'socioeconomico') {
            $base[] = 'Documentación: Puede adjuntar constancia laboral y recibo de luz si los tiene disponibles (opcionales).';
        }

        return $base;
    }
}
