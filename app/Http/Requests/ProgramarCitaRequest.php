<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProgramarCitaRequest extends FormRequest
{
    /** Solo usuarios REPRO (role_as >= 2) pueden programar citas. */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role_as >= 2;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'evaluado_orden_id' => ['required', 'integer', 'exists:evaluados_orden,id'],
            'fecha'             => ['required', 'date', 'after_or_equal:today'],
            'hora_inicio'       => ['required', 'date_format:H:i'],
            'hora_fin'          => ['required', 'date_format:H:i', 'after:hora_inicio'],
            'poligrafista_id'   => ['required', 'integer', 'exists:users,id'],
            'sede_id'           => ['required', 'integer', 'exists:sedes,id'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'evaluado_orden_id.required' => 'Debe seleccionar un evaluado.',
            'evaluado_orden_id.exists'   => 'El evaluado seleccionado no existe.',
            'fecha.required'             => 'La fecha es obligatoria.',
            'fecha.after_or_equal'       => 'La fecha no puede ser anterior a hoy.',
            'hora_inicio.required'       => 'La hora de inicio es obligatoria.',
            'hora_inicio.date_format'    => 'La hora de inicio debe tener formato HH:MM.',
            'hora_fin.required'          => 'La hora de fin es obligatoria.',
            'hora_fin.date_format'       => 'La hora de fin debe tener formato HH:MM.',
            'hora_fin.after'             => 'La hora de fin debe ser posterior a la hora de inicio.',
            'poligrafista_id.required'   => 'Debe asignar un poligrafista.',
            'poligrafista_id.exists'     => 'El poligrafista seleccionado no existe.',
            'sede_id.required'           => 'Debe seleccionar una sede.',
            'sede_id.exists'             => 'La sede seleccionada no existe.',
        ];
    }

    /**
     * Obtener datetime de inicio (fecha + hora_inicio).
     */
    public function getInicio(): string
    {
        return $this->fecha . ' ' . $this->hora_inicio . ':00';
    }

    /**
     * Obtener datetime de fin (fecha + hora_fin).
     */
    public function getFin(): string
    {
        return $this->fecha . ' ' . $this->hora_fin . ':00';
    }
}
