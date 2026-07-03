<?php

namespace App\Support;

use App\Models\CuestionarioRespuesta;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Fase F (formularios) — E1.5: foto obligatoria del candidato.
 */
class CuestionarioFotoCandidato
{
    public const CAMPO = 'foto_candidato';

    public static function obtenerRuta(int $cuestionarioId, string $seccion): ?string
    {
        $valor = CuestionarioRespuesta::query()
            ->where('cuestionario_id', $cuestionarioId)
            ->where('seccion', $seccion)
            ->where('campo', self::CAMPO)
            ->value('valor');

        return $valor ?: null;
    }

    public static function guardar(UploadedFile $archivo, int $cuestionarioId, string $seccion): string
    {
        $rutaAnterior = self::obtenerRuta($cuestionarioId, $seccion);
        if ($rutaAnterior) {
            Storage::disk('local')->delete($rutaAnterior);
        }

        $extension = strtolower($archivo->getClientOriginalExtension() ?: 'jpg');
        if (! in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            $extension = 'jpg';
        }

        $directorio = "cuestionarios/fotos/{$cuestionarioId}";
        $nombre = self::CAMPO . '.' . $extension;
        $ruta = $archivo->storeAs($directorio, $nombre, 'local');

        CuestionarioRespuesta::updateOrCreate(
            [
                'cuestionario_id' => $cuestionarioId,
                'seccion' => $seccion,
                'campo' => self::CAMPO,
            ],
            [
                'valor' => $ruta,
                'tipo_campo' => 'file',
                'requerido' => true,
            ]
        );

        return $ruta;
    }
}
