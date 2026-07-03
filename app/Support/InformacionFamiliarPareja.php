<?php

namespace App\Support;

/**
 * E2.3 — Pareja actual (condicional por tipo de relación).
 */
class InformacionFamiliarPareja
{
    /** @var array<string, string> */
    public const TIPOS_RELACION = [
        'casado' => 'Casado/a',
        'union_libre' => 'Unión libre',
        'noviazgo' => 'Noviazgo',
        'convivencia' => 'Convivencia',
    ];

    /** @var array<string, string> */
    public const CALIDAD_RELACION = [
        'excelente' => 'Excelente',
        'buena' => 'Buena',
        'regular' => 'Regular',
        'mala' => 'Mala',
    ];

    public static function tienePareja(?string $viveConPareja): bool
    {
        return $viveConPareja === 'si';
    }

    /**
     * @return array<string, mixed>
     */
    public static function reglasValidacion(): array
    {
        return [
            'pareja_tipo_relacion' => 'nullable|required_if:vive_con_pareja,si|in:'.implode(',', array_keys(self::TIPOS_RELACION)),
            'pareja_nombre' => 'nullable|required_if:vive_con_pareja,si|string|max:100',
            'pareja_edad' => 'nullable|required_if:vive_con_pareja,si|integer|min:16|max:120',
            'pareja_telefono' => 'nullable|required_if:vive_con_pareja,si|string|max:15|regex:/^[0-9\-\+\(\)\s]+$/',
            'pareja_direccion' => 'nullable|required_if:vive_con_pareja,si|string|max:500',
            'pareja_ocupacion' => 'nullable|required_if:vive_con_pareja,si|string|max:100',
            'pareja_lugar_trabajo' => 'nullable|string|max:150',
            'pareja_tiempo_relacion' => 'nullable|required_if:vive_con_pareja,si|string|max:100',
            'pareja_calidad_relacion' => 'nullable|required_if:vive_con_pareja,si|in:'.implode(',', array_keys(self::CALIDAD_RELACION)),
            'pareja_trabaja' => 'nullable|required_if:vive_con_pareja,si|in:si,no',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function mensajesValidacion(): array
    {
        return [
            'pareja_tipo_relacion.required_if' => 'Seleccione el tipo de relación con su pareja.',
            'pareja_nombre.required_if' => 'El nombre de la pareja es obligatorio.',
            'pareja_edad.required_if' => 'La edad de la pareja es obligatoria.',
            'pareja_telefono.required_if' => 'El teléfono de la pareja es obligatorio.',
            'pareja_direccion.required_if' => 'La dirección de la pareja es obligatoria.',
            'pareja_ocupacion.required_if' => 'La ocupación de la pareja es obligatoria.',
            'pareja_tiempo_relacion.required_if' => 'Indique el tiempo de relación.',
            'pareja_calidad_relacion.required_if' => 'Indique la calidad de la relación.',
            'pareja_trabaja.required_if' => 'Indique si su pareja trabaja.',
        ];
    }

    public static function etiquetaTipo(?string $tipo): string
    {
        return self::TIPOS_RELACION[$tipo ?? ''] ?? (string) $tipo;
    }

    public static function etiquetaCalidad(?string $calidad): string
    {
        return self::CALIDAD_RELACION[$calidad ?? ''] ?? (string) $calidad;
    }
}
