<?php

namespace App\Support;

/** E2.13–2.16 — Salud, hábitos y sustancias (campos internos). */
class SaludHabitosCampos
{
    /** @return array<string, mixed> */
    public static function reglasValidacion(): array
    {
        return [
            'salud_preocupaciones' => 'required|string|max:2000',
            'salud_estado_general' => 'required|in:excelente,bueno,regular,malo',
            'salud_atencion_psicologica' => 'required|in:si,no',
            'salud_detalle_psicologica' => 'nullable|required_if:salud_atencion_psicologica,si|string|max:2000',
            'salud_situacion_emocional' => 'required|string|max:1000',
            'salud_ideacion_dano' => 'required|in:si,no',
            'salud_detalle_ideacion' => 'nullable|required_if:salud_ideacion_dano,si|string|max:2000',
            'salud_tipo_sangre' => 'required|string|max:10',
            'salud_peso' => 'required|numeric|min:20|max:300',
            'salud_estatura' => 'required|numeric|min:1|max:2.5',
            'salud_practica_deporte' => 'required|in:si,no',
            'salud_detalle_deporte' => 'nullable|string|max:500',
            'salud_tratamiento_medico' => 'required|in:si,no',
            'salud_detalle_tratamiento' => 'nullable|required_if:salud_tratamiento_medico,si|string|max:2000',
            'salud_hospitalizaciones' => 'required|in:si,no',
            'salud_detalle_hospitalizaciones' => 'nullable|required_if:salud_hospitalizaciones,si|string|max:2000',
            'salud_ausencias_enfermedad' => 'required|in:si,no',
            'salud_detalle_ausencias' => 'nullable|required_if:salud_ausencias_enfermedad,si|string|max:2000',
            'tiene_tatuajes' => 'required|in:si,no',
            'tiene_perforaciones' => 'required|in:si,no',
            'habito_tiempo_libre' => 'required|string|max:1000',
            'habito_alcohol_frecuencia' => 'required|in:nunca,ocasional,regular,frecuente',
            'habito_alcohol_ultimo' => 'nullable|string|max:200',
            'habito_alcohol_excesos' => 'required|in:si,no',
            'habito_alcohol_laboral' => 'required|in:si,no',
            'habito_tabaco' => 'required|in:si,no',
            'habito_juegos_azar' => 'required|in:si,no',
            'sustancias_usadas' => 'nullable|array',
            'sustancias_usadas.*' => 'string|in:ninguna,marihuana,cocaina,anfetaminas,opioides,alucinogenos,otras',
            'sustancias_complemento' => 'nullable|string|max:2000',
        ];
    }

    /** @var array<string, string> */
    public const SUSTANCIAS = [
        'ninguna' => 'Ninguna',
        'marihuana' => 'Marihuana',
        'cocaina' => 'Cocaína',
        'anfetaminas' => 'Anfetaminas',
        'opioides' => 'Opioides',
        'alucinogenos' => 'Alucinógenos',
        'otras' => 'Otras',
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

    /** @return array<string, string> */
    public static function mensajesValidacion(): array
    {
        return [
            'salud_preocupaciones.required' => 'Indique sus preocupaciones de salud.',
            'salud_estado_general.required' => 'Seleccione su estado general de salud.',
            'salud_tipo_sangre.required' => 'Indique su tipo de sangre.',
            'salud_detalle_psicologica.required_if' => 'Describa la atención psicológica que ha recibido.',
            'salud_detalle_ideacion.required_if' => 'Describa la situación relacionada con pensamientos de hacerse daño o dañar a otros.',
            'salud_detalle_tratamiento.required_if' => 'Describa el tratamiento médico que recibe o recibió.',
            'salud_detalle_hospitalizaciones.required_if' => 'Describa las hospitalizaciones que ha tenido.',
            'salud_detalle_ausencias.required_if' => 'Describa las ausencias por enfermedad en el trabajo o estudios.',
        ];
    }
}
