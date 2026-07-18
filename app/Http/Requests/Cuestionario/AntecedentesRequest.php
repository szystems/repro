<?php

namespace App\Http\Requests\Cuestionario;

use App\Http\Requests\Cuestionario\Concerns\EtiquetasValidacionCuestionario;
use App\Http\Requests\Cuestionario\Concerns\PreparaTablasDinamicasParaValidacion;
use App\Support\AntecedentesJudiciales;
use App\Support\InformacionComplementaria;
use App\Support\SaludHabitosCampos;
use App\Support\TablaDinamica;
use Illuminate\Foundation\Http\FormRequest;

class AntecedentesRequest extends FormRequest
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

        $this->prepararTablasDinamicas();
    }

    public function rules(): array
    {
        return array_merge([
            'referencia1_nombre' => 'required|string|max:100',
            'referencia1_telefono' => 'required|string|max:15',
            'referencia1_relacion' => 'required|string|max:50',
            'referencia2_nombre' => 'required|string|max:100',
            'referencia2_telefono' => 'required|string|max:15',
            'referencia2_relacion' => 'required|string|max:50',
            'antecedentes_penales' => 'required|in:si,no',
            'detalle_antecedentes' => 'nullable|string|max:2000',
            'despedido_trabajo' => 'required|in:si,no',
            'motivo_despido' => 'nullable|string|max:1000',
            'consume_alcohol' => 'required|in:no,ocasionalmente,socialmente,frecuentemente',
            'consume_drogas' => 'required|in:nunca,pasado,ocasionalmente,frecuentemente',
            'problemas_salud_mental' => 'required|in:si,no',
            'detalle_salud_mental' => 'nullable|string|max:1000',
            'observaciones_adicionales' => 'nullable|string|max:2000',
            'informacion_adicional_final' => 'nullable|string|max:3000',
        ], TablaDinamica::reglasValidacion(5, $this->resolverTipoFormularioCuestionario()), SaludHabitosCampos::reglasValidacion(), AntecedentesJudiciales::reglasValidacion(), InformacionComplementaria::reglasValidacion());
    }

    public function messages(): array
    {
        return array_merge([
            'referencia1_nombre.required' => 'El nombre de la referencia #1 es obligatorio.',
            'referencia2_nombre.required' => 'El nombre de la referencia #2 es obligatorio.',
            'antecedentes_penales.required' => 'Debe indicar si tiene antecedentes penales.',
            'despedido_trabajo.required' => 'Debe indicar si ha sido despedido.',
        ], TablaDinamica::mensajesValidacion(), SaludHabitosCampos::mensajesValidacion());
    }
}
