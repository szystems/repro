<?php

namespace App\Http\Requests\Cuestionario;

use App\Http\Requests\Cuestionario\Concerns\EtiquetasValidacionCuestionario;
use App\Support\InformacionFamiliarExparejas;
use App\Support\InformacionFamiliarPadres;
use App\Support\InformacionFamiliarPareja;
use App\Support\TablaDinamica;
use Illuminate\Foundation\Http\FormRequest;

class InformacionFamiliarRequest extends FormRequest
{
    use EtiquetasValidacionCuestionario;
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('convive_con')) {
            $this->merge([
                'convive_con' => InformacionFamiliarPadres::conviveConParaAlmacenar($this->input('convive_con')),
            ]);
        }
    }

    public function rules(): array
    {
        return array_merge([
            // Estado civil y pareja
            'estado_civil_detalle' => 'required|string|in:soltero,casado,union_libre,divorciado,viudo',
            'vive_con_pareja' => 'required|in:si,no',
            'tiene_hijos' => 'required|in:si,no',
            'tiene_hermanos' => 'required|in:si,no',
            'numero_hijos' => 'nullable|required_if:tiene_hijos,si|integer|min:1|max:20',
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
        ], InformacionFamiliarPadres::reglasValidacion(), InformacionFamiliarPareja::reglasValidacion(), InformacionFamiliarExparejas::reglasValidacion(), TablaDinamica::reglasValidacion(2, 'preempleo'));
    }

    public function messages(): array
    {
        return array_merge([
            'estado_civil_detalle.required' => 'El estado civil es obligatorio.',
            'estado_civil_detalle.in' => 'Seleccione un estado civil válido.',
            'vive_con_pareja.required' => 'Debe indicar si tiene pareja actual.',
            'vive_con_pareja.in' => 'Seleccione una opción válida.',
            'tiene_hijos.required' => 'Debe indicar si tiene hijos.',
            'tiene_hijos.in' => 'El campo tiene hijos debe ser verdadero o falso.',
            'tiene_hermanos.required' => 'Debe indicar si tiene hermanos.',
            'tiene_hermanos.in' => 'Seleccione una opción válida.',
            'numero_hijos.integer' => 'El número de hijos debe ser un número entero.',
            'numero_hijos.required_if' => 'Indique el número de hijos.',
            'numero_hijos.min' => 'Debe indicar al menos 1 hijo.',
            'personas_hogar.required' => 'Debe indicar cuántas personas viven en su hogar.',
            'personas_hogar.integer' => 'El número de personas debe ser un número entero.',
            'personas_hogar.min' => 'Debe haber al menos 1 persona en el hogar.',
            'dependientes_economicos.required' => 'Debe indicar el número de dependientes económicos.',
            'tipo_vivienda.required' => 'Debe seleccionar el tipo de vivienda.',
            'tipo_vivienda.in' => 'Seleccione un tipo de vivienda válido.',
            'personas_contribuyen_gastos.required' => 'Debe indicar cuántas personas contribuyen a los gastos.',
            'personas_contribuyen_gastos.min' => 'Al menos 1 persona debe contribuir a los gastos.',
        ], InformacionFamiliarPadres::mensajesValidacion(), InformacionFamiliarPareja::mensajesValidacion(), InformacionFamiliarExparejas::mensajesValidacion(), TablaDinamica::mensajesValidacion());
    }
}
