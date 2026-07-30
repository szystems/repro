<?php

namespace App\Support;

use App\Models\Cuestionario;
use App\Models\EvaluadoOrden;
use App\Models\Orden;

/** Compila narrativas del informe Word (F7 fase D / 7.6) desde cuestionario y evaluador. */
class InformeWordNarrativas
{
    /** @var list<array{key: string, label: string}> */
    private const CAMPOS_SALUD = [
        ['key' => 'salud_preocupaciones', 'label' => 'Preocupaciones de salud'],
        ['key' => 'salud_estado_general', 'label' => 'Estado general'],
        ['key' => 'salud_atencion_psicologica', 'label' => 'Atención psicológica'],
        ['key' => 'salud_detalle_psicologica', 'label' => 'Detalle atención psicológica'],
        ['key' => 'salud_situacion_emocional', 'label' => 'Situación emocional'],
        ['key' => 'salud_ideacion_dano', 'label' => 'Ideación de daño'],
        ['key' => 'salud_detalle_ideacion', 'label' => 'Detalle ideación'],
        ['key' => 'salud_tipo_sangre', 'label' => 'Tipo de sangre'],
        ['key' => 'salud_intento_suicidio', 'label' => 'Intento de suicidio'],
        ['key' => 'salud_detalle_emocional', 'label' => 'Detalle situación emocional'],
        ['key' => 'salud_peso', 'label' => 'Peso (libras)'],
        ['key' => 'salud_estatura', 'label' => 'Estatura (m)'],
        ['key' => 'salud_practica_deporte', 'label' => 'Practica deporte'],
        ['key' => 'salud_detalle_deporte', 'label' => 'Detalle deporte'],
        ['key' => 'salud_tratamiento_medico', 'label' => 'Tratamiento médico'],
        ['key' => 'salud_detalle_tratamiento', 'label' => 'Detalle tratamiento'],
        ['key' => 'salud_hospitalizaciones', 'label' => 'Hospitalizaciones'],
        ['key' => 'salud_detalle_hospitalizaciones', 'label' => 'Detalle hospitalizaciones'],
        ['key' => 'salud_ausencias_enfermedad', 'label' => 'Ausencias por enfermedad'],
        ['key' => 'salud_detalle_ausencias', 'label' => 'Detalle ausencias'],
    ];

    /** @var list<array{key: string, label: string}> */
    private const CAMPOS_HABITOS = [
        ['key' => 'habito_tiempo_libre', 'label' => 'Tiempo libre'],
        ['key' => 'habito_bares_frecuencia', 'label' => 'Frecuencia en bares'],
        ['key' => 'habito_alcohol_ultimo', 'label' => 'Último consumo de alcohol'],
        ['key' => 'habito_alcohol_mensual', 'label' => 'Consumo mensual de alcohol'],
        ['key' => 'habito_alcohol_detenido', 'label' => 'Detenido por alcohol'],
        ['key' => 'habito_alcohol_laboral', 'label' => 'Alcohol en el trabajo'],
        ['key' => 'habito_alcohol_despido', 'label' => 'Despido por alcohol'],
        ['key' => 'habito_tabaco', 'label' => 'Tabaco'],
        ['key' => 'habito_juegos_azar', 'label' => 'Juegos de azar'],
        ['key' => 'habito_alcohol_frecuencia', 'label' => 'Consumo de alcohol (legacy)'],
        ['key' => 'habito_alcohol_excesos', 'label' => 'Excesos de alcohol (legacy)'],
    ];

    /**
     * @return array{
     *   salud: string,
     *   habitos: string,
     *   drogas: string,
     *   judicial: string,
     *   recomendaciones: string,
     *   conclusiones: string,
     *   nombre_candidato: string,
     *   resultado_poligrafico: string,
     *   poligrafista: string,
     *   informacion_complementaria: list<array{etiqueta: string, respuesta: string}>
     * }
     */
    public static function compilar(Orden $orden, EvaluadoOrden $evaluado, string $variante): array
    {
        $evaluado->loadMissing(['cuestionario', 'poligrafista']);
        $cuestionario = $evaluado->cuestionario;
        $respuestasAntecedentes = self::respuestasAntecedentes($cuestionario);
        $respuestasSeccion1 = self::respuestasSeccion1($cuestionario);
        $respuestasLaborales = self::respuestasLaborales($cuestionario);
        $notasEvaluador = EvaluadorNotasSupport::mapaPorSeccion($evaluado->id);

        $nombre = trim($evaluado->nombre . ' ' . $evaluado->apellidos);

        $notasEvaluador = EvaluadorNotasSupport::mapaPorSeccion($evaluado->id);

        return [
            'salud' => self::textoEvaluador($notasEvaluador['word_salud'] ?? null)
                ?: self::compilarCampos(self::CAMPOS_SALUD, $respuestasAntecedentes),
            'habitos' => self::textoEvaluador($notasEvaluador['word_habitos'] ?? null)
                ?: self::compilarCampos(self::CAMPOS_HABITOS, $respuestasAntecedentes),
            'drogas' => self::textoEvaluador($notasEvaluador['word_sustancias'] ?? null)
                ?: self::compilarDrogas($respuestasAntecedentes),
            'judicial' => self::textoEvaluador($notasEvaluador['word_judicial'] ?? null)
                ?: self::compilarJudicial($respuestasAntecedentes, $notasEvaluador['antecedentes'] ?? null),
            'recomendaciones' => self::compilarRecomendaciones($evaluado, $notasEvaluador['antecedentes'] ?? null),
            'conclusiones' => self::compilarConclusiones($evaluado),
            'nombre_candidato' => $nombre !== '' ? $nombre : '—',
            'resultado_poligrafico' => self::codigoResultadoPoligrafico($evaluado),
            'poligrafista' => trim((string) ($evaluado->poligrafista?->name ?: '—')),
            'informacion_complementaria' => self::filasInformacionComplementaria(
                $respuestasAntecedentes,
                $respuestasSeccion1,
                $respuestasLaborales,
                $variante
            ),
        ];
    }

    /**
     * @param  array<string, mixed>  $respuestas
     */
    private static function compilarCampos(array $campos, array $respuestas): string
    {
        $lineas = [];

        foreach ($campos as $campo) {
            $valor = self::formatearValor($respuestas[$campo['key']] ?? null);
            if ($valor === '') {
                continue;
            }

            $lineas[] = $campo['label'] . ': ' . $valor;
        }

        return implode("\n", $lineas);
    }

    /**
     * @param  array<string, mixed>  $respuestas
     */
    private static function compilarDrogas(array $respuestas): string
    {
        $lineas = [];
        $sustancias = SaludHabitosCampos::sustanciasDesdeAlmacenamiento($respuestas['sustancias_usadas'] ?? null);

        if ($sustancias !== []) {
            $etiquetas = array_map(
                fn (string $clave): string => SaludHabitosCampos::SUSTANCIAS[$clave] ?? $clave,
                $sustancias
            );
            $lineas[] = 'Sustancias declaradas: ' . implode(', ', $etiquetas);
        }

        $complemento = trim((string) ($respuestas['sustancias_complemento'] ?? ''));
        if ($complemento !== '') {
            $lineas[] = 'Información complementaria: ' . $complemento;
        }

        return implode("\n", $lineas);
    }

    /**
     * @param  array<string, mixed>  $respuestas
     */
    private static function compilarJudicial(array $respuestas, ?string $notaEvaluador): string
    {
        $lineas = [];

        foreach (AntecedentesJudiciales::PREGUNTAS as $pregunta) {
            $respuesta = trim((string) ($respuestas[$pregunta['key']] ?? ''));
            if ($respuesta === '') {
                continue;
            }

            $lineas[] = $pregunta['label'];
            $lineas[] = $respuesta;
            $lineas[] = '';
        }

        $nota = trim((string) $notaEvaluador);
        if ($nota !== '') {
            if ($lineas !== []) {
                $lineas[] = '';
            }

            $lineas[] = 'Notas adicionales del evaluador:';
            $lineas[] = $nota;
        }

        return rtrim(implode("\n", $lineas));
    }

    private static function compilarRecomendaciones(EvaluadoOrden $evaluado, ?string $notaAntecedentes): string
    {
        $texto = trim((string) ($evaluado->notas_poligrafo ?? ''));
        if ($texto === '') {
            $texto = trim((string) $notaAntecedentes);
        }

        return $texto !== '' ? $texto : '—';
    }

    private static function compilarConclusiones(EvaluadoOrden $evaluado): string
    {
        $texto = trim((string) ($evaluado->texto_informe_preliminar ?? ''));
        if ($texto !== '') {
            return $texto;
        }

        return match ($evaluado->resultado) {
            'aprobado' => 'Con base en la evaluación realizada, se recomienda la contratación del evaluado.',
            'aprobado_con_obs' => 'Con base en la evaluación realizada, se recomienda la contratación con observaciones.',
            'aprobado_excepcion' => 'Con base en la evaluación realizada, se recomienda la contratación con excepción.',
            'no_aprobado' => 'Con base en la evaluación realizada, no se recomienda la contratación del evaluado.',
            'inconcluso' => 'La evaluación resultó inconclusa; se requiere ampliación o nueva entrevista.',
            default => '—',
        };
    }

    private static function codigoResultadoPoligrafico(EvaluadoOrden $evaluado): string
    {
        return match ($evaluado->resultado) {
            'no_aprobado' => 'DI',
            'inconcluso' => 'INCONCLUSO',
            default => 'NDI',
        };
    }

    /**
     * @param  array<string, mixed>  $respuestasAntecedentes
     * @param  array<string, mixed>  $respuestasSeccion1
     * @return list<array{etiqueta: string, respuesta: string}>
     */
    private static function filasInformacionComplementaria(
        array $respuestasAntecedentes,
        array $respuestasSeccion1,
        array $respuestasLaborales,
        string $variante
    ): array {
        if ($variante === InformeWordPlantillas::VARIANTE_PREEMPLEO) {
            $filas = [];
            foreach (InformacionComplementaria::PREGUNTAS as $pregunta) {
                $filas[] = [
                    'etiqueta' => rtrim($pregunta['label'], '.').':',
                    'respuesta' => self::texto($respuestasAntecedentes[$pregunta['key']] ?? ''),
                ];
            }
            $filas[] = ['etiqueta' => 'Observaciones adicionales:', 'respuesta' => self::texto($respuestasAntecedentes['informacion_adicional_final'] ?? '—')];

            return $filas;
        }

        return [
            ['etiqueta' => '¿Cuánto tiempo piensa seguir laborando en la empresa?', 'respuesta' => self::texto($respuestasLaborales['periodico_03'] ?? '')],
            ['etiqueta' => '¿Está de acuerdo con las condiciones laborales que le ofrece la empresa?:', 'respuesta' => self::texto($respuestasLaborales['periodico_04'] ?? '')],
            ['etiqueta' => '¿Qué cambios o mejoras sugiere a la empresa?', 'respuesta' => self::texto($respuestasLaborales['periodico_05'] ?? '')],
            ['etiqueta' => 'Cuáles son sus metas laborales:', 'respuesta' => self::texto($respuestasLaborales['periodico_06'] ?? '')],
            ['etiqueta' => 'Cuáles son sus metas personales:', 'respuesta' => self::texto($respuestasLaborales['comp_metas'] ?? $respuestasAntecedentes['comp_metas'] ?? '')],
            ['etiqueta' => 'Colaboración y actitud durante este proceso:', 'respuesta' => self::texto($respuestasLaborales['periodico_07'] ?? '')],
            ['etiqueta' => 'Observaciones adicionales:', 'respuesta' => self::texto($respuestasLaborales['periodico_info_adicional'] ?? '')],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function respuestasAntecedentes(?Cuestionario $cuestionario): array
    {
        if ($cuestionario === null) {
            return [];
        }

        return $cuestionario->obtenerRespuestasSeccion(5);
    }

    /**
     * @return array<string, mixed>
     */
    private static function respuestasLaborales(?Cuestionario $cuestionario): array
    {
        if ($cuestionario === null) {
            return [];
        }

        return $cuestionario->obtenerRespuestasSeccion(3);
    }

    /**
     * @return array<string, mixed>
     */
    private static function respuestasSeccion1(?Cuestionario $cuestionario): array
    {
        if ($cuestionario === null) {
            return [];
        }

        return $cuestionario->obtenerRespuestasSeccion(1);
    }

    private static function formatearValor(mixed $valor): string
    {
        if (is_array($valor)) {
            return implode(', ', array_filter(array_map(
                fn ($item): string => self::texto($item),
                $valor
            )));
        }

        $texto = self::texto($valor);
        if ($texto === '') {
            return '';
        }

        return match ($texto) {
            'si' => 'Sí',
            'no' => 'No',
            'excelente' => 'Excelente',
            'bueno' => 'Bueno',
            'regular' => 'Regular',
            'malo' => 'Malo',
            'nunca' => 'Nunca',
            'ocasional' => 'Ocasional',
            'frecuente' => 'Frecuente',
            default => $texto,
        };
    }

    private static function texto(mixed $valor): string
    {
        return trim((string) $valor);
    }

    private static function textoEvaluador(?string $nota): string
    {
        return trim((string) $nota);
    }
}
