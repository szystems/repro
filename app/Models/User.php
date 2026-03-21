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
        'sede_id',
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

    public function sede()
    {
        return $this->belongsTo(Sede::class, 'sede_id');
    }

    /**
     * Relación con roles (nuevo sistema)
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_role')
            ->withTimestamps();
    }

    /**
     * Verificar si el usuario tiene un rol específico
     */
    public function hasRole(string $roleName): bool
    {
        return $this->roles()->where('name', $roleName)->exists();
    }

    /**
     * Verificar si el usuario tiene alguno de los roles especificados
     */
    public function hasAnyRole(array $roleNames): bool
    {
        return $this->roles()->whereIn('name', $roleNames)->exists();
    }

    /**
     * Verificar si el usuario tiene todos los roles especificados
     */
    public function hasAllRoles(array $roleNames): bool
    {
        foreach ($roleNames as $roleName) {
            if (!$this->hasRole($roleName)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Verificar si el usuario tiene un permiso específico
     */
    public function hasPermission(string $permissionName): bool
    {
        return $this->roles()
            ->whereHas('permissions', function ($query) use ($permissionName) {
                $query->where('name', $permissionName);
            })
            ->exists();
    }

    /**
     * Verificar si el usuario tiene alguno de los permisos especificados
     */
    public function hasAnyPermission(array $permissionNames): bool
    {
        return $this->roles()
            ->whereHas('permissions', function ($query) use ($permissionNames) {
                $query->whereIn('name', $permissionNames);
            })
            ->exists();
    }

    /**
     * Verificar si el usuario tiene todos los permisos especificados
     */
    public function hasAllPermissions(array $permissionNames): bool
    {
        foreach ($permissionNames as $permissionName) {
            if (!$this->hasPermission($permissionName)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Asignar rol al usuario
     */
    public function assignRole(string $roleName): void
    {
        $role = Role::where('name', $roleName)->firstOrFail();
        $this->roles()->syncWithoutDetaching([$role->id]);
    }

    /**
     * Remover rol del usuario
     */
    public function removeRole(string $roleName): void
    {
        $role = Role::where('name', $roleName)->first();
        if ($role) {
            $this->roles()->detach($role->id);
        }
    }

    /**
     * Obtener todos los permisos del usuario (a través de sus roles)
     */
    public function getAllPermissions()
    {
        return Permission::whereHas('roles', function ($query) {
            $query->whereIn('role_id', $this->roles->pluck('id'));
        })->get();
    }

    /**
     * Verificar si el usuario empresa tiene un permiso específico.
     * Usuarios principales siempre tienen todos los permisos.
     */
    public function tienePermisoEmpresa(string $permiso): bool
    {
        if ($this->principal == 1) {
            return true;
        }

        $permisos = $this->permisos
            ? (is_array($this->permisos) ? $this->permisos : json_decode($this->permisos, true))
            : [];

        return is_array($permisos) && in_array($permiso, $permisos);
    }

    // ==================== Scopes ====================

    /**
     * Scope: Usuarios que pueden ser poligrafistas (admin y repro).
     * Los poligrafistas son usuarios REPRO (role_as >= 2) con estado activo.
     */
    public function scopePoligrafistas($query)
    {
        return $query->where('role_as', '>=', 2)
            ->where('estado', 1)
            ->orderBy('name');
    }

    // ==================== Métodos de compatibilidad con role_as ====================

    /**
     * Verificar si el usuario es administrador
     */
    public function isAdmin(): bool
    {
        return $this->role_as == 3 || $this->hasRole('admin');
    }

    /**
     * Verificar si el usuario es de REPRO
     */
    public function isRepro(): bool
    {
        return $this->role_as == 2 || $this->hasRole('repro');
    }

    /**
     * Verificar si el usuario es de una empresa cliente
     */
    public function isEmpresa(): bool
    {
        return $this->role_as == 1 || $this->hasRole('empresa');
    }

    /**
     * Verificar si el usuario es una persona evaluada
     * NOTA: Los evaluados acceden via token, no como usuarios del sistema
     */
    public function isEvaluado(): bool
    {
        return $this->role_as == 0; // Solo para compatibilidad legacy
    }

    /**
     * Obtener el nombre del rol del usuario (legacy)
     */
    public function getRoleName(): string
    {
        // Priorizar el nuevo sistema de roles
        if ($this->roles->isNotEmpty()) {
            return $this->roles->first()->display_name;
        }

        // Fallback al sistema antiguo
        switch ($this->role_as) {
            case 0: return 'Evaluado (Legacy)'; // Los evaluados no son usuarios del sistema
            case 1: return 'Empresa';
            case 2: return 'Repro';
            case 3: return 'Administrador';
            default: return 'Desconocido';
        }
    }

    /**
     * Obtener todos los nombres de roles del usuario
     */
    public function getRoleNames(): array
    {
        return $this->roles->pluck('display_name')->toArray();
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
