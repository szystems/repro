<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EmpresasExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $empresas;

    public function __construct(Collection $empresas)
    {
        $this->empresas = $empresas;
    }

    public function collection()
    {
        return $this->empresas;
    }

    public function headings(): array
    {
        return [
            'Nombre',
            'NIT',
            'Email',
            'Teléfono',
            'Contacto',
            'Estado',
            'Total Órdenes',
            'Fecha Registro',
        ];
    }

    public function map($empresa): array
    {
        return [
            $empresa->nombre,
            $empresa->nit ?? 'N/A',
            $empresa->email ?? 'N/A',
            $empresa->telefono ?? 'N/A',
            $empresa->contacto_nombre ?? 'N/A',
            $empresa->estado == 1 ? 'Activa' : 'Inactiva',
            $empresa->ordenes_count ?? 0,
            $empresa->created_at?->format('d/m/Y'),
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
        foreach ($this->collection() as $empresa) {
            $html .= '<tr>';
            foreach ($this->map($empresa) as $cell) {
                $html .= '<td>'.e((string) $cell).'</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table></body></html>';

        return $html;
    }
}
