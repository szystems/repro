<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'level',
    ];

    /** Roles internos creados al guardar permisos de un usuario REPRO (`user_{id}`). */
    public function esPersonal(): bool
    {
        return str_starts_with((string) $this->name, 'user_');
    }

    /** Roles que se muestran en Gestión de Roles / selector de usuario. */
    public function scopeSinPersonales($query)
    {
        return $query->where('name', 'not like', 'user_%');
    }

    /**
     * Relación con permisos
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permission')
            ->withTimestamps();
    }

    /**
     * Relación con usuarios
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_role')
            ->withTimestamps();
    }

    /**
     * Verificar si el rol tiene un permiso específico
     */
    public function hasPermission(string $permissionName): bool
    {
        return $this->permissions()->where('name', $permissionName)->exists();
    }

    /**
     * Asignar permiso al rol
     */
    public function givePermission(Permission $permission): void
    {
        $this->permissions()->syncWithoutDetaching([$permission->id]);
    }

    /**
     * Remover permiso del rol
     */
    public function revokePermission(Permission $permission): void
    {
        $this->permissions()->detach($permission->id);
    }
}
