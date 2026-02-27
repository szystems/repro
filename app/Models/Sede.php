<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sede extends Model
{
    /** @use HasFactory<\Database\Factories\SedeFactory> */
    use HasFactory;

    protected $table = 'sedes';

    protected $fillable = [
        'nombre',
        'direccion',
        'telefono',
        'capacidad',
        'estado',
        'notas',
    ];

    protected function casts(): array
    {
        return [
            'capacidad' => 'integer',
            'estado'    => 'integer',
        ];
    }

    /** Evaluados asignados a esta sede. */
    public function evaluados(): HasMany
    {
        return $this->hasMany(EvaluadoOrden::class, 'sede_id');
    }

    /** Scope: solo sedes activas. */
    public function scopeActivas($query)
    {
        return $query->where('estado', 1);
    }

    /**
     * Verifica si hay traslape: misma sede + mismo poligrafista + misma fecha = conflicto.
     * Reglas de negocio:
     *   - Misma sede + misma hora + mismo evaluador  → NO permitido
     *   - Misma hora + diferente sede                → Permitido
     *   - Misma sede + misma hora + diferente eval.  → Permitido
     *
     * @param int      $poligrafistaId
     * @param string   $fechaProgramada  formato Y-m-d H:i:s
     * @param int|null $excludeEvaluadoId  excluir al editar
     */
    public function tieneTraslape(int $poligrafistaId, string $fechaProgramada, ?int $excludeEvaluadoId = null): bool
    {
        $query = $this->evaluados()
            ->where('poligrafista_id', $poligrafistaId)
            ->where('fecha_programada', $fechaProgramada)
            ->whereNotIn('estado_evaluacion', ['cancelado', 'desistio', 'inasistencia']);

        if ($excludeEvaluadoId) {
            $query->where('id', '<>', $excludeEvaluadoId);
        }

        return $query->exists();
    }
}
