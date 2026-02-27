<?php

namespace App\Http\Requests\Cuestionario;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class DatosPersonalesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Para cuestionarios públicos, siempre es true (autorización por token)
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Datos básicos
            'nombres_completos' => 'required|string|max:100|regex:/^[a-zA-ZáéíóúÁÉÍÓÚüÜñÑ\s]+$/',
            'apellidos_completos' => 'required|string|max:100|regex:/^[a-zA-ZáéíóúÁÉÍÓÚüÜñÑ\s]+$/',
            'dpi' => 'required|string|size:13|regex:/^[0-9]{13}$/',
            'fecha_nacimiento' => [
                'required',
                'date',
                'before:today',
                'after:' . Carbon::now()->subYears(80)->format('Y-m-d'),
                'before:' . Carbon::now()->subYears(16)->format('Y-m-d')
            ],
            
            // Estado civil y datos personales
            'estado_civil' => 'required|in:soltero,casado,divorciado,viudo,union_libre',
            'genero' => 'required|in:masculino,femenino',
            'nacionalidad' => 'required|string|max:100',
            'lugar_nacimiento' => 'required|string|max:255',
            
            // Contacto
            'direccion_residencia' => 'required|string|max:500',
            'departamento' => 'required|string|max:100',
            'municipio' => 'required|string|max:100',
            'telefono_alternativo' => 'nullable|string|max:15|regex:/^[0-9\-\+\(\)\s]+$/',
            'telefono_personal' => 'required|string|max:15|regex:/^[0-9\-\+\(\)\s]+$/',
            'email_personal' => 'required|email|max:100',
            
            // Información profesional
            'profesion_oficio' => 'required|string|max:100',
            'nivel_educativo' => 'required|in:primaria_incompleta,primaria_completa,basicos_incompletos,basicos_completos,diversificado_incompleto,diversificado_completo,universidad_incompleta,universidad_completa,posgrado',
            'titulo_obtenido' => 'nullable|string|max:255',
            'institucion_educativa' => 'nullable|string|max:255',
            
            // Otros campos del formulario actual
            'lugar_nacimiento' => 'required|string|max:100',
            'nacionalidad' => 'required|string|max:50',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nombres_completos.required' => 'Los nombres son obligatorios.',
            'nombres_completos.regex' => 'Los nombres solo pueden contener letras y espacios.',
            'apellidos_completos.required' => 'Los apellidos son obligatorios.',
            'apellidos_completos.regex' => 'Los apellidos solo pueden contener letras y espacios.',
            'dpi.required' => 'El DPI es obligatorio.',
            'dpi.size' => 'El DPI debe tener exactamente 13 dígitos.',
            'dpi.regex' => 'El DPI solo puede contener números.',
            'fecha_nacimiento.required' => 'La fecha de nacimiento es obligatoria.',
            'fecha_nacimiento.before' => 'La fecha de nacimiento debe ser anterior a hoy.',
            'fecha_nacimiento.after' => 'Debe ser mayor de 16 años.',
            'estado_civil.required' => 'El estado civil es obligatorio.',
            'estado_civil.in' => 'Seleccione un estado civil válido.',
            'direccion_residencia.required' => 'La dirección de residencia es obligatoria.',
            'telefono_personal.required' => 'El teléfono personal es obligatorio.',
            'telefono_personal.regex' => 'Formato de teléfono no válido.',
            'email_personal.required' => 'El correo electrónico personal es obligatorio.',
            'email_personal.email' => 'Ingrese un correo electrónico válido.',
            'profesion_oficio.required' => 'La profesión u oficio es obligatoria.',
            'nivel_educativo.required' => 'El nivel educativo es obligatorio.',
            'lugar_nacimiento.required' => 'El lugar de nacimiento es obligatorio.',
            'nacionalidad.required' => 'La nacionalidad es obligatoria.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nombres_completos' => 'nombres completos',
            'apellidos_completos' => 'apellidos completos',
            'fecha_nacimiento' => 'fecha de nacimiento',
            'estado_civil' => 'estado civil',
            'direccion_residencia' => 'dirección de residencia',
            'telefono_personal' => 'teléfono personal',
            'telefono_alternativo' => 'teléfono alternativo',
            'email_personal' => 'correo electrónico personal',
            'nivel_educativo' => 'nivel educativo',
            'profesion_oficio' => 'profesión u oficio',
            'lugar_nacimiento' => 'lugar de nacimiento',
            'nacionalidad' => 'nacionalidad',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // El DPI debe coincidir con el del evaluado (validación de identidad)
            // No validamos formato guatemalteco aquí porque ya está en la base de datos
            // y solo queremos verificar identidad
            if ($this->dpi && $this->route('token')) {
                $evaluado = \App\Models\EvaluadoOrden::where('token_unico', $this->route('token'))->first();
                if ($evaluado && $this->dpi !== $evaluado->dpi) {
                    $validator->errors()->add('dpi', 'El DPI ingresado no coincide con el registrado.');
                }
            }
        });
    }

    /**
     * Validar DPI guatemalteco usando algoritmo oficial
     */
    private function validarDpiGuatemalteco(string $dpi): bool
    {
        if (strlen($dpi) !== 13) {
            return false;
        }

        $total = 0;
        $multiplicador = 2;

        // Calcular suma ponderada de los primeros 12 dígitos
        for ($i = 11; $i >= 0; $i--) {
            $total += intval($dpi[$i]) * $multiplicador;
            $multiplicador++;
        }

        $modulo = $total % 11;
        $digitoVerificador = ($modulo == 0) ? 0 : 11 - $modulo;

        return $digitoVerificador == intval($dpi[12]);
    }
}
