<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

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
    use HasFactory, SoftDeletes;

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
        'email',
        'telefono',
        'celular',
        'dpi',
        'tipo_documento',
        'token_unico',
        'token_expira_at',
        'token_usado_at',
        'cuestionario_completado',
        'completado_at',
        'firma_digital',
        'ip_completado',
        'user_agent',
        'intentos_acceso',
        'notas',
        'notificado',
        'notificado_at',
    ];

    /**
     * Campos de fecha
     */
    protected $dates = [
        'token_expira_at',
        'token_usado_at',
        'completado_at',
        'notificado_at',
        'created_at',
        'updated_at',
        'deleted_at',
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
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function evaluaciones()
    {
        return $this->hasMany(Evaluacion::class, 'evaluado_orden_id');
    }

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
            ->with(['orden.empresa', 'cuestionario', 'evaluaciones'])
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
}
