<?php

namespace App\Support;

use App\Models\Cuestionario;
use App\Models\EvaluadoOrden;
use App\Models\Orden;

/** Extrae datos del evaluado/orden/cuestionario para rellenar plantillas Word. */
class InformeWordDatos
{
    public static function seccionFotoCandidato(Cuestionario $cuestionario): string
    {
        return InformeDatos::seccionFotoCandidato($cuestionario);
    }

    /**
     * @return array<string, string>
     */
    public static function encabezado(Orden $orden, EvaluadoOrden $evaluado): array
    {
        return InformeDatos::encabezado($orden, $evaluado);
    }

    /**
     * @return array<string, mixed>
     */
    public static function tablas(EvaluadoOrden $evaluado): array
    {
        return InformeDatos::tablas($evaluado);
    }

    public static function fotoEvaluadoRuta(EvaluadoOrden $evaluado): ?string
    {
        $cuestionario = $evaluado->cuestionario;
        if (! $cuestionario) {
            return null;
        }

        $seccionFoto = self::seccionFotoCandidato($cuestionario);
        $ruta = CuestionarioFotoCandidato::obtenerRuta($cuestionario->id, $seccionFoto);
        if (! $ruta || ! \Illuminate\Support\Facades\Storage::disk('local')->exists($ruta)) {
            return null;
        }

        return \Illuminate\Support\Facades\Storage::disk('local')->path($ruta);
    }

    public static function fotoEvaluadoBytes(EvaluadoOrden $evaluado): ?string
    {
        $path = self::fotoEvaluadoRuta($evaluado);
        if ($path === null) {
            return null;
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if ($extension === 'png') {
            $bytes = file_get_contents($path);

            return $bytes !== false ? $bytes : null;
        }

        if (in_array($extension, ['jpg', 'jpeg'], true)) {
            $png = self::convertirJpegAPng($path);
            if ($png !== null) {
                return $png;
            }

            $bytes = file_get_contents($path);

            return $bytes !== false ? $bytes : null;
        }

        if ($extension === 'webp' && function_exists('imagecreatefromwebp')) {
            $imagen = @imagecreatefromwebp($path);
            if ($imagen === false) {
                return null;
            }

            ob_start();
            imagepng($imagen);
            $bytes = ob_get_clean();
            imagedestroy($imagen);

            return $bytes !== false ? $bytes : null;
        }

        return null;
    }

    private static function convertirJpegAPng(string $path): ?string
    {
        if (! function_exists('imagecreatefromjpeg')) {
            return null;
        }

        $imagen = @imagecreatefromjpeg($path);
        if ($imagen === false) {
            return null;
        }

        ob_start();
        imagepng($imagen);
        $bytes = ob_get_clean();
        imagedestroy($imagen);

        return $bytes !== false ? $bytes : null;
    }
}
