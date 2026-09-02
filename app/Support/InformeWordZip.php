<?php

namespace App\Support;

use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\Shared\ZipArchive as PhpWordZipArchive;

/** Inicializa zip para informes Word (PCLZip cuando el hosting no tiene ext-zip). */
class InformeWordZip
{
    private static bool $booted = false;

    /** @var array<string, array<string, string>> */
    private static array $reemplazosPendientes = [];

    public static function boot(): void
    {
        if (self::$booted) {
            return;
        }

        if (! class_exists(\ZipArchive::class)) {
            Settings::setZipClass(Settings::PCLZIP);
        }

        $tempDir = storage_path('app/temp');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        Settings::setTempDir($tempDir);

        self::$booted = true;
    }

    public static function create(): PhpWordZipArchive
    {
        self::boot();

        return new PhpWordZipArchive();
    }

    public static function usaPclZip(): bool
    {
        self::boot();

        return Settings::getZipClass() === Settings::PCLZIP;
    }

    /**
     * Reemplaza una entrada existente del .docx.
     * PhpWord no implementa deleteName con PCLZip; sin esto el XML modificado nunca se persiste.
     */
    public static function reemplazarEntrada(PhpWordZipArchive $zip, string $localname, string $contents): bool
    {
        self::boot();

        if (! self::usaPclZip()) {
            $zip->deleteName($localname);

            return (bool) $zip->addFromString($localname, $contents);
        }

        self::$reemplazosPendientes[$zip->filename][$localname] = $contents;

        return true;
    }

    /**
     * Lee una entrada del zip respetando reemplazos aún no persistidos (PCLZip).
     *
     * @return string|false
     */
    public static function leerEntrada(PhpWordZipArchive $zip, string $localname): string|false
    {
        self::boot();

        $archivo = $zip->filename;
        if ($archivo !== '' && isset(self::$reemplazosPendientes[$archivo][$localname])) {
            return self::$reemplazosPendientes[$archivo][$localname];
        }

        return $zip->getFromName($localname);
    }

    public static function cerrar(PhpWordZipArchive $zip): bool
    {
        self::boot();

        $archivo = $zip->filename;
        $reemplazos = ($archivo !== '' && isset(self::$reemplazosPendientes[$archivo]))
            ? self::$reemplazosPendientes[$archivo]
            : null;

        if ($reemplazos !== null) {
            unset(self::$reemplazosPendientes[$archivo]);
        }

        // Liberar el .docx antes de reconstruirlo (PCLZip mantiene el archivo abierto).
        $zip->close();

        if ($reemplazos !== null) {
            return self::reconstruirZipPcl($archivo, $reemplazos);
        }

        return true;
    }

    /**
     * @param  array<string, string>  $reemplazos
     */
    private static function reconstruirZipPcl(string $archivo, array $reemplazos): bool
    {
        if (! class_exists(\PclZip::class)) {
            $pclPath = base_path('vendor/phpoffice/phpword/src/PhpWord/Shared/PCLZip/pclzip.lib.php');
            if (is_file($pclPath)) {
                require_once $pclPath;
            }
        }

        if (! class_exists(\PclZip::class)) {
            return false;
        }

        $tempDir = storage_path('app/temp/pcl_rebuild_' . uniqid('', true));
        $stagingDir = $tempDir . '/files';
        if (! mkdir($stagingDir, 0755, true) && ! is_dir($stagingDir)) {
            return false;
        }

        // Extraer a disco (binario). EXTRACT_AS_STRING corrompe .png/.wdp y Word
        // pide recuperar el archivo en polígrafo; socio no trae esos HD Photo.
        $pcl = new \PclZip($archivo);
        $extraido = $pcl->extract(PCLZIP_OPT_PATH, $stagingDir);
        if ($extraido === 0) {
            self::eliminarDirectorio($tempDir);

            return false;
        }

        foreach ($reemplazos as $nombre => $contenido) {
            $ruta = $stagingDir.'/'.ltrim(str_replace('\\', '/', $nombre), '/');
            $directorio = dirname($ruta);
            if (! is_dir($directorio) && ! mkdir($directorio, 0755, true) && ! is_dir($directorio)) {
                self::eliminarDirectorio($tempDir);

                return false;
            }

            file_put_contents($ruta, $contenido);
        }

        $archivos = self::archivosRecursivos($stagingDir);
        if ($archivos === []) {
            self::eliminarDirectorio($tempDir);

            return false;
        }

        $nuevoArchivo = $tempDir . '/nuevo.docx';
        $pclNuevo = new \PclZip($nuevoArchivo);
        $resultado = $pclNuevo->create($archivos, PCLZIP_OPT_REMOVE_PATH, $stagingDir);

        if ($resultado === 0) {
            self::eliminarDirectorio($tempDir);

            return false;
        }

        $ok = @rename($nuevoArchivo, $archivo) || @copy($nuevoArchivo, $archivo);
        self::eliminarDirectorio($tempDir);

        return $ok;
    }

    /** @return list<string> */
    private static function archivosRecursivos(string $directorio): array
    {
        $archivos = [];
        $iterador = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directorio, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterador as $archivo) {
            if ($archivo->isFile()) {
                $archivos[] = $archivo->getPathname();
            }
        }

        sort($archivos);

        return $archivos;
    }

    private static function eliminarDirectorio(string $directorio): void
    {
        if (! is_dir($directorio)) {
            return;
        }

        $iterador = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directorio, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterador as $archivo) {
            if ($archivo->isDir()) {
                @rmdir($archivo->getPathname());
            } else {
                @unlink($archivo->getPathname());
            }
        }

        @rmdir($directorio);
    }
}
