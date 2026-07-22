<?php

namespace App\Support;

use App\Models\EvaluadoOrden;
use App\Models\Orden;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;
use ZipArchive;

/**
 * E7 / F7 — Exportación de informe editable (.docx) por candidato.
 * Usa plantillas oficiales REPRO (jul-2026) preservando tablas y diseño.
 */
class InformeWordExport
{
    public static function generar(Orden $orden, EvaluadoOrden $evaluado): string
    {
        $evaluado->loadMissing(['sede', 'cuestionario', 'poligrafista', 'orden.empresa', 'orden.sede']);

        $config = InformeWordPlantillas::resolver($evaluado);
        if ($config !== null) {
            return self::generarDesdePlantilla($config, $orden, $evaluado);
        }

        return self::generarBasico($orden, $evaluado);
    }

    public static function rutaPlantilla(): string
    {
        return InformeWordPlantillas::rutaPorDefecto();
    }

    /**
     * @param  array{path: string, variante: string, foto_media: string}  $config
     */
    private static function generarDesdePlantilla(array $config, Orden $orden, EvaluadoOrden $evaluado): string
    {
        $dir = storage_path('app/temp');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $destino = $dir . '/informe_' . $evaluado->id . '_' . uniqid() . '.docx';
        if (! copy($config['path'], $destino)) {
            return self::generarBasico($orden, $evaluado);
        }

        $zip = new ZipArchive();
        if ($zip->open($destino) !== true) {
            @unlink($destino);

            return self::generarBasico($orden, $evaluado);
        }

        $xml = $zip->getFromName('word/document.xml');
        if ($xml === false) {
            $zip->close();
            @unlink($destino);

            return self::generarBasico($orden, $evaluado);
        }

        $xml = InformeWordRelleno::aplicar($xml, $orden, $evaluado, $config);
        $xml = self::aplicarFotoEvaluadoEnDocumento($xml, $evaluado, $zip);

        if (! InformeWordXml::esValido($xml)) {
            $zip->close();
            @unlink($destino);

            return self::generarBasico($orden, $evaluado);
        }

        $zip->deleteName('word/document.xml');
        $zip->addFromString('word/document.xml', $xml);
        InformeWordAnexos::aplicar($zip, $evaluado);
        $zip->close();

        return $destino;
    }

    private static function aplicarFotoEvaluadoEnDocumento(string $documentXml, EvaluadoOrden $evaluado, ZipArchive $zip): string
    {
        $rutaFoto = InformeWordDatos::fotoEvaluadoRuta($evaluado);
        $media = $rutaFoto !== null ? InformeWordFoto::prepararMedia($rutaFoto) : null;

        if ($media === null) {
            return InformeWordFoto::compactarEspacioSinFoto($documentXml);
        }

        $relsXml = $zip->getFromName('word/_rels/document.xml.rels');
        if ($relsXml === false) {
            return $documentXml;
        }

        $relId = InformeWordXml::siguienteRelId($relsXml);
        $nombreArchivo = 'foto_evaluado.' . $media['extension'];
        $relsXml = InformeWordXml::agregarRelacionImagen($relsXml, $relId, $nombreArchivo);

        $zip->addFromString('word/media/' . $nombreArchivo, $media['bytes']);
        InformeWordXml::registrarExtensionMedia($zip, $media['extension']);
        $zip->deleteName('word/_rels/document.xml.rels');
        $zip->addFromString('word/_rels/document.xml.rels', $relsXml);

        return InformeWordFoto::insertarEnDocumento($documentXml, $media, $relId);
    }

    private static function generarBasico(Orden $orden, EvaluadoOrden $evaluado): string
    {
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(10);

        $section = $phpWord->addSection([
            'marginTop' => 720,
            'marginBottom' => 720,
            'marginLeft' => 900,
            'marginRight' => 900,
        ]);

        $section->addText('REPRO GUATEMALA', ['bold' => true, 'size' => 14, 'color' => '000555']);
        $section->addText('Informe de Candidato', ['bold' => true, 'size' => 12, 'color' => 'FFB000']);
        $section->addTextBreak(1);

        $section->addText('Orden: ' . ($orden->codigo_orden ?? '—'), ['bold' => true]);
        $section->addText('Empresa: ' . ($orden->empresa?->nombre ?? '—'));
        $section->addText('Fecha de generación: ' . now()->format('d/m/Y H:i'));
        $section->addTextBreak(1);

        $section->addText('Datos del candidato', ['bold' => true, 'size' => 11, 'underline' => 'single']);
        $section->addText('Nombre: ' . trim($evaluado->nombre . ' ' . $evaluado->apellidos));
        $section->addText('DPI: ' . ($evaluado->dpi ?? '—'));
        $section->addText('Servicio: ' . $evaluado->tipo_servicio_texto);
        $section->addText('Formulario: ' . ucfirst($evaluado->tipo_formulario ?? '—'));
        $section->addText('Puesto: ' . ($evaluado->puesto_evaluar ?: '—'));

        if ($evaluado->motivo_hecho_evaluacion) {
            $section->addText('Motivo/hecho: ' . $evaluado->motivo_hecho_evaluacion);
        }

        $section->addTextBreak(1);
        $section->addText('Resultado', ['bold' => true, 'size' => 11, 'underline' => 'single']);
        $section->addText('Clasificación: ' . ucfirst(str_replace('_', ' ', $evaluado->resultado ?? 'pendiente')));

        if ($evaluado->fecha_realizada) {
            $valores = InformeWordDatos::encabezado($orden, $evaluado);
            $section->addText('Fecha evaluación: ' . $valores['fecha']);
        }

        if ($evaluado->texto_informe_preliminar) {
            $section->addTextBreak(1);
            $section->addText('Informe preliminar', ['bold' => true, 'size' => 11, 'underline' => 'single']);
            $section->addText($evaluado->texto_informe_preliminar);
        }

        if ($evaluado->notas_poligrafo) {
            $section->addTextBreak(1);
            $section->addText('Observaciones / notas del evaluador', ['bold' => true, 'size' => 11, 'underline' => 'single']);
            $section->addText($evaluado->notas_poligrafo);
        }

        $section->addTextBreak(2);
        $section->addText(
            'Documento generado automáticamente por REPRO. Puede editar libremente en Microsoft Word antes de entregar al cliente.',
            ['italic' => true, 'size' => 9, 'color' => '666666'],
            ['alignment' => Jc::CENTER]
        );

        $dir = storage_path('app/temp');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $path = $dir . '/informe_' . $evaluado->id . '_' . uniqid() . '.docx';
        IOFactory::createWriter($phpWord, 'Word2007')->save($path);

        return $path;
    }
}
