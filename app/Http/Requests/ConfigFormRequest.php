<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConfigFormRequest extends FormRequest
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
        return [
            'currency'              => 'required|string|max:50',
            'nombre_empresa'        => 'nullable|string|max:100',
            'email'                 => 'nullable|email|max:255',
            'fb_link'               => 'nullable|url',
            'inst_link'             => 'nullable|url',
            'yt_link'               => 'nullable|url',
            'wapp_link'             => 'nullable|url',
            'impuesto'              => 'nullable|numeric|min:0|max:100',
            'descuento_maximo'      => 'nullable|numeric|min:0|max:100',
            'dias_vigencia_token'   => 'nullable|integer|min:30|max:365',
            'historial_visible_empresa' => 'nullable|boolean',
        ];
    }
}
