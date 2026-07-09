<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\Sede;
use App\Traits\RegistraCambiosEstado;
use App\Traits\ValidaEstadosPermitidos;

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
    use RegistraCambiosEstado;
    use ValidaEstadosPermitidos;

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
        'direccion',
        'dpi',
        'tipo_documento',
        'tipo_servicio',
        'tipo_formulario',
        'poligrafista_id',
        'responsable_id',
        'sede_id',
        'modalidad',
        'fecha_programada',
        'fecha_hora_fin',
        'fecha_realizada',
        'estado_evaluacion',
        'estado_formulario',
        'estado_programacion',
        'resultado',
        'notas_poligrafo',
        'token_unico',
        'token_expira_at',
        'cuestionario_completado',
        'cuestionario_completado_at',
        'completado_at',
        'ip_acceso',
        'observaciones',
        'notas',
        'puesto_evaluar',
        'sede_region_empresa',
        'archivo_resultado_preliminar',
        'texto_informe_preliminar',
        'archivo_resultado_final',
        'resultado_preliminar_at',
        'resultado_final_at',
        'resultado_subido_por',
        'fecha_programada_original',
    ];

    /**
     * Campos de fecha
     */
    protected $dates = [
        'fecha_programada',
        'fecha_programada_original',
        'fecha_hora_fin',
        'fecha_realizada',
        'token_expira_at',
        'token_usado_at',
        'completado_at',
        'notificado_at',
        'resultado_preliminar_at',
        'resultado_final_at',
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
            'fecha_programada' => 'datetime',
            'fecha_programada_original' => 'datetime',
            'fecha_hora_fin' => 'datetime',
            'token_expira_at' => 'datetime',
            'token_usado_at' => 'datetime',
            'completado_at' => 'datetime',
            'notificado_at' => 'datetime',
            'resultado_preliminar_at' => 'datetime',
            'resultado_final_at' => 'datetime',
            // H-09: PII cifrado en base de datos
            'observaciones' => 'encrypted',
            'notas_poligrafo' => 'encrypted',
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
     * Responsable del proceso de evaluación.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function responsable()
    {
        return $this->belongsTo(User::class, 'responsable_id');
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

    /** Tipo de formulario del cuestionario digital (puede diferir del selector en orden para socio). */
    public function tipoFormularioCuestionario(): string
    {
        if ($this->tipo_servicio === 'socioeconomico') {
            return 'socioeconomico';
        }

        return $this->tipo_formulario ?? 'preempleo';
    }

    /**
     * Notas internas del evaluador REPRO (E1.8) — separadas de respuestas del candidato.
     */
    public function evaluadorNotas()
    {
        return $this->hasMany(EvaluadorNota::class, 'evaluado_orden_id');
    }

    /**
     * Documentos adjuntos del evaluado.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function documentos()
    {
        return $this->hasMany(DocumentoEvaluado::class, 'evaluado_orden_id');
    }

    /**
     * Historial de cambios de estados (Fase 18)
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function historialEstados()
    {
        return $this->hasMany(EstadoHistorial::class, 'evaluado_orden_id')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Usuario que subió el archivo de resultado.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function resultadoSubidoPor(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'resultado_subido_por');
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
     * Scope: Evaluados programados (con fecha_programada asignada)
     */
    public function scopeProgramados($query)
    {
        // Fase 18: filtra por estado_programacion, ya no por estado_evaluacion
        return $query->whereNotNull('fecha_programada')
            ->whereNotIn('estado_programacion', ['cancelado', 'desistio']);
    }

    /**
     * Scope: Evaluados pendientes de programar (sin fecha_programada)
     */
    public function scopePendientesProgramar($query)
    {
        // Fase 18: usa estado_programacion para excluir los que ya no necesitan programación
        return $query->whereNull('fecha_programada')
            ->whereNotIn('estado_programacion', ['cancelado', 'desistio', 'proceso_realizado'])
            ->whereNotIn('estado_evaluacion', ['cancelado', 'desistio', 'informe_final_enviado']);
    }

    /**
     * Scope: Evaluados programados en un rango de fechas.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $desde  Formato Y-m-d o Y-m-d H:i:s
     * @param string $hasta  Formato Y-m-d o Y-m-d H:i:s
     */
    public function scopeEnRangoFechas($query, string $desde, string $hasta)
    {
        return $query->programados()
            ->where('fecha_programada', '>=', $desde)
            ->where('fecha_programada', '<=', $hasta);
    }

    /**
     * Scope: Evaluados programados en un día específico.
     */
    public function scopeEnDia($query, string $fecha)
    {
        return $query->enRangoFechas(
            $fecha . ' 00:00:00',
            $fecha . ' 23:59:59'
        );
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
     * Fecha de expiración para un token nuevo o regenerado.
     */
    public static function calcularExpiracionToken(): \Illuminate\Support\Carbon
    {
        return now()->addDays(Config::diasVigenciaTokenEnlace());
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
            'estado_formulario' => 'formulario_completado_y_recibido',
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
        return route('cuestionario.mostrar', ['token' => $this->token_unico]);
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
        return self::buscarHistorial($dpi);
    }

    public static function buscarHistorial(string $termino)
    {
        return self::buscarCandidatos($termino);
    }

    public static function buscarPorEmpresa(int $empresaId, string $termino)
    {
        return self::buscarCandidatos($termino, $empresaId);
    }

    public static function buscarCandidatos(string $termino, ?int $empresaId = null)
    {
        $termino = trim($termino);

        $query = self::with(['orden', 'cuestionario']);

        if ($empresaId !== null) {
            $query->whereHas('orden', fn ($q) => $q->where('empresa_id', $empresaId)->activas());
        } else {
            $query->with(['orden.empresa']);
        }

        if (preg_match('/^\d{13}$/', $termino)) {
            $query->where('dpi', $termino);
        } else {
            $query->where(function ($q) use ($termino) {
                $q->where('nombre', 'LIKE', "%{$termino}%")
                    ->orWhere('apellidos', 'LIKE', "%{$termino}%")
                    ->orWhereRaw("CONCAT(nombre, ' ', apellidos) LIKE ?", ["%{$termino}%"]);
            });
        }

        return $query->orderBy('created_at', 'desc')->get();
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
     * Accessor: Estado del cuestionario en texto.
     * Lee desde estado_formulario si existe, sino calcula de campos legacy.
     */
    public function getEstadoCuestionarioAttribute(): string
    {
        // Fase 18: usa los 5 valores nuevos de estado_formulario
        return match($this->estado_formulario) {
            'formulario_completado_y_recibido' => 'Completado',
            'vencido'                          => 'Vencido',
            'pendiente_de_llenar'              => 'En Progreso',
            'link_enviado'                     => 'Link Enviado',
            'link_pendiente'                   => 'Pendiente',
            default                            => 'Pendiente',
        };
    }

    /**
     * Accessor: Nombre completo
     */
    public function getNombreCompletoAttribute(): string
    {
        return trim($this->nombre . ' ' . $this->apellidos);
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
        // Fase 18: la programación vive en estado_programacion
        return $this->estado_programacion === 'programado' && !is_null($this->fecha_programada);
    }

    /**
     * Verificar si la evaluación está completada
     */
    public function evaluacionCompletada(): bool
    {
        return $this->estado_evaluacion === 'informe_final_enviado';
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
            'pendiente_de_evaluacion' => 'Pendiente de Evaluación',
            'en_proceso'              => 'En Proceso',
            'en_revision'             => 'En Revisión',
            'resultado_preliminar'    => 'Resultado Preliminar',
            'informe_final_enviado'   => 'Informe Final Enviado',
            'cancelado'               => 'Cancelado',
            'desistio'                => 'Desistió',
            default                   => 'Estado desconocido',
        };
    }

    /**
     * Obtener color del badge según estado de evaluación
     */
    public function getEstadoEvaluacionColorAttribute(): string
    {
        return match($this->estado_evaluacion) {
            'pendiente_de_evaluacion' => 'secondary',
            'en_proceso'              => 'info',
            'en_revision'             => 'primary',
            'resultado_preliminar'    => 'warning',
            'informe_final_enviado'   => 'success',
            'cancelado'               => 'danger',
            'desistio'                => 'dark',
            default                   => 'secondary',
        };
    }

    /**
     * Obtener texto legible para estado_formulario
     */
    public function getEstadoFormularioTextoAttribute(): string
    {
        return match($this->estado_formulario) {
            'link_pendiente'                   => 'Link Pendiente',
            'link_enviado'                     => 'Link Enviado',
            'pendiente_de_llenar'              => 'Pendiente de Llenar',
            'formulario_completado_y_recibido' => 'Formulario Completado y Recibido',
            'vencido'                          => 'Vencido',
            default                            => 'Estado desconocido'
        };
    }

    /**
     * Obtener color del badge según estado_formulario
     */
    public function getEstadoFormularioColorAttribute(): string
    {
        return match($this->estado_formulario) {
            'link_pendiente'                   => 'secondary',
            'link_enviado'                     => 'primary',
            'pendiente_de_llenar'              => 'warning',
            'formulario_completado_y_recibido' => 'success',
            'vencido'                          => 'danger',
            default                            => 'secondary'
        };
    }

    /**
     * Obtener texto legible para estado_programacion
     */
    public function getEstadoProgramacionTextoAttribute(): string
    {
        return match($this->estado_programacion) {
            'contactando'       => 'Contactando',
            'contactado'        => 'Contactado',
            'programado'        => 'Programado',
            'proceso_realizado' => 'Proceso Realizado',
            'reprogramado'      => 'Reprogramado',
            'inasistencia'      => 'Inasistencia',
            'desistio'          => 'Desistió',
            'cancelado'         => 'Cancelado',
            default             => 'Estado desconocido'
        };
    }

    /**
     * Obtener color del badge según estado_programacion
     */
    public function getEstadoProgramacionColorAttribute(): string
    {
        return match($this->estado_programacion) {
            'contactando'       => 'info',
            'contactado'        => 'info',
            'programado'        => 'primary',
            'proceso_realizado' => 'success',
            'reprogramado'      => 'warning',
            'inasistencia'      => 'danger',
            'desistio'          => 'dark',
            'cancelado'         => 'danger',
            default             => 'secondary'
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
     * Programar evaluación con hora inicio y fin.
     *
     * @param string $inicio       Fecha-hora inicio (Y-m-d H:i:s)
     * @param string $fin           Fecha-hora fin (Y-m-d H:i:s)
     * @param int    $poligrafistaId
     * @param int|null $sedeId
     */
    public function programarEvaluacion(string $inicio, string $fin, ?int $poligrafistaId = null, ?int $sedeId = null, ?string $modalidad = null, ?int $responsableId = null): bool
    {
        $estadoAnterior = $this->estado_programacion;
        $this->fecha_programada = $inicio;
        $this->fecha_hora_fin = $fin;
        if ($poligrafistaId) {
            $this->poligrafista_id = $poligrafistaId;
        }
        if ($sedeId) {
            $this->sede_id = $sedeId;
        }
        if ($modalidad) {
            $this->modalidad = $modalidad;
        }
        $this->responsable_id = $responsableId;
        // Fase 18: la programación se refleja en estado_programacion, no en estado_evaluacion
        $this->estado_programacion = 'programado';
        $resultado = $this->save();
        if ($resultado && $estadoAnterior !== 'programado') {
            $this->registrarCambioEstado('estado_programacion', $estadoAnterior, 'programado');
        }
        return $resultado;
    }

    /**
     * Reprogramar evaluación (actualizar fecha/hora).
     */
    public function reprogramarEvaluacion(string $inicio, string $fin, ?int $poligrafistaId = null, ?int $sedeId = null, ?string $modalidad = null, ?int $responsableId = null): bool
    {
        $estadoAnterior = $this->estado_programacion;
        if ($this->fecha_programada) {
            $this->fecha_programada_original = $this->fecha_programada;
        }
        $this->fecha_programada = $inicio;
        $this->fecha_hora_fin = $fin;
        if ($poligrafistaId) {
            $this->poligrafista_id = $poligrafistaId;
        }
        if ($sedeId) {
            $this->sede_id = $sedeId;
        }
        if ($modalidad) {
            $this->modalidad = $modalidad;
        }
        $this->responsable_id = $responsableId;
        // Fase 18: la reprogramación se refleja en estado_programacion, no en estado_evaluacion
        $this->estado_programacion = 'reprogramado';
        $resultado = $this->save();
        if ($resultado && $estadoAnterior !== 'reprogramado') {
            $this->registrarCambioEstado('estado_programacion', $estadoAnterior, 'reprogramado');
        }
        return $resultado;
    }

    /**
     * Cancelar cita programada.
     */
    public function cancelarCita(): bool
    {
        $this->fecha_programada = null;
        $this->fecha_hora_fin = null;
        // Fase 18: cancelar cita afecta la programación, no la evaluación
        $this->estado_programacion = 'cancelado';
        return $this->save();
    }

    // ========================================
    // Transiciones de Estado
    // ========================================

    /**
     * Transiciones válidas para estado_evaluacion.
     * Flexible: admin puede saltar pasos pero no ir a estados ilógicos.
     *
     * @return array<string, string[]>
     */
    /**
     * Fase 18: M\u00e1quina de estados para FORMULARIO (5 valores)
     * 
     * @return array<string, string[]>
     */
    public static function transicionesFormulario(): array
    {
        return [
            'link_pendiente'                   => ['link_enviado', 'vencido'],
            'link_enviado'                     => ['pendiente_de_llenar', 'vencido'],
            'pendiente_de_llenar'              => ['formulario_completado_y_recibido', 'vencido'],
            'formulario_completado_y_recibido' => [], // estado final
            'vencido'                          => [], // estado final
        ];
    }

    /**
     * Fase 18: M\u00e1quina de estados para PROGRAMACI\u00d3N (8 valores sin "Asisti\u00f3")
     * 
     * @return array<string, string[]>
     */
    public static function transicionesProgramacion(): array
    {
        return [
            'contactando'       => ['contactado', 'desistio', 'cancelado'],
            'contactado'        => ['programado', 'reprogramado', 'desistio', 'cancelado'],
            'programado'        => ['proceso_realizado', 'inasistencia', 'reprogramado', 'cancelado'],
            'proceso_realizado' => [], // estado final
            'reprogramado'      => ['contactando', 'programado', 'cancelado'],
            'inasistencia'      => ['reprogramado', 'cancelado'],
            'desistio'          => ['contactando'], // reactivable per respuesta cliente #8
            'cancelado'         => [], // estado final
        ];
    }

    /**
     * Fase 18: M\u00e1quina de estados para EVALUACI\u00d3N (7 valores)
     * 
     * @return array<string, string[]>
     */
    public static function transicionesEvaluacion(): array
    {
        // Fase 18 — PDF cliente (p.2): flujo estricto de evaluación física
        // Cancelado/Desistió SOLO desde pendiente_de_evaluacion (una vez iniciada, es irreversible)
        return [
            'pendiente_de_evaluacion' => ['en_proceso', 'cancelado', 'desistio'],
            'en_proceso'              => ['en_revision'],   // 100% manual (respuesta cliente #2)
            'en_revision'             => ['resultado_preliminar'],
            'resultado_preliminar'    => ['informe_final_enviado'],
            'informe_final_enviado'   => [],                // estado final
            'cancelado'               => ['pendiente_de_evaluacion'], // reactivable
            'desistio'                => ['pendiente_de_evaluacion'], // reactivable (respuesta cliente #8)
        ];
    }

    /**
     * Verificar si puede transicionar a un nuevo estado de evaluación.
     */
    public function puedeTransicionarEstadoEvaluacion(string $nuevoEstado): bool
    {
        if ($this->estado_evaluacion === $nuevoEstado) {
            return false;
        }

        $transiciones = self::transicionesEvaluacion();

        if (!isset($transiciones[$this->estado_evaluacion])) {
            return false;
        }

        return in_array($nuevoEstado, $transiciones[$this->estado_evaluacion]);
    }

    /**
     * Cambiar estado de evaluación con validación.
     * 
     * Reglas de sinergia aplicadas aquí:
     * S4 — Al entrar en 'en_proceso': formulario debe estar 'formulario_completado_y_recibido'.
     * S5 — Al entrar en 'en_proceso': estado_programacion debe ser 'programado' o 'proceso_realizado'.
     * S6 — Al entrar en 'en_revision': auto-disparar cambiarEstadoProgramacion('proceso_realizado').
     *
     * @throws \Illuminate\Validation\ValidationException si S4/S5 no se cumplen
     */
    public function cambiarEstadoEvaluacion(string $nuevoEstado, ?string $observacion = null): bool
    {
        if (!$this->puedeTransicionarEstadoEvaluacion($nuevoEstado)) {
            return false;
        }

        // S4: formulario completo requerido para iniciar evaluación
        if ($nuevoEstado === 'en_proceso' && $this->estado_formulario !== 'formulario_completado_y_recibido') {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'estado_evaluacion' => ['El formulario debe estar "Completado y recibido" antes de iniciar la evaluación.'],
            ]);
        }

        // S5: debe haber pasado por la programación antes de iniciar evaluación
        if ($nuevoEstado === 'en_proceso' && !in_array($this->estado_programacion, ['programado', 'proceso_realizado'])) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'estado_evaluacion' => ['El evaluado debe tener una cita programada antes de iniciar la evaluación.'],
            ]);
        }

        $estadoAnterior = $this->estado_evaluacion;
        $this->estado_evaluacion = $nuevoEstado;
        $resultado = $this->save();

        if ($resultado) {
            $this->registrarCambioEstado('estado_evaluacion', $estadoAnterior, $nuevoEstado, $observacion);

            // S6: al pasar a 'en_revision', marcar la programación como 'proceso_realizado' automáticamente
            if ($nuevoEstado === 'en_revision' && $this->estado_programacion === 'programado') {
                $this->cambiarEstadoProgramacion('proceso_realizado'); // sin observacion — es automático
            }
        }

        return $resultado;
    }

    /**
     * Verificar si puede transicionar a un nuevo estado de programación.
     */
    public function puedeTransicionarEstadoProgramacion(string $nuevoEstado): bool
    {
        if ($this->estado_programacion === $nuevoEstado) {
            return false;
        }

        $transiciones = self::transicionesProgramacion();

        if (!isset($transiciones[$this->estado_programacion])) {
            return false;
        }

        return in_array($nuevoEstado, $transiciones[$this->estado_programacion]);
    }

    /**
     * Cambiar estado de programación con validación.
     */
    public function cambiarEstadoProgramacion(string $nuevoEstado, ?string $observacion = null): bool
    {
        if (!$this->puedeTransicionarEstadoProgramacion($nuevoEstado)) {
            return false;
        }

        $estadoAnterior = $this->estado_programacion;
        $this->estado_programacion = $nuevoEstado;
        $resultado = $this->save();

        if ($resultado) {
            $this->registrarCambioEstado('estado_programacion', $estadoAnterior, $nuevoEstado, $observacion);
        }

        return $resultado;
    }

    /**
     * Verificar si puede transicionar a un nuevo estado de formulario.
     */
    public function puedeTransicionarEstadoFormulario(string $nuevoEstado): bool
    {
        if ($this->estado_formulario === $nuevoEstado) {
            return false;
        }

        $transiciones = self::transicionesFormulario();

        if (!isset($transiciones[$this->estado_formulario])) {
            return false;
        }

        return in_array($nuevoEstado, $transiciones[$this->estado_formulario]);
    }

    /**
     * Cambiar estado de formulario con validación.
     */
    public function cambiarEstadoFormulario(string $nuevoEstado, ?string $observacion = null): bool
    {
        if (!$this->puedeTransicionarEstadoFormulario($nuevoEstado)) {
            return false;
        }

        $estadoAnterior = $this->estado_formulario;
        $this->estado_formulario = $nuevoEstado;

        // Fase 18: Sincronizar cuestionario_completado con nuevo estado
        if ($nuevoEstado === 'formulario_completado_y_recibido') {
            $this->cuestionario_completado = true;
        } elseif (in_array($nuevoEstado, ['link_pendiente', 'link_enviado', 'pendiente_de_llenar', 'vencido'])) {
            $this->cuestionario_completado = false;
        }

        $resultado = $this->save();

        if ($resultado) {
            $this->registrarCambioEstado('estado_formulario', $estadoAnterior, $nuevoEstado, $observacion);
        }

        return $resultado;
    }

    /**
     * Obtener estados disponibles para estado_evaluacion.
     *
     * @return array<string, string>
     */
    public static function estadosEvaluacionDisponibles(): array
    {
        // Fase 18 — PDF cliente (p.2): 7 estados de la etapa técnica de evaluación
        return [
            'pendiente_de_evaluacion' => 'Pendiente de Evaluación',
            'en_proceso'              => 'En Proceso',
            'en_revision'             => 'En Revisión',
            'resultado_preliminar'    => 'Resultado Preliminar',
            'informe_final_enviado'   => 'Informe Final Enviado',
            'cancelado'               => 'Cancelado',
            'desistio'                => 'Desistió',
        ];
    }

    /**
     * Obtener estados disponibles para estado_formulario.
     *
     * @return array<string, string>
     */
    public static function estadosFormularioDisponibles(): array
    {
        // Fase 18: 5 valores del flujo de formulario
        return [
            'link_pendiente'                       => 'Link Pendiente',
            'link_enviado'                         => 'Link Enviado',
            'pendiente_de_llenar'                  => 'Pendiente de Llenar',
            'formulario_completado_y_recibido'     => 'Formulario Completado y Recibido',
            'vencido'                              => 'Vencido',
        ];
    }

    /**
     * Obtener estados disponibles para estado_programacion.
     *
     * @return array<string, string>
     */
    public static function estadosProgramacionDisponibles(): array
    {
        // Fase 18: 8 valores sin "Asistió" según respuesta cliente #3
        return [
            'contactando'        => 'Contactando',
            'contactado'         => 'Contactado',
            'programado'         => 'Programado',
            'proceso_realizado'  => 'Proceso Realizado',
            'reprogramado'       => 'Reprogramado',
            'inasistencia'       => 'Inasistencia',
            'desistio'           => 'Desistió',
            'cancelado'          => 'Cancelado',
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
        // Fase 18: 3 campos de estado independientes
        return [
            'estado_formulario'    => array_keys(self::estadosFormularioDisponibles()),
            'estado_programacion'  => array_keys(self::estadosProgramacionDisponibles()),
            'estado_evaluacion'    => array_keys(self::estadosEvaluacionDisponibles()),
        ];
    }

    /**
     * Verificar si tiene archivo de resultado preliminar.
     */
    public function tieneResultadoPreliminar(): bool
    {
        return !empty($this->archivo_resultado_preliminar);
    }

    /**
     * Verificar si tiene archivo de resultado final.
     */
    public function tieneResultadoFinal(): bool
    {
        return !empty($this->archivo_resultado_final);
    }

    /**
     * Marcar evaluación como completada
     */
    public function completarEvaluacion(string $resultado, ?string $notas = null): bool
    {
        $this->fecha_realizada = now();
        $this->estado_evaluacion = 'informe_final_enviado';
        $this->resultado = $resultado;
        if ($notas) {
            $this->notas_poligrafo = $notas;
        }
        return $this->save();
    }
}
