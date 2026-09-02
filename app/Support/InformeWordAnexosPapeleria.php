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
        $raw = trim((string) (EvaluadorNotasSupport::mapaPorSeccion($evaluadoOrdenId)[self::SECCION_NOTA] ?? ''));
        if ($raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            return [];
        }

        return array_values(array_filter(
            $decoded,
            static fn ($tipo): bool => is_string($tipo) && in_array($tipo, self::TIPOS_ANEXO, true)
        ));
    }

    /**
     * @param  list<string>|null  $tipos
     */
    public static function guardarSeleccion(int $evaluadoOrdenId, ?array $tipos, ?int $userId): void
    {
        if ($tipos === null) {
            return;
        }

        $validos = array_values(array_unique(array_filter(
            $tipos,
            static fn ($tipo): bool => is_string($tipo) && in_array($tipo, self::TIPOS_ANEXO, true)
        )));

        EvaluadorNota::guardarNota(
            $evaluadoOrdenId,
            self::SECCION_NOTA,
            '',
            $validos === [] ? null : json_encode($validos, JSON_UNESCAPED_UNICODE),
            $userId
        );
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
            if (in_array($tipo, $tiposSubidos, true)) {
                $disponibles[$tipo] = $etiquetas[$tipo] ?? $tipo;
            }
        }

        return $disponibles;
    }

    /**
     * Documentos a insertar en ANEXOS del Word (imágenes y PDFs embebidos como páginas PNG).
     *
     * @return Collection<int, DocumentoEvaluado>
     */
    public static function documentosParaWord(EvaluadoOrden $evaluado): Collection
    {
        $seleccionados = self::tiposSeleccionados($evaluado->id);
        if ($seleccionados === []) {
            return collect();
        }

        $evaluado->loadMissing('documentos');

        return $evaluado->documentos
            ->filter(fn (DocumentoEvaluado $doc): bool => in_array($doc->tipo_documento, $seleccionados, true))
            ->values();
    }
}
