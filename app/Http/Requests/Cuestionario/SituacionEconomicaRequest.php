<?php

namespace App\Http\Requests\Cuestionario;

use Illuminate\Foundation\Http\FormRequest;

class SituacionEconomicaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Ingresos
            'ingresos_principales' => 'required|numeric|min:0|max:999999.99',
            'ingresos_adicionales' => 'nullable|numeric|min:0|max:999999.99',
            'ingresos_familiares' => 'nullable|numeric|min:0|max:999999.99',
            'total_ingresos' => 'nullable|numeric|min:0',
            
            // Gastos mensuales
            'gastos_vivienda' => 'required|numeric|min:0|max:99999.99',
            'gastos_alimentacion' => 'required|numeric|min:0|max:99999.99',
            'gastos_transporte' => 'required|numeric|min:0|max:99999.99',
            'gastos_educacion' => 'nullable|numeric|min:0|max:99999.99',
            'gastos_salud' => 'nullable|numeric|min:0|max:99999.99',
            'gastos_otros' => 'nullable|numeric|min:0|max:99999.99',
            'total_gastos' => 'nullable|numeric|min:0',
            'balance_mensual' => 'nullable|numeric',
            
            // Información financiera adicional
            'tiene_deudas' => 'required|in:si,no',
            'detalle_deudas' => 'nullable|string|max:2000',
            'tiene_ahorros' => 'required|in:si,no',
            
            // Observaciones
            'observaciones_economicas' => 'nullable|string|max:2000',
        ];
    }

    public function messages(): array
    {
        return [
            'ingresos_principales.required' => 'Los ingresos mensuales principales son obligatorios.',
            'ingresos_principales.numeric' => 'Los ingresos deben ser un número válido.',
            'ingresos_principales.min' => 'Los ingresos no pueden ser negativos.',
            'gastos_vivienda.required' => 'El gasto en vivienda es obligatorio.',
            'gastos_vivienda.numeric' => 'El gasto en vivienda debe ser un número válido.',
            'gastos_alimentacion.required' => 'El gasto en alimentación es obligatorio.',
            'gastos_alimentacion.numeric' => 'El gasto en alimentación debe ser un número válido.',
            'gastos_transporte.required' => 'El gasto en transporte es obligatorio.',
            'gastos_transporte.numeric' => 'El gasto en transporte debe ser un número válido.',
            'tiene_deudas.required' => 'Debe indicar si tiene deudas.',
            'tiene_deudas.in' => 'Seleccione una opción válida para deudas.',
            'tiene_ahorros.required' => 'Debe indicar si tiene ahorros.',
            'tiene_ahorros.in' => 'Seleccione una opción válida para ahorros.',
        ];
    }
}
