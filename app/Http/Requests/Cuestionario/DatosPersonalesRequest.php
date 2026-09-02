<?php

namespace App\Http\Requests\Cuestionario;

use App\Http\Requests\Cuestionario\Concerns\EtiquetasValidacionCuestionario;
use App\Support\DatosPersonalesCampos;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class DatosPersonalesRequest extends FormRequest
{
    use EtiquetasValidacionCuestionario;
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $fecha = $this->input('fecha_nacimiento');
        if (! is_string($fecha)) {
            return;
        }

        $this->merge([
            'fecha_nacimiento' => DatosPersonalesCampos::normalizarFechaNacimiento($fecha),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $tipos = implode(',', array_keys(DatosPersonalesCampos::TIPOS_IDENTIFICACION));
        $licencias = implode(',', array_keys(DatosPersonalesCampos::LICENCIA_CONDUCIR));

        $reglas = [
            'nombres_completos' => 'required|string|max:100|regex:/^[a-zA-ZáéíóúÁÉÍÓÚüÜñÑ\s]+$/',
            'apellidos_completos' => 'required|string|max:100|regex:/^[a-zA-ZáéíóúÁÉÍÓÚüÜñÑ\s]+$/',
            'tipo_identificacion' => 'required|in:'.$tipos,
            'dpi' => 'required|string|max:30',
            'fecha_nacimiento' => [
                'required',
                'date',
                'before:today',
                'after:'.Carbon::now()->subYears(80)->format('Y-m-d'),
                'before:'.Carbon::now()->subYears(18)->format('Y-m-d'),
            ],
            'edad' => 'nullable|integer|min:18|max:120',
            'estado_civil' => 'required|in:soltero,casado,divorciado,viudo,union_libre',
            'nacionalidad' => 'required|string|max:100',
            'telefono_personal' => 'required|string|max:15|regex:/^[0-9\-\+\(\)\s]+$/',
            'telefono_alternativo' => 'nullable|string|max:15|regex:/^[0-9\-\+\(\)\s]+$/',
            'email_personal' => 'required|email|max:100',
            'departamento_nacimiento' => 'required|string|max:100',
            'municipio_nacimiento' => 'required|string|max:100',
            'direccion_residencia' => 'required|string|max:500',
            'departamento' => 'required|string|max:100',
            'municipio' => 'required|string|max:100',
            'licencia_conducir' => 'required|in:'.$licencias,
            'foto_candidato' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
            'foto_candidato_existente' => 'nullable|in:1',
        ];

        if (! in_array($this->tipoFormularioCuestionario(), ['periodica', 'especifica'], true)) {
            $reglas['igss'] = 'nullable|string|max:30';
            $reglas['nit'] = 'nullable|string|max:20';
        }

        return $reglas;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nombres_completos.required' => 'Los nombres son obligatorios.',
            'nombres_completos.regex' => 'Los nombres solo pueden contener letras y espacios.',
            'apellidos_completos.required' => 'Los apellidos son obligatorios.',
            'apellidos_completos.regex' => 'Los apellidos solo pueden contener letras y espacios.',
            'tipo_identificacion.required' => 'Seleccione el tipo de identificación.',
            'dpi.required' => 'El número de identificación es obligatorio.',
            'fecha_nacimiento.required' => 'La fecha de nacimiento es obligatoria.',
            'fecha_nacimiento.before' => 'Debe ser mayor de 18 años.',
            'fecha_nacimiento.after' => 'Ingrese una fecha de nacimiento válida.',
            'estado_civil.required' => 'El estado civil es obligatorio.',
            'direccion_residencia.required' => 'La dirección de residencia es obligatoria.',
            'departamento_nacimiento.required' => 'Seleccione el departamento de nacimiento.',
            'municipio_nacimiento.required' => 'Seleccione el municipio de nacimiento.',
            'departamento.required' => 'Seleccione el departamento de residencia.',
            'municipio.required' => 'Seleccione el municipio de residencia.',
            'telefono_personal.required' => 'El teléfono personal es obligatorio.',
            'email_personal.required' => 'El correo electrónico personal es obligatorio.',
            'licencia_conducir.required' => 'Indique si posee licencia de conducir.',
            'foto_candidato.image' => 'La fotografía debe ser una imagen válida.',
            'foto_candidato.mimes' => 'Formatos permitidos: JPG, PNG o WEBP.',
            'foto_candidato.max' => 'La fotografía no puede superar 5 MB.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nombres_completos' => 'nombres completos',
            'apellidos_completos' => 'apellidos completos',
            'tipo_identificacion' => 'tipo de identificación',
            'dpi' => 'número de identificación',
            'fecha_nacimiento' => 'fecha de nacimiento',
            'estado_civil' => 'estado civil',
            'direccion_residencia' => 'dirección de residencia',
            'departamento_nacimiento' => 'departamento de nacimiento',
            'municipio_nacimiento' => 'municipio de nacimiento',
            'departamento' => 'departamento de residencia',
            'municipio' => 'municipio de residencia',
            'telefono_personal' => 'teléfono personal',
            'telefono_alternativo' => 'teléfono de emergencia',
            'email_personal' => 'correo electrónico personal',
            'igss' => 'IGSS',
            'nit' => 'NIT',
            'licencia_conducir' => 'licencia de conducir',
            'nacionalidad' => 'nacionalidad',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $tipo = (string) $this->input('tipo_identificacion', 'dpi');
            $numero = (string) $this->input('dpi', '');

            if ($tipo === 'dpi') {
                if (! preg_match('/^\d{13}$/', $numero)) {
                    $validator->errors()->add('dpi', 'El DPI debe tener exactamente 13 dígitos numéricos.');
                } elseif ($this->route('token')) {
                    $evaluado = \App\Models\EvaluadoOrden::where('token_unico', $this->route('token'))->first();
                    if ($evaluado && $numero !== (string) $evaluado->dpi) {
                        $validator->errors()->add('dpi', 'El DPI ingresado no coincide con el registrado.');
                    }
                }
            } elseif ($numero === '') {
                $validator->errors()->add('dpi', 'El número de identificación es obligatorio.');
            }

            if (! $this->hasFile('foto_candidato') && ! $this->tieneFotoCandidatoExistente()) {
                $validator->errors()->add('foto_candidato', 'Debe tomar o subir su fotografía para continuar.');
            }
        });
    }

    private function tieneFotoCandidatoExistente(): bool
    {
        if ($this->input('foto_candidato_existente') === '1') {
            return true;
        }

        $token = $this->route('token');
        if (! $token) {
            return false;
        }

        $evaluado = \App\Models\EvaluadoOrden::where('token_unico', $token)->with('cuestionario')->first();
        if (! $evaluado?->cuestionario) {
            return false;
        }

        return (bool) \App\Support\CuestionarioFotoCandidato::obtenerRuta(
            $evaluado->cuestionario->id,
            $this->slugSeccionDatosPersonales($evaluado->cuestionario->tipo_formulario)
        );
    }

    private function slugSeccionDatosPersonales(string $tipoFormulario): string
    {
        return match ($tipoFormulario) {
            'periodica' => 'actualizacion_datos',
            'especifica' => 'datos_basicos',
            default => 'datos_personales',
        };
    }

    private function tipoFormularioCuestionario(): string
    {
        $token = $this->route('token');

        if (! is_string($token) || $token === '') {
            return 'preempleo';
        }

        $evaluado = \App\Models\EvaluadoOrden::query()
            ->where('token_unico', $token)
            ->first();

        return $evaluado?->tipoFormularioCuestionario() ?? 'preempleo';
    }
}
