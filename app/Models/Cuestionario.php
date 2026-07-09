<?php

namespace App\Models;

use App\Support\CuestionarioSecciones;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Cuestionario extends Model
{
    protected $table = 'cuestionarios';
    
    protected $fillable = [
        'evaluado_orden_id',
        'tipo_formulario',
        'seccion_actual',
        'total_secciones',
        'progreso_porcentaje',
        'completado',
        'bloqueado',
        'instrucciones_leidas_at',
        'ip_instrucciones',
        'datos_precarga_json',
        'acepta_terminos',
        'acepta_terminos_at',
        'firma_autorizacion',
        'ip_terminos',
        'firma_digital',
        'ip_completado',
        'completado_at',
        'observaciones_repro'
    ];
    
    protected $casts = [
        'completado' => 'boolean',
        'bloqueado' => 'boolean',
        'acepta_terminos' => 'boolean',
        'acepta_terminos_at' => 'datetime',
        'instrucciones_leidas_at' => 'datetime',
        'datos_precarga_json' => 'array',
        'progreso_porcentaje' => 'decimal:2',
        'completado_at' => 'datetime'
    ];
    
    protected $dates = [
        'completado_at'
    ];
    
    // Relaciones
    public function evaluadoOrden(): BelongsTo
    {
        return $this->belongsTo(EvaluadoOrden::class);
    }
    
    public function respuestas(): HasMany
    {
        return $this->hasMany(CuestionarioRespuesta::class);
    }
    
    // Métodos útiles
    public function getSeccionesConfig(): array
    {
        // Nota: La "Firma Digital" no es una sección regular,
        // se maneja en la pantalla de finalización
        $secciones = [
            'preempleo' => [
                1 => 'Datos Personales',
                2 => 'Información Familiar', 
                3 => 'Historial Laboral',
                4 => 'Situación Económica',
                5 => 'Antecedentes y Referencias',
            ],
            'periodica' => [
                1 => 'Actualización de Datos',
                2 => 'Cambios Familiares',
                3 => 'Situación Laboral Actual',
                4 => 'Situación Económica',
                5 => 'Antecedentes y Referencias',
            ],
            'especifica' => [
                1 => 'Datos Básicos',
                2 => 'Situación Específica',
                3 => 'Situación Económica',
                4 => 'Antecedentes Relevantes', 
            ],
            'socioeconomico' => [
                1 => 'Datos Personales',
                2 => 'Información Familiar',
                3 => 'Historial Laboral',
                4 => 'Situación Económica',
                5 => 'Aspectos Complementarios',
                6 => 'Información Socioeconómica Complementaria',
            ]
        ];
        
        return $secciones[$this->tipo_formulario] ?? [];
    }
    
    public function getNombreSeccion(int $numero): string
    {
        $secciones = $this->getSeccionesConfig();
        return $secciones[$numero] ?? 'Sección ' . $numero;
    }
    
    public function actualizarProgreso(): void
    {
        $this->progreso_porcentaje = ($this->seccion_actual / $this->total_secciones) * 100;
        $this->save();
    }
    
    public function marcarCompletado(string $ip = null): void
    {
        $this->update([
            'completado' => true,
            'bloqueado' => true,
            'progreso_porcentaje' => 100,
            'completado_at' => now(),
            'ip_completado' => $ip ?? request()->ip()
        ]);
        
        // También marcar el evaluado_orden como completado
        $this->evaluadoOrden->update([
            'cuestionario_completado' => true,
            'cuestionario_completado_at' => now()
        ]);
    }
    
    public function getRespuestasPorSeccion(string $seccion): array
    {
        return $this->respuestas()
            ->where('seccion', $seccion)
            ->whereNotNull('valor')
            ->pluck('valor', 'campo')
            ->toArray();
    }

    /**
     * Tablas dinámicas (valor_json) de una sección, indexadas por nombre de campo.
     *
     * @return array<string, list<array<string, mixed>>>
     */
    public function getTablasPorSeccion(string $seccion): array
    {
        return $this->respuestas()
            ->where('seccion', $seccion)
            ->whereNotNull('valor_json')
            ->get()
            ->mapWithKeys(fn (CuestionarioRespuesta $r) => [$r->campo => $r->getTabla()])
            ->all();
    }

    /**
     * Tablas dinámicas de una sección por número (1-based).
     *
     * @return array<string, list<array<string, mixed>>>
     */
    public function getTablasPorNumeroSeccion(int $numeroSeccion): array
    {
        return $this->getTablasPorSeccion($this->getSlugSeccion($numeroSeccion));
    }
    
    /**
     * Obtener respuestas de una sección por número
     */
    public function obtenerRespuestasSeccion(int $numeroSeccion): array
    {
        $slugSeccion = $this->getSlugSeccion($numeroSeccion);
        
        return $this->respuestas()
            ->where('seccion', $slugSeccion)
            ->pluck('valor', 'campo')
            ->toArray();
    }
    
    /**
     * Obtener el slug de una sección por número
     */
    protected function getSlugSeccion(int $numero): string
    {
        return CuestionarioSecciones::slug($numero, $this->tipo_formulario ?? 'preempleo');
    }

    public static function totalSeccionesParaTipo(string $tipoFormulario): int
    {
        $cuestionario = new self(['tipo_formulario' => $tipoFormulario]);

        return count($cuestionario->getSeccionesConfig());
    }
    
    /**
     * Obtener el progreso de cada sección
     */
    public function getProgresoSeccionesAttribute(): array
    {
        $progreso = [];
        $totalSecciones = $this->total_secciones ?? 5;
        
        for ($i = 1; $i <= $totalSecciones; $i++) {
            // Una sección está completada si la sección actual es mayor
            $progreso[$i] = $i < $this->seccion_actual || $this->completado;
        }
        
        return $progreso;
    }
    
    /**
     * Obtener el estado del cuestionario
     */
    public function getEstadoAttribute(): string
    {
        if ($this->completado) {
            return 'completado';
        } elseif ($this->seccion_actual > 1) {
            return 'en_progreso';
        } else {
            return 'pendiente';
        }
    }

    public function puedeAvanzarASeccion(int $numeroSeccion): bool
    {
        return !$this->bloqueado && $numeroSeccion <= ($this->seccion_actual + 1);
    }
    
    public function calcularProgreso(): float
    {
        if ($this->completado) {
            return 100.0;
        }
        
        if ($this->total_secciones <= 0) {
            return 0.0;
        }
        
        // Calcular progreso basado en la sección actual
        return round(($this->seccion_actual / $this->total_secciones) * 100, 1);
    }
    
    public function getProgresoAttribute(): float
    {
        return $this->calcularProgreso();
    }
    
    public function getEstadoTextoAttribute(): string
    {
        if ($this->completado) {
            return 'Completado';
        } elseif ($this->seccion_actual > 1) {
            return 'En Progreso';
        } else {
            return 'Pendiente';
        }
    }
}
