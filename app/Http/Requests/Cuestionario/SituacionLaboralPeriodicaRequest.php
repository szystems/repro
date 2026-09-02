<?php

namespace App\Http\Requests\Cuestionario;

use App\Http\Requests\Cuestionario\Concerns\EtiquetasValidacionCuestionario;
use App\Http\Requests\Cuestionario\Concerns\PreparaTablasDinamicasParaValidacion;
use App\Models\EvaluadoOrden;
use App\Support\HistorialAcademico;
use App\Support\HistorialLaboralPeriodico;
use App\Support\TablaDinamica;
use Illuminate\Foundation\Http\FormRequest;

class SituacionLaboralPeriodicaRequest extends FormRequest
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

        if ($this->tipoFormulario() !== 'periodica') {
            return;
        }

        if (! $this->has('formacion_academica') || ! is_array($this->input('formacion_academica'))) {
            return;
        }

        $this->merge([
            'formacion_academica' => HistorialAcademico::filasParaValidacion(
                $this->input('ultimo_nivel_academico'),
                $this->input('formacion_academica')
            ),
        ]);
    }

    public function rules(): array
    {
        $tipo = $this->tipoFormulario();
        $esEspecifica = $tipo === 'especifica';

        $reglas = [
            'ultimo_nivel_academico' => HistorialAcademico::reglasValidacion()['ultimo_nivel_academico'],
            'tiene_empleo_actual' => 'required|in:si,no',
        ];

        if ($tipo === 'periodica') {
            $reglas['estudia_actualmente'] = HistorialAcademico::reglasEstudiaActualmente()['estudia_actualmente'];
        }

        return array_merge(
            $reglas,
            TablaDinamica::reglasValidacion(3, $tipo),
            HistorialLaboralPeriodico::reglasValidacion($esEspecifica)
        );
    }

    public function messages(): array
    {
        return array_merge(
            [
                'tiene_empleo_actual.required' => 'Indique si tiene o ha tenido empleo.',
            ],
            HistorialAcademico::mensajesValidacion(),
            TablaDinamica::mensajesValidacion(),
            HistorialLaboralPeriodico::mensajesValidacion()
        );
    }

    private function tipoFormulario(): string
    {
        $token = $this->route('token');

        if (! is_string($token) || $token === '') {
            return 'periodica';
        }

        $evaluado = EvaluadoOrden::query()->where('token_unico', $token)->first();

        return $evaluado?->tipoFormularioCuestionario() ?? 'periodica';
    }
}
