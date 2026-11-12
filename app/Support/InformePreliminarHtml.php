<?php

namespace App\Support;

/** Convierte el formato de Quill (clases y etiquetas font) a estilos en línea. */
class InformePreliminarHtml
{
    public static function normalizar(?string $html): ?string
    {
        if ($html === null || trim($html) === '') {
            return $html;
        }

        $actual = $html;
        for ($i = 0; $i < 6; $i++) {
            $siguiente = preg_replace_callback(
                '/<font\b([^>]*)>(.*?)<\/font>/is',
                [self::class, 'reemplazarFont'],
                $actual
            );
            if (! is_string($siguiente) || $siguiente === $actual) {
                break;
            }
            $actual = $siguiente;
        }

        $conEstilo = preg_replace_callback(
            '/<([a-zA-Z][a-zA-Z0-9]*)\b([^>]*)>/',
            [self::class, 'clasesQuillAEstilo'],
            $actual
        );

        return is_string($conEstilo) ? $conEstilo : $actual;
    }

    /** @param  array<int, string>  $m */
    private static function reemplazarFont(array $m): string
    {
        $attrs = $m[1];
        $inner = $m[2];
        $estilos = [];
        $color = null;
        if (preg_match('/\bcolor\s*=\s*"([^"]+)"/i', $attrs, $c)
            || preg_match("/\\bcolor\\s*=\\s*'([^']+)'/i", $attrs, $c)
            || preg_match('/\bcolor\s*=\s*([^\s>]+)/i', $attrs, $c)) {
            $color = self::colorSeguro($c[1]);
        }
        if ($color !== null) {
            $estilos[] = 'color: '.$color;
        }
        if (preg_match('/\bstyle\s*=\s*("|\')(.*?)\1/i', $attrs, $s) && trim($s[2]) !== '') {
            $estilos[] = rtrim(trim($s[2]), ';');
        }
        if ($estilos === []) {
            return $inner;
        }

        return '<span style="'.implode('; ', $estilos).'">'.$inner.'</span>';
    }

    /** @param  array<int, string>  $m */
    private static function clasesQuillAEstilo(array $m): string
    {
        $tag = $m[1];
        $attrs = $m[2];
        if (! preg_match('/\sclass\s*=\s*("|\')([^"\']*)\1/i', $attrs, $cm)) {
            return $m[0];
        }

        $estilosNuevos = [];
        $clases = [];
        foreach (preg_split('/\s+/', trim($cm[2])) ?: [] as $clase) {
            if ($clase === '') {
                continue;
            }
            if (preg_match('/^ql-color-(.+)$/i', $clase, $c)) {
                $color = self::colorDesdeClaseQuill($c[1]);
                if ($color !== null) {
                    $estilosNuevos[] = 'color: '.$color;

                    continue;
                }
            }
            if (preg_match('/^ql-bg-(.+)$/i', $clase, $c)) {
                $color = self::colorDesdeClaseQuill($c[1]);
                if ($color !== null) {
                    $estilosNuevos[] = 'background-color: '.$color;

                    continue;
                }
            }
            $clases[] = $clase;
        }

        if ($estilosNuevos === []) {
            return $m[0];
        }

        $attrs = preg_replace('/\sclass\s*=\s*("|\')([^"\']*)\1/i', '', $attrs, 1) ?? $attrs;
        if ($clases !== []) {
            $attrs .= ' class="'.implode(' ', $clases).'"';
        }

        if (preg_match('/\sstyle\s*=\s*("|\')(.*?)\1/i', $attrs, $sm)) {
            $previo = rtrim(trim($sm[2]), ';');
            $combinado = ($previo !== '' ? $previo.'; ' : '').implode('; ', $estilosNuevos);
            $attrs = preg_replace(
                '/\sstyle\s*=\s*("|\')(.*?)\1/i',
                ' style="'.$combinado.'"',
                $attrs,
                1
            ) ?? $attrs;
        } else {
            $attrs .= ' style="'.implode('; ', $estilosNuevos).'"';
        }

        return '<'.$tag.$attrs.'>';
    }

    private static function colorDesdeClaseQuill(string $valor): ?string
    {
        $valor = str_replace(['_', '%23'], ['', '#'], $valor);
        if (preg_match('/^[0-9a-f]{3,8}$/i', $valor)) {
            $valor = '#'.$valor;
        }

        return self::colorSeguro($valor);
    }

    private static function colorSeguro(string $color): ?string
    {
        $color = trim($color);
        if (preg_match('/^#[0-9a-f]{3,8}$/i', $color)) {
            return $color;
        }
        if (preg_match('/^rgb(a)?\(\s*\d{1,3}\s*,\s*\d{1,3}\s*,\s*\d{1,3}\s*(,\s*(0|1|0?\.\d+))?\s*\)$/i', $color)) {
            return $color;
        }
        if (preg_match('/^[a-z]{3,20}$/i', $color)) {
            return strtolower($color);
        }

        return null;
    }

    /**
     * Limpia el HTML del editor Quill: deja solo etiquetas y atributos seguros.
     * Conserva ul/ol/li, data-list (viñetas/números de Quill), estilos de color y tablas.
     */
    public static function sanitizar(?string $html): ?string
    {
        if ($html === null || trim($html) === '') {
            return $html;
        }

        $html = self::normalizar($html) ?? $html;

        $permitidas = [
            'p', 'br', 'b', 'strong', 'i', 'em', 'u', 's', 'strike',
            'ul', 'ol', 'li', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
            'blockquote', 'pre', 'code', 'span', 'div', 'a',
            'table', 'thead', 'tbody', 'tr', 'th', 'td',
        ];
        $attrsPermitidos = [
            'href', 'colspan', 'rowspan', 'data-list', 'style', 'class',
        ];
        $estilosPermitidos = [
            'color', 'background-color', 'text-align',
            'border', 'border-collapse', 'border-color', 'border-width',
            'width', 'padding', 'font-weight', 'font-style', 'text-decoration',
            'list-style-type', 'margin', 'margin-left', 'padding-left',
        ];

        $wrapped = '<div id="repro-root">'.$html.'</div>';
        $prev = libxml_use_internal_errors(true);
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $cargado = $dom->loadHTML('<?xml encoding="UTF-8">'.$wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($prev);
        if (! $cargado) {
            return strip_tags($html, '<'.implode('><', $permitidas).'>');
        }

        $root = $dom->getElementById('repro-root');
        if (! $root instanceof \DOMElement) {
            return strip_tags($html, '<'.implode('><', $permitidas).'>');
        }

        $walker = static function (\DOMNode $nodo) use (&$walker, $permitidas, $attrsPermitidos, $estilosPermitidos): void {
            if ($nodo instanceof \DOMElement) {
                $tag = strtolower($nodo->tagName);
                if (! in_array($tag, $permitidas, true)) {
                    $parent = $nodo->parentNode;
                    $movidos = [];
                    if ($parent) {
                        while ($nodo->firstChild) {
                            $movidos[] = $nodo->firstChild;
                            $parent->insertBefore($nodo->firstChild, $nodo);
                        }
                        $parent->removeChild($nodo);
                    }
                    foreach ($movidos as $hijo) {
                        $walker($hijo);
                    }

                    return;
                }

                $quitar = [];
                foreach ($nodo->attributes ?? [] as $attr) {
                    $nombre = strtolower($attr->name);
                    if (str_starts_with($nombre, 'on')) {
                        $quitar[] = $attr->name;

                        continue;
                    }
                    if (! in_array($nombre, $attrsPermitidos, true)) {
                        $quitar[] = $attr->name;

                        continue;
                    }
                    if (in_array($nombre, ['href', 'src'], true) && preg_match('/^\s*javascript:/i', $attr->value)) {
                        $quitar[] = $attr->name;

                        continue;
                    }
                    if ($nombre === 'style') {
                        $keep = [];
                        foreach (explode(';', $attr->value) as $decl) {
                            if (! str_contains($decl, ':')) {
                                continue;
                            }
                            [$prop, $val] = array_map('trim', explode(':', $decl, 2));
                            $prop = strtolower($prop);
                            if (! in_array($prop, $estilosPermitidos, true) || $val === '') {
                                continue;
                            }
                            if (preg_match('/expression|javascript|behavior|@import|url\s*\(/i', $val)) {
                                continue;
                            }
                            $keep[] = $prop.': '.$val;
                        }
                        if ($keep === []) {
                            $quitar[] = $attr->name;
                        } else {
                            $nodo->setAttribute('style', implode('; ', $keep));
                        }
                    }
                    if ($nombre === 'class') {
                        $clases = [];
                        foreach (preg_split('/\s+/', trim($attr->value)) ?: [] as $clase) {
                            if ($clase === '' || preg_match('/^ql-/i', $clase)) {
                                continue;
                            }
                            if (preg_match('/^[a-zA-Z0-9_-]+$/', $clase)) {
                                $clases[] = $clase;
                            }
                        }
                        if ($clases === []) {
                            $quitar[] = $attr->name;
                        } else {
                            $nodo->setAttribute('class', implode(' ', $clases));
                        }
                    }
                    if ($nombre === 'data-list' && ! in_array($attr->value, ['bullet', 'ordered'], true)) {
                        $quitar[] = $attr->name;
                    }
                }
                foreach ($quitar as $nombre) {
                    $nodo->removeAttribute($nombre);
                }
            }

            $hijos = [];
            foreach ($nodo->childNodes as $hijo) {
                $hijos[] = $hijo;
            }
            foreach ($hijos as $hijo) {
                $walker($hijo);
            }
        };
        $walker($root);

        $salida = '';
        foreach ($root->childNodes as $hijo) {
            $salida .= $dom->saveHTML($hijo);
        }

        return $salida;
    }
}
