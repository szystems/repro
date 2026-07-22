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
use App\Notifications\EvaluadoAsignadoNotification;
use App\Notifications\OrdenCreadaNotification;
use App\Notifications\ResultadoPreliminarNotification;
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

        if ($request->boolean('archivadas') && Auth::user()->role_as >= 3) {
            $query->archivadas();
        } else {
            $query->activas();
        }

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
        $totalOrdenes = Orden::activas()->count();
        $ordenesActivas = Orden::activas()->where('estado', 'en_proceso')->count();
        $ordenesCompletadas = Orden::activas()->where('estado', 'entregado')->count();
        $ordenesPendientes = Orden::activas()->where('estado', 'orden_recibida')->count();

        $totalEvaluados = EvaluadoOrden::count();
        $evaluadosCompletados = EvaluadoOrden::where('estado_evaluacion', 'informe_final_enviado')->count();

        $porEmpresa = Orden::activas()->select('empresa_id', DB::raw('COUNT(*) as total'))
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
            'sede_id' => 'nullable|exists:sedes,id,estado,1',
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
            'evaluados.*.puesto_evaluar' => 'nullable|string|max:100',
            'evaluados.*.motivo_hecho_evaluacion' => 'nullable|string|max:2000',
            'evaluados.*.sede_id' => 'nullable|exists:sedes,id',
            'evaluados.*.sede_region_empresa' => 'nullable|string|max:100',
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

            // H-08: validar emails duplicados entre personas distintas (DPIs distintos) en la misma orden
            $emailToDpis = collect($validated['evaluados'])
                ->filter(fn($e) => !empty($e['email']) && !empty($e['dpi']))
                ->groupBy(fn($e) => strtolower(trim($e['email'])))
                ->map(fn($grupo) => collect($grupo)->pluck('dpi')->unique()->count());
            if ($emailToDpis->filter(fn($n) => $n > 1)->isNotEmpty()) {
                return back()->withErrors(['evaluados' => 'No se puede usar el mismo email para evaluados diferentes (DPIs distintos) en la misma orden.'])->withInput();
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
                'estado' => 'orden_recibida',
                // Sede de REPRO que trabajará la orden — disponible para REPRO y cliente.
                'sede_id' => $validated['sede_id'] ?? null,
            ];

            // Campos exclusivos REPRO (role_as >= 2)
            if (Auth::user()->role_as >= 2) {
                $datosOrden['observaciones_internas'] = $validated['observaciones_internas'] ?? null;
                $datosOrden['prioridad'] = $validated['prioridad'] ?? 'normal';
                $datosOrden['fecha_limite'] = $validated['fecha_limite'] ?? null;
                $datosOrden['requerimientos_generales'] = $validated['requerimientos_generales'] ?? null;
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

            // Recargar relaciones necesarias para notificaciones
            $orden->load(['empresa', 'evaluados']);

            // Notificar a usuarios de la sede asignada
            if ($orden->sede_id) {
                $this->notificarUsuariosSede($orden);
            }

            // Notificación in-app a todos los usuarios REPRO/admin (incluye al creador como confirmación)
            $usuariosNotificar = User::where('role_as', '>=', 2)
                ->where('estado', 1)
                ->get();
            foreach ($usuariosNotificar as $usuario) {
                $usuario->notify(new OrdenCreadaNotification($orden));
            }

            // Notificación in-app a usuarios de la empresa (incluye al creador si es usuario empresa)
            if ($orden->empresa_id) {
                $usuariosEmpresa = User::where('empresa_id', $orden->empresa_id)
                    ->where('role_as', 1)
                    ->where('estado', 1)
                    ->get();
                foreach ($usuariosEmpresa as $usuario) {
                    $usuario->notify(new OrdenCreadaNotification($orden));
                }
            }

            // Redirigir según el rol del usuario
            if (Auth::user()->role_as == 1) {
                // Usuario empresa: redirigir a módulo empresa
                return redirect()->route('empresa.ordenes.show', $orden)
                    ->with('success', 'Orden creada exitosamente.')
                    ->with('mostrar_papeleria', true);
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
            'archivadaPor',
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

        // Usuario empresa no envía empresa_id en el formulario de edición;
        // se fuerza desde su sesión para validar y mantener consistencia.
        if (Auth::user()->role_as == 1) {
            $request->merge(['empresa_id' => Auth::user()->empresa_id]);
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
            'evaluados.*.puesto_evaluar' => 'nullable|string|max:100',
            'evaluados.*.motivo_hecho_evaluacion' => 'nullable|string|max:2000',
            'evaluados.*.sede_id' => 'nullable|exists:sedes,id',
            'evaluados.*.sede_region_empresa' => 'nullable|string|max:100',
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

            $evaluadosPayload = collect($request->input('evaluados', []))
                ->map(function ($item, $index) {
                    return [
                        'index' => $index,
                        'id' => $item['id'] ?? null,
                        'dpi' => $item['dpi'] ?? null,
                        'tipo_servicio' => $item['tipo_servicio'] ?? null,
                        'nombre' => $item['nombre'] ?? null,
                        'email' => $item['email'] ?? null,
                    ];
                })
                ->values()
                ->all();

            Log::error('Error al actualizar orden:', [
                'error' => $e->getMessage(),
                'orden_id' => $orden->id,
                'user_id' => Auth::id(),
                'evaluados_payload' => $evaluadosPayload,
            ]);

            return back()->with('error', 'Error al actualizar la orden: ' . $e->getMessage())
                        ->withInput();
        }
    }

    /**
     * Archivar orden (solo admin — conserva expediente)
     */
    public function archivar(Orden $orden)
    {
        if (Auth::user()->role_as < 3) {
            abort(403, 'Solo el administrador puede archivar órdenes.');
        }

        if ($orden->archivada) {
            return back()->with('warning', "La orden {$orden->codigo_orden} ya está archivada.");
        }

        $orden->update([
            'archivada' => true,
            'archivada_at' => now(),
            'archivada_por' => Auth::id(),
        ]);

        Log::info('Orden archivada', [
            'orden_id' => $orden->id,
            'codigo_orden' => $orden->codigo_orden,
            'archivada_por' => Auth::id(),
        ]);

        return redirect()->route('ordenes.index')
            ->with('success', "Orden {$orden->codigo_orden} archivada correctamente.");
    }

    /**
     * Eliminar orden (deshabilitado — usar archivar)
     */
    public function destroy(Orden $orden)
    {
        abort(403, 'Las órdenes no se eliminan. Use la opción de archivar (solo administrador).');
    }

    /**
     * Cambiar estado de la orden
     */
    public function cambiarEstado(Request $request, Orden $orden)
    {
        $request->validate([
            'nuevo_estado' => 'required|in:orden_recibida,en_proceso,entregado,cancelado',
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

        $request->validate([
            'tipo_estado'  => 'required|in:evaluacion,formulario,programacion',
            'nuevo_estado' => 'required|string',
            'observacion'  => 'nullable|string|max:1000',
        ]);

        $tipo        = $request->tipo_estado;
        $nuevoEstado = $request->nuevo_estado;
        $observacion = $request->filled('observacion') ? trim($request->observacion) : null;
        $nombre      = "{$evaluado->nombre} {$evaluado->apellidos}";

        if ($tipo === 'evaluacion') {
            if (!$evaluado->puedeTransicionarEstadoEvaluacion($nuevoEstado)) {
                $estadoActual = $evaluado->estado_evaluacion_texto;
                return back()->with('error', "No se puede cambiar evaluación de '{$estadoActual}' a '{$nuevoEstado}' para {$nombre}.");
            }
            $estadoAnterior = $evaluado->estado_evaluacion_texto;
            try {
                $evaluado->cambiarEstadoEvaluacion($nuevoEstado, $observacion);
            } catch (\Illuminate\Validation\ValidationException $e) {
                $mensajes = collect($e->errors())->flatten()->implode(' ');
                return back()->with('error', "No se puede iniciar la evaluación para {$nombre}: {$mensajes}");
            }
            $estadoNuevoTexto = $evaluado->fresh()->estado_evaluacion_texto;
            return back()->with('success', "Estado evaluación de {$nombre}: '{$estadoAnterior}' → '{$estadoNuevoTexto}'.");
        }

        if ($tipo === 'formulario') {
            if (!$evaluado->puedeTransicionarEstadoFormulario($nuevoEstado)) {
                $estadoActual = EvaluadoOrden::estadosFormularioDisponibles()[$evaluado->estado_formulario] ?? $evaluado->estado_formulario;
                return back()->with('error', "No se puede cambiar formulario de '{$estadoActual}' a '{$nuevoEstado}' para {$nombre}.");
            }
            $estadoAnterior = EvaluadoOrden::estadosFormularioDisponibles()[$evaluado->estado_formulario] ?? $evaluado->estado_formulario;
            $evaluado->cambiarEstadoFormulario($nuevoEstado, $observacion);
            $estadoNuevoTexto = EvaluadoOrden::estadosFormularioDisponibles()[$evaluado->fresh()->estado_formulario] ?? $nuevoEstado;
            return back()->with('success', "Estado formulario de {$nombre}: '{$estadoAnterior}' → '{$estadoNuevoTexto}'.");
        }

        if ($tipo === 'programacion') {
            if (!$evaluado->puedeTransicionarEstadoProgramacion($nuevoEstado)) {
                $estadoActual = EvaluadoOrden::estadosProgramacionDisponibles()[$evaluado->estado_programacion] ?? $evaluado->estado_programacion;
                return back()->with('error', "No se puede cambiar programación de '{$estadoActual}' a '{$nuevoEstado}' para {$nombre}.");
            }
            $estadoAnterior = EvaluadoOrden::estadosProgramacionDisponibles()[$evaluado->estado_programacion] ?? $evaluado->estado_programacion;
            $evaluado->cambiarEstadoProgramacion($nuevoEstado, $observacion);
            $estadoNuevoTexto = EvaluadoOrden::estadosProgramacionDisponibles()[$evaluado->fresh()->estado_programacion] ?? $nuevoEstado;
            return back()->with('success', "Estado programación de {$nombre}: '{$estadoAnterior}' → '{$estadoNuevoTexto}'.");
        }

        return back()->with('error', 'Tipo de estado no válido.');
    }

    /**
     * Actualizar observación del evaluado (visible para empresa).
     */
    public function actualizarObservacion(Request $request, EvaluadoOrden $evaluado): \Illuminate\Http\RedirectResponse
    {
        if (Auth::user()->role_as < 2) {
            return back()->with('error', 'No tiene permisos para realizar esta acción.');
        }

        $request->validate([
            'observaciones' => 'nullable|string|max:2000',
        ]);

        $evaluado->update(['observaciones' => $request->observaciones]);

        return back()->with('success', "Observación de {$evaluado->nombre} {$evaluado->apellidos} actualizada.");
    }

    /**
     * Guardar el texto del informe preliminar (editor enriquecido).
     */
    public function guardarInformePreliminar(Request $request, EvaluadoOrden $evaluado): \Illuminate\Http\RedirectResponse
    {
        if (Auth::user()->role_as < 2) {
            return back()->with('error', 'No tiene permisos para realizar esta acción.');
        }

        $request->validate([
            'texto_informe_preliminar' => 'nullable|string',
        ]);

        $textoLimpio = $this->sanitizarHtmlInforme($request->texto_informe_preliminar);

        $evaluado->update(['texto_informe_preliminar' => $textoLimpio]);

        // Auto-liberar resultados para el cliente al guardar el informe preliminar
        $orden = $evaluado->orden;
        if (!$orden->resultados_visibles_empresa) {
            $orden->update(['resultados_visibles_empresa' => true]);
            $this->notificarResultadosDisponibles($orden);
        }

        // Notificar que hay un resultado preliminar disponible
        $this->notificarPreliminarSubido($evaluado);

        return back()->with('success', "Informe preliminar de {$evaluado->nombre} {$evaluado->apellidos} guardado y liberado al cliente.");
    }

    /**
     * Toggle visibilidad de resultados para empresa
     */
    public function toggleResultadosVisibles(Orden $orden)
    {
        // Solo administradores de REPRO pueden bloquear/desbloquear manualmente la visibilidad
        if (Auth::user()->role_as < 3) {
            return back()->with('error', 'Solo los administradores pueden bloquear o desbloquear manualmente la visibilidad de resultados.');
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

            // Correo a usuarios de la empresa
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

            // Notificación in-app a admins y colaboradores REPRO (Fase 18 — Prioridad 3)
            $usuariosRepro = \App\Models\User::where('role_as', '>=', 2)
                ->where('estado', 1)
                ->get();

            foreach ($orden->evaluados as $evaluado) {
                foreach ($usuariosEmpresa as $usuario) {
                    $usuario->notify(new ResultadosDisponiblesNotification($evaluado));
                }
                foreach ($usuariosRepro as $usuario) {
                    $usuario->notify(new ResultadosDisponiblesNotification($evaluado));
                }
            }

            Log::info('Notificación de resultados enviada', [
                'orden_id'      => $orden->id,
                'empresa'       => $empresa->nombre,
                'emails'        => $emailsEmpresa->toArray(),
                'repro_notif'   => $usuariosRepro->count(),
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
        $evaluadosExistentes = [];
        $evaluadosPorIndice = collect();

        if ($esActualizacion) {
            $evaluadosExistentes = $orden->evaluados->pluck('id')->toArray();
            $evaluadosPorIndice = $orden->evaluados()->orderBy('id')->get()->values();
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
                'token_expira_at' => EvaluadoOrden::calcularExpiracionToken(),
                // Nuevos campos granulares
                'tipo_servicio' => $evaluadoData['tipo_servicio'],
                // E6.1 — Matriz: socio siempre preempleo en BD; cuestionario vía tipoFormularioCuestionario()
                'tipo_formulario' => \App\Support\MatrizFormularioServicio::tipoFormularioParaOrden(
                    $evaluadoData['tipo_servicio'],
                    $evaluadoData['tipo_formulario'] ?? null
                ),
                'puesto_evaluar' => $evaluadoData['puesto_evaluar'] ?? null,
                'motivo_hecho_evaluacion' => $evaluadoData['motivo_hecho_evaluacion'] ?? null,
                'sede_id' => $evaluadoData['sede_id'] ?? null,
                'sede_region_empresa' => $evaluadoData['sede_region_empresa'] ?? null,
                'fecha_programada' => $evaluadoData['fecha_programada'] ?? null,
                'poligrafista_id' => $evaluadoData['poligrafista_id'] ?? null,
                'observaciones' => $evaluadoData['observaciones'] ?? null,
                'modalidad'           => $evaluadoData['modalidad'] ?? 'presencial',
                'estado_evaluacion'   => 'pendiente_de_evaluacion',
                'estado_formulario'   => 'link_pendiente',
                'estado_programacion' => 'contactando',
            ];

            if ($esActualizacion) {
                $evaluado = null;

                if (!empty($evaluadoData['id'])) {
                    $evaluado = EvaluadoOrden::find($evaluadoData['id']);
                    if ($evaluado && $evaluado->orden_id !== $orden->id) {
                        $evaluado = null;
                    }
                }

                // Fallback por posición cuando el id se pierde en el frontend (p. ej. cambio de DPI)
                if (!$evaluado && $evaluadosPorIndice->has($index)) {
                    $candidatoIndice = $evaluadosPorIndice[$index];
                    if (in_array($candidatoIndice->id, $evaluadosExistentes, true)) {
                        $evaluado = $candidatoIndice;
                    }
                }

                if ($evaluado) {
                    $this->actualizarEvaluadoEnOrden($evaluado, $datosEvaluado, $evaluadosExistentes);
                } else {
                    $duplicado = EvaluadoOrden::where('orden_id', $orden->id)
                        ->where('dpi', $evaluadoData['dpi'])
                        ->where('tipo_servicio', $evaluadoData['tipo_servicio'])
                        ->first();

                    // Si ya existe por clave única (orden+dpi+servicio), tratarlo como actualización
                    // aunque el id venga vacío, inválido o desalineado desde el frontend.
                    if ($duplicado) {
                        $this->actualizarEvaluadoEnOrden($duplicado, $datosEvaluado, $evaluadosExistentes);
                        continue;
                    }

                    $evaluadoCreado = EvaluadoOrden::create($datosEvaluado);

                    // Enviar notificación al evaluado si tiene email
                    $this->notificarEvaluadoAsignado($evaluadoCreado);

                    // Notificaciones in-app al asignar un nuevo evaluado
                    $this->notificarEvaluadoAsignadoInApp($evaluadoCreado, $esActualizacion);

                    // Fase 18: si tiene email, el link del formulario fue enviado (solo formulario)
                    // estado_evaluacion permanece en 'pendiente_de_evaluacion' — es independiente
                    if (!empty($evaluadoCreado->email)) {
                        $evaluadoCreado->cambiarEstadoFormulario('link_enviado');
                    }
                }
            } else {
                $evaluadoCreado = EvaluadoOrden::create($datosEvaluado);

                // Enviar notificación al evaluado si tiene email
                $this->notificarEvaluadoAsignado($evaluadoCreado);

                // Notificaciones in-app al asignar un nuevo evaluado
                $this->notificarEvaluadoAsignadoInApp($evaluadoCreado, $esActualizacion);

                // Fase 18: si tiene email, el link del formulario fue enviado (solo formulario)
                if (!empty($evaluadoCreado->email)) {
                    $evaluadoCreado->cambiarEstadoFormulario('link_enviado');
                }
            }
        }

        if ($esActualizacion && !empty($evaluadosExistentes)) {
            Log::warning('Evaluados no enviados durante actualización de orden; se preservan para evitar pérdida de datos.', [
                'orden_id' => $orden->id,
                'evaluados_omitidos' => array_values($evaluadosExistentes),
                'user_id' => Auth::id(),
            ]);
        }
    }

    /**
     * Actualiza un evaluado existente preservando estados y datos gestionados por otros flujos.
     */
    private function actualizarEvaluadoEnOrden(EvaluadoOrden $evaluado, array $datosEvaluado, array &$evaluadosExistentes): void
    {
        $modalidadAnterior = $evaluado->modalidad;
        $datosActualizacion = array_merge($datosEvaluado, [
            'estado_evaluacion'          => $evaluado->estado_evaluacion,
            'estado_formulario'          => $evaluado->estado_formulario,
            'estado_programacion'        => $evaluado->estado_programacion,
            'cuestionario_completado'    => $evaluado->cuestionario_completado,
            'cuestionario_completado_at' => $evaluado->cuestionario_completado_at,
            'fecha_programada'           => $evaluado->fecha_programada,
            'fecha_hora_fin'             => $evaluado->fecha_hora_fin,
            'token_unico'                => $evaluado->token_unico,
            'token_expira_at'            => $evaluado->token_expira_at,
        ]);
        $evaluado->update($datosActualizacion);

        $evaluado->fresh()->sincronizarCuestionarioConServicio();

        $nuevaModalidad = $datosActualizacion['modalidad'] ?? $evaluado->modalidad;
        if ($modalidadAnterior !== $nuevaModalidad) {
            \App\Models\EstadoHistorial::create([
                'evaluado_orden_id' => $evaluado->id,
                'campo'             => 'modalidad',
                'estado_anterior'   => $modalidadAnterior,
                'estado_nuevo'      => $nuevaModalidad,
                'user_id'           => auth()->id(),
            ]);
        }

        $evaluadosExistentes = array_values(array_diff($evaluadosExistentes, [$evaluado->id]));
    }

    /**
     * Notificaciones in-app cuando se asigna un nuevo evaluado.
     * Durante creación: solo empresa. Durante actualización: admin/repro + empresa.
     */
    private function notificarEvaluadoAsignadoInApp(EvaluadoOrden $evaluado, bool $esActualizacion): void
    {
        try {
            $evaluado->loadMissing('orden.empresa');
            $orden = $evaluado->orden;

            // Durante actualización de orden: notificar admin/repro
            if ($esActualizacion) {
                $usuariosRepro = User::where('role_as', '>=', 2)
                    ->where('estado', 1)
                    ->where('id', '!=', Auth::id())
                    ->get();
                foreach ($usuariosRepro as $usuario) {
                    $usuario->notify(new EvaluadoAsignadoNotification($evaluado));
                }
            }

            // Siempre: notificar a usuarios de la empresa
            if ($orden->empresa_id) {
                $usuariosEmpresa = User::where('empresa_id', $orden->empresa_id)
                    ->where('role_as', 1)
                    ->where('estado', 1)
                    ->get();
                foreach ($usuariosEmpresa as $usuario) {
                    $usuario->notify(new EvaluadoAsignadoNotification($evaluado));
                }
            }
        } catch (\Exception $e) {
            Log::error('Error enviando notificación in-app de evaluado asignado', [
                'evaluado_id' => $evaluado->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Notificar a admin y empresa cuando se sube un resultado preliminar.
     */
    private function notificarPreliminarSubido(EvaluadoOrden $evaluado): void
    {
        try {
            $evaluado->loadMissing('orden.empresa');
            $orden = $evaluado->orden;

            // Notificar a admins y colaboradores (role_as >= 2), excepto quien subió el archivo
            $admins = User::where('role_as', '>=', 2)
                ->where('estado', 1)
                ->where('id', '!=', Auth::id())
                ->get();
            foreach ($admins as $admin) {
                $admin->notify(new ResultadoPreliminarNotification($evaluado));
            }

            // Notificar a usuarios de la empresa
            if ($orden->empresa_id) {
                $usuariosEmpresa = User::where('empresa_id', $orden->empresa_id)
                    ->where('role_as', 1)
                    ->where('estado', 1)
                    ->get();
                foreach ($usuariosEmpresa as $usuario) {
                    $usuario->notify(new ResultadoPreliminarNotification($evaluado));
                }
            }
        } catch (\Exception $e) {
            Log::error('Error enviando notificación de preliminar subido', [
                'evaluado_id' => $evaluado->id,
                'error' => $e->getMessage(),
            ]);
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
        if (Auth::user()->role_as == 1) {
            return (int) $orden->empresa_id === (int) Auth::user()->empresa_id
                && $orden->estado === 'orden_recibida';
        }

        return false;
    }

    /**
     * Generar PDF de la orden
     *
     * El PDF contiene solo datos administrativos de la orden (código, estado, evaluados,
     * fechas). NO incluye resultados de cuestionarios. Por eso está disponible
     * para cliente desde que se crea la orden, sin esperar a que sea entregada.
     */
    public function pdf(Orden $orden)
    {
        // Verificar permisos
        if (!$this->usuarioPuedeVerOrden($orden)) {
            abort(403, 'No tienes permisos para ver esta orden.');
        }

        // Cargar relaciones necesarias
        $orden->load(['empresa', 'creador', 'evaluados.poligrafista', 'evaluados.responsable', 'evaluados.sede']);

        $estados = Orden::estadosDisponibles();
        $config = \App\Models\Config::first();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.ordenes.pdf', compact('orden', 'estados', 'config'));

        return $pdf->stream('OrdenServicio_' . $orden->codigo_orden . '_' . ($orden->empresa->nombre ?? 'SinEmpresa') . '.pdf');
    }

    /**
     * PDF Informe del Candidato — muestra los resultados de evaluación por evaluado.
     */
    public function pdfInforme(Orden $orden)
    {
        if (!$this->usuarioPuedeVerOrden($orden)) {
            abort(403, 'No tienes permisos para ver esta orden.');
        }

        $orden->load(['empresa', 'sede', 'evaluados.poligrafista', 'evaluados.responsable', 'evaluados.sede']);

        $esEmpresa = Auth::user()->role_as < 2;
        $mostrarInformePreliminar = !$esEmpresa || $orden->resultados_visibles_empresa;
        $config = \App\Models\Config::first();
        $estados = Orden::estadosDisponibles();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.ordenes.pdf-informe', compact('orden', 'mostrarInformePreliminar', 'config', 'estados'));

        return $pdf->stream('Informe_' . $orden->codigo_orden . '_' . ($orden->empresa->nombre ?? 'SinEmpresa') . '.pdf');
    }

    /**
     * E7 — Informe editable en Word (.docx) por evaluado.
     */
    public function informeWord(Orden $orden, EvaluadoOrden $evaluado)
    {
        if ($evaluado->orden_id !== $orden->id) {
            abort(404);
        }

        if (!$this->usuarioPuedeVerOrden($orden)) {
            abort(403, 'No tienes permisos para ver esta orden.');
        }

        if (Auth::user()->role_as < 2) {
            abort(403, 'Solo usuarios REPRO pueden descargar el informe Word.');
        }

        $evaluado->load(['poligrafista', 'responsable', 'sede', 'orden.empresa', 'orden.sede']);

        $path = \App\Support\InformeWordExport::generar($orden, $evaluado);
        $filename = 'Informe_' . $evaluado->dpi . '_' . ($orden->codigo_orden ?? 'orden') . '.docx';

        return response()->download($path, $filename)->deleteFileAfterSend(true);
    }

    /**
     * Fase A.3 — Motivo/hecho de evaluación (Periódica / Específica).
     */
    public function actualizarMotivoHecho(Request $request, EvaluadoOrden $evaluado)
    {
        if (!$this->usuarioPuedeVerOrden($evaluado->orden)) {
            abort(403, 'No tiene permisos para esta acción.');
        }

        if (Auth::user()->role_as < 2) {
            abort(403, 'Solo usuarios REPRO pueden editar este campo.');
        }

        $request->validate([
            'motivo_hecho_evaluacion' => 'required|string|max:2000',
        ], [
            'motivo_hecho_evaluacion.required' => 'Indique el motivo o hecho de la evaluación.',
        ]);

        $evaluado->update([
            'motivo_hecho_evaluacion' => $request->input('motivo_hecho_evaluacion'),
        ]);

        return back()->with('success', 'Motivo/hecho de evaluación actualizado.');
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
                'token_expira_at' => EvaluadoOrden::calcularExpiracionToken(),
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

            // Fase 18: reenviar link solo afecta estado_formulario, nunca estado_evaluacion
            if ($evaluado->estado_formulario === 'link_pendiente') {
                $evaluado->cambiarEstadoFormulario('link_enviado');
            }

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

        // CO3: bloquear subida de informe final si la orden ya fue entregada
        // Solo admins pueden reemplazarlo
        if ($tipo === 'final' && $evaluado->orden->estado === 'entregado' && Auth::user()->role_as < 3) {
            return back()->with('error', 'No se puede reemplazar el informe final de una orden ya entregada. Solo los administradores pueden hacerlo.');
        }

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

        // Auto-liberar resultados al cliente según tipo de archivo subido
        $orden = $evaluado->orden;
        if ($tipo === 'final') {
            // Fase 18: subir informe final = cierre del proceso → forzar 'informe_final_enviado'
            $estadoAnteriorEv = $evaluado->estado_evaluacion;
            if ($estadoAnteriorEv !== 'informe_final_enviado') {
                $evaluado->update(['estado_evaluacion' => 'informe_final_enviado']);
                \App\Models\EstadoHistorial::create([
                    'evaluado_orden_id' => $evaluado->id,
                    'campo'             => 'estado_evaluacion',
                    'estado_anterior'   => $estadoAnteriorEv,
                    'estado_nuevo'      => 'informe_final_enviado',
                    'observacion'       => 'Informe final subido',
                    'user_id'           => Auth::id(),
                ]);
            }

            $orden->update(['resultados_visibles_empresa' => true]);
            $orden->unsetRelation('evaluados');
            $orden->recalcularEstado();
            $orden->refresh();
            $this->notificarResultadosDisponibles($orden);

            return back()->with('success', 'Archivo de resultado final subido. Los resultados han sido liberados automáticamente al cliente.');
        }

        // Subir preliminar → liberar resultados al cliente (Fase 18: sin auto-cambio de estado)
        if ($tipo === 'preliminar') {
            // Fase 18: la orden no tiene estado 'preliminar'; se mantiene 'en_proceso'
            // hasta que recalcularEstado() la marque como 'entregado' cuando corresponda.
            if (!$orden->resultados_visibles_empresa) {
                $orden->update(['resultados_visibles_empresa' => true]);
                $this->notificarResultadosDisponibles($orden);
            }
            // Fase 18 (respuesta cliente #2): 'en_proceso' es 100% MANUAL, no automático al subir preliminar.
            // Notificar a admin y empresa que hay un resultado preliminar
            $this->notificarPreliminarSubido($evaluado);
        }

        return back()->with('success', "Archivo de resultado {$tipo} subido correctamente. Los resultados han sido liberados al cliente.");
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

        // CO3: no se puede eliminar el informe final de una orden ya entregada
        // Solo admins pueden hacerlo
        if ($tipo === 'final' && $evaluado->orden->estado === 'entregado' && Auth::user()->role_as < 3) {
            return back()->with('error', 'No se puede eliminar el informe final de una orden ya entregada. Solo los administradores pueden hacerlo.');
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
                    'completado'              => false,
                    'bloqueado'               => false,
                    'progreso_porcentaje'     => 0,
                    'completado_at'           => null,
                    'ip_completado'           => null,
                    'firma_digital'           => null,
                    'instrucciones_leidas_at' => null,
                    'ip_instrucciones'        => null,
                    'datos_precarga_json'     => null,
                    'acepta_terminos'         => false,
                    'acepta_terminos_at'      => null,
                    'firma_autorizacion'      => null,
                    'ip_terminos'             => null,
                ]);
            }

            // Regenerar token y resetear estado del evaluado
            $evaluado->update([
                'cuestionario_completado'    => false,
                'cuestionario_completado_at' => null,
                'completado_at'              => null,
                'estado_formulario'          => 'link_pendiente',
                'token_unico'                => EvaluadoOrden::generarToken(),
                'token_expira_at'            => EvaluadoOrden::calcularExpiracionToken(),
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
                'estado_formulario'          => 'formulario_completado_y_recibido',
                'token_expira_at'            => now(),
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

    /**
     * Sanitiza HTML proveniente del editor enriquecido (Quill) permitiendo
     * solo etiquetas y atributos seguros para evitar XSS almacenado.
     */
    private function sanitizarHtmlInforme(?string $html): ?string
    {
        if ($html === null || trim($html) === '') {
            return $html;
        }

        $tagsPermitidos = '<p><br><b><strong><i><em><u><s><strike><ul><ol><li><h1><h2><h3><h4><h5><h6><blockquote><pre><code><span><div><a>';
        $limpio = strip_tags($html, $tagsPermitidos);

        // Eliminar atributos peligrosos (on*, javascript:, style con expresiones)
        $limpio = preg_replace('/\s+on[a-z]+\s*=\s*"[^"]*"/i', '', $limpio) ?? $limpio;
        $limpio = preg_replace("/\s+on[a-z]+\s*=\s*'[^']*'/i", '', $limpio) ?? $limpio;
        $limpio = preg_replace('/\s+(href|src)\s*=\s*"\s*javascript:[^"]*"/i', '', $limpio) ?? $limpio;
        $limpio = preg_replace("/\s+(href|src)\s*=\s*'\s*javascript:[^']*'/i", '', $limpio) ?? $limpio;

        return $limpio;
    }
}
