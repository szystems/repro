<?php

namespace App\Support;

use App\Models\Cuestionario;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use Carbon\Carbon;

/** Fuente única de datos del informe Word (formulario + overrides REPRO). Sprint G5. */
class InformeDatos
{
    /** @var array<string, string> etiqueta personal → clave encabezado Word */
    private const MAPEO_PERSONAL_ENCABEZADO = [
        'Nombres completos' => 'nombres',
        'Apellidos completos' => 'apellidos',
        'Número de identificación' => 'dpi',
        'Fecha de nacimiento' => 'fecha_nacimiento',
        'Estado civil' => 'estado_civil',
        'Nacionalidad' => 'nacionalidad',
        'Municipio de nacimiento' => 'lugar_nacimiento',
        'Dirección de residencia' => 'direccion',
        'Teléfono personal' => 'telefono',
        'Teléfono alternativo' => 'telefono_emergencia',
        'Correo electrónico' => 'correo',
        'IGSS' => 'igss',
        'NIT' => 'nit',
        'Licencia de conducir' => 'licencia',
        'Puesto a evaluar' => 'puesto',
    ];

    /**
     * @return array{
     *   encabezado: array<string, string>,
     *   tablas: array<string, mixed>,
     *   bloques_word: array<string, string>,
     *   tatuajes: list<array<string, mixed>>
     * }
     */
    public static function paraEvaluado(EvaluadoOrden $evaluado, ?Orden $orden = null): array
    {
        $evaluado->loadMissing(['cuestionario', 'orden.empresa', 'sede']);

        $orden ??= $evaluado->orden;
        $tablas = self::tablas($evaluado);
        $encabezado = $orden !== null
            ? self::encabezado($orden, $evaluado, $tablas)
            : [];

        $tatuajes = is_array($tablas['tatuajes'] ?? null) ? $tablas['tatuajes'] : [];

        return [
            'encabezado' => $encabezado,
            'tablas' => $tablas,
            'bloques_word' => InformeWordBloquesEvaluador::mapa($evaluado->id),
            'tatuajes' => $tatuajes,
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function encabezado(Orden $orden, EvaluadoOrden $evaluado, ?array $tablas = null): array
    {
        $tablas ??= self::tablas($evaluado);
        $encabezado = self::encabezadoDesdeEvaluado($orden, $evaluado);

        $personal = is_array($tablas['personal'] ?? null) ? $tablas['personal'] : [];
        if ($personal !== []) {
            $encabezado = self::aplicarPersonalAEncabezado($encabezado, $personal);

            // El override de REPRO trae solo la calle, así que el municipio y el departamento se
            // vuelven a anexar después de aplicarlo.
            $encabezado['direccion'] = self::direccionCompleta(
                (string) ($encabezado['direccion'] ?? ''),
                self::datosCuestionario($evaluado)
            );
        }

        $encabezado['estado_civil'] = self::etiquetaEstadoCivil((string) ($encabezado['estado_civil'] ?? ''));

        return self::sinGuionesDeRelleno($encabezado);
    }

    /**
     * @return array<string, mixed>
     */
    public static function tablas(EvaluadoOrden $evaluado): array
    {
        $cuestionario = $evaluado->cuestionario;
        if (! $cuestionario) {
            return [];
        }

        $tipo = $evaluado->tipoFormularioCuestionario();

        if (InformePreempleo::aplicaATipo($tipo)) {
            return InformePreempleo::tablasParaAdmin($cuestionario);
        }

        if (! in_array($tipo, ['periodica', 'especifica'], true)) {
            return [];
        }

        $tablasS3 = $cuestionario->getTablasPorNumeroSeccion(3);
        $tablasS4 = $cuestionario->getTablasPorNumeroSeccion(4);
        $respuestasS3 = $cuestionario->obtenerRespuestasSeccion(3);

        return [
            'familiar' => ResumenFamiliar::compilar($cuestionario),
            'academico' => $tablasS3['formacion_academica'] ?? [],
            'estudios_actuales' => $tablasS3['estudios_actuales'] ?? [],
            'laboral' => self::laboralPeriodica($tablasS3, $evaluado),
            'deudas' => $tablasS4['deudas'] ?? [],
            'labor_complementaria' => self::laborComplementariaPeriodica($respuestasS3, $tipo),
            'complementaria' => [],
            'tatuajes' => $cuestionario->getTablasPorNumeroSeccion(5)['tatuajes'] ?? [],
        ];
    }

    public static function seccionFotoCandidato(Cuestionario $cuestionario): string
    {
        return CuestionarioSecciones::slug(1, $cuestionario->tipo_formulario ?? 'preempleo');
    }

    /**
     * @param  list<array{pregunta: string, respuesta: string}>  $personal
     * @param  array<string, string>  $encabezado
     * @return array<string, string>
     */
    private static function aplicarPersonalAEncabezado(array $encabezado, array $personal): array
    {
        foreach ($personal as $fila) {
            if (! is_array($fila)) {
                continue;
            }

            $pregunta = trim((string) ($fila['pregunta'] ?? ''));
            $respuesta = trim((string) ($fila['respuesta'] ?? ''));

            if ($pregunta === '' || $respuesta === '') {
                continue;
            }

            $clave = self::MAPEO_PERSONAL_ENCABEZADO[$pregunta] ?? null;
            if ($clave === null) {
                continue;
            }

            $encabezado[$clave] = match ($clave) {
                'dpi' => self::formatearDpi($respuesta),
                'telefono', 'telefono_emergencia' => self::formatearTelefono($respuesta),
                'fecha_nacimiento' => self::formatearFechaNacimiento($respuesta),
                default => $respuesta,
            };
        }

        $nombres = trim((string) ($encabezado['nombres'] ?? ''));
        $apellidos = trim((string) ($encabezado['apellidos'] ?? ''));
        $encabezado['nombre'] = trim($nombres . ' ' . $apellidos) ?: ($encabezado['nombre'] ?? '—');

        if (($encabezado['lugar_nacimiento'] ?? '—') !== '—' || ($encabezado['fecha_nacimiento'] ?? '—') !== '—') {
            $encabezado['lugar_fecha_nacimiento'] = self::lugarFechaNacimientoDesdeEncabezado($encabezado);
        }

        return $encabezado;
    }

    /**
     * @return array<string, string>
     */
    private static function encabezadoDesdeEvaluado(Orden $orden, EvaluadoOrden $evaluado): array
    {
        $cuestionario = self::datosCuestionario($evaluado);

        $telefono = trim((string) ($evaluado->telefono ?: $evaluado->celular ?: $cuestionario['telefono_personal'] ?? ''));
        $direccion = self::direccionCompleta(
            trim((string) ($evaluado->direccion ?: $cuestionario['direccion_residencia'] ?? '')),
            $cuestionario
        );
        $edad = trim((string) ($cuestionario['edad'] ?? ''));
        if ($edad !== '' && ! str_contains($edad, 'año')) {
            $edad .= ' años';
        }

        $agencia = trim((string) ($evaluado->sede_region_empresa ?? ''));
        if ($agencia === '') {
            $agencia = '—';
        }

        // Cuadro OBSERVACIONES de la primera hoja: es del evaluador. El motivo y las observaciones
        // que la empresa registra en la orden ya salen en «Motivo de la prueba», y arrastrarlas aquí
        // hacía que el informe entregara notas internas del pedido (cliente 17-08-2026).
        $observaciones = InformeWordBloquesEvaluador::observaciones($evaluado->id);

        $fechaNacimiento = self::formatearFechaNacimiento((string) ($cuestionario['fecha_nacimiento'] ?? ''));
        $lugarNacimiento = trim((string) ($cuestionario['municipio_nacimiento'] ?? $cuestionario['lugar_nacimiento'] ?? ''));
        // El formulario guarda el teléfono de emergencia como "telefono_alternativo"; leyendo solo
        // "telefono_emergencia" el informe entregaba ese campo siempre vacío.
        $telefonoEmergencia = trim((string) (
            $cuestionario['telefono_alternativo']
            ?? $cuestionario['telefono_emergencia']
            ?? $evaluado->telefono_emergencia
            ?? ''
        ));
        $correo = trim((string) ($evaluado->email ?: $cuestionario['correo_electronico'] ?? $cuestionario['email'] ?? $cuestionario['email_personal'] ?? ''));
        $nacionalidad = trim((string) ($cuestionario['nacionalidad'] ?? 'Guatemalteca'));
        $estadoCivil = self::etiquetaEstadoCivil(
            (string) ($cuestionario['estado_civil'] ?? $cuestionario['estado_civil_detalle'] ?? '')
        );
        $igss = trim((string) ($cuestionario['igss'] ?? $cuestionario['numero_igss'] ?? ''));
        $nit = trim((string) ($cuestionario['nit'] ?? ''));
        $licencia = trim((string) ($cuestionario['licencia_conducir'] ?? $cuestionario['licencia'] ?? ''));

        return [
            'proceso' => self::etiquetaProceso($evaluado),
            'fecha' => self::formatearFecha($evaluado->fecha_realizada ?? now()),
            'nombre' => trim($evaluado->nombre . ' ' . $evaluado->apellidos),
            'nombres' => trim((string) $evaluado->nombre),
            'apellidos' => trim((string) $evaluado->apellidos),
            'puesto' => trim((string) ($evaluado->puesto_evaluar ?: '—')),
            'empresa' => trim((string) ($orden->empresa?->nombre ?: '—')),
            'agencia' => $agencia,
            'dpi' => self::formatearDpi((string) ($evaluado->dpi ?? '')),
            'telefono' => $telefono !== '' ? self::formatearTelefono($telefono) : '—',
            'telefono_emergencia' => $telefonoEmergencia !== '' ? self::formatearTelefono($telefonoEmergencia) : '—',
            'correo' => $correo !== '' ? $correo : '—',
            'nacionalidad' => $nacionalidad !== '' ? $nacionalidad : '—',
            'estado_civil' => $estadoCivil !== '' ? $estadoCivil : '—',
            'fecha_nacimiento' => $fechaNacimiento,
            'lugar_nacimiento' => $lugarNacimiento !== '' ? $lugarNacimiento : '—',
            'igss' => $igss !== '' ? $igss : '—',
            'nit' => $nit !== '' ? $nit : '—',
            'licencia' => $licencia !== '' ? $licencia : '—',
            'lugar_fecha_nacimiento' => self::lugarFechaNacimiento($cuestionario),
            'edad' => $edad !== '' ? $edad : '—',
            'direccion' => $direccion !== '' ? $direccion : '—',
            'resultado' => self::resultadoInforme($evaluado),
            'observaciones' => $observaciones,
        ];
    }

    /**
     * El cliente prefiere la celda en blanco antes que un guion de relleno (17-08-2026). El guion
     * se sigue usando internamente como marca de "sin dato" al armar lugar y fecha de nacimiento,
     * así que solo se limpia al entregar el encabezado.
     *
     * @param  array<string, string>  $encabezado
     * @return array<string, string>
     */
    private static function sinGuionesDeRelleno(array $encabezado): array
    {
        foreach ($encabezado as $clave => $valor) {
            if (trim((string) $valor) === '—') {
                $encabezado[$clave] = '';
            }
        }

        return $encabezado;
    }

    /** Claves del formulario (`casado`) → texto del informe (`Casado(a)`). */
    public static function etiquetaEstadoCivil(string $valor): string
    {
        $valor = trim($valor);
        if ($valor === '') {
            return '';
        }

        $mapa = [
            'soltero' => 'Soltero(a)',
            'casado' => 'Casado(a)',
            'union_libre' => 'Unión libre',
            'divorciado' => 'Divorciado(a)',
            'viudo' => 'Viudo(a)',
        ];

        $clave = mb_strtolower($valor);

        return $mapa[$clave] ?? $valor;
    }

    /**
     * La dirección del formulario suele traer solo calle y zona; el municipio y el departamento se
     * capturan en campos aparte y el informe los necesita completos (cliente 17-08-2026).
     *
     * @param  array<string, mixed>  $cuestionario
     */
    private static function direccionCompleta(string $direccion, array $cuestionario): string
    {
        $partes = [$direccion];

        foreach (['municipio', 'departamento'] as $campo) {
            $valor = trim((string) ($cuestionario[$campo] ?? ''));
            if ($valor === '') {
                continue;
            }

            // Evita repetir "Quetzaltenango" cuando la persona ya lo escribió en la dirección.
            if (self::contieneTexto($direccion, $valor)) {
                continue;
            }

            $partes[] = $valor;
        }

        return implode(', ', array_filter($partes, static fn (string $parte): bool => trim($parte) !== ''));
    }

    private static function contieneTexto(string $texto, string $aguja): bool
    {
        $normalizar = static fn (string $valor): string => mb_strtolower(trim($valor));

        return $aguja !== '' && str_contains($normalizar($texto), $normalizar($aguja));
    }

    /**
     * @param  array<string, mixed>  $tablasS3
     * @return list<array<string, mixed>>
     */
    public static function laboralPeriodica(array $tablasS3, EvaluadoOrden $evaluado): array
    {
        $empleo = $tablasS3['empleo_actual'][0] ?? null;
        if (! is_array($empleo)) {
            return [];
        }

        $fechas = trim((string) ($empleo['fechas_laboradas'] ?? ''));
        if ($fechas === '') {
            $ingreso = trim((string) ($empleo['fecha_ingreso'] ?? ''));
            if ($ingreso !== '') {
                try {
                    $fechas = Carbon::parse($ingreso)->format('d/m/Y');
                } catch (\Throwable) {
                    $fechas = $ingreso;
                }
            }
        }

        return [[
            'empresa' => $empleo['empresa'] ?? '',
            'puesto' => $empleo['puesto'] ?? '',
            'fechas' => $fechas,
            'salario' => isset($empleo['salario_actual']) ? (string) $empleo['salario_actual'] : '',
            'motivo' => trim((string) ($evaluado->motivo_hecho_evaluacion ?: '—')),
        ]];
    }

    /**
     * @param  array<string, mixed>  $respuestas
     * @return list<array{pregunta: string, respuesta: string}>
     */
    public static function laborComplementariaPeriodica(array $respuestas, string $tipoFormulario): array
    {
        $filas = [];

        foreach (HistorialLaboralPeriodico::PREGUNTAS as $indice => $pregunta) {
            $valor = trim((string) ($respuestas[$pregunta['key']] ?? ''));
            if ($valor === '') {
                continue;
            }

            $label = $indice === 0 && $tipoFormulario === 'especifica'
                ? HistorialLaboralPeriodico::labelPregunta1(true)
                : $pregunta['label'];

            $filas[] = [
                'pregunta' => $label,
                'respuesta' => $valor,
            ];
        }

        $adicional = trim((string) ($respuestas[HistorialLaboralPeriodico::CAMPO_INFORMACION_ADICIONAL['key']] ?? ''));
        if ($adicional !== '') {
            $filas[] = [
                'pregunta' => HistorialLaboralPeriodico::CAMPO_INFORMACION_ADICIONAL['label'],
                'respuesta' => $adicional,
            ];
        }

        return $filas;
    }

    /**
     * @return array<string, mixed>
     */
    private static function datosCuestionario(EvaluadoOrden $evaluado): array
    {
        if (! $evaluado->cuestionario) {
            return [];
        }

        return $evaluado->cuestionario->obtenerRespuestasSeccion(1);
    }

    private static function etiquetaProceso(EvaluadoOrden $evaluado): string
    {
        $servicio = match ($evaluado->tipo_servicio) {
            'poligrafo' => 'Polígrafo',
            'vsa' => 'VSA',
            'socioeconomico' => 'Estudio Socioeconómico',
            default => ucfirst((string) $evaluado->tipo_servicio),
        };

        return match ($evaluado->tipo_formulario) {
            'preempleo' => 'Prueba de ' . $servicio . ' Pre-empleo',
            'periodica' => 'Prueba de ' . $servicio . ' - Periódica',
            'especifica' => 'Prueba de ' . $servicio . ' - Específica',
            default => 'Prueba de ' . $servicio,
        };
    }

    private static function resultadoInforme(EvaluadoOrden $evaluado): string
    {
        if (! $evaluado->resultado || $evaluado->resultado === 'pendiente') {
            return 'PENDIENTE';
        }

        $texto = strtoupper($evaluado->resultado_texto);

        return match ($evaluado->resultado) {
            'aprobado' => 'APROBADO',
            'aprobado_con_obs' => 'APROBADO CON OBSERVACIONES',
            'aprobado_excepcion' => 'APROBADO CON EXCEPCIÓN',
            'no_aprobado' => 'NO APROBADO',
            'inconcluso' => 'INCONCLUSO',
            default => $texto,
        };
    }

    /**
     * @param  array<string, mixed>  $cuestionario
     */
    private static function lugarFechaNacimiento(array $cuestionario): string
    {
        $municipio = trim((string) ($cuestionario['municipio_nacimiento'] ?? $cuestionario['lugar_nacimiento'] ?? ''));
        $fechaRaw = trim((string) ($cuestionario['fecha_nacimiento'] ?? ''));

        if ($municipio === '' && $fechaRaw === '') {
            return '—';
        }

        $fechaTexto = '—';
        if ($fechaRaw !== '') {
            try {
                $fechaTexto = Carbon::parse($fechaRaw)->locale('es')->translatedFormat('j \d\e F \d\e Y');
            } catch (\Throwable) {
                $fechaTexto = $fechaRaw;
            }
        }

        if ($municipio === '') {
            return $fechaTexto;
        }

        if ($fechaTexto === '—') {
            return $municipio;
        }

        return $municipio . ', ' . $fechaTexto;
    }

    /** @param array<string, string> $encabezado */
    private static function lugarFechaNacimientoDesdeEncabezado(array $encabezado): string
    {
        return self::lugarFechaNacimiento([
            'municipio_nacimiento' => ($encabezado['lugar_nacimiento'] ?? '') === '—' ? '' : ($encabezado['lugar_nacimiento'] ?? ''),
            'fecha_nacimiento' => ($encabezado['fecha_nacimiento'] ?? '') === '—' ? '' : ($encabezado['fecha_nacimiento'] ?? ''),
        ]);
    }

    private static function formatearFechaNacimiento(string $fechaRaw): string
    {
        $fechaRaw = trim($fechaRaw);
        if ($fechaRaw === '') {
            return '—';
        }

        try {
            return Carbon::parse($fechaRaw)->format('d/m/Y');
        } catch (\Throwable) {
            return $fechaRaw;
        }
    }

    private static function formatearFecha(mixed $fecha): string
    {
        if ($fecha instanceof Carbon) {
            return $fecha->format('d/m/Y');
        }

        if ($fecha) {
            try {
                return Carbon::parse($fecha)->format('d/m/Y');
            } catch (\Throwable) {
                return (string) $fecha;
            }
        }

        return now()->format('d/m/Y');
    }

    private static function formatearDpi(string $dpi): string
    {
        $digits = preg_replace('/\D/', '', $dpi) ?? '';

        if (strlen($digits) === 13) {
            return substr($digits, 0, 4) . ' ' . substr($digits, 4, 5) . ' ' . substr($digits, 9, 4);
        }

        return $dpi !== '' ? $dpi : '—';
    }

    private static function formatearTelefono(string $telefono): string
    {
        $digits = preg_replace('/\D/', '', $telefono) ?? '';

        if (strlen($digits) === 8) {
            return substr($digits, 0, 4) . ' ' . substr($digits, 4, 4);
        }

        return $telefono;
    }
}
