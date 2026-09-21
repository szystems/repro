<?php

namespace App\Support;

use App\Models\DocumentoEvaluado;
use App\Models\EvaluadorNota;
use App\Models\EvaluadoOrden;
use Illuminate\Support\Collection;

/** Selección de papelería del evaluado para anexar al informe Word. */
class InformeWordAnexosPapeleria
{
    public const SECCION_NOTA = 'word_anexos_papeleria';

    /** Tipos de documento que pueden incluirse como anexo (excluye tatuajes — van por flujo propio). */
    public const TIPOS_ANEXO = [
        'dpi_archivo',
        'antecedentes_penales',
        'antecedentes_policiacos',
        'cv',
        'constancia_estudios',
        'licencia_auto',
        'licencia_moto',
        'pasaporte',
        'carta_laboral',
        'constancia_laboral',
        'recibo_luz',
        'autorizacion_firmada',
        'otro',
    ];

    /**
     * @return list<string>
     */
    public static function tiposSeleccionados(int $evaluadoOrdenId): array
    {
        $decoded = self::seleccionDecodificada($evaluadoOrdenId);

        return array_values(array_filter(
            $decoded,
            static fn ($tipo): bool => is_string($tipo) && in_array($tipo, self::TIPOS_ANEXO, true)
        ));
    }

    /**
     * Acepta ids de documento (selección por archivo) o claves de tipo (selección anterior).
     *
     * @param  list<int|string>|null  $seleccion
     */
    public static function guardarSeleccion(int $evaluadoOrdenId, ?array $seleccion, ?int $userId): void
    {
        if ($seleccion === null) {
            return;
        }

        $ids = [];
        $tipos = [];
        foreach ($seleccion as $valor) {
            if (is_int($valor) || (is_string($valor) && ctype_digit($valor))) {
                $id = (int) $valor;
                if ($id > 0) {
                    $ids[] = $id;
                }

                continue;
            }
            if (is_string($valor) && in_array($valor, self::TIPOS_ANEXO, true)) {
                $tipos[] = $valor;
            }
        }

        $ids = array_values(array_unique($ids));
        $tipos = array_values(array_unique($tipos));
        $payload = $ids !== []
            ? array_map(static fn (int $id): string => (string) $id, $ids)
            : $tipos;

        EvaluadorNota::guardarNota(
            $evaluadoOrdenId,
            self::SECCION_NOTA,
            '',
            $payload === [] ? null : json_encode($payload, JSON_UNESCAPED_UNICODE),
            $userId
        );
    }

    /**
     * @return list<int>
     */
    public static function idsSeleccionados(int $evaluadoOrdenId): array
    {
        $decoded = self::seleccionDecodificada($evaluadoOrdenId);
        $ids = [];
        foreach ($decoded as $valor) {
            if (is_int($valor) || (is_string($valor) && ctype_digit($valor))) {
                $ids[] = (int) $valor;
            }
        }

        return array_values(array_unique($ids));
    }

    /**
     * @return list<mixed>
     */
    private static function seleccionDecodificada(int $evaluadoOrdenId): array
    {
        $raw = trim((string) (EvaluadorNotasSupport::mapaPorSeccion($evaluadoOrdenId)[self::SECCION_NOTA] ?? ''));
        if ($raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Tipos con al menos un documento subido (para mostrar checkboxes).
     *
     * @return array<string, string> tipo => etiqueta
     */
    public static function tiposDisponibles(EvaluadoOrden $evaluado): array
    {
        $evaluado->loadMissing('documentos');
        $tiposSubidos = $evaluado->documentos
            ->pluck('tipo_documento')
            ->unique()
            ->all();

        $etiquetas = DocumentoEvaluado::tiposDocumento();
        $disponibles = [];

        foreach (self::TIPOS_ANEXO as $tipo) {
            if (! in_array($tipo, $tiposSubidos, true)) {
                continue;
            }
            $tieneImagen = $evaluado->documentos
                ->contains(fn (DocumentoEvaluado $doc): bool => $doc->tipo_documento === $tipo && $doc->es_imagen);
            if ($tieneImagen) {
                $disponibles[$tipo] = $etiquetas[$tipo] ?? $tipo;
            }
        }

        return $disponibles;
    }

    /**
     * Imágenes que se pueden marcar una por una (dos archivos del mismo tipo no se anexan juntos).
     *
     * @return Collection<int, DocumentoEvaluado>
     */
    public static function imagenesDisponibles(EvaluadoOrden $evaluado): Collection
    {
        $evaluado->loadMissing('documentos');

        return $evaluado->documentos
            ->filter(fn (DocumentoEvaluado $doc): bool => $doc->es_imagen && in_array($doc->tipo_documento, self::TIPOS_ANEXO, true))
            ->values();
    }

    /**
     * Imágenes marcadas para el Word. Si la selección guardada es por tipo (anterior),
     * entran todas las imágenes de esos tipos.
     *
     * @return Collection<int, DocumentoEvaluado>
     */
    public static function documentosParaWord(EvaluadoOrden $evaluado): Collection
    {
        $evaluado->loadMissing('documentos');
        $ids = self::idsSeleccionados($evaluado->id);
        if ($ids !== []) {
            return $evaluado->documentos
                ->filter(fn (DocumentoEvaluado $doc): bool => in_array($doc->id, $ids, true)
                    && $doc->es_imagen
                    && in_array($doc->tipo_documento, self::TIPOS_ANEXO, true))
                ->values();
        }

        $seleccionados = self::tiposSeleccionados($evaluado->id);
        if ($seleccionados === []) {
            return collect();
        }

        return $evaluado->documentos
            ->filter(fn (DocumentoEvaluado $doc): bool => in_array($doc->tipo_documento, $seleccionados, true) && $doc->es_imagen)
            ->values();
    }
}
