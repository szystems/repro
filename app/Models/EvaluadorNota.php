<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Fase F (formularios) — E1.8.
 *
 * Espacio interno del evaluador (solo REPRO/ADMIN). Almacena observaciones,
 * análisis y conclusiones por sección, separadas de las respuestas del
 * candidato, para incorporarse luego al informe final.
 */
class EvaluadorNota extends Model
{
    protected $table = 'evaluador_notas';

    protected $fillable = [
        'evaluado_orden_id',
        'seccion',
        'campo',
        'contenido',
        'user_id',
    ];

    public function evaluadoOrden(): BelongsTo
    {
        return $this->belongsTo(EvaluadoOrden::class, 'evaluado_orden_id');
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Crear o actualizar una nota interna del evaluador.
     */
    public static function guardarNota(int $evaluadoOrdenId, string $seccion, ?string $campo, ?string $contenido, ?int $userId = null): self
    {
        return static::updateOrCreate(
            [
                'evaluado_orden_id' => $evaluadoOrdenId,
                'seccion' => $seccion,
                'campo' => $campo ?? '',
            ],
            [
                'contenido' => $contenido,
                'user_id' => $userId,
            ]
        );
    }
}
