<?php

namespace App\Support;

/**
 * E5.2 — Preguntas complementarias laborales periódica/específica (internas).
 *
 * Orden y redacción literales de PERIODICO ESPECIFICO.pdf (ago-2025).
 */
class HistorialLaboralPeriodico
{
    /** @var list<array{key: string, label: string}> */
    public const PREGUNTAS = [
        ['key' => 'periodico_01', 'label' => 'Describa de forma detallada el motivo por el cual se está realizando está prueba:'],
        ['key' => 'periodico_02', 'label' => 'Describa su experiencia en general de su empleo actual:'],
        ['key' => 'periodico_03', 'label' => '¿Cuanto tiempo piensa seguir laborando en la empresa?'],
        ['key' => 'periodico_04', 'label' => '¿Está de acuerdo con las condiciones laborales que la empresa le ofrece?'],
        ['key' => 'periodico_05', 'label' => '¿Qué cambios o mejoras sugiera a la empresa?'],
        ['key' => 'periodico_06', 'label' => '¿Cuáles son sus metas laborales?'],
        ['key' => 'periodico_07', 'label' => '¿Cuáles son sus metas personales?'],
        ['key' => 'periodico_08', 'label' => '¿Cómo considera la relación que lleva con sus compañeros de trabajo y superiores? ¿Tiene algún problema con ellos?'],
        ['key' => 'periodico_09', 'label' => '¿Cuál ha sido el problema más serio que ha tenido en este empleo? ¿Cómo lo resolvió?'],
        ['key' => 'periodico_10', 'label' => '¿Maneja efectivo o producto? ¿Cuánto ha sido el monto o la cantidad máxima?'],
        ['key' => 'periodico_11', 'label' => '¿Cuál fue el faltante más grande que tuvo? ¿Cómo lo resolvió?'],
        ['key' => 'periodico_12', 'label' => '¿Cuál fue el sobrante más grande que tuvo?'],
        ['key' => 'periodico_13', 'label' => 'Para evitarse problemas, ¿qué procedimiento interno de la empresa ha tenido la necesidad de alterar u omitir?'],
        ['key' => 'periodico_14', 'label' => '¿Qué docuemntos o facturas ha tenido que alterar?'],
        ['key' => 'periodico_15', 'label' => '¿Con justificación ha tomado sin autorización dinero o producto?'],
        ['key' => 'periodico_16', 'label' => '¿Cuál ha sido la cantidad máxima que se ha quedado de producto sobrante o promocional?'],
        ['key' => 'periodico_17', 'label' => '¿Cuánto tendría que pagar por lo que ha tomado en sus empleos?'],
        ['key' => 'periodico_18', 'label' => 'En el último año¿Cuántas actas administrativas le fueron impuestas en sus empleos? y ¿Cuál fue el motivo?'],
        ['key' => 'periodico_19', 'label' => '¿Sospecha de qué sus compañeros de trabajo este haciendo algo indebido hacia la empresa?'],
        ['key' => 'periodico_20', 'label' => '¿Cuántas veces no ha reportado a algún compañero por pena o por no meterse en problemas?'],
        ['key' => 'periodico_21', 'label' => '¿Qué actividades ilegales han realizado sus compañeros de trabajo en contra de la empresa?'],
        ['key' => 'periodico_22', 'label' => '¿Qué hacen su compañeros para que no lo descubran en algún mal procedimiento que realicen?'],
        ['key' => 'periodico_23', 'label' => '¿Algún compañero le enseñó a como robar en sus empleos?'],
        ['key' => 'periodico_24', 'label' => '¿Alguna vez lo han amenazado?'],
        ['key' => 'periodico_25', 'label' => '¿Qué cree que debe sucederle a alguien que lo encuentre robando en sus empleos?'],
        ['key' => 'periodico_26', 'label' => '¿Le daría otra oportunidad?'],
        ['key' => 'periodico_27', 'label' => '¿Cuántas veces los clientes de la empresa lo han sobornado?'],
        ['key' => 'periodico_28', 'label' => 'En los últimos 6 meses ¿Cuántas veces le han levantado actas administrativa o llamadas de atención en este empleo? ¿Por qué?'],
        ['key' => 'periodico_29', 'label' => '¿Cuántas veces ha realizado diligencias personales en horarios de trabajo?'],
        ['key' => 'periodico_30', 'label' => '¿Alguien le ha pedido información confidencial de la empresa?'],
        ['key' => 'periodico_31', 'label' => '¿Usted ha brindado información confidencial de la empresa?'],
    ];

    public const CAMPO_INFORMACION_ADICIONAL = [
        'key' => 'periodico_info_adicional',
        'label' => 'Desea agregar alguna información laboral:',
    ];

    /** Duplica el cuadro de empleo actual — omitir en periódica/específica (revisión cliente ago 2026). */
    public const CLAVES_OMITIDAS_EMPLEO_ACTUAL = ['periodico_02'];

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
    public static function preguntasVisibles(): array
    {
        return array_values(array_filter(
            self::PREGUNTAS,
            fn (array $pregunta): bool => ! in_array($pregunta['key'], self::CLAVES_OMITIDAS_EMPLEO_ACTUAL, true)
        ));
    }

    /** @return list<array{key: string, label: string}> */
    public static function preguntasDesdeLaSegunda(): array
    {
        return array_values(array_slice(self::preguntasVisibles(), 1));
    }

    public static function labelPregunta1(bool $esEspecifica = false): string
    {
        return $esEspecifica ? self::LABEL_PREGUNTA_1_ESPECIFICA : self::PREGUNTAS[0]['label'];
    }

    /** @return array<string, mixed> */
    public static function reglasValidacion(bool $esEspecifica = false): array
    {
        $reglas = [];
        foreach (self::preguntasVisibles() as $pregunta) {
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
        foreach (self::preguntasVisibles() as $i => $pregunta) {
            $mensajes[$pregunta['key'].'.required'] = 'Debe responder la pregunta laboral #'.($i + 1).'.';
        }
        $mensajes[self::CAMPO_INFORMACION_ADICIONAL['key'].'.required'] =
            'Indique si desea agregar información laboral (puede escribir N/A).';
        $mensajes['periodico_01.min'] =
            'Describa el caso o hecho con suficiente detalle (circunstancias, fechas y personas involucradas).';

        return $mensajes;
    }
}
