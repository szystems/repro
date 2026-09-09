<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

/**
 * Fotos de usuario y logos de empresa en public/assets/imgs/{subdir}.
 * Evita move() relativo al CWD (en Docker Coolify no es public/).
 */
class PerfilImagenSupport
{
    public static function guardar(UploadedFile $file, string $subdir, ?string $anterior = null): string
    {
        $dir = public_path('assets/imgs/'.$subdir);
        File::ensureDirectoryExists($dir, 0755);

        if ($anterior) {
            self::borrar($subdir, $anterior);
        }

        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $filename = time().'_'.bin2hex(random_bytes(4)).'.'.$ext;
        $file->move($dir, $filename);

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
