<?php

namespace App\Support;

use ZipArchive;

/** Utilidades XML para plantillas Word (.docx) sin destruir tablas ni diseño. */
class InformeWordXml
{
    public static function escapar(string $texto): string
    {
        return htmlspecialchars($texto, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }

    public static function esValido(string $xml): bool
    {
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $valido = $dom->loadXML($xml);
        libxml_clear_errors();

        return $valido;
    }

    public static function reemplazarTexto(string $xml, string $buscar, string $reemplazo, int $limit = -1): string
    {
        if ($buscar === '') {
            return $xml;
        }

        $chars = preg_split('//u', $buscar, -1, PREG_SPLIT_NO_EMPTY);
        $pattern = implode('(?:<[^>]+>)*', array_map(static fn (string $c): string => preg_quote($c, '/'), $chars));

        return preg_replace(
            '/' . $pattern . '/u',
            self::escapar($reemplazo),
            $xml,
            $limit
        ) ?? $xml;
    }

    public static function insertarTrasEtiqueta(string $xml, string $etiqueta, string $valor, int $limit = 1): string
    {
        if ($valor === '' || $valor === '—') {
            return $xml;
        }

        return self::reemplazarTexto($xml, $etiqueta, $etiqueta . $valor, $limit);
    }

    /**
     * @param  array<string, string>  $reemplazos
     */
    public static function reemplazarMarcadores(string $xml, array $reemplazos): string
    {
        foreach ($reemplazos as $marcador => $valor) {
            $xml = self::reemplazarTexto($xml, $marcador, $valor);
        }

        return $xml;
    }

    /** @return list<string> */
    public static function filasTabla(string $tablaXml): array
    {
        preg_match_all('/<w:tr\b[^>]*>.*?<\/w:tr>/s', $tablaXml, $coincidencias);

        return $coincidencias[0] ?? [];
    }

    /** @return list<string> */
    public static function celdasFila(string $filaXml): array
    {
        preg_match_all('/<w:tc\b.*?<\/w:tc>/s', $filaXml, $coincidencias);

        return $coincidencias[0] ?? [];
    }

    public static function establecerTextoCelda(string $celdaXml, string $texto): string
    {
        if (preg_match('/<w:t(?:\s+xml:space="preserve")?>/', $celdaXml)) {
            return preg_replace(
                '/(<w:t(?:\s+xml:space="preserve")?>)(.*?)(<\/w:t>)/s',
                '$1' . self::escapar($texto) . '$3',
                $celdaXml,
                1
            ) ?? $celdaXml;
        }

        $parrafo = '<w:p><w:r><w:rPr><w:rFonts w:ascii="Helvetica" w:hAnsi="Helvetica"/></w:rPr>'
            . '<w:t xml:space="preserve">' . self::escapar($texto) . '</w:t></w:r></w:p>';

        return preg_replace('/<\/w:tc>/', $parrafo . '</w:tc>', $celdaXml, 1) ?? $celdaXml;
    }

    /**
     * @param  list<string>  $textosPorColumna
     */
    public static function establecerFila(string $filaXml, array $textosPorColumna, int $columnaInicio = 0): string
    {
        $celdas = self::celdasFila($filaXml);

        foreach ($textosPorColumna as $indice => $texto) {
            $columna = $columnaInicio + $indice;
            if (! isset($celdas[$columna])) {
                continue;
            }

            $celdas[$columna] = self::establecerTextoCelda($celdas[$columna], $texto);
        }

        preg_match('/<w:tr\b[^>]*>/', $filaXml, $apertura);

        return ($apertura[0] ?? '<w:tr>') . implode('', $celdas) . '</w:tr>';
    }

    /**
     * @param  array<int, string>  $textosPorIndiceColumna
     */
    public static function establecerCeldasFila(string $filaXml, array $textosPorIndiceColumna): string
    {
        $celdas = self::celdasFila($filaXml);

        foreach ($textosPorIndiceColumna as $columna => $texto) {
            if (! isset($celdas[$columna])) {
                continue;
            }

            $celdas[$columna] = self::establecerTextoCelda($celdas[$columna], $texto);
        }

        preg_match('/<w:tr\b[^>]*>/', $filaXml, $apertura);

        return ($apertura[0] ?? '<w:tr>') . implode('', $celdas) . '</w:tr>';
    }

    public static function establecerCeldaParrafos(string $celdaXml, array $lineas): string
    {
        $parrafos = '';

        foreach ($lineas as $linea) {
            $parrafos .= self::parrafoTextoSimple($linea);
        }

        if ($parrafos === '') {
            $parrafos = '<w:p/>';
        }

        return preg_replace('/<w:tc\b([^>]*)>.*?<\/w:tc>/s', '<w:tc$1>' . $parrafos . '</w:tc>', $celdaXml, 1) ?? $celdaXml;
    }

    private static function parrafoTextoSimple(string $texto): string
    {
        return '<w:p><w:r><w:rPr><w:rFonts w:ascii="Helvetica" w:hAnsi="Helvetica"/></w:rPr>'
            . '<w:t xml:space="preserve">' . self::escapar($texto) . '</w:t></w:r></w:p>';
    }

    private static function textoParrafoAnclado(string $parrafoXml): string
    {
        if (preg_match('/<w:txbxContent>(.*?)<\/w:txbxContent>/s', $parrafoXml, $coincidencia) === 1) {
            preg_match_all('/<w:t(?:\s+xml:space="preserve")?>(.*?)<\/w:t>/s', $coincidencia[1], $textos);
            $texto = trim(html_entity_decode(implode('', $textos[1] ?? []), ENT_QUOTES | ENT_XML1, 'UTF-8'));

            if ($texto !== '') {
                return $texto;
            }
        }

        return self::textoParrafo($parrafoXml);
    }

    public static function textoCelda(string $celdaXml): string
    {
        preg_match_all('/<w:t(?:\s+xml:space="preserve")?>(.*?)<\/w:t>/s', $celdaXml, $coincidencias);
        $texto = html_entity_decode(implode('', $coincidencias[1] ?? []), ENT_QUOTES | ENT_XML1, 'UTF-8');

        return trim($texto);
    }

    public static function textoFila(string $filaXml): string
    {
        $partes = [];
        foreach (self::celdasFila($filaXml) as $celda) {
            $texto = self::textoCelda($celda);
            if ($texto !== '') {
                $partes[] = $texto;
            }
        }

        return implode(' ', $partes);
    }

    public static function filaTieneTextoEnColumnas(string $filaXml, int $columnaInicio = 0): bool
    {
        foreach (self::celdasFila($filaXml) as $indice => $celda) {
            if ($indice < $columnaInicio) {
                continue;
            }

            if (self::textoCelda($celda) !== '') {
                return true;
            }
        }

        return false;
    }

    /** @param list<string> $etiquetas */
    public static function eliminarFilasPorEtiquetas(string $tablaXml, array $etiquetas): string
    {
        $filas = self::filasTabla($tablaXml);
        $filas = array_values(array_filter($filas, function (string $fila) use ($etiquetas): bool {
            $texto = self::textoFila($fila);
            foreach ($etiquetas as $etiqueta) {
                if (str_contains($texto, $etiqueta)) {
                    return false;
                }
            }

            return true;
        }));

        return self::reconstruirTabla($tablaXml, $filas);
    }

    public static function podarFilasDatosVacias(
        string $tablaXml,
        int $indicePrimeraFilaDatos,
        ?int $indiceFilaPreservarDesde = null,
        int $columnaInicio = 0
    ): string {
        $filas = self::filasTabla($tablaXml);
        $resultado = array_slice($filas, 0, $indicePrimeraFilaDatos);
        $finRango = $indiceFilaPreservarDesde ?? count($filas);

        for ($indice = $indicePrimeraFilaDatos; $indice < $finRango; $indice++) {
            if (! isset($filas[$indice])) {
                break;
            }

            if (self::filaTieneTextoEnColumnas($filas[$indice], $columnaInicio)) {
                $resultado[] = $filas[$indice];
            }
        }

        if ($indiceFilaPreservarDesde !== null) {
            $resultado = array_merge($resultado, array_slice($filas, $indiceFilaPreservarDesde));
        }

        return self::reconstruirTabla($tablaXml, $resultado);
    }

    public static function eliminarFilasSinDatosEnRango(string $tablaXml, int $indiceInicio, int $indiceFin, int $columnaInicio = 1): string
    {
        $filas = self::filasTabla($tablaXml);
        $resultado = array_slice($filas, 0, $indiceInicio);

        for ($indice = $indiceInicio; $indice <= $indiceFin && isset($filas[$indice]); $indice++) {
            if (self::filaTieneTextoEnColumnas($filas[$indice], $columnaInicio)) {
                $resultado[] = $filas[$indice];
            }
        }

        if (isset($filas[$indiceFin + 1])) {
            $resultado = array_merge($resultado, array_slice($filas, $indiceFin + 1));
        }

        return self::reconstruirTabla($tablaXml, $resultado);
    }

    /**
     * @param  list<string>  $textosPorColumna
     */
    public static function reemplazarFilaEnTabla(string $tablaXml, int $indiceFila, array $textosPorColumna, int $columnaInicio = 0): string
    {
        $filas = self::filasTabla($tablaXml);
        if (! isset($filas[$indiceFila])) {
            return $tablaXml;
        }

        $filas[$indiceFila] = self::establecerFila($filas[$indiceFila], $textosPorColumna, $columnaInicio);

        return self::reconstruirTabla($tablaXml, $filas);
    }

    /** @param list<string> $filas */
    public static function reconstruirTabla(string $tablaXml, array $filas): string
    {
        $inicioFilas = strpos($tablaXml, '<w:tr');
        $finFilas = strrpos($tablaXml, '</w:tr>');

        if ($inicioFilas === false || $finFilas === false) {
            return $tablaXml;
        }

        $finFilas += strlen('</w:tr>');

        return substr($tablaXml, 0, $inicioFilas) . implode('', $filas) . substr($tablaXml, $finFilas);
    }

    public static function reemplazarTablaPorMarcador(string $xml, string $marcador, callable $callback): string
    {
        $limites = self::limitesTablaPorMarcador($xml, $marcador);
        if ($limites === null) {
            return $xml;
        }

        [$inicioTabla, $finTabla] = $limites;
        $tabla = substr($xml, $inicioTabla, $finTabla - $inicioTabla);
        $nueva = $callback($tabla);

        return substr($xml, 0, $inicioTabla) . $nueva . substr($xml, $finTabla);
    }

    /** @return array{0: int, 1: int}|null */
    public static function limitesTablaPorMarcador(string $xml, string $marcador): ?array
    {
        $posicion = strpos($xml, $marcador);
        if ($posicion === false) {
            return null;
        }

        $inicioTabla = strrpos(substr($xml, 0, $posicion), '<w:tbl>');
        if ($inicioTabla === false) {
            return null;
        }

        $finTabla = self::finTablaDesde($xml, $inicioTabla);
        if ($finTabla === null) {
            return null;
        }

        return [$inicioTabla, $finTabla];
    }

    private static function finTablaDesde(string $xml, int $inicioTabla): ?int
    {
        $posicion = $inicioTabla;
        $profundidad = 0;
        $longitud = strlen($xml);

        while ($posicion < $longitud) {
            if (substr($xml, $posicion, 7) === '<w:tbl>') {
                $profundidad++;
                $posicion += 7;

                continue;
            }

            if (substr($xml, $posicion, 8) === '</w:tbl>') {
                $profundidad--;
                $posicion += 8;

                if ($profundidad === 0) {
                    return $posicion;
                }

                continue;
            }

            $posicion++;
        }

        return null;
    }

    public static function textoParrafo(string $parrafoXml): string
    {
        preg_match_all('/<w:t(?:\s+xml:space="preserve")?>(.*?)<\/w:t>/s', $parrafoXml, $coincidencias);

        return trim(html_entity_decode(implode('', $coincidencias[1] ?? []), ENT_QUOTES | ENT_XML1, 'UTF-8'));
    }

    public static function parrafoEstaVacio(string $parrafoXml): bool
    {
        if (self::textoParrafo($parrafoXml) !== '') {
            return false;
        }

        return ! str_contains($parrafoXml, 'wp:inline')
            && ! str_contains($parrafoXml, 'wp:anchor')
            && ! str_contains($parrafoXml, 'v:imagedata');
    }

    public static function quitarParrafosVaciosCola(string $xml): string
    {
        while (true) {
            $ultimo = self::ultimoParrafoTopLevel($xml);
            if ($ultimo === null || ! self::parrafoEstaVacio($ultimo['xml'])) {
                break;
            }

            $xml = substr($xml, 0, $ultimo['start']) . substr($xml, $ultimo['end']);
        }

        return $xml;
    }

    public static function quitarParrafosVacios(string $xml): string
    {
        $resultado = '';
        $offset = 0;

        foreach (self::parrafosTopLevel($xml) as $parrafo) {
            $resultado .= substr($xml, $offset, $parrafo['start'] - $offset);

            if (! self::parrafoEstaVacio($parrafo['xml'])) {
                $resultado .= $parrafo['xml'];
            }

            $offset = $parrafo['end'];
        }

        return $resultado . substr($xml, $offset);
    }

    public static function quitarParrafosAnclados(string $xml): string
    {
        $resultado = '';
        $offset = 0;

        foreach (self::parrafosTopLevel($xml) as $parrafo) {
            $resultado .= substr($xml, $offset, $parrafo['start'] - $offset);

            if (! str_contains($parrafo['xml'], 'wp:anchor')) {
                $resultado .= $parrafo['xml'];
            }

            $offset = $parrafo['end'];
        }

        return $resultado . substr($xml, $offset);
    }

    public static function reemplazarParrafosAncladosPorTexto(string $xml): string
    {
        $resultado = '';
        $offset = 0;

        foreach (self::parrafosTopLevel($xml) as $parrafo) {
            $resultado .= substr($xml, $offset, $parrafo['start'] - $offset);

            if (str_contains($parrafo['xml'], 'wp:anchor')) {
                $texto = self::textoParrafoAnclado($parrafo['xml']);
                if ($texto !== '') {
                    $resultado .= self::parrafoTextoSimple($texto);
                }
            } else {
                $resultado .= $parrafo['xml'];
            }

            $offset = $parrafo['end'];
        }

        return $resultado . substr($xml, $offset);
    }

    public static function compactarEntreTablasPorMarcadores(
        string $xml,
        string $marcadorAnterior,
        string $marcadorSiguiente
    ): string {
        $posAnterior = strpos($xml, $marcadorAnterior);
        $posSiguiente = strpos($xml, $marcadorSiguiente, $posAnterior !== false ? $posAnterior : 0);

        if ($posAnterior === false || $posSiguiente === false) {
            return $xml;
        }

        $finTablaAnterior = strpos($xml, '</w:tbl>', $posAnterior);
        $inicioTablaSiguiente = strrpos(substr($xml, 0, $posSiguiente), '<w:tbl>');

        if ($finTablaAnterior === false || $inicioTablaSiguiente === false) {
            return $xml;
        }

        $finTablaAnterior += strlen('</w:tbl>');
        $entre = substr($xml, $finTablaAnterior, $inicioTablaSiguiente - $finTablaAnterior);
        $entre = self::quitarParrafosVacios($entre);

        return substr($xml, 0, $finTablaAnterior) . $entre . substr($xml, $inicioTablaSiguiente);
    }

    public static function aplicarKeepNextFilaTitulo(string $tablaXml, int $indiceFila = 0): string
    {
        $filas = self::filasTabla($tablaXml);
        if (! isset($filas[$indiceFila])) {
            return $tablaXml;
        }

        $fila = $filas[$indiceFila];

        if (! str_contains($fila, 'w:cantSplit')) {
            if (preg_match('/<w:trPr>/', $fila) === 1) {
                $fila = preg_replace('/<w:trPr>/', '<w:trPr><w:cantSplit/>', $fila, 1) ?? $fila;
            } else {
                $fila = preg_replace('/<w:tr\b([^>]*)>/', '<w:tr$1><w:trPr><w:cantSplit/></w:trPr>', $fila, 1) ?? $fila;
            }
        }

        if (! str_contains($fila, 'w:keepNext')) {
            if (preg_match('/<w:pPr>/', $fila) === 1) {
                $fila = preg_replace('/<w:pPr>/', '<w:pPr><w:keepNext/>', $fila) ?? $fila;
            } else {
                $fila = preg_replace('/<w:p\b([^>]*)>/', '<w:p$1><w:pPr><w:keepNext/></w:pPr>', $fila) ?? $fila;
            }
        }

        $filas[$indiceFila] = $fila;

        return self::reconstruirTabla($tablaXml, $filas);
    }

    /**
     * @return array{start: int, end: int, xml: string}|null
     */
    private static function ultimoParrafoTopLevel(string $xml): ?array
    {
        $ultimo = null;

        foreach (self::parrafosTopLevel($xml) as $parrafo) {
            $ultimo = $parrafo;
        }

        return $ultimo;
    }

    /**
     * @return list<array{start: int, end: int, xml: string}>
     */
    private static function parrafosTopLevel(string $xml): array
    {
        $parrafos = [];
        $offset = 0;
        $longitud = strlen($xml);

        while ($offset < $longitud) {
            if (! preg_match('/<w:p\b/', $xml, $coincidencia, PREG_OFFSET_CAPTURE, $offset)) {
                break;
            }

            $inicio = $coincidencia[0][1];
            $fin = self::finParrafoTopLevel($xml, $inicio);

            if ($fin === null) {
                break;
            }

            $parrafos[] = [
                'start' => $inicio,
                'end' => $fin,
                'xml' => substr($xml, $inicio, $fin - $inicio),
            ];

            $offset = $fin;
        }

        return $parrafos;
    }

    private static function finParrafoTopLevel(string $xml, int $inicio): ?int
    {
        if (! preg_match('/<w:p\b/', $xml, $coincidencia, PREG_OFFSET_CAPTURE, $inicio)) {
            return null;
        }

        $posicion = $coincidencia[0][1];
        $profundidad = 0;
        $longitud = strlen($xml);

        while ($posicion < $longitud) {
            if (preg_match('/<w:p\b/', $xml, $apertura, PREG_OFFSET_CAPTURE, $posicion) === 1
                && $apertura[0][1] === $posicion) {
                $profundidad++;
                $posicion += strlen($apertura[0][0]);

                continue;
            }

            if (substr($xml, $posicion, 6) === '</w:p>') {
                $profundidad--;

                if ($profundidad === 0) {
                    return $posicion + 6;
                }

                $posicion += 6;

                continue;
            }

            $posicion++;
        }

        return null;
    }

    public static function quitarAnclajeFlotanteTabla(string $tablaXml): string
    {
        return preg_replace('/<w:tblpPr\b[^>]*\/>/', '', $tablaXml) ?? $tablaXml;
    }

    /**
     * @param  array<int, int>  $anchosPorIndice  Anchos en dxa (twips)
     */
    public static function ajustarAnchosColumnas(string $tablaXml, array $anchosPorIndice): string
    {
        if (preg_match('/<w:tblGrid>(.*?)<\/w:tblGrid>/s', $tablaXml, $coincidencia) === 1) {
            $gridInterno = $coincidencia[1];
            preg_match_all('/<w:gridCol\b[^>]*\/>/', $gridInterno, $columnas);
            foreach ($columnas[0] as $indice => $columna) {
                if (! isset($anchosPorIndice[$indice])) {
                    continue;
                }

                $nueva = preg_replace(
                    '/w:w="\d+"/',
                    'w:w="' . $anchosPorIndice[$indice] . '"',
                    $columna
                ) ?? $columna;
                $gridInterno = str_replace($columna, $nueva, $gridInterno);
            }

            $tablaXml = str_replace($coincidencia[0], '<w:tblGrid>' . $gridInterno . '</w:tblGrid>', $tablaXml);
        }

        $filas = self::filasTabla($tablaXml);
        foreach ($filas as $indiceFila => $fila) {
            $celdas = self::celdasFila($fila);
            $columnaActual = 0;
            $modificada = false;

            foreach ($celdas as $indiceCelda => $celda) {
                $span = 1;
                if (preg_match('/w:gridSpan w:val="(\d+)"/', $celda, $spanMatch) === 1) {
                    $span = (int) $spanMatch[1];
                }

                if (isset($anchosPorIndice[$columnaActual]) && $span === 1
                    && preg_match('/<w:tcW\b[^>]*\/>/', $celda) === 1) {
                    $celdas[$indiceCelda] = preg_replace(
                        '/w:w="\d+"/',
                        'w:w="' . $anchosPorIndice[$columnaActual] . '"',
                        $celda
                    ) ?? $celda;
                    $modificada = true;
                }

                $columnaActual += $span;
            }

            if ($modificada) {
                preg_match('/<w:tr\b[^>]*>/', $fila, $apertura);
                $filas[$indiceFila] = ($apertura[0] ?? '<w:tr>') . implode('', $celdas) . '</w:tr>';
            }
        }

        return self::reconstruirTabla($tablaXml, $filas);
    }

    public static function siguienteRelId(string $relsXml): string
    {
        preg_match_all('/Id="rId(\d+)"/', $relsXml, $coincidencias);
        $maximo = 0;

        foreach ($coincidencias[1] ?? [] as $numero) {
            $maximo = max($maximo, (int) $numero);
        }

        return 'rId' . ($maximo + 1);
    }

    public static function agregarRelacionImagen(string $relsXml, string $relId, string $nombreArchivo): string
    {
        $relacion = '<Relationship Id="' . $relId . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="media/' . $nombreArchivo . '"/>';

        return str_replace('</Relationships>', $relacion . '</Relationships>', $relsXml);
    }

    public static function registrarExtensionMedia(ZipArchive $zip, string $extension): void
    {
        $extension = strtolower($extension === 'jpeg' ? 'jpg' : $extension);
        $tipos = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
        ];

        if (! isset($tipos[$extension])) {
            return;
        }

        $contentTypes = $zip->getFromName('[Content_Types].xml');
        if ($contentTypes === false) {
            return;
        }

        $marcador = 'Extension="' . $extension . '"';
        if (str_contains($contentTypes, $marcador)) {
            return;
        }

        $entrada = '<Default Extension="' . $extension . '" ContentType="' . $tipos[$extension] . '"/>';
        $contentTypes = str_replace('</Types>', $entrada . '</Types>', $contentTypes);

        $zip->deleteName('[Content_Types].xml');
        $zip->addFromString('[Content_Types].xml', $contentTypes);
    }
}
