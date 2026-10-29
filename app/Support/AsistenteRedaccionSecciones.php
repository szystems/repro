<?php

namespace App\Support;

use App\Models\EvaluadoOrden;

/**
 * Las seis secciones del asistente. Hábitos y las observaciones de la primera hoja no entran.
 */
class AsistenteRedaccionSecciones
{
    /** @var array<string, string> */
    public const TITULOS = [
        'word_laboral' => 'Información laboral',
        'word_economico' => 'Información económica',
        'word_salud' => 'Salud',
        'word_sustancias' => 'Actividades delictivas y drogas',
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
            ['clave' => 'sustancias_usadas', 'pregunta' => 'Sustancias que declara conocer o haber usado', 'tipo' => 'sustancias'],
        ];
        foreach (SaludHabitosCampos::SUSTANCIAS_PREGUNTAS as $clave => $pregunta) {
            $definiciones[] = ['clave' => $clave, 'pregunta' => $pregunta, 'tipo' => 'texto'];
        }
        $respuestas = $evaluado->cuestionario?->obtenerRespuestasSeccion(5) ?? [];

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
            ['clave' => 'tiene_deudas', 'pregunta' => '¿Tiene deudas?', 'tipo' => 'si_no'],
            ['clave' => 'detalle_deudas', 'pregunta' => 'Detalle de deudas', 'tipo' => 'detalle', 'depende_de' => 'tiene_deudas'],
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
            ['clave' => 'econ_demandas_deudas', 'pregunta' => SituacionEconomicaCampos::LABEL_DEMANDAS, 'tipo' => 'si_no'],
            ['clave' => 'econ_detalle_demandas', 'pregunta' => 'Detalle de demandas por deuda', 'tipo' => 'detalle', 'depende_de' => 'econ_demandas_deudas'],
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

        $deudas = $evaluado->cuestionario?->getTablasPorNumeroSeccion(4)['deudas'] ?? [];
        $tabla = AsistenteRedaccionCampos::tabla('deuda', 'Deuda', is_array($deudas) ? $deudas : [], TablaDinamica::columnasDeudas());
        if (($tabla[0]['vacio'] ?? false) && self::valorDe($campos, 'tiene_deudas') === 'no') {
            $tabla[0]['incompleto'] = false;
        }
        $campos = array_merge($campos, $tabla);

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
        $definiciones = [
            ['clave' => 'experiencia_previa', 'pregunta' => HistorialLaboralIntegridad::LABEL_EXPERIENCIA_PREVIA, 'tipo' => 'texto'],
            ['clave' => 'observaciones_laborales', 'pregunta' => HistorialLaboralIntegridad::LABEL_OBSERVACIONES_LABORALES, 'tipo' => 'texto'],
        ];
        foreach ($preguntas as $pregunta) {
            $definiciones[] = ['clave' => $pregunta['key'], 'pregunta' => $pregunta['label'], 'tipo' => 'texto'];
        }

        $campos = AsistenteRedaccionCampos::lista($definiciones, $respuestas);
        $tablas = $evaluado->cuestionario?->getTablasPorNumeroSeccion(3) ?? [];
        $filas = $peri ? ($tablas['empleo_actual'] ?? []) : ($tablas['empleos'] ?? []);
        $columnas = $peri ? TablaDinamica::columnasEmpleoActualPeriodico() : TablaDinamica::columnasEmpleosPreempleo();
        $campos = array_merge($campos, AsistenteRedaccionCampos::tabla(
            'empleo',
            'Empleo',
            is_array($filas) ? $filas : [],
            $columnas,
            ['jefe_inmediato', 'contacto_rrhh']
        ));

        return AsistenteRedaccionCampos::cerrar(
            $evaluado,
            'word_laboral',
            self::TITULOS['word_laboral'],
            $campos,
            $observacion
        );
    }

    /**
     * @return array<string, mixed>
     */
    private static function recomendaciones(EvaluadoOrden $evaluado, ?string $observacion): array
    {
        $notas = EvaluadorNotasSupport::mapaPorSeccion($evaluado->id);
        $campos = [];
        foreach (self::TITULOS as $slug => $titulo) {
            if ($slug === InformeWordBloquesEvaluador::NOTA_RECOMENDACIONES) {
                continue;
            }
            $texto = trim((string) ($notas[$slug] ?? ''));
            $campos[] = [
                'clave' => $slug,
                'pregunta' => $titulo.' ya redactada',
                'valor' => $texto,
                'texto' => $texto,
                'vacio' => $texto === '',
                'incompleto' => false,
            ];
        }

        return AsistenteRedaccionCampos::cerrar(
            $evaluado,
            InformeWordBloquesEvaluador::NOTA_RECOMENDACIONES,
            self::TITULOS[InformeWordBloquesEvaluador::NOTA_RECOMENDACIONES],
            $campos,
            $observacion
        );
    }

    /**
     * @param  list<array{clave: string, valor: string}>  $campos
     */
    private static function valorDe(array $campos, string $clave): string
    {
        foreach ($campos as $campo) {
            if ($campo['clave'] === $clave) {
                return $campo['valor'];
            }
        }

        return '';
    }
}
