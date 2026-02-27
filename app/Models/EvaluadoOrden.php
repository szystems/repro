<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\Sede;

/**
 * Modelo para evaluados vinculados a órdenes.
 * 
 * IMPORTANTE: Los evaluados NO son usuarios del sistema.
 * - No tienen cuenta ni password
 * - No pueden hacer login
 * - Acceden mediante token único temporal
 * - Se identifican por DPI (único en Guatemala)
 */
class EvaluadoOrden extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla
     */
    protected $table = 'evaluados_orden';

    /**
     * Campos asignables en masa
     */
    protected $fillable = [
        'orden_id',
        'nombre',
        'apellidos',
        'email',
        'telefono',
        'celular',
        'dpi',
        'tipo_documento',
        'tipo_servicio',
        'tipo_formulario',
        'poligrafista_id',
        'sede_id',
        'fecha_programada',
        'fecha_realizada',
        'estado_evaluacion',
        'resultado',
        'notas_poligrafo',
        'token_unico',
        'token_expira_at',
        'cuestionario_completado',
        'cuestionario_completado_at',
        'ip_acceso',
        'observaciones',
        'notas',
    ];

    /**
     * Campos de fecha
     */
    protected $dates = [
        'fecha_programada',
        'fecha_realizada',
        'token_expira_at',
        'token_usado_at',
        'completado_at',
        'notificado_at',
        'created_at',
        'updated_at',
    ];

    /**
     * Casts de atributos
     */
    protected function casts(): array
    {
        return [
            'cuestionario_completado' => 'boolean',
            'notificado' => 'boolean',
            'intentos_acceso' => 'integer',
            'token_expira_at' => 'datetime',
            'token_usado_at' => 'datetime',
            'completado_at' => 'datetime',
            'notificado_at' => 'datetime',
        ];
    }

    /**
     * Atributos ocultos (para serialización)
     */
    protected $hidden = [
        'token_unico', // No exponer token en APIs
        'firma_digital', // Dato sensible
        'notas', // Notas internas de Repro
    ];

    // ========================================
    // Relaciones
    // ========================================

    /**
     * Orden a la que pertenece este evaluado
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function orden()
    {
        return $this->belongsTo(Orden::class, 'orden_id');
    }

    /**
     * Polígrafo asignado a este evaluado específico
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function poligrafo()
    {
        return $this->belongsTo(User::class, 'poligrafista_id');
    }

    /**
     * Alias para poligrafo (compatibilidad)
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function poligrafista()
    {
        return $this->poligrafo();
    }

    /**
     * Sede donde se realiza la evaluación.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sede()
    {
        return $this->belongsTo(Sede::class, 'sede_id');
    }

    /**
     * Cuestionario completado por este evaluado
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function cuestionario()
    {
        return $this->hasOne(Cuestionario::class, 'evaluado_orden_id');
    }

    /**
     * Evaluaciones realizadas a este evaluado
     * TODO: Crear modelo Evaluacion en el siguiente módulo
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    // public function evaluaciones()
    // {
    //     return $this->hasMany(Evaluacion::class, 'evaluado_orden_id');
    // }

    // ========================================
    // Scopes
    // ========================================

    /**
     * Scope: Evaluados con cuestionario pendiente
     */
    public function scopePendientes($query)
    {
        return $query->where('cuestionario_completado', false)
            ->whereNotNull('token_unico')
            ->where('token_expira_at', '>', now());
    }

    /**
     * Scope: Evaluados con cuestionario completado
     */
    public function scopeCompletados($query)
    {
        return $query->where('cuestionario_completado', true);
    }

    /**
     * Scope: Tokens expirados
     */
    public function scopeTokensExpirados($query)
    {
        return $query->where('cuestionario_completado', false)
            ->where('token_expira_at', '<', now());
    }

    /**
     * Scope: Buscar por DPI (historial completo)
     */
    public function scopePorDpi($query, string $dpi)
    {
        return $query->where('dpi', $dpi)->orderBy('created_at', 'desc');
    }

    // ========================================
    // Métodos de Negocio
    // ========================================

    /**
     * Generar token único para este evaluado
     * 
     * @param int $diasExpiracion Días hasta expiración (default: 30)
     * @return string Token generado
     */
    public static function generarToken(int $diasExpiracion = 30): string
    {
        do {
            $token = Str::random(64);
        } while (self::where('token_unico', $token)->exists());

        return $token;
    }

    /**
     * Verificar si el token es válido
     * 
     * @return bool
     */
    public function tokenValido(): bool
    {
        return !$this->cuestionario_completado 
            && $this->token_expira_at > now()
            && !is_null($this->token_unico);
    }

    /**
     * Marcar cuestionario como completado
     * 
     * @param string|null $firmaDigital Firma en base64
     * @param string|null $ip IP del usuario
     * @param string|null $userAgent User agent del navegador
     * @return bool
     */
    public function completarCuestionario(?string $firmaDigital = null, ?string $ip = null, ?string $userAgent = null): bool
    {
        return $this->update([
            'cuestionario_completado' => true,
            'completado_at' => now(),
            'firma_digital' => $firmaDigital,
            'ip_completado' => $ip ?? request()->ip(),
            'user_agent' => $userAgent ?? request()->userAgent(),
        ]);
    }

    /**
     * Registrar intento de acceso al formulario
     * 
     * @return void
     */
    public function registrarAcceso(): void
    {
        $this->increment('intentos_acceso');
        
        if (is_null($this->token_usado_at)) {
            $this->update(['token_usado_at' => now()]);
        }
    }

    /**
     * Obtener URL pública del cuestionario
     * 
     * @return string
     */
    public function getUrlCuestionario(): string
    {
        return route('cuestionario.show', ['token' => $this->token_unico]);
    }

    /**
     * Verificar si el token ha expirado
     * 
     * @return bool
     */
    public function tokenExpirado(): bool
    {
        return $this->token_expira_at < now();
    }

    /**
     * Obtener historial completo de evaluaciones previas por DPI
     * Solo accesible por Admin y Repro
     * 
     * @param string $dpi
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function historialPorDpi(string $dpi)
    {
        return self::where('dpi', $dpi)
            ->with(['orden.empresa', 'cuestionario']) // TODO: agregar 'evaluaciones' cuando se cree el modelo
            ->orderBy('created_at', 'desc')
            ->get();
    }

    // ========================================
    // Accessors & Mutators
    // ========================================

    /**
     * Formatear DPI al guardarlo (solo números)
     */
    protected function setDpiAttribute($value): void
    {
        $this->attributes['dpi'] = preg_replace('/[^0-9]/', '', $value);
    }

    /**
     * Accessor: Estado del cuestionario en texto
     */
    public function getEstadoCuestionarioAttribute(): string
    {
        if ($this->cuestionario_completado) {
            return 'Completado';
        }
        
        if ($this->tokenExpirado()) {
            return 'Expirado';
        }
        
        if ($this->intentos_acceso > 0) {
            return 'En Progreso';
        }
        
        return 'Pendiente';
    }

    /**
     * Accessor: Nombre corto (primeros 2 nombres)
     */
    public function getNombreCortoAttribute(): string
    {
        $nombres = explode(' ', $this->nombre);
        return implode(' ', array_slice($nombres, 0, 2));
    }

    // ========================================
    // Métodos para nuevos campos específicos
    // ========================================

    /**
     * Verificar si el evaluado está programado
     */
    public function estaProgramado(): bool
    {
        return $this->estado_evaluacion === 'programado' && !is_null($this->fecha_programada);
    }

    /**
     * Verificar si la evaluación está completada
     */
    public function evaluacionCompletada(): bool
    {
        return $this->estado_evaluacion === 'completado' && !is_null($this->fecha_realizada);
    }

    /**
     * Obtener el nombre del polígrafo asignado
     */
    public function getNombrePoligrafoAttribute(): ?string
    {
        return $this->poligrafo ? $this->poligrafo->name : null;
    }

    /**
     * Obtener texto del tipo de servicio
     */
    public function getTipoServicioTextoAttribute(): string
    {
        return match($this->tipo_servicio) {
            'poligrafo' => 'Polígrafo',
            'vsa' => 'VSA (Voice Stress Analysis)',
            'socioeconomico' => 'Socioeconómico',
            default => 'No definido'
        };
    }

    /**
     * Obtener texto del tipo de formulario
     */
    public function getTipoFormularioTextoAttribute(): string
    {
        return match($this->tipo_formulario) {
            'preempleo' => 'Pre-empleo',
            'periodica' => 'Periódica',
            'especifica' => 'Específica',
            default => 'No definido'
        };
    }

    /**
     * Obtener texto del estado de evaluación
     */
    public function getEstadoEvaluacionTextoAttribute(): string
    {
        return match($this->estado_evaluacion) {
            'pendiente' => 'Pendiente',
            'contactado' => 'Contactado',
            'programado' => 'Programado',
            'en_proceso' => 'En Proceso',
            'completado' => 'Completado',
            'cancelado' => 'Cancelado',
            'reprogramado' => 'Reprogramado',
            default => 'Estado desconocido'
        };
    }

    /**
     * Obtener texto del resultado
     */
    public function getResultadoTextoAttribute(): string
    {
        return match($this->resultado) {
            'pendiente' => 'Pendiente',
            // Polígrafo / VSA
            'aprobado' => 'Aprobado / Sin Observaciones',
            'aprobado_con_obs' => 'Aprobado / Con Observación Leve',
            'aprobado_excepcion' => 'Aprobado con Excepción',
            'no_aprobado' => 'No Aprobado / Indicación de Mentira',
            'inconcluso' => 'Inconcluso',
            // Socioeconómico
            'tipo_a' => 'Tipo A',
            'a_condicionado' => 'A - Condicionado',
            'tipo_b' => 'Tipo B',
            'tipo_c' => 'Tipo C',
            default => 'Sin resultado'
        };
    }

    /**
     * Obtener color del badge según resultado (clasificación REPRO)
     */
    public function getResultadoColorAttribute(): string
    {
        return match($this->resultado) {
            'pendiente' => 'secondary',
            // Polígrafo / VSA
            'aprobado' => 'success',             // Verde
            'aprobado_con_obs' => 'warning',     // Amarillo
            'aprobado_excepcion' => 'warning',   // Amarillo
            'no_aprobado' => 'danger',           // Rojo
            'inconcluso' => 'secondary',
            // Socioeconómico
            'tipo_a' => 'success',               // Verde
            'a_condicionado' => 'warning',       // Amarillo
            'tipo_b' => 'orange',                // Naranja
            'tipo_c' => 'danger',                // Rojo
            default => 'secondary'
        };
    }

    /**
     * Obtener opciones de resultado según tipo de servicio
     */
    public static function resultadosPorTipoServicio(string $tipoServicio): array
    {
        if ($tipoServicio === 'socioeconomico') {
            return [
                'pendiente' => 'Pendiente',
                'tipo_a' => 'Tipo A (cumple requisitos)',
                'a_condicionado' => 'A - Condicionado (info pendiente)',
                'tipo_b' => 'Tipo B (requiere análisis)',
                'tipo_c' => 'Tipo C (no cumple criterios)',
                'inconcluso' => 'Inconcluso',
            ];
        }

        // Polígrafo y VSA
        return [
            'pendiente' => 'Pendiente',
            'aprobado' => 'Aprobado / Sin Observaciones',
            'aprobado_con_obs' => 'Aprobado / Con Observación Leve',
            'aprobado_excepcion' => 'Aprobado con Excepción',
            'no_aprobado' => 'No Aprobado / Indicación de Mentira',
            'inconcluso' => 'Inconcluso',
        ];
    }

    /**
     * Programar evaluación
     */
    public function programarEvaluacion(string $fecha, int $poligrafistaid): bool
    {
        $this->fecha_programada = $fecha;
        $this->poligrafista_id = $poligrafistaid;
        $this->estado_evaluacion = 'programado';
        return $this->save();
    }

    /**
     * Marcar evaluación como completada
     */
    public function completarEvaluacion(string $resultado, ?string $notas = null): bool
    {
        $this->fecha_realizada = now();
        $this->estado_evaluacion = 'completado';
        $this->resultado = $resultado;
        if ($notas) {
            $this->notas_poligrafo = $notas;
        }
        return $this->save();
    }
}
