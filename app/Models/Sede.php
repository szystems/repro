<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Sede extends Model
{
    /** @use HasFactory<\Database\Factories\SedeFactory> */
    use HasFactory;

    protected $table = 'sedes';

    protected $fillable = [
        'nombre',
        'direccion',
        'telefono',
        'whatsapp',
        'enlace_maps',
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

    public const CACHE_WHATSAPP = 'sedes_whatsapp_activas';

    protected static function booted(): void
    {
        static::saved(static fn () => Cache::forget(self::CACHE_WHATSAPP));
        static::deleted(static fn () => Cache::forget(self::CACHE_WHATSAPP));
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
     * Verifica si la sede alcanzó su capacidad máxima en un rango horario.
     *
     * Fase 19: el límite es por sede (campo capacidad), no por poligrafista.
     * Un mismo evaluador puede tener varias citas simultáneas si la sede tiene cupo.
     *
     * Fórmula de solapamiento: existente.inicio < nuevo.fin AND existente.fin > nuevo.inicio
     *
     * @param int      $poligrafistaId     Ignorado (compatibilidad de firma)
     * @param string   $inicio             Fecha-hora inicio (Y-m-d H:i:s)
     * @param string   $fin                Fecha-hora fin (Y-m-d H:i:s)
     * @param int|null $excludeEvaluadoId  Excluir al reprogramar
     */
    public function tieneTraslape(?int $poligrafistaId, string $inicio, string $fin, ?int $excludeEvaluadoId = null): bool
    {
        $query = $this->evaluados()
            ->whereNotNull('fecha_programada')
            ->where('fecha_programada', '<', $fin)
            ->where('fecha_hora_fin', '>', $inicio)
            ->whereNotIn('estado_programacion', ['cancelado', 'desistio', 'inasistencia'])
            ->whereNotIn('estado_evaluacion', ['cancelado']);

        if ($excludeEvaluadoId) {
            $query->where('id', '<>', $excludeEvaluadoId);
        }

        $ocupadas = $query->count();
        $capacidad = max(1, (int) $this->capacidad);

        return $ocupadas >= $capacidad;
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
