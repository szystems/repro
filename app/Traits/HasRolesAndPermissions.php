<?php

namespace App\Traits;

use App\Models\Permission;
use App\Models\Role;

trait HasRolesAndPermissions
{
    /**
     * Relación con roles
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
     * Asignar múltiples roles al usuario
     */
    public function assignRoles(array $roleNames): void
    {
        $roles = Role::whereIn('name', $roleNames)->get();
        $this->roles()->syncWithoutDetaching($roles->pluck('id')->toArray());
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
     * Sincronizar roles (reemplaza todos los roles existentes)
     */
    public function syncRoles(array $roleNames): void
    {
        $roles = Role::whereIn('name', $roleNames)->get();
        $this->roles()->sync($roles->pluck('id')->toArray());
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
     * Verificar si el usuario puede realizar una acción en un módulo
     */
    public function can(string $action, string $module): bool
    {
        $permissionName = "{$module}.{$action}";
        return $this->hasPermission($permissionName);
    }
}
