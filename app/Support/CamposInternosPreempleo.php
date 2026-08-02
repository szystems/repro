<?php

namespace App\Support;

/** Marca campos internos del evaluador (no van auto al informe). */
class CamposInternosPreempleo
{
    /** @return list<string> */
    public static function claves(): array
    {
        return array_merge(
            HistorialLaboralIntegridad::claves(),
            HistorialLaboralPeriodico::claves(),
            array_column(AntecedentesJudiciales::PREGUNTAS, 'key'),
            [
                'salud_preocupaciones', 'salud_estado_general', 'salud_atencion_psicologica',
                'salud_detalle_psicologica', 'salud_situacion_emocional', 'salud_detalle_emocional',
                'salud_ideacion_dano',
                'salud_detalle_ideacion', 'salud_tipo_sangre', 'salud_peso', 'salud_estatura',
                'salud_practica_deporte', 'salud_detalle_deporte', 'salud_tratamiento_medico',
                'salud_detalle_tratamiento', 'salud_hospitalizaciones', 'salud_detalle_hospitalizaciones',
                'salud_ausencias_enfermedad', 'salud_detalle_ausencias', 'salud_intento_suicidio',
                'habito_tiempo_libre', 'habito_bares_frecuencia', 'habito_alcohol_ultimo',
                'habito_alcohol_mensual', 'habito_alcohol_detenido', 'habito_alcohol_laboral',
                'habito_alcohol_despido', 'habito_tabaco', 'habito_juegos_azar',
                'sustancia_experiencia', 'sustancia_ultima_vez', 'sustancia_ultimos_6_meses',
                'sustancia_familiar_consume', 'sustancia_consumo_frente', 'sustancia_guardo_transporto',
                'sustancia_mejora_animo', 'sustancias_complemento',
                'econ_tipo_vivienda_detalle', 'econ_monto_alquiler', 'econ_ingresos_adicionales_detalle',
                'econ_dependientes_detalle', 'econ_es_fiador', 'econ_detalle_es_fiador',
                'econ_posee_propiedades', 'econ_detalle_propiedades', 'econ_posee_vehiculos',
                'econ_detalle_vehiculos', 'econ_pretension_salarial', 'econ_gastos_mensuales_aprox',
                'econ_tiene_fiador', 'econ_detalle_fiador', 'econ_problemas_bancarios',
                'econ_detalle_problemas_bancarios', 'econ_demandas_deudas', 'econ_detalle_demandas',
                'econ_problemas_sat', 'econ_detalle_sat', 'econ_patrimonio_aprox',
                'viv_tipo_vivienda_detalle', 'viv_habitantes_detalle',
            ]
        );
    }

    public static function esInterno(string $campo): bool
    {
        return in_array($campo, self::claves(), true)
            || str_starts_with($campo, 'integridad_')
            || str_starts_with($campo, 'periodico_')
            || str_starts_with($campo, 'judicial_')
            || str_starts_with($campo, 'salud_')
            || str_starts_with($campo, 'habito_')
            || str_starts_with($campo, 'econ_')
            || str_starts_with($campo, 'sustancias_');
    }

    /** Campos de formulario/sistema que no deben aparecer en PDF ni portal. */
    public static function esCampoSistema(string $campo): bool
    {
        return in_array($campo, ['_token', 'action', 'token', 'observaciones_genericas'], true)
            || str_starts_with($campo, '_');
    }

    /**
     * @param  array<string, mixed>  $respuestas
     * @return array<string, mixed>
     */
    public static function excluirCamposSistema(array $respuestas): array
    {
        return array_filter(
            $respuestas,
            fn ($valor, string $campo) => ! self::esCampoSistema($campo),
            ARRAY_FILTER_USE_BOTH
        );
    }

    /**
     * E3.3 — Respuestas visibles para empresa / informe externo (Pre-empleo).
     *
     * @param  array<string, mixed>  $respuestas
     * @return array<string, mixed>
     */
    public static function filtrarRespuestasParaEmpresa(array $respuestas, string $tipoFormulario): array
    {
        if (! in_array($tipoFormulario, ['preempleo', 'socioeconomico', 'periodica', 'especifica'], true)) {
            return self::excluirCamposSistema($respuestas);
        }

        return array_filter(
            $respuestas,
            function ($valor, string $campo) use ($tipoFormulario) {
                if (self::esCampoSistema($campo) || self::esInterno($campo)) {
                    return false;
                }
                if ($tipoFormulario === 'socioeconomico' && (
                    str_starts_with($campo, 'viv_')
                    || $campo === 'referencias_vecinales'
                )) {
                    return false;
                }

                return true;
            },
            ARRAY_FILTER_USE_BOTH
        );
    }
}
