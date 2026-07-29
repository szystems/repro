<?php

namespace App\Support;

use App\Models\EvaluadoOrden;

/** Seis bloques narrativos del informe Word que redacta el evaluador (confirmación cliente jul-2026). */
class InformeWordBloquesEvaluador
{
    /** @var list<array{slug: string, titulo: string}> */
    public const BLOQUES = [
        ['slug' => 'word_salud', 'titulo' => 'Aspectos de salud'],
        ['slug' => 'word_habitos', 'titulo' => 'Hábitos personales'],
        ['slug' => 'word_sustancias', 'titulo' => 'Actividades delictivas / sustancias'],
        ['slug' => 'word_judicial', 'titulo' => 'Aspectos judiciales'],
        ['slug' => 'word_laboral', 'titulo' => 'Aspecto laboral'],
        ['slug' => 'word_economico', 'titulo' => 'Aspecto económico'],
    ];

    /**
     * @return array<string, string> slug => contenido
     */
    public static function mapa(int $evaluadoOrdenId): array
    {
        $notas = EvaluadorNotasSupport::mapaPorSeccion($evaluadoOrdenId);
        $mapa = [];

        foreach (self::BLOQUES as $bloque) {
            $mapa[$bloque['slug']] = trim((string) ($notas[$bloque['slug']] ?? ''));
        }

        return $mapa;
    }

    /**
     * @return list<string> slugs de bloques vacíos
     */
    public static function faltantes(int $evaluadoOrdenId): array
    {
        $faltantes = [];

        foreach (self::mapa($evaluadoOrdenId) as $slug => $contenido) {
            if ($contenido === '') {
                $faltantes[] = $slug;
            }
        }

        return $faltantes;
    }

    public static function completos(int $evaluadoOrdenId): bool
    {
        return self::faltantes($evaluadoOrdenId) === [];
    }

    public static function titulosFaltantes(int $evaluadoOrdenId): array
    {
        $faltantes = self::faltantes($evaluadoOrdenId);
        $titulos = [];

        foreach (self::BLOQUES as $bloque) {
            if (in_array($bloque['slug'], $faltantes, true)) {
                $titulos[] = $bloque['titulo'];
            }
        }

        return $titulos;
    }

    public static function mensajeBloqueo(EvaluadoOrden $evaluado): string
    {
        $titulos = self::titulosFaltantes($evaluado->id);

        if ($titulos === []) {
            return '';
        }

        return 'Complete la redacción del informe Word antes de marcar el informe como listo. Faltan: '
            . implode(', ', $titulos)
            . '. Edítelos en Gestión de Cuestionarios → Redacción del informe Word.';
    }
}
