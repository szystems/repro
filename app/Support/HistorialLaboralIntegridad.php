<?php

namespace App\Support;

/**
 * E2.10 — Preguntas complementarias laborales (internas).
 *
 * Orden y redacción literales de POLIGRAFO PRESENCIAL (2).pdf (ago-2025).
 * Las claves integridad_01…19 siguen el orden del PDF (1→19); respuestas
 * guardadas con el orden anterior pueden quedar desalineadas semánticamente.
 */
class HistorialLaboralIntegridad
{
    public const LABEL_EXPERIENCIA_PREVIA =
        '¿Posee experiencia laboral previa, incluyendo empleos formales, informales, temporales, independientes, prácticas, pasantías o apoyo en negocios familiares?';

    public const LABEL_OBSERVACIONES_LABORALES =
        'Si desea agregar alguna información laboral, lo puede hacer en este espacio: (lagunas de tiempo, ampliación del motivo de retiro, complementos, entre otros…)';

    public const TITULO_BLOQUE = 'Preguntas complementarias laborales';

    /** @var list<array{key: string, label: string}> */
    public const PREGUNTAS = [
        ['key' => 'integridad_01', 'label' => '¿Cuál fue el problema más serio que tuvo en sus empleos? ¿Cómo lo resolvió?'],
        ['key' => 'integridad_02', 'label' => '¿Ha trabajado en alguna corporación policial o militar? ¿Cuál?'],
        ['key' => 'integridad_03', 'label' => '¿En el último año, cuantas veces estuvo ausente en su empleo?'],
        ['key' => 'integridad_04', 'label' => '¿Manejó efectivo en sus empleos? ¿Cuánto fue el monto máximo?'],
        ['key' => 'integridad_05', 'label' => '¿Cuál fue el faltante más grande que tuvo? ¿Cómo lo resolvió?'],
        ['key' => 'integridad_06', 'label' => '¿Cuál fue el sobrante más grande que tuvo en sus empleos?'],
        ['key' => 'integridad_07', 'label' => '¿Cuántas veces alteró documentos o facturas en sus empleos?'],
        ['key' => 'integridad_08', 'label' => '¿Cuándo llamemos a pedir referencias en sus empleos ¿cree que alguien vaya a recomendarlo mal?'],
        ['key' => 'integridad_09', 'label' => '¿Cuál ha sido la cantidad máxima que se ha quedado de producto sobrante o promocional de sus empleos?'],
        ['key' => 'integridad_10', 'label' => '¿Cuál fue el soborno más grande que aceptó en sus empleos?'],
        ['key' => 'integridad_11', 'label' => '¿En qué empleo le acusaron de deshonestidad?'],
        ['key' => 'integridad_12', 'label' => '¿Con justificación tomó sin autorización dinero, producto en sus empleos?'],
        ['key' => 'integridad_13', 'label' => '¿Cuánto tendría que pagar por lo que ha tomado en sus empleos?'],
        ['key' => 'integridad_14', 'label' => '¿Cuántas actas administrativas le fueron impuestas en sus empleos? y ¿Cuál fue el motivo?'],
        ['key' => 'integridad_15', 'label' => '¿Algún compañero le enseñó a como robar en sus empleos?'],
        ['key' => 'integridad_16', 'label' => '¿Cuántas veces no reportó a algún compañero por pena o por no meterse en problemas?'],
        ['key' => 'integridad_17', 'label' => '¿Alguna vez abandonó algún empleo sin previo aviso? ¿cuál fue?'],
        ['key' => 'integridad_18', 'label' => '¿Tuvo necesidad alguna vez de prestar dinero sin autorización o sin permiso en sus empleos?'],
        ['key' => 'integridad_19', 'label' => '¿Qué empleo está omitiendo porque pudiera afectar su proceso de contratación actual?'],
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
            $mensajes[$pregunta['key'].'.required'] = 'Debe responder la pregunta complementaria laboral #'.($i + 1).'.';
        }

        return $mensajes;
    }
}
