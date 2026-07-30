<?php

namespace App\Support;

/** E2.13–2.16 — Salud, hábitos y sustancias (campos internos). */
class SaludHabitosCampos
{
    public const TITULO_SALUD = 'Aspectos de salud';

    public const TITULO_HABITOS = 'Hábitos personales';

    public const TITULO_SUSTANCIAS = 'Vínculo con actividades delictivas y drogas ilegales';

    public const INTRO_SUSTANCIAS =
        'Las estadísticas muestran que cerca del 90% de las personas han experimentado o tenido contacto con algún tipo de droga ilegal; por lo cual es muy común que hayamos tenido algún tipo de acercamiento con alguna. Encierre todas las que usted conoce, ha experimentado o usado eventualmente, incluso si solo fue una vez.';

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
            'salud_situacion_emocional' => 'required|string|max:2000',
            'salud_detalle_emocional' => 'nullable|string|max:2000',
            'salud_ideacion_dano' => 'required|in:si,no',
            'salud_detalle_ideacion' => 'nullable|required_if:salud_ideacion_dano,si|string|max:2000',
            'salud_tipo_sangre' => 'required|string|max:10',
            'salud_peso' => 'required|numeric|min:20|max:500',
            'salud_estatura' => 'required|numeric|min:1|max:2.5',
            'salud_practica_deporte' => 'required|in:si,no',
            'salud_detalle_deporte' => 'nullable|string|max:500',
            'salud_tratamiento_medico' => 'required|in:si,no',
            'salud_detalle_tratamiento' => 'nullable|required_if:salud_tratamiento_medico,si|string|max:2000',
            'salud_hospitalizaciones' => 'required|in:si,no',
            'salud_detalle_hospitalizaciones' => 'nullable|required_if:salud_hospitalizaciones,si|string|max:2000',
            'salud_ausencias_enfermedad' => 'required|in:si,no',
            'salud_detalle_ausencias' => 'nullable|required_if:salud_ausencias_enfermedad,si|string|max:2000',
            'salud_intento_suicidio' => 'required|string|max:2000',
            'tiene_tatuajes' => 'required|in:si,no',
            'tiene_perforaciones' => 'required|in:si,no',
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
        ], self::reglasValidacionLegacy());
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
        'heroina' => 'Heroína',
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
            'salud_preocupaciones.required' => 'Indique el problema personal o situación que le genera mayor preocupación.',
            'salud_estado_general.required' => 'Seleccione su estado general de salud.',
            'salud_tipo_sangre.required' => 'Indique su tipo de sangre.',
            'salud_detalle_psicologica.required_if' => 'Amplíe la información sobre la atención psicológica recibida.',
            'salud_detalle_ideacion.required_if' => 'Amplíe la información sobre pensamientos de hacerse daño.',
            'salud_detalle_tratamiento.required_if' => 'Describa el tratamiento médico actual.',
            'salud_detalle_hospitalizaciones.required_if' => 'Describa las hospitalizaciones o cirugías.',
            'salud_detalle_ausencias.required_if' => 'Explique las ausencias por enfermedad en el último año.',
            'salud_intento_suicidio.required' => 'Indique si ha intentado suicidarse alguna vez y el motivo.',
        ];
    }
}
