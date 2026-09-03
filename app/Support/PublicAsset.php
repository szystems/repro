<?php

namespace App\Support;

/**
 * URL de un archivo público con cache-busting.
 * filemtime() crudo tira 500 si el JS no está en la imagen (Coolify / gitignore).
 */
class PublicAsset
{
    public static function version(string $relativePath): string
    {
        $full = public_path($relativePath);

        return is_file($full) ? (string) filemtime($full) : (string) time();
    }

    public static function url(string $relativePath): string
    {
        return asset($relativePath).'?v='.self::version($relativePath);
    }
}
