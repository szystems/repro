<?php

namespace App\Support;

use App\Models\DocumentoEvaluado;
use App\Models\EvaluadoOrden;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\Shared\ZipArchive as PhpWordZipArchive;

/** Inserta fotografías de tatuajes y anexos similares en la sección ANEXOS del informe Word. */
class InformeWordAnexos
{
    /**
     * Rasterizar PDF a PNG (Imagick/gs) en la petición HTTP satura LiteSpeed/iPage:
     * 503 al descargar el Word y, de paso, “no deja editar” porque el worker queda ocupado.
     * La UI de anexos ya documenta PDFs como fila descriptiva; las imágenes sí se embeben.
     */
    private const RASTERIZAR_PDF_EN_WORD = false;

    /** Tope de tiempo extra para anexos: el resto de la generación del Word también cuenta. */
    private const SEGUNDOS_MAX_ANEXOS = 12;

    public static function aplicar(PhpWordZipArchive $zip, EvaluadoOrden $evaluado): void
    {
        try {
            self::aplicarInterno($zip, $evaluado);
        } catch (\Throwable $e) {
            Log::warning('Informe Word: se omitieron anexos para no bloquear la descarga.', [
                'evaluado_id' => $evaluado->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private static function aplicarInterno(PhpWordZipArchive $zip, EvaluadoOrden $evaluado): void
    {
        $evaluado->loadMissing(['documentos', 'cuestionario']);
        self::aplicarPapeleria($zip, $evaluado);
    }

    /**
     * @return list<array{bytes: string, extension: string, widthPx: int, heightPx: int}>
     */
    private static function recopilarImagenes(EvaluadoOrden $evaluado): array
    {
        $documentos = $evaluado->documentos
            ->filter(fn (DocumentoEvaluado $documento): bool => $documento->es_imagen && $documento->tipo_documento === 'foto_tatuaje')
            ->values();

        $imagenes = [];

        foreach ($documentos as $documento) {
            $ruta = Storage::disk('local')->path($documento->ruta_archivo);
            $media = InformeWordFoto::prepararMedia($ruta);
            if ($media === null) {
                continue;
            }

            $imagenes[] = [
                'bytes' => $media['bytes'],
                'extension' => $media['extension'],
                'widthPx' => $media['widthPx'],
                'heightPx' => $media['heightPx'],
            ];
        }

        return $imagenes;
    }

    private static function aplicarPapeleria(PhpWordZipArchive $zip, EvaluadoOrden $evaluado): void
    {
        $documentos = InformeWordAnexosPapeleria::documentosParaWord($evaluado);
        $imagenesTatuaje = self::recopilarImagenes($evaluado);
        if ($documentos->isEmpty() && $imagenesTatuaje === []) {
            return;
        }

        $documentXml = InformeWordZip::leerEntrada($zip, 'word/document.xml');
        $relsXml = InformeWordZip::leerEntrada($zip, 'word/_rels/document.xml.rels');
        if ($documentXml === false || $relsXml === false) {
            return;
        }

        $posicionInsercion = InformeWordXml::posicionFinTablaPorMarcador($documentXml, 'TATUAJES')
            ?? InformeWordXml::posicionAntesDeSectPr($documentXml);
        if ($posicionInsercion === null) {
            return;
        }

        $siguienteRelId = InformeWordXml::siguienteRelId($relsXml);
        $siguienteDocPrId = 930001;
        $indice = 0;
        $filasTabla = [];
        $limite = microtime(true) + self::SEGUNDOS_MAX_ANEXOS;

        foreach ($documentos as $documento) {
            $etiqueta = DocumentoEvaluado::tiposDocumento()[$documento->tipo_documento] ?? $documento->tipo_documento;

            if (microtime(true) > $limite) {
                $filasTabla[] = self::construirFilaPapeleriaTexto('[Omitido] ' . $etiqueta);
                continue;
            }

            if ($documento->es_imagen) {
                $ruta = Storage::disk('local')->path($documento->ruta_archivo);
                $media = InformeWordFoto::prepararMedia($ruta);
                if ($media === null) {
                    $filasTabla[] = self::construirFilaPapeleriaTexto('[Imagen] ' . $etiqueta);

                    continue;
                }

                $fila = self::construirFilaPapeleriaImagen(
                    $media['bytes'],
                    $media['extension'],
                    $media['widthPx'] ?? 480,
                    $media['heightPx'] ?? 360,
                    $zip,
                    $relsXml,
                    $siguienteRelId,
                    $siguienteDocPrId,
                    $indice,
                    'anexo_papeleria_'
                );
                if ($fila !== '') {
                    $filasTabla[] = $fila;
                }

                continue;
            }

            if ($documento->es_pdf) {
                if (self::RASTERIZAR_PDF_EN_WORD) {
                    $ruta = Storage::disk('local')->path($documento->ruta_archivo);
                    $paginas = InformeWordPdfPaginas::paginasComoPng($ruta);

                    if ($paginas !== []) {
                        foreach ($paginas as $pagina) {
                            $fila = self::construirFilaPapeleriaImagen(
                                $pagina['bytes'],
                                'png',
                                $pagina['widthPx'],
                                $pagina['heightPx'],
                                $zip,
                                $relsXml,
                                $siguienteRelId,
                                $siguienteDocPrId,
                                $indice,
                                'anexo_papeleria_pdf_'
                            );
                            if ($fila !== '') {
                                $filasTabla[] = $fila;
                            }
                        }

                        continue;
                    }
                }

                $filasTabla[] = self::construirFilaPapeleriaTexto('[PDF] ' . $etiqueta);

                continue;
            }

            $filasTabla[] = self::construirFilaPapeleriaTexto('[Documento] ' . $etiqueta);
        }

        foreach ($imagenesTatuaje as $imagen) {
            if (microtime(true) > $limite) {
                $filasTabla[] = self::construirFilaPapeleriaTexto('[Omitido] Tatuajes');
                break;
            }

            $fila = self::construirFilaPapeleriaImagen(
                $imagen['bytes'],
                $imagen['extension'],
                $imagen['widthPx'] ?? 480,
                $imagen['heightPx'] ?? 360,
                $zip,
                $relsXml,
                $siguienteRelId,
                $siguienteDocPrId,
                $indice,
                'anexo_tatuaje_'
            );
            if ($fila !== '') {
                $filasTabla[] = $fila;
            }
        }

        if ($filasTabla === []) {
            return;
        }

        $fragmento = InformeWordXml::parrafoTituloSeccion('DOCUMENTOS ADJUNTOS:')
            . InformeWordXml::construirTablaUnaColumna($filasTabla);

        $documentXml = InformeWordXml::insertarEnPosicion($documentXml, $posicionInsercion, $fragmento);

        if (! self::documentoAptoParaWord($documentXml, $relsXml, 'papelería adjunta')) {
            return;
        }

        InformeWordZip::reemplazarEntrada($zip, 'word/document.xml', $documentXml);
        InformeWordZip::reemplazarEntrada($zip, 'word/_rels/document.xml.rels', $relsXml);
    }

    /**
     * Descarta el bloque en lugar de entregar un .docx que Word no pueda abrir; el informe
     * sigue siendo utilizable y el problema queda registrado para corregirlo.
     */
    private static function documentoAptoParaWord(string $documentXml, string $relsXml, string $bloque): bool
    {
        $problemas = InformeWordXml::problemasEstructura($documentXml);
        $relacionesFaltantes = InformeWordXml::relacionesFaltantes($documentXml, $relsXml);

        if ($problemas === [] && $relacionesFaltantes === []) {
            return true;
        }

        Log::warning('Informe Word: se omitió ' . $bloque . ' por XML inválido.', [
            'problemas' => $problemas,
            'relaciones_faltantes' => $relacionesFaltantes,
        ]);

        return false;
    }

    private static function construirFilaPapeleriaTexto(string $texto): string
    {
        $celda = InformeWordXml::construirCeldaSimple(10800, InformeWordXml::establecerTextoCelda(
            '<w:tc><w:tcPr><w:tcW w:w="10800" w:type="dxa"/></w:tcPr><w:p/></w:tc>',
            $texto
        ));

        return InformeWordXml::construirFilaUnaColumna($celda);
    }

    private static function construirFilaPapeleriaImagen(
        string $bytes,
        string $extension,
        int $widthPx,
        int $heightPx,
        PhpWordZipArchive $zip,
        string &$relsXml,
        string &$siguienteRelId,
        int &$siguienteDocPrId,
        int &$indice,
        string $prefijoArchivo
    ): string {
        $relId = $siguienteRelId;
        $siguienteRelId = 'rId' . (((int) preg_replace('/\D/', '', $relId)) + 1);
        $nombreArchivo = $prefijoArchivo . (++$indice) . '.' . $extension;

        $relsXml = InformeWordXml::agregarRelacionImagen($relsXml, $relId, $nombreArchivo);
        $zip->addFromString('word/media/' . $nombreArchivo, $bytes);
        InformeWordXml::registrarExtensionMedia($zip, $extension);

        $celdaBase = '<w:tc><w:tcPr><w:tcW w:w="10800" w:type="dxa"/></w:tcPr><w:p/></w:tc>';
        ['cx' => $cx, 'cy' => $cy] = InformeWordFoto::dimensionesEmu($widthPx, $heightPx, 620, 820);
        $celdaImagen = InformeWordXml::construirCeldaSimple(10800, InformeWordFoto::establecerImagenCelda(
            $celdaBase,
            $relId,
            $cx,
            $cy,
            $siguienteDocPrId
        ));
        $siguienteDocPrId++;

        return InformeWordXml::construirFilaUnaColumna($celdaImagen);
    }
}
