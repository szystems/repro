<?php

namespace App\Support;

/** E2.13–2.16 — Salud, hábitos y sustancias (internos). Literales POLIGRAFO PRESENCIAL (2).pdf. */
class SaludHabitosCampos
{
    public const TITULO_SALUD = 'Aspectos de salud';

    public const TITULO_HABITOS = 'Hábitos personales';

    public const TITULO_SUSTANCIAS = 'ASPECTOS VARIOS';

    public const LABEL_PREOCUPACIONES = '¿Cual es el problema personal mas serio que tiene actualmente?';

    public const LABEL_ESTADO_GENERAL = 'Cómo considera su condición general de salud actual?';

    public const LABEL_PSICOLOGICA = '¿Ha estado alguna vez en algún tratamiento psicológico o psiquiátrico? ¿Por qué motivo?';

    public const LABEL_TIPO_SANGRE = '¿Cuál es su tipo de sangre?';

    public const LABEL_PESO_ESTATURA = 'Cuál es su peso y estatura';

    public const LABEL_PESO = 'Peso (libras)';

    public const LABEL_ESTATURA = 'Estatura (metros)';

    public const LABEL_DEPORTE = '¿Practica algún deporte?';

    public const LABEL_TRATAMIENTO_MEDICO = '¿Actualmente está en algún tratamiento médico?';

    public const LABEL_HOSPITALIZACIONES = 'Ha sido operad@ u hospitalizad@ alguna vez? ¿Por qué motivo? ¿Hace cuánto tiempo fue?';

    public const LABEL_TATUAJES_PERFORACIONES = '¿Tiene tatuajes o perforaciones? ¿Cuantos? ¿Ubicación? ¿Significado?';

    public const LABEL_SUICIDIO = '¿Ha intentado suicidarse alguna vez? ¿Por qué motivo?';

    public const LABEL_AUSENCIAS_ENFERMEDAD = 'En el último año ¿cuantas veces faltó al trabajo por enfermedad?';

    public const LABEL_ALERGIAS = '¿Padece alergias?';

    public const LABEL_EMBARAZADA = '¿Está embarazada?';

    public const INTRO_SUSTANCIAS =
        'Las estadísticas muestran que cerca del 90% de las personas han experimentado o tenido contacto con algún tipo de droga ilegal; por lo cual es muy común que hayamos tenido algún tipo de acercamientocon alguna. Encierre todas las que usted conoce, ha experimentado o usado eventualmente, incluso si solo fue una vez.';

    /** @var array<string, string> */
    public const HABITOS = [
        'habito_tiempo_libre' => '¿Qué hace en sus tiempos libres?',
        'habito_bares_frecuencia' => '¿A cada cuanto visita bares o discotecas?',
        'habito_alcohol_ultimo' => '¿Cuándo fue la última vez que consumió bebidas alcohólicas? ¿Qué y cuánto consumió?',
        'habito_alcohol_mensual' => '¿Cuantas veces consume bebidas alcohólicas al mes?',
        'habito_alcohol_detenido' => '¿Cuándo fue la última vez que estuvo detenido por consumir bebidas alcohólicas?',
        'habito_alcohol_laboral' => '¿En el ultima año, cuantas veces se presentó a laborar en estado de ebriedad o resaca?',
        'habito_alcohol_despido' => '¿En qué empleo fue despedido por excederse en el consumo de alcohol?',
        'habito_tabaco' => '¿Con qué frecuencia fuma?',
        'habito_juegos_azar' => '¿Qué juegos de azar practica? ¿Con qué frecuencia?',
    ];

    /** @var array<string, string> */
    public const SUSTANCIAS_PREGUNTAS = [
        'sustancia_experiencia' => '¿Cómo ha sido su experiencia?',
        'sustancia_ultima_vez' => '¿Cuándo fue la última vez que experimento?',
        'sustancia_ultimos_6_meses' => '¿En los últimos 6 meses cuantas veces consumió?',
        'sustancia_familiar_consume' => '¿Tiene algún amigo o familiar que las consuma?',
        'sustancia_consumo_frente' => '¿Cuándo fue la última vez que consumieron frente a usted?',
        'sustancia_guardo_transporto' => '¿Cuándo fue la última vez que guardó, transportó o vendió alguna droga ilegal?',
        'sustancia_mejora_animo' => 'Alguna de ellas le ayuda a mejorar su salud o estado de ánimo? Cuál?',
    ];

    /** @var array<string, string> */
    public const ESTADOS_GENERAL = [
        'excelente' => 'Excelente',
        'buena' => 'Buena',
        'regular' => 'Regular',
        'mala' => 'Mala',
    ];

    /** @return array<string, mixed> */
    public static function reglasValidacion(): array
    {
        return array_merge([
            'salud_preocupaciones' => 'required|string|max:2000',
            'salud_estado_general' => 'required|in:excelente,buena,regular,mala,bueno,malo',
            'salud_atencion_psicologica' => 'required|in:si,no',
            'salud_detalle_psicologica' => 'nullable|required_if:salud_atencion_psicologica,si|string|max:2000',
            'salud_tipo_sangre' => 'required|string|max:10',
            'salud_peso' => 'required|numeric|min:20|max:500',
            'salud_estatura' => 'required|numeric|min:1|max:2.5',
            'salud_practica_deporte' => 'required|in:si,no',
            'salud_detalle_deporte' => 'nullable|string|max:500',
            'salud_tratamiento_medico' => 'required|in:si,no',
            'salud_detalle_tratamiento' => 'nullable|required_if:salud_tratamiento_medico,si|string|max:2000',
            'salud_hospitalizaciones' => 'required|in:si,no',
            'salud_detalle_hospitalizaciones' => 'nullable|required_if:salud_hospitalizaciones,si|string|max:2000',
            'salud_ausencias_enfermedad' => 'required|string|max:2000',
            'salud_intento_suicidio' => 'required|string|max:2000',
            'tiene_tatuajes' => 'required|in:si,no',
            'tiene_perforaciones' => 'nullable|in:si,no',
            'habito_tiempo_libre' => 'required|string|max:1000',
            'habito_bares_frecuencia' => 'required|string|max:500',
            'habito_alcohol_ultimo' => 'required|string|max:500',
            'habito_alcohol_mensual' => 'required|string|max:500',
            'habito_alcohol_detenido' => 'required|string|max:500',
            'habito_alcohol_laboral' => 'required|string|max:500',
            'habito_alcohol_despido' => 'required|string|max:500',
            'habito_tabaco' => 'required|string|max:500',
            'habito_juegos_azar' => 'required|string|max:500',
            'sustancias_usadas' => 'nullable|array',
            'sustancias_usadas.*' => 'string|in:ninguna,marihuana,cocaina,heroina,lsc,metanfetaminas,popper,hongos,cristal,opio,otras',
            'sustancia_experiencia' => 'nullable|string|max:2000',
            'sustancia_ultima_vez' => 'nullable|string|max:2000',
            'sustancia_ultimos_6_meses' => 'nullable|string|max:2000',
            'sustancia_familiar_consume' => 'nullable|string|max:2000',
            'sustancia_consumo_frente' => 'nullable|string|max:2000',
            'sustancia_guardo_transporto' => 'nullable|string|max:2000',
            'sustancia_mejora_animo' => 'nullable|string|max:2000',
            // Campos legacy/spec — no en PDF pre-empleo ago-2025
            'salud_situacion_emocional' => 'nullable|string|max:2000',
            'salud_detalle_emocional' => 'nullable|string|max:2000',
            'salud_ideacion_dano' => 'nullable|in:si,no',
            'salud_detalle_ideacion' => 'nullable|string|max:2000',
            'salud_detalle_ausencias' => 'nullable|string|max:2000',
        ], self::reglasAlergiasEmbarazo(), self::reglasValidacionLegacy());
    }

    /**
     * M-F2/F3: las dos preguntas van en todos los formularios (peri/espe solo estas de salud).
     *
     * @return array<string, mixed>
     */
    public static function reglasAlergiasEmbarazo(): array
    {
        return [
            'salud_alergias' => 'required|in:si,no',
            'salud_detalle_alergias' => 'nullable|required_if:salud_alergias,si|string|max:2000',
            'salud_embarazada' => 'required|in:si,no',
        ];
    }

    /** @return list<array{key: string, label: string}> */
    public static function preguntasAlergiasEmbarazo(): array
    {
        return [
            ['key' => 'salud_alergias', 'label' => self::LABEL_ALERGIAS],
            ['key' => 'salud_detalle_alergias', 'label' => 'Detalle de alergias'],
            ['key' => 'salud_embarazada', 'label' => self::LABEL_EMBARAZADA],
        ];
    }

    /** Compatibilidad con respuestas almacenadas con campos anteriores. */
    private static function reglasValidacionLegacy(): array
    {
        return [
            'habito_alcohol_frecuencia' => 'nullable|in:nunca,ocasional,regular,frecuente',
            'habito_alcohol_excesos' => 'nullable|in:si,no',
            'sustancias_complemento' => 'nullable|string|max:2000',
        ];
    }

    /** @var array<string, string> */
    public const SUSTANCIAS = [
        'marihuana' => 'Marihuana',
        'cocaina' => 'Cocaína',
        'heroina' => 'Heroina',
        'lsc' => 'LSC',
        'metanfetaminas' => 'Metanfetaminas',
        'popper' => 'Popper',
        'hongos' => 'Hongos',
        'cristal' => 'Cristal',
        'opio' => 'Opio',
        'otras' => 'Otras',
        'ninguna' => 'Ninguna',
    ];

    /** @param  list<string>|string|null  $input */
    public static function sustanciasParaAlmacenar(array|string|null $input): string
    {
        if (is_string($input)) {
            return $input;
        }

        if (! is_array($input) || $input === []) {
            return '';
        }

        return implode(',', array_values(array_filter($input, fn ($v) => is_string($v) && $v !== '')));
    }

    /** @return list<string> */
    public static function sustanciasDesdeAlmacenamiento(mixed $valor): array
    {
        if (is_array($valor)) {
            return array_values(array_filter($valor, fn ($v) => is_string($v) && $v !== ''));
        }

        if (! is_string($valor) || trim($valor) === '') {
            return [];
        }

        if (str_starts_with(trim($valor), '[')) {
            $decoded = json_decode($valor, true);

            return is_array($decoded) ? array_values(array_filter($decoded, fn ($v) => is_string($v) && $v !== '')) : [];
        }

        return array_values(array_filter(explode(',', $valor), fn ($v) => $v !== ''));
    }

    public static function normalizarEstadoGeneral(?string $valor): string
    {
        return match ($valor) {
            'bueno' => 'buena',
            'malo' => 'mala',
            default => (string) $valor,
        };
    }

    /** @return array<string, string> */
    public static function mensajesValidacion(): array
    {
        return [
            'salud_preocupaciones.required' => 'Indique el problema personal más serio que tiene actualmente.',
            'salud_estado_general.required' => 'Seleccione su condición general de salud.',
            'salud_tipo_sangre.required' => 'Indique su tipo de sangre.',
            'salud_detalle_psicologica.required_if' => 'Amplíe la información sobre el tratamiento psicológico o psiquiátrico.',
            'salud_detalle_tratamiento.required_if' => 'Describa el tratamiento médico actual.',
            'salud_detalle_hospitalizaciones.required_if' => 'Describa las hospitalizaciones o cirugías.',
            'salud_ausencias_enfermedad.required' => 'Indique cuántas veces faltó al trabajo por enfermedad en el último año.',
            'salud_intento_suicidio.required' => 'Indique si ha intentado suicidarse alguna vez y el motivo.',
            'salud_alergias.required' => 'Indique si padece alergias.',
            'salud_detalle_alergias.required_if' => 'Describa las alergias que padece.',
            'salud_embarazada.required' => 'Indique si está embarazada. Si no aplica, seleccione No.',
        ];
    }
}
