<?php

namespace App\Support;

use App\Models\EvaluadoOrden;

/**
 * Paquete de la sección Salud.
 * Solo lo que el formulario consultó y la nota del evaluador.
 * No incluye nombre, DPI, empresa, hábitos ni el detalle de cada tatuaje.
 * Sí incluye si tiene o no tatuajes, para que el párrafo lo mencione.
 */
class AsistenteRedaccionSalud
{
    /**
     * @var list<array{clave: string, pregunta: string, tipo: string, depende_de?: string}>
     */
    private const CONSULTADOS = [
        ['clave' => 'salud_preocupaciones', 'pregunta' => SaludHabitosCampos::LABEL_PREOCUPACIONES, 'tipo' => 'texto'],
        ['clave' => 'salud_estado_general', 'pregunta' => SaludHabitosCampos::LABEL_ESTADO_GENERAL, 'tipo' => 'estado'],
        ['clave' => 'salud_atencion_psicologica', 'pregunta' => SaludHabitosCampos::LABEL_PSICOLOGICA, 'tipo' => 'si_no'],
        ['clave' => 'salud_detalle_psicologica', 'pregunta' => 'Detalle del tratamiento psicológico o psiquiátrico', 'tipo' => 'detalle', 'depende_de' => 'salud_atencion_psicologica'],
        ['clave' => 'salud_tipo_sangre', 'pregunta' => SaludHabitosCampos::LABEL_TIPO_SANGRE, 'tipo' => 'texto'],
        ['clave' => 'salud_peso', 'pregunta' => SaludHabitosCampos::LABEL_PESO, 'tipo' => 'texto'],
        ['clave' => 'salud_estatura', 'pregunta' => SaludHabitosCampos::LABEL_ESTATURA, 'tipo' => 'texto'],
        ['clave' => 'salud_practica_deporte', 'pregunta' => SaludHabitosCampos::LABEL_DEPORTE, 'tipo' => 'si_no'],
        ['clave' => 'salud_detalle_deporte', 'pregunta' => 'Detalle del deporte', 'tipo' => 'detalle', 'depende_de' => 'salud_practica_deporte'],
        ['clave' => 'salud_tratamiento_medico', 'pregunta' => SaludHabitosCampos::LABEL_TRATAMIENTO_MEDICO, 'tipo' => 'si_no'],
        ['clave' => 'salud_detalle_tratamiento', 'pregunta' => 'Detalle del tratamiento médico', 'tipo' => 'detalle', 'depende_de' => 'salud_tratamiento_medico'],
        ['clave' => 'salud_hospitalizaciones', 'pregunta' => SaludHabitosCampos::LABEL_HOSPITALIZACIONES, 'tipo' => 'si_no'],
        ['clave' => 'salud_detalle_hospitalizaciones', 'pregunta' => 'Detalle de hospitalizaciones o cirugías', 'tipo' => 'detalle', 'depende_de' => 'salud_hospitalizaciones'],
        ['clave' => 'salud_ausencias_enfermedad', 'pregunta' => SaludHabitosCampos::LABEL_AUSENCIAS_ENFERMEDAD, 'tipo' => 'texto'],
        ['clave' => 'salud_intento_suicidio', 'pregunta' => SaludHabitosCampos::LABEL_SUICIDIO, 'tipo' => 'texto'],
        ['clave' => 'salud_alergias', 'pregunta' => SaludHabitosCampos::LABEL_ALERGIAS, 'tipo' => 'si_no'],
        ['clave' => 'salud_detalle_alergias', 'pregunta' => 'Detalle de alergias', 'tipo' => 'detalle', 'depende_de' => 'salud_alergias'],
        ['clave' => 'salud_embarazada', 'pregunta' => SaludHabitosCampos::LABEL_EMBARAZADA, 'tipo' => 'si_no'],
        ['clave' => 'tiene_tatuajes', 'pregunta' => '¿Tiene tatuajes?', 'tipo' => 'si_no'],
    ];

    /**
     * @var list<array{clave: string, pregunta: string}>
     */
    private const LEGADO = [
        ['clave' => 'salud_situacion_emocional', 'pregunta' => 'Situación emocional'],
        ['clave' => 'salud_detalle_emocional', 'pregunta' => 'Detalle de la situación emocional'],
        ['clave' => 'salud_ideacion_dano', 'pregunta' => 'Ideación de daño'],
        ['clave' => 'salud_detalle_ideacion', 'pregunta' => 'Detalle de ideación'],
        ['clave' => 'salud_detalle_ausencias', 'pregunta' => 'Detalle de ausencias por enfermedad'],
    ];

    /**
     * @return array<string, mixed>
     */
    public static function paquete(EvaluadoOrden $evaluado, ?string $observacion = null): array
    {
        $evaluado->loadMissing('cuestionario');
        $respuestas = $evaluado->cuestionario?->obtenerRespuestasSeccion(5) ?? [];

        return AsistenteRedaccionCampos::cerrar(
            $evaluado,
            AsistenteRedaccion::SECCION_PILOTO,
            'Salud',
            AsistenteRedaccionCampos::lista(self::CONSULTADOS, $respuestas, self::LEGADO),
            $observacion
        );
    }
}
