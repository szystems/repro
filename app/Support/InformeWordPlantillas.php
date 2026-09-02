<?php

namespace App\Support;

use App\Models\EvaluadoOrden;

/**
 * Resuelve plantilla Word por tipo de servicio × formulario.
 * Sprint F3: plantillas cliente ago-2026 (FORMATOS.pdf + 7× .docx).
 */
class InformeWordPlantillas
{
    public const VARIANTE_PREEMPLEO = 'preempleo';

    public const VARIANTE_PERIODICA = 'periodica';

    public const VARIANTE_ESPECIFICA = 'especifica';

    public const VARIANTE_SOCIO = 'socioeconomico';

    /** Layout de plantilla: legacy (Proceso:) o v2 (DATOS GENERALES). */
    public const LAYOUT_LEGACY = 'legacy';

    public const LAYOUT_V2 = 'v2';

    /**
     * Matriz servicio × formulario → archivo en resources/templates/.
     *
     * @var array<string, array<string, string>>
     */
    private const MATRIZ_V2 = [
        'poligrafo' => [
            'preempleo' => 'informe-poligrafo-preempleo-v2.docx',
            'periodica' => 'informe-poligrafo-periodica-v2.docx',
            'especifica' => 'informe-poligrafo-especifica-v2.docx',
        ],
        'vsa' => [
            'preempleo' => 'informe-vsa-preempleo-v2.docx',
            'periodica' => 'informe-vsa-periodica-v2.docx',
            'especifica' => 'informe-vsa-especifica-v2.docx',
        ],
        'socioeconomico' => [
            'preempleo' => 'informe-socioeconomico-v2.docx',
        ],
    ];

    /** Fallback histórico (pre-F3) si falta el archivo v2. */
    private const PLANTILLA_LEGACY_PREEMPLEO = 'informe-poligrafo-preempleo.docx';

    private const PLANTILLA_LEGACY_PERIODICA = 'informe-poligrafo-periodica.docx';

    /** Foto del evaluado en el cuerpo del documento. */
    private const FOTO_EVALUADO_MEDIA = 'word/media/foto_evaluado.png';

    /**
     * @return array{path: string, variante: string, layout: string, foto_media: string}|null
     */
    public static function resolver(EvaluadoOrden $evaluado): ?array
    {
        $variante = self::variantePlantilla($evaluado);
        $archivo = self::archivoPara($evaluado);
        $ruta = resource_path('templates/' . $archivo);

        if (! is_readable($ruta)) {
            return null;
        }

        $layout = str_ends_with($archivo, '-v2.docx') ? self::LAYOUT_V2 : self::LAYOUT_LEGACY;

        return [
            'path' => $ruta,
            'variante' => $variante,
            'layout' => $layout,
            'foto_media' => self::FOTO_EVALUADO_MEDIA,
        ];
    }

    public static function rutaPorDefecto(): string
    {
        return resource_path('templates/' . self::PLANTILLA_LEGACY_PREEMPLEO);
    }

    public static function variantePlantilla(EvaluadoOrden $evaluado): string
    {
        if (($evaluado->tipo_servicio ?? '') === 'socioeconomico') {
            return self::VARIANTE_SOCIO;
        }

        return match ($evaluado->tipo_formulario) {
            'periodica' => self::VARIANTE_PERIODICA,
            'especifica' => self::VARIANTE_ESPECIFICA,
            default => self::VARIANTE_PREEMPLEO,
        };
    }

    /** Preempleo + socio: incluyen hermanos / complementaria preempleo. */
    public static function esVariantePreempleoLike(string $variante): bool
    {
        return in_array($variante, [self::VARIANTE_PREEMPLEO, self::VARIANTE_SOCIO], true);
    }

    /** Periódica + específica: laboral simplificado + labor complementaria. */
    public static function esVariantePeriodicaLike(string $variante): bool
    {
        return in_array($variante, [self::VARIANTE_PERIODICA, self::VARIANTE_ESPECIFICA], true);
    }

    public static function archivoPara(EvaluadoOrden $evaluado): string
    {
        $servicio = $evaluado->tipo_servicio ?: 'poligrafo';
        $formulario = $evaluado->tipo_formulario ?: 'preempleo';

        if ($servicio === 'socioeconomico') {
            $formulario = 'preempleo';
        }

        $candidato = self::MATRIZ_V2[$servicio][$formulario] ?? null;
        if ($candidato !== null && is_readable(resource_path('templates/' . $candidato))) {
            return $candidato;
        }

        // Fallback legacy: periódica/específica → plantilla periódica; resto → preempleo.
        return in_array($formulario, ['periodica', 'especifica'], true)
            ? self::PLANTILLA_LEGACY_PERIODICA
            : self::PLANTILLA_LEGACY_PREEMPLEO;
    }
}
