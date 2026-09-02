<?php

namespace App\Support;

use App\Models\Cuestionario;

/** E2.7 — Compila tablas resumen familiar desde sección 2. */
class ResumenFamiliar
{
    /**
     * @return array<string, mixed>
     */
    public static function compilar(Cuestionario $cuestionario): array
    {
        $respuestas = $cuestionario->obtenerRespuestasSeccion(2);
        $tablas = $cuestionario->getTablasPorNumeroSeccion(2);

        return [
            'convive_con' => self::etiquetasConvive($respuestas['convive_con'] ?? null),
            'padre' => self::resumenProgenitor($respuestas, 'padre'),
            'madre' => self::resumenProgenitor($respuestas, 'madre'),
            'pareja' => self::resumenPareja($respuestas),
            'hijos' => $tablas['hijos'] ?? [],
            'hermanos' => $tablas['hermanos'] ?? [],
            'expareja' => self::resumenExpareja($respuestas),
        ];
    }

    /** @return list<string> */
    private static function etiquetasConvive(?string $convive): array
    {
        $claves = InformacionFamiliarPadres::normalizarConviveCon($convive);

        return array_map(
            fn ($k) => InformacionFamiliarPadres::CONVIVE_OPCIONES[$k] ?? $k,
            $claves
        );
    }

    /** @return array<string, mixed> */
    private static function resumenProgenitor(array $r, string $prefijo): array
    {
        return [
            'nombre' => $r[$prefijo.'_nombre'] ?? null,
            'vive' => $r[$prefijo.'_vive'] ?? null,
            'edad' => $r[$prefijo.'_edad'] ?? null,
            'direccion' => $r[$prefijo.'_direccion'] ?? null,
            'ocupacion' => $r[$prefijo.'_ocupacion'] ?? null,
            'telefono' => $r[$prefijo.'_telefono'] ?? null,
        ];
    }

    /** @return array<string, mixed> */
    private static function resumenPareja(array $r): array
    {
        $tienePareja = ($r['vive_con_pareja'] ?? '') === 'si'
            || trim((string) ($r['pareja_nombre'] ?? '')) !== '';

        if (! $tienePareja) {
            return ['tiene' => false];
        }

        return [
            'tiene' => true,
            'tipo' => InformacionFamiliarPareja::etiquetaTipo($r['pareja_tipo_relacion'] ?? null),
            'nombre' => $r['pareja_nombre'] ?? null,
            'edad' => $r['pareja_edad'] ?? null,
            'telefono' => $r['pareja_telefono'] ?? null,
            'direccion' => $r['pareja_direccion'] ?? null,
            'ocupacion' => $r['pareja_ocupacion'] ?? null,
            'tiempo_relacion' => $r['pareja_tiempo_relacion'] ?? null,
            'calidad_relacion' => InformacionFamiliarPareja::etiquetaCalidad($r['pareja_calidad_relacion'] ?? null),
        ];
    }

    /** @return array<string, mixed> */
    private static function resumenExpareja(array $r): array
    {
        $tieneDatos = ($r['tuvo_matrimonio_union_hijos'] ?? '') === 'si'
            || trim((string) ($r['expareja_nombre'] ?? '')) !== '';

        if (! $tieneDatos) {
            return ['aplica' => false];
        }

        return [
            'aplica' => true,
            'nombre' => $r['expareja_nombre'] ?? null,
            'tipo' => InformacionFamiliarExparejas::TIPOS_RELACION[$r['expareja_tipo_relacion'] ?? ''] ?? null,
            'tiempo_relacion' => $r['expareja_tiempo_relacion'] ?? null,
            'hijos_comun' => $r['expareja_hijos_comun'] ?? null,
            'cantidad_hijos' => $r['expareja_cantidad_hijos'] ?? null,
            'problemas_legales' => $r['expareja_problemas_legales'] ?? null,
        ];
    }
}
