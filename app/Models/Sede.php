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
     * Verifica si hay traslape de horario usando solapamiento de rangos.
     *
     * Reglas de negocio:
     *   - Misma sede + mismo poligrafista + rango que se cruza → NO permitido
     *   - Diferente sede o diferente poligrafista             → Permitido
     *   - Citas canceladas/desistió/inasistencia              → Ignoradas
     *
     * Fórmula de solapamiento: existente.inicio < nuevo.fin AND existente.fin > nuevo.inicio
     *
     * @param int      $poligrafistaId
     * @param string   $inicio           Fecha-hora inicio (Y-m-d H:i:s)
     * @param string   $fin              Fecha-hora fin (Y-m-d H:i:s)
     * @param int|null $excludeEvaluadoId  Excluir al reprogramar
     */
    public function tieneTraslape(int $poligrafistaId, string $inicio, string $fin, ?int $excludeEvaluadoId = null): bool
    {
        $query = $this->evaluados()
            ->where('poligrafista_id', $poligrafistaId)
            ->where('fecha_programada', '<', $fin)
            ->where('fecha_hora_fin', '>', $inicio)
            ->whereNotIn('estado_evaluacion', ['cancelado', 'desistio', 'inasistencia']);

        if ($excludeEvaluadoId) {
            $query->where('id', '<>', $excludeEvaluadoId);
        }

        return $query->exists();
    }

    /**
     * Obtener evaluados programados para un día específico en esta sede.
     *
     * @param string $fecha  Formato Y-m-d
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function evaluadosDelDia(string $fecha)
    {
        return $this->evaluados()
            ->enDia($fecha)
            ->with(['poligrafo', 'orden.empresa'])
            ->orderBy('fecha_programada')
            ->get();
    }
}
