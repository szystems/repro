<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UsersExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function __construct(protected Collection $usuarios)
    {
    }

    public function collection()
    {
        return $this->usuarios;
    }

    public function headings(): array
    {
        return [
            'Nombre',
            'Email',
            'Teléfono',
            'Tipo',
            'Rol',
            'Empresa',
            'Estado',
            'Principal',
            'Cargo',
            'Fecha registro',
        ];
    }

    public function map($usuario): array
    {
        $tipo = match ((int) $usuario->role_as) {
            3 => 'Administrador',
            2 => 'REPRO',
            1 => 'Empresa',
            default => 'Otro',
        };

        $rol = $usuario->roles->pluck('display_name')->filter()->implode(', ');
        if ($rol === '') {
            $rol = $usuario->roles->pluck('name')->filter()->implode(', ');
        }

        return [
            $usuario->name,
            $usuario->email,
            $usuario->telefono ?? '',
            $tipo,
            $rol !== '' ? $rol : '—',
            $usuario->empresa->nombre ?? '—',
            ((int) $usuario->estado === 1) ? 'Activo' : 'Inactivo',
            ((int) $usuario->principal === 1) ? 'Sí' : 'No',
            $usuario->cargo ?? '',
            $usuario->created_at?->format('d/m/Y'),
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

    public function toHtmlTable(): string
    {
        $html = '<html><head><meta charset="UTF-8"></head><body><table border="1"><thead><tr>';
        foreach ($this->headings() as $heading) {
            $html .= '<th>'.e((string) $heading).'</th>';
        }
        $html .= '</tr></thead><tbody>';
        foreach ($this->collection() as $usuario) {
            $html .= '<tr>';
            foreach ($this->map($usuario) as $cell) {
                $html .= '<td>'.e((string) $cell).'</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table></body></html>';

        return $html;
    }
}
