<?php

namespace App\Support;

use PhpOffice\PhpWord\Shared\ZipArchive as PhpWordZipArchive;

/** Utilidades XML para plantillas Word (.docx) sin destruir tablas ni diseño. */
class InformeWordXml
{
    private const NS_W = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';

    public static function escapar(string $texto): string
    {
        return htmlspecialchars($texto, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }

    public static function actualizarTituloCore(string $coreXml, string $titulo): string
    {
        $escaped = self::escapar($titulo);
        if (preg_match('/<dc:title\b[^>]*>.*?<\/dc:title>/s', $coreXml) === 1) {
            return preg_replace('/<dc:title\b[^>]*>.*?<\/dc:title>/s', '<dc:title>'.$escaped.'</dc:title>', $coreXml, 1) ?? $coreXml;
        }

        return preg_replace(
            '/<\/cp:coreProperties>/',
            '<dc:title>'.$escaped.'</dc:title></cp:coreProperties>',
            $coreXml,
            1
        ) ?? $coreXml;
    }

    public static function esValido(string $xml): bool
    {
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $valido = $dom->loadXML($xml);
        libxml_clear_errors();

        return $valido;
    }

    /**
     * Word rechaza el archivo («Word detectó un error al intentar abrir el archivo») cuando el
     * XML es well-formed pero viola el esquema OOXML. esValido() no lo detecta, así que aquí se
     * comprueban las relaciones padre/hijo que Word exige en tablas y párrafos.
     *
     * @return list<string>
     */
    public static function problemasEstructura(string $xml): array
    {
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $cargado = $dom->loadXML($xml);
        libxml_clear_errors();

        if (! $cargado) {
            return ['XML mal formado'];
        }

        $problemas = [];
        $reglas = [
            'tc' => ['tr'],
            'tr' => ['tbl', 'sdtContent', 'customXml'],
        ];

        foreach ($reglas as $hijo => $padresValidos) {
            foreach ($dom->getElementsByTagNameNS(self::NS_W, $hijo) as $nodo) {
                $padre = $nodo->parentNode;
                $nombrePadre = $padre instanceof \DOMElement ? $padre->localName : '(raíz)';

                if (! in_array($nombrePadre, $padresValidos, true)) {
                    $problemas[] = 'w:' . $hijo . ' con padre w:' . $nombrePadre;
                }
            }
        }

        foreach ($dom->getElementsByTagNameNS(self::NS_W, 'p') as $nodo) {
            $padre = $nodo->parentNode;
            if ($padre instanceof \DOMElement && $padre->localName === 'p') {
                $problemas[] = 'w:p anidado dentro de w:p';
            }
        }

        foreach ($dom->getElementsByTagNameNS(self::NS_W, 'tbl') as $tabla) {
            $columnas = 0;
            foreach ($tabla->childNodes as $hijo) {
                if ($hijo instanceof \DOMElement && $hijo->localName === 'tblGrid') {
                    foreach ($hijo->childNodes as $col) {
                        if ($col instanceof \DOMElement && $col->localName === 'gridCol') {
                            $columnas++;
                        }
                    }
                }
            }
            if ($columnas === 0) {
                continue;
            }
            foreach ($tabla->childNodes as $filaNodo) {
                if (! ($filaNodo instanceof \DOMElement) || $filaNodo->localName !== 'tr') {
                    continue;
                }
                foreach ($filaNodo->childNodes as $celda) {
                    if (! ($celda instanceof \DOMElement) || $celda->localName !== 'tc') {
                        continue;
                    }
                    $span = 1;
                    foreach ($celda->childNodes as $hijo) {
                        if (! ($hijo instanceof \DOMElement) || $hijo->localName !== 'tcPr') {
                            continue;
                        }
                        foreach ($hijo->getElementsByTagNameNS(self::NS_W, 'gridSpan') as $gs) {
                            $span = max(1, (int) $gs->getAttributeNS(self::NS_W, 'val'));
                        }
                    }
                    if ($span > $columnas) {
                        $problemas[] = 'w:gridSpan '.$span.' > columnas '.$columnas;
                        break 2;
                    }
                }
            }
        }

        return array_values(array_unique($problemas));
    }

    public static function estructuraValida(string $xml): bool
    {
        return self::problemasEstructura($xml) === [];
    }

    /**
     * Relaciones r:embed/r:id referenciadas por el documento que no existen en el .rels:
     * Word también rechaza el archivo cuando falta el destino de una imagen.
     *
     * @return list<string>
     */
    public static function relacionesFaltantes(string $documentXml, string $relsXml): array
    {
        preg_match_all('/r:(?:embed|id|link)="(rId\d+)"/', $documentXml, $referencias);
        preg_match_all('/Id="(rId\d+)"/', $relsXml, $declaradas);

        $faltantes = array_diff(
            array_unique($referencias[1] ?? []),
            array_unique($declaradas[1] ?? [])
        );

        return array_values($faltantes);
    }

    public static function reemplazarTexto(string $xml, string $buscar, string $reemplazo, int $limit = -1): string
    {
        if ($buscar === '') {
            return $xml;
        }

        $chars = preg_split('//u', $buscar, -1, PREG_SPLIT_NO_EMPTY);
        $pattern = implode('(?:<[^>]+>)*', array_map(static fn (string $c): string => preg_quote($c, '/'), $chars));

        $seguro = self::escapar($reemplazo);

        return preg_replace_callback(
            '/' . $pattern . '/u',
            static fn (): string => $seguro,
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

    /**
     * Pinta de un color el run que contiene $texto (ya concatenado en un w:t).
     * Quita highlight de la plantilla para que APROBADO salga en negro.
     */
    public static function forzarColorEnTexto(string $xml, string $texto, string $colorHex): string
    {
        if ($texto === '') {
            return $xml;
        }

        $pos = strpos($xml, $texto);
        if ($pos === false) {
            return $xml;
        }

        $antes = substr($xml, 0, $pos);
        if (preg_match_all('/<w:r(?:\s|>)/', $antes, $coincidencias, PREG_OFFSET_CAPTURE) < 1) {
            return $xml;
        }
        $inicioRun = (int) $coincidencias[0][array_key_last($coincidencias[0])][1];
        $finRun = strpos($xml, '</w:r>', $pos);
        if ($finRun === false) {
            return $xml;
        }

        $finRun += strlen('</w:r>');
        $run = substr($xml, $inicioRun, $finRun - $inicioRun);
        $color = '<w:color w:val="'.$colorHex.'"/>';
        $run = preg_replace('/<w:highlight\b[^\/]*\/>/', '', $run) ?? $run;
        $run = preg_replace('/<w:highlight\b[^>]*>.*?<\/w:highlight>/s', '', $run) ?? $run;

        if (preg_match('/<w:color\b/', $run) === 1) {
            $run = preg_replace('/<w:color\b[^\/]*\/>/', $color, $run, 1) ?? $run;
        } elseif (str_contains($run, '<w:rPr>')) {
            $run = preg_replace('/<w:rPr>/', '<w:rPr>'.$color, $run, 1) ?? $run;
        } else {
            $run = preg_replace('/<w:r\b[^>]*>/', '$0<w:rPr>'.$color.'</w:rPr>', $run, 1) ?? $run;
        }

        if (! str_contains($run, '<w:b') && str_contains($run, '<w:rPr>')) {
            $run = preg_replace('/<w:rPr>/', '<w:rPr><w:b/>', $run, 1) ?? $run;
        }

        return substr($xml, 0, $inicioRun).$run.substr($xml, $finRun);
    }

    /** Quita el párrafo del marcador y, si sigue una tabla, también esa tabla. */
    public static function eliminarParrafoYTablaSiguiente(string $xml, string $marcador): string
    {
        $pos = strpos($xml, $marcador);
        if ($pos === false) {
            return $xml;
        }

        $antes = substr($xml, 0, $pos);
        if (preg_match_all('/<w:p(?:\s|>)/', $antes, $coincidencias, PREG_OFFSET_CAPTURE) < 1) {
            return $xml;
        }
        $inicioP = (int) $coincidencias[0][array_key_last($coincidencias[0])][1];
        $finP = strpos($xml, '</w:p>', $pos);
        if ($finP === false) {
            return $xml;
        }

        $finP += strlen('</w:p>');
        $resto = substr($xml, $finP);
        if (preg_match('/^(\s*)<w:tbl\b/', $resto, $espacio) === 1) {
            $inicioTbl = $finP + strlen($espacio[1]);
            $finTbl = self::finTablaDesde($xml, $inicioTbl);
            if ($finTbl !== null) {
                return substr($xml, 0, $inicioP).substr($xml, $finTbl);
            }
        }

        return substr($xml, 0, $inicioP).substr($xml, $finP);
    }

    /**
     * P-R1: quita la tabla vacía tras las R1/R2 de la conclusión.
     * No usa el primer R1 del documento (está en la tabla de preguntas).
     */
    public static function eliminarTablaSiguienteTrasParrafo(string $xml, string $ancla): string
    {
        $desde = strpos($xml, 'NO RESPONDIÓ CON VERACIDAD');
        if ($desde === false) {
            $desde = strpos($xml, 'NO RESPONDIO CON VERACIDAD');
        }
        $pos = $desde !== false ? strpos($xml, $ancla, $desde) : strpos($xml, $ancla);
        if ($pos === false && $desde !== false) {
            $pos = $desde;
        }
        if ($pos === false) {
            return $xml;
        }

        $cursor = strpos($xml, '</w:p>', $pos);
        if ($cursor === false) {
            return $xml;
        }
        $cursor += strlen('</w:p>');

        for ($i = 0; $i < 12; $i++) {
            if (preg_match('/^\s+/', substr($xml, $cursor), $ws) === 1) {
                $cursor += strlen($ws[0]);
            }
            $siguiente = substr($xml, $cursor, 8);
            if (str_starts_with($siguiente, '<w:tbl') && ! str_starts_with(substr($xml, $cursor), '<w:tblPr') && ! str_starts_with(substr($xml, $cursor), '<w:tblGrid')) {
                $finTbl = self::finTablaDesde($xml, $cursor);
                if ($finTbl === null) {
                    return $xml;
                }
                $tabla = substr($xml, $cursor, $finTbl - $cursor);
                if (trim(self::textoTablaConcatenado($tabla)) !== '') {
                    return $xml;
                }

                return substr($xml, 0, $cursor).substr($xml, $finTbl);
            }
            if (str_starts_with($siguiente, '<w:p')) {
                $finP = strpos($xml, '</w:p>', $cursor);
                if ($finP === false) {
                    return $xml;
                }
                $parrafo = substr($xml, $cursor, $finP + 6 - $cursor);
                $plano = mb_strtoupper(self::textoParrafo($parrafo));
                if (str_contains($parrafo, 'se clasifica') || str_contains($plano, 'CLASIFIC')) {
                    return $xml;
                }
                $cursor = $finP + strlen('</w:p>');
                continue;
            }

            return $xml;
        }

        return $xml;
    }

    /** P-T1: inserta un párrafo de espacio entre tablas pegadas. No toca tablas flotantes. */
    public static function separarTablasContiguas(string $xml, int $antes = 160, int $despues = 80): string
    {
        $espacio = self::parrafoEspacio($antes, $despues);
        $offset = 0;

        while (preg_match('/<\/w:tbl>(\s*)<w:tbl\b/', $xml, $m, PREG_OFFSET_CAPTURE, $offset) === 1) {
            $pos = (int) $m[0][1];
            $mirada = substr($xml, $pos, 280);
            if (str_contains($mirada, 'tblpPr')) {
                $offset = $pos + 8;
                continue;
            }

            $trasCierre = $pos + 8;
            $xml = substr($xml, 0, $trasCierre).$espacio.substr($xml, $trasCierre + strlen($m[1][0]));
            $offset = $trasCierre + strlen($espacio);
        }

        return $xml;
    }

    /** @return list<string> */
    public static function filasTabla(string $tablaXml): array
    {
        $filas = self::bloquesHijoNivelTabla($tablaXml, 'tr');
        if ($filas !== []) {
            return $filas;
        }

        preg_match_all('/<w:tr\b[^>]*>.*?<\/w:tr>/s', $tablaXml, $coincidencias);

        return $coincidencias[0] ?? [];
    }

    /** @return list<string> */
    public static function celdasFila(string $filaXml): array
    {
        return self::bloquesHijoNivelTabla($filaXml, 'tc', 0);
    }

    /**
     * Filas/celdas del nivel pedido, sin cortar tablas anidadas (el regex no-greedy
     * tomaba el primer </w:tr> interno y Word pedía recuperar el archivo).
     *
     * @return list<string>
     */
    private static function bloquesHijoNivelTabla(string $xml, string $hijo, int $profundidadTblObjetivo = 1): array
    {
        $bloques = [];
        $longitud = strlen($xml);
        $posicion = 0;
        $profundidadTbl = 0;

        while ($posicion < $longitud) {
            if (self::esAperturaTag($xml, $posicion, 'tbl')) {
                $profundidadTbl++;
                $posicion += 6;
                continue;
            }
            if (substr($xml, $posicion, 8) === '</w:tbl>') {
                $profundidadTbl = max(0, $profundidadTbl - 1);
                $posicion += 8;
                continue;
            }
            if ($profundidadTbl === $profundidadTblObjetivo && self::esAperturaTag($xml, $posicion, $hijo)) {
                $fin = self::finBloqueTag($xml, $posicion, $hijo);
                if ($fin === null) {
                    break;
                }
                $bloques[] = substr($xml, $posicion, $fin - $posicion);
                $posicion = $fin;
                continue;
            }
            $posicion++;
        }

        return $bloques;
    }

    private static function esAperturaTag(string $xml, int $posicion, string $localName): bool
    {
        $tag = '<w:'.$localName;
        if (! str_starts_with(substr($xml, $posicion), $tag)) {
            return false;
        }
        $siguiente = $xml[$posicion + strlen($tag)] ?? '';

        return $siguiente === '>' || $siguiente === ' ' || $siguiente === '/';
    }

    private static function finBloqueTag(string $xml, int $inicio, string $localName): ?int
    {
        $cierre = '</w:'.$localName.'>';
        $longitud = strlen($xml);
        $posicion = $inicio;
        $profundidad = 0;

        while ($posicion < $longitud) {
            if (self::esAperturaTag($xml, $posicion, $localName)) {
                $profundidad++;
                $posicion += 4 + strlen($localName);
                continue;
            }
            if (substr($xml, $posicion, strlen($cierre)) === $cierre) {
                $profundidad--;
                $posicion += strlen($cierre);
                if ($profundidad === 0) {
                    return $posicion;
                }
                continue;
            }
            $posicion++;
        }

        return null;
    }

    public static function establecerTextoCelda(string $celdaXml, string $texto): string
    {
        // Sprint F1: reemplazar el cuerpo completo de la celda (conservando w:tcPr).
        // Evita celdas "en blanco"/parciales cuando Word parte el texto en varios <w:t>.
        if (preg_match('/^(<w:tc\b[^>]*>)(.*)<\/w:tc>$/s', $celdaXml, $partes) === 1) {
            $apertura = $partes[1];
            $interior = $partes[2];
            $tcPr = '';
            if (preg_match('/<w:tcPr\b[^>]*>.*?<\/w:tcPr>/s', $interior, $pr) === 1) {
                $tcPr = $pr[0];
            }

            $parrafo = '<w:p><w:r><w:rPr><w:rFonts w:ascii="Helvetica" w:hAnsi="Helvetica"/></w:rPr>'
                . '<w:t xml:space="preserve">' . self::escapar($texto) . '</w:t></w:r></w:p>';

            return $apertura . $tcPr . $parrafo . '</w:tc>';
        }

        if (preg_match('/<w:t(?:\s+xml:space="preserve")?>/', $celdaXml)) {
            return preg_replace_callback(
                '/(<w:t(?:\s+xml:space="preserve")?>)(.*?)(<\/w:t>)/s',
                static fn (array $coincidencias): string => $coincidencias[1] . self::escapar($texto) . $coincidencias[3],
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

    /** M-S5: reponer negrita de encabezados al reconstruir la fila (establecerTextoCelda la quita). */
    public static function aplicarNegritaFila(string $filaXml): string
    {
        if (! str_contains($filaXml, '<w:rPr')) {
            return preg_replace('/<w:r>/', '<w:r><w:rPr><w:b/><w:bCs/></w:rPr>', $filaXml) ?? $filaXml;
        }

        return preg_replace_callback(
            '/<w:rPr\b[^>]*>.*?<\/w:rPr>/s',
            static function (array $m): string {
                if (str_contains($m[0], '<w:b') || str_contains($m[0], '<w:b/')) {
                    return $m[0];
                }

                return preg_replace('/<\/w:rPr>/', '<w:b/><w:bCs/></w:rPr>', $m[0], 1) ?? $m[0];
            },
            $filaXml
        ) ?? $filaXml;
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

    /**
     * Añade un prefijo al texto de la celda conservando su formato (negrita, color, relleno);
     * reemplazar el cuerpo completo perdería el estilo que trae la plantilla.
     */
    public static function prefijarTextoCelda(string $celdaXml, string $prefijo): string
    {
        if ($prefijo === '') {
            return $celdaXml;
        }

        return preg_replace(
            '/(<w:t(?:\s[^>]*)?>)/',
            '$1' . self::escapar($prefijo),
            $celdaXml,
            1
        ) ?? $celdaXml;
    }

    /** Añade texto al final del último run de la celda, conservando el formato de la plantilla. */
    public static function prefijarTextoCeldaFinal(string $celdaXml, string $texto): string
    {
        if ($texto === '') {
            return $celdaXml;
        }

        if (! preg_match_all('/<\/w:t>/', $celdaXml, $coincidencias, PREG_OFFSET_CAPTURE)) {
            return $celdaXml;
        }

        $ultimo = end($coincidencias[0]);
        $posicion = (int) $ultimo[1];

        return substr($celdaXml, 0, $posicion) . self::escapar($texto) . substr($celdaXml, $posicion);
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

        // El cuerpo se reemplaza conservando w:tcPr: ahí viven gridSpan, tcW, bordes y relleno.
        // Sin él las narrativas (aspecto económico, salud, exparejas) perdían la combinación de
        // columnas y quedaban en una franja del ancho de la primera columna.
        return preg_replace_callback(
            '/<w:tc\b([^>]*)>(.*?)<\/w:tc>/s',
            static function (array $partes) use ($parrafos): string {
                $tcPr = preg_match('/<w:tcPr\b[^>]*>.*?<\/w:tcPr>/s', $partes[2], $pr) === 1 ? $pr[0] : '';

                return '<w:tc' . $partes[1] . '>' . $tcPr . $parrafos . '</w:tc>';
            },
            $celdaXml,
            1
        ) ?? $celdaXml;
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

    /** Índice de columna (con gridSpan) cuya celda contiene el texto, o null. */
    public static function indiceColumnaPorTexto(string $tablaXml, string $texto, int $filaEncabezado = 1): ?int
    {
        $filas = self::filasTabla($tablaXml);
        if (! isset($filas[$filaEncabezado])) {
            return null;
        }

        $columnaActual = 0;
        foreach (self::celdasFila($filas[$filaEncabezado]) as $celda) {
            $span = 1;
            if (preg_match('/w:gridSpan w:val="(\d+)"/', $celda, $spanMatch) === 1) {
                $span = max(1, (int) $spanMatch[1]);
            }

            if (str_contains(self::textoCelda($celda), $texto)) {
                return $columnaActual;
            }

            $columnaActual += $span;
        }

        return null;
    }

    /**
     * Quita columnas por índice de grid (0-based). Ajusta tblGrid y gridSpan.
     *
     * @param  list<int>  $indices
     */
    public static function eliminarColumnas(string $tablaXml, array $indices): string
    {
        $quitar = array_values(array_unique(array_filter(
            $indices,
            static fn (mixed $indice): bool => is_int($indice) || ctype_digit((string) $indice)
        )));
        $quitar = array_map(static fn (mixed $indice): int => (int) $indice, $quitar);
        sort($quitar);
        if ($quitar === []) {
            return $tablaXml;
        }

        if (preg_match('/<w:tblGrid>(.*?)<\/w:tblGrid>/s', $tablaXml, $coincidencia) === 1) {
            preg_match_all('/<w:gridCol\b[^>]*\/>/', $coincidencia[1], $columnas);
            $grid = $columnas[0];
            foreach (array_reverse($quitar) as $indice) {
                unset($grid[$indice]);
            }
            $tablaXml = str_replace(
                $coincidencia[0],
                '<w:tblGrid>' . implode('', $grid) . '</w:tblGrid>',
                $tablaXml
            );
        }

        $filas = self::filasTabla($tablaXml);
        foreach ($filas as $indiceFila => $fila) {
            $filas[$indiceFila] = self::eliminarColumnasDeFila($fila, $quitar);
        }

        return self::reconstruirTabla($tablaXml, $filas);
    }

    /** @param  list<int>  $quitar */
    private static function eliminarColumnasDeFila(string $filaXml, array $quitar): string
    {
        $celdas = self::celdasFila($filaXml);
        if ($celdas === []) {
            return $filaXml;
        }

        $resultado = [];
        $columnaActual = 0;
        foreach ($celdas as $celda) {
            $span = 1;
            if (preg_match('/w:gridSpan w:val="(\d+)"/', $celda, $spanMatch) === 1) {
                $span = max(1, (int) $spanMatch[1]);
            }

            $inicio = $columnaActual;
            $fin = $columnaActual + $span;
            $quitadas = 0;
            for ($columna = $inicio; $columna < $fin; $columna++) {
                if (in_array($columna, $quitar, true)) {
                    $quitadas++;
                }
            }
            $columnaActual = $fin;

            $nuevoSpan = $span - $quitadas;
            if ($nuevoSpan <= 0) {
                continue;
            }

            if ($nuevoSpan !== $span && str_contains($celda, 'gridSpan')) {
                $celda = preg_replace(
                    '/w:gridSpan w:val="\d+"/',
                    'w:gridSpan w:val="' . $nuevoSpan . '"',
                    $celda
                ) ?? $celda;
            }

            $resultado[] = $celda;
        }

        preg_match('/<w:tr\b[^>]*>/', $filaXml, $apertura);

        return ($apertura[0] ?? '<w:tr>') . implode('', $resultado) . '</w:tr>';
    }

    /** Tabla de una columna (título + cuerpo) para bloques narrativos que la plantilla no trae. */
    public static function tablaTituloYCuerpo(string $titulo, string $cuerpo = ''): string
    {
        $ancho = 10790;
        $filaTitulo = '<w:tr><w:tc><w:tcPr><w:tcW w:w="' . $ancho . '" w:type="dxa"/></w:tcPr>'
            . '<w:p><w:r><w:rPr><w:rFonts w:ascii="Helvetica" w:hAnsi="Helvetica"/><w:b/></w:rPr>'
            . '<w:t xml:space="preserve">' . self::escapar($titulo) . '</w:t></w:r></w:p></w:tc></w:tr>';
        $filaCuerpo = '<w:tr><w:tc><w:tcPr><w:tcW w:w="' . $ancho . '" w:type="dxa"/></w:tcPr>'
            . '<w:p><w:r><w:rPr><w:rFonts w:ascii="Helvetica" w:hAnsi="Helvetica"/></w:rPr>'
            . '<w:t xml:space="preserve">' . self::escapar($cuerpo) . '</w:t></w:r></w:p></w:tc></w:tr>';

        return '<w:tbl><w:tblPr><w:tblW w:w="10790" w:type="dxa"/><w:tblBorders>'
            . '<w:top w:val="single" w:sz="4" w:space="0" w:color="000000"/>'
            . '<w:left w:val="single" w:sz="4" w:space="0" w:color="000000"/>'
            . '<w:bottom w:val="single" w:sz="4" w:space="0" w:color="000000"/>'
            . '<w:right w:val="single" w:sz="4" w:space="0" w:color="000000"/>'
            . '<w:insideH w:val="single" w:sz="4" w:space="0" w:color="000000"/>'
            . '</w:tblBorders></w:tblPr>'
            . '<w:tblGrid><w:gridCol w:w="' . $ancho . '"/></w:tblGrid>'
            . $filaTitulo . $filaCuerpo
            . '</w:tbl>';
    }

    /**
     * Combina celdas consecutivas de una fila (gridSpan) y deja el texto en la primera.
     *
     * @param  list<string>  $textosPorColumna  Textos a partir de $columnaInicio
     */
    public static function combinarCeldasFila(
        string $filaXml,
        int $columnaInicio,
        int $columnaFinInclusive,
        array $textosPorColumna = []
    ): string {
        $celdas = self::celdasFila($filaXml);
        if ($celdas === [] || $columnaFinInclusive <= $columnaInicio || ! isset($celdas[$columnaInicio])) {
            return $filaXml;
        }

        $columnaFinInclusive = min($columnaFinInclusive, count($celdas) - 1);
        $span = $columnaFinInclusive - $columnaInicio + 1;
        $primera = $celdas[$columnaInicio];
        if ($textosPorColumna !== []) {
            $primera = self::establecerTextoCelda($primera, (string) ($textosPorColumna[0] ?? ''));
        }

        if (preg_match('/<w:tcPr\b[^>]*>.*?<\/w:tcPr>/s', $primera, $pr) === 1) {
            $tcPr = str_contains($pr[0], 'gridSpan')
                ? preg_replace('/w:gridSpan w:val="\d+"/', 'w:gridSpan w:val="'.$span.'"', $pr[0]) ?? $pr[0]
                : preg_replace('/^(<w:tcPr\b[^>]*>)/', '$1<w:gridSpan w:val="'.$span.'"/>', $pr[0], 1) ?? $pr[0];
            $primera = str_replace($pr[0], $tcPr, $primera);
        } else {
            $primera = preg_replace(
                '/^(<w:tc\b[^>]*>)/',
                '$1<w:tcPr><w:gridSpan w:val="'.$span.'"/></w:tcPr>',
                $primera,
                1
            ) ?? $primera;
        }

        $resultado = [];
        foreach ($celdas as $indice => $celda) {
            if ($indice > $columnaInicio && $indice <= $columnaFinInclusive) {
                continue;
            }
            $resultado[] = $indice === $columnaInicio ? $primera : $celda;
        }

        preg_match('/<w:tr\b[^>]*>/', $filaXml, $apertura);

        return ($apertura[0] ?? '<w:tr>') . implode('', $resultado) . '</w:tr>';
    }

    public static function insertarTablaTrasMarcador(string $xml, string $marcador, string $tablaXml): string
    {
        $fin = self::posicionFinTablaPorMarcador($xml, $marcador);
        if ($fin === null) {
            return $xml;
        }

        return self::insertarEnPosicion($xml, $fin, $tablaXml);
    }

    public static function insertarFragmentoTrasTabla(string $xml, string $marcador, string $fragmento): string
    {
        $limites = self::limitesTablaPorMarcador($xml, $marcador);
        if ($limites === null || $fragmento === '') {
            return $xml;
        }

        return self::insertarEnPosicion($xml, $limites[1], $fragmento);
    }

    public static function parrafoEspacio(int $antes = 160, int $despues = 80): string
    {
        return '<w:p><w:pPr><w:spacing w:before="'.$antes.'" w:after="'.$despues.'"/></w:pPr></w:p>';
    }

    public static function aplicarColorFila(string $filaXml, string $colorHex): string
    {
        $color = '<w:color w:val="'.$colorHex.'"/>';
        $filaXml = preg_replace_callback(
            '/<w:rPr\b[^>]*>.*?<\/w:rPr>/s',
            static function (array $m) use ($color): string {
                if (str_contains($m[0], '<w:color')) {
                    return preg_replace('/<w:color\b[^\/]*\/>/', $color, $m[0], 1) ?? $m[0];
                }

                return preg_replace('/^(<w:rPr\b[^>]*>)/', '$1'.$color, $m[0], 1) ?? $m[0];
            },
            $filaXml
        ) ?? $filaXml;

        return preg_replace('/<w:r>/', '<w:r><w:rPr>'.$color.'</w:rPr>', $filaXml) ?? $filaXml;
    }

    public static function forzarTamanoFuenteTabla(string $tablaXml, int $halfPoints): string
    {
        $tablaXml = preg_replace_callback(
            '/<w:sz(Cs)? w:val="(\d+)"\/>/',
            static function (array $m) use ($halfPoints): string {
                if ((int) $m[2] > 22) {
                    return $m[0];
                }

                return '<w:sz'.$m[1].' w:val="'.$halfPoints.'"/>';
            },
            $tablaXml
        ) ?? $tablaXml;

        $sz = '<w:sz w:val="'.$halfPoints.'"/><w:szCs w:val="'.$halfPoints.'"/>';

        return preg_replace_callback(
            '/<w:rPr\b[^>]*>.*?<\/w:rPr>/s',
            static function (array $m) use ($sz): string {
                if (str_contains($m[0], '<w:sz')) {
                    return $m[0];
                }

                return preg_replace('/^(<w:rPr\b[^>]*>)/', '$1'.$sz, $m[0], 1) ?? $m[0];
            },
            $tablaXml
        ) ?? $tablaXml;
    }

    /** P-E1: filas angostas (observaciones) en 12; la grilla Deudas en 11. */
    public static function forzarTamanoFuenteFilasPorAncho(
        string $tablaXml,
        int $halfPointsAngostas,
        int $halfPointsAnchas,
        int $minCeldasAnchas = 5
    ): string {
        $filas = self::filasTabla($tablaXml);
        foreach ($filas as $indice => $fila) {
            $puntos = count(self::celdasFila($fila)) >= $minCeldasAnchas
                ? $halfPointsAnchas
                : $halfPointsAngostas;
            $filas[$indice] = self::forzarTamanoFuenteTabla($fila, $puntos);
        }

        return self::reconstruirTabla($tablaXml, $filas);
    }

    public static function anchoTablaDxa(string $tablaXml): ?int
    {
        if (preg_match('/<w:tblW\b[^>]*w:w="(\d+)"[^>]*w:type="dxa"/', $tablaXml, $m) === 1) {
            $ancho = (int) $m[1];

            return $ancho > 0 ? $ancho : null;
        }

        if (preg_match('/<w:tblGrid>(.*?)<\/w:tblGrid>/s', $tablaXml, $grid) === 1) {
            preg_match_all('/w:w="(\d+)"/', $grid[1], $anchos);
            $suma = array_sum(array_map('intval', $anchos[1] ?? []));

            return $suma > 0 ? $suma : null;
        }

        return null;
    }

    public static function anchoTablaPorMarcador(string $xml, string $marcador): ?int
    {
        $limites = self::limitesTablaPorMarcador($xml, $marcador);
        if ($limites === null) {
            return null;
        }

        return self::anchoTablaDxa(substr($xml, $limites[0], $limites[1] - $limites[0]));
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

    /** Quita filas vacías o que solo contienen marcadores tipo xxxxx. */
    public static function eliminarFilasVaciasOPlaceholder(string $tablaXml): string
    {
        $filas = self::filasTabla($tablaXml);
        $resultado = array_values(array_filter($filas, static function (string $fila): bool {
            $texto = trim(self::textoFila($fila));
            if ($texto === '') {
                return false;
            }

            $compacto = preg_replace('/[\s.:·Qq]/', '', $texto) ?? $texto;

            return ! preg_match('/^x+$/i', $compacto);
        }));

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

    /** N-A1: la tabla de firmas APA no debe marcar cuadros. */
    public static function ocultarBordesTabla(string $tablaXml): string
    {
        $nil = '<w:tblBorders>'
            .'<w:top w:val="nil"/>'
            .'<w:left w:val="nil"/>'
            .'<w:bottom w:val="nil"/>'
            .'<w:right w:val="nil"/>'
            .'<w:insideH w:val="nil"/>'
            .'<w:insideV w:val="nil"/>'
            .'</w:tblBorders>';

        $tablaXml = preg_replace('/<w:tblBorders>.*?<\/w:tblBorders>/s', $nil, $tablaXml) ?? $tablaXml;
        $tablaXml = preg_replace('/<w:tcBorders>.*?<\/w:tcBorders>/s', '', $tablaXml) ?? $tablaXml;

        if (! str_contains($tablaXml, '<w:tblBorders>')) {
            if (str_contains($tablaXml, '<w:tblPr>')) {
                $tablaXml = preg_replace('/<w:tblPr>/', '<w:tblPr>'.$nil, $tablaXml, 1) ?? $tablaXml;
            } else {
                $tablaXml = preg_replace('/<w:tbl\b[^>]*>/', '$0<w:tblPr>'.$nil.'</w:tblPr>', $tablaXml, 1) ?? $tablaXml;
            }
        }

        return $tablaXml;
    }

    /** Recorre todas las tablas cuyo texto concatenado contiene el marcador. */
    public static function ocultarBordesTablasQueContienen(string $xml, string $marcador): string
    {
        $offset = 0;
        $longitud = strlen($xml);

        while ($offset < $longitud && preg_match('/<w:tbl\b[^>]*>/', $xml, $match, PREG_OFFSET_CAPTURE, $offset) === 1) {
            $inicio = (int) $match[0][1];
            $fin = self::finTablaDesde($xml, $inicio);
            if ($fin === null) {
                break;
            }

            $tabla = substr($xml, $inicio, $fin - $inicio);
            if (str_contains(self::textoTablaConcatenado($tabla), $marcador)) {
                $nueva = self::ocultarBordesTabla($tabla);
                $xml = substr($xml, 0, $inicio).$nueva.substr($xml, $fin);
                $offset = $inicio + strlen($nueva);
                $longitud = strlen($xml);

                continue;
            }

            $offset = $fin;
        }

        return $xml;
    }

    public static function reemplazarTablaPorMarcador(string $xml, string $marcador, callable $callback): string
    {
        $limites = self::limitesTablaPorMarcador($xml, $marcador);
        if ($limites === null) {
            return $xml;
        }

        return self::reemplazarTablaEnLimites($xml, $limites, $callback);
    }

    /**
     * Tabla de historial laboral: la que sigue al título INFORMACIÓN LABORAL,
     * o la que ya contiene Empresa si el título vive dentro de la tabla.
     * No toma INFORMACIÓN LABORAL COMPLEMENTARIA.
     *
     * @return array{0: int, 1: int}|null
     */
    public static function limitesTablaTrasTexto(string $xml, string $texto): ?array
    {
        $offset = 0;
        while (true) {
            $posicion = strpos($xml, $texto, $offset);
            if ($posicion === false) {
                return null;
            }

            $despues = preg_replace('/<[^>]+>/', '', substr($xml, $posicion + strlen($texto), 160)) ?? '';
            if (str_starts_with(ltrim($despues), 'COMPLEMENTARIA')) {
                $offset = $posicion + strlen($texto);
                continue;
            }

            $inicioAnterior = self::inicioTablaInmediatamenteAntes($xml, $posicion);
            if ($inicioAnterior !== null) {
                $finAnterior = self::finTablaDesde($xml, $inicioAnterior);
                if ($finAnterior !== null && $posicion < $finAnterior) {
                    $tabla = substr($xml, $inicioAnterior, $finAnterior - $inicioAnterior);
                    if (str_contains(self::textoTablaConcatenado($tabla), 'Empresa')) {
                        return [$inicioAnterior, $finAnterior];
                    }
                }
            }

            $finParrafo = strpos($xml, '</w:p>', $posicion);
            $desde = $finParrafo !== false ? $finParrafo : $posicion;
            if (preg_match('/<w:tbl\b[^>]*>/', $xml, $match, PREG_OFFSET_CAPTURE, $desde) !== 1) {
                $offset = $posicion + strlen($texto);
                continue;
            }

            $inicio = (int) $match[0][1];
            $fin = self::finTablaDesde($xml, $inicio);

            return $fin !== null ? [$inicio, $fin] : null;
        }
    }

    /**
     * Grilla EMPLEOS anidada (título en fila 0, columnas Empresa/Puesto adentro).
     *
     * @return array{0: int, 1: int}|null
     */
    public static function limitesTablaInternaConTextos(string $tablaXml, string $textoA, string $textoB): ?array
    {
        if (preg_match('/<w:tbl\b[^>]*>/', $tablaXml, $apertura, PREG_OFFSET_CAPTURE) !== 1) {
            return null;
        }

        $offset = (int) $apertura[0][1] + strlen($apertura[0][0]);
        while (preg_match('/<w:tbl\b[^>]*>/', $tablaXml, $match, PREG_OFFSET_CAPTURE, $offset) === 1) {
            $inicio = (int) $match[0][1];
            $fin = self::finTablaDesde($tablaXml, $inicio);
            if ($fin === null) {
                break;
            }

            $texto = self::textoTablaConcatenado(substr($tablaXml, $inicio, $fin - $inicio));
            if (str_contains($texto, $textoA) && str_contains($texto, $textoB)) {
                return [$inicio, $fin];
            }

            $offset = $fin;
        }

        return null;
    }

    private static function inicioTablaInmediatamenteAntes(string $xml, int $posicion): ?int
    {
        $fragmento = substr($xml, 0, $posicion);
        $cierre = strrpos($fragmento, '<w:tbl>');
        $espacio = strrpos($fragmento, '<w:tbl ');
        $inicio = max($cierre === false ? -1 : $cierre, $espacio === false ? -1 : $espacio);

        return $inicio >= 0 ? $inicio : null;
    }

    /**
     * @param  array{0: int, 1: int}  $limites
     */
    public static function reemplazarTablaEnLimites(string $xml, array $limites, callable $callback): string
    {
        [$inicioTabla, $finTabla] = $limites;
        $tabla = substr($xml, $inicioTabla, $finTabla - $inicioTabla);
        $nueva = $callback($tabla);

        return substr($xml, 0, $inicioTabla).$nueva.substr($xml, $finTabla);
    }

    /** M-P6: saca del documento la tabla cuyo título coincide con el marcador. */
    public static function eliminarTablaPorMarcador(string $xml, string $marcador): string
    {
        $limites = self::limitesTablaPorMarcador($xml, $marcador);
        if ($limites === null) {
            return $xml;
        }

        return substr($xml, 0, $limites[0]) . substr($xml, $limites[1]);
    }

    /** @return array{0: int, 1: int}|null */
    public static function limitesTablaPorMarcador(string $xml, string $marcador): ?array
    {
        $posicion = strpos($xml, $marcador);
        if ($posicion !== false) {
            $inicioAnterior = strrpos(substr($xml, 0, $posicion), '<w:tbl>');
            if ($inicioAnterior !== false) {
                $finAnterior = self::finTablaDesde($xml, $inicioAnterior);
                // Marcador dentro de la tabla → esa tabla. Si el título está entre tablas
                // (INFORMACIÓN LABORAL antes de EMPLEOS), no usar la tabla de arriba.
                if ($finAnterior !== null && $posicion < $finAnterior) {
                    return [$inicioAnterior, $finAnterior];
                }
            }

            if (preg_match('/<w:tbl\b[^>]*>/', $xml, $match, PREG_OFFSET_CAPTURE, $posicion) === 1) {
                $inicioSiguiente = (int) $match[0][1];
                $finSiguiente = self::finTablaDesde($xml, $inicioSiguiente);
                if ($finSiguiente !== null) {
                    return [$inicioSiguiente, $finSiguiente];
                }
            }
        }

        // Sprint F3: plantillas cliente parten etiquetas en varios <w:t> ("E"+"mpresa").
        return self::limitesTablaPorTextoConcatenado($xml, $marcador);
    }

    /** @return array{0: int, 1: int}|null */
    public static function limitesTablaPorTextoConcatenado(string $xml, string $marcador): ?array
    {
        $offset = 0;
        while (preg_match('/<w:tbl\b[^>]*>/', $xml, $match, PREG_OFFSET_CAPTURE, $offset) === 1) {
            $inicioTabla = (int) $match[0][1];
            $finTabla = self::finTablaDesde($xml, $inicioTabla);
            if ($finTabla === null) {
                break;
            }

            $tabla = substr($xml, $inicioTabla, $finTabla - $inicioTabla);
            $texto = self::textoTablaConcatenado($tabla);
            if (str_contains($texto, $marcador)) {
                return [$inicioTabla, $finTabla];
            }

            $offset = $finTabla;
        }

        return null;
    }

    public static function textoTablaConcatenado(string $tablaXml): string
    {
        preg_match_all('/<w:t(?:\s+xml:space="preserve")?>(.*?)<\/w:t>/s', $tablaXml, $coincidencias);

        return html_entity_decode(implode('', $coincidencias[1] ?? []), ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    private static function finTablaDesde(string $xml, int $inicioTabla): ?int
    {
        $posicion = $inicioTabla;
        $profundidad = 0;
        $longitud = strlen($xml);

        while ($posicion < $longitud) {
            if (preg_match('/^<w:tbl(?=[\s>])/', substr($xml, $posicion, 8)) === 1) {
                $profundidad++;
                $posicion += 6;

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

    /**
     * Párrafo con la imagen flotante que las plantillas v2 usan como marco de la foto: silueta
     * vertical situada a la izquierda de la tabla DATOS GENERALES (que va posicionada con tblpX).
     *
     * @return array{start: int, end: int, xml: string}|null
     */
    public static function parrafoMarcoFoto(string $xml, int $limiteFinal): ?array
    {
        $candidato = null;

        foreach (self::parrafosTopLevel($xml) as $parrafo) {
            if ($parrafo['end'] > $limiteFinal) {
                break;
            }

            if (! str_contains($parrafo['xml'], 'wp:anchor')
                || preg_match('/r:embed="rId\d+"/', $parrafo['xml']) !== 1
                || preg_match('/<wp:extent cx="(\d+)" cy="(\d+)"\s*\/>/', $parrafo['xml'], $extent) !== 1) {
                continue;
            }

            // Marco vertical (silueta de la plantilla) o marco ya sustituido por la foto real.
            if ((int) $extent[2] < (int) $extent[1] && ! str_contains($parrafo['xml'], 'name="Foto evaluado"')) {
                continue;
            }

            $candidato = $parrafo;
        }

        return $candidato;
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
        $limiteAnterior = self::limitesTablaPorMarcador($xml, $marcadorAnterior);
        $limiteSiguiente = self::limitesTablaPorMarcador($xml, $marcadorSiguiente);
        if ($limiteAnterior === null || $limiteSiguiente === null || $limiteAnterior[1] >= $limiteSiguiente[0]) {
            return $xml;
        }

        $entre = substr($xml, $limiteAnterior[1], $limiteSiguiente[0] - $limiteAnterior[1]);
        $entre = self::quitarParrafosVacios($entre);

        return substr($xml, 0, $limiteAnterior[1]).$entre.substr($xml, $limiteSiguiente[0]);
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

    /**
     * Evita que el cuerpo invada el pie de la primera hoja (términos de confidencialidad en footer2).
     * Inserta salto de página antes del bloque PROCEDIMIENTO DE EVALUACIÓN.
     */
    /**
     * El socio trae el aviso CONFIDENCIAL en un textbox del cuerpo y otra vez en el pie.
     * Stephany 20-ago: quitar el del cuerpo; el pie se conserva.
     */
    public static function quitarAvisoConfidencialDuplicadoDelCuerpo(string $xml): string
    {
        if (! str_contains($xml, 'INFORMACIÓN CONFIDENCIAL')) {
            return $xml;
        }

        $pos = strpos($xml, 'INFORMACIÓN CONFIDENCIAL');
        if ($pos === false) {
            return $xml;
        }

        $altStart = strrpos(substr($xml, 0, $pos), '<mc:AlternateContent');
        if ($altStart !== false) {
            $altEnd = strpos($xml, '</mc:AlternateContent>', $altStart);
            if ($altEnd !== false) {
                $xml = substr($xml, 0, $altStart).substr($xml, $altEnd + strlen('</mc:AlternateContent>'));
            }
        }

        if (preg_match_all(
            '/<w:p\b[^>]*>(?:(?!<w:p\b).)*?INFORMACIÓN CONFIDENCIAL(?:(?!<w:p\b).)*?<\/w:p>/su',
            $xml,
            $coincidencias,
            PREG_OFFSET_CAPTURE
        ) === 0) {
            return $xml;
        }

        foreach (array_reverse($coincidencias[0]) as $ocurrencia) {
            $fragmento = $ocurrencia[0];
            $inicio = $ocurrencia[1];
            $plano = trim(html_entity_decode(preg_replace('/<[^>]+>/', '', $fragmento) ?? '', ENT_QUOTES | ENT_XML1, 'UTF-8'));
            if ($plano === '' || strlen($plano) > 280) {
                continue;
            }
            if (! str_contains($plano, 'INFORMACIÓN CONFIDENCIAL')) {
                continue;
            }
            $xml = substr($xml, 0, $inicio).substr($xml, $inicio + strlen($fragmento));
        }

        return $xml;
    }

    public static function evitarTraslapePiePrimeraPagina(string $xml): string
    {
        if (! str_contains($xml, 'PROCEDIMIENTO DE EVALUACIÓN')) {
            return $xml;
        }

        return self::insertarSaltoPaginaAntesDeTexto($xml, 'PROCEDIMIENTO DE EVALUACIÓN');
    }

    public static function insertarSaltoPaginaAntesDeTexto(string $xml, string $marcador): string
    {
        if (! str_contains($xml, $marcador)) {
            return $xml;
        }

        foreach (self::parrafosTopLevel($xml) as $parrafo) {
            if (! str_contains($parrafo['xml'], $marcador)) {
                continue;
            }

            if (str_contains($parrafo['xml'], 'w:pageBreakBefore')) {
                return $xml;
            }

            $nuevo = self::aplicarSaltoPaginaAntesParrafo($parrafo['xml']);

            return substr($xml, 0, $parrafo['start']) . $nuevo . substr($xml, $parrafo['end']);
        }

        return $xml;
    }

    private static function aplicarSaltoPaginaAntesParrafo(string $parrafoXml): string
    {
        if (preg_match('/<w:pPr>/', $parrafoXml) === 1) {
            return preg_replace('/<w:pPr>/', '<w:pPr><w:pageBreakBefore/>', $parrafoXml, 1) ?? $parrafoXml;
        }

        return preg_replace('/<w:p\b([^>]*)>/', '<w:p$1><w:pPr><w:pageBreakBefore/></w:pPr>', $parrafoXml, 1) ?? $parrafoXml;
    }

    /**
     * Repara el borde izquierdo faltante en la esquina superior de DATOS FAMILIARES (fila encabezados + Padre).
     */
    public static function repararBordesColumnaEtiquetaFamiliar(string $tablaXml): string
    {
        $filas = self::filasTabla($tablaXml);
        $indices = [];

        if (isset($filas[1]) && str_contains(self::textoFila($filas[1]), 'Nombre:')) {
            $indices[] = 1;
        }

        foreach ($filas as $indice => $fila) {
            if (str_starts_with(self::textoCelda(self::celdasFila($fila)[0] ?? ''), 'Padre:')) {
                $indices[] = $indice;
                break;
            }
        }

        foreach (array_unique($indices) as $indice) {
            if (! isset($filas[$indice])) {
                continue;
            }

            $celdas = self::celdasFila($filas[$indice]);
            if (! isset($celdas[0])) {
                continue;
            }

            $celdas[0] = self::asegurarBordeCelda($celdas[0], 'left');
            preg_match('/<w:tr\b[^>]*>/', $filas[$indice], $apertura);
            $filas[$indice] = ($apertura[0] ?? '<w:tr>') . implode('', $celdas) . '</w:tr>';
        }

        return self::reconstruirTabla($tablaXml, $filas);
    }

    /** @param  'left'|'top'|'right'|'bottom'  $lado  */
    public static function asegurarBordeCelda(string $celdaXml, string $lado): string
    {
        $borde = match ($lado) {
            'left' => '<w:left w:val="single" w:sz="4" w:space="0" w:color="auto"/>',
            'top' => '<w:top w:val="single" w:sz="4" w:space="0" w:color="auto"/>',
            'right' => '<w:right w:val="single" w:sz="4" w:space="0" w:color="auto"/>',
            'bottom' => '<w:bottom w:val="single" w:sz="4" w:space="0" w:color="auto"/>',
            default => '',
        };

        if ($borde === '') {
            return $celdaXml;
        }

        $etiqueta = 'w:' . $lado;

        if (preg_match('/<w:tcBorders\b[^>]*>.*?<\/w:tcBorders>/s', $celdaXml) === 1) {
            if (preg_match('/<' . $etiqueta . '\b[^>]*\/>/', $celdaXml) === 1) {
                return preg_replace('/<' . $etiqueta . '\b[^>]*\/>/', $borde, $celdaXml, 1) ?? $celdaXml;
            }

            return preg_replace(
                '/(<w:tcBorders\b[^>]*>)/',
                '$1' . $borde,
                $celdaXml,
                1
            ) ?? $celdaXml;
        }

        $tcBorders = '<w:tcBorders>' . $borde . '</w:tcBorders>';

        if (preg_match('/<w:tcPr\b[^>]*>.*?<\/w:tcPr>/s', $celdaXml) === 1) {
            return preg_replace(
                '/(<w:tcPr\b[^>]*>)/',
                '$1' . $tcBorders,
                $celdaXml,
                1
            ) ?? $celdaXml;
        }

        return preg_replace(
            '/(<w:tc\b[^>]*>)/',
            '$1<w:tcPr>' . $tcBorders . '</w:tcPr>',
            $celdaXml,
            1
        ) ?? $celdaXml;
    }

    public static function quitarAnclajeFlotanteTabla(string $tablaXml): string
    {
        return preg_replace('/<w:tblpPr\b[^>]*\/>/', '', $tablaXml) ?? $tablaXml;
    }

    /** Quita posicionamiento flotante para que la tabla use el ancho completo del margen. */
    public static function quitarPosicionFlotanteTabla(string $tablaXml): string
    {
        return preg_replace('/<w:tblpPr\b[^>]*\/>/', '', $tablaXml) ?? $tablaXml;
    }

    /**
     * Expande una tabla estrecha (p. ej. DATOS GENERALES junto a foto de perfil) al ancho de página.
     */
    public static function expandirTablaAnchoPagina(string $tablaXml, int $anchoTotalDxa = 10915): string
    {
        $tablaXml = self::quitarPosicionFlotanteTabla($tablaXml);
        $tblW = '<w:tblW w:w="' . $anchoTotalDxa . '" w:type="dxa"/>';

        if (preg_match('/<w:tblW\b[^>]*\/>/', $tablaXml) === 1) {
            $tablaXml = preg_replace(
                '/<w:tblW\b[^>]*\/>/',
                $tblW,
                $tablaXml,
                1
            ) ?? $tablaXml;
        } elseif (preg_match('/<w:tblPr\b[^>]*>/', $tablaXml) === 1) {
            $tablaXml = preg_replace('/(<w:tblPr\b[^>]*>)/', '$1'.$tblW, $tablaXml, 1) ?? $tablaXml;
        }

        if (! preg_match('/<w:tblGrid>(.*?)<\/w:tblGrid>/s', $tablaXml, $coincidencia)) {
            return $tablaXml;
        }

        preg_match_all('/w:w="(\d+)"/', $coincidencia[1], $anchos);
        $columnas = array_map('intval', $anchos[1] ?? []);
        if ($columnas === []) {
            return $tablaXml;
        }

        $anchoActual = array_sum($columnas);
        if ($anchoActual <= 0 || $anchoActual >= $anchoTotalDxa) {
            return $tablaXml;
        }

        // M-P7: la última col. de 22 dxa (sobrante de plantilla) no debe absorber el resto
        // al expandir: se veía como una franja vacía a la derecha de ASPECTO ECONÓMICO.
        $minimoReal = 50;
        $sumaTiny = 0;
        $ultimoReal = 0;
        foreach ($columnas as $indice => $ancho) {
            if ($ancho < $minimoReal) {
                $sumaTiny += $ancho;
            } else {
                $ultimoReal = $indice;
            }
        }
        $sumaReal = $anchoActual - $sumaTiny;
        if ($sumaReal <= 0) {
            return $tablaXml;
        }

        $escala = ($anchoTotalDxa - $sumaTiny) / $sumaReal;
        $nuevosAnchos = [];
        $acumulado = 0;
        $tinyTrasUltimoReal = 0;
        foreach ($columnas as $indice => $ancho) {
            if ($indice > $ultimoReal && $ancho < $minimoReal) {
                $tinyTrasUltimoReal += $ancho;
            }
        }

        foreach ($columnas as $indice => $ancho) {
            if ($ancho < $minimoReal) {
                $nuevosAnchos[] = $ancho;
                $acumulado += $ancho;
                continue;
            }
            if ($indice === $ultimoReal) {
                $nuevosAnchos[] = max(1, $anchoTotalDxa - $acumulado - $tinyTrasUltimoReal);
            } else {
                $nuevo = max(1, (int) round($ancho * $escala));
                $nuevosAnchos[] = $nuevo;
                $acumulado += $nuevo;
            }
        }

        return self::ajustarAnchosColumnas($tablaXml, array_combine(array_keys($columnas), $nuevosAnchos) ?: []);
    }

    /** Filas de una sola celda (título / narrativa): abarcan todas las columnas del grid. */
    public static function extenderFilasDeUnaCeldaAlGrid(string $tablaXml): string
    {
        if (preg_match('/<w:tblGrid>(.*?)<\/w:tblGrid>/s', $tablaXml, $coincidencia) !== 1) {
            return $tablaXml;
        }

        preg_match_all('/w:w="(\d+)"/', $coincidencia[1], $anchos);
        $columnas = array_map('intval', $anchos[1] ?? []);
        $totalColumnas = count($columnas);
        if ($totalColumnas < 2) {
            return $tablaXml;
        }

        $anchoTotal = array_sum($columnas);
        $filas = self::filasTabla($tablaXml);
        foreach ($filas as $indice => $fila) {
            $celdas = self::celdasFila($fila);
            if (count($celdas) !== 1) {
                continue;
            }

            $celda = $celdas[0];
            if (str_contains($celda, 'gridSpan')) {
                $celda = preg_replace('/w:gridSpan w:val="\d+"/', 'w:gridSpan w:val="'.$totalColumnas.'"', $celda) ?? $celda;
            } elseif (preg_match('/<w:tcPr\b[^>]*>/', $celda) === 1) {
                $celda = preg_replace('/(<w:tcPr\b[^>]*>)/', '$1<w:gridSpan w:val="'.$totalColumnas.'"/>', $celda, 1) ?? $celda;
            }

            if (preg_match('/<w:tcW\b[^>]*\/>/', $celda) === 1) {
                $celda = preg_replace(
                    '/<w:tcW\b[^>]*\/>/',
                    '<w:tcW w:w="'.$anchoTotal.'" w:type="dxa"/>',
                    $celda,
                    1
                ) ?? $celda;
            }

            preg_match('/<w:tr\b[^>]*>/', $fila, $apertura);
            $filas[$indice] = ($apertura[0] ?? '<w:tr>').$celda.'</w:tr>';
        }

        return self::reconstruirTabla($tablaXml, $filas);
    }

    /**
     * Elimina anclajes de foto de perfil y expande DATOS GENERALES tras la tabla de encabezado v2.
     */
    public static function limpiarZonaFotoPerfilV2(string $xml): string
    {
        $limDatos = self::limitesTablaPorMarcador($xml, 'DATOS GENERALES');
        if ($limDatos === null) {
            return self::quitarParrafosAnclados($xml);
        }

        $prefijo = substr($xml, 0, $limDatos[0]);
        $tablaDatos = substr($xml, $limDatos[0], $limDatos[1] - $limDatos[0]);
        $sufijo = substr($xml, $limDatos[1]);

        $prefijo = self::quitarParrafosAnclados($prefijo);
        $prefijo = self::quitarParrafosVacios($prefijo);
        $tablaDatos = self::expandirTablaAnchoPagina($tablaDatos);

        return $prefijo . $tablaDatos . $sufijo;
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

                // Las celdas combinadas valen la suma de las columnas que abarcan; si se dejan con
                // el ancho original la fila se ve más corta que el resto de la tabla.
                $ancho = 0;
                for ($columna = $columnaActual; $columna < $columnaActual + $span; $columna++) {
                    if (! isset($anchosPorIndice[$columna])) {
                        $ancho = 0;
                        break;
                    }
                    $ancho += $anchosPorIndice[$columna];
                }

                if ($ancho > 0 && preg_match('/<w:tcW\b[^>]*\/>/', $celda) === 1) {
                    $celdas[$indiceCelda] = preg_replace(
                        '/w:w="\d+"/',
                        'w:w="' . $ancho . '"',
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

    public static function registrarExtensionMedia(PhpWordZipArchive $zip, string $extension): void
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

        $contentTypes = InformeWordZip::leerEntrada($zip, '[Content_Types].xml');
        if ($contentTypes === false) {
            return;
        }

        $marcador = 'Extension="' . $extension . '"';
        if (str_contains($contentTypes, $marcador)) {
            return;
        }

        $entrada = '<Default Extension="' . $extension . '" ContentType="' . $tipos[$extension] . '"/>';
        $contentTypes = str_replace('</Types>', $entrada . '</Types>', $contentTypes);

        InformeWordZip::reemplazarEntrada($zip, '[Content_Types].xml', $contentTypes);
    }

    /** Posición justo después de la última </w:tbl> que contiene el marcador. */
    public static function posicionFinTablaPorMarcador(string $xml, string $marcador): ?int
    {
        $limites = self::limitesTablaPorMarcador($xml, $marcador);
        if ($limites === null) {
            return null;
        }

        $ultimaFin = $limites[1];
        $offset = $limites[1];
        while ($offset < strlen($xml)) {
            $siguientes = self::limitesTablaPorTextoConcatenadoDesde($xml, $marcador, $offset);
            if ($siguientes === null) {
                break;
            }
            $ultimaFin = $siguientes[1];
            $offset = $siguientes[1];
        }

        return $ultimaFin;
    }

    /** @return array{0: int, 1: int}|null */
    private static function limitesTablaPorTextoConcatenadoDesde(string $xml, string $marcador, int $offset): ?array
    {
        while (preg_match('/<w:tbl\b[^>]*>/', $xml, $match, PREG_OFFSET_CAPTURE, $offset) === 1) {
            $inicioTabla = (int) $match[0][1];
            $finTabla = self::finTablaDesde($xml, $inicioTabla);
            if ($finTabla === null) {
                break;
            }

            $tabla = substr($xml, $inicioTabla, $finTabla - $inicioTabla);
            $texto = self::textoTablaConcatenado($tabla);
            if (str_contains($texto, $marcador)) {
                return [$inicioTabla, $finTabla];
            }

            $offset = $finTabla;
        }

        return null;
    }

    public static function insertarEnPosicion(string $xml, int $posicion, string $fragmento): string
    {
        if ($fragmento === '') {
            return $xml;
        }

        return substr($xml, 0, $posicion) . $fragmento . substr($xml, $posicion);
    }

    public static function parrafoTituloSeccion(string $titulo): string
    {
        return '<w:p><w:pPr><w:spacing w:before="120" w:after="80"/></w:pPr>'
            . '<w:r><w:rPr><w:rFonts w:ascii="Helvetica" w:hAnsi="Helvetica" w:cs="Helvetica"/>'
            . '<w:b/><w:bCs/><w:sz w:val="22"/><w:szCs w:val="22"/></w:rPr>'
            . '<w:t xml:space="preserve">' . self::escapar($titulo) . '</w:t></w:r></w:p>';
    }

    /**
     * $contenidoCelda puede llegar como cuerpo de celda (párrafos) o como una celda completa,
     * según el helper que la haya producido. Envolver una celda dentro de otra genera
     * w:tc anidado y Word rechaza el documento, así que aquí solo se ajusta el ancho.
     */
    public static function construirCeldaSimple(int $anchoDxa, string $contenidoCelda): string
    {
        if (preg_match('/^\s*<w:tc\b/', $contenidoCelda) === 1) {
            return self::establecerAnchoCelda($contenidoCelda, $anchoDxa);
        }

        return '<w:tc><w:tcPr><w:tcW w:w="' . $anchoDxa . '" w:type="dxa"/></w:tcPr>'
            . $contenidoCelda
            . '</w:tc>';
    }

    public static function establecerAnchoCelda(string $celdaXml, int $anchoDxa): string
    {
        $tcW = '<w:tcW w:w="' . $anchoDxa . '" w:type="dxa"/>';

        if (preg_match('/<w:tcPr\b[^>]*>.*?<\/w:tcPr>/s', $celdaXml, $pr) === 1) {
            $tcPr = str_contains($pr[0], '<w:tcW')
                ? preg_replace('/<w:tcW\b[^>]*\/>/', $tcW, $pr[0], 1) ?? $pr[0]
                : preg_replace('/^(<w:tcPr\b[^>]*>)/', '$1' . $tcW, $pr[0], 1) ?? $pr[0];

            return str_replace($pr[0], $tcPr, $celdaXml);
        }

        return preg_replace(
            '/^(\s*<w:tc\b[^>]*>)/',
            '$1<w:tcPr>' . $tcW . '</w:tcPr>',
            $celdaXml,
            1
        ) ?? $celdaXml;
    }

    /**
     * @param  list<string>  $filasXml  Filas <w:tr>...</w:tr>
     */
    public static function construirTablaDosColumnas(array $filasXml, int $anchoCol1 = 3600, int $anchoCol2 = 7200): string
    {
        $filas = implode('', $filasXml);

        return '<w:tbl><w:tblPr><w:tblW w:w="0" w:type="auto"/><w:tblBorders>'
            . '<w:top w:val="single" w:sz="4" w:space="0" w:color="auto"/>'
            . '<w:left w:val="single" w:sz="4" w:space="0" w:color="auto"/>'
            . '<w:bottom w:val="single" w:sz="4" w:space="0" w:color="auto"/>'
            . '<w:right w:val="single" w:sz="4" w:space="0" w:color="auto"/>'
            . '<w:insideH w:val="single" w:sz="4" w:space="0" w:color="auto"/>'
            . '<w:insideV w:val="single" w:sz="4" w:space="0" w:color="auto"/>'
            . '</w:tblBorders></w:tblPr>'
            . '<w:tblGrid><w:gridCol w:w="' . $anchoCol1 . '"/><w:gridCol w:w="' . $anchoCol2 . '"/></w:tblGrid>'
            . $filas
            . '</w:tbl>';
    }

    public static function construirFilaDosColumnas(string $celda1, string $celda2): string
    {
        return '<w:tr>' . $celda1 . $celda2 . '</w:tr>';
    }

    /**
     * Elimina sub-sección Deudas/TOTALES cuando no hay filas de deuda con datos.
     *
     * Conserva la fila con el marcador de narrativa: es el único hueco donde se escribe el texto
     * del aspecto económico y, al borrarla aquí, la sección se entregaba solo con su título.
     * Si finalmente no se rellena, eliminarFilasVaciasOPlaceholder() la quita al cerrar la tabla.
     */
    public static function podarSeccionDeudasVacia(string $tablaXml): string
    {
        $filas = self::filasTabla($tablaXml);
        $tieneDatosDeuda = false;

        foreach ($filas as $fila) {
            $texto = self::textoFila($fila);
            if (str_contains($texto, 'Deudas:') || str_contains($texto, 'Entidad:') || str_contains($texto, 'TOTALES:')) {
                continue;
            }
            if (preg_match('/^x+$/i', preg_replace('/[\s.:·Qq]/', '', $texto) ?? '')) {
                continue;
            }
            if (preg_match('/^(ASPECTO ECONÓMICO|Indicó|Señaló|Refirió|Manifestó)/iu', trim($texto))) {
                continue;
            }
            if (trim($texto) !== '') {
                $celdas = self::celdasFila($fila);
                foreach (array_slice($celdas, 0, 8) as $celda) {
                    $t = trim(self::textoParrafo($celda));
                    if ($t !== '' && ! preg_match('/^x+$/i', preg_replace('/[\s.:·Qq]/', '', $t) ?? '')) {
                        $tieneDatosDeuda = true;
                        break 2;
                    }
                }
            }
        }

        if ($tieneDatosDeuda) {
            return $tablaXml;
        }

        $resultado = array_values(array_filter($filas, static function (string $fila): bool {
            $texto = self::textoFila($fila);

            if (str_contains($texto, 'Deudas:')) {
                return false;
            }

            if (str_contains($texto, 'Entidad:') && str_contains($texto, 'Monto:')) {
                return false;
            }

            return ! str_contains($texto, 'TOTALES:');
        }));

        return self::reconstruirTabla($tablaXml, $resultado);
    }
}
