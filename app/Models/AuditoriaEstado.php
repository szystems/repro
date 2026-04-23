<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditoriaEstado extends Model
{
    protected $table = 'auditoria_estados';

    public $timestamps = false;

    protected $fillable = [
        'entidad_tipo',
        'entidad_id',
        'campo',
        'estado_anterior',
        'estado_nuevo',
        'user_id',
        'ip',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
