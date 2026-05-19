<?php

namespace App\Http\Controllers;

use App\Http\Requests\Cuestionario\DatosPersonalesRequest;
use App\Http\Requests\Cuestionario\InformacionFamiliarRequest;
use App\Http\Requests\Cuestionario\HistorialLaboralRequest;
use App\Http\Requests\Cuestionario\SituacionEconomicaRequest;
use App\Http\Requests\Cuestionario\AntecedentesRequest;
use App\Models\EvaluadoOrden;
use App\Models\Cuestionario;
use App\Models\CuestionarioRespuesta;
use App\Models\FormularioCampo;
use App\Models\User;
use App\Notifications\CuestionarioCompletadoNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Controlador público para cuestionarios de evaluados
 * 
 * IMPORTANTE: Este controlador NO requiere autenticación
 * Los evaluados acceden mediante token único
 */
class CuestionarioController extends Controller
{
    /**
     * Mostrar página de verificación de identidad y acceso inicial
     */
    public function mostrar(string $token)
    {
        try {
            // Buscar evaluado por token
            $evaluado = EvaluadoOrden::where('token_unico', $token)
                ->where('token_expira_at', '>', now())
                ->with(['orden.empresa'])
                ->firstOrFail();

            // Verificar si ya completó el cuestionario
            if ($evaluado->cuestionario_completado) {
                return view('cuestionario.completado', compact('evaluado'));
            }

            // Registrar acceso (comentado temporalmente para debug)
            // $evaluado->registrarAcceso();

            // Verificar si ya existe un cuestionario iniciado
            $cuestionario = $evaluado->cuestionario;
            
            // Comentado temporalmente para debug
            // if (!$cuestionario) {
            //     // Crear nuevo cuestionario
            //     $cuestionario = $this->crearCuestionario($evaluado);
            // }

            // Redirigir a verificación de identidad
            return view('cuestionario.verificar-identidad', compact('evaluado', 'token'));

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Token no encontrado o expirado
            abort(404, 'El enlace al cuestionario no es válido o ha expirado.');
        } catch (\Exception $e) {
            Log::error('Error en cuestionario.mostrar', [
                'token' => $token,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            abort(500, 'Ha ocurrido un error al cargar el cuestionario.');
        }
    }

    /**
     * Verificar identidad del evaluado con DPI
     */
    public function verificarIdentidad(Request $request, string $token)
    {
        $request->validate([
            'dpi_ingresado' => 'required|string|size:13|regex:/^[0-9]{13}$/',
        ], [
            'dpi_ingresado.required' => 'Debe ingresar su DPI.',
            'dpi_ingresado.size' => 'El DPI debe tener exactamente 13 dígitos.',
            'dpi_ingresado.regex' => 'El DPI solo puede contener números.',
        ]);

        $evaluado = EvaluadoOrden::where('token_unico', $token)
            ->where('token_expira_at', '>', now())
            ->firstOrFail();
        $dpiIngresado = preg_replace('/[^0-9]/', '', $request->dpi_ingresado);

        if ($dpiIngresado !== $evaluado->dpi) {
            return back()->withErrors([
                'dpi_ingresado' => 'El DPI ingresado no coincide con nuestros registros.'
            ])->withInput();
        }

        // DPI correcto, crear cuestionario si no existe y redirigir
        $cuestionario = $evaluado->cuestionario;
        
        if (!$cuestionario) {
            // Crear nuevo cuestionario para este evaluado
            $tipoFormulario = $evaluado->tipo_formulario ?? 'preempleo';
            $totalSeccionesPorTipo = [
                'preempleo' => 5,
                'periodica' => 5,
                'especifica' => 4,
                'socioeconomico' => 7,
            ];
            $cuestionario = $evaluado->cuestionario()->create([
                'tipo_formulario' => $tipoFormulario,
                'seccion_actual' => 1,
                'total_secciones' => $totalSeccionesPorTipo[$tipoFormulario] ?? 5,
                'progreso_porcentaje' => 0,
                'completado' => false,
                'bloqueado' => false
            ]);
        }
        
        // Si ya aceptó términos, ir a la sección; si no, ir a términos
        if ($cuestionario->acepta_terminos) {
            return redirect()->route('cuestionario.seccion', [
                'token' => $token,
                'numero' => $cuestionario->seccion_actual
            ]);
        }

        return redirect()->route('cuestionario.terminos', ['token' => $token]);
    }

    /**
     * Mostrar pantalla de Términos y Condiciones con firma de autorización.
     */
    public function terminos(string $token)
    {
        $evaluado = EvaluadoOrden::where('token_unico', $token)
            ->with(['orden.empresa'])
            ->firstOrFail();

        if ($evaluado->cuestionario_completado) {
            return view('cuestionario.completado', compact('evaluado'));
        }

        $cuestionario = $evaluado->cuestionario;

        // Si ya aceptó, redirigir a secciones
        if ($cuestionario && $cuestionario->acepta_terminos) {
            return redirect()->route('cuestionario.seccion', [
                'token' => $token,
                'numero' => $cuestionario->seccion_actual
            ]);
        }

        $tipoServicio = $evaluado->tipo_servicio;

        return view('cuestionario.terminos', compact('evaluado', 'cuestionario', 'token', 'tipoServicio'));
    }

    /**
     * Procesar aceptación de términos con firma digital.
     */
    public function aceptarTerminos(Request $request, string $token)
    {
        $request->validate([
            'acepta_terminos'    => 'required|accepted',
            'firma_digital'      => 'required|string',
            'tipo_proceso'       => 'nullable|string',
        ], [
            'acepta_terminos.required' => 'Debe aceptar los términos y condiciones.',
            'acepta_terminos.accepted' => 'Debe aceptar los términos y condiciones.',
            'firma_digital.required'   => 'Debe proporcionar su firma digital.',
        ]);

        $evaluado = EvaluadoOrden::where('token_unico', $token)
            ->where('token_expira_at', '>', now())
            ->firstOrFail();
        $cuestionario = $evaluado->cuestionario;

        if (!$cuestionario) {
            return back()->with('error', 'Cuestionario no encontrado.');
        }

        $cuestionario->update([
            'acepta_terminos'    => true,
            'acepta_terminos_at' => now(),
            'ip_terminos'        => $request->ip(),
            'firma_digital'      => $request->input('firma_digital'),
        ]);

        return redirect()->route('cuestionario.seccion', [
            'token' => $token,
            'numero' => $cuestionario->seccion_actual
        ]);
    }

    /**
     * Mostrar sección específica del cuestionario
     */
    public function seccion(string $token, int $numero)
    {
        $evaluado = EvaluadoOrden::where('token_unico', $token)
            ->with(['orden.empresa'])
            ->firstOrFail();
        
        // Verificar si el token está expirado
        if ($evaluado->token_expira_at <= now()) {
            return response()->json(['error' => 'Token expirado'], 403);
        }
        
        // Verificar si ya completó el cuestionario
        if ($evaluado->cuestionario_completado) {
            return view('cuestionario.completado', compact('evaluado'));
        }
        
        $cuestionario = $evaluado->cuestionario;

        // Crear cuestionario si no existe
        if (!$cuestionario) {
            $tipoFormulario = $evaluado->tipo_formulario ?? 'preempleo';
            $totalSeccionesPorTipo = [
                'preempleo' => 5,
                'periodica' => 5,
                'especifica' => 4,
                'socioeconomico' => 7,
            ];
            $cuestionario = $evaluado->cuestionario()->create([
                'tipo_formulario' => $tipoFormulario,
                'seccion_actual' => 1,
                'total_secciones' => $totalSeccionesPorTipo[$tipoFormulario] ?? 5,
                'progreso_porcentaje' => 0,
                'completado' => false,
                'bloqueado' => false
            ]);
        }

        // Verificar que puede acceder a esta sección
        if (!$cuestionario->puedeAvanzarASeccion($numero)) {
            return redirect()->route('cuestionario.seccion', [
                'token' => $token,
                'numero' => $cuestionario->seccion_actual
            ]);
        }

        // Obtener configuración de secciones
        $secciones = $cuestionario->getSeccionesConfig();
        $nombreSeccion = $secciones[$numero] ?? 'Sección ' . $numero;

        // Obtener respuestas existentes para esta sección
        $seccionSlug = $this->getSlugSeccion($numero, $cuestionario->tipo_formulario);
        $respuestasExistentes = $cuestionario->getRespuestasPorSeccion($seccionSlug);

        // Datos adicionales para la vista
        $numeroSeccion = $numero;
        $totalSecciones = count($secciones);
        $tituloSeccion = $nombreSeccion;
        $nombresSecciones = $secciones;
        $iconoSeccion = $this->getIconoSeccion($numero);

        // Variables adicionales para el layout
        $currentSection = $numero;
        $totalSections = count($secciones);

        return view('cuestionario.seccion', compact(
            'evaluado', 
            'cuestionario', 
            'token', 
            'numero',
            'numeroSeccion',
            'totalSecciones',
            'totalSections',
            'currentSection',
            'tituloSeccion',
            'nombreSeccion', 
            'secciones',
            'nombresSecciones',
            'respuestasExistentes',
            'iconoSeccion'
        ));
    }

    /**
     * Guardar datos de una sección
     */
    public function guardarSeccion(Request $request, string $token, int $numero)
    {
        $evaluado = EvaluadoOrden::where('token_unico', $token)
            ->where('token_expira_at', '>', now())
            ->firstOrFail();
        $cuestionario = $evaluado->cuestionario;

        // Validar según la sección
        $datosValidados = $this->validarSeccion($request, $numero);

        DB::beginTransaction();
        try {
            // Obtener slug de la sección
            $seccionSlug = $this->getSlugSeccion($numero, $cuestionario->tipo_formulario);

            // Guardar respuestas
            CuestionarioRespuesta::guardarRespuestas(
                $cuestionario->id, 
                $seccionSlug, 
                $datosValidados
            );

            // Actualizar progreso si es necesario
            if ($numero >= $cuestionario->seccion_actual) {
                $cuestionario->seccion_actual = min($numero + 1, $cuestionario->total_secciones);
                $cuestionario->actualizarProgreso();
            }

            DB::commit();

            // Verificar si es la última sección
            $esUltimaSeccion = $numero >= $cuestionario->total_secciones;

            if ($esUltimaSeccion) {
                return redirect()->route('cuestionario.finalizar', ['token' => $token]);
            }

            // Redirigir a siguiente sección
            return redirect()->route('cuestionario.seccion', [
                'token' => $token,
                'numero' => $numero + 1
            ])->with('success', 'Sección guardada correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withErrors(['error' => 'Error al guardar la información. Intente nuevamente.'])
                ->withInput();
        }
    }

    /**
     * Mostrar página de finalización y firma digital
     */
    public function finalizar(string $token)
    {
        $evaluado = EvaluadoOrden::where('token_unico', $token)
            ->where('token_expira_at', '>', now())
            ->firstOrFail();
        $cuestionario = $evaluado->cuestionario;

        // Verificar que completó todas las secciones
        if ($cuestionario->seccion_actual < $cuestionario->total_secciones) {
            return redirect()->route('cuestionario.seccion', [
                'token' => $token,
                'numero' => $cuestionario->seccion_actual
            ]);
        }

        // Generar resumen de secciones
        $secciones = $cuestionario->getSeccionesConfig();
        $iconos = [
            1 => 'user',
            2 => 'users',
            3 => 'briefcase',
            4 => 'dollar-sign',
            5 => 'clipboard-check',
            6 => 'home',
            7 => 'file-alt',
        ];
        
        $resumenSecciones = [];
        foreach ($secciones as $numero => $nombre) {
            $seccionSlug = $this->getSlugSeccion($numero, $cuestionario->tipo_formulario);
            $respuestas = $cuestionario->getRespuestasPorSeccion($seccionSlug);
            $totalCampos = count($respuestas) > 0 ? count($respuestas) : 5; // Estimado
            $camposCompletados = count(array_filter($respuestas, fn($v) => !empty($v)));
            
            $resumenSecciones[$numero] = [
                'nombre' => $nombre,
                'icono' => $iconos[$numero] ?? 'check',
                'completada' => $camposCompletados > 0,
                'campos_completados' => $camposCompletados,
                'total_campos' => $totalCampos,
            ];
        }

        // Obtener datos para mostrar en el resumen
        $datosPersonales = $cuestionario->getRespuestasPorSeccion($this->getSlugSeccion(1, $cuestionario->tipo_formulario));
        $historialLaboral = $cuestionario->getRespuestasPorSeccion($this->getSlugSeccion(3, $cuestionario->tipo_formulario));
        $situacionEconomica = $cuestionario->getRespuestasPorSeccion($this->getSlugSeccion(4, $cuestionario->tipo_formulario));
        
        // Alias para compatibilidad con la vista
        $evaluadoOrden = $evaluado;
        $evaluadoOrden->load('documentos');

        return view('cuestionario.finalizar', compact(
            'evaluado', 
            'evaluadoOrden',
            'cuestionario', 
            'token', 
            'resumenSecciones',
            'datosPersonales',
            'historialLaboral',
            'situacionEconomica'
        ));
    }

    /**
     * Completar cuestionario con firma digital
     */
    public function completar(Request $request, string $token)
    {
        $request->validate([
            'confirmacion_final' => 'required|accepted',
        ], [
            'confirmacion_final.required' => 'Debe confirmar que ha revisado la información.',
            'confirmacion_final.accepted' => 'Debe confirmar que ha revisado la información.',
        ]);

        $evaluado = EvaluadoOrden::where('token_unico', $token)
            ->where('token_expira_at', '>', now())
            ->firstOrFail();
        $cuestionario = $evaluado->cuestionario;

        DB::beginTransaction();
        try {
            // Marcar como completado (firma ya guardada al aceptar términos)
            $cuestionario->completado = true;
            $cuestionario->bloqueado = true;
            $cuestionario->progreso_porcentaje = 100;
            $cuestionario->completado_at = now();
            $cuestionario->ip_completado = $request->ip();
            $cuestionario->save();
            
            \Log::info('Cuestionario guardado', [
                'id' => $cuestionario->id,
                'firma_guardada' => !empty($cuestionario->firma_digital),
                'firma_length' => strlen($cuestionario->firma_digital ?? '')
            ]);

            // Marcar evaluado como completado
            $evaluado->cuestionario_completado = true;
            $evaluado->cuestionario_completado_at = now();
            $evaluado->completado_at = now();
            $evaluado->estado_evaluacion = 'docs_pendientes'; // Formulario recibido, pendiente de revisar
            $evaluado->ip_acceso = $request->ip();
            $evaluado->save();

            DB::commit();

            // Enviar notificación a administradores y usuarios REPRO sobre cuestionario completado
            $this->notificarCuestionarioCompletado($evaluado);

            return redirect()->route('cuestionario.completado', ['token' => $token]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al completar cuestionario: ' . $e->getMessage(), [
                'token' => $token,
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('cuestionario.finalizar', ['token' => $token])
                ->withErrors(['error' => 'Error al completar el cuestionario. Intente nuevamente.']);
        }
    }

    /**
     * Mostrar página de cuestionario completado
     */
    public function completado(string $token)
    {
        $evaluado = EvaluadoOrden::where('token_unico', $token)->firstOrFail();
        
        if (!$evaluado->cuestionario_completado) {
            return redirect()->route('cuestionario.mostrar', ['token' => $token]);
        }

        return view('cuestionario.completado', compact('evaluado'));
    }

    // ========================================
    // Métodos privados auxiliares
    // ========================================

    /**
     * Crear nuevo cuestionario para un evaluado
     */
    private function crearCuestionario(EvaluadoOrden $evaluado): Cuestionario
    {
        $tipoFormulario = $evaluado->tipo_formulario;
        $secciones = $this->getSecciones($tipoFormulario);

        return Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => $tipoFormulario,
            'seccion_actual' => 1,
            'total_secciones' => count($secciones),
        ]);
    }

    /**
     * Obtener configuración de secciones por tipo de formulario
     */
    private function getSecciones(string $tipo): array
    {
        $secciones = [
            'preempleo' => [
                1 => 'Datos Personales',
                2 => 'Información Familiar', 
                3 => 'Historial Laboral',
                4 => 'Situación Económica',
                5 => 'Antecedentes y Referencias',
                6 => 'Firma Digital'
            ],
            'periodica' => [
                1 => 'Actualización de Datos',
                2 => 'Cambios Familiares',
                3 => 'Situación Laboral Actual',
                4 => 'Antecedentes Recientes',
                5 => 'Firma Digital'
            ],
            'especifica' => [
                1 => 'Datos Básicos',
                2 => 'Situación Específica',
                3 => 'Antecedentes Relevantes', 
                4 => 'Firma Digital'
            ],
            'socioeconomico' => [
                1 => 'Datos Personales',
                2 => 'Información Familiar',
                3 => 'Historial Laboral',
                4 => 'Situación Económica Detallada',
                5 => 'Situación Habitacional',
                6 => 'Referencias Comunitarias',
                7 => 'Verificación de Documentos',
                8 => 'Firma Digital'
            ]
        ];
        
        return $secciones[$tipo] ?? [];
    }

    /**
     * Obtener slug de sección para base de datos
     */
    private function getSlugSeccion(int $numero, string $tipo): string
    {
        $slugs = [
            'preempleo' => [
                1 => 'datos_personales',
                2 => 'informacion_familiar',
                3 => 'historial_laboral',
                4 => 'situacion_economica',
                5 => 'antecedentes',
                6 => 'firma_digital'
            ],
            'periodica' => [
                1 => 'actualizacion_datos',
                2 => 'cambios_familiares',
                3 => 'situacion_laboral',
                4 => 'antecedentes_recientes',
                5 => 'firma_digital'
            ],
            'especifica' => [
                1 => 'datos_basicos',
                2 => 'situacion_especifica',
                3 => 'antecedentes_relevantes',
                4 => 'firma_digital'
            ],
            'socioeconomico' => [
                1 => 'datos_personales',
                2 => 'informacion_familiar',
                3 => 'historial_laboral',
                4 => 'situacion_economica_detallada',
                5 => 'situacion_habitacional',
                6 => 'referencias_comunitarias',
                7 => 'verificacion_documentos',
                8 => 'firma_digital'
            ]
        ];

        return $slugs[$tipo][$numero] ?? 'seccion_' . $numero;
    }

    /**
     * Obtener vista para una sección específica
     */
    private function getVistaSeccion(int $numero): string
    {
        $vistas = [
            1 => 'cuestionario.secciones.datos-personales',
            2 => 'cuestionario.secciones.informacion-familiar',
            3 => 'cuestionario.secciones.historial-laboral',
            4 => 'cuestionario.secciones.situacion-economica',
            5 => 'cuestionario.secciones.antecedentes',
        ];

        return $vistas[$numero] ?? 'cuestionario.secciones.generica';
    }

    /**
     * Obtener icono FontAwesome para una sección específica
     */
    private function getIconoSeccion(int $numero): string
    {
        $iconos = [
            1 => 'user',
            2 => 'users',
            3 => 'briefcase',
            4 => 'dollar-sign',
            5 => 'shield-alt',
        ];

        return $iconos[$numero] ?? 'file-alt';
    }

    /**
     * Validar datos según la sección
     */
    private function validarSeccion(Request $request, int $numero): array
    {
        switch ($numero) {
            case 1:
                $formRequest = app(DatosPersonalesRequest::class);
                break;
            case 2:
                $formRequest = app(InformacionFamiliarRequest::class);
                break;
            case 3:
                $formRequest = app(HistorialLaboralRequest::class);
                break;
            case 4:
                $formRequest = app(SituacionEconomicaRequest::class);
                break;
            case 5:
                $formRequest = app(AntecedentesRequest::class);
                break;
            default:
                return $request->all();
        }

        return $request->validate($formRequest->rules(), $formRequest->messages());
    }

    /**
     * Enviar notificación cuando un cuestionario se completa.
     *
     * Solo crea notificación in-app para usuarios REPRO/Admin. El envío de correo
     * se removió a petición del cliente (2026-04-22) para reducir spam de emails;
     * los correos a la empresa se conservan únicamente cuando los resultados están
     * disponibles (ver `OrdenesController::toggleResultadosVisibles`).
     */
    private function notificarCuestionarioCompletado(EvaluadoOrden $evaluado): void
    {
        try {
            $evaluado->loadMissing('orden.empresa');

            // Notificación in-app a usuarios REPRO/admin
            $usuariosNotificar = User::where('role_as', '>=', 2)
                ->where('estado', 1)
                ->get();
            foreach ($usuariosNotificar as $usuario) {
                $usuario->notify(new CuestionarioCompletadoNotification($evaluado));
            }

            Log::info('Notificaciones in-app de cuestionario completado enviadas', [
                'evaluado_id' => $evaluado->id,
                'destinatarios' => $usuariosNotificar->count(),
            ]);

        } catch (\Exception $e) {
            Log::error('Error enviando notificación de cuestionario completado', [
                'evaluado_id' => $evaluado->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Subir un documento desde el cuestionario (ruta pública con token).
     */
    public function subirDocumento(Request $request, string $token)
    {
        $evaluado = EvaluadoOrden::where('token_unico', $token)
            ->where('token_expira_at', '>', now())
            ->firstOrFail();

        if ($evaluado->cuestionario_completado) {
            return back()->with('error', 'El cuestionario ya fue completado. No se pueden subir más documentos.');
        }

        $request->validate([
            'tipo_documento' => ['required', \Illuminate\Validation\Rule::in(array_keys(\App\Models\DocumentoEvaluado::tiposDocumento()))],
            'archivo'        => [
                'required',
                'file',
                'max:10240',
                'mimes:pdf,jpg,jpeg,png,doc,docx',
                'mimetypes:application/pdf,image/jpeg,image/png,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ],
        ]);

        $archivo = $request->file('archivo');
        $ruta = $archivo->store('documentos_evaluados/' . $evaluado->id, 'local');

        \App\Models\DocumentoEvaluado::create([
            'evaluado_orden_id'   => $evaluado->id,
            'tipo_documento'      => $request->tipo_documento,
            'nombre_original'     => $archivo->getClientOriginalName(),
            'ruta_archivo'        => $ruta,
            'mime_type'           => $archivo->getMimeType(),
            'tamano'              => $archivo->getSize(),
            'subido_por_tipo'     => 'evaluado',
            'subido_por_user_id'  => null,
            'estado_verificacion' => 'pendiente',
        ]);

        return back()->with('success', 'Documento subido correctamente.');
    }
}
