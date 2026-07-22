<?php

namespace App\Support;

use App\Models\EvaluadoOrden;

/** Resuelve plantilla Word oficial por tipo de servicio y formulario (jul-2026). */
class InformeWordPlantillas
{
    public const VARIANTE_PREEMPLEO = 'preempleo';

    public const VARIANTE_PERIODICA = 'periodica';

    private const PLANTILLA_PREEMPLEO = 'informe-poligrafo-preempleo.docx';

    private const PLANTILLA_PERIODICA = 'informe-poligrafo-periodica.docx';

    /** Foto del evaluado en el cuerpo del documento (encima de tabla Proceso). */
    private const FOTO_EVALUADO_MEDIA = 'word/media/foto_evaluado.png';

    /**
     * @return array{path: string, variante: string, foto_media: string}|null
     */
    public static function resolver(EvaluadoOrden $evaluado): ?array
    {
        $variante = self::variantePlantilla($evaluado);
        $archivo = $variante === self::VARIANTE_PERIODICA
            ? self::PLANTILLA_PERIODICA
            : self::PLANTILLA_PREEMPLEO;

        $ruta = resource_path('templates/' . $archivo);

        if (! is_readable($ruta)) {
            return null;
        }

        return [
            'path' => $ruta,
            'variante' => $variante,
            'foto_media' => self::FOTO_EVALUADO_MEDIA,
        ];
    }

    public static function rutaPorDefecto(): string
    {
        return resource_path('templates/' . self::PLANTILLA_PREEMPLEO);
    }

    public static function variantePlantilla(EvaluadoOrden $evaluado): string
    {
        return in_array($evaluado->tipo_formulario, ['periodica', 'especifica'], true)
            ? self::VARIANTE_PERIODICA
            : self::VARIANTE_PREEMPLEO;
    }
}
