<?php

namespace App\Http\Controllers\Empresa;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\User;
use App\Models\EvaluadoOrden;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class EmpresaController extends Controller
{
    /**
     * Mostrar detalle de una orden para empresa
     */
    public function verOrden(\App\Models\Orden $orden)
    {
        $user = Auth::user();
        $userEmpresaId = $user->empresa_id;

        if (!$userEmpresaId) {
            Log::warning('Usuario empresa sin empresa_id intentó ver orden', [
                'user_id' => $user->id,
                'orden_id' => $orden->id,
            ]);
            return redirect()->route('dashboard')
                ->with('error', 'Su usuario no tiene una empresa asociada. Contacte al administrador.');
        }

        // Comparación tolerante a tipos (int vs string)
        if ((int) $orden->empresa_id !== (int) $userEmpresaId) {
            Log::warning('Usuario empresa intentó ver orden de otra empresa', [
                'user_id' => $user->id,
                'user_empresa_id' => $userEmpresaId,
                'orden_id' => $orden->id,
                'orden_empresa_id' => $orden->empresa_id,
            ]);
            abort(403, 'Acceso no autorizado. Esta orden no pertenece a su empresa.');
        }

        $orden->load(['evaluados.documentos.subidoPor', 'evaluados.cuestionario']);

        return view('empresa.ordenes.show', compact('orden'));
    }
    /**
     * Listar órdenes de la empresa (solo lectura)
     */
    public function indexOrdenesEmpresa()
    {
        $empresa = Auth::user()->empresa;
        if (!$empresa) {
            return back()->with('error', 'No tiene una empresa asociada');
        }

        // Eager loading y conteo de evaluados
        $ordenes = $empresa->ordenes()
            ->withCount('evaluados')
            ->with(['evaluados' => function ($q) {
                $q->orderBy('id')->limit(1);
            }])
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('empresa.ordenes.index', compact('ordenes'));
    }
    /**
     * Verificar que el usuario sea de tipo empresa
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (Auth::user()->role_as != 1) {
                abort(403, 'Acceso no autorizado');
            }
            return $next($request);
        });
    }

    /**
     * Mostrar información de la empresa del usuario
     */
    public function miEmpresa()
    {
        $empresa = Auth::user()->empresa;

        if (!$empresa) {
            return back()->with('error', 'No tiene una empresa asociada');
        }

        // Estados agrupados según flujo de 8 etapas:
        // Pendientes: solicitud, autorizacion, requisito, programacion
        // En proceso: en_proceso, preliminar, final
        // Completadas: entregado

        $estadosPendientes = ['solicitud', 'autorizacion', 'requisito', 'programacion'];
        $estadosProceso = ['en_proceso', 'preliminar', 'final'];


        // Estadísticas de la empresa
        $stats = [
            'total_ordenes' => $empresa->ordenes()->count(),
            'ordenes_pendientes' => $empresa->ordenes()->whereIn('estado', $estadosPendientes)->count(),
            'ordenes_proceso' => $empresa->ordenes()->whereIn('estado', $estadosProceso)->count(),
            'ordenes_completadas' => $empresa->ordenes()->where('estado', 'entregado')->count(),
            'total_evaluados' => EvaluadoOrden::whereHas('orden', function($q) use ($empresa) {
                $q->where('empresa_id', $empresa->id);
            })->count(),
            'cuestionarios_completados' => EvaluadoOrden::whereHas('orden', function($q) use ($empresa) {
                $q->where('empresa_id', $empresa->id);
            })->where('cuestionario_completado', true)->count(),
            'usuarios_activos' => $empresa->usuariosActivos()->count(),
        ];

        return view('empresa.mi-empresa.index', compact('empresa', 'stats'));
    }

    /**
     * Mostrar formulario de edición de empresa
     */
    public function editarEmpresa()
    {
        $empresa = Auth::user()->empresa;

        if (!$empresa) {
            return back()->with('error', 'No tiene una empresa asociada');
        }

        // Solo el usuario principal puede editar
        if (Auth::user()->principal != 1) {
            return back()->with('error', 'Solo el usuario principal puede editar la información de la empresa');
        }

        return view('empresa.mi-empresa.edit', compact('empresa'));
    }

    /**
     * Actualizar información de la empresa
     */
    public function actualizarEmpresa(Request $request)
    {
        $empresa = Auth::user()->empresa;

        if (!$empresa) {
            return back()->with('error', 'No tiene una empresa asociada');
        }

        // Solo el usuario principal puede editar
        if (Auth::user()->principal != 1) {
            return back()->with('error', 'Solo el usuario principal puede editar la información de la empresa');
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'nit' => 'nullable|string|max:50',
            'direccion' => 'nullable|string|max:500',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'descripcion' => 'nullable|string|max:1000',
            'sitio_web' => 'nullable|url|max:255',
            'contacto_nombre' => 'nullable|string|max:255',
            'contacto_cargo' => 'nullable|string|max:255',
            'contacto_telefono' => 'nullable|string|max:20',
            'contacto_email' => 'nullable|email|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Manejar logo
        if ($request->hasFile('logo')) {
            // Eliminar logo anterior si existe
            if ($empresa->logo && file_exists(public_path('assets/imgs/empresas/' . $empresa->logo))) {
                unlink(public_path('assets/imgs/empresas/' . $empresa->logo));
            }

            $logoName = time() . '_' . $request->file('logo')->getClientOriginalName();
            $request->file('logo')->move(public_path('assets/imgs/empresas'), $logoName);
            $validated['logo'] = $logoName;
        }

        $empresa->update($validated);

        return redirect()->route('empresa.mi-empresa')->with('success', 'Información de empresa actualizada correctamente');
    }

    // ========================================
    // GESTIÓN DE USUARIOS DE EMPRESA
    // ========================================

    /**
     * Listar usuarios de la empresa
     */
    public function usuarios()
    {
        // Solo el usuario principal puede ver usuarios
        if (Auth::user()->principal != 1) {
            return back()->with('error', 'Solo el usuario principal puede gestionar usuarios');
        }

        $empresa = Auth::user()->empresa;
        $usuarios = $empresa->usuarios()
            ->orderBy('principal', 'desc')
            ->orderBy('name', 'asc')
            ->paginate(10);

        return view('empresa.usuarios.index', compact('usuarios', 'empresa'));
    }

    /**
     * Mostrar formulario para crear usuario
     */
    public function crearUsuario()
    {
        if (Auth::user()->principal != 1) {
            return back()->with('error', 'Solo el usuario principal puede crear usuarios');
        }

        return view('empresa.usuarios.create');
    }

    /**
     * Guardar nuevo usuario de empresa
     */
    public function guardarUsuario(Request $request)
    {
        if (Auth::user()->principal != 1) {
            return back()->with('error', 'Solo el usuario principal puede crear usuarios');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email|max:255',
            'password' => 'required|string|min:8|confirmed',
            'telefono' => 'nullable|string|max:20',
            'cargo' => 'nullable|string|max:255',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'telefono' => $validated['telefono'] ?? null,
            'cargo' => $validated['cargo'] ?? null,
            'empresa_id' => Auth::user()->empresa_id,
            'role_as' => 1, // Usuario tipo empresa
            'principal' => 0, // No es principal
            'estado' => 1, // Activo
            'permisos' => json_encode($request->input('permisos_empresa', [])),
        ]);

        return redirect()->route('empresa.usuarios')->with('success', 'Usuario creado correctamente');
    }

    /**
     * Mostrar formulario para editar usuario
     */
    public function editarUsuario(User $usuario)
    {
        if (Auth::user()->principal != 1) {
            return back()->with('error', 'Solo el usuario principal puede editar usuarios');
        }

        // Verificar que el usuario pertenece a la misma empresa
        if ($usuario->empresa_id != Auth::user()->empresa_id) {
            abort(403, 'Acceso no autorizado');
        }

        // No permitir editar al usuario principal
        if ($usuario->principal == 1) {
            return back()->with('error', 'No se puede editar al usuario principal desde aquí');
        }

        return view('empresa.usuarios.edit', compact('usuario'));
    }

    /**
     * Actualizar usuario de empresa
     */
    public function actualizarUsuario(Request $request, User $usuario)
    {
        if (Auth::user()->principal != 1) {
            return back()->with('error', 'Solo el usuario principal puede editar usuarios');
        }

        // Verificar que el usuario pertenece a la misma empresa
        if ($usuario->empresa_id != Auth::user()->empresa_id) {
            abort(403, 'Acceso no autorizado');
        }

        // No permitir editar al usuario principal
        if ($usuario->principal == 1) {
            return back()->with('error', 'No se puede editar al usuario principal desde aquí');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($usuario->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'telefono' => 'nullable|string|max:20',
            'cargo' => 'nullable|string|max:255',
            'estado' => 'required|in:0,1',
        ]);

        $usuario->name = $validated['name'];
        $usuario->email = $validated['email'];
        $usuario->telefono = $validated['telefono'] ?? null;
        $usuario->cargo = $validated['cargo'] ?? null;
        $usuario->estado = $validated['estado'];

        if (!empty($validated['password'])) {
            $usuario->password = Hash::make($validated['password']);
        }

        $usuario->permisos = json_encode($request->input('permisos_empresa', []));

        $usuario->save();

        return redirect()->route('empresa.usuarios')->with('success', 'Usuario actualizado correctamente');
    }

    /**
     * Eliminar usuario de empresa
     */
    public function eliminarUsuario(User $usuario)
    {
        if (Auth::user()->principal != 1) {
            return back()->with('error', 'Solo el usuario principal puede eliminar usuarios');
        }

        // Verificar que el usuario pertenece a la misma empresa
        if ($usuario->empresa_id != Auth::user()->empresa_id) {
            abort(403, 'Acceso no autorizado');
        }

        // No permitir eliminar al usuario principal
        if ($usuario->principal == 1) {
            return back()->with('error', 'No se puede eliminar al usuario principal');
        }

        // No permitir eliminar a uno mismo
        if ($usuario->id == Auth::id()) {
            return back()->with('error', 'No se puede eliminar a sí mismo');
        }

        $usuario->delete();

        return redirect()->route('empresa.usuarios')->with('success', 'Usuario eliminado correctamente');
    }

    // ========================================
    // CUESTIONARIOS (SOLO LECTURA)
    // ========================================

    /**
     * Listar cuestionarios de la empresa (solo lectura)
     */
    public function cuestionarios(Request $request)
    {
        $empresa = Auth::user()->empresa;

        $query = EvaluadoOrden::whereHas('orden', function($q) use ($empresa) {
            $q->where('empresa_id', $empresa->id);
        })->with(['orden']);

        // Filtro por estado de cuestionario
        if ($request->filled('estado')) {
            if ($request->estado === 'completado') {
                $query->where('cuestionario_completado', true);
            } elseif ($request->estado === 'pendiente') {
                $query->where('cuestionario_completado', false);
            }
        }

        // Filtro por orden
        if ($request->filled('orden_id')) {
            $query->where('orden_id', $request->orden_id);
        }

        // Búsqueda por nombre
        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('apellidos', 'like', "%{$buscar}%")
                  ->orWhere('email', 'like', "%{$buscar}%")
                  ->orWhere('dpi', 'like', "%{$buscar}%");
            });
        }

        $cuestionarios = $query->orderBy('created_at', 'desc')->paginate(15);

        // Órdenes para el filtro
        $ordenes = $empresa->ordenes()->orderBy('created_at', 'desc')->get();

        // Estadísticas
        $stats = [
            'total' => EvaluadoOrden::whereHas('orden', function($q) use ($empresa) {
                $q->where('empresa_id', $empresa->id);
            })->count(),
            'completados' => EvaluadoOrden::whereHas('orden', function($q) use ($empresa) {
                $q->where('empresa_id', $empresa->id);
            })->where('cuestionario_completado', true)->count(),
            'pendientes' => EvaluadoOrden::whereHas('orden', function($q) use ($empresa) {
                $q->where('empresa_id', $empresa->id);
            })->where('cuestionario_completado', false)->count(),
        ];

        return view('empresa.cuestionarios.index', compact('cuestionarios', 'ordenes', 'stats'));
    }

    /**
     * Ver detalle de cuestionario (solo lectura)
     */
    public function verCuestionario(EvaluadoOrden $evaluado)
    {
        $empresa = Auth::user()->empresa;

        // Verificar que el evaluado pertenece a una orden de esta empresa
        if ($evaluado->orden->empresa_id != $empresa->id) {
            abort(403, 'Acceso no autorizado');
        }

        // La vista maneja la visualización según el estado de los resultados
        // (muestra "en proceso" si no están disponibles)

        return view('empresa.cuestionarios.show', compact('evaluado'));
    }

        /**
         * Descargar PDF del cuestionario individual para empresa (solo si autorizado)
         */
        public function generarPDFCuestionarioEmpresa(EvaluadoOrden $evaluado)
        {
            $empresa = Auth::user()->empresa;
            // Verificar que el evaluado pertenece a una orden de esta empresa
            if ($evaluado->orden->empresa_id != $empresa->id) {
                abort(403, 'Acceso no autorizado');
            }
            // Verificar que la orden esté entregada y resultados disponibles para empresa
            if (!$evaluado->orden->resultadosDisponiblesParaEmpresa()) {
                abort(403, 'Resultados no autorizados para descarga');
            }

            $evaluado->load(['cuestionario', 'documentos', 'responsable', 'orden.empresa']);

            $pdf = app('dompdf.wrapper');
            $pdf->loadView('pdf.cuestionario_empresa', ['evaluado' => $evaluado]);
            $nombreArchivo = $evaluado->nombre . '_' .
                ($evaluado->apellidos ?? '') . '_Orden' .
                $evaluado->orden->codigo_orden . '.pdf';
            return $pdf->download($nombreArchivo);
        }
}
