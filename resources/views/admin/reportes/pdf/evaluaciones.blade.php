<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Reporte de Evaluaciones</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            background-color: #000555;
            color: white;
            padding: 15px 20px;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 18px;
            margin-bottom: 5px;
        }
        .header p {
            font-size: 10px;
            opacity: 0.9;
        }
        .filters {
            background-color: #f8f9fa;
            padding: 10px 15px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .filters h3 {
            font-size: 11px;
            color: #000555;
            margin-bottom: 8px;
        }
        .filters-grid {
            display: table;
            width: 100%;
        }
        .filter-item {
            display: inline-block;
            margin-right: 20px;
            font-size: 9px;
        }
        .filter-item strong {
            color: #000555;
        }
        .stats {
            margin-bottom: 15px;
        }
        .stats-table {
            width: 100%;
            border-collapse: collapse;
        }
        .stats-table td {
            padding: 10px;
            text-align: center;
            border: 1px solid #dee2e6;
            width: 25%;
        }
        .stats-table .stat-label {
            font-size: 8px;
            color: #6c757d;
            text-transform: uppercase;
        }
        .stats-table .stat-value {
            font-size: 18px;
            font-weight: bold;
            color: #000555;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table.data-table th {
            background-color: #000555;
            color: white;
            padding: 8px 5px;
            text-align: left;
            font-size: 9px;
            font-weight: bold;
        }
        table.data-table td {
            padding: 6px 5px;
            border-bottom: 1px solid #dee2e6;
            font-size: 9px;
        }
        table.data-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        .badge-success {
            background-color: #198754;
            color: white;
        }
        .badge-warning {
            background-color: #ffc107;
            color: #333;
        }
        .badge-primary {
            background-color: #000555;
            color: white;
        }
        .badge-info {
            background-color: #0dcaf0;
            color: #333;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 10px 20px;
            border-top: 2px solid #ffb000;
            font-size: 8px;
            color: #6c757d;
        }
        .footer-content {
            display: table;
            width: 100%;
        }
        .footer-left {
            display: table-cell;
            text-align: left;
        }
        .footer-right {
            display: table-cell;
            text-align: right;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>REPORTE DE EVALUACIONES</h1>
        <p>REPRO Guatemala - Sistema de Evaluaciones</p>
        <p>Generado: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="filters">
        <h3>Filtros Aplicados</h3>
        <div class="filter-item">
            <strong>Fecha Inicio:</strong> {{ $filtros['fecha_inicio'] ?? 'Sin filtro' }}
        </div>
        <div class="filter-item">
            <strong>Fecha Fin:</strong> {{ $filtros['fecha_fin'] ?? 'Sin filtro' }}
        </div>
        <div class="filter-item">
            <strong>Empresa:</strong> {{ $filtros['empresa'] ?? 'Todas' }}
        </div>
        <div class="filter-item">
            <strong>Tipo Servicio:</strong> {{ ucfirst($filtros['tipo_servicio'] ?? 'Todos') }}
        </div>
        <div class="filter-item">
            <strong>Estado:</strong> {{ ucfirst($filtros['estado'] ?? 'Todos') }}
        </div>
    </div>

    <div class="stats">
        <table class="stats-table">
            <tr>
                <td>
                    <div class="stat-label">Total Evaluados</div>
                    <div class="stat-value">{{ $stats['total'] }}</div>
                </td>
                <td>
                    <div class="stat-label">Completados</div>
                    <div class="stat-value" style="color: #198754;">{{ $stats['completados'] }}</div>
                </td>
                <td>
                    <div class="stat-label">Pendientes</div>
                    <div class="stat-value" style="color: #ffc107;">{{ $stats['pendientes'] }}</div>
                </td>
                <td>
                    <div class="stat-label">% Completado</div>
                    <div class="stat-value">{{ $stats['total'] > 0 ? round(($stats['completados'] / $stats['total']) * 100, 1) : 0 }}%</div>
                </td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 12%;">Código Orden</th>
                <th style="width: 18%;">Empresa</th>
                <th style="width: 20%;">Evaluado</th>
                <th style="width: 12%;">DPI</th>
                <th style="width: 12%;">Servicio</th>
                <th style="width: 10%;">Puesto</th>
                <th style="width: 8%;">Estado</th>
                <th style="width: 8%;">Fecha</th>
            </tr>
        </thead>
        <tbody>
            @forelse($evaluados as $evaluado)
                <tr>
                    <td>{{ $evaluado->orden->codigo_orden ?? 'N/A' }}</td>
                    <td>{{ Str::limit($evaluado->orden->empresa->nombre ?? 'N/A', 20) }}</td>
                    <td>{{ $evaluado->nombre }} {{ $evaluado->apellidos }}</td>
                    <td>{{ $evaluado->dpi }}</td>
                    <td>
                        <span class="badge badge-{{ $evaluado->tipo_servicio == 'poligrafo' ? 'primary' : ($evaluado->tipo_servicio == 'vsa' ? 'info' : 'warning') }}">
                            {{ ucfirst($evaluado->tipo_servicio) }}
                        </span>
                    </td>
                    <td>{{ Str::limit($evaluado->puesto ?? 'N/A', 15) }}</td>
                    <td>
                        <span class="badge badge-{{ $evaluado->cuestionario_completado ? 'success' : 'warning' }}">
                            {{ $evaluado->cuestionario_completado ? 'Completado' : 'Pendiente' }}
                        </span>
                    </td>
                    <td>{{ $evaluado->created_at->format('d/m/Y') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 20px;">
                        No se encontraron evaluados con los filtros seleccionados
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <div class="footer-content">
            <div class="footer-left">
                REPRO Guatemala - Reporte de Evaluaciones
            </div>
            <div class="footer-right">
                Página 1 | Generado: {{ now()->format('d/m/Y H:i') }}
            </div>
        </div>
    </div>
</body>
</html>
