<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmpresaFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Solo usuarios con rol administrador o de REPRO pueden gestionar empresas
        return auth()->user()->role_as >= 2;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'nombre' => [
                'required',
                'string',
                'max:191',
                Rule::unique('empresas')->ignore($this->route('id')),
            ],
            'nit' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:500',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:191',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'estado' => 'nullable|integer|in:0,1',
            'descripcion' => 'nullable|string|max:1000',
            'sitio_web' => 'nullable|string|url|max:191',
            'contacto_nombre' => 'nullable|string|max:100',
            'contacto_cargo' => 'nullable|string|max:100',
            'contacto_telefono' => 'nullable|string|max:20',
            'contacto_email' => 'nullable|email|max:191',
            'notas' => 'nullable|string',
            'requerimientos_repro' => 'nullable|string|max:5000',
            'preguntas_preempleo' => 'nullable|array|max:5',
            'preguntas_preempleo.*' => 'nullable|string|max:500',
            'preguntas_puesto_nombre' => 'nullable|string|max:100',
            'preguntas_preempleo_puesto' => 'nullable|array|max:5',
            'preguntas_preempleo_puesto.*' => 'nullable|string|max:500',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (! $this->exists('preguntas_preempleo') && ! $this->exists('preguntas_puesto_nombre')) {
                return;
            }

            $nombre = trim((string) $this->input('preguntas_puesto_nombre'));
            $hayPuesto = collect($this->input('preguntas_preempleo_puesto', []))
                ->contains(fn ($texto) => trim((string) $texto) !== '');

            if ($nombre !== '' && ! $hayPuesto) {
                $validator->errors()->add('preguntas_puesto_nombre', 'Escriba al menos una pregunta del puesto.');
            }

            if ($nombre === '' && $hayPuesto) {
                $validator->errors()->add('preguntas_puesto_nombre', 'Escriba el nombre del puesto de este segundo juego.');
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'nombre.required' => 'El nombre de la empresa es obligatorio',
            'nombre.max' => 'El nombre de la empresa no puede tener más de 191 caracteres',
            'nombre.unique' => 'Ya existe una empresa registrada con este nombre',
            'nit.max' => 'El NIT no puede tener más de 20 caracteres',
            'direccion.max' => 'La dirección no puede tener más de 500 caracteres',
            'telefono.max' => 'El teléfono no puede tener más de 20 caracteres',
            'email.email' => 'El formato del correo electrónico no es válido',
            'logo.image' => 'El archivo debe ser una imagen',
            'logo.mimes' => 'El logo debe ser un archivo de tipo: jpeg, png, jpg, gif',
            'logo.max' => 'El tamaño máximo del logo es 2MB',
            'sitio_web.url' => 'El formato del sitio web no es válido',
        ];
    }
}
