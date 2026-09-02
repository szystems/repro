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
        $imagenes = self::recopilarImagenes($evaluado);

        if ($imagenes !== []) {
            $documentXml = InformeWordZip::leerEntrada($zip, 'word/document.xml');
            $relsXml = InformeWordZip::leerEntrada($zip, 'word/_rels/document.xml.rels');

            if ($documentXml === false || $relsXml === false) {
                return;
            }

            $siguienteRelId = InformeWordXml::siguienteRelId($relsXml);
            $siguienteDocPrId = 920001;

            $documentXml = InformeWordXml::reemplazarTablaPorMarcador($documentXml, 'TATUAJES', function (string $tabla) use (
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

                $plantillaFila = $filas[1] ?? $filas[0];
                $filasResultado = $filas;

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

            if (! self::documentoAptoParaWord($documentXml, $relsXml, 'anexos de tatuajes')) {
                return;
            }

            InformeWordZip::reemplazarEntrada($zip, 'word/document.xml', $documentXml);
            InformeWordZip::reemplazarEntrada($zip, 'word/_rels/document.xml.rels', $relsXml);
        }

        self::aplicarPapeleria($zip, $evaluado);
    }

    /**
     * @return list<array{bytes: string, extension: string, descripcion: string}>
     */
    private static function recopilarImagenes(EvaluadoOrden $evaluado): array
    {
        $tatuajes = InformeDatos::tablas($evaluado)['tatuajes'] ?? [];
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

    private static function aplicarPapeleria(PhpWordZipArchive $zip, EvaluadoOrden $evaluado): void
    {
        $documentos = InformeWordAnexosPapeleria::documentosParaWord($evaluado);
        if ($documentos->isEmpty()) {
            return;
        }

        $documentXml = InformeWordZip::leerEntrada($zip, 'word/document.xml');
        $relsXml = InformeWordZip::leerEntrada($zip, 'word/_rels/document.xml.rels');
        if ($documentXml === false || $relsXml === false) {
            return;
        }

        $posicionInsercion = InformeWordXml::posicionFinTablaPorMarcador($documentXml, 'TATUAJES');
        if ($posicionInsercion === null) {
            return;
        }

        $siguienteRelId = InformeWordXml::siguienteRelId($relsXml);
        $siguienteDocPrId = 930001;
        $indice = 0;
        $filasTabla = [];

        $celdaTitulo1 = InformeWordXml::construirCeldaSimple(3600, InformeWordXml::establecerTextoCelda(
            '<w:tc><w:tcPr><w:tcW w:w="3600" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="002060"/></w:tcPr><w:p/></w:tc>',
            'Documento'
        ));
        $celdaTitulo2 = InformeWordXml::construirCeldaSimple(7200, InformeWordXml::establecerTextoCelda(
            '<w:tc><w:tcPr><w:tcW w:w="7200" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="002060"/></w:tcPr><w:p/></w:tc>',
            'Descripción'
        ));
        $filasTabla[] = InformeWordXml::construirFilaDosColumnas($celdaTitulo1, $celdaTitulo2);
        $limite = microtime(true) + self::SEGUNDOS_MAX_ANEXOS;

        foreach ($documentos as $documento) {
            $etiqueta = DocumentoEvaluado::tiposDocumento()[$documento->tipo_documento] ?? $documento->tipo_documento;
            $descripcionBase = $etiqueta . "\n" . $documento->nombre_original;

            if (microtime(true) > $limite) {
                $filasTabla[] = self::construirFilaPapeleriaTexto($descripcionBase, '[Omitido] ' . $etiqueta);
                continue;
            }

            if ($documento->es_imagen) {
                $ruta = Storage::disk('local')->path($documento->ruta_archivo);
                $media = InformeWordFoto::prepararMedia($ruta);
                if ($media === null) {
                    $filasTabla[] = self::construirFilaPapeleriaTexto($descripcionBase, '[Imagen] ' . $etiqueta);

                    continue;
                }

                $fila = self::construirFilaPapeleriaDosColumnas(
                    $media['bytes'],
                    $media['extension'],
                    $media['widthPx'] ?? 480,
                    $media['heightPx'] ?? 360,
                    $descripcionBase,
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
                        foreach ($paginas as $numeroPagina => $pagina) {
                            $descripcion = $descripcionBase;
                            if ($numeroPagina > 0) {
                                $descripcion = $etiqueta . ' — pág. ' . ($numeroPagina + 1);
                            }

                            $fila = self::construirFilaPapeleriaDosColumnas(
                                $pagina['bytes'],
                                'png',
                                $pagina['widthPx'],
                                $pagina['heightPx'],
                                $descripcion,
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

                $filasTabla[] = self::construirFilaPapeleriaTexto($descripcionBase, '[PDF] ' . $etiqueta);

                continue;
            }

            $filasTabla[] = self::construirFilaPapeleriaTexto($descripcionBase, '[Documento] ' . $etiqueta);
        }

        if (count($filasTabla) <= 1) {
            return;
        }

        $fragmento = InformeWordXml::parrafoTituloSeccion('DOCUMENTOS ADJUNTOS:')
            . InformeWordXml::construirTablaDosColumnas($filasTabla);

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

    private static function construirFilaPapeleriaTexto(string $descripcion, string $tituloColumna): string
    {
        $celdaDoc = InformeWordXml::construirCeldaSimple(3600, InformeWordXml::establecerTextoCelda(
            '<w:tc><w:tcPr><w:tcW w:w="3600" w:type="dxa"/></w:tcPr><w:p/></w:tc>',
            $tituloColumna
        ));
        $celdaDesc = InformeWordXml::construirCeldaSimple(7200, InformeWordXml::establecerCeldaParrafos(
            '<w:tc><w:tcPr><w:tcW w:w="7200" w:type="dxa"/></w:tcPr><w:p/></w:tc>',
            explode("\n", $descripcion)
        ));

        return InformeWordXml::construirFilaDosColumnas($celdaDoc, $celdaDesc);
    }

    private static function construirFilaPapeleriaDosColumnas(
        string $bytes,
        string $extension,
        int $widthPx,
        int $heightPx,
        string $descripcion,
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

        $celdaBase = '<w:tc><w:tcPr><w:tcW w:w="3600" w:type="dxa"/></w:tcPr><w:p/></w:tc>';
        ['cx' => $cx, 'cy' => $cy] = InformeWordFoto::dimensionesEmu($widthPx, $heightPx, 480);
        $celdaImagen = InformeWordXml::construirCeldaSimple(3600, InformeWordFoto::establecerImagenCelda(
            $celdaBase,
            $relId,
            $cx,
            $cy,
            $siguienteDocPrId
        ));
        $siguienteDocPrId++;

        $celdaDesc = InformeWordXml::construirCeldaSimple(7200, InformeWordXml::establecerCeldaParrafos(
            '<w:tc><w:tcPr><w:tcW w:w="7200" w:type="dxa"/></w:tcPr><w:p/></w:tc>',
            explode("\n", $descripcion)
        ));

        return InformeWordXml::construirFilaDosColumnas($celdaImagen, $celdaDesc);
    }

    private static function construirFilaAnexoImagen(
        string $plantillaFila,
        string $bytes,
        string $extension,
        int $widthPx,
        int $heightPx,
        string $descripcion,
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

        $celdas = InformeWordXml::celdasFila($plantillaFila);

        if (isset($celdas[0])) {
            ['cx' => $cx, 'cy' => $cy] = InformeWordFoto::dimensionesEmu($widthPx, $heightPx, 480);
            $celdas[0] = InformeWordFoto::establecerImagenCelda(
                $celdas[0],
                $relId,
                $cx,
                $cy,
                $siguienteDocPrId
            );
            $siguienteDocPrId++;
        }

        if (isset($celdas[1])) {
            $celdas[1] = InformeWordXml::establecerCeldaParrafos($celdas[1], explode("\n", $descripcion));
        }

        preg_match('/<w:tr\b[^>]*>/', $plantillaFila, $apertura);

        return ($apertura[0] ?? '<w:tr>') . implode('', $celdas) . '</w:tr>';
    }

    private static function construirFilaAnexoTexto(string $plantillaFila, string $titulo, string $descripcion): string
    {
        $celdas = InformeWordXml::celdasFila($plantillaFila);

        if (isset($celdas[0])) {
            $celdas[0] = InformeWordXml::establecerCeldaParrafos($celdas[0], [$titulo]);
        }

        if (isset($celdas[1])) {
            $celdas[1] = InformeWordXml::establecerCeldaParrafos($celdas[1], explode("\n", $descripcion));
        }

        preg_match('/<w:tr\b[^>]*>/', $plantillaFila, $apertura);

        return ($apertura[0] ?? '<w:tr>') . implode('', $celdas) . '</w:tr>';
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
