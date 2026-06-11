<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para el historial de cambios de estados.
 * 
 * Fase 18: Registra todos los cambios de los 4 campos de estado
 * independientes (formulario, programación, evaluación, orden)
 * más cambios en modalidad.
 */
class EstadoHistorial extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla
     */
    protected $table = 'estado_historial';

    /**
     * Campos asignables en masa
     */
    protected $fillable = [
        'evaluado_orden_id',
        'orden_id',
        'campo',
        'estado_anterior',
        'estado_nuevo',
        'observacion',
        'user_id',
    ];

    /**
     * Campos de fecha
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    // ========================================
    // Relaciones
    // ========================================

    /**
     * Evaluado asociado al cambio
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function evaluadoOrden()
    {
        return $this->belongsTo(EvaluadoOrden::class, 'evaluado_orden_id');
    }

    /**
     * Orden asociada al cambio
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function orden()
    {
        return $this->belongsTo(Orden::class, 'orden_id');
    }

    /**
     * Usuario que realizó el cambio
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ========================================
    // Métodos de consulta
    // ========================================

    /**
     * Scope para filtrar por evaluado
     */
    public function scopeDeEvaluado($query, int $evaluadoOrdenId)
    {
        return $query->where('evaluado_orden_id', $evaluadoOrdenId);
    }

    /**
     * Scope para filtrar por orden
     */
    public function scopeDeOrden($query, int $ordenId)
    {
        return $query->where('orden_id', $ordenId);
    }

    /**
     * Scope para filtrar por campo específico
     */
    public function scopeDeCampo($query, string $campo)
    {
        return $query->where('campo', $campo);
    }

    /**
     * Scope para ordenar por más reciente
     */
    public function scopeMasRecientes($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Obtener el historial completo de un evaluado, ordenado cronológicamente
     * 
     * @param int $evaluadoOrdenId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function obtenerHistorialEvaluado(int $evaluadoOrdenId)
    {
        return self::deEvaluado($evaluadoOrdenId)
            ->with('usuario:id,name')
            ->masRecientes()
            ->get();
    }

    /**
     * Obtener el historial de un campo específico
     * 
     * @param int $evaluadoOrdenId
     * @param string $campo
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function obtenerHistorialCampo(int $evaluadoOrdenId, string $campo)
    {
        return self::deEvaluado($evaluadoOrdenId)
            ->deCampo($campo)
            ->with('usuario:id,name')
            ->masRecientes()
            ->get();
    }
}
