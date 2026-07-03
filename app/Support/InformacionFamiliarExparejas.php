<?php

namespace App\Support;

/** E2.6 — Exparejas (condicional). */
class InformacionFamiliarExparejas
{
    /** @var array<string, string> */
    public const TIPOS_RELACION = [
        'matrimonio' => 'Matrimonio',
        'union_libre' => 'Unión libre',
    ];

    public static function aplicaExparejas(?string $estadoCivil, ?string $tuvoRelacion): bool
    {
        if ($tuvoRelacion === 'si') {
            return true;
        }

        return in_array($estadoCivil, ['casado', 'union_libre', 'divorciado', 'viudo'], true);
    }

    /** @return array<string, mixed> */
    public static function reglasValidacion(): array
    {
        return [
            'tuvo_matrimonio_union_hijos' => 'required|in:si,no',
            'expareja_nombre' => 'nullable|required_if:tuvo_matrimonio_union_hijos,si|string|max:100',
            'expareja_tipo_relacion' => 'nullable|required_if:tuvo_matrimonio_union_hijos,si|in:'.implode(',', array_keys(self::TIPOS_RELACION)),
            'expareja_tiempo_relacion' => 'nullable|required_if:tuvo_matrimonio_union_hijos,si|string|max:100',
            'expareja_hijos_comun' => 'nullable|required_if:tuvo_matrimonio_union_hijos,si|in:si,no',
            'expareja_cantidad_hijos' => 'nullable|required_if:expareja_hijos_comun,si|integer|min:1|max:20',
            'expareja_problemas_legales' => 'nullable|required_if:tuvo_matrimonio_union_hijos,si|in:si,no',
            'expareja_detalle_problemas' => 'nullable|required_if:expareja_problemas_legales,si|string|max:1000',
            'expareja_apoyo_economico' => 'nullable|required_if:tuvo_matrimonio_union_hijos,si|in:si,no',
            'expareja_detalle_apoyo' => 'nullable|string|max:500',
        ];
    }

    /** @return array<string, string> */
    public static function mensajesValidacion(): array
    {
        return [
            'tuvo_matrimonio_union_hijos.required' => 'Indique si ha tenido matrimonio, unión libre o hijos en común.',
            'expareja_nombre.required_if' => 'El nombre de la expareja es obligatorio.',
            'expareja_tipo_relacion.required_if' => 'Seleccione el tipo de relación anterior.',
            'expareja_hijos_comun.required_if' => 'Indique si tiene hijos en común.',
            'expareja_problemas_legales.required_if' => 'Indique si hubo problemas legales.',
        ];
    }
}
