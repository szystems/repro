<?php

namespace App\Http\Requests\Cuestionario;

use App\Http\Requests\Cuestionario\Concerns\EtiquetasValidacionCuestionario;
use App\Http\Requests\Cuestionario\Concerns\PreparaTablasDinamicasParaValidacion;
use App\Support\SocioeconomicoComplementariaCampos;
use Illuminate\Foundation\Http\FormRequest;

class SocioeconomicoComplementariaRequest extends FormRequest
{
    use EtiquetasValidacionCuestionario;
    use PreparaTablasDinamicasParaValidacion;

    protected function numeroSeccionTablasDinamicas(): int
    {
        return 6;
    }

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->prepararTablasDinamicas();

        if (! $this->has('presupuesto') || ! is_array($this->input('presupuesto')) || $this->input('presupuesto') === []) {
            $this->merge([
                'presupuesto' => SocioeconomicoComplementariaCampos::filasPresupuestoIniciales(),
            ]);
        }
    }

    public function rules(): array
    {
        return SocioeconomicoComplementariaCampos::reglasValidacion();
    }

    public function messages(): array
    {
        return SocioeconomicoComplementariaCampos::mensajesValidacion();
    }
}
