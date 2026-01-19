<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cuestionario;
use App\Models\CuestionarioRespuesta;
use App\Models\EvaluadoOrden;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Controlador administrativo para gestión de cuestionarios
 * 
 * Permite a REPRO:
 * - Ver listado de cuestionarios completados
 * - Ver detalles de cuestionarios específicos
 * - Editar respuestas para correcciones
 * - Buscar por DPI (historial completo)
 */
class CuestionariosController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Mostrar listado de cuestionarios
     */
    public function index(Request $request)
    {
        $query = Cuestionario::with(['evaluadoOrden.orden.empresa'])
            ->orderBy('created_at', 'desc');

        // Filtros
        if ($request->filled('empresa_id')) {
            $query->whereHas('evaluadoOrden.orden', function($q) use ($request) {
                $q->where('empresa_id', $request->empresa_id);
            });
        }

        if ($request->filled('tipo_formulario')) {
            $query->where('tipo_formulario', $request->tipo_formulario);
        }

        if ($request->filled('completado')) {
            $query->where('completado', $request->boolean('completado'));
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }

        // Búsqueda por nombre o DPI
        if ($request->filled('buscar')) {
            $busqueda = $request->buscar;
            $query->whereHas('evaluadoOrden', function($q) use ($busqueda) {
                $q->where('nombre', 'LIKE', "%{$busqueda}%")
                  ->orWhere('dpi', 'LIKE', "%{$busqueda}%");
            });
        }

        $cuestionarios = $query->paginate(20)->appends($request->query());

        // Estadísticas para el dashboard
        $estadisticas = [
            'total' => Cuestionario::count(),
            'completados' => Cuestionario::where('completado', 1)->count(),
            'en_progreso' => Cuestionario::where('completado', 0)->where('seccion_actual', '>', 1)->count(),
            'pendientes' => Cuestionario::where('completado', 0)->where('seccion_actual', '<=', 1)->count(),
            'iniciados' => Cuestionario::where('seccion_actual', 1)->count(),
            'completados_hoy' => Cuestionario::where('completado', 1)->whereDate('completado_at', today())->count(),
            'progreso_promedio' => round(Cuestionario::avg('progreso_porcentaje') ?? 0, 1),
            'por_tipo' => Cuestionario::select('tipo_formulario', DB::raw('count(*) as total'))
                ->groupBy('tipo_formulario')
                ->pluck('total', 'tipo_formulario')
                ->toArray(),
            'por_estado' => [
                'completados' => Cuestionario::where('completado', 1)->count(),
                'en_progreso' => Cuestionario::where('completado', 0)->where('seccion_actual', '>', 1)->count(),
                'pendientes' => Cuestionario::where('completado', 0)->where('seccion_actual', '<=', 1)->count(),
                'iniciados' => Cuestionario::where('seccion_actual', 1)->count(),
            ]
        ];

        // Datos para filtros
        $empresas = \App\Models\Empresa::where('estado', 1)->orderBy('nombre')->get();
        $tiposFormulario = [
            'preempleo' => 'Pre-empleo',
            'periodica' => 'Periódica',
            'especifica' => 'Específica',
            'socioeconomico' => 'Socioeconómico'
        ];

        return view('admin.cuestionarios.index', compact(
            'cuestionarios', 
            'empresas', 
            'tiposFormulario',
            'estadisticas'
        ));
    }

    /**
     * Mostrar detalles de un cuestionario específico
     */
    public function show(int $id)
    {
        $cuestionario = Cuestionario::with([
            'evaluadoOrden.orden.empresa',
            'respuestas' => function($query) {
                $query->orderBy('seccion')->orderBy('campo');
            }
        ])->findOrFail($id);

        // Organizar respuestas por sección
        $respuestasPorSeccion = $cuestionario->respuestas->groupBy('seccion');

        // Obtener configuración de secciones
        $secciones = $cuestionario->getSeccionesConfig();

        // Obtener historial por DPI (solo para admin y repro)
        $historialDPI = null;
        if (Auth::user()->isAdmin() || Auth::user()->isRepro()) {
            $historialDPI = EvaluadoOrden::historialPorDpi($cuestionario->evaluadoOrden->dpi);
        }

        return view('admin.cuestionarios.show', compact(
            'cuestionario', 
            'respuestasPorSeccion',
            'secciones',
            'historialDPI'
        ));
    }

    /**
     * Mostrar formulario de edición de cuestionario
     * Permite editar cuestionarios completados para correcciones
     */
    public function edit(int $id)
    {
        $cuestionario = Cuestionario::with([
            'evaluadoOrden.orden.empresa',
            'respuestas' => function($query) {
                $query->orderBy('seccion')->orderBy('campo');
            }
        ])->findOrFail($id);

        // Organizar respuestas por sección para edición
        $respuestasPorSeccion = $cuestionario->respuestas->groupBy('seccion');

        // Obtener configuración de secciones
        $secciones = $cuestionario->getSeccionesConfig();

        return view('admin.cuestionarios.edit', compact(
            'cuestionario', 
            'respuestasPorSeccion',
            'secciones'
        ));
    }

    /**
     * Actualizar respuestas del cuestionario
     */
    public function update(Request $request, int $id)
    {
        $cuestionario = Cuestionario::findOrFail($id);

        // Validar datos básicos
        $request->validate([
            'observaciones_repro' => 'nullable|string|max:2000',
            'respuestas' => 'required|array',
        ]);

        DB::beginTransaction();
        try {
            // Log especial para ediciones de cuestionarios completados
            if ($cuestionario->estado === 'completado') {
                \Illuminate\Support\Facades\Log::info('Editando cuestionario completado', [
                    'cuestionario_id' => $cuestionario->id,
                    'usuario' => Auth::user()->name,
                    'usuario_id' => Auth::id(),
                    'evaluado_dpi' => $cuestionario->evaluadoOrden->dpi,
                    'motivo' => 'Corrección post-completado',
                    'timestamp' => now()
                ]);
            }

            // Actualizar observaciones de REPRO
            $cuestionario->update([
                'observaciones_repro' => $request->observaciones_repro
            ]);

            // Actualizar respuestas y registrar cambios
            $cambiosRealizados = [];
            foreach ($request->respuestas as $respuestaId => $nuevoValor) {
                $respuesta = CuestionarioRespuesta::where('cuestionario_id', $cuestionario->id)
                    ->where('id', $respuestaId)
                    ->first();

                if ($respuesta) {
                    $valorAnterior = $respuesta->valor;
                    if ($valorAnterior !== $nuevoValor) {
                        $cambiosRealizados[] = [
                            'campo' => $respuesta->campo,
                            'seccion' => $respuesta->seccion,
                            'valor_anterior' => $valorAnterior,
                            'valor_nuevo' => $nuevoValor
                        ];
                    }
                    $respuesta->update(['valor' => $nuevoValor]);
                }
            }

            // Log detallado de cambios para cuestionarios completados
            if ($cuestionario->estado === 'completado' && !empty($cambiosRealizados)) {
                \Illuminate\Support\Facades\Log::info('Cambios detallados en cuestionario completado', [
                    'cuestionario_id' => $cuestionario->id,
                    'usuario' => Auth::user()->name,
                    'cambios' => $cambiosRealizados,
                    'total_cambios' => count($cambiosRealizados)
                ]);
            }

            DB::commit();

            $mensaje = $cuestionario->estado === 'completado' 
                ? 'Correcciones guardadas correctamente en cuestionario completado.'
                : 'Cuestionario actualizado correctamente.';

            return redirect()
                ->route('admin.cuestionarios.show', $cuestionario->id)
                ->with('success', $mensaje);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withErrors(['error' => 'Error al actualizar el cuestionario.'])
                ->withInput();
        }
    }

    /**
     * Buscar historial completo por DPI
     */
    public function historialDpi(Request $request)
    {
        $this->middleware('permission:cuestionarios.historial');

        $request->validate([
            'dpi' => 'required|string|size:13|regex:/^[0-9]{13}$/'
        ]);

        $dpi = $request->dpi;
        $historial = EvaluadoOrden::historialPorDpi($dpi);

        if ($historial->isEmpty()) {
            return back()->with('warning', 'No se encontraron registros para el DPI proporcionado.');
        }

        return view('admin.cuestionarios.historial-dpi', compact('historial', 'dpi'));
    }

    /**
     * Exportar cuestionario a PDF
     */
    public function exportarPdf(int $id)
    {
        $this->middleware('permission:cuestionarios.exportar');

        $cuestionario = Cuestionario::with([
            'evaluadoOrden.orden.empresa',
            'respuestas' => function($query) {
                $query->orderBy('seccion')->orderBy('campo');
            }
        ])->findOrFail($id);

        $respuestasPorSeccion = $cuestionario->respuestas->groupBy('seccion');

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('admin.cuestionarios.pdf', compact(
            'cuestionario', 
            'respuestasPorSeccion'
        ));

        $nombreArchivo = 'cuestionario_' . 
            $cuestionario->evaluadoOrden->dpi . '_' . 
            $cuestionario->created_at->format('Y-m-d') . '.pdf';

        return $pdf->download($nombreArchivo);
    }

    /**
     * Reenviar token de cuestionario
     */
    public function reenviarToken(int $evaluadoId)
    {
        $this->middleware('permission:ordenes.gestionar');

        $evaluado = EvaluadoOrden::findOrFail($evaluadoId);

        // Verificar que no esté completado
        if ($evaluado->cuestionario_completado) {
            return back()->with('error', 'Este cuestionario ya fue completado.');
        }

        // Generar nuevo token si el actual expiró
        if ($evaluado->token_expira_at < now()) {
            $evaluado->update([
                'token_unico' => EvaluadoOrden::generarToken(),
                'token_expira_at' => now()->addDays(30)
            ]);
        }

        // TODO: Enviar email con nuevo token

        return back()->with('success', 'Token reenviado correctamente al evaluado.');
    }

    /**
     * Generar PDF del cuestionario
     */
    public function generarPDF(int $id)
    {
        $cuestionario = Cuestionario::with([
            'evaluadoOrden.orden.empresa',
            'respuestas' => function($query) {
                $query->orderBy('seccion')->orderBy('campo');
            }
        ])->findOrFail($id);

        $respuestasPorSeccion = $cuestionario->respuestas->groupBy('seccion');

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('admin.cuestionarios.pdf', compact(
            'cuestionario', 
            'respuestasPorSeccion'
        ));

        $nombreArchivo = 'cuestionario_' . 
            $cuestionario->evaluadoOrden->dpi . '_' . 
            $cuestionario->created_at->format('Y-m-d') . '.pdf';

        return $pdf->download($nombreArchivo);
    }

    /**
     * Marcar cuestionario como completado manualmente
     */
    public function marcarCompleto(int $id)
    {
        $cuestionario = Cuestionario::findOrFail($id);

        if ($cuestionario->completado) {
            return back()->with('warning', 'Este cuestionario ya está marcado como completado.');
        }

        DB::beginTransaction();
        try {
            $cuestionario->update([
                'completado' => true,
                'completado_at' => now(),
                'estado' => 'completado',
                'seccion_actual' => $cuestionario->total_secciones,
                'progreso_secciones' => array_fill(1, $cuestionario->total_secciones, true)
            ]);

            // Log de la acción manual
            \Illuminate\Support\Facades\Log::info('Cuestionario marcado como completado manualmente', [
                'cuestionario_id' => $cuestionario->id,
                'usuario' => Auth::user()->name,
                'usuario_id' => Auth::id(),
                'evaluado_dpi' => $cuestionario->evaluadoOrden->dpi,
                'timestamp' => now()
            ]);

            DB::commit();

            return back()->with('success', 'Cuestionario marcado como completado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al marcar el cuestionario como completado.');
        }
    }

    /**
     * Estadísticas generales
     */
    public function estadisticas()
    {
        $this->middleware('permission:reportes.ver');

        $stats = [
            'total_cuestionarios' => Cuestionario::count(),
            'completados' => Cuestionario::where('completado', true)->count(),
            'pendientes' => Cuestionario::where('completado', false)->count(),
            'por_tipo' => Cuestionario::select('tipo_formulario', DB::raw('count(*) as total'))
                ->groupBy('tipo_formulario')
                ->get()
                ->pluck('total', 'tipo_formulario'),
            'por_mes' => Cuestionario::select(
                    DB::raw('YEAR(created_at) as año'),
                    DB::raw('MONTH(created_at) as mes'),
                    DB::raw('count(*) as total')
                )
                ->groupBy('año', 'mes')
                ->orderBy('año', 'desc')
                ->orderBy('mes', 'desc')
                ->limit(12)
                ->get(),
        ];

        return view('admin.cuestionarios.estadisticas', compact('stats'));
    }
}
