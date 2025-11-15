<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EvaluadoFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Este form request se usa para evaluados que NO son usuarios del sistema
        // pero necesitan completar cuestionarios vía token
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Datos personales obligatorios
            'nombre' => 'required|string|max:100|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'apellidos' => 'required|string|max:100|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'dpi' => 'required|string|size:13|regex:/^[0-9]{13}$/|unique:evaluados_orden,dpi',
            
            // Datos de contacto
            'telefono' => 'nullable|string|max:20|regex:/^[0-9\-\+\s\(\)]+$/',
            'celular' => 'nullable|string|max:20|regex:/^[0-9\-\+\s\(\)]+$/',
            'email' => 'nullable|email|max:100',
            
            // Tipo de documento (por defecto DPI en Guatemala)
            'tipo_documento' => 'required|in:dpi,pasaporte,cedula',
            
            // Datos laborales (opcionales para empresas)
            'puesto_evaluar' => 'nullable|string|max:100',
            'observaciones' => 'nullable|string|max:500'
        ];
    }

    /**
     * Get custom validation messages
     */
    public function messages(): array
    {
        return [
            // Validaciones de nombres
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.regex' => 'El nombre solo puede contener letras y espacios.',
            'apellidos.required' => 'Los apellidos son obligatorios.',
            'apellidos.regex' => 'Los apellidos solo pueden contener letras y espacios.',
            
            // Validaciones de DPI
            'dpi.required' => 'El DPI (Documento Personal de Identificación) es obligatorio.',
            'dpi.size' => 'El DPI debe tener exactamente 13 dígitos.',
            'dpi.regex' => 'El DPI debe contener solo números.',
            'dpi.unique' => 'Este DPI ya está registrado en el sistema.',
            
            // Validaciones de contacto
            'telefono.regex' => 'Formato de teléfono inválido.',
            'celular.regex' => 'Formato de teléfono celular inválido.',
            'email.email' => 'Formato de correo electrónico inválido.',
            
            // Tipo de documento
            'tipo_documento.required' => 'El tipo de documento es obligatorio.',
            'tipo_documento.in' => 'Tipo de documento inválido.'
        ];
    }

    /**
     * Preparar los datos para validación
     */
    protected function prepareForValidation(): void
    {
        // Limpiar y formatear DPI
        if ($this->has('dpi')) {
            $this->merge([
                'dpi' => preg_replace('/[^0-9]/', '', $this->dpi)
            ]);
        }

        // Limpiar números de teléfono
        if ($this->has('telefono')) {
            $this->merge([
                'telefono' => $this->telefono ? trim($this->telefono) : null
            ]);
        }

        if ($this->has('celular')) {
            $this->merge([
                'celular' => $this->celular ? trim($this->celular) : null
            ]);
        }

        // Normalizar nombres (título case)
        if ($this->has('nombre')) {
            $this->merge([
                'nombre' => ucwords(strtolower(trim($this->nombre)))
            ]);
        }

        if ($this->has('apellidos')) {
            $this->merge([
                'apellidos' => ucwords(strtolower(trim($this->apellidos)))
            ]);
        }
    }

    /**
     * Validaciones adicionales
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validar que al menos un método de contacto sea proporcionado
            if (!$this->telefono && !$this->celular && !$this->email) {
                $validator->errors()->add('contacto', 'Debe proporcionar al menos un método de contacto (teléfono, celular o email).');
            }

            // Validación específica del DPI guatemalteco (algoritmo de verificación)
            if ($this->dpi && strlen($this->dpi) === 13) {
                if (!$this->validarDPIGuatemala($this->dpi)) {
                    $validator->errors()->add('dpi', 'El DPI ingresado no es válido según el algoritmo de verificación guatemalteco.');
                }
            }
        });
    }

    /**
     * Validar DPI guatemalteco con algoritmo de verificación
     */
    private function validarDPIGuatemala(string $dpi): bool
    {
        if (strlen($dpi) !== 13) {
            return false;
        }

        // Extraer dígitos
        $digitos = str_split($dpi);
        $verificador = (int)array_pop($digitos);

        // Calcular dígito verificador
        $suma = 0;
        $multiplicador = 2;

        for ($i = 11; $i >= 0; $i--) {
            $suma += (int)$digitos[$i] * $multiplicador;
            $multiplicador = $multiplicador === 7 ? 2 : $multiplicador + 1;
        }

        $modulo = $suma % 11;
        $digitoCalculado = $modulo === 0 ? 0 : 11 - $modulo;

        return $digitoCalculado === $verificador;
    }
}
