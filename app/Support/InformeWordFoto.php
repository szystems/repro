<?php

namespace App\Support;

/**
 * Coloca la foto del evaluado en el informe. En las plantillas v2 la foto sustituye a la silueta
 * flotante que ya viene maquetada a la izquierda de la tabla DATOS GENERALES; en las plantillas
 * legacy, que no tienen ese marco, se inserta centrada encima de la tabla de encabezado.
 */
class InformeWordFoto
{
    /** Resolución con la que se guarda el mapa de bits: cubre el marco de la plantilla (~3,1 × 4,6 pulg). */
    private const MAX_ALMACEN_ANCHO_PX = 420;

    private const MAX_ALMACEN_ALTO_PX = 560;

    /** Tamaño visible cuando se inserta centrada (plantillas legacy). Proporción ~3:4 tipo carnet. */
    private const MAX_ANCHO_PX_FOTO = 240;

    private const MAX_ALTO_PX_FOTO = 300;

    /** Límite genérico para otras imágenes (anexos). */
    private const MAX_ANCHO_PX = 480;

    private const MAX_ALTO_PX = 360;

    private const EMU_POR_PX = 9525;

    /** Una línea de espacio (~12 pt) antes y después de la foto. */
    private const ESPACIO_LINEA_TWIPS = 240;

    public static function prepararPng(string $rutaFotoCandidato): ?string
    {
        $media = self::prepararMedia($rutaFotoCandidato);

        return $media !== null && $media['extension'] === 'png' ? $media['bytes'] : null;
    }

    /**
     * @return array{bytes: string, extension: string, widthPx: int, heightPx: int}|null
     */
    public static function prepararMedia(string $rutaFotoCandidato): ?array
    {
        if (! is_readable($rutaFotoCandidato)) {
            return null;
        }

        $info = @getimagesize($rutaFotoCandidato);
        $widthPx = (int) ($info[0] ?? 0);
        $heightPx = (int) ($info[1] ?? 0);
        $tamano = @filesize($rutaFotoCandidato);

        // Archivos >5 MB en el .docx saturan LiteSpeed (L0). Sí se aceptan JPEGs de celular
        // de 8–12 MP si pesan poco: Word escala el marco y no hay que pasarlos por GD.
        if (is_int($tamano) && $tamano > 5_000_000) {
            return null;
        }

        $demasiadoGrandeParaGd = $widthPx > 0 && $heightPx > 0 && ($widthPx * $heightPx) > 8_000_000;

        if (! $demasiadoGrandeParaGd && function_exists('imagecreatetruecolor')) {
            $foto = self::cargarImagen($rutaFotoCandidato);
            if ($foto !== null) {
                $redimensionada = self::redimensionarProporcional(
                    $foto,
                    self::MAX_ALMACEN_ANCHO_PX,
                    self::MAX_ALMACEN_ALTO_PX
                );
                \imagedestroy($foto);

                if ($redimensionada !== null) {
                    $widthPx = \imagesx($redimensionada);
                    $heightPx = \imagesy($redimensionada);
                    \ob_start();
                    \imagepng($redimensionada);
                    $bytes = \ob_get_clean();
                    \imagedestroy($redimensionada);

                    if ($bytes !== false && $bytes !== '') {
                        return [
                            'bytes' => $bytes,
                            'extension' => 'png',
                            'widthPx' => $widthPx,
                            'heightPx' => $heightPx,
                        ];
                    }
                }
            }
        }

        $extension = strtolower(pathinfo($rutaFotoCandidato, PATHINFO_EXTENSION));
        if (! in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            return null;
        }

        $bytes = file_get_contents($rutaFotoCandidato);
        if ($bytes === false || $bytes === '') {
            return null;
        }

        if ($widthPx <= 0 || $heightPx <= 0) {
            $widthPx = self::MAX_ALMACEN_ANCHO_PX;
            $heightPx = (int) round(self::MAX_ALMACEN_ALTO_PX * 0.75);
        }

        return [
            'bytes' => $bytes,
            'extension' => $extension === 'jpeg' ? 'jpg' : $extension,
            'widthPx' => $widthPx,
            'heightPx' => $heightPx,
        ];
    }

    /**
     * @param  array{bytes: string, extension: string, widthPx: int, heightPx: int}  $media
     */
    public static function insertarEnDocumento(string $documentXml, array $media, string $relId): string
    {
        $enMarcoPlantilla = self::sustituirMarcoFoto($documentXml, $media, $relId);
        if ($enMarcoPlantilla !== null) {
            return $enMarcoPlantilla;
        }

        ['cx' => $cx, 'cy' => $cy] = self::dimensionesEmu(
            $media['widthPx'],
            $media['heightPx'],
            self::MAX_ANCHO_PX_FOTO,
            self::MAX_ALTO_PX_FOTO
        );
        $parrafo = self::parrafoImagenCentrada(
            $relId,
            $cx,
            $cy,
            spacingBefore: self::ESPACIO_LINEA_TWIPS,
            spacingAfter: self::ESPACIO_LINEA_TWIPS
        );

        return self::prepararEspacioTablaProceso($documentXml, $parrafo);
    }

    /**
     * Sustituye la silueta de la plantilla por la foto real conservando su anclaje, de modo que la
     * foto queda al costado de DATOS GENERALES tal como en el formato entregado por el cliente.
     * Se recalcula el tamaño para llenar el marco sin deformar la imagen.
     *
     * @param  array{bytes: string, extension: string, widthPx: int, heightPx: int}  $media
     */
    private static function sustituirMarcoFoto(string $documentXml, array $media, string $relId): ?string
    {
        $limitesDatos = InformeWordXml::limitesTablaPorMarcador($documentXml, 'DATOS GENERALES');
        if ($limitesDatos === null) {
            return null;
        }

        $marco = InformeWordXml::parrafoMarcoFoto($documentXml, $limitesDatos[0]);
        if ($marco === null) {
            return null;
        }

        if (preg_match('/<wp:extent cx="(\d+)" cy="(\d+)"\s*\/>/', $marco['xml'], $extent) !== 1) {
            return null;
        }

        ['cx' => $cx, 'cy' => $cy] = self::dimensionesEmuEnMarco(
            $media['widthPx'],
            $media['heightPx'],
            (int) $extent[1],
            (int) $extent[2]
        );

        $parrafo = preg_replace('/r:embed="rId\d+"/', 'r:embed="' . $relId . '"', $marco['xml'], 1) ?? $marco['xml'];
        // El recorte de la plantilla estaba calculado para la silueta y desencuadraría la foto.
        $parrafo = preg_replace('/<a:srcRect\b[^>]*\/>/', '', $parrafo) ?? $parrafo;
        $parrafo = preg_replace(
            '/<wp:extent cx="\d+" cy="\d+"\s*\/>/',
            '<wp:extent cx="' . $cx . '" cy="' . $cy . '"/>',
            $parrafo,
            1
        ) ?? $parrafo;
        $parrafo = preg_replace(
            '/<a:ext cx="\d+" cy="\d+"\s*\/>/',
            '<a:ext cx="' . $cx . '" cy="' . $cy . '"/>',
            $parrafo,
            1
        ) ?? $parrafo;
        $parrafo = preg_replace('/(<wp:docPr id="\d+" name=)"[^"]*"/', '$1"Foto evaluado"', $parrafo, 1) ?? $parrafo;

        return substr($documentXml, 0, $marco['start'])
            . $parrafo
            . substr($documentXml, $marco['end']);
    }

    /**
     * Escala la foto para ocupar el marco de la plantilla sin recortarla ni deformarla.
     *
     * @return array{cx: int, cy: int}
     */
    public static function dimensionesEmuEnMarco(int $widthPx, int $heightPx, int $marcoCx, int $marcoCy): array
    {
        if ($widthPx <= 0 || $heightPx <= 0 || $marcoCx <= 0 || $marcoCy <= 0) {
            return ['cx' => $marcoCx, 'cy' => $marcoCy];
        }

        $anchoNatural = $widthPx * self::EMU_POR_PX;
        $altoNatural = $heightPx * self::EMU_POR_PX;
        $escala = min($marcoCx / $anchoNatural, $marcoCy / $altoNatural);

        return [
            'cx' => (int) round($anchoNatural * $escala),
            'cy' => (int) round($altoNatural * $escala),
        ];
    }

    public static function compactarEspacioSinFoto(string $documentXml): string
    {
        return self::prepararEspacioTablaProceso($documentXml, null);
    }

    public static function establecerImagenCelda(
        string $celdaXml,
        string $relId,
        int $anchoEmu = 2286000,
        int $altoEmu = 2286000,
        int $docPrId = 910001
    ): string {
        $parrafo = self::parrafoImagenCentrada($relId, $anchoEmu, $altoEmu, $docPrId);

        return preg_replace('/<w:tc\b([^>]*)>.*?<\/w:tc>/s', '<w:tc$1>' . $parrafo . '</w:tc>', $celdaXml, 1) ?? $celdaXml;
    }

    /**
     * @return array{cx: int, cy: int}
     */
    public static function dimensionesEmu(
        int $widthPx,
        int $heightPx,
        int $maxAnchoPx = self::MAX_ANCHO_PX,
        int $maxAltoPx = self::MAX_ALTO_PX
    ): array {
        if ($widthPx <= 0 || $heightPx <= 0) {
            return ['cx' => 2743200, 'cy' => 2057400];
        }

        $escala = min(1.0, $maxAnchoPx / $widthPx, $maxAltoPx / $heightPx);

        return [
            'cx' => (int) round($widthPx * $escala * self::EMU_POR_PX),
            'cy' => (int) round($heightPx * $escala * self::EMU_POR_PX),
        ];
    }

    private static function prepararEspacioTablaProceso(string $documentXml, ?string $parrafoFoto): string
    {
        // Legacy: Proceso: · v2 cliente: Agencia/Sede: / DATOS GENERALES
        $limites = InformeWordXml::limitesTablaPorMarcador($documentXml, 'Proceso:')
            ?? InformeWordXml::limitesTablaPorMarcador($documentXml, 'Agencia/Sede:')
            ?? InformeWordXml::limitesTablaPorMarcador($documentXml, 'DATOS GENERALES');
        if ($limites === null) {
            return $documentXml;
        }

        [$inicioTabla, $finTabla] = $limites;
        $antes = substr($documentXml, 0, $inicioTabla);
        $tabla = substr($documentXml, $inicioTabla, $finTabla - $inicioTabla);
        $despues = substr($documentXml, $finTabla);

        $tabla = InformeWordXml::quitarAnclajeFlotanteTabla($tabla);
        $antes = InformeWordXml::quitarParrafosAnclados($antes);
        if ($parrafoFoto === null) {
            $antes = InformeWordXml::reemplazarParrafosAncladosPorTexto($antes);
        }
        $antes = InformeWordXml::quitarParrafosVacios($antes);
        $despues = InformeWordXml::limpiarZonaFotoPerfilV2($despues);

        if ($parrafoFoto !== null) {
            $antes .= $parrafoFoto;
        } else {
            $antes = InformeWordXml::quitarParrafosVaciosCola($antes);
        }

        return $antes . $tabla . $despues;
    }

    private static function parrafoImagenCentrada(
        string $relId,
        int $cx,
        int $cy,
        int $docPrId = 900001,
        int $spacingBefore = 0,
        int $spacingAfter = 0
    ): string {
        return '<w:p w14:paraId="F0T0EVAD" w14:textId="77777777" w:rsidR="006332A0" w:rsidRDefault="006332A0" w:rsidP="006332A0">'
            . '<w:pPr><w:spacing w:before="' . $spacingBefore . '" w:after="' . $spacingAfter . '" w:line="240" w:lineRule="auto"/>'
            . '<w:jc w:val="center"/></w:pPr>'
            . '<w:r><w:rPr><w:noProof/></w:rPr><w:drawing>'
            . '<wp:inline distT="0" distB="0" distL="0" distR="0">'
            . '<wp:extent cx="' . $cx . '" cy="' . $cy . '"/>'
            . '<wp:effectExtent l="0" t="0" r="0" b="0"/>'
            . '<wp:docPr id="' . $docPrId . '" name="Foto evaluado"/>'
            . '<wp:cNvGraphicFramePr><a:graphicFrameLocks xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" noChangeAspect="1"/></wp:cNvGraphicFramePr>'
            . '<a:graphic xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main">'
            . '<a:graphicData uri="http://schemas.openxmlformats.org/drawingml/2006/picture">'
            . '<pic:pic xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture">'
            . '<pic:nvPicPr><pic:cNvPr id="' . $docPrId . '" name="Foto evaluado"/><pic:cNvPicPr/></pic:nvPicPr>'
            . '<pic:blipFill><a:blip r:embed="' . $relId . '"/><a:stretch><a:fillRect/></a:stretch></pic:blipFill>'
            . '<pic:spPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="' . $cx . '" cy="' . $cy . '"/></a:xfrm>'
            . '<a:prstGeom prst="rect"><a:avLst/></a:prstGeom></pic:spPr>'
            . '</pic:pic></a:graphicData></a:graphic>'
            . '</wp:inline></w:drawing></w:r></w:p>';
    }

    private static function cargarImagen(string $ruta): ?\GdImage
    {
        $extension = strtolower(pathinfo($ruta, PATHINFO_EXTENSION));

        $imagen = match ($extension) {
            'png' => @\imagecreatefrompng($ruta),
            'jpg', 'jpeg' => self::cargarJpeg($ruta),
            'webp' => function_exists('imagecreatefromwebp') ? @\imagecreatefromwebp($ruta) : false,
            default => false,
        };

        return $imagen === false ? null : $imagen;
    }

    private static function cargarJpeg(string $ruta): \GdImage|false
    {
        if (function_exists('imagecreatefromjpeg')) {
            return @\imagecreatefromjpeg($ruta);
        }

        return false;
    }

    private static function redimensionarProporcional(\GdImage $origen, int $maxAncho, int $maxAlto): ?\GdImage
    {
        $origenAncho = \imagesx($origen);
        $origenAlto = \imagesy($origen);

        if ($origenAncho <= 0 || $origenAlto <= 0) {
            return null;
        }

        $escala = min(1.0, $maxAncho / $origenAncho, $maxAlto / $origenAlto);
        $destinoAncho = (int) max(1, round($origenAncho * $escala));
        $destinoAlto = (int) max(1, round($origenAlto * $escala));

        if ($destinoAncho === $origenAncho && $destinoAlto === $origenAlto) {
            $destino = \imagecreatetruecolor($destinoAncho, $destinoAlto);
            if ($destino === false) {
                return null;
            }

            \imagecopy($destino, $origen, 0, 0, 0, 0, $destinoAncho, $destinoAlto);

            return $destino;
        }

        $destino = \imagecreatetruecolor($destinoAncho, $destinoAlto);
        if ($destino === false) {
            return null;
        }

        \imagecopyresampled($destino, $origen, 0, 0, 0, 0, $destinoAncho, $destinoAlto, $origenAncho, $origenAlto);

        return $destino;
    }
}
