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
        return CuestionarioPresentacionDashboard::respuestasSeccion($cuestionario, $numeroSeccion, true);
    }

    /**
     * @return array<string, list<array<string, mixed>>>
     */
    public static function tablasSeccion(Cuestionario $cuestionario, int $numeroSeccion): array
    {
        return CuestionarioPresentacionDashboard::tablasSeccion($cuestionario, $numeroSeccion, true);
    }
}
