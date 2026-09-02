<?php

namespace App\Support;

use App\Models\Cuestionario;
use App\Models\EvaluadoOrden;
use App\Models\EvaluadorNota;

/** E3.2 — Compila y persiste tablas del informe Pre-empleo (editables por evaluador). */
class InformePreempleo
{
    public const SECCION_NOTAS = 'informe_preempleo';

    /** @var array<string, string> */
    public const CLAVES_TABLAS = [
        'personal' => 'Información personal',
        'familiar' => 'Información familiar',
        'academico' => 'Formación académica',
        'estudios_actuales' => 'Estudia actualmente',
        'laboral' => 'Historial laboral',
        'deudas' => 'Deudas',
        'tatuajes' => 'Tatuajes',
        'complementaria' => 'Información complementaria',
    ];

    /** @var array<string, string> */
    private const CLAVES_TABLAS_SOCIO = [
        'referencias_familiares' => 'Referencias familiares',
        'referencias_personales' => 'Referencias personales',
        'referencias_laborales' => 'Referencias laborales',
        'bienes' => 'Bienes y pertenencias',
        'presupuesto' => 'Presupuesto personal',
    ];

    /**
     * @return array<string, string>
     */
    public static function clavesTablas(?string $tipoFormulario = null): array
    {
        if (in_array($tipoFormulario, ['periodica', 'especifica'], true)) {
            $claves = [
                'personal' => 'Información personal',
                'familiar' => 'Información familiar',
                'academico' => 'Formación académica',
                'laboral' => 'Historial laboral',
                'deudas' => 'Deudas',
                'tatuajes' => 'Tatuajes',
            ];
            if ($tipoFormulario === 'periodica') {
                $claves = [
                    'personal' => 'Información personal',
                    'familiar' => 'Información familiar',
                    'academico' => 'Formación académica',
                    'estudios_actuales' => 'Estudia actualmente',
                    'laboral' => 'Historial laboral',
                    'deudas' => 'Deudas',
                    'tatuajes' => 'Tatuajes',
                ];
            }

            return $claves;
        }

        $claves = self::CLAVES_TABLAS;

        if ($tipoFormulario === 'socioeconomico') {
            $claves = array_merge($claves, self::CLAVES_TABLAS_SOCIO);
        }

        return $claves;
    }

    /**
     * @return list<string>
     */
    public static function camposInformeGuardables(?string $tipoFormulario = null): array
    {
        return array_keys(self::clavesTablas($tipoFormulario));
    }

    public static function aplicaATipo(?string $tipoFormulario): bool
    {
        return in_array($tipoFormulario, ['preempleo', 'socioeconomico', 'periodica', 'especifica'], true);
    }

    /** Periódica/específica no llevan hermanos (formulario, tablas REPRO ni Word). */
    public static function incluyeHermanos(?string $tipoFormulario): bool
    {
        return ! in_array($tipoFormulario, ['periodica', 'especifica'], true);
    }

    /**
     * @return array<string, mixed>
     */
    public static function compilarTablas(Cuestionario $cuestionario): array
    {
        $tipo = $cuestionario->tipo_formulario ?? 'preempleo';
        if (in_array($tipo, ['periodica', 'especifica'], true)) {
            return self::compilarTablasPeriodica($cuestionario);
        }

        $tablasS3 = $cuestionario->getTablasPorNumeroSeccion(3);
        $tablasS4 = $cuestionario->getTablasPorNumeroSeccion(4);
        $tablasS5 = $cuestionario->getTablasPorNumeroSeccion(5);
        $respuestasS5 = $cuestionario->obtenerRespuestasSeccion(5);
        $tablas = [
            'personal' => self::compilarPersonal($cuestionario),
            'familiar' => ResumenFamiliar::compilar($cuestionario),
            'academico' => $tablasS3['formacion_academica'] ?? [],
            'estudios_actuales' => $tablasS3['estudios_actuales'] ?? [],
            'laboral' => $tablasS3['empleos'] ?? [],
            'deudas' => $tablasS4['deudas'] ?? [],
            'tatuajes' => $tablasS5['tatuajes'] ?? [],
            'complementaria' => self::compilarComplementaria(
                $respuestasS5,
                $cuestionario->tipo_formulario === 'socioeconomico'
                    ? $cuestionario->obtenerRespuestasSeccion(6)
                    : []
            ),
            'labor_complementaria' => self::compilarLaborComplementaria(
                $cuestionario->obtenerRespuestasSeccion(3)
            ),
        ];

        if ($cuestionario->tipo_formulario === 'socioeconomico') {
            $tablasS6 = $cuestionario->getTablasPorNumeroSeccion(6);
            $tablas['referencias_familiares'] = $tablasS6['referencias_familiares'] ?? [];
            $tablas['referencias_personales'] = $tablasS6['referencias_personales'] ?? [];
            $tablas['referencias_laborales'] = $tablasS6['referencias_laborales'] ?? [];
            $tablas['bienes'] = $tablasS6['bienes'] ?? [];
            $tablas['presupuesto'] = $tablasS6['presupuesto'] ?? [];
            $tablas['domicilio'] = self::compilarDomicilio($cuestionario);
        }

        return $tablas;
    }

    /** @return array<string, mixed> */
    private static function compilarTablasPeriodica(Cuestionario $cuestionario): array
    {
        $tipo = $cuestionario->tipo_formulario ?? 'periodica';
        $tablasS3 = $cuestionario->getTablasPorNumeroSeccion(3);
        $tablasS4 = $cuestionario->getTablasPorNumeroSeccion(4);
        $tablasS5 = $cuestionario->getTablasPorNumeroSeccion(5);
        $respuestasS3 = $cuestionario->obtenerRespuestasSeccion(3);
        $academico = $tablasS3['formacion_academica'] ?? [];
        $ultimoNivel = trim((string) ($respuestasS3['ultimo_nivel_academico'] ?? ''));
        if ($academico === [] && $ultimoNivel !== '' && $ultimoNivel !== 'ninguno') {
            $academico = [['nivel' => $ultimoNivel]];
        }

        $evaluado = $cuestionario->evaluadoOrden ?? EvaluadoOrden::query()->find($cuestionario->evaluado_orden_id);

        $familiar = ResumenFamiliar::compilar($cuestionario);
        unset($familiar['hermanos']);

        return [
            'personal' => self::compilarPersonal($cuestionario),
            'familiar' => $familiar,
            'academico' => $academico,
            'estudios_actuales' => $tablasS3['estudios_actuales'] ?? [],
            'laboral' => $evaluado instanceof EvaluadoOrden
                ? InformeDatos::laboralPeriodica($tablasS3, $evaluado)
                : [],
            'deudas' => $tablasS4['deudas'] ?? [],
            'tatuajes' => $tablasS5['tatuajes'] ?? [],
            'complementaria' => [],
            'labor_complementaria' => InformeDatos::laborComplementariaPeriodica($respuestasS3, $tipo),
        ];
    }

    /** @return list<array{pregunta: string, respuesta: string}> */
    private static function compilarPersonal(Cuestionario $cuestionario): array
    {
        $respuestas = $cuestionario->obtenerRespuestasSeccion(1);
        $campos = [
            ['key' => 'nombres_completos', 'label' => 'Nombres completos'],
            ['key' => 'apellidos_completos', 'label' => 'Apellidos completos'],
            ['key' => 'tipo_identificacion', 'label' => 'Tipo de identificación'],
            ['key' => 'dpi', 'label' => 'Número de identificación'],
            ['key' => 'fecha_nacimiento', 'label' => 'Fecha de nacimiento'],
            ['key' => 'estado_civil', 'label' => 'Estado civil'],
            ['key' => 'nacionalidad', 'label' => 'Nacionalidad'],
            ['key' => 'departamento_nacimiento', 'label' => 'Departamento de nacimiento'],
            ['key' => 'municipio_nacimiento', 'label' => 'Municipio de nacimiento'],
            ['key' => 'direccion_residencia', 'label' => 'Dirección de residencia'],
            ['key' => 'departamento', 'label' => 'Departamento de residencia'],
            ['key' => 'municipio', 'label' => 'Municipio de residencia'],
            ['key' => 'telefono_personal', 'label' => 'Teléfono personal'],
            ['key' => 'telefono_alternativo', 'label' => 'Teléfono alternativo'],
            ['key' => 'email_personal', 'label' => 'Correo electrónico'],
            ['key' => 'igss', 'label' => 'IGSS'],
            ['key' => 'nit', 'label' => 'NIT'],
            ['key' => 'licencia_conducir', 'label' => 'Licencia de conducir'],
            ['key' => 'empresa_solicitante', 'label' => 'Empresa solicitante'],
            ['key' => 'puesto_evaluar', 'label' => 'Puesto a evaluar'],
        ];

        $filas = [];
        foreach ($campos as $campo) {
            $valor = trim((string) ($respuestas[$campo['key']] ?? ''));
            if ($valor === '') {
                continue;
            }
            $filas[] = [
                'pregunta' => $campo['label'],
                'respuesta' => $valor,
            ];
        }

        return $filas;
    }

    /**
     * @param  array<string, mixed>  $respuestas
     * @param  array<string, mixed>  $respuestasSocio
     * @return list<array{pregunta: string, respuesta: string}>
     */
    private static function compilarComplementaria(array $respuestas, array $respuestasSocio = []): array
    {
        $filas = [];

        foreach (InformacionComplementaria::PREGUNTAS as $pregunta) {
            $valor = trim((string) ($respuestas[$pregunta['key']] ?? ''));
            if ($valor === '') {
                continue;
            }

            $filas[] = [
                'pregunta' => $pregunta['label'],
                'respuesta' => $valor,
            ];
        }

        $haLaborado = trim((string) ($respuestasSocio['comp_ha_laborado_empresa'] ?? $respuestas['comp_ha_laborado_empresa'] ?? ''));
        if ($haLaborado !== '') {
            $filas[] = [
                'pregunta' => SocioeconomicoComplementariaCampos::LABEL_HA_LABORADO_EMPRESA,
                'respuesta' => $haLaborado,
            ];
        }

        return $filas;
    }

    /**
     * @return array<string, string>
     */
    private static function compilarDomicilio(Cuestionario $cuestionario): array
    {
        $s1 = $cuestionario->obtenerRespuestasSeccion(1);
        $s6 = $cuestionario->obtenerRespuestasSeccion(6);
        $direccion = trim(implode(', ', array_filter([
            trim((string) ($s1['direccion_residencia'] ?? '')),
            trim((string) ($s1['municipio'] ?? '')),
            trim((string) ($s1['departamento'] ?? '')),
        ], static fn (string $parte): bool => $parte !== '')));

        $tipoClave = (string) ($s6['viv_tipo_vivienda'] ?? '');
        $tipo = SocioeconomicoComplementariaCampos::tiposVivienda()[$tipoClave]
            ?? trim((string) ($s6['viv_tipo_vivienda_detalle'] ?? $tipoClave));

        $zona = match ($s6['viv_zona_riesgo'] ?? '') {
            'si' => trim('Sí'.(trim((string) ($s6['viv_detalle_zona_riesgo'] ?? '')) !== '' ? ': '.$s6['viv_detalle_zona_riesgo'] : '')),
            'no' => 'No',
            default => '',
        };

        $renta = trim((string) ($s6['viv_monto_alquiler'] ?? ''));

        return [
            'direccion_verificada' => $direccion,
            'direccion_reportada' => $direccion,
            'tiempo_residencia' => trim((string) ($s6['viv_tiempo_residencia'] ?? '')),
            'tipo_vivienda' => $tipo,
            'pago_renta' => $renta,
            'propietario' => trim((string) ($s6['viv_propietario'] ?? '')),
            'habitantes' => trim((string) ($s6['viv_habitantes_detalle'] ?? '')),
            'refs_ubicacion' => trim((string) ($s6['viv_refs_ubicacion'] ?? '')),
            'zona_roja' => $zona,
            'direcciones_anteriores' => trim((string) ($s6['viv_direcciones_anteriores'] ?? '')),
        ];
    }

    /**
     * @param  array<string, mixed>  $respuestas
     * @return list<array{pregunta: string, respuesta: string}>
     */
    private static function compilarLaborComplementaria(array $respuestas): array
    {
        $filas = [];

        foreach (HistorialLaboralIntegridad::PREGUNTAS as $pregunta) {
            $valor = trim((string) ($respuestas[$pregunta['key']] ?? ''));
            if ($valor === '') {
                continue;
            }

            $filas[] = [
                'pregunta' => $pregunta['label'],
                'respuesta' => $valor,
            ];
        }

        $experiencia = trim((string) ($respuestas['experiencia_previa'] ?? ''));
        if ($experiencia !== '') {
            array_unshift($filas, [
                'pregunta' => HistorialLaboralIntegridad::LABEL_EXPERIENCIA_PREVIA,
                'respuesta' => $experiencia,
            ]);
        }

        $observaciones = trim((string) ($respuestas['observaciones_laborales'] ?? ''));
        if ($observaciones !== '') {
            $filas[] = [
                'pregunta' => HistorialLaboralIntegridad::LABEL_OBSERVACIONES_LABORALES,
                'respuesta' => $observaciones,
            ];
        }

        return $filas;
    }

    /**
     * @return array<string, mixed>
     */
    public static function overrides(int $evaluadoOrdenId): array
    {
        $registros = EvaluadorNota::query()
            ->where('evaluado_orden_id', $evaluadoOrdenId)
            ->where('seccion', self::SECCION_NOTAS)
            ->whereIn('campo', self::camposInformeGuardables(
                EvaluadoOrden::query()->find($evaluadoOrdenId)?->tipoFormularioCuestionario()
            ))
            ->get();

        $overrides = [];

        foreach ($registros as $nota) {
            if ($nota->contenido === null || $nota->contenido === '') {
                continue;
            }

            $decoded = json_decode($nota->contenido, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $overrides[$nota->campo] = $decoded;
            }
        }

        return $overrides;
    }

    /**
     * @return list<string>
     */
    public static function clavesConOverride(int $evaluadoOrdenId): array
    {
        return array_keys(self::overrides($evaluadoOrdenId));
    }

    /**
     * @return array<string, mixed>
     */
    public static function tablasParaAdmin(Cuestionario $cuestionario): array
    {
        $tablas = self::compilarTablas($cuestionario);
        $overrides = self::overrides($cuestionario->evaluado_orden_id);

        foreach ($overrides as $clave => $datos) {
            if (! array_key_exists($clave, $tablas) && ! in_array($clave, self::camposInformeGuardables($cuestionario->tipo_formulario), true)) {
                continue;
            }
            // P-S1: un override vacío (o solo ----- / sin empresa) no debe tapar los empleos del formulario.
            if ($clave === 'laboral' && self::filasLaboralOverrideVacias($datos) && ! self::filasLaboralOverrideVacias($tablas['laboral'] ?? [])) {
                continue;
            }
            $tablas[$clave] = $datos;
        }

        if (! self::incluyeHermanos($cuestionario->tipo_formulario)
            && isset($tablas['familiar']) && is_array($tablas['familiar'])) {
            unset($tablas['familiar']['hermanos']);
        }

        return $tablas;
    }

    /**
     * @param  array<string, mixed>  $input
     * @param  array<string, mixed>  $restaurar
     */
    public static function guardarDesdeRequest(int $evaluadoOrdenId, array $input, array $restaurar, ?int $userId): void
    {
        $tipo = EvaluadoOrden::query()->find($evaluadoOrdenId)?->tipoFormularioCuestionario() ?? 'preempleo';

        foreach (self::clavesTablas($tipo) as $clave => $titulo) {
            if (! empty($restaurar[$clave])) {
                EvaluadorNota::query()
                    ->where('evaluado_orden_id', $evaluadoOrdenId)
                    ->where('seccion', self::SECCION_NOTAS)
                    ->where('campo', $clave)
                    ->delete();

                continue;
            }

            if (! array_key_exists($clave, $input)) {
                continue;
            }

            $normalizado = self::normalizarTabla($clave, $input[$clave], $tipo);
            $json = json_encode($normalizado, JSON_UNESCAPED_UNICODE);

            EvaluadorNota::guardarNota(
                $evaluadoOrdenId,
                self::SECCION_NOTAS,
                $clave,
                $json !== false ? $json : null,
                $userId
            );
        }
    }

    /**
     * @param  mixed  $datos
     * @return mixed
     */
    private static function normalizarTabla(string $clave, mixed $datos, string $tipoFormulario = 'preempleo')
    {
        if (! is_array($datos)) {
            return $datos;
        }

        if ($clave === 'familiar') {
            return self::normalizarFamiliar($datos, $tipoFormulario);
        }

        if (in_array($clave, ['complementaria', 'personal', 'labor_complementaria'], true)) {
            return self::normalizarComplementaria($datos);
        }

        $columnas = self::columnasParaClaveInforme($clave, $tipoFormulario);
        if ($columnas !== null) {
            return TablaDinamica::normalizarFilas($datos, $columnas);
        }

        return array_values(array_filter($datos, fn ($fila) => is_array($fila)));
    }

    /** @return list<array<string, mixed>>|null */
    private static function columnasParaClaveInforme(string $clave, string $tipoFormulario): ?array
    {
        return match ($clave) {
            'academico' => TablaDinamica::columnasFormacionAcademica(),
            'laboral' => match (true) {
                in_array($tipoFormulario, ['periodica', 'especifica'], true) => TablaDinamica::columnasLaboralInformePeriodica(),
                in_array($tipoFormulario, ['preempleo', 'socioeconomico'], true) => TablaDinamica::columnasEmpleosPreempleo(),
                default => TablaDinamica::columnasEmpleos(),
            },
            'deudas' => TablaDinamica::columnasDeudas(),
            'tatuajes' => TablaDinamica::columnasTatuajes(),
            'estudios_actuales' => TablaDinamica::columnasEstudiosActuales(),
            'referencias_familiares' => TablaDinamica::columnasReferenciasFamiliares(),
            'referencias_personales' => TablaDinamica::columnasReferenciasPersonales(),
            'referencias_laborales' => TablaDinamica::columnasReferenciasLaborales(),
            'bienes' => TablaDinamica::columnasBienes(),
            'presupuesto' => TablaDinamica::columnasPresupuesto(),
            default => null,
        };
    }

    /** @param  array<string, mixed>  $datos */
    private static function normalizarFamiliar(array $datos, string $tipoFormulario = 'preempleo'): array
    {
        if (isset($datos['convive_con']) && is_string($datos['convive_con'])) {
            $datos['convive_con'] = array_values(array_filter(array_map('trim', explode(',', $datos['convive_con']))));
        }

        foreach (['hijos', 'hermanos'] as $tabla) {
            if (isset($datos[$tabla]) && is_array($datos[$tabla])) {
                $datos[$tabla] = array_values($datos[$tabla]);
            }
        }

        if (! self::incluyeHermanos($tipoFormulario)) {
            unset($datos['hermanos']);
        }

        if (isset($datos['pareja']) && is_array($datos['pareja'])) {
            $datos['pareja']['tiene'] = self::siNoABooleano($datos['pareja']['tiene'] ?? false);
        }

        if (isset($datos['expareja']) && is_array($datos['expareja'])) {
            $datos['expareja']['aplica'] = self::siNoABooleano($datos['expareja']['aplica'] ?? false);
        }

        return $datos;
    }

    /** @param  mixed  $datos */
    private static function filasOverrideVacias(mixed $datos): bool
    {
        if (! is_array($datos) || $datos === []) {
            return true;
        }

        foreach ($datos as $fila) {
            if (! is_array($fila)) {
                continue;
            }
            foreach ($fila as $valor) {
                if (is_array($valor)) {
                    if (! self::filasOverrideVacias($valor)) {
                        return false;
                    }

                    continue;
                }
                if (self::valorOverrideVacio((string) $valor)) {
                    continue;
                }

                return false;
            }
        }

        return true;
    }

    /** @param  mixed  $datos */
    private static function filasLaboralOverrideVacias(mixed $datos): bool
    {
        if (self::filasOverrideVacias($datos)) {
            return true;
        }

        if (! is_array($datos)) {
            return true;
        }

        foreach ($datos as $fila) {
            if (! is_array($fila)) {
                continue;
            }

            $empresa = (string) ($fila['empresa'] ?? '');
            $puesto = (string) ($fila['puesto'] ?? '');
            if (! self::valorOverrideVacio($empresa) || ! self::valorOverrideVacio($puesto)) {
                return false;
            }
        }

        return true;
    }

    private static function valorOverrideVacio(string $valor): bool
    {
        $texto = trim($valor);
        if ($texto === '') {
            return true;
        }

        $compacto = preg_replace('/[\s.:·\-–—]/u', '', $texto) ?? $texto;

        return $compacto === '' || preg_match('/^x+$/i', $compacto) === 1;
    }

    private static function siNoABooleano(mixed $valor): bool
    {
        if (is_bool($valor)) {
            return $valor;
        }

        return in_array(mb_strtolower(trim((string) $valor)), ['si', 'sí', '1', 'true'], true);
    }

    /** @param  array<int, mixed>  $datos */
    private static function normalizarComplementaria(array $datos): array
    {
        $filas = [];

        foreach ($datos as $fila) {
            if (! is_array($fila)) {
                continue;
            }

            $pregunta = trim((string) ($fila['pregunta'] ?? ''));
            $respuesta = trim((string) ($fila['respuesta'] ?? ''));

            if ($pregunta === '' && $respuesta === '') {
                continue;
            }

            $filas[] = [
                'pregunta' => $pregunta,
                'respuesta' => $respuesta,
            ];
        }

        return $filas;
    }
}
