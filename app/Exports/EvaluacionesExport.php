<?php

namespace App\Exports;

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
            'Tipo de Servicio',
            'Puesto',
            'Estado Cuestionario',
            'Fecha Creación',
            'Fecha Completado',
        ];
    }

    public function map($evaluado): array
    {
        return [
            $evaluado->orden->codigo_orden ?? 'N/A',
            $evaluado->orden->empresa->nombre ?? 'N/A',
            $evaluado->nombre,
            $evaluado->apellidos,
            $evaluado->dpi,
            ucfirst($evaluado->tipo_servicio),
            $evaluado->puesto ?? 'N/A',
            $evaluado->cuestionario_completado ? 'Completado' : 'Pendiente',
            $evaluado->created_at?->format('d/m/Y'),
            $evaluado->cuestionario_completado_at?->format('d/m/Y H:i') ?? '-',
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
}
