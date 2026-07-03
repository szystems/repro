<?php

namespace App\Support;

/** Etiquetas legibles para mensajes de validación del cuestionario pre-empleo. */
class CuestionarioValidacionLabels
{
    /** @return array<string, string> */
    public static function atributos(): array
    {
        return [
            'ultimo_nivel_academico' => 'último nivel académico',
            'formacion_academica' => 'formación académica',
            'econ_posee_propiedades' => '¿posee propiedades?',
            'econ_detalle_propiedades' => 'detalle de propiedades',
            'econ_posee_vehiculos' => '¿posee vehículos?',
            'econ_detalle_vehiculos' => 'detalle de vehículos',
            'econ_problemas_bancarios' => '¿problemas bancarios?',
            'econ_detalle_problemas_bancarios' => 'detalle de problemas bancarios',
            'econ_problemas_sat' => '¿problemas con SAT?',
            'econ_detalle_sat' => 'detalle de problemas con SAT',
            'econ_demandas_deudas' => '¿demandas por deudas?',
            'econ_detalle_demandas' => 'detalle de demandas por deudas',
            'econ_tiene_fiador' => '¿tiene fiador?',
            'econ_detalle_fiador' => 'detalle del fiador',
            'econ_pretension_salarial' => 'pretensión salarial',
            'salud_atencion_psicologica' => '¿atención psicológica?',
            'salud_detalle_psicologica' => 'detalle de atención psicológica',
            'salud_ideacion_dano' => '¿pensamientos de daño?',
            'salud_detalle_ideacion' => 'detalle sobre pensamientos de daño',
            'salud_tratamiento_medico' => '¿tratamiento médico?',
            'salud_detalle_tratamiento' => 'detalle del tratamiento médico',
            'salud_hospitalizaciones' => '¿hospitalizaciones?',
            'salud_detalle_hospitalizaciones' => 'detalle de hospitalizaciones',
            'salud_ausencias_enfermedad' => '¿ausencias por enfermedad?',
            'salud_detalle_ausencias' => 'detalle de ausencias por enfermedad',
            'salud_practica_deporte' => '¿practica deporte?',
            'salud_detalle_deporte' => 'detalle del deporte que practica',
            'salud_preocupaciones' => 'preocupaciones de salud',
            'salud_estado_general' => 'estado general de salud',
            'salud_tipo_sangre' => 'tipo de sangre',
            'salud_peso' => 'peso',
            'salud_estatura' => 'estatura',
            'salud_situacion_emocional' => 'situación emocional',
            'habito_tiempo_libre' => 'actividades en tiempo libre',
            'habito_alcohol_frecuencia' => 'frecuencia de consumo de alcohol',
            'tiene_tatuajes' => '¿tiene tatuajes?',
            'tiene_perforaciones' => '¿tiene perforaciones?',
            'tatuajes' => 'tatuajes',
            'perforaciones' => 'perforaciones',
            'deudas' => 'deudas',
            'empleos' => 'empleos anteriores',
            'expareja_detalle_problemas' => 'detalle de problemas legales con expareja',
        ];
    }
}
