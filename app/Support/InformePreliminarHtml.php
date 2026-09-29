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
}
