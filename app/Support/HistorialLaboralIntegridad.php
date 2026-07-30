<?php

namespace App\Support;

/**
 * E2.10 — Preguntas complementarias laborales (internas).
 *
 * Textos alineados a POLIGRAFO PRESENCIAL (ago-2025) + CREACIÓN FORMULARIOS DE SISTEMA.pdf.
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
        ['key' => 'integridad_01', 'label' => '¿Ha trabajado en alguna corporación policial o militar? ¿Cuál?'],
        ['key' => 'integridad_02', 'label' => '¿Cuál fue la cantidad máxima de efectivo que manejó en sus empleos?'],
        ['key' => 'integridad_03', 'label' => '¿Cuál fue la cantidad máxima de producto, inventario o mercadería que tuvo bajo su responsabilidad en sus empleos?'],
        ['key' => 'integridad_04', 'label' => '¿Cuál fue el faltante más grande que tuvo y cómo lo resolvió?'],
        ['key' => 'integridad_05', 'label' => '¿Cuál fue el sobrante más grande que tuvo y cómo lo resolvió?'],
        ['key' => 'integridad_06', 'label' => '¿Cuál fue el problema más serio que tuvo en sus empleos y cómo lo resolvió?'],
        ['key' => 'integridad_07', 'label' => '¿Cuántas veces alteró documentos, registros o facturas en sus empleos para no meterse en problemas? Explique'],
        ['key' => 'integridad_08', 'label' => 'Cuando solicitemos referencias laborales, ¿considera que algún empleador o compañero podría brindar una referencia negativa sobre usted? ¿Por qué motivo?'],
        ['key' => 'integridad_09', 'label' => '¿Cuál ha sido la cantidad máxima de efectivo, producto, material promocional o recurso de la empresa que conservó para uso personal?'],
        ['key' => 'integridad_10', 'label' => '¿Cuál fue el soborno, beneficio o favor más grande que aceptó en sus empleos?'],
        ['key' => 'integridad_11', 'label' => '¿En qué empleo fue acusado de deshonestidad y cuál fue la situación?'],
        ['key' => 'integridad_12', 'label' => '¿Ha tomado alguna vez dinero, producto o recursos de una empresa sin autorización?'],
        ['key' => 'integridad_13', 'label' => 'Si tuviera que reponer dinero, producto o recursos tomados sin autorización, ¿a cuánto ascendería aproximadamente el monto?'],
        ['key' => 'integridad_14', 'label' => '¿Cuántas actas administrativas, llamados de atención o sanciones recibió en sus empleos y cuál fue el motivo?'],
        ['key' => 'integridad_15', 'label' => '¿Algún compañero le enseñó o sugirió cómo obtener beneficios no autorizados o sustraer producto o efectivo en sus empleos? Explique.'],
        ['key' => 'integridad_16', 'label' => '¿Cuántas veces omitió reportar una conducta incorrecta de un compañero por pena, amistad o para evitar problemas? Explique.'],
        ['key' => 'integridad_17', 'label' => '¿Ha abandonado algún empleo sin previo aviso? ¿Cuál fue?'],
        ['key' => 'integridad_18', 'label' => '¿Alguna vez utilizó, prestó o tomó dinero de una empresa sin autorización con la intención de devolverlo posteriormente?'],
        ['key' => 'integridad_19', 'label' => '¿Existe algún empleo que no haya registrado en este formulario? ¿Cuál?'],
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
