<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Empresa extends Model
{
    use HasFactory;

    protected $table = 'empresas';

    protected $fillable = [
        'nombre',
        'nit',
        'direccion',
        'telefono',
        'email',
        'logo',
        'estado',
        'descripcion',
        'sitio_web',
        'contacto_nombre',
        'contacto_cargo',
        'contacto_telefono',
        'contacto_email',
        'notas'
    ];

    /**
     * Relación con usuarios de la empresa
     */
    public function usuarios()
    {
        return $this->hasMany(User::class, 'empresa_id', 'id');
    }

    /**
     * Obtener el usuario principal de la empresa
     */
    public function usuarioPrincipal()
    {
        return $this->hasOne(User::class, 'empresa_id', 'id')
            ->where('principal', 1)
            ->where('estado', 1);
    }

    /**
     * Usuarios activos de esta empresa
     */
    public function usuariosActivos()
    {
        return $this->hasMany(User::class, 'empresa_id', 'id')
            ->where('estado', 1)
            ->orderBy('principal', 'desc')
            ->orderBy('name', 'asc');
    }

    /**
     * Obtener el estado en formato legible
     */
    public function getEstadoTexto()
    {
        return $this->estado == 1 ? 'Activa' : 'Inactiva';
    }

    /**
     * Métodos para formatear fechas
     */
    public function getCreatedAtFormateada()
    {
        return Carbon::parse($this->created_at)->format('d/m/Y H:i');
    }

    public function getUpdatedAtFormateada()
    {
        return Carbon::parse($this->updated_at)->format('d/m/Y H:i');
    }

    /**
     * Obtener cantidad de usuarios de la empresa
     */
    public function getTotalUsuarios()
    {
        return $this->usuarios()->where('estado', 1)->count();
    }
}
