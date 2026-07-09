<?php

namespace App\Support;

use App\Http\Requests\Cuestionario\SocioeconomicoComplementariaRequest;
use App\Http\Requests\Cuestionario\AntecedentesRequest;
use App\Http\Requests\Cuestionario\DatosPersonalesRequest;
use App\Http\Requests\Cuestionario\HistorialLaboralRequest;
use App\Http\Requests\Cuestionario\InformacionFamiliarRequest;
use App\Http\Requests\Cuestionario\SituacionEconomicaRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Validación permisiva y normalización para autosave / borrador parcial (E1.3).
 */
class CuestionarioAutosave
{
    /**
     * @return array<string, mixed>
     */
    public static function validarParcial(Request $request, int $numero, string $tipoFormulario): array
    {
        if ($numero === 2 && $request->has('convive_con') && is_array($request->input('convive_con'))) {
            $request->merge([
                'convive_con' => InformacionFamiliarPadres::conviveConParaAlmacenar($request->input('convive_con')) ?? '',
            ]);
        }

        $request->merge(
            TablaDinamica::mergeTablasNormalizadas($request->all(), $numero, $tipoFormulario)
        );

        $reglas = self::reglasPermisivas($numero, $tipoFormulario);

        if ($reglas === []) {
            return $request->except(['_token', 'action']);
        }

        $validator = Validator::make($request->all(), $reglas);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    /**
     * @return array<string, mixed>
     */
    public static function reglasPermisivas(int $numero, string $tipoFormulario): array
    {
        $clase = match (true) {
            $numero === 6 && $tipoFormulario === 'socioeconomico' => SocioeconomicoComplementariaRequest::class,
            $numero === 1 => DatosPersonalesRequest::class,
            $numero === 2 => InformacionFamiliarRequest::class,
            $numero === 3 => HistorialLaboralRequest::class,
            $numero === 4 => SituacionEconomicaRequest::class,
            $numero === 5 => AntecedentesRequest::class,
            default => null,
        };

        if ($clase === null) {
            return [];
        }

        /** @var \Illuminate\Foundation\Http\FormRequest $instancia */
        $instancia = new $clase;
        $reglas = $instancia->rules();

        return self::suavizarReglas($reglas, $numero, $tipoFormulario);
    }

    /**
     * Convierte reglas estrictas en permisivas (nullable, sin mínimos obligatorios).
     *
     * @param  array<string, mixed>  $reglas
     * @return array<string, mixed>
     */
    private static function suavizarReglas(array $reglas, int $numero, string $tipoFormulario): array
    {
        $suaves = [];

        foreach ($reglas as $campo => $regla) {
            if ($campo === 'foto_candidato') {
                $suaves[$campo] = 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120';
                continue;
            }

            if ($campo === 'foto_candidato_existente') {
                $suaves[$campo] = 'nullable|in:1';
                continue;
            }

            if ($campo === 'fecha_nacimiento') {
                $suaves[$campo] = 'nullable|date|before:today';
                continue;
            }

            if ($campo === 'hijos' || $campo === 'hermanos' || $campo === 'empleos'
                || $campo === 'formacion_academica' || $campo === 'deudas'
                || $campo === 'tatuajes' || $campo === 'perforaciones'
                || $campo === 'referencias_familiares' || $campo === 'referencias_personales'
                || $campo === 'referencias_vecinales' || $campo === 'referencias_laborales'
                || $campo === 'bienes' || $campo === 'presupuesto') {
                $suaves[$campo] = 'nullable|array';
                continue;
            }

            if (is_array($regla)) {
                $partes = collect($regla)
                    ->reject(fn ($r) => $r === 'required'
                        || (is_string($r) && str_starts_with($r, 'exclude_unless:'))
                        || (is_string($r) && str_starts_with($r, 'required_if:')))
                    ->values()
                    ->all();
                array_unshift($partes, 'nullable');
                $suaves[$campo] = $partes;
                continue;
            }

            $texto = (string) $regla;
            $texto = preg_replace('/\brequired\b/', 'nullable', $texto) ?? $texto;
            $texto = preg_replace('/\brequired_if:[^|]+\|?/', '', $texto) ?? $texto;
            $texto = preg_replace('/exclude_unless:[^|]+\|/', '', $texto) ?? $texto;
            $texto = preg_replace('/\|min:1(?=\||$)/', '', $texto) ?? $texto;

            if (! str_contains($texto, 'nullable')) {
                $texto = 'nullable|'.$texto;
            }

            $suaves[$campo] = $texto;
        }

        return $suaves;
    }
}
