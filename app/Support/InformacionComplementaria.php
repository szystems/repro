<?php

namespace App\Support;

/** E2.18 — Información complementaria (va al informe). */
class InformacionComplementaria
{
    public const TITULO_BLOQUE = 'Información complementaria';

    /** @var list<array{key: string, label: string}> */
    public const PREGUNTAS = [
        ['key' => 'comp_licencia_conducir', 'label' => 'Tipo de licencia de conducir / vigencia'],
        ['key' => 'comp_sindicato', 'label' => '¿En qué empleos perteneció a un sindicato? Explique.'],
        ['key' => 'comp_familiar_empresa', 'label' => '¿Tiene algún familiar o amigo laborando en la empresa contratante? Explique.'],
        ['key' => 'comp_como_se_entero', 'label' => '¿Cómo se enteró de esta oportunidad laboral?'],
        ['key' => 'comp_condiciones_laborales', 'label' => '¿Está de acuerdo con las condiciones laborales que le ofrece la empresa? Explique.'],
        ['key' => 'comp_metas', 'label' => '¿Cuáles son sus metas personales y laborales a corto, mediano y largo plazo?'],
        ['key' => 'comp_cualidades_defectos', 'label' => 'Mencione sus principales cualidades y aspectos que considera debe mejorar.'],
        ['key' => 'comp_redes_usuario', 'label' => 'Indique los nombres de usuario o perfiles que utiliza en redes sociales actualmente.'],
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
            $mensajes[$pregunta['key'].'.required'] = 'Debe responder la pregunta de información complementaria #'.($i + 1).'.';
        }

        return $mensajes;
    }
}
