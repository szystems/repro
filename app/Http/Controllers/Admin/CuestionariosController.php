<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cuestionario;
use App\Support\CamposInternosPreempleo;
use App\Support\CuestionariosIndexSupport;
use App\Support\CuestionarioFotoCandidato;
use App\Support\CuestionarioPrecarga;
use App\Support\CuestionarioSecciones;
use App\Support\EvaluadorNotasSupport;
use App\Support\FormularioAutoTransiciones;
use App\Support\InformePreempleo;
use App\Support\InformeWordAnexosPapeleria;
use App\Support\InformeWordBloquesEvaluador;
use App\Support\InformeWordExport;
use App\Support\InformeWordNombresArchivo;
use App\Support\InformeWordPreguntasPoligraficas;
use App\Support\InformeWordResultado;
use App\Support\TablaDinamica;
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
        FormularioAutoTransiciones::aplicarAlAcceder();

        return view('admin.cuestionarios.index', CuestionariosIndexSupport::resolver($request));
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
        ) + $contextoNotas + $this->contextoInformePreempleo($cuestionario) + $this->contextoInformeWord($cuestionario));
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
        ) + $contextoNotas + $this->contextoInformePreempleo($cuestionario) + $this->contextoInformeWord($cuestionario));
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
            'sede_region_empresa' => 'nullable|string|max:100',
            'respuestas' => 'nullable|array',
            'respuestas_campo' => 'nullable|array',
            'respuestas_campo.*' => 'nullable|array',
            'evaluador_notas' => 'nullable|array',
            'evaluador_notas.*' => 'nullable|string|max:10000',
            'informe_tablas' => 'nullable|array',
            'informe_tablas_restaurar' => 'nullable|array',
            'respuestas_tablas' => 'nullable|array',
            'foto_candidato' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
            'resultado_informe' => 'nullable|string|max:40',
        ]);

        if (($request->has('evaluador_notas') || $request->has('informe_tablas') || $request->has('informe_tablas_restaurar'))
            && ! EvaluadorNotasSupport::puedeGestionar(Auth::user())) {
            abort(403, 'No autorizado para editar notas internas del evaluador.');
        }

        DB::beginTransaction();
        try {
            $datosActualizar = [
                'observaciones_repro' => $request->observaciones_repro,
            ];

            if ($request->has('estado')) {
                $datosActualizar['estado'] = $request->input('estado');
            }

            if ($request->has('completado_at')) {
                $completadoAt = $request->input('completado_at');
                $datosActualizar['completado_at'] = $completadoAt !== '' && $completadoAt !== null
                    ? $completadoAt
                    : null;
            }

            if ($request->has('progreso_secciones') && is_array($request->input('progreso_secciones'))) {
                $progreso = [];
                foreach ($request->input('progreso_secciones') as $num => $valor) {
                    $progreso[(int) $num] = (bool) $valor;
                }
                $datosActualizar['progreso_secciones'] = $progreso;
            }

            $cuestionario->update($datosActualizar);

            if ($request->has('sede_region_empresa') && Auth::user()->role_as >= 2) {
                $evaluado = $cuestionario->evaluadoOrden;
                $nuevaAgencia = trim((string) $request->input('sede_region_empresa'));
                $evaluado->update(['sede_region_empresa' => $nuevaAgencia !== '' ? $nuevaAgencia : null]);

                $snapshot = $cuestionario->datos_precarga_json;
                if (is_array($snapshot)) {
                    $snapshot['agencia_region'] = $nuevaAgencia;
                    $cuestionario->update(['datos_precarga_json' => $snapshot]);
                }
            }

            if ($request->has('evaluador_notas') && EvaluadorNotasSupport::puedeGestionar(Auth::user())) {
                EvaluadorNotasSupport::guardarDesdeRequest(
                    $cuestionario->evaluado_orden_id,
                    $request->input('evaluador_notas', []),
                    Auth::id()
                );
            }

            if ($request->has('resultado_informe') && EvaluadorNotasSupport::puedeGestionar(Auth::user())) {
                InformeWordResultado::guardarEnEvaluado(
                    $cuestionario->evaluadoOrden,
                    (string) $request->input('resultado_informe')
                );
            }

            if (EvaluadorNotasSupport::puedeGestionar(Auth::user())) {
                if ($request->has('_word_anexos_papeleria')) {
                    InformeWordAnexosPapeleria::guardarSeleccion(
                        $cuestionario->evaluado_orden_id,
                        $request->input('word_anexos_papeleria', []),
                        Auth::id()
                    );
                }

                if ($request->has('_preguntas_poligraficas')) {
                    InformeWordPreguntasPoligraficas::guardarDesdeRequest(
                        $cuestionario->evaluado_orden_id,
                        $request->input('preguntas_poligraficas', []),
                        Auth::id()
                    );
                }
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

            if ($request->has('respuestas_tablas') && is_array($request->respuestas_tablas)) {
                $this->guardarTablasDesdeAdmin($cuestionario, $request->respuestas_tablas);
            }

            // Respuestas por slug+campo (formulario admin alineado al PDF)
            if ($request->has('respuestas_campo') && is_array($request->respuestas_campo)) {
                foreach ($request->respuestas_campo as $slugSeccion => $campos) {
                    if (! is_string($slugSeccion) || ! is_array($campos)) {
                        continue;
                    }
                    foreach ($campos as $campo => $nuevoValor) {
                        if (! is_string($campo) || CamposInternosPreempleo::esCampoSistema($campo)) {
                            continue;
                        }
                        CuestionarioRespuesta::updateOrCreate(
                            [
                                'cuestionario_id' => $cuestionario->id,
                                'seccion' => $slugSeccion,
                                'campo' => $campo,
                            ],
                            ['valor' => is_array($nuevoValor) ? json_encode($nuevoValor, JSON_UNESCAPED_UNICODE) : (string) $nuevoValor]
                        );
                    }
                }
            }

            // Si hay respuestas para actualizar (legacy por ID de fila)
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
                $request->has('respuestas_tablas') || $request->has('respuestas_campo') || $request->has('respuestas') => 'Cuestionario actualizado correctamente.',
                $request->has('evaluador_notas') => 'Notas del evaluador guardadas correctamente.',
                default => 'Observaciones guardadas correctamente.',
            };

            return redirect()
                ->route('admin.cuestionarios.edit', $cuestionario->id)
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

    /** @param  array<string, mixed>  $tablasPorSlug */
    private function guardarTablasDesdeAdmin(Cuestionario $cuestionario, array $tablasPorSlug): void
    {
        $tipo = $cuestionario->tipo_formulario ?? 'preempleo';
        $slugANumero = array_flip(CuestionarioSecciones::slugsPorTipo()[$tipo] ?? []);

        foreach ($tablasPorSlug as $slugSeccion => $tablas) {
            if (! is_string($slugSeccion) || ! is_array($tablas)) {
                continue;
            }

            $numero = $slugANumero[$slugSeccion] ?? null;
            if ($numero === null) {
                continue;
            }

            $camposColumnas = TablaDinamica::camposPorSeccion((int) $numero, $tipo);

            foreach ($tablas as $campo => $filas) {
                if (! isset($camposColumnas[$campo])) {
                    continue;
                }

                $normalizadas = TablaDinamica::normalizarFilas($filas, $camposColumnas[$campo]);
                CuestionarioRespuesta::guardarTabla(
                    $cuestionario->id,
                    $slugSeccion,
                    (string) $campo,
                    $normalizadas
                );
            }
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

        return $pdf->stream($nombreArchivo);
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
                'token_expira_at' => EvaluadoOrden::calcularExpiracionToken(),
            ]);
        }

        // TODO: Enviar email con nuevo token

        return back()->with('success', 'Token reenviado correctamente al evaluado.');
    }

    /**
     * PDF del cuestionario: se abre en el navegador (inline). El usuario descarga desde el visor si lo necesita.
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

        return $pdf->stream($nombreArchivo);
    }

    /**
     * G1.3 — Borrador Word desde edición de cuestionario (mismo motor que orden).
     */
    public function informeWordBorrador(int $id)
    {
        $cuestionario = Cuestionario::with([
            'evaluadoOrden.orden.empresa',
            'evaluadoOrden.poligrafista',
            'evaluadoOrden.responsable',
            'evaluadoOrden.sede',
        ])->findOrFail($id);

        if (! EvaluadorNotasSupport::puedeGestionar(Auth::user())) {
            abort(403, 'No autorizado para generar el informe Word.');
        }

        $evaluado = $cuestionario->evaluadoOrden;
        $orden = $evaluado->orden;

        $path = InformeWordExport::generar($orden, $evaluado);
        $filename = InformeWordNombresArchivo::generar($evaluado, $orden, 'Borrador');

        return response()->download($path, $filename)->deleteFileAfterSend(true);
    }

    /**
     * H13 — Vista previa inline del borrador Word generado (mismo motor que descarga).
     */
    public function informeWordPreview(int $id)
    {
        $cuestionario = Cuestionario::with([
            'evaluadoOrden.orden.empresa',
            'evaluadoOrden.poligrafista',
            'evaluadoOrden.responsable',
            'evaluadoOrden.sede',
        ])->findOrFail($id);

        if (! EvaluadorNotasSupport::puedeGestionar(Auth::user())) {
            abort(403, 'No autorizado para previsualizar el informe Word.');
        }

        $evaluado = $cuestionario->evaluadoOrden;
        $orden = $evaluado->orden;

        $path = InformeWordExport::generar($orden, $evaluado);
        $filename = InformeWordNombresArchivo::generar($evaluado, $orden, 'Borrador');

        return response()->file($path, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ])->deleteFileAfterSend(true);
    }

    /**
     * G1.3 — Vista previa inline del informe final subido (PDF).
     */
    public function informeFinalPreview(int $id)
    {
        $cuestionario = Cuestionario::with('evaluadoOrden')->findOrFail($id);

        if (! EvaluadorNotasSupport::puedeGestionar(Auth::user())) {
            abort(403);
        }

        $evaluado = $cuestionario->evaluadoOrden;
        $archivo = $evaluado->archivo_resultado_final;

        if (! $archivo || ! Storage::disk('local')->exists($archivo)) {
            abort(404, 'No hay informe final subido para este evaluado.');
        }

        $extension = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
        if ($extension !== 'pdf') {
            abort(415, 'La vista previa solo está disponible para informes finales en PDF.');
        }

        $path = Storage::disk('local')->path($archivo);
        $nombre = 'InformeFinal_' . ($evaluado->dpi ?: $evaluado->id) . '.pdf';

        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $nombre . '"',
        ]);
    }

    /**
     * PDF de autorización y términos (documento aparte del cuestionario).
     * Se abre en el navegador (inline); el usuario descarga desde el visor si lo necesita.
     */
    public function generarPdfAutorizacion(int $id)
    {
        $cuestionario = Cuestionario::with([
            'evaluadoOrden.orden.empresa',
            'evaluadoOrden.responsable',
        ])->findOrFail($id);

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('admin.cuestionarios.pdf-autorizacion', compact('cuestionario'));

        $nombreArchivo = $cuestionario->evaluadoOrden->nombre . '_' .
            ($cuestionario->evaluadoOrden->apellidos ?? '') . '_Autorizacion_Orden' .
            $cuestionario->evaluadoOrden->orden->codigo_orden . '.pdf';

        return $pdf->stream($nombreArchivo);
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

    /**
     * @return array{
     *   informeWordPoligrafico: bool,
     *   preguntasPoligraficas: list<array{pregunta: string, respuesta: string, resultado: string, puntuacion: string}>,
     *   tiposAnexoDisponibles: array<string, string>,
     *   anexosPapeleriaSeleccionados: list<string>
     * }
     */
    private function contextoInformeWord(Cuestionario $cuestionario): array
    {
        $evaluado = $cuestionario->evaluadoOrden;
        $puede = EvaluadorNotasSupport::puedeGestionar(Auth::user());

        return [
            'informeWordPoligrafico' => InformeWordPreguntasPoligraficas::aplicaA($evaluado),
            'preguntasPoligraficasUsaPuntuacion' => InformeWordPreguntasPoligraficas::usaPuntuacion($evaluado),
            'preguntasPoligraficas' => $puede
                ? InformeWordPreguntasPoligraficas::filas($evaluado->id, $evaluado)
                : [],
            'tiposAnexoDisponibles' => $puede
                ? InformeWordAnexosPapeleria::tiposDisponibles($evaluado)
                : [],
            'anexosPapeleriaSeleccionados' => $puede
                ? InformeWordAnexosPapeleria::tiposSeleccionados($evaluado->id)
                : [],
            'bloquesWordCompletos' => InformeWordBloquesEvaluador::completos($evaluado->id),
            'bloquesWordFaltantes' => InformeWordBloquesEvaluador::titulosFaltantes($evaluado->id),
            'tieneInformeFinal' => $evaluado->tieneResultadoFinal(),
            'informeFinalEsPdf' => $evaluado->archivo_resultado_final
                && strtolower(pathinfo($evaluado->archivo_resultado_final, PATHINFO_EXTENSION)) === 'pdf',
        ];
    }
}
