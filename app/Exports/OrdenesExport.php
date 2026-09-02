<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OrdenesExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function __construct(protected Collection $ordenes)
    {
    }

    public function collection()
    {
        return $this->ordenes;
    }

    public function headings(): array
    {
        return [
            'Código',
            'Empresa',
            'Tipos de Servicio',
            'Estado',
            'Evaluados',
            'Fecha Solicitud',
            'Fecha Creación',
            'Prioridad',
            'Cantidad Evaluados',
        ];
    }

    public function map($orden): array
    {
        $tipos = $orden->evaluados
            ->pluck('tipo_servicio')
            ->unique()
            ->map(fn ($tipo) => match ($tipo) {
                'poligrafo' => 'Polígrafo',
                'vsa' => 'VSA',
                'socioeconomico' => 'Socioeconómico',
                default => $tipo,
            })
            ->filter()
            ->implode(', ');

        $evaluados = $orden->evaluados
            ->map(fn ($evaluado) => trim($evaluado->nombre.' '.$evaluado->apellidos))
            ->filter()
            ->implode('; ');

        return [
            $orden->codigo_orden,
            $orden->empresa->nombre ?? 'N/A',
            $tipos !== '' ? $tipos : 'Sin definir',
            $orden->estado_human,
            $evaluados !== '' ? $evaluados : 'Sin evaluados',
            $orden->fecha_solicitud?->format('d/m/Y') ?? 'N/A',
            $orden->created_at?->format('d/m/Y') ?? 'N/A',
            ucfirst($orden->prioridad ?? 'normal'),
            $orden->evaluados_count ?? $orden->evaluados->count(),
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
        foreach ($this->collection() as $orden) {
            $html .= '<tr>';
            foreach ($this->map($orden) as $cell) {
                $html .= '<td>'.e((string) $cell).'</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table></body></html>';

        return $html;
    }
}
