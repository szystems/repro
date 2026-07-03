<?php

namespace App\Support;

/** E2.10 — Preguntas complementarias de integridad (internas). */
class HistorialLaboralIntegridad
{
    /** @var list<array{key: string, label: string}> */
    public const PREGUNTAS = [
        ['key' => 'integridad_01', 'label' => '¿Ha mentido en algún currículum o entrevista de trabajo?'],
        ['key' => 'integridad_02', 'label' => '¿Ha ocultado información relevante a empleadores anteriores?'],
        ['key' => 'integridad_03', 'label' => '¿Ha falsificado documentos o certificaciones?'],
        ['key' => 'integridad_04', 'label' => '¿Ha tenido conflictos graves con compañeros o jefes?'],
        ['key' => 'integridad_05', 'label' => '¿Ha incumplido políticas importantes de alguna empresa?'],
        ['key' => 'integridad_06', 'label' => '¿Ha sustraído bienes o dinero de algún empleador?'],
        ['key' => 'integridad_07', 'label' => '¿Ha compartido información confidencial de empleadores?'],
        ['key' => 'integridad_08', 'label' => '¿Ha tenido problemas por asistencia o puntualidad?'],
        ['key' => 'integridad_09', 'label' => '¿Ha sido sancionado disciplinariamente en algún trabajo?'],
        ['key' => 'integridad_10', 'label' => '¿Ha renunciado abruptamente sin preaviso?'],
        ['key' => 'integridad_11', 'label' => '¿Ha trabajado simultáneamente para competidores sin declararlo?'],
        ['key' => 'integridad_12', 'label' => '¿Ha recibido sobornos o pagos indebidos en el trabajo?'],
        ['key' => 'integridad_13', 'label' => '¿Ha manipulado registros o reportes laborales?'],
        ['key' => 'integridad_14', 'label' => '¿Ha tenido demandas laborales en su contra?'],
        ['key' => 'integridad_15', 'label' => '¿Ha incumplido contratos de confidencialidad?'],
        ['key' => 'integridad_16', 'label' => '¿Ha usado recursos de la empresa para beneficio personal?'],
        ['key' => 'integridad_17', 'label' => '¿Ha tenido problemas por consumo de alcohol/drogas en el trabajo?'],
        ['key' => 'integridad_18', 'label' => '¿Ha ocultado antecedentes penales a empleadores?'],
        ['key' => 'integridad_19', 'label' => '¿Hay algún otro aspecto de integridad laboral que deba mencionar?'],
    ];

    /** @return list<string> */
    public static function claves(): array
    {
        return array_column(self::PREGUNTAS, 'key');
    }

    /** @return array<string, mixed> */
    public static function reglasValidacion(): array
    {
        $reglas = [];
        foreach (self::PREGUNTAS as $pregunta) {
            $reglas[$pregunta['key']] = 'required|string|max:2000';
        }

        return $reglas;
    }

    /** @return array<string, string> */
    public static function mensajesValidacion(): array
    {
        $mensajes = [];
        foreach (self::PREGUNTAS as $i => $pregunta) {
            $mensajes[$pregunta['key'].'.required'] = 'Debe responder la pregunta de integridad #'.($i + 1).'.';
        }

        return $mensajes;
    }
}
