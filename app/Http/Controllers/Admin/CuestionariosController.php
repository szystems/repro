<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cuestionario;
use App\Support\CuestionarioFotoCandidato;
use App\Support\CuestionarioPrecarga;
use App\Support\CuestionarioSecciones;
use App\Support\EvaluadorNotasSupport;
use App\Support\InformePreempleo;
use App\Models\CuestionarioRespuesta;
use App\Models\EvaluadoOrden;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

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
        $query = EvaluadoOrden::with(['orden.empresa', 'orden.sede', 'cuestionario'])
            ->whereHas('orden', fn ($q) => $q->activas())
            ->orderBy('created_at', 'desc');

        if ($request->filled('empresa_id')) {
            $query->whereHas('orden', function ($q) use ($request) {
                $q->where('empresa_id', $request->empresa_id);
            });
        }

        if ($request->filled('tipo_servicio')) {
            $query->where('tipo_servicio', $request->tipo_servicio);
        }

        if ($request->filled('sede_id')) {
            $query->where(function ($q) use ($request) {
                $q->where('sede_id', $request->sede_id)
                    ->orWhereHas('orden', fn ($oq) => $oq->where('sede_id', $request->sede_id));
            });
        }

        if ($request->filled('tipo_formulario')) {
            $query->where('tipo_formulario', $request->tipo_formulario);
        }

        if ($request->filled('estado')) {
            match ($request->estado) {
                'completado' => $query->where('cuestionario_completado', true),
                'en_progreso' => $query->where('cuestionario_completado', false)
                    ->whereHas('cuestionario', fn ($q) => $q->where('seccion_actual', '>', 1)),
                'pendiente' => $query->where('cuestionario_completado', false)
                    ->where(function ($q) {
                        $q->doesntHave('cuestionario')
                            ->orWhereHas('cuestionario', fn ($c) => $c->where('seccion_actual', '<=', 1));
                    }),
                default => null,
            };
        } elseif ($request->filled('completado')) {
            $query->where('cuestionario_completado', $request->boolean('completado'));
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }

        if ($request->filled('buscar')) {
            $busqueda = $request->buscar;
            $query->where(function ($q) use ($busqueda) {
                $q->where('nombre', 'LIKE', "%{$busqueda}%")
                    ->orWhere('apellidos', 'LIKE', "%{$busqueda}%")
                    ->orWhere('dpi', 'LIKE', "%{$busqueda}%")
                    ->orWhere('telefono', 'LIKE', "%{$busqueda}%")
                    ->orWhere('email', 'LIKE', "%{$busqueda}%");
            });
        }

        if ($request->filled('sort')) {
            $direction = $request->input('direction', 'desc') === 'asc' ? 'asc' : 'desc';
            $query->reorder()->orderBy(
                in_array($request->sort, ['id', 'created_at'], true) ? $request->sort : 'created_at',
                $direction
            );
        }

        $evaluados = $query->paginate(20)->appends($request->query());

        $baseStats = EvaluadoOrden::whereHas('orden', fn ($q) => $q->activas());

        $estadisticas = [
            'total' => (clone $baseStats)->count(),
            'completados' => (clone $baseStats)->where('cuestionario_completado', true)->count(),
            'en_progreso' => (clone $baseStats)->where('cuestionario_completado', false)
                ->whereHas('cuestionario', fn ($q) => $q->where('seccion_actual', '>', 1))->count(),
            'pendientes' => (clone $baseStats)->where('cuestionario_completado', false)
                ->where(function ($q) {
                    $q->doesntHave('cuestionario')
                        ->orWhereHas('cuestionario', fn ($c) => $c->where('seccion_actual', '<=', 1));
                })->count(),
            'iniciados' => (clone $baseStats)->whereHas('cuestionario', fn ($q) => $q->where('seccion_actual', 1))->count(),
            'completados_hoy' => (clone $baseStats)->where('cuestionario_completado', true)
                ->whereDate('completado_at', today())->count(),
            'progreso_promedio' => round((float) Cuestionario::avg('progreso_porcentaje') ?? 0, 1),
            'por_tipo' => (clone $baseStats)->select('tipo_formulario', DB::raw('count(*) as total'))
                ->groupBy('tipo_formulario')
                ->pluck('total', 'tipo_formulario')
                ->toArray(),
            'por_estado' => [],
        ];

        $estadisticas['por_estado'] = [
            'completados' => $estadisticas['completados'],
            'en_progreso' => $estadisticas['en_progreso'],
            'pendientes' => $estadisticas['pendientes'],
            'iniciados' => $estadisticas['iniciados'],
        ];

        // Datos para filtros
        $empresas = \App\Models\Empresa::where('estado', 1)->orderBy('nombre')->get();
        $sedes = \App\Models\Sede::where('estado', 1)->orderBy('nombre')->get();
        $tiposFormulario = [
            'preempleo' => 'Pre-empleo',
            'periodica' => 'Periódica',
            'especifica' => 'Específica',
            'socioeconomico' => 'Socioeconómico'
        ];

        return view('admin.cuestionarios.index', compact(
            'evaluados',
            'empresas',
            'sedes',
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
            'evaluadoOrden.documentos',
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

        $cambiosPrecarga = CuestionarioPrecarga::cambiosRegistrados($cuestionario);
        $etiquetasPrecarga = CuestionarioPrecarga::etiquetasCampos();
        $contextoNotas = $this->contextoNotasEvaluador($cuestionario);
        $seccionFotoSlug = CuestionarioSecciones::slug(1, $cuestionario->tipo_formulario);
        $fotoCandidatoUrl = CuestionarioFotoCandidato::obtenerRuta($cuestionario->id, $seccionFotoSlug)
            ? route('admin.cuestionarios.foto-candidato', $cuestionario)
            : null;

        return view('admin.cuestionarios.show', compact(
            'cuestionario', 
            'respuestasPorSeccion',
            'secciones',
            'historialDPI',
            'cambiosPrecarga',
            'etiquetasPrecarga',
            'fotoCandidatoUrl'
        ) + $contextoNotas + $this->contextoInformePreempleo($cuestionario));
    }

    /**
     * Mostrar formulario de edición de cuestionario
     * Permite editar cuestionarios completados para correcciones
     */
    public function edit(int $id)
    {
        $cuestionario = Cuestionario::with([
            'evaluadoOrden.orden.empresa',
            'evaluadoOrden.documentos',
            'respuestas' => function($query) {
                $query->orderBy('seccion')->orderBy('campo');
            }
        ])->findOrFail($id);

        // Organizar respuestas por sección para edición
        $respuestasPorSeccion = $cuestionario->respuestas->groupBy('seccion');

        // Obtener configuración de secciones
        $secciones = $cuestionario->getSeccionesConfig();
        $contextoNotas = $this->contextoNotasEvaluador($cuestionario);
        $seccionFotoSlug = CuestionarioSecciones::slug(1, $cuestionario->tipo_formulario);
        $fotoCandidatoUrl = CuestionarioFotoCandidato::obtenerRuta($cuestionario->id, $seccionFotoSlug)
            ? route('admin.cuestionarios.foto-candidato', $cuestionario)
            : null;

        return view('admin.cuestionarios.edit', compact(
            'cuestionario', 
            'respuestasPorSeccion',
            'secciones',
            'fotoCandidatoUrl'
        ) + $contextoNotas + $this->contextoInformePreempleo($cuestionario));
    }

    /**
     * Actualizar respuestas del cuestionario
     */
    public function update(Request $request, int $id)
    {
        $cuestionario = Cuestionario::findOrFail($id);

        // Validar datos básicos - respuestas es opcional para permitir solo guardar observaciones
        $request->validate([
            'observaciones_repro' => 'nullable|string|max:2000',
            'respuestas' => 'nullable|array',
            'evaluador_notas' => 'nullable|array',
            'evaluador_notas.*' => 'nullable|string|max:10000',
            'informe_tablas' => 'nullable|array',
            'informe_tablas_restaurar' => 'nullable|array',
            'foto_candidato' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
        ]);

        if (($request->has('evaluador_notas') || $request->has('informe_tablas') || $request->has('informe_tablas_restaurar'))
            && ! EvaluadorNotasSupport::puedeGestionar(Auth::user())) {
            abort(403, 'No autorizado para editar notas internas del evaluador.');
        }

        DB::beginTransaction();
        try {
            // Actualizar observaciones de REPRO
            $cuestionario->update([
                'observaciones_repro' => $request->observaciones_repro
            ]);

            if ($request->has('evaluador_notas') && EvaluadorNotasSupport::puedeGestionar(Auth::user())) {
                EvaluadorNotasSupport::guardarDesdeRequest(
                    $cuestionario->evaluado_orden_id,
                    $request->input('evaluador_notas', []),
                    Auth::id()
                );
            }

            if (InformePreempleo::aplicaATipo($cuestionario->tipo_formulario)
                && EvaluadorNotasSupport::puedeGestionar(Auth::user())
                && ($request->has('informe_tablas') || $request->has('informe_tablas_restaurar'))) {
                InformePreempleo::guardarDesdeRequest(
                    $cuestionario->evaluado_orden_id,
                    $request->input('informe_tablas', []),
                    $request->input('informe_tablas_restaurar', []),
                    Auth::id()
                );
            }

            if ($request->hasFile('foto_candidato')) {
                $seccionFotoSlug = CuestionarioSecciones::slug(1, $cuestionario->tipo_formulario);
                CuestionarioFotoCandidato::guardar(
                    $request->file('foto_candidato'),
                    $cuestionario->id,
                    $seccionFotoSlug
                );
            }

            // Si hay respuestas para actualizar
            if ($request->has('respuestas') && is_array($request->respuestas)) {
                // Log especial para ediciones de cuestionarios completados
                if ($cuestionario->estado === 'completado') {
                    Log::info('Editando cuestionario completado', [
                        'cuestionario_id' => $cuestionario->id,
                        'usuario' => Auth::user()->name,
                        'usuario_id' => Auth::id(),
                        'evaluado_dpi' => $cuestionario->evaluadoOrden->dpi,
                        'motivo' => 'Corrección post-completado',
                        'timestamp' => now()
                    ]);
                }

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
                    Log::info('Cambios detallados en cuestionario completado', [
                        'cuestionario_id' => $cuestionario->id,
                        'usuario' => Auth::user()->name,
                        'cambios' => $cambiosRealizados,
                        'total_cambios' => count($cambiosRealizados)
                    ]);
                }
            }

            DB::commit();

            if ($request->has('guardar_borrador') && $request->expectsJson()) {
                return response()->json(['success' => true]);
            }

            $mensaje = match (true) {
                $request->hasFile('foto_candidato') => 'Fotografía del evaluado actualizada correctamente.',
                $request->has('respuestas') => 'Cuestionario actualizado correctamente.',
                $request->has('evaluador_notas') => 'Notas del evaluador guardadas correctamente.',
                default => 'Observaciones guardadas correctamente.',
            };

            return redirect()
                ->route('admin.cuestionarios.show', $cuestionario->id)
                ->with('success', $mensaje);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar cuestionario', [
                'cuestionario_id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            return back()
                ->withErrors(['error' => 'Error al actualizar el cuestionario.'])
                ->withInput();
        }
    }

    /**
     * Servir la foto del candidato en contexto administrativo (auth).
     */
    public function fotoCandidato(int $id): Response
    {
        $cuestionario = Cuestionario::findOrFail($id);
        $seccionSlug = CuestionarioSecciones::slug(1, $cuestionario->tipo_formulario);
        $path = CuestionarioFotoCandidato::obtenerRuta($cuestionario->id, $seccionSlug);

        if (! $path || ! Storage::disk('local')->exists($path)) {
            abort(404);
        }

        return Storage::disk('local')->response($path);
    }

    /**
     * Buscar historial completo por DPI
     */
    public function historialDpi(Request $request)
    {
        $buscar = null;
        $historial = collect();

        $termino = trim((string) $request->input('buscar', $request->input('dpi', '')));

        if ($termino !== '') {
            if (!preg_match('/^\d{13}$/', $termino) && (strlen($termino) < 2 || strlen($termino) > 100)) {
                return back()
                    ->withErrors(['buscar' => 'Ingrese un DPI de 13 dígitos o un nombre/apellido de al menos 2 caracteres.'])
                    ->withInput();
            }

            $buscar = $termino;
            $historial = EvaluadoOrden::buscarHistorial($buscar);
        }

        return view('admin.cuestionarios.historial-dpi', compact('historial', 'buscar'));
    }

    /**
     * Exportar cuestionario a PDF
     */
    public function exportarPdf(int $id)
    {
        $this->middleware('permission:cuestionarios.exportar');

        $cuestionario = Cuestionario::with([
            'evaluadoOrden.orden.empresa',
            'evaluadoOrden.documentos',
            'evaluadoOrden.responsable',
            'respuestas' => function($query) {
                $query->orderBy('seccion')->orderBy('campo');
            }
        ])->findOrFail($id);

        $respuestasPorSeccion = $cuestionario->respuestas->groupBy('seccion');

        // Obtener logo de REPRO desde configuración
        $config = \App\Models\Config::first();
        $imagen = null;
        if ($config && $config->logo && file_exists(public_path('assets/imgs/logos/'.$config->logo))) {
            $imagen = public_path('assets/imgs/logos/'.$config->logo);
        }

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('admin.cuestionarios.pdf', compact(
            'cuestionario', 
            'respuestasPorSeccion',
            'imagen'
        ));

        $nombreArchivo = $cuestionario->evaluadoOrden->nombre . '_' .
            ($cuestionario->evaluadoOrden->apellidos ?? '') . '_Orden' .
            $cuestionario->evaluadoOrden->orden->codigo_orden . '.pdf';

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
            'evaluadoOrden.documentos',
            'evaluadoOrden.responsable',
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

        $nombreArchivo = $cuestionario->evaluadoOrden->nombre . '_' .
            ($cuestionario->evaluadoOrden->apellidos ?? '') . '_Orden' .
            $cuestionario->evaluadoOrden->orden->codigo_orden . '.pdf';

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
            Log::info('Cuestionario marcado como completado manualmente', [
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
            Log::error('Error al marcar cuestionario como completado', [
                'cuestionario_id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
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

    /**
     * @return array{bloquesNotasEvaluador: list<array{numero: int, slug: string, titulo: string}>, notasEvaluador: array<string, string>, puedeGestionarNotasEvaluador: bool}
     */
    private function contextoNotasEvaluador(Cuestionario $cuestionario): array
    {
        $puedeGestionar = EvaluadorNotasSupport::puedeGestionar(Auth::user());

        return [
            'bloquesNotasEvaluador' => CuestionarioSecciones::bloquesNotasEvaluador($cuestionario->tipo_formulario),
            'notasEvaluador' => $puedeGestionar
                ? EvaluadorNotasSupport::mapaPorSeccion($cuestionario->evaluado_orden_id)
                : [],
            'puedeGestionarNotasEvaluador' => $puedeGestionar,
        ];
    }

    /**
     * @return array{informePreempleoActivo: bool, tablasInforme: array<string, mixed>, overridesInforme: list<string>, puedeGestionarInformePreempleo: bool}
     */
    private function contextoInformePreempleo(Cuestionario $cuestionario): array
    {
        if (! InformePreempleo::aplicaATipo($cuestionario->tipo_formulario)) {
            return [
                'informePreempleoActivo' => false,
                'tablasInforme' => [],
                'overridesInforme' => [],
                'puedeGestionarInformePreempleo' => false,
            ];
        }

        $puedeGestionar = EvaluadorNotasSupport::puedeGestionar(Auth::user());

        return [
            'informePreempleoActivo' => true,
            'tablasInforme' => $puedeGestionar
                ? InformePreempleo::tablasParaAdmin($cuestionario)
                : [],
            'overridesInforme' => $puedeGestionar
                ? InformePreempleo::clavesConOverride($cuestionario->evaluado_orden_id)
                : [],
            'puedeGestionarInformePreempleo' => $puedeGestionar,
        ];
    }
}
