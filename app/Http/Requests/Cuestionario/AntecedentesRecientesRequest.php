<?php

namespace App\Http\Requests\Cuestionario;

use App\Http\Requests\Cuestionario\Concerns\EtiquetasValidacionCuestionario;
use App\Http\Requests\Cuestionario\Concerns\PreparaTablasDinamicasParaValidacion;
use App\Support\AntecedentesJudiciales;
use App\Support\SaludHabitosCampos;
use App\Support\TablaDinamica;
use Illuminate\Foundation\Http\FormRequest;

class AntecedentesRecientesRequest extends FormRequest
{
    use EtiquetasValidacionCuestionario;
    use PreparaTablasDinamicasParaValidacion;

    protected function numeroSeccionTablasDinamicas(): int
    {
        return 5;
    }

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('sustancias_usadas') && is_string($this->input('sustancias_usadas'))) {
            $this->merge(['sustancias_usadas' => [$this->input('sustancias_usadas')]]);
        }

        if ($this->has('tiene_tatuajes') && ! $this->has('tiene_perforaciones')) {
            $this->merge(['tiene_perforaciones' => $this->input('tiene_tatuajes')]);
        }

        $this->prepararTablasDinamicas();
    }

    public function rules(): array
    {
        return array_merge(
            AntecedentesJudiciales::reglasValidacion(),
            SaludHabitosCampos::reglasValidacion(),
            [
                'informacion_adicional_final' => 'nullable|string|max:2000',
            ],
            TablaDinamica::reglasValidacion(5, $this->resolverTipoFormularioCuestionario())
        );
    }

    public function messages(): array
    {
        return array_merge(
            AntecedentesJudiciales::mensajesValidacion(),
            SaludHabitosCampos::mensajesValidacion(),
            TablaDinamica::mensajesValidacion()
        );
    }
}
