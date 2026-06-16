<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    use HasFactory;
    protected $table = 'configs';
    protected $attributes = [
        'historial_visible_empresa' => true,
    ];
    protected $fillable = [
        'logo',
        'nombre_empresa',
        'email',
        'time_zone',
        'currency',
        'currency_simbol',
        'currency_iso',
        'fb_link',
        'inst_link',
        'yt_link',
        'wapp_link',
        'descuento_maximo',
        'impuesto',
        'dias_vigencia_token',
        'historial_visible_empresa',
    ];

    protected function casts(): array
    {
        return [
            'historial_visible_empresa' => 'boolean',
        ];
    }

    public static function historialVisibleParaEmpresa(): bool
    {
        $config = static::first();

        return $config ? (bool) $config->historial_visible_empresa : true;
    }

    /**
     * Días de vigencia del enlace público del cuestionario (mínimo 1).
     */
    public static function diasVigenciaTokenEnlace(): int
    {
        $dias = (int) (static::value('dias_vigencia_token') ?? 30);

        return max(1, $dias > 0 ? $dias : 30);
    }
}
