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
        'tipo_creador',
        'fecha_solicitud',
        'fecha_limite',
        'observaciones_internas',
        'instrucciones_generales',
        'requerimientos_generales',
        'prioridad',
        'resultados_visibles_empresa',
        'documentos_adjuntos',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'fecha_solicitud' => 'date',
        'fecha_limite' => 'date',
        'documentos_adjuntos' => 'array',
        'resultados_visibles_empresa' => 'boolean',
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
            'validacion' => 'Validación',
            'registrado' => 'Registrado',
            'programacion' => 'Programación',
            'en_proceso' => 'En Proceso',
            'operaciones' => 'En Operaciones',
            'analisis' => 'En Análisis',
            'preliminar' => 'Resultado Preliminar',
            'final' => 'Resultado Final',
            'entregado' => 'Entregado',
            'cancelado' => 'Cancelado',
            default => $this->estado,
        };
    }

    /**
     * Obtener color del badge según estado
     */
    public function getEstadoColorAttribute(): string
    {
        return match ($this->estado) {
            'solicitud' => 'secondary',
            'validacion' => 'info',
            'registrado' => 'info',
            'programacion' => 'primary',
            'en_proceso' => 'warning',
            'operaciones' => 'warning',
            'analisis' => 'orange',
            'preliminar' => 'orange',
            'final' => 'orange',
            'entregado' => 'success',
            'cancelado' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Verificar si los resultados pueden ser vistos por la empresa
     * Requiere: resultados_visibles_empresa = true Y estado = entregado
     */
    public function resultadosDisponiblesParaEmpresa(): bool
    {
        return $this->resultados_visibles_empresa && $this->estado === 'entregado';
    }

    /**
     * Verificar si puede cambiar a un estado específico
     * Los administradores pueden saltar estados si es necesario
     */
    public function puedeTransicionarA(string $nuevoEstado): bool
    {
        // Estados válidos del sistema
        $estadosValidos = array_keys(self::estadosDisponibles());
        
        // Si el nuevo estado no es válido, rechazar
        if (!in_array($nuevoEstado, $estadosValidos)) {
            return false;
        }
        
        // Si ya está en ese estado, no permitir
        if ($this->estado === $nuevoEstado) {
            return false;
        }
        
        // Estados finales no pueden cambiar
        if ($this->estado === 'entregado') {
            return false;
        }

        // Transiciones recomendadas (flujo normal)
        // Admin puede saltar pasos, pero no ir hacia atrás de entregado
        // y cancelado solo puede ir a solicitud (reactivar)
        $transicionesLogicas = [
            'solicitud'    => ['validacion', 'registrado', 'programacion', 'cancelado'],
            'validacion'   => ['registrado', 'programacion', 'cancelado'],
            'registrado'   => ['programacion', 'cancelado'],
            'programacion' => ['en_proceso', 'cancelado'],
            'en_proceso'   => ['operaciones', 'analisis', 'completado', 'cancelado'],
            'operaciones'  => ['analisis', 'cancelado'],
            'analisis'     => ['preliminar', 'final', 'entregado', 'cancelado'],
            'preliminar'   => ['final', 'entregado', 'cancelado'],
            'final'        => ['entregado', 'cancelado'],
            'cancelado'    => ['solicitud'], // reactivar
        ];
        
        // Flexible: si hay transiciones definidas, verificar; si no, permitir
        if (isset($transicionesLogicas[$this->estado])) {
            return in_array($nuevoEstado, $transicionesLogicas[$this->estado]);
        }
        
        return true;
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

    /**
     * Obtener todos los estados válidos con sus etiquetas humanas.
     *
     * @return array<string, string>
     */
    public static function estadosDisponibles(): array
    {
        return [
            'solicitud' => 'Solicitud',
            'validacion' => 'Validación',
            'registrado' => 'Registrado',
            'programacion' => 'Programación',
            'en_proceso' => 'En Proceso',
            'operaciones' => 'En Operaciones',
            'analisis' => 'En Análisis',
            'preliminar' => 'Resultado Preliminar',
            'final' => 'Resultado Final',
            'entregado' => 'Entregado',
            'cancelado' => 'Cancelado',
        ];
    }
}
