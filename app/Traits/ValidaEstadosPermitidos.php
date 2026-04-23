<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

/**
 * Valida que los campos de estado solo tomen valores del catálogo declarado.
 *
 * El modelo que use este trait debe implementar el método estático:
 *
 *     public static function camposEstadoValidos(): array
 *     {
 *         return [
 *             'estado' => ['solicitud', 'validacion', ...],
 *         ];
 *     }
 *
 * Si alguien intenta asignar un valor fuera del catálogo antes de save(),
 * se lanza ValidationException, evitando corrupción de datos.
 */
trait ValidaEstadosPermitidos
{
    public static function bootValidaEstadosPermitidos(): void
    {
        static::saving(function (Model $model): void {
            if (!method_exists($model, 'camposEstadoValidos')) {
                return;
            }

            /** @var array<string, string[]> $campos */
            $campos = $model::camposEstadoValidos();

            foreach ($campos as $campo => $valoresPermitidos) {
                $valor = $model->getAttribute($campo);
                if ($valor === null || $valor === '') {
                    continue;
                }
                if (!in_array($valor, $valoresPermitidos, true)) {
                    throw ValidationException::withMessages([
                        $campo => "Valor '{$valor}' no es un estado válido para el campo '{$campo}' del modelo " . class_basename($model) . '.',
                    ]);
                }
            }
        });
    }
}
