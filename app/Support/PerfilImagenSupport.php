<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

/**
 * Fotos de usuario y logos de empresa.
 *
 * El archivo vive en storage/app (volumen persistente de Coolify).
 * public/assets/imgs es un enlace a esa carpeta, así nginx sigue sirviendo
 * la misma URL después de un deploy o de volver a entrar.
 */
class PerfilImagenSupport
{
    public static function guardar(UploadedFile $file, string $subdir, ?string $anterior = null): string
    {
        self::asegurarDirectorio(self::directorioPersistente($subdir));

        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        if (! preg_match('/^[a-z0-9]+$/', $ext)) {
            $ext = 'jpg';
        }
        $filename = time().'_'.bin2hex(random_bytes(4)).'.'.$ext;
        $destino = self::directorioPersistente($subdir).'/'.$filename;

        try {
            $file->move(self::directorioPersistente($subdir), $filename);
        } catch (FileException $e) {
            throw new RuntimeException('No se pudo guardar la imagen.', 0, $e);
        }

        if (! is_file($destino)) {
            throw new RuntimeException('No se pudo guardar la imagen.');
        }

        @chmod($destino, 0644);
        self::publicar($subdir, $filename);

        if ($anterior && $anterior !== $filename) {
            self::borrar($subdir, $anterior);
        }

        return $filename;
    }

    public static function borrar(string $subdir, ?string $filename): void
    {
        if (! self::nombreSeguro($filename)) {
            return;
        }

        foreach (self::rutas($subdir, $filename) as $path) {
            if (is_file($path)) {
                File::delete($path);
            }
        }
    }

    /**
     * Vuelve a dejar en public/ un archivo que sigue en el volumen.
     */
    public static function reponerDesdeVolumen(string $subdir, string $filename): void
    {
        if (! self::nombreSeguro($filename) || ! is_file(self::rutaPersistente($subdir, $filename))) {
            return;
        }

        self::publicar($subdir, $filename);
    }

    public static function rutaPersistente(string $subdir, string $filename): string
    {
        return self::directorioPersistente($subdir).'/'.$filename;
    }

    /**
     * URL pública solo si el archivo sigue en disco. Un nombre en la base
     * sin archivo (se perdió al recrear el contenedor) no debe pintarse roto.
     */
    public static function url(string $subdir, ?string $filename): ?string
    {
        if (! self::nombreSeguro($filename)) {
            return null;
        }

        $publico = public_path('assets/imgs/'.$subdir.'/'.$filename);
        if (! is_file($publico)) {
            self::reponerDesdeVolumen($subdir, $filename);
        }

        if (! is_file($publico)) {
            return null;
        }

        return asset('assets/imgs/'.$subdir.'/'.$filename);
    }

    private static function publicar(string $subdir, string $filename): void
    {
        $origen = self::rutaPersistente($subdir, $filename);
        $dirPublico = public_path('assets/imgs/'.$subdir);

        if (self::mismoDirectorio(self::directorioPersistente($subdir), $dirPublico)) {
            return;
        }

        self::asegurarDirectorio($dirPublico);
        $destino = $dirPublico.'/'.$filename;
        if (is_file($destino) && realpath($destino) === realpath($origen)) {
            return;
        }
        if (! @copy($origen, $destino)) {
            throw new RuntimeException('No se pudo publicar la imagen.');
        }
        @chmod($destino, 0644);
    }

    /**
     * @return array<int, string>
     */
    private static function rutas(string $subdir, string $filename): array
    {
        return array_values(array_unique([
            self::rutaPersistente($subdir, $filename),
            public_path('assets/imgs/'.$subdir.'/'.$filename),
        ]));
    }

    private static function raizPersistente(): string
    {
        return storage_path('app/public/assets/imgs');
    }

    private static function directorioPersistente(string $subdir): string
    {
        return self::raizPersistente().'/'.$subdir;
    }

    private static function asegurarDirectorio(string $dir): void
    {
        if (! is_dir($dir) && ! @mkdir($dir, 0775, true) && ! is_dir($dir)) {
            throw new RuntimeException('No se pudo crear el directorio de imágenes.');
        }
        if (! is_writable($dir)) {
            throw new RuntimeException('El directorio de imágenes no tiene permiso de escritura.');
        }
    }

    private static function mismoDirectorio(string $a, string $b): bool
    {
        if (! is_dir($a) || ! is_dir($b)) {
            return false;
        }

        $realA = realpath($a);
        $realB = realpath($b);

        return $realA !== false && $realA === $realB;
    }

    private static function nombreSeguro(?string $filename): bool
    {
        return is_string($filename) && $filename !== '' && (bool) preg_match('/^[A-Za-z0-9._-]+$/', $filename);
    }
}
