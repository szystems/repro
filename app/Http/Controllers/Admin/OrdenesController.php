<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrdenFormRequest;
use App\Mail\EvaluadoAsignadoMail;
use App\Models\Orden;
use App\Models\EvaluadoOrden;
use App\Models\Empresa;
use App\Models\Sede;
use App\Models\User;
use App\Notifications\OrdenCreadaNotification;
use App\Notifications\ResultadosDisponiblesNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class OrdenesController extends Controller
{
    /**
     * Mostrar lista de órdenes
     */
    public function index(Request $request)
    {
        $query = Orden::with(['empresa', 'creador', 'poligrafista']);

        // Filtros por rol
        if (Auth::user()->role_as == 1) {
            // Usuario empresa: solo ve sus órdenes
            $query->where('empresa_id', Auth::user()->empresa_id);
        } elseif ($request->filled('empresa_id') && Auth::user()->role_as >= 2) {
            $query->where('empresa_id', $request->empresa_id);
        }

        // Filtros adicionales
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('tipo_servicio')) {
            $query->whereHas('evaluados', function($q) use ($request) {
                $q->where('tipo_servicio', $request->tipo_servicio);
            });
        }

        if ($request->filled('sede_id')) {
            $query->where('sede_id', $request->sede_id);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_solicitud', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_solicitud', '<=', $request->fecha_hasta);
        }

        // Búsqueda por código o empresa
        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function($q) use ($buscar) {
                $q->where('codigo_orden', 'LIKE', "%{$buscar}%")
                  ->orWhereHas('empresa', function($eq) use ($buscar) {
                      $eq->where('nombre', 'LIKE', "%{$buscar}%");
                  });
            });
        }

        $ordenes = $query->with(['empresa', 'creador', 'sede', 'evaluados'])
                        ->withCount('evaluados')
                        ->orderBy('fecha_solicitud', 'desc')
                        ->paginate(15);

        // Datos para filtros
        $empresas = Auth::user()->role_as >= 2
            ? Empresa::orderBy('nombre')->get()
            : collect();

        $estados = Orden::estadosDisponibles();

        $tiposServicio = [
            'poligrafo' => 'Polígrafo',
            'vsa' => 'VSA (Voice Stress Analysis)',
            'socioeconomico' => 'Estudio Socioeconómico'
        ];

        $sedes = Auth::user()->role_as >= 2
            ? Sede::where('estado', 1)->orderBy('nombre')->get()
            : collect();

        return view('admin.ordenes.index', compact('ordenes', 'empresas', 'estados', 'tiposServicio', 'sedes'));
    }

    /**
     * Resumen estadístico de órdenes para admin/repro
     */
    public function resumen(): \Illuminate\View\View
    {
        $totalOrdenes = Orden::count();
        $ordenesActivas = Orden::where('estado', 'en_proceso')->count();
        $ordenesCompletadas = Orden::where('estado', 'completada')->count();
        $ordenesPendientes = Orden::where('estado', 'pendiente')->count();

        $totalEvaluados = EvaluadoOrden::count();
        $evaluadosCompletados = EvaluadoOrden::where('estado_evaluacion', 'completado')->count();

        $porEmpresa = Orden::select('empresa_id', DB::raw('COUNT(*) as total'))
            ->with('empresa:id,nombre')
            ->groupBy('empresa_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $porSede = Orden::select('sede_id', DB::raw('COUNT(*) as total'))
            ->with('sede:id,nombre')
            ->whereNotNull('sede_id')
            ->groupBy('sede_id')
            ->orderByDesc('total')
            ->get();

        return view('admin.ordenes.resumen', compact(
            'totalOrdenes', 'ordenesActivas', 'ordenesCompletadas', 'ordenesPendientes',
            'totalEvaluados', 'evaluadosCompletados', 'porEmpresa', 'porSede'
        ));
    }

    /**
     * Mostrar formulario de creación
     */
    public function create()
    {
        // Todas las empresas para admin/repro, solo la propia para empresa
        $empresas = collect();

        // Admin (role_as=3) o Repro (role_as=2) pueden crear órdenes para cualquier empresa
        if (Auth::user()->role_as >= 2) {
            $empresas = Empresa::where('estado', 1)->orderBy('nombre')->get();
        } elseif (Auth::user()->role_as == 1) {
            // Usuario empresa solo ve su propia empresa (pre-seleccionada)
            $empresas = collect([Auth::user()->empresa]);
        }

        // Solo admin/repro pueden asignar polígrafos específicos
        $poligrafistas = collect();
        if (Auth::user()->role_as >= 2) {
            $poligrafistas = User::whereHas('roles', function($query) {
                $query->whereIn('name', ['admin', 'repro', 'poligrafo']);
            })->where('estado', 1)->orderBy('name')->get();
        }

        $sedes = Sede::where('estado', 1)->orderBy('nombre')->get();

        return view('admin.ordenes.create', compact('empresas', 'poligrafistas', 'sedes'));
    }

    /**
     * Almacenar nueva orden
     */
    public function store(Request $request)
    {

        // Validación manual temporal
        $validated = $request->validate([
            'empresa_id' => 'required|exists:empresas,id',
            'sede_id' => 'nullable|exists:sedes,id',
            'observaciones_internas' => 'nullable|string|max:500',
            'prioridad' => 'nullable|in:baja,normal,alta,urgente',
            'fecha_limite' => 'nullable|date|after:today',
            'instrucciones_generales' => 'nullable|string|max:1000',
            'requerimientos_generales' => 'nullable|string|max:1000',
            'evaluados' => 'required|array|min:1',
            'evaluados.*.nombre' => 'required|string|max:100',
            'evaluados.*.apellidos' => 'required|string|max:100',
            'evaluados.*.dpi' => 'required|string|size:13',
            'evaluados.*.email' => 'required|email|max:100',
            'evaluados.*.telefono' => 'nullable|string|max:20',
            'evaluados.*.tipo_servicio' => 'required|in:poligrafo,vsa,socioeconomico',
            'evaluados.*.tipo_formulario' => 'required|in:preempleo,periodica,especifica',
            'evaluados.*.fecha_programada' => 'nullable|date|after:today',
            'evaluados.*.poligrafista_id' => 'nullable|exists:users,id',
        ]);

        // Validar que no haya DPI+servicio duplicados en la misma orden
        if (is_array($validated['evaluados'])) {
            $combinaciones = collect($validated['evaluados'])
                ->map(fn($e) => ($e['dpi'] ?? '') . '|' . ($e['tipo_servicio'] ?? ''))
                ->filter();
            if ($combinaciones->count() !== $combinaciones->unique()->count()) {
                return back()->withErrors(['evaluados' => 'No se puede repetir el mismo DPI con el mismo tipo de servicio en la misma orden.'])->withInput();
            }
        }

        DB::beginTransaction();

        try {
            // Crear orden con solo los campos permitidos
            $datosOrden = [
                'instrucciones_generales' => $validated['instrucciones_generales'] ?? null,
                'tipo_creador' => Auth::user()->role_as >= 2 ? 'repro' : 'empresa',
                'creado_por' => Auth::id(),
                'fecha_solicitud' => now()->toDateString(),
                'estado' => 'solicitud',
            ];

            // Campos exclusivos REPRO (role_as >= 2)
            if (Auth::user()->role_as >= 2) {
                $datosOrden['observaciones_internas'] = $validated['observaciones_internas'] ?? null;
                $datosOrden['prioridad'] = $validated['prioridad'] ?? 'normal';
                $datosOrden['fecha_limite'] = $validated['fecha_limite'] ?? null;
                $datosOrden['requerimientos_generales'] = $validated['requerimientos_generales'] ?? null;
                $datosOrden['sede_id'] = $validated['sede_id'] ?? null;
            }

            if (Auth::user()->role_as == 1) {
                // Usuario empresa: usar su empresa_id
                if (!Auth::user()->empresa_id) {
                    throw new \RuntimeException('Su usuario no tiene una empresa asociada. Contacte al administrador.');
                }
                $datosOrden['empresa_id'] = Auth::user()->empresa_id;
            } else {
                $datosOrden['empresa_id'] = $validated['empresa_id'];
            }

            $orden = Orden::create($datosOrden);

            if ($request->has('evaluados')) {
                $this->procesarEvaluados($orden, $request->evaluados);
            }

            DB::commit();

            // Notificar a usuarios de la sede asignada
            if ($orden->sede_id) {
                $this->notificarUsuariosSede($orden);
            }

            // Notificación in-app a usuarios REPRO/admin (excepto al creador)
            $usuariosNotificar = User::where('role_as', '>=', 2)
                ->where('estado', 1)
                ->where('id', '!=', Auth::id())
                ->get();
            foreach ($usuariosNotificar as $usuario) {
                $usuario->notify(new OrdenCreadaNotification($orden));
            }

            // Redirigir según el rol del usuario
            if (Auth::user()->role_as == 1) {
                // Usuario empresa: redirigir a módulo empresa
                return redirect()->route('empresa.ordenes.show', $orden)
                    ->with('success', 'Orden creada exitosamente.');
            }

            return redirect()->route('ordenes.show', $orden)
                ->with('success', 'Orden creada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error al crear orden:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
            ]);

            return back()->with('error', 'Error al crear la orden: ' . $e->getMessage())
                        ->withInput();
        }
    }

    /**
     * Mostrar orden específica
     */
    public function show(Orden $orden)
    {
        if (!$this->usuarioPuedeVerOrden($orden)) {
            abort(403);
        }

        $orden->load([
            'empresa',
            'creador',
            'sede',
            'evaluados' => function($query) {
                $query->with(['poligrafista', 'responsable', 'sede', 'cuestionario', 'documentos'])->orderBy('nombre');
            }
        ]);

        $estados = Orden::estadosDisponibles();

        // Datos para modal de programar cita
        $sedes = Sede::activas()->orderBy('nombre')->get();
        $poligrafistas = User::poligrafistas()->get();

        return view('admin.ordenes.show', compact('orden', 'estados', 'sedes', 'poligrafistas'));
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(Orden $orden)
    {
        if (!$this->usuarioPuedeEditarOrden($orden)) {
            abort(403);
        }

        $orden->load('evaluados');

        $empresas = collect();

        if (Auth::user()->hasAnyRole(['admin', 'repro'])) {
            $empresas = Empresa::where('estado', 1)->orderBy('nombre')->get();
        }

        $poligrafistas = User::whereHas('roles', function($query) {
            $query->whereIn('name', ['admin', 'repro', 'poligrafo']);
        })->where('estado', 1)->orderBy('name')->get();

        $estados = Orden::estadosDisponibles();

        $sedes = Sede::where('estado', 1)->orderBy('nombre')->get();

        return view('admin.ordenes.edit', compact('orden', 'empresas', 'poligrafistas', 'estados', 'sedes'));
    }

    /**
     * Actualizar orden
     */
    public function update(Request $request, Orden $orden)
    {
        if (!$this->usuarioPuedeEditarOrden($orden)) {
            abort(403);
        }

        // Validación
        $validated = $request->validate([
            'empresa_id' => 'required|exists:empresas,id',
            'sede_id' => 'nullable|exists:sedes,id',
            'observaciones_internas' => 'nullable|string|max:500',
            'prioridad' => 'nullable|in:baja,normal,alta,urgente',
            'fecha_limite' => 'nullable|date|after:today',
            'fecha_solicitud' => 'nullable|date',
            'instrucciones_generales' => 'nullable|string|max:1000',
            'requerimientos_generales' => 'nullable|string|max:1000',
            'poligrafista_id' => 'nullable|exists:users,id',
            'evaluados' => 'required|array|min:1',
            'evaluados.*.id' => 'nullable|exists:evaluados_orden,id',
            'evaluados.*.nombre' => 'required|string|max:100',
            'evaluados.*.apellidos' => 'required|string|max:100',
            'evaluados.*.dpi' => 'required|string|size:13',
            'evaluados.*.email' => 'required|email|max:100',
            'evaluados.*.telefono' => 'nullable|string|max:20',
            'evaluados.*.tipo_servicio' => 'required|in:poligrafo,vsa,socioeconomico',
            'evaluados.*.tipo_formulario' => 'required|in:preempleo,periodica,especifica',
            'evaluados.*.fecha_programada' => 'nullable|date|after:today',
            'evaluados.*.poligrafista_id' => 'nullable|exists:users,id',
        ]);

        DB::beginTransaction();

        try {
            // Actualizar datos básicos de la orden
            $datosOrden = [
                'instrucciones_generales' => $validated['instrucciones_generales'] ?? $orden->instrucciones_generales,
            ];

            // Campos exclusivos REPRO (role_as >= 2)
            if (Auth::user()->role_as >= 2) {
                $datosOrden['observaciones_internas'] = $validated['observaciones_internas'] ?? $orden->observaciones_internas;
                $datosOrden['prioridad'] = $validated['prioridad'] ?? $orden->prioridad ?? 'normal';
                $datosOrden['requerimientos_generales'] = $validated['requerimientos_generales'] ?? $orden->requerimientos_generales;
            }

            // Solo actualizar estos campos si vienen explícitamente en el request
            if (isset($validated['fecha_limite']) && Auth::user()->role_as >= 2) {
                $datosOrden['fecha_limite'] = $validated['fecha_limite'];
            }
            if (isset($validated['fecha_solicitud'])) {
                $datosOrden['fecha_solicitud'] = $validated['fecha_solicitud'];
            }
            if (isset($validated['poligrafista_id'])) {
                $datosOrden['poligrafista_id'] = $validated['poligrafista_id'];
            }
            if (Auth::user()->role_as >= 2 && array_key_exists('sede_id', $validated)) {
                $datosOrden['sede_id'] = $validated['sede_id'];
            }

            if (Auth::user()->hasAnyRole(['admin', 'repro'])) {
                $datosOrden['empresa_id'] = $validated['empresa_id'];
            }

            $orden->update($datosOrden);

            if ($request->has('evaluados')) {
                $this->procesarEvaluados($orden, $request->evaluados, true);
            }

            DB::commit();

            // Redirigir según el rol del usuario
            if (Auth::user()->role_as == 1) {
                // Usuario empresa: redirigir a módulo empresa
                return redirect()->route('empresa.ordenes.show', $orden)
                    ->with('success', 'Orden actualizada exitosamente.');
            }

            return redirect()->route('ordenes.show', $orden)
                ->with('success', 'Orden actualizada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error al actualizar orden:', [
                'error' => $e->getMessage(),
                'orden_id' => $orden->id,
                'user_id' => Auth::id()
            ]);

            return back()->with('error', 'Error al actualizar la orden: ' . $e->getMessage())
                        ->withInput();
        }
    }

    /**
     * Eliminar orden
     */
    public function destroy(Orden $orden)
    {
        if (!Auth::user()->hasRole('admin')) {
            abort(403, 'Solo los administradores pueden eliminar órdenes.');
        }

        if (in_array($orden->estado, ['en_proceso', 'analisis', 'entregado'])) {
            return back()->with('error', 'No se puede eliminar una orden que está en proceso o completada.');
        }

        $codigoOrden = $orden->codigo_orden;
        $orden->delete();

        // Redirigir según el rol del usuario
        if (Auth::user()->role_as == 1) {
            return redirect()->route('empresa.ordenes.index')
                ->with('success', "Orden {$codigoOrden} eliminada exitosamente.");
        }

        return redirect()->route('ordenes.index')
            ->with('success', "Orden {$codigoOrden} eliminada exitosamente.");
    }

    /**
     * Cambiar estado de la orden
     */
    public function cambiarEstado(Request $request, Orden $orden)
    {
        $request->validate([
            'nuevo_estado' => 'required|in:solicitud,validacion,registrado,programacion,en_proceso,operaciones,analisis,preliminar,final,entregado,cancelado',
            'observaciones' => 'nullable|string|max:500'
        ]);

        if (!$orden->puedeTransicionarA($request->nuevo_estado)) {
            $estadoActualTexto = $orden->estado_human;
            return back()->with('error', "No se puede cambiar de '{$estadoActualTexto}' a '{$request->nuevo_estado}'. Transición no permitida.");
        }

        $estadoAnterior = $orden->estado;

        if ($orden->cambiarEstado($request->nuevo_estado)) {
            if ($request->filled('observaciones')) {
                $orden->update(['observaciones' => $request->observaciones]);
            }

            return back()->with('success', "Estado cambiado de '{$estadoAnterior}' a '{$request->nuevo_estado}'.");
        }

        return back()->with('error', 'No se pudo cambiar el estado de la orden.');
    }

    /**
     * Cambiar estado de evaluación o formulario de un evaluado.
     */
    public function cambiarEstadoEvaluado(Request $request, EvaluadoOrden $evaluado): \Illuminate\Http\RedirectResponse
    {
        if (Auth::user()->role_as < 2) {
            return back()->with('error', 'No tiene permisos para realizar esta acción.');
        }

        $estadosEvaluacion = implode(',', array_keys(EvaluadoOrden::estadosEvaluacionDisponibles()));
        $estadosFormulario = implode(',', array_keys(EvaluadoOrden::estadosFormularioDisponibles()));

        $request->validate([
            'tipo_estado' => 'required|in:evaluacion,formulario',
            'nuevo_estado' => "required|string",
        ]);

        $tipo = $request->tipo_estado;
        $nuevoEstado = $request->nuevo_estado;
        $nombre = "{$evaluado->nombre} {$evaluado->apellidos}";

        if ($tipo === 'evaluacion') {
            if (!$evaluado->puedeTransicionarEstadoEvaluacion($nuevoEstado)) {
                $estadoActual = $evaluado->estado_evaluacion_texto;
                return back()->with('error', "No se puede cambiar evaluación de '{$estadoActual}' a '{$nuevoEstado}' para {$nombre}.");
            }
            $estadoAnterior = $evaluado->estado_evaluacion_texto;
            $evaluado->cambiarEstadoEvaluacion($nuevoEstado);
            $estadoNuevoTexto = $evaluado->fresh()->estado_evaluacion_texto;
            return back()->with('success', "Estado evaluación de {$nombre}: '{$estadoAnterior}' → '{$estadoNuevoTexto}'.");
        }

        if ($tipo === 'formulario') {
            if (!$evaluado->puedeTransicionarEstadoFormulario($nuevoEstado)) {
                $estadoActual = EvaluadoOrden::estadosFormularioDisponibles()[$evaluado->estado_formulario] ?? $evaluado->estado_formulario;
                return back()->with('error', "No se puede cambiar formulario de '{$estadoActual}' a '{$nuevoEstado}' para {$nombre}.");
            }
            $estadoAnterior = EvaluadoOrden::estadosFormularioDisponibles()[$evaluado->estado_formulario] ?? $evaluado->estado_formulario;
            $evaluado->cambiarEstadoFormulario($nuevoEstado);
            $estadoNuevoTexto = EvaluadoOrden::estadosFormularioDisponibles()[$evaluado->fresh()->estado_formulario] ?? $nuevoEstado;
            return back()->with('success', "Estado formulario de {$nombre}: '{$estadoAnterior}' → '{$estadoNuevoTexto}'.");
        }

        return back()->with('error', 'Tipo de estado no válido.');
    }

    /**
     * Toggle visibilidad de resultados para empresa
     */
    public function toggleResultadosVisibles(Orden $orden)
    {
        // Solo admin y repro pueden cambiar la visibilidad
        if (Auth::user()->role_as < 2) {
            return back()->with('error', 'No tiene permisos para realizar esta acción.');
        }

        $nuevoEstado = !$orden->resultados_visibles_empresa;
        $orden->update(['resultados_visibles_empresa' => $nuevoEstado]);

        // Enviar email a la empresa cuando se hacen visibles
        if ($nuevoEstado) {
            $this->notificarResultadosDisponibles($orden);
        }

        $mensaje = $nuevoEstado
            ? 'Resultados ahora visibles para la empresa. Se envió notificación por correo.'
            : 'Resultados ocultos para la empresa.';

        return back()->with('success', $mensaje);
    }

    /**
     * Enviar notificación por email cuando resultados están disponibles.
     */
    private function notificarResultadosDisponibles(Orden $orden): void
    {
        try {
            $orden->load(['empresa', 'evaluados']);
            $empresa = $orden->empresa;

            if (!$empresa) {
                return;
            }

            // Obtener emails de usuarios de la empresa
            $emailsEmpresa = \App\Models\User::where('empresa_id', $empresa->id)
                ->where('role_as', 1)
                ->pluck('email')
                ->filter()
                ->unique();

            foreach ($emailsEmpresa as $email) {
                Mail::to($email)->send(new \App\Mail\ResultadosDisponiblesMail($orden));
            }

            // Notificación in-app a usuarios de la empresa
            $usuariosEmpresa = \App\Models\User::where('empresa_id', $empresa->id)
                ->where('role_as', 1)
                ->where('estado', 1)
                ->get();
            foreach ($orden->evaluados as $evaluado) {
                foreach ($usuariosEmpresa as $usuario) {
                    $usuario->notify(new ResultadosDisponiblesNotification($evaluado));
                }
            }

            Log::info('Notificación de resultados enviada', [
                'orden_id' => $orden->id,
                'empresa' => $empresa->nombre,
                'emails' => $emailsEmpresa->toArray(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error enviando notificación de resultados', [
                'orden_id' => $orden->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Procesar evaluados asociados a la orden
     */
    private function procesarEvaluados(Orden $orden, array $evaluados, bool $esActualizacion = false): void
    {
        if ($esActualizacion) {
            $evaluadosExistentes = $orden->evaluados->pluck('id')->toArray();
        }

        foreach ($evaluados as $index => $evaluadoData) {
            $datosEvaluado = [
                'nombre' => $evaluadoData['nombre'],
                'apellidos' => $evaluadoData['apellidos'] ?? null,
                'dpi' => $evaluadoData['dpi'],
                'telefono' => $evaluadoData['telefono'] ?? null,
                'email' => $evaluadoData['email'] ?? null,
                'direccion' => $evaluadoData['direccion'] ?? null,
                'orden_id' => $orden->id,
                'token_unico' => Str::random(32),
                'token_expira_at' => now()->addDays(30),
                // Nuevos campos granulares
                'tipo_servicio' => $evaluadoData['tipo_servicio'],
                // Regla de negocio: socioeconómico siempre usa formulario preempleo
                'tipo_formulario' => $evaluadoData['tipo_servicio'] === 'socioeconomico'
                    ? 'preempleo'
                    : $evaluadoData['tipo_formulario'],
                'fecha_programada' => $evaluadoData['fecha_programada'] ?? null,
                'poligrafista_id' => $evaluadoData['poligrafista_id'] ?? null,
                'observaciones' => $evaluadoData['observaciones'] ?? null,
                'estado_evaluacion' => 'pendiente'
            ];

            if (isset($evaluadoData['id']) && $esActualizacion) {
                $evaluado = EvaluadoOrden::find($evaluadoData['id']);
                if ($evaluado && $evaluado->orden_id === $orden->id) {
                    $evaluado->update($datosEvaluado);
                    $evaluadosExistentes = array_diff($evaluadosExistentes, [$evaluado->id]);
                }
            } else {
                $evaluadoCreado = EvaluadoOrden::create($datosEvaluado);

                // Enviar notificación al evaluado si tiene email
                $this->notificarEvaluadoAsignado($evaluadoCreado);
            }
        }

        if ($esActualizacion && !empty($evaluadosExistentes)) {
            EvaluadoOrden::whereIn('id', $evaluadosExistentes)->delete();
        }
    }

    /**
     * Enviar notificación por email al evaluado cuando es asignado a una orden.
     */
    private function notificarEvaluadoAsignado(EvaluadoOrden $evaluado): void
    {
        try {
            if (empty($evaluado->email)) {
                Log::info('Evaluado sin email, no se envía notificación', [
                    'evaluado_id' => $evaluado->id,
                ]);
                return;
            }

            // Cargar relaciones necesarias
            $evaluado->load('orden.empresa');

            Mail::to($evaluado->email)
                ->queue(new EvaluadoAsignadoMail($evaluado));

            Log::info('Notificación de asignación enviada', [
                'evaluado_id' => $evaluado->id,
                'email' => $evaluado->email,
            ]);

        } catch (\Exception $e) {
            // No fallar el flujo principal si la notificación falla
            Log::error('Error enviando notificación de asignación', [
                'evaluado_id' => $evaluado->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Notificar a usuarios REPRO asignados a la sede de la orden.
     */
    private function notificarUsuariosSede(Orden $orden): void
    {
        try {
            $usuarios = User::where('sede_id', $orden->sede_id)
                ->where('estado', 1)
                ->where('role_as', '>=', 2)
                ->get();

            foreach ($usuarios as $usuario) {
                Mail::to($usuario->email)->queue(new \App\Mail\NuevaOrdenSedeMail($orden));
            }
        } catch (\Exception $e) {
            Log::error('Error notificando usuarios de sede', [
                'orden_id' => $orden->id,
                'sede_id' => $orden->sede_id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Verificar si el usuario puede ver la orden
     */
    private function usuarioPuedeVerOrden(Orden $orden): bool
    {
        // Admin y repro pueden ver cualquier orden
        if (Auth::user()->role_as >= 2) {
            return true;
        }

        // Usuarios empresa solo pueden ver órdenes de su empresa
        if (Auth::user()->role_as == 1) {
            return $orden->empresa_id === Auth::user()->empresa_id;
        }

        return false;
    }

    /**
     * Verificar si el usuario puede editar la orden
     */
    private function usuarioPuedeEditarOrden(Orden $orden): bool
    {
        // Las órdenes entregadas o canceladas no se pueden editar
        if (in_array($orden->estado, ['entregado', 'cancelado'])) {
            return false;
        }

        // Admin y repro pueden editar cualquier orden
        if (Auth::user()->hasAnyRole(['admin', 'repro'])) {
            return true;
        }

        // Las empresas pueden editar sus propias órdenes solo si están en estado inicial
        if (Auth::user()->hasRole('empresa')) {
            return $orden->empresa_id === Auth::user()->empresa_id
                && in_array($orden->estado, ['pendiente', 'programada']);
        }

        return false;
    }

    /**
     * Generar PDF de la orden
     */
    public function pdf(Orden $orden)
    {
        // Verificar permisos
        if (!$this->usuarioPuedeVerOrden($orden)) {
            abort(403, 'No tienes permisos para ver esta orden.');
        }

        // Verificar si es usuario empresa y los resultados no están disponibles
        if (Auth::user()->role_as == 1 && !$orden->resultadosDisponiblesParaEmpresa()) {
            return back()->with('error', 'Los resultados de esta orden aún no están disponibles. Estarán visibles cuando la orden sea entregada.');
        }

        // Cargar relaciones necesarias
        $orden->load(['empresa', 'creador', 'evaluados.poligrafista', 'evaluados.responsable']);

        $estados = Orden::estadosDisponibles();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.ordenes.pdf', compact('orden', 'estados'));

        return $pdf->stream('Orden_' . $orden->codigo_orden . '_' . ($orden->empresa->nombre ?? 'SinEmpresa') . '.pdf');
    }

    /**
     * Reenviar correo de asignación a un evaluado.
     */
    public function reenviarCorreo(EvaluadoOrden $evaluado)
    {
        // Verificar que el usuario tiene permiso para ver esta orden
        if (!$this->usuarioPuedeVerOrden($evaluado->orden)) {
            abort(403, 'No tiene permisos para esta acción.');
        }

        // Verificar que el evaluado tiene email
        if (empty($evaluado->email)) {
            return back()->with('error', 'El evaluado no tiene email registrado.');
        }

        // Verificar que el cuestionario no esté completado
        if ($evaluado->cuestionario_completado) {
            return back()->with('warning', 'El evaluado ya completó su cuestionario.');
        }

        // Verificar que el token no haya expirado
        if ($evaluado->token_expira_at && $evaluado->token_expira_at->isPast()) {
            // Regenerar token si expiró
            $evaluado->update([
                'token_unico' => EvaluadoOrden::generarToken(),
                'token_expira_at' => now()->addDays(30),
            ]);
            $evaluado->refresh();
        }

        try {
            // Cargar relaciones necesarias
            $evaluado->load('orden.empresa');

            Mail::to($evaluado->email)
                ->send(new EvaluadoAsignadoMail($evaluado));

            Log::info('Correo reenviado manualmente', [
                'evaluado_id' => $evaluado->id,
                'email' => $evaluado->email,
                'usuario' => Auth::user()->name,
            ]);

            return back()->with('success', "Correo reenviado exitosamente a {$evaluado->email}");

        } catch (\Exception $e) {
            Log::error('Error reenviando correo', [
                'evaluado_id' => $evaluado->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Error al enviar el correo. Intente nuevamente.');
        }
    }

    /**
     * Subir archivo de resultado (preliminar o final) para un evaluado.
     */
    public function subirResultadoArchivo(Request $request, EvaluadoOrden $evaluado)
    {
        // Solo REPRO y admin
        if (Auth::user()->role_as < 2) {
            abort(403, 'No tiene permisos para esta acción.');
        }

        $request->validate([
            'tipo_resultado' => 'required|in:preliminar,final',
            'archivo' => 'required|file|max:20480|mimes:pdf,doc,docx',
        ], [
            'tipo_resultado.required' => 'Debe indicar el tipo de resultado.',
            'tipo_resultado.in'       => 'Tipo de resultado inválido.',
            'archivo.required'        => 'Debe seleccionar un archivo.',
            'archivo.max'             => 'El archivo no puede superar 20MB.',
            'archivo.mimes'           => 'Solo se permiten archivos PDF, DOC o DOCX.',
        ]);

        $tipo = $request->tipo_resultado;
        $campo = $tipo === 'preliminar' ? 'archivo_resultado_preliminar' : 'archivo_resultado_final';
        $campoFecha = $tipo === 'preliminar' ? 'resultado_preliminar_at' : 'resultado_final_at';

        // Eliminar archivo anterior si existe
        if ($evaluado->$campo) {
            Storage::disk('local')->delete($evaluado->$campo);
        }

        $path = $request->file('archivo')->store(
            "resultados/{$evaluado->id}",
            'local'
        );

        $evaluado->update([
            $campo         => $path,
            $campoFecha    => now(),
            'resultado_subido_por' => Auth::id(),
        ]);

        return back()->with('success', "Archivo de resultado {$tipo} subido correctamente.");
    }

    /**
     * Descargar archivo de resultado de un evaluado.
     */
    public function descargarResultadoArchivo(EvaluadoOrden $evaluado, string $tipo)
    {
        if (!in_array($tipo, ['preliminar', 'final'])) {
            abort(404);
        }

        if (!$this->usuarioPuedeVerOrden($evaluado->orden)) {
            abort(403, 'No tiene permisos para esta acción.');
        }

        // Empresa solo puede ver si resultados están disponibles
        if (Auth::user()->role_as == 1 && !$evaluado->orden->resultadosDisponiblesParaEmpresa()) {
            return back()->with('error', 'Los resultados aún no están disponibles.');
        }

        $campo = $tipo === 'preliminar' ? 'archivo_resultado_preliminar' : 'archivo_resultado_final';

        if (!$evaluado->$campo || !Storage::disk('local')->exists($evaluado->$campo)) {
            return back()->with('error', "No hay archivo de resultado {$tipo}.");
        }

        $extension = pathinfo($evaluado->$campo, PATHINFO_EXTENSION);
        $nombreDescarga = "resultado_{$tipo}_{$evaluado->nombre}_{$evaluado->apellidos}.{$extension}";

        return Storage::disk('local')->download($evaluado->$campo, $nombreDescarga);
    }

    /**
     * Eliminar archivo de resultado de un evaluado.
     */
    public function eliminarResultadoArchivo(EvaluadoOrden $evaluado, string $tipo)
    {
        // Solo REPRO y admin
        if (Auth::user()->role_as < 2) {
            abort(403, 'No tiene permisos para esta acción.');
        }

        if (!in_array($tipo, ['preliminar', 'final'])) {
            abort(404);
        }

        $campo = $tipo === 'preliminar' ? 'archivo_resultado_preliminar' : 'archivo_resultado_final';
        $campoFecha = $tipo === 'preliminar' ? 'resultado_preliminar_at' : 'resultado_final_at';

        if ($evaluado->$campo) {
            Storage::disk('local')->delete($evaluado->$campo);
        }

        $evaluado->update([
            $campo      => null,
            $campoFecha => null,
        ]);

        return back()->with('success', "Archivo de resultado {$tipo} eliminado correctamente.");
    }

    /**
     * Rehabilitar cuestionario de un evaluado (permite que vuelva a llenarlo).
     * Solo REPRO/admin pueden hacer esto.
     */
    public function rehabilitarCuestionario(EvaluadoOrden $evaluado)
    {
        // Solo REPRO y admin
        if (Auth::user()->role_as < 2) {
            abort(403, 'No tiene permisos para esta acción.');
        }

        if (!$evaluado->cuestionario_completado) {
            return back()->with('warning', 'El cuestionario de este evaluado no está completado.');
        }

        DB::beginTransaction();
        try {
            $cuestionario = $evaluado->cuestionario;

            if ($cuestionario) {
                $cuestionario->update([
                    'completado'          => false,
                    'bloqueado'           => false,
                    'progreso_porcentaje' => 0,
                    'completado_at'       => null,
                    'ip_completado'       => null,
                    'firma_digital'       => null,
                    'acepta_terminos'     => false,
                    'acepta_terminos_at'  => null,
                    'firma_autorizacion'  => null,
                    'ip_terminos'         => null,
                ]);
            }

            // Regenerar token y resetear estado del evaluado
            $evaluado->update([
                'cuestionario_completado'    => false,
                'cuestionario_completado_at' => null,
                'completado_at'              => null,
                'estado_evaluacion'          => 'pendiente',
                'token_unico'                => EvaluadoOrden::generarToken(),
                'token_expira_at'            => now()->addDays(30),
            ]);

            DB::commit();

            Log::info('Cuestionario rehabilitado', [
                'evaluado_id' => $evaluado->id,
                'evaluado'    => $evaluado->nombre . ' ' . $evaluado->apellidos,
                'usuario'     => Auth::user()->name,
            ]);

            return back()->with('success', "Cuestionario rehabilitado para {$evaluado->nombre} {$evaluado->apellidos}. Se generó un nuevo enlace de acceso.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error rehabilitando cuestionario', [
                'evaluado_id' => $evaluado->id,
                'error'       => $e->getMessage(),
            ]);

            return back()->with('error', 'Error al rehabilitar el cuestionario. Intente nuevamente.');
        }
    }

    /**
     * Deshabilitar cuestionario de un evaluado (revierte una rehabilitación).
     * Bloquea el cuestionario y marca al evaluado como completado nuevamente.
     * Solo REPRO/admin pueden hacer esto.
     */
    public function deshabilitarCuestionario(EvaluadoOrden $evaluado): \Illuminate\Http\RedirectResponse
    {
        if (Auth::user()->role_as < 2) {
            abort(403, 'No tiene permisos para esta acción.');
        }

        if ($evaluado->cuestionario_completado) {
            return back()->with('warning', 'El cuestionario de este evaluado ya está completado/bloqueado.');
        }

        $cuestionario = $evaluado->cuestionario;
        if (!$cuestionario) {
            return back()->with('warning', 'Este evaluado no tiene un cuestionario asociado.');
        }

        DB::beginTransaction();
        try {
            $cuestionario->update([
                'completado'          => true,
                'bloqueado'           => true,
                'completado_at'       => now(),
            ]);

            $evaluado->update([
                'cuestionario_completado'    => true,
                'cuestionario_completado_at' => now(),
                'completado_at'              => now(),
                'estado_evaluacion'          => 'completado',
                'token_expira_at'            => now(), // Expirar token inmediatamente
            ]);

            DB::commit();

            Log::info('Cuestionario deshabilitado', [
                'evaluado_id' => $evaluado->id,
                'evaluado'    => $evaluado->nombre . ' ' . $evaluado->apellidos,
                'usuario'     => Auth::user()->name,
            ]);

            return back()->with('success', "Cuestionario deshabilitado para {$evaluado->nombre} {$evaluado->apellidos}. El enlace fue invalidado.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deshabilitando cuestionario', [
                'evaluado_id' => $evaluado->id,
                'error'       => $e->getMessage(),
            ]);

            return back()->with('error', 'Error al deshabilitar el cuestionario. Intente nuevamente.');
        }
    }
}
