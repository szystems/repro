<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    use HasFactory;
    protected $table = 'configs';
    protected $fillable = [
        'logo',
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
    ];
}
