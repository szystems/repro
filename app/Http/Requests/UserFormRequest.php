<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($this->route('id')),
            ],
            'name'=>'required|max:191',
            'fotografia' => 'mimes:jpg,jpeg,bmp,png,gif|max:3000|nullable',
            'fecha_nacimiento'=>'required|date',
            'telefono'=>'string|max:20|nullable',
            'celular'=>'string|max:20|nullable',
            'direccion'=>'string|max:500|nullable',
            'role_as'=>'integer|nullable',
            'cargo'=>'string|max:100|nullable',
        ];

        // Validar empresa_id solo si el role_as es 1 (empresa)
        if ($this->input('role_as') == 1) {
            $rules['empresa_id'] = 'required|integer|exists:empresas,id';
        } else {
            $rules['empresa_id'] = 'nullable|integer';
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'name.required' => 'El nombre es obligatorio',
            'email.required' => 'El correo electrónico es obligatorio',
            'email.email' => 'El formato del correo electrónico no es válido',
            'email.unique' => 'Este correo electrónico ya está registrado',
            'fecha_nacimiento.required' => 'La fecha de nacimiento es obligatoria',
            'fotografia.mimes' => 'El archivo debe ser una imagen (jpg, jpeg, png, bmp, gif)',
            'fotografia.max' => 'El tamaño máximo de la imagen es 3MB',
            'empresa_id.required' => 'Debe seleccionar una empresa para usuarios tipo empresa',
            'empresa_id.exists' => 'La empresa seleccionada no existe o está inactiva',
        ];
    }
}
