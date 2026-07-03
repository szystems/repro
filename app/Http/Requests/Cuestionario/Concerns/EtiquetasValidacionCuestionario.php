<?php

namespace App\Http\Requests\Cuestionario\Concerns;

use App\Support\CuestionarioValidacionLabels;

trait EtiquetasValidacionCuestionario
{
    /** @return array<string, string> */
    public function attributes(): array
    {
        return CuestionarioValidacionLabels::atributos();
    }
}
