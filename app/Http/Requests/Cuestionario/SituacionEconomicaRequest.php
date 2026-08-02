<?php

namespace App\Http\Requests\Cuestionario;

use App\Http\Requests\Cuestionario\Concerns\EtiquetasValidacionCuestionario;
use App\Http\Requests\Cuestionario\Concerns\PreparaTablasDinamicasParaValidacion;
use App\Support\SituacionEconomicaCampos;
use App\Support\TablaDinamica;
use Illuminate\Foundation\Http\FormRequest;

class SituacionEconomicaRequest extends FormRequest
{
    use EtiquetasValidacionCuestionario;
    use PreparaTablasDinamicasParaValidacion;

    protected function numeroSeccionTablasDinamicas(): int
    {
        return 4;
    }

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->prepararTablasDinamicas();
    }

    public function rules(): array
    {
        $reglas = SituacionEconomicaCampos::reglasValidacion();

        if ($this->resolverTipoFormularioCuestionario() === 'socioeconomico') {
            $reglas['econ_patrimonio_aprox'] = 'required|string|max:500';
        }

        return array_merge(
            $reglas,
            TablaDinamica::reglasValidacion(4, $this->resolverTipoFormularioCuestionario())
        );
    }

    public function messages(): array
    {
        return array_merge(
            TablaDinamica::mensajesValidacion(),
            SituacionEconomicaCampos::mensajesValidacion(),
            [
                'econ_patrimonio_aprox.required' => 'Indique el monto aproximado de su patrimonio.',
            ]
        );
    }
}
