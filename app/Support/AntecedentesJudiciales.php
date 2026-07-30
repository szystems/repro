<?php

namespace App\Support;

/**
 * E2.17 — Aspecto judicial (interno).
 *
 * Textos alineados a POLIGRAFO PRESENCIAL (ago-2025) + CREACIÓN FORMULARIOS DE SISTEMA.pdf.
 */
class AntecedentesJudiciales
{
    public const TITULO_BLOQUE = 'Aspecto judicial';

    /** @var list<array{key: string, label: string}> */
    public const PREGUNTAS = [
        ['key' => 'judicial_01', 'label' => '¿Cuándo fue la última vez que tramitó sus antecedentes penales y policiales?'],
        ['key' => 'judicial_02', 'label' => '¿Tiene actualmente algún antecedente penal o policial? Explique.'],
        ['key' => 'judicial_03', 'label' => '¿Alguna vez realizó gestiones para eliminar, cancelar o limpiar un antecedente penal o policial? Explique.'],
        ['key' => 'judicial_04', 'label' => '¿Cuándo fue la última vez que fue detenido(a), arrestado(a) o permaneció en una cárcel, delegación policial o centro de detención? Explique.'],
        ['key' => 'judicial_05', 'label' => '¿Ha presentado alguna demanda, denuncia o proceso legal contra una persona o empresa? Explique.'],
        ['key' => 'judicial_06', 'label' => '¿Ha sido demandado, denunciado o sujeto de algún proceso legal? Explique.'],
        ['key' => 'judicial_07', 'label' => '¿Alguna vez ha tenido la necesidad de ocultar su identidad o utilizar información distinta a la propia? Explique.'],
        ['key' => 'judicial_08', 'label' => '¿Ha portado armas de fuego u otras armas? ¿Por qué motivo? Explique.'],
        ['key' => 'judicial_09', 'label' => '¿Ha tomado alguna vez un objeto, dinero o bien ajeno sin autorización por un valor superior a Q.200.00? Explique.'],
        ['key' => 'judicial_10', 'label' => '¿Ha tomado alguna vez un objeto, dinero o bien ajeno sin autorización por un valor igual o menor a Q.200.00? Explique.'],
        ['key' => 'judicial_11', 'label' => '¿Ha tenido necesidad de falsificar, alterar o utilizar documentos falsos? Explique.'],
        ['key' => 'judicial_12', 'label' => '¿Algún familiar ha estado involucrado en extorsiones, delitos o actividades ilícitas? Explique.'],
        ['key' => 'judicial_13', 'label' => '¿Algún amigo o familiar se encuentra privado de libertad? Explique.'],
        ['key' => 'judicial_14', 'label' => '¿Cuándo fue la última vez que visitó a una persona privada de libertad? Explique.'],
        ['key' => 'judicial_15', 'label' => '¿Alguna vez se ha visto involucrado, aunque haya sido involuntariamente, en una actividad ilícita? Explique.'],
        ['key' => 'judicial_16', 'label' => '¿Considera que su lugar de residencia presenta problemas de delincuencia, pandillas, extorsiones o actividades ilícitas? Explique.'],
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
