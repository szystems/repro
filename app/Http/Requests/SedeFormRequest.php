<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SedeFormRequest extends FormRequest
{
    /** Solo usuarios REPRO (role_as >= 3) pueden gestionar sedes. */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role_as >= 3;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'nombre'      => [
                'required',
                'string',
                'max:191',
                Rule::unique('sedes')->ignore($this->route('id')),
            ],
            'direccion'   => ['nullable', 'string', 'max:500'],
            'telefono'    => ['nullable', 'string', 'max:30'],
            'whatsapp'    => ['nullable', 'string', 'max:30'],
            'enlace_maps' => ['nullable', 'url', 'max:500'],
            'capacidad'   => ['nullable', 'integer', 'min:1', 'max:999'],
            'estado'      => ['nullable', 'integer', 'in:0,1'],
            'notas'       => ['nullable', 'string'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre de la sede es obligatorio.',
            'nombre.max'      => 'El nombre no puede tener más de 191 caracteres.',
            'nombre.unique'   => 'Ya existe una sede registrada con este nombre.',
            'capacidad.min'   => 'La capacidad mínima es 1.',
        ];
    }
}
