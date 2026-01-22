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
            'situacion_laboral_actual' => 'required|string|in:empleado,independiente,empresario,desempleado,estudiante,jubilado',
            'anos_experiencia_laboral' => 'required|integer|min:0|max:50',
            
            // Empleo actual (si aplica - empleado)
            'empresa_actual' => 'nullable|string|max:100',
            'puesto_actual' => 'nullable|string|max:100',
            'fecha_inicio_actual' => 'nullable|date|before_or_equal:today',
            'salario_actual' => 'nullable|numeric|min:0',
            'jefe_inmediato' => 'nullable|string|max:100',
            
            // Trabajo independiente (si aplica)
            'tipo_negocio' => 'nullable|string|max:100',
            'ingresos_mensuales' => 'nullable|numeric|min:0',
            
            // Historial laboral (texto libre)
            'empleos_anteriores' => 'nullable|string|max:2000',
            
            // Motivo de búsqueda de empleo
            'motivo_busqueda' => 'nullable|string|in:desempleo,mejor_oportunidad,cambio_de_area,mejores_ingresos,crecimiento_profesional,ambiente_laboral,otro',
        ];
    }

    public function messages(): array
    {
        return [
            'situacion_laboral_actual.required' => 'Debe indicar su situación laboral actual.',
            'situacion_laboral_actual.in' => 'Seleccione una situación laboral válida.',
            'anos_experiencia_laboral.required' => 'Debe indicar sus años de experiencia laboral.',
            'anos_experiencia_laboral.integer' => 'Los años de experiencia deben ser un número entero.',
            'anos_experiencia_laboral.min' => 'Los años de experiencia no pueden ser negativos.',
            'anos_experiencia_laboral.max' => 'Los años de experiencia no pueden ser mayores a 50.',
            'fecha_inicio_actual.date' => 'La fecha de inicio debe ser una fecha válida.',
            'fecha_inicio_actual.before_or_equal' => 'La fecha de inicio no puede ser futura.',
            'salario_actual.numeric' => 'El salario debe ser un número válido.',
            'salario_actual.min' => 'El salario no puede ser negativo.',
            'motivo_busqueda.in' => 'Seleccione un motivo válido.',
        ];
    }
}
