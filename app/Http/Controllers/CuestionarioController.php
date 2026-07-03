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
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Support\CuestionarioAutosave;
use App\Support\CuestionarioFotoCandidato;
use App\Support\CuestionarioPrecarga;
use App\Support\DatosPersonalesCampos;
use App\Support\CamposInternosPreempleo;
use App\Support\SaludHabitosCampos;
use App\Support\TablaDinamica;
use App\Support\GuatemalaCatalogo;
use App\Support\HistorialAcademico;
use Illuminate\Support\Facades\Storage;

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
            $resultado = $this->evaluadoConTokenVigente($token);
            if ($resultado instanceof Response) {
                return $resultado;
            }

            $evaluado = $resultado->loadMissing(['orden.empresa']);

            // Verificar si ya completó el cuestionario — mostrar estado del proceso
            if ($evaluado->cuestionario_completado) {
                return redirect()->route('cuestionario.estado', ['token' => $token]);
            }

            // Registrar acceso (comentado temporalmente para debug)
            // $evaluado->registrarAcceso();

            // Verificar si ya existe un cuestionario iniciado
            $cuestionario = $evaluado->cuestionario;

            // Redirigir a verificación de identidad
            return view('cuestionario.verificar-identidad', compact('evaluado', 'token'));

        } catch (\Exception $e) {
            Log::error('Error en cuestionario.mostrar', [
                'token_prefijo' => substr($token, 0, 8),
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

        CuestionarioPrecarga::asegurarSnapshot($cuestionario, $evaluado);
        
        return $this->redirigirTrasVerificar($cuestionario, $token);
    }

    /**
     * Pantalla de instrucciones obligatoria (E1.6).
     */
    public function instrucciones(string $token)
    {
        $evaluado = EvaluadoOrden::where('token_unico', $token)
            ->with(['orden.empresa'])
            ->firstOrFail();

        if ($evaluado->cuestionario_completado) {
            return view('cuestionario.completado', compact('evaluado'));
        }

        $cuestionario = $evaluado->cuestionario;

        if (!$cuestionario) {
            return redirect()->route('cuestionario.mostrar', ['token' => $token]);
        }

        if ($cuestionario->instrucciones_leidas_at) {
            return $this->redirigirTrasVerificar($cuestionario, $token);
        }

        return view('cuestionario.instrucciones', compact('evaluado', 'cuestionario', 'token'));
    }

    /**
     * Registrar aceptación de instrucciones.
     */
    public function aceptarInstrucciones(Request $request, string $token)
    {
        $request->validate([
            'acepta_instrucciones' => 'required|accepted',
        ], [
            'acepta_instrucciones.required' => 'Debe confirmar que ha leído las instrucciones.',
            'acepta_instrucciones.accepted' => 'Debe confirmar que ha leído las instrucciones.',
        ]);

        $evaluado = EvaluadoOrden::where('token_unico', $token)
            ->where('token_expira_at', '>', now())
            ->firstOrFail();

        $cuestionario = $evaluado->cuestionario;

        if (!$cuestionario) {
            return back()->with('error', 'Cuestionario no encontrado.');
        }

        $cuestionario->update([
            'instrucciones_leidas_at' => now(),
            'ip_instrucciones'        => $request->ip(),
        ]);

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

        if ($cuestionario && !$cuestionario->instrucciones_leidas_at) {
            return redirect()->route('cuestionario.instrucciones', ['token' => $token]);
        }

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

        if (!$cuestionario->instrucciones_leidas_at) {
            return redirect()->route('cuestionario.instrucciones', ['token' => $token]);
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
            ->with(['orden.empresa', 'orden.sede', 'sede'])
            ->firstOrFail();
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

        $precarga = CuestionarioPrecarga::asegurarSnapshot($cuestionario, $evaluado);

        if ($redirect = $this->redirigirSiFlujoIncompleto($cuestionario, $token, 'seccion')) {
            return $redirect;
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
        $tablasExistentes = $cuestionario->getTablasPorSeccion($seccionSlug);

        // Datos adicionales para la vista
        $numeroSeccion = $numero;
        $totalSecciones = count($secciones);
        $tituloSeccion = $nombreSeccion;
        $nombresSecciones = $secciones;
        $iconoSeccion = $this->getIconoSeccion($numero);

        // Variables adicionales para el layout
        $currentSection = $numero;
        $totalSections = count($secciones);
        $catalogoGt = GuatemalaCatalogo::paraSelectCliente();
        $fotoCandidatoUrl = null;
        if ($numero === 1) {
            $fotoPath = CuestionarioFotoCandidato::obtenerRuta($cuestionario->id, $seccionSlug);
            if ($fotoPath && Storage::disk('local')->exists($fotoPath)) {
                $fotoCandidatoUrl = route('cuestionario.foto-candidato', ['token' => $token]);
            }
        }

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
            'iconoSeccion',
            'catalogoGt',
            'fotoCandidatoUrl',
            'precarga',
            'tablasExistentes'
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

        if (! $cuestionario) {
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
                'bloqueado' => false,
            ]);
        }

        if ($redirect = $this->redirigirSiFlujoIncompleto($cuestionario, $token, 'seccion')) {
            return $redirect;
        }

        $evaluado->loadMissing(['orden.empresa', 'orden.sede', 'sede']);
        $precargaSnapshot = CuestionarioPrecarga::asegurarSnapshot($cuestionario, $evaluado);

        $accion = $request->input('action', 'siguiente');
        $esParcial = $accion === 'borrador';

        $datosValidados = $esParcial
            ? CuestionarioAutosave::validarParcial($request, $numero, $cuestionario->tipo_formulario)
            : $this->validarSeccion($request, $numero);

        DB::beginTransaction();
        try {
            $this->persistirDatosSeccion($request, $cuestionario, $evaluado, $numero, $datosValidados, $precargaSnapshot);

            if (! $esParcial && $numero >= $cuestionario->seccion_actual) {
                $cuestionario->seccion_actual = min($numero + 1, $cuestionario->total_secciones);
                $cuestionario->actualizarProgreso();
            }

            DB::commit();

            if ($accion === 'borrador') {
                return redirect()->route('cuestionario.seccion', [
                    'token' => $token,
                    'numero' => $numero,
                ])->with('success', 'Borrador guardado correctamente.');
            }

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
     * Autosave silencioso de la sección actual (sin validación completa ni avance).
     */
    public function autosaveSeccion(Request $request, string $token, int $numero)
    {
        $evaluado = EvaluadoOrden::where('token_unico', $token)
            ->where('token_expira_at', '>', now())
            ->firstOrFail();
        $cuestionario = $evaluado->cuestionario;

        if (! $cuestionario) {
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
                'bloqueado' => false,
            ]);
        }

        if ($redirect = $this->redirigirSiFlujoIncompleto($cuestionario, $token, 'seccion')) {
            return response()->json(['error' => 'Flujo incompleto'], 403);
        }

        if ($cuestionario->completado || $cuestionario->bloqueado) {
            return response()->json(['error' => 'Cuestionario no editable'], 403);
        }

        $evaluado->loadMissing(['orden.empresa', 'orden.sede', 'sede']);
        $precargaSnapshot = CuestionarioPrecarga::asegurarSnapshot($cuestionario, $evaluado);

        try {
            $datosValidados = CuestionarioAutosave::validarParcial(
                $request,
                $numero,
                $cuestionario->tipo_formulario
            );

            DB::beginTransaction();
            $this->persistirDatosSeccion(
                $request,
                $cuestionario,
                $evaluado,
                $numero,
                self::filtrarVaciosAutosave($datosValidados),
                $precargaSnapshot
            );
            DB::commit();

            return response()->json([
                'success' => true,
                'saved_at' => now()->toIso8601String(),
                'seccion' => $numero,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Algunos campos tienen un formato inválido.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::warning('Autosave cuestionario falló', [
                'token' => $token,
                'numero' => $numero,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'No se pudo guardar automáticamente.',
            ], 500);
        }
    }

    /**
     * No sobrescribir respuestas con valores vacíos en autosave (borrador parcial).
     *
     * @param  array<string, mixed>  $datos
     * @return array<string, mixed>
     */
    private static function filtrarVaciosAutosave(array $datos): array
    {
        return array_filter(
            $datos,
            fn ($valor) => ! ($valor === null || $valor === '' || $valor === [])
        );
    }

    /**
     * Persiste respuestas y tablas de una sección (compartido por guardar y autosave).
     *
     * @param  array<string, mixed>  $datosValidados
     */
    private function persistirDatosSeccion(
        Request $request,
        Cuestionario $cuestionario,
        EvaluadoOrden $evaluado,
        int $numero,
        array $datosValidados,
        ?array $precargaSnapshot
    ): void {
        $seccionSlug = $this->getSlugSeccion($numero, $cuestionario->tipo_formulario);

        if ($numero === 1) {
            unset($datosValidados['foto_candidato'], $datosValidados['foto_candidato_existente']);

            if ($request->hasFile('foto_candidato')) {
                CuestionarioFotoCandidato::guardar(
                    $request->file('foto_candidato'),
                    $cuestionario->id,
                    $seccionSlug
                );
            }

            $fotoPath = CuestionarioFotoCandidato::obtenerRuta($cuestionario->id, $seccionSlug);
            if ($fotoPath) {
                $datosValidados['foto_candidato'] = $fotoPath;
            }

            $edad = DatosPersonalesCampos::calcularEdad($datosValidados['fecha_nacimiento'] ?? null);
            if ($edad !== null) {
                $datosValidados['edad'] = (string) $edad;
            }
        }

        $tablas = TablaDinamica::extraerTablas($datosValidados, $numero, $cuestionario->tipo_formulario);

        if ($numero === 2) {
            if (($datosValidados['tiene_hijos'] ?? '') === 'no') {
                $tablas['hijos'] = [];
            }
            if (($datosValidados['tiene_hermanos'] ?? '') === 'no') {
                $tablas['hermanos'] = [];
            }
        }

        if ($numero === 3) {
            if (($datosValidados['experiencia_previa'] ?? '') === 'no') {
                $tablas['empleos'] = [];
            }
            if (($datosValidados['ultimo_nivel_academico'] ?? '') === 'ninguno') {
                $tablas['formacion_academica'] = [];
            } elseif (isset($tablas['formacion_academica'])) {
                $tablas['formacion_academica'] = HistorialAcademico::filasParaAlmacenamiento(
                    $datosValidados['ultimo_nivel_academico'] ?? null,
                    $tablas['formacion_academica']
                );
            }
        }

        if ($numero === 4 && ($datosValidados['tiene_deudas'] ?? '') === 'no') {
            $tablas['deudas'] = [];
        }

        if ($numero === 5) {
            if (($datosValidados['tiene_tatuajes'] ?? '') === 'no') {
                $tablas['tatuajes'] = [];
            }
            if (($datosValidados['tiene_perforaciones'] ?? '') === 'no') {
                $tablas['perforaciones'] = [];
            }

            if (isset($datosValidados['sustancias_usadas']) && is_array($datosValidados['sustancias_usadas'])) {
                $datosValidados['sustancias_usadas'] = SaludHabitosCampos::sustanciasParaAlmacenar($datosValidados['sustancias_usadas']);
            }
        }

        foreach ($tablas as $campoTabla => $filas) {
            CuestionarioRespuesta::guardarTabla(
                $cuestionario->id,
                $seccionSlug,
                $campoTabla,
                $filas
            );
        }

        CuestionarioRespuesta::guardarRespuestas(
            $cuestionario->id,
            $seccionSlug,
            $datosValidados,
            $precargaSnapshot
        );

        foreach ($datosValidados as $campo => $valor) {
            if (! CamposInternosPreempleo::esInterno($campo)) {
                continue;
            }

            CuestionarioRespuesta::where('cuestionario_id', $cuestionario->id)
                ->where('seccion', $seccionSlug)
                ->where('campo', $campo)
                ->update(['metadata' => ['tipo_logico' => 'interno', 'informe' => false]]);
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
            // Fase 18: el formulario completado se registra en estado_formulario
            $evaluado->estado_formulario = 'formulario_completado_y_recibido';
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

    /**
     * Vista de estado del proceso — timeline simplificado para el candidato (Fase 18, Semana 3).
     * Accesible en cualquier momento con el mismo token. No requiere que el formulario esté completo.
     */
    public function estadoCandidato(string $token)
    {
        $evaluado = EvaluadoOrden::where('token_unico', $token)
            ->with(['orden.empresa'])
            ->firstOrFail();

        // Calcular el paso activo (1-4) según los estados actuales
        $pasoActivo = 1;

        if ($evaluado->estado_formulario === 'formulario_completado_y_recibido') {
            $pasoActivo = 2;
        }

        if (in_array($evaluado->estado_programacion, ['programado', 'reprogramado', 'proceso_realizado'])) {
            $pasoActivo = 3;
        }

        if ($evaluado->estado_evaluacion === 'informe_final_enviado') {
            $pasoActivo = 4;
        }

        // Estados que indican proceso cancelado o detenido
        $cancelado = in_array($evaluado->estado_evaluacion, ['cancelado', 'desistio'])
            || $evaluado->estado_programacion === 'cancelado';

        return view('cuestionario.estado', compact('evaluado', 'pasoActivo', 'cancelado', 'token'));
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
        $formRequestClass = match ($numero) {
            1 => DatosPersonalesRequest::class,
            2 => InformacionFamiliarRequest::class,
            3 => HistorialLaboralRequest::class,
            4 => SituacionEconomicaRequest::class,
            5 => AntecedentesRequest::class,
            default => null,
        };

        if ($formRequestClass === null) {
            return $request->all();
        }

        /** @var \Illuminate\Foundation\Http\FormRequest $formRequest */
        $formRequest = $formRequestClass::createFrom($request);
        $formRequest->setContainer(app());
        $formRequest->setRedirector(app('redirect'));
        $formRequest->validateResolved();

        return $formRequest->validated();
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
            $usuariosRepro = User::where('role_as', '>=', 2)
                ->where('estado', 1)
                ->get();
            foreach ($usuariosRepro as $usuario) {
                $usuario->notify(new CuestionarioCompletadoNotification($evaluado));
            }

            // Notificación in-app a usuarios de la empresa (Fase 18 — Prioridad 3)
            $empresaId = $evaluado->orden->empresa_id ?? null;
            if ($empresaId) {
                $usuariosEmpresa = User::where('empresa_id', $empresaId)
                    ->where('role_as', 1)
                    ->where('estado', 1)
                    ->get();
                foreach ($usuariosEmpresa as $usuario) {
                    $usuario->notify(new CuestionarioCompletadoNotification($evaluado));
                }
            }

            Log::info('Notificaciones in-app de cuestionario completado enviadas', [
                'evaluado_id'   => $evaluado->id,
                'repro'         => $usuariosRepro->count(),
                'empresa'       => isset($usuariosEmpresa) ? $usuariosEmpresa->count() : 0,
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

    /**
     * Servir foto del candidato (acceso por token vigente).
     */
    public function fotoCandidato(string $token)
    {
        $evaluado = EvaluadoOrden::where('token_unico', $token)
            ->where('token_expira_at', '>', now())
            ->firstOrFail();

        $cuestionario = $evaluado->cuestionario;
        if (! $cuestionario) {
            abort(404);
        }

        $seccionSlug = $this->getSlugSeccion(1, $cuestionario->tipo_formulario);
        $path = CuestionarioFotoCandidato::obtenerRuta($cuestionario->id, $seccionSlug);

        if (! $path || ! Storage::disk('local')->exists($path)) {
            abort(404);
        }

        return Storage::disk('local')->response($path);
    }

    /**
     * Resuelve evaluado por token exigiendo vigencia, o responde con vista de enlace inválido.
     */
    private function evaluadoConTokenVigente(string $token): EvaluadoOrden|Response
    {
        $evaluado = EvaluadoOrden::where('token_unico', $token)->first();

        if (!$evaluado) {
            return $this->respuestaEnlaceInvalido($token, 'no_encontrado');
        }

        if (!$evaluado->token_expira_at || $evaluado->token_expira_at->lte(now())) {
            return $this->respuestaEnlaceInvalido($token, 'expirado', $evaluado);
        }

        return $evaluado;
    }

    /**
     * Vista y log cuando el enlace del cuestionario no puede usarse.
     */
    private function respuestaEnlaceInvalido(string $token, string $motivo, ?EvaluadoOrden $evaluado = null): Response
    {
        Log::warning('Acceso a cuestionario rechazado', [
            'motivo' => $motivo,
            'token_prefijo' => substr($token, 0, 8),
            'token_longitud' => strlen($token),
            'evaluado_id' => $evaluado?->id,
            'token_expira_at' => $evaluado?->token_expira_at?->toIso8601String(),
            'estado_formulario' => $evaluado?->estado_formulario,
        ]);

        [$titulo, $mensaje, $detalle] = match ($motivo) {
            'expirado' => [
                'Enlace expirado',
                'El enlace para completar su formulario ya no está vigente.',
                'Solicite a la empresa o a REPRO que le reenvíen un enlace nuevo. Si recibió el enlace por correo hace varios días, es posible que se haya generado uno más reciente.',
            ],
            default => [
                'Enlace no válido',
                'No pudimos encontrar un formulario asociado a este enlace.',
                'Verifique que copió la dirección completa (debe comenzar con reproappv2.szystems.com/cuestionario/…). Si el enlace vino por correo o WhatsApp, pida uno nuevo desde la orden de evaluación.',
            ],
        };

        return response()->view('cuestionario.enlace-invalido', compact('titulo', 'mensaje', 'detalle', 'motivo'), 404);
    }

    /**
     * Tras verificar DPI: instrucciones → términos → sección actual.
     */
    private function redirigirTrasVerificar(Cuestionario $cuestionario, string $token)
    {
        if (!$cuestionario->instrucciones_leidas_at) {
            return redirect()->route('cuestionario.instrucciones', ['token' => $token]);
        }

        if (!$cuestionario->acepta_terminos) {
            return redirect()->route('cuestionario.terminos', ['token' => $token]);
        }

        return redirect()->route('cuestionario.seccion', [
            'token' => $token,
            'numero' => $cuestionario->seccion_actual,
        ]);
    }

    /**
     * Bloquea acceso a términos o secciones si faltan pasos previos del flujo.
     */
    private function redirigirSiFlujoIncompleto(Cuestionario $cuestionario, string $token, string $pasoRequerido)
    {
        $pasos = ['instrucciones', 'terminos', 'seccion'];
        $indiceRequerido = array_search($pasoRequerido, $pasos, true);

        if ($indiceRequerido === false) {
            return null;
        }

        if ($indiceRequerido >= 1 && !$cuestionario->instrucciones_leidas_at) {
            return redirect()->route('cuestionario.instrucciones', ['token' => $token]);
        }

        if ($indiceRequerido >= 2 && !$cuestionario->acepta_terminos) {
            return redirect()->route('cuestionario.terminos', ['token' => $token]);
        }

        return null;
    }
}
