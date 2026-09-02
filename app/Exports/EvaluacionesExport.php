<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EvaluacionesExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $evaluados;

    public function __construct(Collection $evaluados)
    {
        $this->evaluados = $evaluados;
    }

    public function collection()
    {
        return $this->evaluados;
    }

    public function headings(): array
    {
        return [
            'Código Orden',
            'Empresa',
            'Nombre',
            'Apellidos',
            'DPI',
            'Teléfono',
            'Tipo de Servicio',
            'Tipo de Formulario',
            'Estado de Formulario',
            'Estado de Programación',
            'Estado de Evaluación',
            'Puesto',
            'Fecha Creación',
            'Fecha Completado',
        ];
    }

    public function map($evaluado): array
    {
        // Convertir cuestionario_completado_at a Carbon si es string
        $fechaCompletado = '-';
        if ($evaluado->cuestionario_completado_at) {
            try {
                $fecha = $evaluado->cuestionario_completado_at instanceof Carbon
                    ? $evaluado->cuestionario_completado_at
                    : Carbon::parse($evaluado->cuestionario_completado_at);
                $fechaCompletado = $fecha->format('d/m/Y H:i');
            } catch (\Exception $e) {
                $fechaCompletado = '-';
            }
        }

        return [
            $evaluado->orden->codigo_orden ?? 'N/A',
            $evaluado->orden->empresa->nombre ?? 'N/A',
            $evaluado->nombre,
            $evaluado->apellidos,
            $evaluado->dpi,
            $evaluado->telefono ?? $evaluado->celular ?? '',
            ucfirst($evaluado->tipo_servicio),
            $evaluado->tipo_formulario_texto ?? 'N/A',
            $evaluado->estado_formulario_texto,
            $evaluado->estado_programacion_texto,
            $evaluado->estado_evaluacion_texto,
            $evaluado->puesto_evaluar ?? 'N/A',
            $evaluado->created_at?->format('d/m/Y'),
            $fechaCompletado,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '000555'],
                ],
            ],
        ];
    }

    /**
     * Tabla HTML que Excel abre como libro (iPage no tiene XMLWriter / XLSX).
     */
    public function toHtmlTable(): string
    {
        $html = '<html><head><meta charset="UTF-8"></head><body><table border="1"><thead><tr>';
        foreach ($this->headings() as $heading) {
            $html .= '<th>'.e((string) $heading).'</th>';
        }
        $html .= '</tr></thead><tbody>';
        foreach ($this->collection() as $evaluado) {
            $html .= '<tr>';
            foreach ($this->map($evaluado) as $cell) {
                $html .= '<td>'.e((string) $cell).'</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table></body></html>';

        return $html;
    }
}
