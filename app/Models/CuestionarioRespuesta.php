<?php

namespace App\Models;

use App\Support\CuestionarioPrecarga;
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
        'valor_json',
        'tipo_campo',
        'requerido',
        'metadata'
    ];
    
    protected $casts = [
        'requerido' => 'boolean',
        'metadata' => 'array',
        'valor_json' => 'array'
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
    
    public static function guardarRespuestas(int $cuestionarioId, string $seccion, array $datos, ?array $precargaSnapshot = null): void
    {
        foreach ($datos as $campo => $valor) {
            if (is_array($valor)) {
                $valor = json_encode(array_values($valor), JSON_UNESCAPED_UNICODE);
            }

            $atributos = [
                'valor' => $valor,
                'tipo_campo' => static::detectarTipoCampo($valor),
            ];

            if ($precargaSnapshot !== null) {
                $metaNueva = CuestionarioPrecarga::metadataTrazabilidad($campo, $valor, $precargaSnapshot);
                if ($metaNueva !== null) {
                    $existente = static::where('cuestionario_id', $cuestionarioId)
                        ->where('seccion', $seccion)
                        ->where('campo', $campo)
                        ->first();

                    $atributos['metadata'] = array_merge($existente?->metadata ?? [], $metaNueva);
                }
            }

            static::updateOrCreate(
                [
                    'cuestionario_id' => $cuestionarioId,
                    'seccion' => $seccion,
                    'campo' => $campo
                ],
                $atributos
            );
        }
    }

    /**
     * Guardar una tabla dinámica (filas repetibles) como JSON estructurado.
     *
     * @param array<int, array<string, mixed>> $filas  Lista de filas; cada fila es un mapa campo => valor.
     */
    public static function guardarTabla(int $cuestionarioId, string $seccion, string $campo, array $filas): void
    {
        static::updateOrCreate(
            [
                'cuestionario_id' => $cuestionarioId,
                'seccion' => $seccion,
                'campo' => $campo
            ],
            [
                'valor' => null,
                'valor_json' => array_values($filas),
                'tipo_campo' => 'text',
                'metadata' => ['tipo_logico' => 'tabla'],
            ]
        );
    }

    /**
     * Obtener las filas de una tabla dinámica previamente guardada.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getTabla(): array
    {
        return is_array($this->valor_json) ? $this->valor_json : [];
    }
    
    protected static function detectarTipoCampo($valor): string
    {
        if (is_bool($valor)) {
            return 'boolean';
        }

        if (! is_string($valor) && ! is_numeric($valor)) {
            return 'text';
        }

        $texto = (string) $valor;

        if (in_array(strtolower($texto), ['true', 'false', '1', '0'], true)) {
            return 'boolean';
        }
        if (is_numeric($texto)) {
            return 'number';
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}/', $texto)) {
            return 'date';
        }
        if (str_starts_with($texto, 'cuestionarios/fotos/')) {
            return 'file';
        }
        if (strlen($texto) > 255) {
            return 'textarea';
        }

        return 'text';
    }
}
