<?php

namespace App\Support;

/**
 * E5.2 — Preguntas complementarias laborales periódicas (26, internas).
 *
 * Textos tomados de CREACIÓN FORMULARIOS DE SISTEMA.pdf (jun-2026), cuadro
 * «Preguntas complementarias» del proceso periódico VSA/Polígrafo.
 */
class HistorialLaboralPeriodico
{
    /** @var list<array{key: string, label: string}> */
    public const PREGUNTAS = [
        ['key' => 'periodico_01', 'label' => 'Describa de forma detallada el motivo por el cual se está realizando esta prueba:'],
        ['key' => 'periodico_02', 'label' => 'Describa su experiencia en general de su empleo actual:'],
        ['key' => 'periodico_03', 'label' => '¿Cuánto tiempo más piensa seguir laborando en la empresa?'],
        ['key' => 'periodico_04', 'label' => '¿Está de acuerdo con las condiciones laborales que la empresa le ofrece?'],
        ['key' => 'periodico_05', 'label' => '¿Qué cambios o mejoras sugiera a la empresa en cuanto a sus procesos operativos y de seguridad?'],
        ['key' => 'periodico_06', 'label' => '¿Cuáles son sus metas laborales?'],
        ['key' => 'periodico_07', 'label' => '¿Cómo considera la relación que lleva con sus compañeros de trabajo?'],
        ['key' => 'periodico_08', 'label' => '¿Tiene algún problema con sus compañeros de trabajo actualmente? Explique'],
        ['key' => 'periodico_09', 'label' => '¿Cuál ha sido el problema más serio que ha tenido en este empleo? ¿Cómo lo resolvió?'],
        ['key' => 'periodico_10', 'label' => '¿Maneja efectivo o producto? ¿Cuánto ha sido el monto o la cantidad máxima?'],
        ['key' => 'periodico_11', 'label' => '¿Cuál fue el faltante más grande que tuvo? ¿Cómo lo resolvió?'],
        ['key' => 'periodico_12', 'label' => '¿Qué documentos o facturas ha tenido necesidad de alterar para no meterse en problemas?'],
        ['key' => 'periodico_13', 'label' => '¿Con justificación ha tomado dinero o producto?'],
        ['key' => 'periodico_14', 'label' => '¿Cuál ha sido la cantidad máxima que se ha quedado de producto sobrante o promocional?'],
        ['key' => 'periodico_15', 'label' => '¿Cuánto tendría que pagar por lo que ha tomado en su empleo actual?'],
        ['key' => 'periodico_16', 'label' => 'En el último año ¿Cuántas actas administrativas le fueron impuestas en sus empleos? y ¿Cuál fue el motivo?'],
        ['key' => 'periodico_17', 'label' => '¿Sospecha de que sus compañeros de trabajo estén haciendo algo indebido hacia la empresa?'],
        ['key' => 'periodico_18', 'label' => '¿Cuántas veces no ha reportado a algún compañero por pena o por no meterse en problemas?'],
        ['key' => 'periodico_19', 'label' => '¿Qué actividades ilegales han realizado sus compañeros de trabajo?'],
        ['key' => 'periodico_20', 'label' => '¿Qué hacen sus compañeros para que no lo descubran en algún mal procedimiento que realicen?'],
        ['key' => 'periodico_21', 'label' => '¿Algún compañero le enseñó a cómo robar en su empleo actual?'],
        ['key' => 'periodico_22', 'label' => '¿Alguna vez lo han amenazado sus compañeros?'],
        ['key' => 'periodico_23', 'label' => '¿Qué cree que debe sucederle a alguien que lo encuentren robando en sus empleos?'],
        ['key' => 'periodico_24', 'label' => '¿Le daría otra oportunidad?'],
        ['key' => 'periodico_25', 'label' => '¿Cuántas veces los clientes de la empresa lo han sobornado?'],
        ['key' => 'periodico_26', 'label' => '¿Alguien le ha pedido información confidencial de la empresa?'],
    ];

    /** Cierre laboral (PDF #26 — campo adicional tras las preguntas numeradas). */
    public const CAMPO_INFORMACION_ADICIONAL = [
        'key' => 'periodico_info_adicional',
        'label' => 'Desea agregar alguna información laboral:',
    ];

    /** E5.5 — Pregunta 1 en Específica: espacio amplio del caso/hecho. */
    public const LABEL_PREGUNTA_1_ESPECIFICA = 'Describa de forma detallada el motivo por el cual se está realizando esta prueba. Circunstancias, fechas, personas involucradas y cualquier información que considere relevante:';

    /** @return list<string> */
    public static function claves(): array
    {
        return array_merge(
            array_column(self::PREGUNTAS, 'key'),
            [self::CAMPO_INFORMACION_ADICIONAL['key']]
        );
    }

    /** @return list<array{key: string, label: string}> */
    public static function preguntasDesdeLaSegunda(): array
    {
        return array_values(array_slice(self::PREGUNTAS, 1));
    }

    public static function labelPregunta1(bool $esEspecifica = false): string
    {
        return $esEspecifica ? self::LABEL_PREGUNTA_1_ESPECIFICA : self::PREGUNTAS[0]['label'];
    }

    /** @return array<string, mixed> */
    public static function reglasValidacion(bool $esEspecifica = false): array
    {
        $reglas = [];
        foreach (self::PREGUNTAS as $pregunta) {
            $reglas[$pregunta['key']] = 'required|string|max:2000';
        }

        $reglas[self::CAMPO_INFORMACION_ADICIONAL['key']] = 'required|string|max:2000';

        if ($esEspecifica) {
            $reglas['periodico_01'] = 'required|string|min:20|max:8000';
        }

        return $reglas;
    }

    /** @return array<string, string> */
    public static function mensajesValidacion(): array
    {
        $mensajes = [];
        foreach (self::PREGUNTAS as $i => $pregunta) {
            $mensajes[$pregunta['key'].'.required'] = 'Debe responder la pregunta laboral #'.($i + 1).'.';
        }
        $mensajes[self::CAMPO_INFORMACION_ADICIONAL['key'].'.required'] =
            'Indique si desea agregar información laboral (puede escribir N/A).';
        $mensajes['periodico_01.min'] =
            'Describa el caso o hecho con suficiente detalle (circunstancias, fechas y personas involucradas).';

        return $mensajes;
    }
}
