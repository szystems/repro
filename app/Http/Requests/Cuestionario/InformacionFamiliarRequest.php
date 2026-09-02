<?php

namespace App\Http\Requests\Cuestionario;

use App\Http\Requests\Cuestionario\Concerns\EtiquetasValidacionCuestionario;
use App\Http\Requests\Cuestionario\Concerns\PreparaTablasDinamicasParaValidacion;
use App\Support\InformacionFamiliarExparejas;
use App\Support\InformacionFamiliarPadres;
use App\Support\InformacionFamiliarPareja;
use App\Support\TablaDinamica;
use Illuminate\Foundation\Http\FormRequest;

class InformacionFamiliarRequest extends FormRequest
{
    use EtiquetasValidacionCuestionario;
    use PreparaTablasDinamicasParaValidacion;

    protected function numeroSeccionTablasDinamicas(): int
    {
        return 2;
    }

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('convive_con')) {
            $this->merge([
                'convive_con' => InformacionFamiliarPadres::conviveConParaAlmacenar($this->input('convive_con')),
            ]);
        }

        $this->prepararTablasDinamicas();
    }

    public function rules(): array
    {
        $tipo = $this->resolverTipoFormularioCuestionario();

        $reglas = [
            // Estado civil y pareja
            'estado_civil_detalle' => 'required|string|in:soltero,casado,union_libre,divorciado,viudo',
            'vive_con_pareja' => 'required|in:si,no',
            'tiene_hijos' => 'required|in:si,no',
            'numero_hijos' => 'nullable|required_if:tiene_hijos,si|integer|min:1|max:20',
            'hijos_menores' => 'nullable|integer|min:0|max:20',
            'hijos_dependientes' => 'nullable|integer|min:0|max:20',

            // Observaciones
            'observaciones_familiares' => 'nullable|string|max:2000',
        ];

        if (! in_array($tipo, ['periodica', 'especifica'], true)) {
            $reglas['tiene_hermanos'] = 'required|in:si,no';
        }

        $reglasFamilia = array_merge(
            InformacionFamiliarPadres::reglasValidacion(),
            InformacionFamiliarPareja::reglasValidacion(),
        );

        if (! in_array($tipo, ['periodica', 'especifica'], true)) {
            $reglasFamilia = array_merge($reglasFamilia, InformacionFamiliarExparejas::reglasValidacion());
        }

        return array_merge(
            $reglas,
            $reglasFamilia,
            TablaDinamica::reglasValidacion(2, $tipo)
        );
    }

    public function messages(): array
    {
        return array_merge([
            'estado_civil_detalle.required' => 'El estado civil es obligatorio.',
            'estado_civil_detalle.in' => 'Seleccione un estado civil válido.',
            'vive_con_pareja.required' => 'Debe indicar si tiene pareja actual.',
            'vive_con_pareja.in' => 'Seleccione una opción válida.',
            'tiene_hijos.required' => 'Debe indicar si tiene hijos.',
            'tiene_hijos.in' => 'El campo tiene hijos debe ser verdadero o falso.',
            'tiene_hermanos.required' => 'Debe indicar si tiene hermanos.',
            'tiene_hermanos.in' => 'Seleccione una opción válida.',
            'numero_hijos.integer' => 'El número de hijos debe ser un número entero.',
            'numero_hijos.required_if' => 'Indique el número de hijos.',
            'numero_hijos.min' => 'Debe indicar al menos 1 hijo.',
        ], InformacionFamiliarPadres::mensajesValidacion(), InformacionFamiliarPareja::mensajesValidacion(), InformacionFamiliarExparejas::mensajesValidacion(), TablaDinamica::mensajesValidacion());
    }
}
