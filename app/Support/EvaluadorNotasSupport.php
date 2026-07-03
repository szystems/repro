<?php

namespace App\Support;

use App\Models\EvaluadorNota;
use App\Models\User;

/**
 * E1.8 — Notas internas del evaluador (solo REPRO/ADMIN).
 */
class EvaluadorNotasSupport
{
    public static function puedeGestionar(?User $user): bool
    {
        return $user !== null && ($user->isAdmin() || $user->isRepro());
    }

    /**
     * @return array<string, string> slug => contenido
     */
    public static function mapaPorSeccion(int $evaluadoOrdenId): array
    {
        return EvaluadorNota::query()
            ->where('evaluado_orden_id', $evaluadoOrdenId)
            ->where('campo', '')
            ->pluck('contenido', 'seccion')
            ->all();
    }

    /**
     * @param  array<string, mixed>  $notasInput
     */
    public static function guardarDesdeRequest(int $evaluadoOrdenId, array $notasInput, ?int $userId): void
    {
        foreach ($notasInput as $seccion => $contenido) {
            if (! is_string($seccion) || $seccion === '') {
                continue;
            }

            if (is_array($contenido)) {
                foreach ($contenido as $campo => $texto) {
                    if (! is_string($texto)) {
                        continue;
                    }
                    EvaluadorNota::guardarNota(
                        $evaluadoOrdenId,
                        $seccion,
                        is_string($campo) ? $campo : '',
                        $texto !== '' ? $texto : null,
                        $userId
                    );
                }
                continue;
            }

            if (! is_string($contenido)) {
                continue;
            }

            EvaluadorNota::guardarNota(
                $evaluadoOrdenId,
                $seccion,
                '',
                $contenido !== '' ? $contenido : null,
                $userId
            );
        }
    }
}
