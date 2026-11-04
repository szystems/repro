<?php

namespace App\Support;

use App\Models\EvaluadoOrden;

/**
 * Secciones del asistente. La hoja preliminar solo alimenta las recomendaciones.
 */
class AsistenteRedaccionSecciones
{
    /** @var list<string> */
    private const JUDICIALES = [
        'judicial_01', 'judicial_02', 'judicial_03', 'judicial_04', 'judicial_05', 'judicial_06',
    ];

    /** @var list<string> */
    private const DELICTIVAS = [
        'judicial_07', 'judicial_08', 'judicial_09', 'judicial_10', 'judicial_11',
        'judicial_12', 'judicial_13', 'judicial_14', 'judicial_15', 'judicial_16',
    ];

    /** @var array<string, string> */
    public const TITULOS = [
        'word_laboral' => 'Información laboral',
        'word_economico' => 'Información económica',
        'word_salud' => 'Salud',
        'word_habitos' => 'Hábitos personales',
        'word_sustancias' => 'Actividades delictivas y drogas ilegales',
        'word_judicial' => 'Aspectos judiciales',
        InformeWordBloquesEvaluador::NOTA_RECOMENDACIONES => 'Recomendaciones',
    ];

    public static function permite(string $seccion): bool
    {
        return isset(self::TITULOS[$seccion]);
    }

    /**
     * @return array<string, mixed>
     */
    public static function paquete(EvaluadoOrden $evaluado, string $seccion, ?string $observacion = null): array
    {
        $evaluado->loadMissing('cuestionario');

        return match ($seccion) {
            'word_salud' => AsistenteRedaccionSalud::paquete($evaluado, $observacion),
            'word_habitos' => self::habitos($evaluado, $observacion),
            'word_sustancias' => self::sustancias($evaluado, $observacion),
            'word_judicial' => self::judicial($evaluado, $observacion),
            'word_economico' => self::economico($evaluado, $observacion),
            'word_laboral' => self::laboral($evaluado, $observacion),
            InformeWordBloquesEvaluador::NOTA_RECOMENDACIONES => self::recomendaciones($evaluado, $observacion),
            default => throw new \InvalidArgumentException('Sección no disponible para el asistente.'),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private static function sustancias(EvaluadoOrden $evaluado, ?string $observacion): array
    {
        $definiciones = [
            ['clave' => 'sustancias_usadas', 'pregunta' => 'Drogas ilegales que declara conocer o haber usado', 'tipo' => 'sustancias'],
        ];
        foreach (SaludHabitosCampos::SUSTANCIAS_PREGUNTAS as $clave => $pregunta) {
            $definiciones[] = ['clave' => $clave, 'pregunta' => $pregunta, 'tipo' => 'texto'];
        }
        $respuestas = $evaluado->cuestionario?->obtenerRespuestasSeccion(5) ?? [];
        foreach (AntecedentesJudiciales::PREGUNTAS as $pregunta) {
            if (! in_array($pregunta['key'], self::DELICTIVAS, true)) {
                continue;
            }
            $definiciones[] = ['clave' => $pregunta['key'], 'pregunta' => $pregunta['label'], 'tipo' => 'texto'];
        }

        return AsistenteRedaccionCampos::cerrar(
            $evaluado,
            'word_sustancias',
            self::TITULOS['word_sustancias'],
            AsistenteRedaccionCampos::lista($definiciones, $respuestas, [
                ['clave' => 'sustancias_complemento', 'pregunta' => 'Información complementaria de sustancias'],
            ]),
            $observacion
        );
    }

    /**
     * @return array<string, mixed>
     */
    private static function judicial(EvaluadoOrden $evaluado, ?string $observacion): array
    {
        $definiciones = [];
        foreach (AntecedentesJudiciales::PREGUNTAS as $pregunta) {
            if (! in_array($pregunta['key'], self::JUDICIALES, true)) {
                continue;
            }
            $definiciones[] = ['clave' => $pregunta['key'], 'pregunta' => $pregunta['label'], 'tipo' => 'texto'];
        }

        return AsistenteRedaccionCampos::cerrar(
            $evaluado,
            'word_judicial',
            self::TITULOS['word_judicial'],
            AsistenteRedaccionCampos::lista($definiciones, $evaluado->cuestionario?->obtenerRespuestasSeccion(5) ?? []),
            $observacion
        );
    }

    /**
     * @return array<string, mixed>
     */
    private static function economico(EvaluadoOrden $evaluado, ?string $observacion): array
    {
        $respuestas = $evaluado->cuestionario?->obtenerRespuestasSeccion(4) ?? [];
        $definiciones = [
            ['clave' => 'tipo_vivienda', 'pregunta' => 'Tipo de vivienda', 'tipo' => 'vivienda'],
            ['clave' => 'econ_tipo_vivienda_detalle', 'pregunta' => SituacionEconomicaCampos::LABEL_VIVIENDA, 'tipo' => 'texto'],
            ['clave' => 'personas_hogar', 'pregunta' => 'Personas en el hogar', 'tipo' => 'texto'],
            ['clave' => 'personas_contribuyen_gastos', 'pregunta' => 'Personas que aportan a los gastos', 'tipo' => 'texto'],
            ['clave' => 'dependientes_economicos', 'pregunta' => 'Dependientes económicos', 'tipo' => 'texto'],
            ['clave' => 'econ_dependientes_detalle', 'pregunta' => SituacionEconomicaCampos::LABEL_DEPENDIENTES, 'tipo' => 'texto'],
            ['clave' => 'econ_ingresos_adicionales_detalle', 'pregunta' => SituacionEconomicaCampos::LABEL_INGRESOS_ADICIONALES, 'tipo' => 'texto'],
            ['clave' => 'econ_pretension_salarial', 'pregunta' => SituacionEconomicaCampos::LABEL_PRETENSION, 'tipo' => 'texto'],
            ['clave' => 'econ_gastos_mensuales_aprox', 'pregunta' => SituacionEconomicaCampos::LABEL_GASTOS_MENSUALES, 'tipo' => 'texto'],
            ['clave' => 'econ_es_fiador', 'pregunta' => SituacionEconomicaCampos::LABEL_ES_FIADOR, 'tipo' => 'si_no'],
            ['clave' => 'econ_detalle_es_fiador', 'pregunta' => 'Detalle de fiador', 'tipo' => 'detalle', 'depende_de' => 'econ_es_fiador'],
            ['clave' => 'econ_problemas_bancarios', 'pregunta' => SituacionEconomicaCampos::LABEL_PROBLEMAS_BANCARIOS, 'tipo' => 'si_no'],
            ['clave' => 'econ_detalle_problemas_bancarios', 'pregunta' => 'Detalle de problemas bancarios', 'tipo' => 'detalle', 'depende_de' => 'econ_problemas_bancarios'],
            ['clave' => 'econ_posee_propiedades', 'pregunta' => SituacionEconomicaCampos::LABEL_PROPIEDADES, 'tipo' => 'si_no'],
            ['clave' => 'econ_detalle_propiedades', 'pregunta' => 'Detalle de propiedades', 'tipo' => 'detalle', 'depende_de' => 'econ_posee_propiedades'],
            ['clave' => 'econ_posee_vehiculos', 'pregunta' => SituacionEconomicaCampos::LABEL_VEHICULOS, 'tipo' => 'si_no'],
            ['clave' => 'econ_detalle_vehiculos', 'pregunta' => 'Detalle de vehículos', 'tipo' => 'detalle', 'depende_de' => 'econ_posee_vehiculos'],
            ['clave' => 'econ_problemas_sat', 'pregunta' => SituacionEconomicaCampos::LABEL_SAT, 'tipo' => 'si_no'],
            ['clave' => 'econ_detalle_sat', 'pregunta' => 'Detalle SAT', 'tipo' => 'detalle', 'depende_de' => 'econ_problemas_sat'],
        ];
        $campos = AsistenteRedaccionCampos::lista($definiciones, $respuestas, [
            ['clave' => 'monto_alquiler', 'pregunta' => 'Monto de alquiler'],
            ['clave' => 'econ_monto_alquiler', 'pregunta' => 'Monto de alquiler declarado'],
            ['clave' => 'monto_hipoteca', 'pregunta' => 'Cuota de hipoteca'],
            ['clave' => 'econ_patrimonio_aprox', 'pregunta' => SituacionEconomicaCampos::LABEL_PATRIMONIO_SOCIO],
            ['clave' => 'viv_tipo_vivienda_detalle', 'pregunta' => 'Tipo de vivienda declarado en la visita'],
            ['clave' => 'viv_monto_alquiler', 'pregunta' => 'Alquiler declarado en la visita'],
            ['clave' => 'viv_tiempo_residencia', 'pregunta' => 'Tiempo en el domicilio'],
            ['clave' => 'viv_habitantes_detalle', 'pregunta' => 'Habitantes del domicilio'],
        ]);

        return AsistenteRedaccionCampos::cerrar(
            $evaluado,
            'word_economico',
            self::TITULOS['word_economico'],
            $campos,
            $observacion
        );
    }

    /**
     * @return array<string, mixed>
     */
    private static function laboral(EvaluadoOrden $evaluado, ?string $observacion): array
    {
        $respuestas = $evaluado->cuestionario?->obtenerRespuestasSeccion(3) ?? [];
        $tipo = (string) ($evaluado->tipo_formulario ?: $evaluado->cuestionario?->tipo_formulario ?: 'preempleo');
        $peri = in_array($tipo, ['periodica', 'especifica'], true);
        $preguntas = $peri ? HistorialLaboralPeriodico::PREGUNTAS : HistorialLaboralIntegridad::PREGUNTAS;
        $omitir = ['periodico_02', 'periodico_info_adicional'];
        $definiciones = [];
        foreach ($preguntas as $pregunta) {
            if (in_array($pregunta['key'], $omitir, true)) {
                continue;
            }
            $definiciones[] = ['clave' => $pregunta['key'], 'pregunta' => $pregunta['label'], 'tipo' => 'texto'];
        }

        return AsistenteRedaccionCampos::cerrar(
            $evaluado,
            'word_laboral',
            self::TITULOS['word_laboral'],
            AsistenteRedaccionCampos::lista($definiciones, $respuestas),
            $observacion
        );
    }

    /**
     * @return array<string, mixed>
     */
    private static function habitos(EvaluadoOrden $evaluado, ?string $observacion): array
    {
        $definiciones = [];
        foreach (SaludHabitosCampos::HABITOS as $clave => $pregunta) {
            $definiciones[] = ['clave' => $clave, 'pregunta' => $pregunta, 'tipo' => 'texto'];
        }

        return AsistenteRedaccionCampos::cerrar(
            $evaluado,
            'word_habitos',
            self::TITULOS['word_habitos'],
            AsistenteRedaccionCampos::lista($definiciones, $evaluado->cuestionario?->obtenerRespuestasSeccion(5) ?? []),
            $observacion
        );
    }

    /**
     * @return array<string, mixed>
     */
    private static function recomendaciones(EvaluadoOrden $evaluado, ?string $observacion): array
    {
        $notas = EvaluadorNotasSupport::mapaPorSeccion($evaluado->id);
        $texto = trim((string) ($notas[InformeWordBloquesEvaluador::NOTA_OBSERVACIONES] ?? ''));
        $campos = [[
            'clave' => InformeWordBloquesEvaluador::NOTA_OBSERVACIONES,
            'pregunta' => 'Observaciones del evaluador en la hoja preliminar',
            'valor' => $texto,
            'texto' => $texto,
            'vacio' => $texto === '',
            'incompleto' => false,
        ]];

        return AsistenteRedaccionCampos::cerrar(
            $evaluado,
            InformeWordBloquesEvaluador::NOTA_RECOMENDACIONES,
            self::TITULOS[InformeWordBloquesEvaluador::NOTA_RECOMENDACIONES],
            $campos,
            $observacion
        );
    }
}
