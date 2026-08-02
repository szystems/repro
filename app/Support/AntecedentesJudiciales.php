<?php

namespace App\Support;

/**
 * E2.17 — Aspecto judicial (interno).
 *
 * Redacción literales de POLIGRAFO PRESENCIAL (2).pdf (ago-2025).
 */
class AntecedentesJudiciales
{
    public const TITULO_BLOQUE = 'Aspecto judicial';

    /** @var list<array{key: string, label: string}> */
    public const PREGUNTAS = [
        ['key' => 'judicial_01', 'label' => '¿Cuándo fue la última vez que tramitó sus antecedentes penales y policiacos?'],
        ['key' => 'judicial_02', 'label' => '¿Tiene algún antecedente penal o policiaco?'],
        ['key' => 'judicial_03', 'label' => '¿Alguna vez tuvo que limpiar algun antecedentepenal o policial ¿Por qué motivo?'],
        ['key' => 'judicial_04', 'label' => '¿Alguna vez estuvo detenido en cárceles o delegaciones? ¿Por qué motivo?'],
        ['key' => 'judicial_05', 'label' => '¿Ha demandado alguna vez a alguien o a alguna empresa por cualquier motivo?'],
        ['key' => 'judicial_06', 'label' => '¿Lo han demandado a usted alguna vez? ¿Por qué motivo?'],
        ['key' => 'judicial_07', 'label' => '¿Alguna vez tuvo necesidad de ocultar su identidad por cualquier motivo?'],
        ['key' => 'judicial_08', 'label' => '¿Ha portado armas alguna vez? ¿Por qué motivo?'],
        ['key' => 'judicial_09', 'label' => '¿Ha robado cualquier objeto con valor superior a Q.200?'],
        ['key' => 'judicial_10', 'label' => '¿Ha robado cualquier objeto con valor menor a Q.200?'],
        ['key' => 'judicial_11', 'label' => '¿Ha tenido la necesidad de alguna vez falsificar, alterar o utilizar documentos falsos?'],
        ['key' => 'judicial_12', 'label' => '¿Usted o algún familiar involuntariamente ha estado involucrado en extorsiones o alguna actividad delictiva?'],
        ['key' => 'judicial_13', 'label' => '¿Algún amigo o familiar está privado de libertad? Por qué motivo?'],
        ['key' => 'judicial_14', 'label' => '¿Cuándo fue la última vez que lo visitó?'],
        ['key' => 'judicial_15', 'label' => '¿Alguna vez usted involuntariamente ha estado involucrado en alguna actividad ilicita?'],
        ['key' => 'judicial_16', 'label' => '¿Su lugar de residencia es considerado zona roja?'],
    ];

    /** @return array<string, mixed> */
    public static function reglasValidacion(): array
    {
        $reglas = [];
        foreach (self::PREGUNTAS as $p) {
            $reglas[$p['key']] = 'required|string|max:2000';
        }

        return $reglas;
    }

    /** @return array<string, string> */
    public static function mensajesValidacion(): array
    {
        $mensajes = [];
        foreach (self::PREGUNTAS as $i => $pregunta) {
            $mensajes[$pregunta['key'].'.required'] = 'Debe responder la pregunta de aspecto judicial #'.($i + 1).'.';
        }

        return $mensajes;
    }
}
