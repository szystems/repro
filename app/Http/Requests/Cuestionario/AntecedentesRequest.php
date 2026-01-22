<?php

namespace App\Http\Requests\Cuestionario;

use Illuminate\Foundation\Http\FormRequest;

class AntecedentesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Referencias personales #1
            'referencia1_nombre' => 'required|string|max:100',
            'referencia1_telefono' => 'required|string|max:15',
            'referencia1_relacion' => 'required|string|max:50',
            
            // Referencias personales #2
            'referencia2_nombre' => 'required|string|max:100',
            'referencia2_telefono' => 'required|string|max:15',
            'referencia2_relacion' => 'required|string|max:50',
            
            // Antecedentes penales
            'antecedentes_penales' => 'required|in:si,no',
            'detalle_antecedentes' => 'nullable|string|max:2000',
            
            // Despido de trabajo
            'despedido_trabajo' => 'required|in:si,no',
            'motivo_despido' => 'nullable|string|max:1000',
            
            // Consumo de alcohol
            'consume_alcohol' => 'required|in:no,ocasionalmente,socialmente,frecuentemente',
            
            // Consumo de drogas
            'consume_drogas' => 'required|in:nunca,pasado,ocasionalmente,frecuentemente',
            
            // Salud mental
            'problemas_salud_mental' => 'required|in:si,no',
            'detalle_salud_mental' => 'nullable|string|max:1000',
            
            // Observaciones adicionales
            'observaciones_adicionales' => 'nullable|string|max:2000',
        ];
    }

    public function messages(): array
    {
        return [
            'referencia1_nombre.required' => 'El nombre de la referencia #1 es obligatorio.',
            'referencia1_telefono.required' => 'El teléfono de la referencia #1 es obligatorio.',
            'referencia1_relacion.required' => 'La relación con la referencia #1 es obligatoria.',
            'referencia2_nombre.required' => 'El nombre de la referencia #2 es obligatorio.',
            'referencia2_telefono.required' => 'El teléfono de la referencia #2 es obligatorio.',
            'referencia2_relacion.required' => 'La relación con la referencia #2 es obligatoria.',
            'antecedentes_penales.required' => 'Debe indicar si tiene antecedentes penales.',
            'antecedentes_penales.in' => 'Seleccione una opción válida.',
            'despedido_trabajo.required' => 'Debe indicar si ha sido despedido.',
            'despedido_trabajo.in' => 'Seleccione una opción válida.',
            'consume_alcohol.required' => 'Debe indicar su consumo de alcohol.',
            'consume_alcohol.in' => 'Seleccione una opción válida.',
            'consume_drogas.required' => 'Debe indicar sobre consumo de sustancias.',
            'consume_drogas.in' => 'Seleccione una opción válida.',
            'problemas_salud_mental.required' => 'Debe indicar sobre tratamiento psicológico.',
            'problemas_salud_mental.in' => 'Seleccione una opción válida.',
        ];
    }
}
