<?php

namespace App\Http\Requests\Cuestionario;

use Illuminate\Foundation\Http\FormRequest;

class InformacionFamiliarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Estado civil y pareja
            'estado_civil_detalle' => 'required|string|in:soltero,casado_civil,casado_religioso,union_libre,divorciado,separado,viudo',
            'vive_con_pareja' => 'required|in:si,no',
            'pareja_trabaja' => 'nullable|in:si,no',
            
            // Hijos
            'tiene_hijos' => 'required|in:si,no',
            'numero_hijos' => 'nullable|integer|min:0|max:20',
            'hijos_menores' => 'nullable|integer|min:0|max:20',
            'hijos_dependientes' => 'nullable|integer|min:0|max:20',
            
            // Hogar
            'personas_hogar' => 'required|integer|min:1|max:50',
            'dependientes_economicos' => 'required|integer|min:0|max:20',
            
            // Vivienda
            'tipo_vivienda' => 'required|string|in:propia_pagada,propia_pagando,alquilada,prestada,familiar,otro',
            'monto_hipoteca' => 'nullable|numeric|min:0',
            'anos_restantes_hipoteca' => 'nullable|integer|min:0|max:50',
            'monto_alquiler' => 'nullable|numeric|min:0',
            
            // Gastos del hogar
            'personas_contribuyen_gastos' => 'required|integer|min:1|max:20',
            
            // Observaciones
            'observaciones_familiares' => 'nullable|string|max:2000',
        ];
    }

    public function messages(): array
    {
        return [
            'estado_civil_detalle.required' => 'El estado civil es obligatorio.',
            'estado_civil_detalle.in' => 'Seleccione un estado civil válido.',
            'vive_con_pareja.required' => 'Debe indicar si vive con pareja.',
            'vive_con_pareja.in' => 'Seleccione una opción válida para vive con pareja.',
            'tiene_hijos.required' => 'Debe indicar si tiene hijos.',
            'tiene_hijos.in' => 'El campo tiene hijos debe ser verdadero o falso.',
            'numero_hijos.integer' => 'El número de hijos debe ser un número entero.',
            'numero_hijos.min' => 'El número de hijos no puede ser negativo.',
            'personas_hogar.required' => 'Debe indicar cuántas personas viven en su hogar.',
            'personas_hogar.integer' => 'El número de personas debe ser un número entero.',
            'personas_hogar.min' => 'Debe haber al menos 1 persona en el hogar.',
            'dependientes_economicos.required' => 'Debe indicar el número de dependientes económicos.',
            'tipo_vivienda.required' => 'Debe seleccionar el tipo de vivienda.',
            'tipo_vivienda.in' => 'Seleccione un tipo de vivienda válido.',
            'personas_contribuyen_gastos.required' => 'Debe indicar cuántas personas contribuyen a los gastos.',
            'personas_contribuyen_gastos.min' => 'Al menos 1 persona debe contribuir a los gastos.',
        ];
    }
}
