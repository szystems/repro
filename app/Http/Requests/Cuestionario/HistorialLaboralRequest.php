<?php

namespace App\Http\Requests\Cuestionario;

use App\Http\Requests\Cuestionario\Concerns\EtiquetasValidacionCuestionario;
use App\Http\Requests\Cuestionario\Concerns\PreparaTablasDinamicasParaValidacion;
use App\Support\HistorialAcademico;
use App\Support\HistorialLaboralIntegridad;
use App\Support\TablaDinamica;
use Illuminate\Foundation\Http\FormRequest;

class HistorialLaboralRequest extends FormRequest
{
    use EtiquetasValidacionCuestionario;
    use PreparaTablasDinamicasParaValidacion;

    protected function numeroSeccionTablasDinamicas(): int
    {
        return 3;
    }

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->prepararTablasDinamicas();

        if (! $this->has('formacion_academica') || ! is_array($this->input('formacion_academica'))) {
            return;
        }

        $filas = $this->input('formacion_academica');

        $this->merge([
            'formacion_academica' => HistorialAcademico::filasParaValidacion(
                $this->input('ultimo_nivel_academico'),
                $filas
            ),
        ]);
    }

    public function rules(): array
    {
        $tipo = $this->resolverTipoFormularioCuestionario();

        return array_merge([
            'ultimo_nivel_academico' => HistorialAcademico::reglasValidacion()['ultimo_nivel_academico'],
            'estudia_actualmente' => HistorialAcademico::reglasEstudiaActualmente()['estudia_actualmente'],
            'experiencia_previa' => 'required|in:si,no',
            'observaciones_laborales' => 'nullable|string|max:2000',
        ], TablaDinamica::reglasValidacion(3, $tipo), HistorialLaboralIntegridad::reglasValidacion());
    }

    public function messages(): array
    {
        return array_merge([
            'experiencia_previa.required' => 'Indique si posee experiencia laboral previa.',
        ], HistorialAcademico::mensajesValidacion(), TablaDinamica::mensajesValidacion(), HistorialLaboralIntegridad::mensajesValidacion());
    }
}
