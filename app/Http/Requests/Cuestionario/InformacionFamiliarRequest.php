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
            // Información del cónyuge/pareja (si aplica)
            'tiene_pareja' => 'required|boolean',
            'pareja_nombre' => 'required_if:tiene_pareja,true|nullable|string|max:255',
            'pareja_edad' => 'required_if:tiene_pareja,true|nullable|integer|between:18,100',
            'pareja_profesion' => 'required_if:tiene_pareja,true|nullable|string|max:255',
            'pareja_lugar_trabajo' => 'nullable|string|max:255',
            'pareja_telefono' => 'nullable|string|max:20',
            'años_relacion' => 'required_if:tiene_pareja,true|nullable|integer|between:0,80',
            
            // Información de los padres
            'padre_nombre' => 'required|string|max:255',
            'padre_edad' => 'nullable|integer|between:30,120',
            'padre_vive' => 'required|boolean',
            'padre_profesion' => 'nullable|string|max:255',
            'padre_telefono' => 'nullable|string|max:20',
            
            'madre_nombre' => 'required|string|max:255',
            'madre_edad' => 'nullable|integer|between:30,120',
            'madre_vive' => 'required|boolean',
            'madre_profesion' => 'nullable|string|max:255',
            'madre_telefono' => 'nullable|string|max:20',
            
            // Hermanos
            'numero_hermanos' => 'required|integer|between:0,20',
            'hermanos' => 'nullable|array',
            'hermanos.*.nombre' => 'required|string|max:255',
            'hermanos.*.edad' => 'required|integer|between:0,100',
            'hermanos.*.profesion' => 'nullable|string|max:255',
            'hermanos.*.telefono' => 'nullable|string|max:20',
            
            // Hijos (si los tiene)
            'tiene_hijos' => 'required|boolean',
            'numero_hijos' => 'required_if:tiene_hijos,true|nullable|integer|between:0,20',
            'hijos' => 'nullable|array',
            'hijos.*.nombre' => 'required|string|max:255',
            'hijos.*.edad' => 'required|integer|between:0,50',
            'hijos.*.vive_con_usted' => 'required|boolean',
            
            // Personas que viven en el hogar
            'personas_hogar' => 'required|integer|between:1,20',
            'otras_personas' => 'nullable|array',
            'otras_personas.*.nombre' => 'required|string|max:255',
            'otras_personas.*.parentesco' => 'required|string|max:100',
            'otras_personas.*.edad' => 'required|integer|between:0,120',
            
            // Referencias familiares
            'referencia_familiar_1_nombre' => 'required|string|max:255',
            'referencia_familiar_1_parentesco' => 'required|string|max:100',
            'referencia_familiar_1_telefono' => 'required|string|max:20',
            'referencia_familiar_1_direccion' => 'required|string|max:500',
            
            'referencia_familiar_2_nombre' => 'nullable|string|max:255',
            'referencia_familiar_2_parentesco' => 'nullable|string|max:100',
            'referencia_familiar_2_telefono' => 'nullable|string|max:20',
            'referencia_familiar_2_direccion' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'tiene_pareja.required' => 'Debe indicar si tiene pareja.',
            'pareja_nombre.required_if' => 'Si tiene pareja, el nombre es obligatorio.',
            'pareja_edad.required_if' => 'Si tiene pareja, la edad es obligatoria.',
            'padre_nombre.required' => 'El nombre del padre es obligatorio.',
            'madre_nombre.required' => 'El nombre de la madre es obligatorio.',
            'numero_hermanos.required' => 'Debe indicar el número de hermanos.',
            'personas_hogar.required' => 'Debe indicar cuántas personas viven en su hogar.',
            'referencia_familiar_1_nombre.required' => 'Debe proporcionar al menos una referencia familiar.',
        ];
    }
}
