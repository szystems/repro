<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Empresa;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Sede;
use App\Http\Requests\UserFormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use App\Models\Config;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserMail;
use App\Mail\UserResetPasswordMail;
use App\Support\EmpresaPermisosSupport;

class UsersController extends Controller
{
    /**
     * Construir query base de usuarios con filtros.
     *
     * @param  array<string, mixed>  $filters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function buildUsersQuery(array $filters): \Illuminate\Database\Eloquent\Builder
    {
        $query = User::where('estado', 1);

        $search = $filters['fuser'] ?? null;
        $roleFilter = $filters['role_filter'] ?? null;
        $empresaFilter = $filters['empresa_filter'] ?? null;

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', '%'.$search.'%')
                  ->orWhere('email', 'LIKE', '%'.$search.'%')
                  ->orWhere('telefono', 'LIKE', '%'.$search.'%')
                  ->orWhere('celular', 'LIKE', '%'.$search.'%');
            });
        }

        if ($roleFilter !== null && $roleFilter !== '') {
            $query->where('role_as', $roleFilter);
        }

        if ($empresaFilter !== null && $empresaFilter !== '') {
            $query->where('empresa_id', $empresaFilter);
        }

        if (Auth::user()->role_as == 1) {
            $query->where('empresa_id', Auth::user()->empresa_id);
        }

        return $query->orderBy('name', 'asc');
    }

    public function users(Request $request)
    {
        $queryUser = $request->input('fuser');
        $role_filter = $request->input('role_filter');
        $empresa_filter = $request->input('empresa_filter');

        $users = $this->buildUsersQuery($request->all())->with('empresa')->paginate(20);
        $filterUsers = User::select('name', 'email')->where('estado', 1)->orderBy('name')->get();
        $empresas = Empresa::where('estado', 1)->orderBy('nombre', 'asc')->get();

        return view('admin.user.index', compact('users', 'queryUser', 'filterUsers', 'role_filter', 'empresa_filter', 'empresas'));
    }

    public function showuser(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            abort(404);
        }

        $currentUser = Auth::user();
        $isOwnProfile = (int) Auth::id() === (int) $id;

        // Cualquier usuario puede ver su propio perfil.
        // Para ver el perfil de otro usuario: necesita usuarios.ver
        // y, si es empresa, debe ser de la misma empresa.
        if (!$isOwnProfile) {
            if (!$currentUser->hasPermission('usuarios.ver')) {
                abort(403, 'No tiene permisos para ver este usuario.');
            }

            if ($currentUser->role_as == 1 && (int) $currentUser->empresa_id !== (int) $user->empresa_id) {
                return redirect('users')->with('status', 'No tiene permisos para ver este usuario');
            }
        }

        $hoy = Carbon::now('America/Guatemala');
        $fechaVista = $hoy->format('d-m-Y');
        $fecha = date("Y-m-d", strtotime($fechaVista));
        $filtros = $request->all();

        return view('admin.user.show', compact('user','fecha','filtros','fechaVista'));
    }

    public function adduser()
    {
        // Verificar permisos para crear usuarios
        $currentUser = Auth::user();
        $canCreateAdmin = $currentUser->role_as == 3; // Solo admin puede crear admins
        $canCreateRepro = $currentUser->role_as == 3; // Solo admin puede crear usuarios de Repro
        $canCreateEmpresa = $currentUser->role_as >= 2; // Admin y Repro pueden crear usuarios de empresa

        // NOTA: Los evaluados NO son usuarios del sistema
        // Se crean en tabla evaluados_orden al crear una orden (sin cuenta de usuario)

        // Cargar todas las empresas activas para el selector
        $empresas = Empresa::where('estado', 1)->orderBy('nombre', 'asc')->get();

        // Cargar todos los roles válidos (excluir evaluado), ordenados por nivel desc y nombre
        $roles = Role::where('name', '!=', 'evaluado')
            ->sinPersonales()
            ->orderByDesc('level')
            ->orderBy('display_name')
            ->get();

        // Mapa role_id → level para el JavaScript de las vistas
        $roleLevels = $roles->pluck('level', 'id');

        // Cargar todos los permisos agrupados por módulo
        $permissions = Permission::all()->groupBy('module');

        // Obtener la empresa_id del parámetro de consulta si existe (para preseleccionar)
        $empresa_id = request('empresa_id');

        $sedes = Sede::where('estado', 1)->orderBy('nombre')->get();

        return view('admin.user.add', compact(
            'canCreateAdmin',
            'canCreateRepro',
            'canCreateEmpresa',
            'empresas',
            'empresa_id',
            'roles',
            'roleLevels',
            'permissions',
            'sedes'
        ));
    }

    public function insertuser(UserFormRequest $request)
    {
        $user = new User();
        $currentUser = Auth::user();

        // Obtener el rol seleccionado por ID y derivar el nivel de acceso
        $selectedRole = Role::findOrFail($request->input('role_id'));
        $requestedLevel = $selectedRole->level;

        if ($requestedLevel >= 3 && $currentUser->role_as != 3) {
            return redirect()->back()->with('error', 'No tiene permisos para crear administradores');
        }

        if ($requestedLevel == 2 && $currentUser->role_as != 3) {
            return redirect()->back()->with('error', 'No tiene permisos para crear usuarios de REPRO');
        }

        if ($requestedLevel == 1 && $currentUser->role_as < 2) {
            return redirect()->back()->with('error', 'No tiene permisos para crear usuarios de empresas');
        }

        // Procesamiento de imagen de perfil
        if($request->hasFile('fotografia')) {
            $file = $request->file('fotografia');
            $ext = $file->getClientOriginalExtension();
            $filename = time().'.'.$ext;
            $file->move('assets/imgs/users', $filename);
            $user->fotografia = $filename;
        }

        // Asignar datos básicos
        $user->estado = 1;
        $user->principal = $request->has('principal') ? 1 : 0;
        $user->name = $request->input('name');
        $user->email = $request->input('email');

        // Generar contraseña temporal
        $tempPassword = 'Repro'.rand(1111,9999);
        $user->password = Hash::make($tempPassword);

        $user->telefono = $request->input('telefono');
        $user->celular = $request->input('celular');
        $user->direccion = $request->input('direccion');
        $user->fecha_nacimiento = $request->input('fecha_nacimiento');

        // Asignar nivel de acceso derivado del rol seleccionado
        $user->role_as = $requestedLevel;

        // Campos específicos según el tipo de usuario
        if ($user->role_as == 1) { // Usuario de empresa
            // Validar que se haya seleccionado una empresa
            if (!$request->input('empresa_id')) {
                return redirect()->back()->with('error', 'Debe seleccionar una empresa para usuarios de tipo empresa')->withInput();
            }
            $user->empresa_id = $request->input('empresa_id');
            $user->cargo = $request->input('cargo');
            // Sin titular: mismo perfil que un trabajador creado en el portal cliente
            if ((int) $user->principal !== 1) {
                $user->permisos = EmpresaPermisosSupport::permisosDefaultTrabajador();
            } else {
                $user->permisos = null;
            }
        } else {
            // Asegurarse de que empresa_id sea null para otros tipos de usuario
            $user->empresa_id = null;
        }

        if ($user->role_as == 2) { // Usuario de Repro
            $user->cargo = $request->input('cargo');
            $user->sede_id = $request->input('sede_id');
        }

        // Campos de identificación
        $user->documento_identidad = $request->input('documento_identidad');
        $user->tipo_documento = $request->input('tipo_documento');

        $user->save();

        // Asignar el rol seleccionado directamente por nombre
        $user->assignRole($selectedRole->name);

        // Asignar roles adicionales para usuarios de Repro
        if ($user->role_as == 2 && $request->has('additional_roles')) {
            foreach ($request->input('additional_roles') as $roleName) {
                if ($roleName !== 'evaluado') {
                    $user->assignRole($roleName);
                }
            }
        }

        // Enviar correo con credenciales
        try {
            Mail::to($user->email)->send(new UserMail($user, $tempPassword));
        } catch (\Exception $e) {
            Log::error('Error enviando email de bienvenida', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }

        return redirect('users')->with('status', __('Usuario agregado correctamente'));
    }

    public function edituser($id)
    {
        $user = User::with('roles.permissions')->find($id);
        $currentUser = Auth::user();

        // Solo el propio usuario puede editar su perfil; editar otros es solo para admin
        if ((int) Auth::id() !== (int) $id && $currentUser->role_as < 3) {
            abort(403);
        }

        // Verificar permisos
        if($currentUser->role_as == 1 && $currentUser->empresa_id != $user->empresa_id) {
            return redirect('users')->with('error', 'No tiene permisos para editar este usuario');
        }

        // Comprobar permisos según roles
        $canEditRole = $currentUser->role_as == 3; // Solo admins pueden cambiar roles
        $canEditEmpresa = $currentUser->role_as >= 2; // Admin y Repro pueden cambiar empresa

        // Cargar todas las empresas activas
        $empresas = Empresa::where('estado', 1)->orderBy('nombre', 'asc')->get();

        // Cargar todos los roles válidos (excluir evaluado)
        $roles = Role::where('name', '!=', 'evaluado')
            ->sinPersonales()
            ->orderByDesc('level')
            ->orderBy('display_name')
            ->get();

        // Mapa role_id → level para el JavaScript
        $roleLevels = $roles->pluck('level', 'id');

        // Determinar el rol principal activo del usuario para pre-seleccionar en el form
        $primaryRoleId = $user->roles
            ->whereIn('name', $roles->pluck('name')->toArray())
            ->first()?->id
            ?? $roles->where('level', $user->role_as)->first()?->id;

        // Cargar todos los permisos agrupados por módulo
        $permissions = Permission::all()->groupBy('module');

        // Obtener los roles actuales del usuario
        $userRoles = $user->roles->pluck('name')->toArray();

        $sedes = Sede::where('estado', 1)->orderBy('nombre')->get();

        return view('admin.user.edit', compact(
            'user',
            'canEditRole',
            'canEditEmpresa',
            'empresas',
            'roles',
            'roleLevels',
            'primaryRoleId',
            'permissions',
            'userRoles',
            'sedes'
        ));
    }

    public function updateuser(UserFormRequest $request, $id)
    {
        $user = User::find($id);
        $currentUser = Auth::user();

        // Solo el propio usuario puede editar su perfil; editar otros es solo para admin
        if ((int) Auth::id() !== (int) $id && $currentUser->role_as < 3) {
            abort(403);
        }

        // Verificar permisos
        if($currentUser->role_as == 1 && $currentUser->empresa_id != $user->empresa_id) {
            return redirect('users')->with('error', 'No tiene permisos para editar este usuario');
        }

        // Verificar si se intenta cambiar el rol y si tiene permisos
        if ($request->has('role_id') && $request->input('role_id')) {
            $newSelectedRole = Role::find($request->input('role_id'));
            $newLevel = $newSelectedRole?->level ?? $user->role_as;
            if ($user->role_as != $newLevel) {
                if ($currentUser->role_as != 3) {
                    return redirect()->back()->with('error', 'No tiene permisos para cambiar el rol del usuario');
                }
            }
        }

        // Procesar imagen si se ha subido una nueva
        if($request->hasFile('fotografia')) {
            $path = 'assets/imgs/users/'.$user->fotografia;
            if(File::exists($path)) {
                File::delete($path);
            }
            $file = $request->file('fotografia');
            $ext = $file->getClientOriginalExtension();
            $filename = time().'.'.$ext;
            $file->move('assets/imgs/users', $filename);
            $user->fotografia = $filename;
        }

        // Actualizar datos básicos
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->telefono = $request->input('telefono');
        $user->celular = $request->input('celular');
        $user->direccion = $request->input('direccion');
        $user->fecha_nacimiento = $request->input('fecha_nacimiento');

        // Checkbox principal: en empresa se puede desmarcar (HTML no envía el campo).
        if ($currentUser->role_as >= 2 && (int) $user->role_as === 2 && $request->has('principal')) {
            $user->principal = 1;
        }

        // Actualizar rol si se tiene permiso (solo admin)
        if ($currentUser->role_as == 3 && $request->has('role_id') && $request->input('role_id')) {
            $roleToAssign = Role::find($request->input('role_id'));
            if ($roleToAssign) {
                $user->role_as = $roleToAssign->level;

                // Reemplazar el rol principal preservando el rol personal (user_N) si existe
                $personalRoleName = 'user_' . $user->id;
                $nonPersonalRoles = $user->roles->filter(fn($r) => $r->name !== $personalRoleName);
                foreach ($nonPersonalRoles as $oldRole) {
                    $user->roles()->detach($oldRole->id);
                }
                $user->assignRole($roleToAssign->name);

                // Si ya no es empresa, limpiar empresa_id
                if ($user->role_as != 1) {
                    $user->empresa_id = null;
                }
            }
        }

        // Campos específicos según el tipo de usuario
        if ($user->role_as == 1) {
            if ($currentUser->role_as >= 2 && $request->has('empresa_id')) {
                // Validar que se haya seleccionado una empresa
                if (!$request->input('empresa_id')) {
                    return redirect()->back()->with('error', 'Debe seleccionar una empresa para usuarios de tipo empresa')->withInput();
                }

                // H-15: si se cambia la empresa, registrar evento y advertir al admin
                $nuevaEmpresaId = (int) $request->input('empresa_id');
                if ((int) $user->empresa_id !== $nuevaEmpresaId) {
                    $empresaAnteriorId = $user->empresa_id;
                    \Illuminate\Support\Facades\Log::warning('Cambio de empresa_id en usuario', [
                        'usuario_id'         => $user->id,
                        'usuario_email'      => $user->email,
                        'empresa_anterior'   => $empresaAnteriorId,
                        'empresa_nueva'      => $nuevaEmpresaId,
                        'modificado_por_id'  => $currentUser->id,
                        'modificado_por'     => $currentUser->email,
                    ]);
                    session()->flash('warning', "Atención: el usuario {$user->name} ha sido reasignado a otra empresa y dejará de ver las órdenes de la empresa anterior.");
                }

                $user->empresa_id = $nuevaEmpresaId;
            }
            $user->cargo = $request->input('cargo');
            if ($currentUser->role_as >= 2) {
                $user->principal = $request->boolean('principal') ? 1 : 0;
            }
            if ((int) $user->principal !== 1 && empty($user->permisos)) {
                $user->permisos = EmpresaPermisosSupport::permisosDefaultTrabajador();
            }
        }

        if ($user->role_as == 2 && $currentUser->role_as == 3) {
            $user->cargo = $request->input('cargo');
            $user->sede_id = $request->input('sede_id');
            // Actualizar permisos
            if ($request->has('permisos')) {
                $user->permisos = json_encode($request->input('permisos'));
            }

            // Sincronizar permisos del nuevo sistema roles/permissions.
            // Se usa 'permisos_enviados' (campo oculto) para detectar el envío incluso
            // cuando no hay ninguna casilla marcada (HTML no envía arrays vacíos).
            if ($request->has('permisos_enviados')) {
                $permisosSeleccionados = \App\Support\ReproPermisosSupport::conNucleo(
                    $request->input('permisos_sistema', [])
                );

                $personalRole = \App\Models\Role::firstOrCreate(
                    ['name' => 'user_' . $user->id],
                    [
                        'display_name' => 'Permisos de ' . $user->name,
                        'description' => 'Permisos individuales (interno)',
                        'level' => 2,
                    ]
                );
                $permissionIds = \App\Models\Permission::whereIn('name', $permisosSeleccionados)->pluck('id')->toArray();
                $personalRole->permissions()->sync($permissionIds);

                // El rol repro se queda: quitarlo dejaba al empleado sin
                // ordenes.editar si las casillas no cubrían el núcleo.
                $rolesMantener = [$personalRole->id];
                $baseRole = \App\Models\Role::where('name', 'repro')->first();
                if ($baseRole) {
                    $rolesMantener[] = $baseRole->id;
                }
                $user->roles()->syncWithoutDetaching($rolesMantener);
            }
        }

        // Resetear contraseña si se solicita
        if ($request->has('reset_password') && $currentUser->role_as >= 2) {
            $tempPassword = 'Repro'.rand(1111,9999);
            $user->password = Hash::make($tempPassword);

            // Enviar email con nueva contraseña
            try {
                Mail::to($user->email)->send(new UserResetPasswordMail($user, $tempPassword));
            } catch (\Exception $e) {
                Log::error('Error enviando email de reset', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Actualizar nuevos campos
        $user->documento_identidad = $request->input('documento_identidad');
        $user->tipo_documento = $request->input('tipo_documento');

        $user->update();

        return redirect('show-user/'.$id)->with('status', __('Usuario actualizado correctamente.'));
    }

    public function destroyuser($id)
    {
        $user = User::find($id);
        $currentUser = Auth::user();

        if ((int) $currentUser->id === (int) $user->id) {
            return redirect('users')->with('error', 'No puede eliminar su propio usuario.');
        }

        if ($currentUser->role_as < 3) {
            return redirect('users')->with('error', 'Solo el administrador puede eliminar usuarios.');
        }

        if ((int) $user->role_as >= 3) {
            $otrosAdmins = User::where('role_as', '>=', 3)->where('estado', 1)->where('id', '!=', $user->id)->count();
            if ($otrosAdmins === 0) {
                return redirect('users')->with('error', 'No se puede eliminar el último administrador del sistema.');
            }
        }

        // Eliminar foto si existe
        if ($user->fotografia) {
            $path = 'assets/imgs/users/'.$user->fotografia;
            if (File::exists($path)) {
                File::delete($path);
            }
        }

        // Marcar como eliminado y modificar email para permitir reutilización
        $user->estado = 0;
        $user->email = $user->email.'-Deleted'.$user->id;
        $user->update();

        $avisoPrincipal = ((int) $user->principal === 1)
            ? ' Era el usuario principal de su empresa; asigne otro titular si hace falta.'
            : '';

        return redirect('users')->with('status', __('Usuario eliminado correctamente.') . $avisoPrincipal);
    }

    public function pdf(Request $request)
    {
        $queryUser = $request->input('fuser');
        $role_filter = $request->input('role_filter');
        $empresa_filter = $request->input('empresa_filter');

        $usuarios = $this->buildUsersQuery($request->all())->with('empresa')->get();
        $nompdf = date('m/d/Y g:ia');
        $path = public_path('assets/imgs/');

        $config = Config::first();
        $currency = $config->currency_simbol;

        $imagen = null;
        if ($config->logo && file_exists(public_path('assets/imgs/logos/'.$config->logo))) {
            $imagen = public_path('assets/imgs/logos/'.$config->logo);
        }

        // Título del PDF
        $titulo = 'Listado de Usuarios';
        if($queryUser) {
            $titulo .= ' (Filtro: '.$queryUser.')';
        }

        // Agregar información de rol al título si se filtró por rol
        if($role_filter !== null && $role_filter !== '') {
            $rolMap = [
                '1' => 'Empresas',
                '2' => 'Repro',
                '3' => 'Administradores'
            ];
            // NOTA: '0' => 'Evaluados' eliminado - evaluados no son usuarios
            $rolName = isset($rolMap[$role_filter]) ? $rolMap[$role_filter] : 'Desconocido';
            $titulo .= ' - '.$rolName;
        }

        // Agregar información de empresa al título si se filtró por empresa
        if($empresa_filter !== null && $empresa_filter !== '') {
            $empresa = Empresa::find($empresa_filter);
            if($empresa) {
                $titulo .= ' - Empresa: '.$empresa->nombre;
            }
        }

        $pdf = Pdf::loadView('admin.user.pdf', [
            'usuarios' => $usuarios,
            'path' => $path,
            'config' => $config,
            'imagen' => $imagen,
            'currency' => $currency,
            'titulo' => $titulo,
            'queryUser' => $queryUser,
            'role_filter' => $role_filter,
            'empresa_filter' => $empresa_filter
        ]);
        return $pdf->stream($titulo.' '.$nompdf.'.pdf');
    }

    public function pdfuser($id)
    {
        $usuario = User::with('empresa')->find($id);
        $currentUser = Auth::user();

        // Verificar permisos
        if($currentUser->role_as == 1 && $currentUser->empresa_id != $usuario->empresa_id) {
            return redirect('users')->with('error', 'No tiene permisos para ver este usuario');
        }

        $verpdf = "Browser";
        $nompdf = date('m/d/Y g:ia');

        // Configuración
        $config = Config::first();
        $currency = $config->currency_simbol;

        // Obtener rutas absolutas para las imágenes
        $pathuser = public_path('assets/imgs/users/');
        $defaultImagePath = public_path('assets/imgs/users/usericon4.png');

        // Imagen del logo
        $imagen = null;
        if ($config->logo && file_exists(public_path('assets/imgs/logos/'.$config->logo))) {
            $imagen = public_path('assets/imgs/logos/'.$config->logo);
        }

        $pdf = Pdf::loadView('admin.user.pdfuser', compact('usuario', 'pathuser', 'defaultImagePath', 'config', 'imagen', 'currency'));

        $pdf->getDomPDF()->set_option("enable_html5_parser", true);
        $pdf->getDomPDF()->set_option("isHtml5ParserEnabled", true);
        $pdf->getDomPDF()->set_option("isRemoteEnabled", true);
        $pdf->setPaper('Letter', 'portrait');

        return $pdf->stream('Usuario_'.$usuario->name.'_'.$nompdf.'.pdf');
    }

    // Método para cambiar contraseña por el propio usuario
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();

        // Verificar contraseña actual
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'La contraseña actual es incorrecta']);
        }

        // Actualizar contraseña
        $user->password = Hash::make($request->new_password);
        $user->save();

        return redirect()->back()->with('status', 'Contraseña actualizada correctamente');
    }
}
