<?php

namespace App\Models;

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
        'firma_digital',
        'ip_completado',
        'completado_at',
        'observaciones_repro'
    ];
    
    protected $casts = [
        'completado' => 'boolean',
        'bloqueado' => 'boolean',
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
                4 => 'Situación Económica Detallada',
                5 => 'Situación Habitacional',
                6 => 'Referencias Comunitarias',
                7 => 'Verificación de Documentos',
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
            ->pluck('valor', 'campo')
            ->toArray();
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
        $slugsPorTipo = [
            'preempleo' => [
                1 => 'datos_personales',
                2 => 'informacion_familiar',
                3 => 'historial_laboral',
                4 => 'situacion_economica',
                5 => 'antecedentes_referencias',
                6 => 'firma_digital'
            ],
            'periodica' => [
                1 => 'actualizacion_datos',
                2 => 'cambios_familiares',
                3 => 'situacion_laboral',
                4 => 'antecedentes_recientes',  // Contiene datos económicos (así se guardó)
                5 => 'firma_digital'             // Contiene antecedentes (así se guardó)
            ],
            'especifica' => [
                1 => 'datos_basicos',
                2 => 'situacion_especifica',
                3 => 'situacion_economica',
                4 => 'antecedentes_relevantes',
                5 => 'firma_digital'
            ],
            'socioeconomico' => [
                1 => 'datos_personales',
                2 => 'informacion_familiar',
                3 => 'historial_laboral',
                4 => 'situacion_economica_detallada',
                5 => 'situacion_habitacional',
                6 => 'referencias_comunitarias',
                7 => 'verificacion_documentos',
                8 => 'firma_digital'
            ]
        ];
        
        $slugs = $slugsPorTipo[$this->tipo_formulario] ?? $slugsPorTipo['preempleo'];
        
        return $slugs[$numero] ?? 'seccion_' . $numero;
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
