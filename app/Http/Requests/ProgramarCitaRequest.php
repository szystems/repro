<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

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
        // Al reprogramar (PATCH), el evaluado viene por route model binding — no se necesita en el body.
        $evaluadoRules = $this->isMethod('PATCH')
            ? ['nullable', 'integer', 'exists:evaluados_orden,id']
            : ['required', 'integer', 'exists:evaluados_orden,id'];

        $motivoRules = $this->isMethod('PATCH')
            ? ['required', 'string', 'min:3', 'max:500']
            : ['nullable', 'string', 'max:500'];

        return [
            'evaluado_orden_id' => $evaluadoRules,
            'fecha'             => ['required', 'date', 'after_or_equal:today'],
            'hora_inicio'       => ['required', 'date_format:H:i'],
            'hora_fin'          => ['required', 'date_format:H:i', 'after:hora_inicio'],
            'poligrafista_id'   => ['nullable', 'integer', 'exists:users,id'],
            'sede_id'           => ['required', 'integer', 'exists:sedes,id'],
            'modalidad'         => ['nullable', 'in:presencial,virtual'],
            'responsable_id'    => ['nullable', 'integer', 'exists:users,id'],
            'motivo_reprogramacion' => $motivoRules,
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
            'poligrafista_id.exists'     => 'El poligrafista seleccionado no existe.',
            'sede_id.required'           => 'Debe seleccionar una sede.',
            'sede_id.exists'             => 'La sede seleccionada no existe.',
            'motivo_reprogramacion.required' => 'Indique el motivo de la reprogramación.',
            'motivo_reprogramacion.min'      => 'El motivo de reprogramación es demasiado corto.',
            'motivo_reprogramacion.max'      => 'El motivo de reprogramación no puede exceder 500 caracteres.',
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

    protected function failedValidation(Validator $validator): void
    {
        $evaluadoId = $this->input('evaluado_orden_id') ?? $this->route('evaluado')?->id;

        throw new HttpResponseException(
            redirect()->back()
                ->withInput()
                ->withErrors($validator)
                ->with('error', $validator->errors()->first())
                ->with('programar_evaluado_id', $evaluadoId)
        );
    }
}
