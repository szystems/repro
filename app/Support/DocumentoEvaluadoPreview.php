<?php

namespace App\Support;

use App\Models\DocumentoEvaluado;
use Illuminate\Support\Facades\Storage;

/**
 * Miniatura de papelería para la vista previa (listado / modal).
 * El archivo original no se toca; la descarga sigue sirviendo el JPEG/PDF completo.
 */
class DocumentoEvaluadoPreview
{
    public const MAX_LADO_PX = 480;

    public const JPEG_QUALITY = 72;

    public static function usaJpeg(): bool
    {
        return \function_exists('imagejpeg');
    }

    public static function rutaMiniatura(string $rutaArchivo): string
    {
        return $rutaArchivo.(self::usaJpeg() ? '.thumb.jpg' : '.thumb.png');
    }

    /**
     * Ruta en disco local a servir en preview. Miniatura JPEG si es imagen y GD puede;
     * si no, el original (PDF, GD ausente, o bytes no decodificables).
     */
    public static function rutaParaPreview(DocumentoEvaluado $documento): string
    {
        if ($documento->es_imagen) {
            $mini = self::asegurarMiniatura($documento);
            if ($mini !== null) {
                return $mini;
            }
        }

        return $documento->ruta_archivo;
    }

    public static function mimeParaPreview(DocumentoEvaluado $documento, string $rutaServida): string
    {
        if ($rutaServida === self::rutaMiniatura($documento->ruta_archivo)) {
            return self::usaJpeg() ? 'image/jpeg' : 'image/png';
        }

        return $documento->mime_type ?: 'application/octet-stream';
    }

    public static function borrarMiniatura(DocumentoEvaluado $documento): void
    {
        foreach (['.thumb.jpg', '.thumb.png'] as $sufijo) {
            $thumb = $documento->ruta_archivo.$sufijo;
            if (Storage::disk('local')->exists($thumb)) {
                Storage::disk('local')->delete($thumb);
            }
        }
    }

    public static function asegurarMiniatura(DocumentoEvaluado $documento): ?string
    {
        if (! $documento->es_imagen || ! \function_exists('imagecreatetruecolor')) {
            return null;
        }

        $disk = Storage::disk('local');
        if (! $disk->exists($documento->ruta_archivo)) {
            return null;
        }

        $thumb = self::rutaMiniatura($documento->ruta_archivo);
        if ($disk->exists($thumb)) {
            return $thumb;
        }

        $absoluta = $disk->path($documento->ruta_archivo);
        if (! is_readable($absoluta)) {
            return null;
        }

        $prevLimit = ini_get('memory_limit');
        @ini_set('memory_limit', '256M');

        try {
            $info = @getimagesize($absoluta);
            $width = (int) ($info[0] ?? 0);
            $height = (int) ($info[1] ?? 0);
            if ($width < 1 || $height < 1) {
                return null;
            }

            $origen = self::cargar($absoluta, (string) ($info['mime'] ?? $documento->mime_type));
            if ($origen === null) {
                return null;
            }

            $destino = self::redimensionar($origen);
            \imagedestroy($origen);
            if ($destino === null) {
                return null;
            }

            $absThumb = $disk->path($thumb);
            $dirThumb = dirname($absThumb);
            if (! is_dir($dirThumb)) {
                @mkdir($dirThumb, 0755, true);
            }

            $ok = self::usaJpeg()
                ? \imagejpeg($destino, $absThumb, self::JPEG_QUALITY)
                : \imagepng($destino, $absThumb, 6);
            \imagedestroy($destino);

            if ($ok !== true || ! is_file($absThumb)) {
                return null;
            }

            return $thumb;
        } finally {
            if (is_string($prevLimit) && $prevLimit !== '') {
                @ini_set('memory_limit', $prevLimit);
            }
        }
    }

    private static function cargar(string $ruta, string $mime): ?\GdImage
    {
        $imagen = match (true) {
            str_contains($mime, 'png') || str_ends_with(strtolower($ruta), '.png') => @\imagecreatefrompng($ruta),
            str_contains($mime, 'webp') => \function_exists('imagecreatefromwebp') ? @\imagecreatefromwebp($ruta) : false,
            \function_exists('imagecreatefromjpeg') => @\imagecreatefromjpeg($ruta),
            default => false,
        };

        return $imagen instanceof \GdImage ? $imagen : null;
    }

    private static function redimensionar(\GdImage $origen): ?\GdImage
    {
        $origenAncho = \imagesx($origen);
        $origenAlto = \imagesy($origen);
        if ($origenAncho < 1 || $origenAlto < 1) {
            return null;
        }

        $escala = min(1.0, self::MAX_LADO_PX / $origenAncho, self::MAX_LADO_PX / $origenAlto);
        $destinoAncho = (int) max(1, round($origenAncho * $escala));
        $destinoAlto = (int) max(1, round($origenAlto * $escala));

        $destino = \imagecreatetruecolor($destinoAncho, $destinoAlto);
        if ($destino === false) {
            return null;
        }

        \imagecopyresampled(
            $destino,
            $origen,
            0,
            0,
            0,
            0,
            $destinoAncho,
            $destinoAlto,
            $origenAncho,
            $origenAlto
        );

        return $destino;
    }
}
