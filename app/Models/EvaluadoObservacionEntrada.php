<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvaluadoObservacionEntrada extends Model
{
    protected $table = 'evaluado_observacion_entradas';

    protected $fillable = [
        'evaluado_orden_id',
        'user_id',
        'texto',
        'sin_fecha_original',
    ];

    protected function casts(): array
    {
        return [
            'texto' => 'encrypted',
            'sin_fecha_original' => 'boolean',
        ];
    }

    public function evaluadoOrden(): BelongsTo
    {
        return $this->belongsTo(EvaluadoOrden::class, 'evaluado_orden_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
