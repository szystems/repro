<?php

namespace App\Http\Requests\Cuestionario;

use App\Http\Requests\Cuestionario\Concerns\EtiquetasValidacionCuestionario;
use App\Support\AntecedentesJudiciales;
use Illuminate\Foundation\Http\FormRequest;

class AntecedentesRecientesRequest extends FormRequest
{
    use EtiquetasValidacionCuestionario;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge(
            AntecedentesJudiciales::reglasValidacion(),
            [
                'informacion_adicional_final' => 'nullable|string|max:2000',
            ]
        );
    }
}
