<?php

namespace App\Support;

use App\Models\EvaluadoOrden;

/**
 * Sincroniza la 1ª hoja Word (resultado + observaciones) al informe preliminar del cliente.
 * No pisa ediciones hechas a mano en la ficha de la orden.
 */
class InformePreliminarDesdeWord
{
    /** @deprecated usar sincronizarDesdeWord */
    public static function copiarTablaSiPreliminarVacio(EvaluadoOrden $evaluado): void
    {
        self::sincronizarDesdeWord($evaluado);
    }

    public static function sincronizarDesdeWord(EvaluadoOrden $evaluado): void
    {
        $evaluado->refresh();
        if ($evaluado->informe_preliminar_editado_manual) {
            return;
        }

        $html = self::htmlTabla($evaluado);
        if ($html === null) {
            return;
        }

        $evaluado->update([
            'texto_informe_preliminar' => $html,
            'informe_preliminar_editado_manual' => false,
        ]);
    }

    public static function htmlTabla(EvaluadoOrden $evaluado): ?string
    {
        $opciones = InformeWordResultado::opcionesInforme($evaluado);
        $resultado = trim((string) ($evaluado->resultado ?? ''));
        $etiqueta = $opciones[$resultado] ?? '';

        $detalles = InformeWordResultado::detalles($evaluado->id);
        $notas = EvaluadorNotasSupport::mapaPorSeccion($evaluado->id);
        $observaciones = trim((string) ($notas[InformeWordBloquesEvaluador::NOTA_OBSERVACIONES] ?? ''));

        $obsPartes = [];
        if ($resultado === 'no_aprobado' && $detalles['mentira'] !== '') {
            $obsPartes[] = InformeWordResultado::ETIQUETA_MENTIRA.' '.$detalles['mentira'];
        }
        if ($resultado === 'aprobado_excepcion' && $detalles['excepcion'] !== '') {
            $obsPartes[] = InformeWordResultado::ETIQUETA_EXCEPCION.' '.$detalles['excepcion'];
        }
        if ($observaciones !== '') {
            $obsPartes[] = $observaciones;
        }

        if ($etiqueta === '' && $obsPartes === []) {
            return null;
        }

        $celdaResultado = e($etiqueta !== '' ? $etiqueta : '—');
        $celdaObs = $obsPartes === []
            ? '—'
            : implode('<br>', array_map(static fn (string $parte): string => e($parte), $obsPartes));

        $th = 'border: 1px solid #6c757d; padding: 6px 8px; min-width: 6rem; background-color: #e9ecef; font-weight: 600;';
        $td = 'border: 1px solid #6c757d; padding: 6px 8px; min-width: 6rem;';

        return '<table style="border-collapse: collapse; width: 100%;">'
            .'<thead><tr>'
            .'<th style="'.$th.'">Resultado:</th>'
            .'<th style="'.$th.'">Observaciones:</th>'
            .'</tr></thead>'
            .'<tbody><tr>'
            .'<td style="'.$td.'">'.$celdaResultado.'</td>'
            .'<td style="'.$td.'">'.$celdaObs.'</td>'
            .'</tr></tbody></table>';
    }
}
