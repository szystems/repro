<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use App\Traits\RegistraCambiosEstado;
use App\Traits\ValidaEstadosPermitidos;

class Orden extends Model
{
    use HasFactory;
    use RegistraCambiosEstado;
    use ValidaEstadosPermitidos;
    /**
     * The table associated with the model.
     */
    protected $table = 'ordenes';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'empresa_id',
        'sede_id',
        'codigo_orden',
        'cantidad_evals',
        'estado',
        'creado_por',
        'reclutador_id',
        'confidencial',
        'tipo_creador',
        'fecha_solicitud',
        'fecha_limite',
        'observaciones_internas',
        'instrucciones_generales',
        'requerimientos_generales',
        'prioridad',
        'resultados_visibles_empresa',
        'archivada',
        'archivada_at',
        'archivada_por',
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
        'confidencial' => 'boolean',
        'archivada' => 'boolean',
        'archivada_at' => 'datetime',
        // H-09: PII cifrado en base de datos
        'observaciones_internas' => 'encrypted',
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

        do {
            $ultimo = static::where('codigo_orden', 'LIKE', $prefijo . '%')
                ->max('codigo_orden');

            $numero = $ultimo ? ((int) substr($ultimo, strlen($prefijo)) + 1) : 1;

            $codigo = $prefijo . str_pad($numero, 4, '0', STR_PAD_LEFT);
        } while (static::where('codigo_orden', $codigo)->exists());

        return $codigo;
    }

    /**
     * Relación con empresa
     */
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    /**
     * Relación con sede responsable
     */
    public function sede(): BelongsTo
    {
        return $this->belongsTo(Sede::class);
    }

    /**
     * Relación con usuario que creó la orden
     */
    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    /** Reclutador de la empresa cliente asignado al proceso (Sprint E). */
    public function reclutador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reclutador_id');
    }

    public function archivadaPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'archivada_por');
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

    public function scopeActivas($query)
    {
        return $query->where('archivada', false);
    }

    public function scopeArchivadas($query)
    {
        return $query->where('archivada', true);
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
        // Fase 18: 4 estados simplificados
        return match ($this->estado) {
            'orden_recibida' => 'Orden Recibida',
            'en_proceso'     => 'En Proceso',
            'entregado'      => 'Entregado',
            'cancelado'      => 'Cancelado',
            default          => ucfirst($this->estado ?? ''),
        };
    }

    /**
     * Obtener color del badge según estado
     */
    public function getEstadoColorAttribute(): string
    {
        return match ($this->estado) {
            'orden_recibida' => 'secondary',
            'en_proceso'     => 'primary',
            'entregado'      => 'success',
            'cancelado'      => 'danger',
            default          => 'secondary',
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

        // Fase 18: transiciones del flujo simplificado de 4 estados.
        $transicionesLogicas = [
            'orden_recibida' => ['en_proceso', 'cancelado'],
            'en_proceso'     => ['entregado', 'cancelado'],
            'cancelado'      => ['orden_recibida'], // reactivar
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

        $estadoAnterior = $this->estado;
        $this->estado = $nuevoEstado;
        $resultado = $this->save();

        if ($resultado) {
            $this->registrarCambioEstado('estado', $estadoAnterior, $nuevoEstado);
        }

        return $resultado;
    }

    /**
     * Forzar cambio de estado saltando validación de transiciones.
     * Usar solo para transiciones automáticas del sistema (ej: subir informe final).
     */
    public function forzarEstado(string $nuevoEstado, ?string $observaciones = null): bool
    {
        $estadoAnterior = $this->estado;
        $this->estado = $nuevoEstado;
        $resultado = $this->save();

        if ($resultado && $estadoAnterior !== $nuevoEstado) {
            $this->registrarCambioEstado('estado', $estadoAnterior, $nuevoEstado, $observaciones ?? 'Cambio automático del sistema');
        }

        return $resultado;
    }

    /**
     * Obtener todos los estados válidos con sus etiquetas humanas.
     * Fase 18: Simplificados a 4 estados
     *
     * @return array<string, string>
     */
    public static function estadosDisponibles(): array
    {
        return [
            'orden_recibida' => 'Orden Recibida',
            'en_proceso'     => 'En Proceso',
            'entregado'      => 'Entregado',
            'cancelado'      => 'Cancelado',
        ];
    }

    /**
     * Catálogo de campos de estado protegidos con sus valores permitidos.
     * Usado por el trait ValidaEstadosPermitidos para prevenir corrupción.
     *
     * @return array<string, string[]>
     */
    public static function camposEstadoValidos(): array
    {
        return [
            'estado' => array_keys(self::estadosDisponibles()),
        ];
    }

    /**
     * Fase 18: Recalcular estado de la orden basándose en estados de evaluados.
     * 
     * Lógica:
     * - Si TODOS completados (evaluación) → entregado
     * - Si TODOS cancelados → cancelado
     * - Si al menos uno en proceso/revisión → en_proceso
     * - Sino → orden_recibida
     * 
     * @return bool True si se actualizó el estado
     */
    public function recalcularEstado(): bool
    {
        $evaluados = $this->evaluados;

        if ($evaluados->isEmpty()) {
            // Sin evaluados, mantener orden_recibida
            if ($this->estado !== 'orden_recibida' && $this->estado !== 'cancelado') {
                $this->estado = 'orden_recibida';
                return $this->save();
            }
            return false;
        }

        // Fase 18 — PDF p.1: Entregado cuando todos están en informe_final_enviado, cancelado o desistio
        $estadosTerminales = ['informe_final_enviado', 'cancelado', 'desistio'];
        $estadosActivosProceso = ['en_proceso', 'en_revision', 'resultado_preliminar'];

        $todosCompletados = $evaluados->every(fn($e) => in_array($e->estado_evaluacion, $estadosTerminales));
        $todosCancelados  = $evaluados->every(fn($e) => in_array($e->estado_evaluacion, ['cancelado', 'desistio']));
        $algunoEnProceso  = $evaluados->some(fn($e) => in_array($e->estado_evaluacion, $estadosActivosProceso));

        $nuevoEstado = null;

        // Verificar todosCancelados ANTES de todosCompletados:
        // si todos son cancelado/desistio, también cumplen "terminales", pero la orden debe ser "cancelado"
        if ($todosCancelados) {
            $nuevoEstado = 'cancelado';
        } elseif ($todosCompletados) {
            // Mezcla de informe_final_enviado + cancelado/desistio → entregado
            $nuevoEstado = 'entregado';
        } elseif ($algunoEnProceso) {
            $nuevoEstado = 'en_proceso';
        } else {
            $nuevoEstado = 'orden_recibida';
        }

        // Solo actualizar si cambió
        if ($this->estado !== $nuevoEstado) {
            $estadoAnterior = $this->estado;
            $this->estado = $nuevoEstado;
            $resultado = $this->save();

            if ($resultado) {
                // Registrar cambio en historial
                EstadoHistorial::create([
                    'orden_id' => $this->id,
                    'campo' => 'estado_orden',
                    'estado_anterior' => $estadoAnterior,
                    'estado_nuevo' => $nuevoEstado,
                    'observacion' => 'Recalculado automáticamente basado en evaluados',
                ]);
            }

            return $resultado;
        }

        return false;
    }
}
