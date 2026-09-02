<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CalendarioExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(protected Collection $citas)
    {
    }

    public function collection()
    {
        return $this->citas;
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'Hora',
            'Candidato',
            'Empresa',
            'Sede',
            'Programó',
            'Encargado',
            'Tipo',
            'Estado evaluación',
            'Estado programación',
        ];
    }

    public function map($cita): array
    {
        $fecha = $cita->fecha_programada ? \Carbon\Carbon::parse($cita->fecha_programada) : null;

        return [
            $fecha?->format('d/m/Y') ?? '—',
            $fecha?->format('H:i') ?? '—',
            trim(($cita->nombre ?? '').' '.($cita->apellidos ?? '')),
            $cita->orden->empresa->nombre ?? '—',
            $cita->sede->nombre ?? '—',
            $cita->poligrafo->name ?? 'Sin asignar',
            $cita->responsable->name ?? 'Sin asignar',
            match ($cita->tipo_servicio) {
                'poligrafo' => 'Polígrafo',
                'vsa' => 'VSA',
                'socioeconomico' => 'Socioeconómico',
                default => (string) ($cita->tipo_servicio ?? '—'),
            },
            str_replace('_', ' ', (string) ($cita->estado_evaluacion ?? '—')),
            str_replace('_', ' ', (string) ($cita->estado_programacion ?? '—')),
        ];
    }

    /** @return list<array{0: string, 1: int}> */
    public function totalesPorEncargado(): array
    {
        $totales = [];
        foreach ($this->citas as $cita) {
            $nombre = $cita->responsable->name ?? 'Sin asignar';
            $totales[$nombre] = ($totales[$nombre] ?? 0) + 1;
        }
        ksort($totales);

        return collect($totales)
            ->map(fn (int $total, string $nombre) => [$nombre, $total])
            ->values()
            ->all();
    }

    /** @deprecated usar totalesPorEncargado */
    public function totalesPorPoligrafista(): array
    {
        return $this->totalesPorEncargado();
    }

    public function toHtmlTable(): string
    {
        $html = '<html><head><meta charset="UTF-8"></head><body>';
        $html .= '<h3>Citas</h3><table border="1"><thead><tr>';
        foreach ($this->headings() as $heading) {
            $html .= '<th>'.e((string) $heading).'</th>';
        }
        $html .= '</tr></thead><tbody>';
        foreach ($this->collection() as $cita) {
            $html .= '<tr>';
            foreach ($this->map($cita) as $cell) {
                $html .= '<td>'.e((string) $cell).'</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';
        $html .= '<h3>Total por encargado</h3><table border="1"><thead><tr><th>Encargado</th><th>Total</th></tr></thead><tbody>';
        foreach ($this->totalesPorEncargado() as [$nombre, $total]) {
            $html .= '<tr><td>'.e($nombre).'</td><td>'.e((string) $total).'</td></tr>';
        }
        $html .= '</tbody></table></body></html>';

        return $html;
    }
}
