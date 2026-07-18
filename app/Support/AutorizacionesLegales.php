<?php

namespace App\Support;

use App\Models\EvaluadoOrden;

class AutorizacionesLegales
{
    public static function clavePlantilla(EvaluadoOrden $evaluado): string
    {
        $servicio = $evaluado->tipo_servicio ?? 'poligrafo';
        $formulario = $evaluado->tipo_formulario ?? 'preempleo';

        if ($servicio === 'socioeconomico') {
            return 'socioeconomico_preempleo';
        }

        return $servicio . '_' . $formulario;
    }

    public static function requiereMotivoHecho(EvaluadoOrden $evaluado): bool
    {
        return in_array($evaluado->tipo_formulario, ['periodica', 'especifica'], true);
    }

    public static function motivoHechoCompleto(EvaluadoOrden $evaluado): bool
    {
        if (!self::requiereMotivoHecho($evaluado)) {
            return true;
        }

        return trim((string) $evaluado->motivo_hecho_evaluacion) !== '';
    }

    public static function requiereInfornet(EvaluadoOrden $evaluado): bool
    {
        return ($evaluado->tipo_formulario ?? '') === 'preempleo';
    }

    public static function variables(EvaluadoOrden $evaluado): array
    {
        $evaluado->loadMissing('orden.empresa');

        $motivo = trim((string) $evaluado->motivo_hecho_evaluacion);
        if ($motivo === '' && self::requiereMotivoHecho($evaluado)) {
            $motivo = '(pendiente de registro por REPRO)';
        }

        return [
            ':nombre_completo:' => trim($evaluado->nombre . ' ' . $evaluado->apellidos),
            ':dpi:' => $evaluado->dpi ?? '',
            ':empresa:' => $evaluado->orden?->empresa?->nombre ?? 'N/A',
            ':puesto:' => $evaluado->puesto_evaluar ?: 'No especificado',
            ':motivo_hecho:' => $motivo,
            ':tipo_evaluacion:' => self::etiquetaTipoEvaluacion($evaluado),
            ':fecha:' => now()->format('d/m/Y'),
            ':lugar:' => 'Guatemala',
        ];
    }

    public static function etiquetaTipoEvaluacion(EvaluadoOrden $evaluado): string
    {
        $servicio = match ($evaluado->tipo_servicio) {
            'vsa' => 'VSA',
            'socioeconomico' => 'Estudio Socioeconómico',
            default => 'Polígrafo',
        };

        $formulario = match ($evaluado->tipo_formulario) {
            'periodica' => 'Periódica',
            'especifica' => 'Específica',
            default => 'Pre-empleo',
        };

        if ($evaluado->tipo_servicio === 'socioeconomico') {
            return $servicio . ' — Pre-empleo';
        }

        return $servicio . ' — ' . $formulario;
    }

    public static function titulo(EvaluadoOrden $evaluado): string
    {
        $clave = self::clavePlantilla($evaluado);
        $plantilla = config("autorizaciones_legales.plantillas.{$clave}");

        return $plantilla['titulo'] ?? 'AUTORIZACIÓN PARA EVALUACIÓN';
    }

    public static function renderHtml(EvaluadoOrden $evaluado): string
    {
        $clave = self::clavePlantilla($evaluado);
        $plantilla = config("autorizaciones_legales.plantillas.{$clave}");

        if (!$plantilla) {
            return '<p>Plantilla de autorización no configurada.</p>';
        }

        $html = '<h5 class="text-center mb-3">' . e($plantilla['titulo']) . '</h5>' . ($plantilla['cuerpo'] ?? '');

        return self::aplicarVariables($html, self::variables($evaluado));
    }

    public static function renderInfornetHtml(EvaluadoOrden $evaluado): string
    {
        $html = config('autorizaciones_legales.infornet', '');

        return self::aplicarVariables($html, self::variables($evaluado));
    }

    public static function aplicarVariables(string $html, array $variables): string
    {
        return str_replace(array_keys($variables), array_map('e', $variables), $html);
    }
}
