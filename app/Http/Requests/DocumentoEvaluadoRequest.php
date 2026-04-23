<?php

namespace App\Http\Requests;

use App\Models\DocumentoEvaluado;
use App\Models\EvaluadoOrden;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class DocumentoEvaluadoRequest extends FormRequest
{
    /**
     * Solo usuarios autenticados que tengan acceso al evaluado.
     */
    public function authorize(): bool
    {
        if (!Auth::check()) {
            return false;
        }

        $evaluadoId = $this->input('evaluado_orden_id');
        if (!$evaluadoId) {
            return false;
        }

        $evaluado = EvaluadoOrden::with('orden')->find($evaluadoId);
        if (!$evaluado) {
            return false;
        }

        $user = Auth::user();

        // Admin/REPRO puede subir documentos a cualquier evaluado
        if ($user->role_as >= 2) {
            return true;
        }

        // Empresa: solo evaluados de sus propias órdenes
        return $evaluado->orden
            && (int) $evaluado->orden->empresa_id === (int) $user->empresa_id;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'evaluado_orden_id' => ['required', 'exists:evaluados_orden,id'],
            'tipo_documento'    => ['required', Rule::in(array_keys(DocumentoEvaluado::tiposDocumento()))],
            'archivo'           => [
                'required',
                'file',
                'max:10240',
                'mimes:pdf,jpg,jpeg,png,doc,docx',
                'mimetypes:application/pdf,image/jpeg,image/png,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ],
            'notas'             => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'archivo.max'       => 'El archivo no debe exceder 10 MB.',
            'archivo.mimes'     => 'Solo se permiten archivos PDF, JPG, PNG, DOC y DOCX.',
            'archivo.mimetypes' => 'El contenido del archivo no coincide con su extensión.',
        ];
    }
}
