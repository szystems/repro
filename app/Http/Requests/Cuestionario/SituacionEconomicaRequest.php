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
        return array_merge([
            'ingresos_principales' => 'required|numeric|min:0|max:999999.99',
            'ingresos_adicionales' => 'nullable|numeric|min:0|max:999999.99',
            'ingresos_familiares' => 'nullable|numeric|min:0|max:999999.99',
            'total_ingresos' => 'nullable|numeric|min:0',
            'gastos_vivienda' => 'required|numeric|min:0|max:99999.99',
            'gastos_alimentacion' => 'required|numeric|min:0|max:99999.99',
            'gastos_transporte' => 'required|numeric|min:0|max:99999.99',
            'gastos_educacion' => 'nullable|numeric|min:0|max:99999.99',
            'gastos_salud' => 'nullable|numeric|min:0|max:99999.99',
            'gastos_otros' => 'nullable|numeric|min:0|max:99999.99',
            'total_gastos' => 'nullable|numeric|min:0',
            'balance_mensual' => 'nullable|numeric',
            'tiene_deudas' => 'required|in:si,no',
            'detalle_deudas' => 'nullable|string|max:2000',
            'tiene_ahorros' => 'required|in:si,no',
            'observaciones_economicas' => 'nullable|string|max:2000',
        ], TablaDinamica::reglasValidacion(4, $this->resolverTipoFormularioCuestionario()), SituacionEconomicaCampos::reglasValidacion());
    }

    public function messages(): array
    {
        return array_merge([
            'ingresos_principales.required' => 'Los ingresos mensuales principales son obligatorios.',
            'gastos_vivienda.required' => 'El gasto en vivienda es obligatorio.',
            'gastos_alimentacion.required' => 'El gasto en alimentación es obligatorio.',
            'gastos_transporte.required' => 'El gasto en transporte es obligatorio.',
            'tiene_deudas.required' => 'Debe indicar si tiene deudas.',
            'tiene_ahorros.required' => 'Debe indicar si tiene ahorros.',
        ], TablaDinamica::mensajesValidacion(), SituacionEconomicaCampos::mensajesValidacion());
    }
}
