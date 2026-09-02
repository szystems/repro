<?php

namespace App\Support;

use App\Models\EvaluadoOrden;
use App\Models\Orden;

/** Nombre de archivo .docx según preferencia cliente (Sprint H7). */
class InformeWordNombresArchivo
{
    public static function generar(EvaluadoOrden $evaluado, Orden $orden, string $prefijo = ''): string
    {
        $nombre = self::slugPartes([$evaluado->nombre, $evaluado->apellidos]);
        $empresa = self::slugPartes([$orden->empresa?->nombre ?? '']);

        $base = $nombre !== '' ? $nombre : ('Evaluado_' . $evaluado->id);
        if ($empresa !== '') {
            $base .= '_' . $empresa;
        }

        if ($prefijo !== '') {
            $base = self::slugPartes([$prefijo]) . '_' . $base;
        }

        return $base . '.docx';
    }

    /** @param  list<mixed>  $partes */
    private static function slugPartes(array $partes): string
    {
        $texto = trim(implode(' ', array_filter(array_map(
            fn (mixed $parte): string => trim((string) $parte),
            $partes
        ))));

        if ($texto === '') {
            return '';
        }

        $texto = preg_replace('/\s+/', '_', $texto) ?? $texto;
        $texto = preg_replace('/[^\p{L}\p{N}_\-]+/u', '', $texto) ?? $texto;

        return trim($texto, '_');
    }
}
