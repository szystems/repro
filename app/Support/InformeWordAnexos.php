<?php

namespace App\Support;

use App\Models\DocumentoEvaluado;
use App\Models\EvaluadoOrden;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

/** Inserta fotografías de tatuajes y anexos similares en la sección ANEXOS del informe Word. */
class InformeWordAnexos
{
    public static function aplicar(ZipArchive $zip, EvaluadoOrden $evaluado): void
    {
        $evaluado->loadMissing(['documentos', 'cuestionario']);
        $imagenes = self::recopilarImagenes($evaluado);

        if ($imagenes === []) {
            return;
        }

        $documentXml = $zip->getFromName('word/document.xml');
        $relsXml = $zip->getFromName('word/_rels/document.xml.rels');

        if ($documentXml === false || $relsXml === false) {
            return;
        }

        $siguienteRelId = InformeWordXml::siguienteRelId($relsXml);
        $siguienteDocPrId = 920001;

        $documentXml = InformeWordXml::reemplazarTablaPorMarcador($documentXml, 'Fotografía de tatuaje', function (string $tabla) use (
            $imagenes,
            &$relsXml,
            $zip,
            &$siguienteRelId,
            &$siguienteDocPrId
        ): string {
            $filas = InformeWordXml::filasTabla($tabla);
            if (! isset($filas[0], $filas[1])) {
                return $tabla;
            }

            $plantillaFila = $filas[1];
            $filasResultado = [$filas[0]];

            foreach ($imagenes as $indice => $imagen) {
                $relId = $siguienteRelId;
                $siguienteRelId = 'rId' . (((int) preg_replace('/\D/', '', $relId)) + 1);
                $nombreArchivo = 'anexo_tatuaje_' . ($indice + 1) . '.' . $imagen['extension'];

                $relsXml = InformeWordXml::agregarRelacionImagen($relsXml, $relId, $nombreArchivo);
                $zip->addFromString('word/media/' . $nombreArchivo, $imagen['bytes']);
                InformeWordXml::registrarExtensionMedia($zip, $imagen['extension']);

                $celdas = InformeWordXml::celdasFila($plantillaFila);
                if (isset($celdas[0])) {
                    ['cx' => $cx, 'cy' => $cy] = InformeWordFoto::dimensionesEmu(
                        $imagen['widthPx'] ?? 480,
                        $imagen['heightPx'] ?? 360,
                        480
                    );
                    $celdas[0] = InformeWordFoto::establecerImagenCelda(
                        $celdas[0],
                        $relId,
                        $cx,
                        $cy,
                        $siguienteDocPrId
                    );
                    $siguienteDocPrId++;
                }

                if (isset($celdas[1]) && ($imagen['descripcion'] ?? '') !== '') {
                    $celdas[1] = InformeWordXml::establecerCeldaParrafos(
                        $celdas[1],
                        explode("\n", (string) $imagen['descripcion'])
                    );
                }

                preg_match('/<w:tr\b[^>]*>/', $plantillaFila, $apertura);
                $filasResultado[] = ($apertura[0] ?? '<w:tr>') . implode('', $celdas) . '</w:tr>';
            }

            return InformeWordXml::reconstruirTabla($tabla, $filasResultado);
        });

        if (! InformeWordXml::esValido($documentXml)) {
            return;
        }

        $zip->deleteName('word/document.xml');
        $zip->addFromString('word/document.xml', $documentXml);
        $zip->deleteName('word/_rels/document.xml.rels');
        $zip->addFromString('word/_rels/document.xml.rels', $relsXml);
    }

    /**
     * @return list<array{bytes: string, extension: string, descripcion: string}>
     */
    private static function recopilarImagenes(EvaluadoOrden $evaluado): array
    {
        $tatuajes = $evaluado->cuestionario?->getTablasPorNumeroSeccion(5)['tatuajes'] ?? [];
        if (! is_array($tatuajes)) {
            $tatuajes = [];
        }

        $documentos = $evaluado->documentos
            ->filter(fn (DocumentoEvaluado $documento): bool => $documento->es_imagen && $documento->tipo_documento === 'foto_tatuaje')
            ->values();

        $imagenes = [];

        foreach ($documentos as $indice => $documento) {
            $ruta = Storage::disk('local')->path($documento->ruta_archivo);
            $media = InformeWordFoto::prepararMedia($ruta);
            if ($media === null) {
                continue;
            }

            $filaTatuaje = is_array($tatuajes[$indice] ?? null) ? $tatuajes[$indice] : [];
            $imagenes[] = [
                'bytes' => $media['bytes'],
                'extension' => $media['extension'],
                'widthPx' => $media['widthPx'],
                'heightPx' => $media['heightPx'],
                'descripcion' => self::descripcionTatuaje($filaTatuaje, $documento),
            ];
        }

        return $imagenes;
    }

    /** @param array<string, mixed> $fila */
    private static function descripcionTatuaje(array $fila, DocumentoEvaluado $documento): string
    {
        $lineas = collect([
            self::lineaEtiqueta('Ubicación', $fila['ubicacion'] ?? ''),
            self::lineaEtiqueta('Tamaño aproximado', $fila['tamano'] ?? ''),
            self::lineaEtiqueta('Descripción', $fila['descripcion'] ?? ''),
            self::lineaEtiqueta('Tiempo', $fila['tiempo'] ?? ''),
            self::lineaEtiqueta('Visible con uniforme', self::formatoSiNo($fila['visible_uniforme'] ?? '')),
            self::lineaEtiqueta('Significado', $fila['significado'] ?? ''),
            trim((string) ($documento->notas ?? '')) !== '' ? 'Notas: ' . trim((string) $documento->notas) : '',
        ])->filter()->values()->all();

        if ($lineas === []) {
            return $documento->tipo_documento_texto;
        }

        return implode("\n", $lineas);
    }

    private static function lineaEtiqueta(string $etiqueta, mixed $valor): string
    {
        $texto = trim((string) $valor);

        return $texto === '' ? '' : $etiqueta . ': ' . $texto;
    }

    private static function formatoSiNo(mixed $valor): string
    {
        return match ((string) $valor) {
            'si' => 'Sí',
            'no' => 'No',
            default => trim((string) $valor),
        };
    }
}
