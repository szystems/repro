<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Carbon\Carbon;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_as',
        'empresa_id',
        'fotografia',
        'estado',
        'principal',
        'fecha_nacimiento',
        'telefono',
        'celular',
        'direccion',
        'cargo',
        'permisos',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'permisos' => 'array',
    ];

    /**
     * Relación con la empresa (para usuarios tipo empresa)
     */
    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    /**
     * Verificar si el usuario es administrador
     */
    public function isAdmin()
    {
        return $this->role_as == 3;
    }

    /**
     * Verificar si el usuario es de REPRO
     */
    public function isRepro()
    {
        return $this->role_as == 2;
    }

    /**
     * Verificar si el usuario es de una empresa cliente
     */
    public function isEmpresa()
    {
        return $this->role_as == 1;
    }

    /**
     * Verificar si el usuario es una persona evaluada
     */
    public function isEvaluado()
    {
        return $this->role_as == 0;
    }

    /**
     * Obtener el nombre del rol del usuario
     */
    public function getRoleName()
    {
        switch ($this->role_as) {
            case 0: return 'Evaluado';
            case 1: return 'Empresa';
            case 2: return 'Repro';
            case 3: return 'Administrador';
            default: return 'Desconocido';
        }
    }

    // Métodos para fechas
    public function getTimeZoneAttribute($value): string
    {
        return $value == config('app.timezone') || empty($value) ? config('app.timezone') : $value;
    }

    public function setTimeZoneAttribute($value)
    {
        $this->attributes['timezone'] = $value == config('app.timezone') || is_null($value) ? null : $value;
    }

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value);
    }

    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value);
    }
}
