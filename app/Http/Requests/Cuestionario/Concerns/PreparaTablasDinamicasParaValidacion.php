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
        $token = $this->route('token');

        if (! is_string($token) || $token === '') {
            return 'preempleo';
        }

        $evaluado = EvaluadoOrden::query()
            ->where('token_unico', $token)
            ->first();

        return $evaluado?->tipoFormularioCuestionario() ?? 'preempleo';
    }
}
