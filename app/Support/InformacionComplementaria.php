<?php

namespace App\Support;

/** E2.18 — Información complementaria (va al informe). */
class InformacionComplementaria
{
    /** @var list<array{key: string, label: string}> */
    public const PREGUNTAS = [
        ['key' => 'comp_sindicato', 'label' => '¿Pertenece o ha pertenecido a algún sindicato?'],
        ['key' => 'comp_familiar_empresa', 'label' => '¿Tiene familiares trabajando en la empresa solicitante?'],
        ['key' => 'comp_como_se_entero', 'label' => '¿Cómo se enteró de la vacante?'],
        ['key' => 'comp_metas', 'label' => '¿Cuáles son sus metas a corto y mediano plazo?'],
        ['key' => 'comp_cualidades', 'label' => '¿Cuáles considera que son sus principales cualidades?'],
        ['key' => 'comp_redes_sociales', 'label' => '¿Qué redes sociales utiliza y con qué frecuencia?'],
        ['key' => 'comp_disponibilidad', 'label' => '¿Cuál es su disponibilidad horaria y para viajar?'],
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
