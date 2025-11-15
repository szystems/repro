<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RolesController extends Controller
{
    /**
     * Mostrar listado de roles
     */
    public function index()
    {
        // Solo administradores pueden gestionar roles
        if (Auth::user()->role_as < 3) {
            return redirect()->back()->with('error', 'No tiene permisos para acceder a este módulo');
        }

        $roles = Role::with('permissions')->orderBy('name')->get();
        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Mostrar formulario de creación de rol
     */
    public function create()
    {
        if (Auth::user()->role_as < 3) {
            return redirect()->back()->with('error', 'No tiene permisos para crear roles');
        }

        $permissions = Permission::all()->groupBy('module');
        return view('admin.roles.create', compact('permissions'));
    }

    /**
     * Almacenar nuevo rol
     */
    public function store(Request $request)
    {
        if (Auth::user()->role_as < 3) {
            return redirect()->back()->with('error', 'No tiene permisos para crear roles');
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'permissions' => 'array'
        ]);

        DB::beginTransaction();
        
        try {
            $role = Role::create([
                'name' => $request->name,
                'display_name' => $request->display_name,
                'description' => $request->description,
            ]);

            // Asignar permisos seleccionados
            if ($request->has('permissions')) {
                $permissions = Permission::whereIn('id', $request->permissions)->get();
                foreach ($permissions as $permission) {
                    $role->givePermission($permission);
                }
            }

            DB::commit();
            return redirect('admin/roles')->with('status', 'Rol creado correctamente');
            
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Error al crear el rol: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar rol específico
     */
    public function show($id)
    {
        if (Auth::user()->role_as < 3) {
            return redirect()->back()->with('error', 'No tiene permisos para ver roles');
        }

        $role = Role::with('permissions', 'users')->findOrFail($id);
        return view('admin.roles.show', compact('role'));
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        if (Auth::user()->role_as < 3) {
            return redirect()->back()->with('error', 'No tiene permisos para editar roles');
        }

        $role = Role::with('permissions')->findOrFail($id);
        $permissions = Permission::all()->groupBy('module');
        $rolePermissions = $role->permissions->pluck('id')->toArray();
        
        return view('admin.roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    /**
     * Actualizar rol
     */
    public function update(Request $request, $id)
    {
        if (Auth::user()->role_as < 3) {
            return redirect()->back()->with('error', 'No tiene permisos para editar roles');
        }

        $role = Role::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $id,
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'permissions' => 'array'
        ]);

        DB::beginTransaction();
        
        try {
            $role->update([
                'name' => $request->name,
                'display_name' => $request->display_name,
                'description' => $request->description,
            ]);

            // Actualizar permisos
            $role->permissions()->detach(); // Remover todos los permisos actuales
            
            if ($request->has('permissions')) {
                $permissions = Permission::whereIn('id', $request->permissions)->get();
                foreach ($permissions as $permission) {
                    $role->givePermission($permission);
                }
            }

            DB::commit();
            return redirect('admin/roles')->with('status', 'Rol actualizado correctamente');
            
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Error al actualizar el rol: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar rol
     */
    public function destroy($id)
    {
        if (Auth::user()->role_as < 3) {
            return redirect()->back()->with('error', 'No tiene permisos para eliminar roles');
        }

        $role = Role::findOrFail($id);

        // Verificar que no sea un rol del sistema (admin, repro, empresa)
        $systemRoles = ['admin', 'repro', 'empresa', 'evaluado'];
        if (in_array($role->name, $systemRoles)) {
            return redirect()->back()->with('error', 'No se pueden eliminar los roles del sistema');
        }

        // Verificar que no tenga usuarios asignados
        if ($role->users()->count() > 0) {
            return redirect()->back()->with('error', 'No se puede eliminar un rol que tiene usuarios asignados');
        }

        DB::beginTransaction();
        
        try {
            $role->permissions()->detach(); // Remover permisos
            $role->delete();
            
            DB::commit();
            return redirect('admin/roles')->with('status', 'Rol eliminado correctamente');
            
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Error al eliminar el rol: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar listado de permisos
     */
    public function permissions()
    {
        if (Auth::user()->role_as < 3) {
            return redirect()->back()->with('error', 'No tiene permisos para ver permisos');
        }

        $permissions = Permission::with('roles')->orderBy('module')->orderBy('name')->get();
        $permissionsByModule = $permissions->groupBy('module');
        
        return view('admin.roles.permissions', compact('permissionsByModule'));
    }
}