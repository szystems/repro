<?php

namespace App\Http\Requests;

use App\Models\DocumentoEvaluado;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DocumentoEvaluadoRequest extends FormRequest
{
    /**
     * Solo usuarios autenticados pueden subir documentos.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'evaluado_orden_id' => ['required', 'exists:evaluados_orden,id'],
            'tipo_documento'    => ['required', Rule::in(array_keys(DocumentoEvaluado::tiposDocumento()))],
            'archivo'           => ['required', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,doc,docx'],
            'notas'             => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'archivo.max'   => 'El archivo no debe exceder 10 MB.',
            'archivo.mimes' => 'Solo se permiten archivos PDF, JPG, PNG, DOC y DOCX.',
        ];
    }
}
