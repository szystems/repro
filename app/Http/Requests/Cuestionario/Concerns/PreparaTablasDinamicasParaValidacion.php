<?php

namespace App\Http\Requests\Cuestionario\Concerns;

use App\Models\EvaluadoOrden;
use App\Support\TablaDinamica;

/**
 * Elimina filas vacías de tablas dinámicas antes de validar (todas las secciones con tablas).
 */
trait PreparaTablasDinamicasParaValidacion
{
    protected function numeroSeccionTablasDinamicas(): int
    {
        return 0;
    }

    protected function prepararTablasDinamicas(): void
    {
        $numero = $this->numeroSeccionTablasDinamicas();
        if ($numero < 1) {
            return;
        }

        $tipo = $this->resolverTipoFormularioCuestionario();

        $this->merge(
            TablaDinamica::mergeTablasNormalizadas(
                $this->all(),
                $numero,
                $tipo
            )
        );
    }

    protected function resolverTipoFormularioCuestionario(): string
    {
        // Preferir el tipo inyectado por el controlador / autosave (coincide con la UI).
        $desdeAtributo = $this->attributes->get('tipo_formulario_cuestionario');
        if (is_string($desdeAtributo) && $desdeAtributo !== '') {
            return $desdeAtributo;
        }

        $token = $this->route('token');

        if (! is_string($token) || $token === '') {
            return 'preempleo';
        }

        $evaluado = EvaluadoOrden::query()
            ->with('cuestionario')
            ->where('token_unico', $token)
            ->first();

        // El tipo del cuestionario es el que ve el candidato (p. ej. omite Hermanos en periódica).
        return $evaluado?->cuestionario?->tipo_formulario
            ?? $evaluado?->tipoFormularioCuestionario()
            ?? 'preempleo';
    }
}
