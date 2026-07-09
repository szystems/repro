<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class OrdenFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Temporalmente permitir a todos los usuarios autenticados
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        // Debug: Log los datos que llegan
        Log::info('=== ORDEN FORM REQUEST ===');
        Log::info('Request data:', $this->all());
        Log::info('Auth user:', Auth::check() ? Auth::user()->name : 'NO USER');
        
        $rules = [
            // Campos a nivel de orden
            'observaciones' => 'nullable|string|max:500',
            'instrucciones_generales' => 'nullable|string|max:1000',
            'prioridad' => 'nullable|in:baja,normal,alta,urgente',
            'fecha_limite' => 'nullable|date|after_or_equal:today',
            
            // Validación de evaluados (requeridos)
            'evaluados' => 'required|array|min:1',
            'evaluados.*.nombre' => 'required|string|max:100',
            'evaluados.*.apellidos' => 'required|string|max:100', 
            'evaluados.*.dpi' => 'required|string|size:13|regex:/^[0-9]{13}$/',
            'evaluados.*.email' => 'required|email|max:100',
            'evaluados.*.telefono' => 'nullable|string|max:20',
            
            // Campos granulares por evaluado
            'evaluados.*.tipo_servicio' => 'required|in:poligrafo,vsa,socioeconomico',
            'evaluados.*.tipo_formulario' => 'required|in:preempleo,periodica,especifica',
            'evaluados.*.fecha_programada' => 'nullable|date|after_or_equal:today',
            'evaluados.*.poligrafista_id' => 'nullable|exists:users,id'
        ];

        // Empresa siempre requerida por ahora
        $rules['empresa_id'] = 'required|exists:empresas,id';

        Log::info('Rules aplicadas:', $rules);
        
        return $rules;
    }

    /**
     * Get custom validation messages
     */
    public function messages(): array
    {
        return [
            'fecha_limite.after_or_equal' => 'La fecha límite debe ser hoy o posterior.',
            'prioridad.in' => 'Prioridad inválida.',
            
            'empresa_id.required' => 'Debe seleccionar una empresa.',
            'empresa_id.exists' => 'La empresa seleccionada no existe.',
            
            'evaluados.required' => 'Debe agregar al menos un evaluado.',
            'evaluados.min' => 'Debe agregar al menos un evaluado.',
            
            'evaluados.*.nombre.required' => 'El nombre del evaluado es obligatorio.',
            'evaluados.*.apellidos.required' => 'Los apellidos del evaluado son obligatorios.',
            'evaluados.*.dpi.required' => 'El DPI del evaluado es obligatorio.',
            'evaluados.*.dpi.size' => 'El DPI debe tener exactamente 13 dígitos.',
            'evaluados.*.dpi.regex' => 'El DPI debe contener solo números.',
            'evaluados.*.email.required' => 'El email del evaluado es obligatorio.',
            'evaluados.*.email.email' => 'Formato de email inválido.',
            
            'evaluados.*.tipo_servicio.required' => 'El tipo de servicio es obligatorio para cada evaluado.',
            'evaluados.*.tipo_servicio.in' => 'Tipo de servicio inválido.',
            'evaluados.*.tipo_formulario.required' => 'El tipo de formulario es obligatorio para cada evaluado.',
            'evaluados.*.tipo_formulario.in' => 'Tipo de formulario inválido.',
            'evaluados.*.fecha_programada.after_or_equal' => 'La fecha programada debe ser hoy o posterior.',
            'evaluados.*.poligrafista_id.exists' => 'El polígrafo seleccionado no existe.'
        ];
    }

    /**
     * Validaciones adicionales después de las reglas básicas
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            Log::info('=== VALIDADOR CUSTOM ===');
            
            // Validar DPIs: permitir mismo DPI solo si diferente tipo_servicio
            if ($this->has('evaluados') && is_array($this->evaluados)) {
                $combinaciones = collect($this->evaluados)->map(fn($e) => ($e['dpi'] ?? '') . '|' . ($e['tipo_servicio'] ?? ''))->filter();
                if ($combinaciones->count() !== $combinaciones->unique()->count()) {
                    Log::info('Error: DPI+servicio duplicados');
                    $validator->errors()->add('evaluados', 'No se puede repetir el mismo DPI con el mismo tipo de servicio en la misma orden.');
                }

                // H-08: Validar emails duplicados dentro de la misma orden
                $emails = collect($this->evaluados)
                    ->pluck('email')
                    ->filter()
                    ->map(fn($e) => strtolower(trim($e)));
                if ($emails->count() !== $emails->unique()->count()) {
                    $validator->errors()->add('evaluados', 'No se puede repetir el mismo email en la misma orden (cada evaluado recibe un token único).');
                }

                // Validar combinación servicio-formulario para cada evaluado
                foreach ($this->evaluados as $index => $evaluado) {
                    if (isset($evaluado['tipo_servicio']) && isset($evaluado['tipo_formulario'])) {
                        if ($evaluado['tipo_servicio'] === 'socioeconomico'
                            && $evaluado['tipo_formulario'] !== 'preempleo') {
                            Log::info("Error: Combinación inválida en evaluado {$index}");
                            $validator->errors()->add("evaluados.{$index}.tipo_formulario",
                                'El estudio socioeconómico se registra con formulario pre-empleo en la orden.');
                        }
                    }
                }
            }
            
            // Log todos los errores
            if ($validator->fails()) {
                Log::error('ERRORES DE VALIDACIÓN:', $validator->errors()->toArray());
            } else {
                Log::info('✅ Validación exitosa');
            }
        });
    }
}
