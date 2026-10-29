<?php

namespace App\Support;

use App\Models\EvaluadoOrden;

/**
 * Paquete de la sección Salud para el asistente.
 * Solo lo que el formulario consultó y la nota del evaluador.
 * Un campo vacío sigue vacío: no se convierte en «no» ni en «ninguna».
 * No incluye nombre, DPI, empresa ni fotos.
 */
class AsistenteRedaccionSalud
{
    /**
     * Campos que el formulario de salud pregunta hoy.
     * El detalle cuelga de la respuesta sí/no: si esa fue «no», el detalle vacío no es un faltante.
     *
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
    ];

    /**
     * Respuestas de formularios viejos. Solo entran si el candidato escribió algo.
     *
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
     * @return array{
     *     seccion: string,
     *     servicio: string,
     *     formulario: string,
     *     campos: list<array{clave: string, pregunta: string, valor: string, texto: string, vacio: bool, incompleto: bool}>,
     *     observacion_evaluador: string,
     *     texto_para_modelo: string
     * }
     */
    public static function paquete(EvaluadoOrden $evaluado): array
    {
        $evaluado->loadMissing('cuestionario');
        $respuestas = $evaluado->cuestionario?->obtenerRespuestasSeccion(5) ?? [];
        $notas = EvaluadorNotasSupport::mapaPorSeccion($evaluado->id);
        $observacion = trim((string) ($notas[InformeWordBloquesEvaluador::NOTA_SALUD] ?? ''));

        $campos = [];
        foreach (self::CONSULTADOS as $definicion) {
            $campos[] = self::campo($definicion, $respuestas, $campos);
        }

        foreach (self::LEGADO as $definicion) {
            $armado = self::campo($definicion + ['tipo' => 'texto'], $respuestas, $campos);
            if ($armado['vacio']) {
                continue;
            }
            $campos[] = $armado;
        }

        $paquete = [
            'seccion' => AsistenteRedaccion::SECCION_PILOTO,
            'servicio' => (string) ($evaluado->tipo_servicio ?? ''),
            'formulario' => (string) ($evaluado->tipo_formulario ?: $evaluado->cuestionario?->tipo_formulario ?: ''),
            'campos' => $campos,
            'observacion_evaluador' => $observacion,
            'texto_para_modelo' => '',
        ];
        $paquete['texto_para_modelo'] = self::textoParaModelo($paquete);

        return $paquete;
    }

    /**
     * @param  array{clave: string, pregunta: string, tipo?: string, depende_de?: string}  $definicion
     * @param  array<string, mixed>  $respuestas
     * @param  list<array{clave: string, pregunta: string, valor: string, texto: string, vacio: bool, incompleto: bool}>  $yaArmados
     * @return array{clave: string, pregunta: string, valor: string, texto: string, vacio: bool, incompleto: bool}
     */
    private static function campo(array $definicion, array $respuestas, array $yaArmados): array
    {
        $crudo = $respuestas[$definicion['clave']] ?? null;
        $valor = is_scalar($crudo) ? trim((string) $crudo) : '';
        $tipo = $definicion['tipo'] ?? 'texto';
        $texto = self::texto($tipo, $valor);
        $vacio = $texto === '';
        $incompleto = $vacio && ! self::detalleCubiertoPorNo($definicion, $yaArmados);

        return [
            'clave' => $definicion['clave'],
            'pregunta' => $definicion['pregunta'],
            'valor' => $valor,
            'texto' => $texto,
            'vacio' => $vacio,
            'incompleto' => $incompleto,
        ];
    }

    /**
     * @param  array{depende_de?: string}  $definicion
     * @param  list<array{clave: string, valor: string}>  $yaArmados
     */
    private static function detalleCubiertoPorNo(array $definicion, array $yaArmados): bool
    {
        $padre = $definicion['depende_de'] ?? '';
        if ($padre === '') {
            return false;
        }

        foreach ($yaArmados as $campo) {
            if ($campo['clave'] === $padre) {
                return $campo['valor'] === 'no';
            }
        }

        return false;
    }

    private static function texto(string $tipo, string $valor): string
    {
        if ($valor === '') {
            return '';
        }

        return match ($tipo) {
            'si_no' => match ($valor) {
                'si' => 'Sí',
                'no' => 'No',
                default => $valor,
            },
            'estado' => SaludHabitosCampos::ESTADOS_GENERAL[SaludHabitosCampos::normalizarEstadoGeneral($valor)] ?? $valor,
            default => $valor,
        };
    }

    /**
     * @param  array{servicio: string, formulario: string, campos: list<array{pregunta: string, texto: string, vacio: bool}>, observacion_evaluador: string}  $paquete
     */
    public static function textoParaModelo(array $paquete): string
    {
        $lineas = [
            'Sección: Salud',
            'Tipo de informe: '.trim($paquete['servicio'].' '.$paquete['formulario']),
            '',
            'Respuestas del formulario:',
        ];

        foreach ($paquete['campos'] as $campo) {
            $lineas[] = '- '.$campo['pregunta'].': '.($campo['vacio'] ? '(vacío)' : $campo['texto']);
        }

        $lineas[] = '';
        $lineas[] = 'Observación del evaluador:';
        $lineas[] = $paquete['observacion_evaluador'] !== ''
            ? $paquete['observacion_evaluador']
            : '(ninguna)';

        return implode("\n", $lineas);
    }
}
