<?php

namespace App\Http\Requests\Cuestionario;

use App\Http\Requests\Cuestionario\Concerns\EtiquetasValidacionCuestionario;
use App\Support\HistorialAcademico;
use App\Support\HistorialLaboralIntegridad;
use App\Support\TablaDinamica;
use Illuminate\Foundation\Http\FormRequest;

class HistorialLaboralRequest extends FormRequest
{
    use EtiquetasValidacionCuestionario;
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->has('formacion_academica') || ! is_array($this->input('formacion_academica'))) {
            return;
        }

        $filas = TablaDinamica::normalizarFilas(
            $this->input('formacion_academica'),
            TablaDinamica::columnasFormacionAcademica()
        );

        $this->merge([
            'formacion_academica' => HistorialAcademico::filasParaValidacion(
                $this->input('ultimo_nivel_academico'),
                $filas
            ),
        ]);
    }

    public function rules(): array
    {
        return array_merge([
            'ultimo_nivel_academico' => HistorialAcademico::reglasValidacion()['ultimo_nivel_academico'],
            'experiencia_previa' => 'required|in:si,no',
            'situacion_laboral_actual' => 'required|string|in:empleado,independiente,empresario,desempleado,estudiante,jubilado',
            'anos_experiencia_laboral' => 'required|integer|min:0|max:50',
            'empresa_actual' => 'nullable|string|max:100',
            'puesto_actual' => 'nullable|string|max:100',
            'fecha_inicio_actual' => 'nullable|date|before_or_equal:today',
            'salario_actual' => 'nullable|numeric|min:0',
            'jefe_inmediato' => 'nullable|string|max:100',
            'tipo_negocio' => 'nullable|string|max:100',
            'ingresos_mensuales' => 'nullable|numeric|min:0',
            'empleos_anteriores' => 'nullable|string|max:2000',
            'motivo_busqueda' => 'nullable|string|in:desempleo,mejor_oportunidad,cambio_de_area,mejores_ingresos,crecimiento_profesional,ambiente_laboral,otro',
            'observaciones_laborales' => 'nullable|string|max:2000',
        ], TablaDinamica::reglasValidacion(3, 'preempleo'), HistorialLaboralIntegridad::reglasValidacion());
    }

    public function messages(): array
    {
        return array_merge([
            'experiencia_previa.required' => 'Indique si posee experiencia laboral previa.',
            'situacion_laboral_actual.required' => 'Debe indicar su situación laboral actual.',
            'anos_experiencia_laboral.required' => 'Debe indicar sus años de experiencia laboral.',
        ], HistorialAcademico::mensajesValidacion(), TablaDinamica::mensajesValidacion(), HistorialLaboralIntegridad::mensajesValidacion());
    }
}
