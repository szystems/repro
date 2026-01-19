<?php

namespace App\Http\Requests\Cuestionario;

use Illuminate\Foundation\Http\FormRequest;

class HistorialLaboralRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Situación laboral actual
            'situacion_actual' => 'required|in:empleado,desempleado,estudiante,independiente,jubilado',
            'empresa_actual' => 'required_unless:situacion_actual,desempleado,estudiante,jubilado|nullable|string|max:255',
            'cargo_actual' => 'required_unless:situacion_actual,desempleado,estudiante,jubilado|nullable|string|max:255',
            'salario_actual' => 'required_unless:situacion_actual,desempleado,estudiante,jubilado|nullable|numeric|min:0',
            'fecha_inicio_actual' => 'required_unless:situacion_actual,desempleado,estudiante,jubilado|nullable|date|before_or_equal:today',
            'jefe_inmediato_nombre' => 'nullable|string|max:255',
            'jefe_inmediato_telefono' => 'nullable|string|max:20',
            
            // Historial laboral (últimos 5 años)
            'empleos_anteriores' => 'nullable|array',
            'empleos_anteriores.*.empresa' => 'required|string|max:255',
            'empleos_anteriores.*.cargo' => 'required|string|max:255',
            'empleos_anteriores.*.fecha_inicio' => 'required|date|before_or_equal:today',
            'empleos_anteriores.*.fecha_fin' => 'required|date|after:empleos_anteriores.*.fecha_inicio|before_or_equal:today',
            'empleos_anteriores.*.salario' => 'required|numeric|min:0',
            'empleos_anteriores.*.motivo_salida' => 'required|string|max:500',
            'empleos_anteriores.*.jefe_nombre' => 'required|string|max:255',
            'empleos_anteriores.*.jefe_telefono' => 'required|string|max:20',
            'empleos_anteriores.*.puede_contactar' => 'required|boolean',
            
            // Períodos de desempleo
            'periodos_desempleo' => 'nullable|array',
            'periodos_desempleo.*.fecha_inicio' => 'required|date',
            'periodos_desempleo.*.fecha_fin' => 'required|date|after:periodos_desempleo.*.fecha_inicio',
            'periodos_desempleo.*.motivo' => 'required|string|max:500',
            'periodos_desempleo.*.actividad_realizada' => 'nullable|string|max:500',
            
            // Referencias laborales
            'referencia_laboral_1_nombre' => 'required|string|max:255',
            'referencia_laboral_1_empresa' => 'required|string|max:255',
            'referencia_laboral_1_cargo' => 'required|string|max:255',
            'referencia_laboral_1_telefono' => 'required|string|max:20',
            'referencia_laboral_1_email' => 'nullable|email|max:255',
            'referencia_laboral_1_años_conoce' => 'required|integer|between:1,50',
            
            'referencia_laboral_2_nombre' => 'nullable|string|max:255',
            'referencia_laboral_2_empresa' => 'nullable|string|max:255',
            'referencia_laboral_2_cargo' => 'nullable|string|max:255',
            'referencia_laboral_2_telefono' => 'nullable|string|max:20',
            'referencia_laboral_2_email' => 'nullable|email|max:255',
            'referencia_laboral_2_años_conoce' => 'nullable|integer|between:1,50',
            
            // Metas profesionales
            'metas_corto_plazo' => 'nullable|string|max:500',
            'metas_largo_plazo' => 'nullable|string|max:500',
            'capacitaciones_recientes' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'situacion_actual.required' => 'Debe indicar su situación laboral actual.',
            'empresa_actual.required_unless' => 'El nombre de la empresa actual es obligatorio.',
            'cargo_actual.required_unless' => 'El cargo actual es obligatorio.',
            'salario_actual.required_unless' => 'El salario actual es obligatorio.',
            'fecha_inicio_actual.required_unless' => 'La fecha de inicio en el trabajo actual es obligatoria.',
            'empleos_anteriores.*.empresa.required' => 'El nombre de la empresa es obligatorio.',
            'empleos_anteriores.*.cargo.required' => 'El cargo desempeñado es obligatorio.',
            'empleos_anteriores.*.motivo_salida.required' => 'El motivo de salida es obligatorio.',
            'referencia_laboral_1_nombre.required' => 'Debe proporcionar al menos una referencia laboral.',
        ];
    }
}
