<?php

namespace App\Support;

use App\Models\Cuestionario;

/** Presentación de cuestionario en portal empresa (solo lectura, sin campos internos). */
class CuestionarioPresentacionEmpresa
{
    /**
     * @return array<string, mixed>
     */
    public static function respuestasSeccion(Cuestionario $cuestionario, int $numeroSeccion): array
    {
        $respuestas = $cuestionario->obtenerRespuestasSeccion($numeroSeccion);

        return CamposInternosPreempleo::filtrarRespuestasParaEmpresa(
            $respuestas,
            $cuestionario->tipo_formulario ?? 'preempleo'
        );
    }

    /**
     * @return array<string, list<array<string, mixed>>>
     */
    public static function tablasSeccion(Cuestionario $cuestionario, int $numeroSeccion): array
    {
        $tablas = $cuestionario->getTablasPorNumeroSeccion($numeroSeccion);

        if ($cuestionario->tipo_formulario === 'socioeconomico' && $numeroSeccion === 6) {
            unset($tablas['referencias_vecinales']);
        }

        return $tablas;
    }
}
