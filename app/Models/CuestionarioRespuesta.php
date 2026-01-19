<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CuestionarioRespuesta extends Model
{
    protected $table = 'cuestionario_respuestas';
    
    protected $fillable = [
        'cuestionario_id',
        'seccion',
        'campo',
        'valor',
        'tipo_campo',
        'requerido',
        'metadata'
    ];
    
    protected $casts = [
        'requerido' => 'boolean',
        'metadata' => 'array'
    ];
    
    // Relaciones
    public function cuestionario(): BelongsTo
    {
        return $this->belongsTo(Cuestionario::class);
    }
    
    // Métodos útiles
    public function getValorFormateado(): mixed
    {
        switch ($this->tipo_campo) {
            case 'boolean':
                return filter_var($this->valor, FILTER_VALIDATE_BOOLEAN);
            case 'number':
                return is_numeric($this->valor) ? (float) $this->valor : $this->valor;
            case 'date':
                return $this->valor ? \Carbon\Carbon::parse($this->valor) : null;
            default:
                return $this->valor;
        }
    }
    
    public function scopePorSeccion($query, string $seccion)
    {
        return $query->where('seccion', $seccion);
    }
    
    public function scopeRequeridos($query)
    {
        return $query->where('requerido', true);
    }
    
    public static function guardarRespuestas(int $cuestionarioId, string $seccion, array $datos): void
    {
        foreach ($datos as $campo => $valor) {
            static::updateOrCreate(
                [
                    'cuestionario_id' => $cuestionarioId,
                    'seccion' => $seccion,
                    'campo' => $campo
                ],
                [
                    'valor' => $valor,
                    'tipo_campo' => static::detectarTipoCampo($valor),
                ]
            );
        }
    }
    
    protected static function detectarTipoCampo($valor): string
    {
        if (is_bool($valor) || in_array(strtolower($valor), ['true', 'false', '1', '0'])) {
            return 'boolean';
        }
        if (is_numeric($valor)) {
            return 'number';
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}/', $valor)) {
            return 'date';
        }
        if (strlen($valor) > 255) {
            return 'textarea';
        }
        return 'text';
    }
}
