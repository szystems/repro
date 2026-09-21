<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

/**
 * Fotos de usuario y logos de empresa en public/assets/imgs/{subdir}.
 * Evita move() relativo al CWD (en Docker Coolify no es public/).
 */
class PerfilImagenSupport
{
    public static function guardar(UploadedFile $file, string $subdir, ?string $anterior = null): string
    {
        $dir = public_path('assets/imgs/'.$subdir);
        if (! is_dir($dir) && ! @mkdir($dir, 0775, true) && ! is_dir($dir)) {
            throw new RuntimeException('No se pudo crear el directorio de imágenes.');
        }
        if (! is_writable($dir)) {
            throw new RuntimeException('El directorio de imágenes no tiene permiso de escritura.');
        }

        if ($anterior) {
            self::borrar($subdir, $anterior);
        }

        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        if (! preg_match('/^[a-z0-9]+$/', $ext)) {
            $ext = 'jpg';
        }
        $filename = time().'_'.bin2hex(random_bytes(4)).'.'.$ext;
        try {
            $file->move($dir, $filename);
        } catch (FileException $e) {
            throw new RuntimeException('No se pudo guardar la imagen.', 0, $e);
        }

        return $filename;
    }

    public static function borrar(string $subdir, ?string $filename): void
    {
        if (! $filename) {
            return;
        }

        $path = public_path('assets/imgs/'.$subdir.'/'.$filename);
        if (File::exists($path)) {
            File::delete($path);
        }
    }
}
