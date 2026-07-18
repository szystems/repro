<?php

namespace App\Support;

use App\Models\EvaluadoOrden;
use App\Models\Orden;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;

class InformeWordExport
{
    public static function generar(Orden $orden, EvaluadoOrden $evaluado): string
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
            $fecha = $evaluado->fecha_realizada instanceof \Carbon\Carbon
                ? $evaluado->fecha_realizada->format('d/m/Y')
                : (string) $evaluado->fecha_realizada;
            $section->addText('Fecha evaluación: ' . $fecha);
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
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $path = $dir . '/informe_' . $evaluado->id . '_' . uniqid() . '.docx';
        IOFactory::createWriter($phpWord, 'Word2007')->save($path);

        return $path;
    }
}
