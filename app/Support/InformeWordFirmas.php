<?php

namespace App\Support;

use App\Models\EvaluadoOrden;
use App\Models\EvaluadorNota;

/**
 * En el Word de polígrafo la plantilla trae tres firmas (Stefanie, Rodrigo y Narda).
 * Si hay una elegida, el documento se entrega solo con esa firma, su nombre y su registro APA.
 * VSA no entra aquí: Aldin Tobar se queda como está en su plantilla.
 */
class InformeWordFirmas
{
    public const SECCION = 'firma_informe_word';

    /** @var array<string, array{nombre: string, apa: string, orden: int}> */
    public const FIRMANTES = [
        'stefanie' => ['nombre' => 'Stefanie Castro', 'apa' => '9245', 'orden' => 0],
        'rodrigo' => ['nombre' => 'Rodrigo Castro', 'apa' => '12871', 'orden' => 1],
        'narda' => ['nombre' => 'Narda Plester', 'apa' => '10296', 'orden' => 2],
    ];

    public static function claveEfectiva(EvaluadoOrden $evaluado): ?string
    {
        if (($evaluado->tipo_servicio ?? '') !== 'poligrafo') {
            return null;
        }

        $guardada = EvaluadorNota::query()
            ->where('evaluado_orden_id', $evaluado->id)
            ->where('seccion', self::SECCION)
            ->where('campo', '')
            ->value('contenido');
        $guardada = is_string($guardada) ? trim($guardada) : '';

        if ($guardada === 'todas') {
            return null;
        }

        if (isset(self::FIRMANTES[$guardada])) {
            return $guardada;
        }

        $evaluado->loadMissing('entrevistador');

        return self::claveDesdeNombre($evaluado->entrevistador?->name);
    }

    public static function claveDesdeNombre(?string $nombre): ?string
    {
        $nombre = mb_strtolower(trim((string) $nombre));
        if ($nombre === '') {
            return null;
        }

        foreach (self::FIRMANTES as $clave => $datos) {
            $primero = mb_strtolower(explode(' ', $datos['nombre'])[0]);
            if ($primero !== '' && str_contains($nombre, $primero)) {
                return $clave;
            }
        }

        return null;
    }

    /**
     * Quita las otras dos firmas del XML. Si no hay una clave válida, el XML no cambia.
     */
    public static function dejarSolo(string $xml, ?string $clave): string
    {
        if ($clave === null || ! isset(self::FIRMANTES[$clave])) {
            return $xml;
        }

        if (! str_contains($xml, 'Stefanie') || ! str_contains($xml, 'Rodrigo') || ! str_contains($xml, 'Narda')) {
            return $xml;
        }

        $cambios = [];
        $desde = 0;
        $marca = 'name="Cuadro de texto 4"';

        while (($pos = strpos($xml, $marca, $desde)) !== false) {
            $cajaInicio = strrpos(substr($xml, 0, $pos), '<w:drawing>');
            $cajaFinRel = strpos($xml, '</w:drawing>', $pos);
            if ($cajaInicio === false || $cajaFinRel === false) {
                break;
            }

            $cajaFin = $cajaFinRel + strlen('</w:drawing>');
            $caja = substr($xml, $cajaInicio, $cajaFin - $cajaInicio);
            if (! str_contains($caja, 'Stefanie') || ! str_contains($caja, 'Narda')) {
                $desde = $cajaFin;
                continue;
            }

            $ventanaInicio = max(0, $cajaInicio - 25000);
            $imagenes = self::dibujosImagen($xml, $ventanaInicio, $cajaInicio);
            if (count($imagenes) !== 3) {
                $desde = $cajaFin;
                continue;
            }

            usort($imagenes, static fn (array $a, array $b): int => $a['x'] <=> $b['x']);
            $elegida = $imagenes[self::FIRMANTES[$clave]['orden']];
            $xCaja = self::posicionHorizontal($caja);
            $anchoCaja = self::anchoDibujo($caja);

            foreach ($imagenes as $imagen) {
                if ($imagen['start'] === $elegida['start']) {
                    $cambios[] = [$imagen['start'], $imagen['end'], self::centrarDibujo($imagen['xml'], $xCaja, $anchoCaja)];
                } else {
                    $cambios[] = [$imagen['start'], $imagen['end'], ''];
                }
            }

            $desde = $cajaFin;
        }

        if ($cambios === []) {
            return $xml;
        }

        usort($cambios, static fn (array $a, array $b): int => $b[0] <=> $a[0]);
        foreach ($cambios as [$inicio, $fin, $reemplazo]) {
            $xml = substr($xml, 0, $inicio).$reemplazo.substr($xml, $fin);
        }

        return self::reescribirTextos($xml, $clave);
    }

    /**
     * La plantilla guarda el nombre dos veces: el dibujo moderno y el respaldo de Word.
     */
    private static function reescribirTextos(string $xml, string $clave): string
    {
        $nuevo = preg_replace_callback(
            '/<w:txbxContent>.*?<\/w:txbxContent>/s',
            static function (array $coincide) use ($clave): string {
                $caja = $coincide[0];
                if (! str_contains($caja, 'Stefanie') || ! str_contains($caja, 'Rodrigo') || ! str_contains($caja, 'Narda')) {
                    return $caja;
                }

                $datos = self::FIRMANTES[$clave];
                $contenido = self::parrafoCentrado($datos['nombre'])
                    .self::parrafoCentrado('Poligrafista Certificado')
                    .self::parrafoCentrado('Registro APA No. '.$datos['apa']);

                return '<w:txbxContent>'.$contenido.'</w:txbxContent>';
            },
            $xml
        );

        return is_string($nuevo) ? $nuevo : $xml;
    }

    /**
     * @return list<array{start: int, end: int, xml: string, x: int}>
     */
    private static function dibujosImagen(string $xml, int $desde, int $hasta): array
    {
        $ventana = substr($xml, $desde, $hasta - $desde);
        $lista = [];
        $cursor = 0;

        while (($rel = strpos($ventana, '<w:drawing>', $cursor)) !== false) {
            $finRel = strpos($ventana, '</w:drawing>', $rel);
            if ($finRel === false) {
                break;
            }

            $finRel += strlen('</w:drawing>');
            $dibujo = substr($ventana, $rel, $finRel - $rel);
            if (preg_match('/<wp:docPr\b[^>]*\bname="Imagen/', $dibujo) === 1) {
                $lista[] = [
                    'start' => $desde + $rel,
                    'end' => $desde + $finRel,
                    'xml' => $dibujo,
                    'x' => self::posicionHorizontal($dibujo),
                ];
            }
            $cursor = $finRel;
        }

        return $lista;
    }

    private static function posicionHorizontal(string $dibujo): int
    {
        if (preg_match('/<wp:positionH\b[^>]*>(.*?)<\/wp:positionH>/s', $dibujo, $coincide) !== 1) {
            return 0;
        }

        if (preg_match('/<wp:posOffset>(-?\d+)<\/wp:posOffset>/', $coincide[1], $offset) === 1) {
            return (int) $offset[1];
        }

        return 0;
    }

    private static function anchoDibujo(string $dibujo): int
    {
        if (preg_match('/<wp:extent\b[^>]*\bcx="(\d+)"/', $dibujo, $coincide) !== 1) {
            return 0;
        }

        return (int) $coincide[1];
    }

    private static function centrarDibujo(string $dibujo, int $xCaja, int $anchoCaja): string
    {
        $ancho = self::anchoDibujo($dibujo);
        if ($anchoCaja <= 0 || $ancho <= 0) {
            return $dibujo;
        }

        $x = max(0, $xCaja + intdiv($anchoCaja - $ancho, 2));
        $reemplazo = '<wp:positionH relativeFrom="column"><wp:posOffset>'.$x.'</wp:posOffset></wp:positionH>';
        $nuevo = preg_replace('/<wp:positionH\b[^>]*>.*?<\/wp:positionH>/s', $reemplazo, $dibujo, 1);

        return is_string($nuevo) ? $nuevo : $dibujo;
    }

    private static function parrafoCentrado(string $texto): string
    {
        $texto = htmlspecialchars($texto, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $fuente = '<w:rFonts w:ascii="Helvetica" w:hAnsi="Helvetica" w:cs="Helvetica"/>'
            .'<w:b/><w:sz w:val="22"/><w:szCs w:val="22"/>';

        return '<w:p><w:pPr><w:jc w:val="center"/><w:rPr>'.$fuente.'</w:rPr></w:pPr>'
            .'<w:r><w:rPr>'.$fuente.'</w:rPr><w:t xml:space="preserve">'.$texto.'</w:t></w:r></w:p>';
    }
}
