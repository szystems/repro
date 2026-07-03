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
            array_column(AntecedentesJudiciales::PREGUNTAS, 'key'),
            [
                'salud_preocupaciones', 'salud_estado_general', 'salud_atencion_psicologica',
                'salud_detalle_psicologica', 'salud_situacion_emocional', 'salud_ideacion_dano',
                'salud_detalle_ideacion', 'salud_tipo_sangre', 'salud_peso', 'salud_estatura',
                'salud_practica_deporte', 'salud_detalle_deporte', 'salud_tratamiento_medico',
                'salud_detalle_tratamiento', 'salud_hospitalizaciones', 'salud_detalle_hospitalizaciones',
                'salud_ausencias_enfermedad', 'salud_detalle_ausencias',
                'habito_tiempo_libre', 'habito_alcohol_frecuencia', 'habito_alcohol_ultimo',
                'habito_alcohol_excesos', 'habito_alcohol_laboral', 'habito_tabaco', 'habito_juegos_azar',
                'sustancias_complemento',
                'econ_tipo_vivienda_detalle', 'econ_monto_alquiler', 'econ_ingresos_adicionales_detalle',
                'econ_posee_propiedades', 'econ_detalle_propiedades', 'econ_posee_vehiculos',
                'econ_detalle_vehiculos', 'econ_pretension_salarial', 'econ_gastos_mensuales_aprox',
                'econ_tiene_fiador', 'econ_detalle_fiador', 'econ_problemas_bancarios',
                'econ_detalle_problemas_bancarios', 'econ_demandas_deudas', 'econ_detalle_demandas',
                'econ_problemas_sat', 'econ_detalle_sat',
            ]
        );
    }

    public static function esInterno(string $campo): bool
    {
        return in_array($campo, self::claves(), true)
            || str_starts_with($campo, 'integridad_')
            || str_starts_with($campo, 'judicial_')
            || str_starts_with($campo, 'salud_')
            || str_starts_with($campo, 'habito_')
            || str_starts_with($campo, 'econ_');
    }
}
