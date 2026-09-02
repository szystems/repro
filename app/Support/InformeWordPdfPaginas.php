<?php

namespace App\Support;

/** Convierte páginas PDF a PNG para embeber en informes Word. */
class InformeWordPdfPaginas
{
    public const MAX_PAGINAS = 4;

    public const DPI = 96;

    /**
     * @return list<array{bytes: string, widthPx: int, heightPx: int}>
     */
    public static function paginasComoPng(string $rutaPdf): array
    {
        if (! is_readable($rutaPdf)) {
            return [];
        }

        $paginas = self::viaImagick($rutaPdf);
        if ($paginas !== []) {
            return $paginas;
        }

        $paginas = self::viaPdftoppm($rutaPdf);
        if ($paginas !== []) {
            return $paginas;
        }

        return self::viaGhostscript($rutaPdf);
    }

    /**
     * @return list<array{bytes: string, widthPx: int, heightPx: int}>
     */
    private static function viaImagick(string $rutaPdf): array
    {
        if (! extension_loaded('imagick') || ! class_exists(\Imagick::class)) {
            return [];
        }

        $paginas = [];

        try {
            for ($indice = 0; $indice < self::MAX_PAGINAS; $indice++) {
                $frame = new \Imagick();
                $frame->setResolution(self::DPI, self::DPI);
                $frame->readImage($rutaPdf . '[' . $indice . ']');
                $frame->setImageBackgroundColor('white');
                if (defined('Imagick::ALPHACHANNEL_REMOVE')) {
                    $frame->setImageAlphaChannel(\Imagick::ALPHACHANNEL_REMOVE);
                }
                $frame->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
                $frame->setImageFormat('png');

                $bytes = $frame->getImageBlob();
                if ($bytes !== false && $bytes !== '') {
                    $paginas[] = [
                        'bytes' => $bytes,
                        'widthPx' => max(1, $frame->getImageWidth()),
                        'heightPx' => max(1, $frame->getImageHeight()),
                    ];
                }

                $frame->clear();
            }

            return $paginas;
        } catch (\Throwable) {
            return $paginas;
        }
    }

    /**
     * @return list<array{bytes: string, widthPx: int, heightPx: int}>
     */
    private static function viaPdftoppm(string $rutaPdf): array
    {
        if (! self::comandoDisponible('pdftoppm')) {
            return [];
        }

        $directorio = sys_get_temp_dir() . '/repro_pdf_' . uniqid('', true);
        if (! @mkdir($directorio, 0700, true) && ! is_dir($directorio)) {
            return [];
        }

        $prefijo = $directorio . '/pagina';
        $comando = sprintf(
            'pdftoppm -png -r %d -f 1 -l %d %s %s 2>/dev/null',
            self::DPI,
            self::MAX_PAGINAS,
            escapeshellarg($rutaPdf),
            escapeshellarg($prefijo)
        );

        if (! function_exists('exec')) {
            return [];
        }

        \exec($comando, $salida, $codigo);

        $archivos = glob($prefijo . '-*.png') ?: [];
        natsort($archivos);
        $archivos = array_slice(array_values($archivos), 0, self::MAX_PAGINAS);

        $paginas = [];
        foreach ($archivos as $archivo) {
            $bytes = @file_get_contents($archivo);
            if ($bytes === false || $bytes === '') {
                continue;
            }

            $dimensiones = @getimagesize($archivo);
            $paginas[] = [
                'bytes' => $bytes,
                'widthPx' => (int) ($dimensiones[0] ?? 800),
                'heightPx' => (int) ($dimensiones[1] ?? 1100),
            ];
        }

        self::eliminarDirectorioTemporal($directorio);

        return $codigo === 0 || $paginas !== [] ? $paginas : [];
    }

    /**
     * @return list<array{bytes: string, widthPx: int, heightPx: int}>
     */
    private static function viaGhostscript(string $rutaPdf): array
    {
        if (! self::comandoDisponible('gs')) {
            return [];
        }

        $directorio = sys_get_temp_dir() . '/repro_pdf_' . uniqid('', true);
        if (! @mkdir($directorio, 0700, true) && ! is_dir($directorio)) {
            return [];
        }

        $salida = $directorio . '/pagina-%d.png';
        $comando = sprintf(
            'gs -dNOPAUSE -dBATCH -dSAFER -sDEVICE=png16m -r%d -dFirstPage=1 -dLastPage=%d -sOutputFile=%s %s 2>/dev/null',
            self::DPI,
            self::MAX_PAGINAS,
            escapeshellarg($salida),
            escapeshellarg($rutaPdf)
        );

        if (! function_exists('exec')) {
            return [];
        }

        \exec($comando, $gsSalida, $codigo);

        $archivos = glob($directorio . '/pagina-*.png') ?: [];
        natsort($archivos);
        $archivos = array_slice(array_values($archivos), 0, self::MAX_PAGINAS);

        $paginas = [];
        foreach ($archivos as $archivo) {
            $bytes = @file_get_contents($archivo);
            if ($bytes === false || $bytes === '') {
                continue;
            }

            $dimensiones = @getimagesize($archivo);
            $paginas[] = [
                'bytes' => $bytes,
                'widthPx' => (int) ($dimensiones[0] ?? 800),
                'heightPx' => (int) ($dimensiones[1] ?? 1100),
            ];
        }

        self::eliminarDirectorioTemporal($directorio);

        return $paginas;
    }

    private static function comandoDisponible(string $comando): bool
    {
        if (! function_exists('shell_exec')) {
            return false;
        }

        $ruta = trim((string) \shell_exec('command -v ' . escapeshellarg($comando) . ' 2>/dev/null'));

        return $ruta !== '';
    }

    private static function eliminarDirectorioTemporal(string $directorio): void
    {
        foreach (glob($directorio . '/*') ?: [] as $archivo) {
            if (is_file($archivo)) {
                @unlink($archivo);
            }
        }

        @rmdir($directorio);
    }
}
