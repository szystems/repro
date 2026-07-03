<?php

namespace App\Support;

/**
 * E2.2 — Datos de padre y madre (condicional ¿vive?).
 */
class InformacionFamiliarPadres
{
    /** @var array<string, string> */
    public const CONVIVE_OPCIONES = [
        'padre' => 'Padre',
        'madre' => 'Madre',
        'pareja' => 'Pareja',
        'hijos' => 'Hijos',
        'hermanos' => 'Hermanos',
        'solo' => 'Solo/a',
    ];

    /** @return list<string> */
    public static function prefijosProgenitor(): array
    {
        return ['padre', 'madre'];
    }

    /** @return list<string> */
    public static function camposDetalleVivo(string $prefijo): array
    {
        return [
            $prefijo.'_edad',
            $prefijo.'_direccion',
            $prefijo.'_ocupacion',
            $prefijo.'_lugar_trabajo',
            $prefijo.'_telefono',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function reglasValidacion(): array
    {
        $reglas = [
            'convive_con' => 'nullable|string|max:200',
        ];

        foreach (self::prefijosProgenitor() as $prefijo) {
            $etiqueta = $prefijo === 'padre' ? 'padre' : 'madre';

            $reglas[$prefijo.'_nombre'] = 'required|string|max:100';
            $reglas[$prefijo.'_vive'] = 'required|in:si,no';
            $reglas[$prefijo.'_edad'] = 'nullable|required_if:'.$prefijo.'_vive,si|integer|min:1|max:120';
            $reglas[$prefijo.'_direccion'] = 'nullable|required_if:'.$prefijo.'_vive,si|string|max:500';
            $reglas[$prefijo.'_ocupacion'] = 'nullable|required_if:'.$prefijo.'_vive,si|string|max:100';
            $reglas[$prefijo.'_lugar_trabajo'] = 'nullable|string|max:150';
            $reglas[$prefijo.'_telefono'] = 'nullable|required_if:'.$prefijo.'_vive,si|string|max:15|regex:/^[0-9\-\+\(\)\s]+$/';
        }

        return $reglas;
    }

    /**
     * @return array<string, string>
     */
    public static function mensajesValidacion(): array
    {
        $mensajes = [];

        foreach (self::prefijosProgenitor() as $prefijo) {
            $nombre = $prefijo === 'padre' ? 'El nombre del padre' : 'El nombre de la madre';
            $mensajes[$prefijo.'_nombre.required'] = $nombre.' es obligatorio.';
            $mensajes[$prefijo.'_vive.required'] = 'Indique si su '.$prefijo.' vive.';
            $mensajes[$prefijo.'_edad.required_if'] = 'La edad del '.$prefijo.' es obligatoria si vive.';
            $mensajes[$prefijo.'_direccion.required_if'] = 'La dirección del '.$prefijo.' es obligatoria si vive.';
            $mensajes[$prefijo.'_ocupacion.required_if'] = 'La ocupación del '.$prefijo.' es obligatoria si vive.';
            $mensajes[$prefijo.'_telefono.required_if'] = 'El teléfono del '.$prefijo.' es obligatorio si vive.';
        }

        return $mensajes;
    }

    /**
     * @param  array<int, string>|string|null  $convive
     * @return list<string>
     */
    public static function normalizarConviveCon(array|string|null $convive): array
    {
        if (is_array($convive)) {
            return array_values(array_filter($convive));
        }

        if (is_string($convive) && $convive !== '') {
            return array_values(array_filter(array_map('trim', explode(',', $convive))));
        }

        return [];
    }

    public static function conviveConParaAlmacenar(array|string|null $convive): ?string
    {
        $valores = self::normalizarConviveCon($convive);

        return $valores === [] ? null : implode(',', $valores);
    }
}
