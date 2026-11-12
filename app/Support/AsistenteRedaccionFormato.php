<?php

namespace App\Support;

/** Deja los párrafos pegados y, en recomendaciones, una viñeta por línea. */
class AsistenteRedaccionFormato
{
    public static function limpiar(string $texto, string $seccion): string
    {
        $texto = str_replace(["\r\n", "\r"], "\n", $texto);
        $lineas = [];
        foreach (preg_split("/\n/", $texto) ?: [] as $linea) {
            $linea = trim($linea);
            if ($linea === '') {
                continue;
            }
            if ($seccion === InformeWordBloquesEvaluador::NOTA_RECOMENDACIONES) {
                $linea = '• '.self::sinMarca($linea);
            } elseif (! in_array($seccion, ['preliminar', InformeWordBloquesEvaluador::NOTA_OBSERVACIONES], true)) {
                $linea = self::sinMarca($linea);
            }
            if ($linea !== '' && $linea !== '•') {
                $lineas[] = $linea;
            }
        }

        if ($seccion === InformeWordBloquesEvaluador::NOTA_RECOMENDACIONES
            && count($lineas) === 1
            && str_contains($lineas[0], 'factores de riesgo')) {
            $lineas[0] = self::sinMarca($lineas[0]);
        }

        return implode("\n", $lineas);
    }

    public static function textoDesdeHtml(string $html): string
    {
        $html = preg_replace('/<li\b[^>]*>/i', "\n• ", $html) ?? $html;
        $html = preg_replace('/<\/(p|div|h[1-6]|li|tr)>/i', "\n", $html) ?? $html;
        $html = preg_replace('/<br\s*\/?>/i', "\n", $html) ?? $html;
        $texto = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return self::limpiar($texto, 'preliminar');
    }

    public static function html(string $texto): string
    {
        $texto = self::limpiar($texto, 'preliminar');
        if ($texto === '') {
            return '';
        }

        $bloques = [];
        $lista = '';
        $tipo = '';
        $cerrar = static function () use (&$bloques, &$lista, &$tipo): void {
            if ($lista === '') {
                return;
            }
            $bloques[] = '<'.$tipo.'>'.$lista.'</'.$tipo.'>';
            $lista = '';
            $tipo = '';
        };

        foreach (explode("\n", $texto) as $linea) {
            if (preg_match('/^•\s+(.*)$/u', $linea, $coincidencia) === 1) {
                if ($tipo !== 'ul') {
                    $cerrar();
                    $tipo = 'ul';
                }
                $lista .= '<li>'.e($coincidencia[1]).'</li>';

                continue;
            }
            if (preg_match('/^\d+\.\s+(.*)$/', $linea, $coincidencia) === 1) {
                if ($tipo !== 'ol') {
                    $cerrar();
                    $tipo = 'ol';
                }
                $lista .= '<li>'.e($coincidencia[1]).'</li>';

                continue;
            }
            $cerrar();
            $bloques[] = '<p>'.e($linea).'</p>';
        }
        $cerrar();

        return implode('', $bloques);
    }

    private static function sinMarca(string $linea): string
    {
        $linea = preg_replace('/^(?:•|-|\*|–|—)\s+/u', '', $linea) ?? $linea;
        $linea = preg_replace('/^\d+[\.\)]\s+/', '', $linea) ?? $linea;

        return trim($linea);
    }
}
