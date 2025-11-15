<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Orden extends Model
{
    use HasFactory;
    /**
     * The table associated with the model.
     */
    protected $table = 'ordenes';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'empresa_id',
        'codigo_orden',
        'cantidad_evals',
        'estado',
        'creado_por',
        'fecha_solicitud',
        'fecha_limite',
        'observaciones',
        'instrucciones_generales',
        'prioridad',
        'documentos_adjuntos',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'fecha_solicitud' => 'date',
        'fecha_limite' => 'date',
        'documentos_adjuntos' => 'array',
    ];

    /**
     * Boot method for model events
     */
    protected static function boot(): void
    {
        parent::boot();
        
        // Generar código único automáticamente al crear
        static::creating(function ($orden) {
            if (empty($orden->codigo_orden)) {
                $orden->codigo_orden = static::generarCodigoUnico();
            }
        });
    }

    /**
     * Generar código único para la orden
     */
    public static function generarCodigoUnico(): string
    {
        $prefijo = 'ORD-' . date('Y') . '-';
        $numero = static::where('codigo_orden', 'LIKE', $prefijo . '%')
            ->count() + 1;
        
        return $prefijo . str_pad($numero, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Relación con empresa
     */
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    /**
     * Relación con usuario que creó la orden
     */
    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    /**
     * Relación con poligrafista asignado
     */
    public function poligrafista(): BelongsTo
    {
        return $this->belongsTo(User::class, 'poligrafista_id');
    }

    /**
     * Alias para poligrafista (compatibilidad)
     */
    public function poligrafo(): BelongsTo
    {
        return $this->poligrafista();
    }

    /**
     * Relación con evaluados de la orden
     */
    public function evaluados(): HasMany
    {
        return $this->hasMany(EvaluadoOrden::class);
    }

    /**
     * Scopes para consultas frecuentes
     */
    public function scopePorEstado($query, string $estado)
    {
        return $query->where('estado', $estado);
    }

    public function scopePorEmpresa($query, int $empresaId)
    {
        return $query->where('empresa_id', $empresaId);
    }

    public function scopeCreatedBy($query, int $userId)
    {
        return $query->where('creado_por', $userId);
    }

    /**
     * Métodos auxiliares
     */
    public function getTipoServicioHumanAttribute(): string
    {
        return match ($this->tipo_servicio) {
            'poligrafo' => 'Prueba de Polígrafo',
            'vsa' => 'Análisis de Estrés Vocal (VSA)',
            'socioeconomico' => 'Estudio Socioeconómico',
            default => $this->tipo_servicio,
        };
    }

    public function getTipoFormularioHumanAttribute(): string
    {
        return match ($this->tipo_formulario) {
            'preempleo' => 'Pre-empleo',
            'periodica' => 'Periódica',
            'especifica' => 'Específica',
            default => $this->tipo_formulario,
        };
    }

    public function getEstadoHumanAttribute(): string
    {
        return match ($this->estado) {
            'solicitud' => 'Solicitud',
            'autorizacion' => 'Esperando Autorización',
            'requisito' => 'Pendiente Requisitos',
            'programacion' => 'En Programación',
            'en_proceso' => 'En Proceso',
            'analisis' => 'En Análisis',
            'preliminar' => 'Informe Preliminar',
            'final' => 'Informe Final',
            'entregado' => 'Entregado',
            'cancelado' => 'Cancelado',
            default => $this->estado,
        };
    }

    /**
     * Verificar si puede cambiar a un estado específico
     */
    public function puedeTransicionarA(string $nuevoEstado): bool
    {
        $transicionesPermitidas = [
            'solicitud' => ['autorizacion', 'cancelado'],
            'autorizacion' => ['requisito', 'programacion', 'cancelado'],
            'requisito' => ['programacion', 'cancelado'],
            'programacion' => ['en_proceso', 'cancelado'],
            'en_proceso' => ['analisis', 'programacion'], // Puede reprogramar
            'analisis' => ['preliminar', 'en_proceso'],
            'preliminar' => ['final', 'analisis'],
            'final' => ['entregado'],
            'entregado' => [], // Estado final
            'cancelado' => [], // Estado final
        ];

        return in_array($nuevoEstado, $transicionesPermitidas[$this->estado] ?? []);
    }

    /**
     * Cambiar estado con validación
     */
    public function cambiarEstado(string $nuevoEstado): bool
    {
        if (!$this->puedeTransicionarA($nuevoEstado)) {
            return false;
        }

        $this->estado = $nuevoEstado;
        return $this->save();
    }
}
