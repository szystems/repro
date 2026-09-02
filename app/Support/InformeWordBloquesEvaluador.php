<?php

namespace App\Support;

use App\Models\EvaluadoOrden;

/** Seis bloques narrativos del informe Word que redacta el evaluador (confirmación cliente jul-2026). */
class InformeWordBloquesEvaluador
{
    /**
     * Observaciones del evaluador para la primera hoja. Es opcional a propósito: no forma parte de
     * BLOQUES para no bloquear el cierre del informe y, si queda vacía, el cuadro se entrega en
     * blanco en lugar de arrastrar el motivo u observaciones que la empresa escribió en la orden.
     */
    public const NOTA_OBSERVACIONES = 'word_observaciones';

    /**
     * Casilla de recomendaciones del informe. Preempleo: recuadro RECOMENDACIONES
     * (después de aspectos judiciales). Peri/espe: OBSERVACIONES ADICIONALES al final.
     * Es opcional: no bloquea el cierre. Antes se rellenaba con notas internas de antecedentes.
     */
    public const NOTA_RECOMENDACIONES = 'word_recomendaciones';

    /** @var list<array{slug: string, titulo: string}> */
    /** Orden confirmado cliente ago-2026 (audio + CAMBIOS PARA LOS REPORTES.pdf). */
    public const BLOQUES = [
        ['slug' => 'word_laboral', 'titulo' => 'Aspecto laboral'],
        ['slug' => 'word_economico', 'titulo' => 'Información económica'],
        ['slug' => 'word_salud', 'titulo' => 'Salud'],
        ['slug' => 'word_habitos', 'titulo' => 'Hábitos personales'],
        ['slug' => 'word_sustancias', 'titulo' => 'Actividades delictivas / sustancias'],
        ['slug' => 'word_judicial', 'titulo' => 'Aspectos judiciales'],
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

    public static function observaciones(int $evaluadoOrdenId): string
    {
        $notas = EvaluadorNotasSupport::mapaPorSeccion($evaluadoOrdenId);

        return trim((string) ($notas[self::NOTA_OBSERVACIONES] ?? ''));
    }

    public static function recomendaciones(int $evaluadoOrdenId): string
    {
        $notas = EvaluadorNotasSupport::mapaPorSeccion($evaluadoOrdenId);

        return trim((string) ($notas[self::NOTA_RECOMENDACIONES] ?? ''));
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
