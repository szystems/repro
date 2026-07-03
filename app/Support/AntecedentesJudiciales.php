<?php

namespace App\Support;

/** E2.17 — Aspecto judicial (interno). */
class AntecedentesJudiciales
{
    /** @var list<array{key: string, label: string}> */
    public const PREGUNTAS = [
        ['key' => 'judicial_01', 'label' => '¿Ha sido detenido o procesado penalmente?'],
        ['key' => 'judicial_02', 'label' => '¿Tiene antecedentes penales registrados?'],
        ['key' => 'judicial_03', 'label' => '¿Ha estado bajo arresto domiciliario o medidas cautelares?'],
        ['key' => 'judicial_04', 'label' => '¿Ha sido demandado civil o mercantilmente?'],
        ['key' => 'judicial_05', 'label' => '¿Posee o ha poseído armas de fuego?'],
        ['key' => 'judicial_06', 'label' => '¿Ha cometido hurtos por montos significativos?'],
        ['key' => 'judicial_07', 'label' => '¿Ha falsificado documentos oficiales?'],
        ['key' => 'judicial_08', 'label' => '¿Tiene familiares en actividades ilícitas?'],
        ['key' => 'judicial_09', 'label' => '¿Vive o ha vivido en zona de alto riesgo?'],
        ['key' => 'judicial_10', 'label' => '¿Ha tenido conflictos con autoridades?'],
        ['key' => 'judicial_11', 'label' => '¿Ha sido víctima de extorsión o amenazas?'],
        ['key' => 'judicial_12', 'label' => '¿Hay algún otro aspecto judicial relevante?'],
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
}
