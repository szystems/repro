<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrdenFormRequest;
use App\Mail\EvaluadoAsignadoMail;
use App\Models\Orden;
use App\Models\EvaluadoOrden;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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

        $ordenes = $query->with(['empresa', 'creador', 'evaluados'])
                        ->withCount('evaluados')
                        ->orderBy('fecha_solicitud', 'desc')
                        ->paginate(15);

        // Datos para filtros
        $empresas = Auth::user()->role_as >= 2
            ? Empresa::orderBy('nombre')->get()
            : collect();

        $estados = [
            'solicitud' => 'Solicitud',
            'programacion' => 'Programación',
            'en_proceso' => 'En Proceso',
            'analisis' => 'En Análisis',
            'entregado' => 'Entregado',
            'cancelado' => 'Cancelado'
        ];

        $tiposServicio = [
            'poligrafo' => 'Polígrafo',
            'vsa' => 'VSA (Voice Stress Analysis)',
            'socioeconomico' => 'Estudio Socioeconómico'
        ];

        return view('admin.ordenes.index', compact('ordenes', 'empresas', 'estados', 'tiposServicio'));
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

        return view('admin.ordenes.create', compact('empresas', 'poligrafistas'));
    }

    /**
     * Almacenar nueva orden
     */
    public function store(Request $request)
    {
        // Debug: Log de entrada
        Log::info('=== INICIO STORE ORDEN (Sin FormRequest) ===');
        Log::info('Request data: ' . json_encode($request->all()));
        Log::info('User ID: ' . Auth::id());
        Log::info('Method: ' . $request->method());
        Log::info('URL: ' . $request->url());

        // Validación manual temporal
        $validated = $request->validate([
            'empresa_id' => 'required|exists:empresas,id',
            'observaciones' => 'nullable|string|max:500',
            'prioridad' => 'nullable|in:baja,normal,alta,urgente',
            'fecha_limite' => 'nullable|date|after:today',
            'instrucciones_generales' => 'nullable|string|max:1000',
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

        Log::info('Validación manual exitosa: ' . json_encode($validated));

        DB::beginTransaction();

        try {
            // Crear orden con solo los campos permitidos
            $datosOrden = [
                'observaciones' => $validated['observaciones'] ?? null,
                'prioridad' => $validated['prioridad'] ?? 'normal',
                'fecha_limite' => $validated['fecha_limite'] ?? null,
                'instrucciones_generales' => $validated['instrucciones_generales'] ?? null,
                'creado_por' => Auth::id(),
                'fecha_solicitud' => now()->toDateString(),
                'estado' => 'solicitud',
            ];

            if (Auth::user()->role_as == 1) {
                // Usuario empresa: usar su empresa_id
                $datosOrden['empresa_id'] = Auth::user()->empresa_id;
            } else {
                $datosOrden['empresa_id'] = $validated['empresa_id'];
            }

            Log::info('Datos para crear orden: ' . json_encode($datosOrden));

            $orden = Orden::create($datosOrden);
            Log::info('Orden creada con ID: ' . $orden->id);

            if ($request->has('evaluados')) {
                $this->procesarEvaluados($orden, $request->evaluados);
            }

            DB::commit();

            // Log temporal para debug
            Log::info('Orden creada exitosamente', [
                'orden_id' => $orden->id,
                'codigo' => $orden->codigo_orden,
                'evaluados_count' => $orden->evaluados()->count()
            ]);

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
                'request_data' => $request->all()
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
            'evaluados' => function($query) {
                $query->with(['poligrafista', 'cuestionario'])->orderBy('nombre');
            }
        ]);

        $estados = [
            'solicitud' => 'Solicitud',
            'programacion' => 'Programación',
            'en_proceso' => 'En Proceso',
            'analisis' => 'En Análisis',
            'entregado' => 'Entregado',
            'cancelado' => 'Cancelado'
        ];

        return view('admin.ordenes.show', compact('orden', 'estados'));
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

        $estados = [
            'solicitud' => 'Solicitud',
            'programacion' => 'Programación',
            'en_proceso' => 'En Proceso',
            'analisis' => 'En Análisis',
            'entregado' => 'Entregado',
            'cancelado' => 'Cancelado'
        ];

        return view('admin.ordenes.edit', compact('orden', 'empresas', 'poligrafistas', 'estados'));
    }

    /**
     * Actualizar orden
     */
    public function update(Request $request, Orden $orden)
    {
        if (!$this->usuarioPuedeEditarOrden($orden)) {
            abort(403);
        }

        // Debug: Log de entrada
        Log::info('=== INICIO UPDATE ORDEN ===');
        Log::info('Orden ID: ' . $orden->id);
        Log::info('Request data: ' . json_encode($request->all()));

        // Validación manual
        $validated = $request->validate([
            'empresa_id' => 'required|exists:empresas,id',
            'observaciones' => 'nullable|string|max:500',
            'prioridad' => 'nullable|in:baja,normal,alta,urgente',
            'fecha_limite' => 'nullable|date|after:today',
            'fecha_solicitud' => 'nullable|date',
            'instrucciones_generales' => 'nullable|string|max:1000',
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

        Log::info('Validación exitosa: ' . json_encode($validated));

        DB::beginTransaction();

        try {
            // Actualizar datos básicos de la orden - solo campos que vienen en el request
            $datosOrden = [
                'observaciones' => $validated['observaciones'] ?? $orden->observaciones,
                'prioridad' => $validated['prioridad'] ?? $orden->prioridad ?? 'normal',
                'instrucciones_generales' => $validated['instrucciones_generales'] ?? $orden->instrucciones_generales,
            ];

            // Solo actualizar estos campos si vienen explícitamente en el request
            if (isset($validated['fecha_limite'])) {
                $datosOrden['fecha_limite'] = $validated['fecha_limite'];
            }
            if (isset($validated['fecha_solicitud'])) {
                $datosOrden['fecha_solicitud'] = $validated['fecha_solicitud'];
            }
            if (isset($validated['poligrafista_id'])) {
                $datosOrden['poligrafista_id'] = $validated['poligrafista_id'];
            }

            if (Auth::user()->hasAnyRole(['admin', 'repro'])) {
                $datosOrden['empresa_id'] = $validated['empresa_id'];
            }

            Log::info('Actualizando orden con datos: ' . json_encode($datosOrden));

            $orden->update($datosOrden);

            if ($request->has('evaluados')) {
                $this->procesarEvaluados($orden, $request->evaluados, true);
            }

            DB::commit();

            Log::info('Orden actualizada exitosamente');

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
            'nuevo_estado' => 'required|in:solicitud,programacion,en_proceso,analisis,entregado,cancelado',
            'observaciones' => 'nullable|string|max:500'
        ]);

        if (!$orden->puedeTransicionarA($request->nuevo_estado)) {
            $estadoActualTexto = $orden->getEstadoTexto();
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

        $mensaje = $nuevoEstado
            ? 'Resultados ahora visibles para la empresa.'
            : 'Resultados ocultos para la empresa.';

        return back()->with('success', $mensaje);
    }

    /**
     * Procesar evaluados asociados a la orden
     */
    private function procesarEvaluados(Orden $orden, array $evaluados, bool $esActualizacion = false): void
    {
        Log::info('=== PROCESANDO EVALUADOS ===');
        Log::info('Orden ID: ' . $orden->id);
        Log::info('Cantidad de evaluados: ' . count($evaluados));
        Log::info('Es actualización: ' . ($esActualizacion ? 'Sí' : 'No'));

        if ($esActualizacion) {
            $evaluadosExistentes = $orden->evaluados->pluck('id')->toArray();
        }

        foreach ($evaluados as $index => $evaluadoData) {
            Log::info('Procesando evaluado ' . ($index + 1) . ': ' . json_encode($evaluadoData));
            $datosEvaluado = [
                'nombre' => $evaluadoData['nombre'],
                'apellidos' => $evaluadoData['apellidos'] ?? null,
                'dpi' => $evaluadoData['dpi'],
                'telefono' => $evaluadoData['telefono'] ?? null,
                'email' => $evaluadoData['email'] ?? null,
                'orden_id' => $orden->id,
                'token_unico' => Str::random(32),
                'token_expira_at' => now()->addDays(30),
                // Nuevos campos granulares
                'tipo_servicio' => $evaluadoData['tipo_servicio'],
                'tipo_formulario' => $evaluadoData['tipo_formulario'],
                'fecha_programada' => $evaluadoData['fecha_programada'] ?? null,
                'poligrafista_id' => $evaluadoData['poligrafista_id'] ?? null,
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
                Log::info('Evaluado creado con ID: ' . $evaluadoCreado->id);

                // Enviar notificación al evaluado si tiene email
                $this->notificarEvaluadoAsignado($evaluadoCreado);
            }
        }

        Log::info('=== FIN PROCESAMIENTO EVALUADOS ===');
        Log::info('Total evaluados en la orden: ' . $orden->evaluados()->count());

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
        $orden->load(['empresa', 'creador', 'evaluados.poligrafista']);

        $estados = [
            'solicitud' => 'Solicitud',
            'programacion' => 'Programación',
            'en_proceso' => 'En Proceso',
            'analisis' => 'En Análisis',
            'entregado' => 'Entregado',
            'cancelado' => 'Cancelado'
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.ordenes.pdf', compact('orden', 'estados'));

        return $pdf->stream('orden-' . $orden->codigo_orden . '.pdf');
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
}
