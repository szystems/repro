<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Reporte de Empresas</title>
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
            width: 50%;
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
        .badge-secondary {
            background-color: #6c757d;
            color: white;
        }
        .badge-primary {
            background-color: #000555;
            color: white;
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
        .repro-header { background-color: #000555; color: white; padding: 15px 20px; margin-bottom: 20px; }
        .repro-header-content { display: table; width: 100%; }
        .repro-logo-cell { display: table-cell; width: 160px; vertical-align: middle; }
        .repro-logo-container { background-color: white; padding: 4px 8px; border-radius: 4px; display: inline-block; }
        .repro-logo { max-height: 40px; max-width: 150px; display: block; }
        .repro-title-cell { display: table-cell; vertical-align: middle; padding-left: 15px; }
        .repro-title-cell h1 { font-size: 16px; margin-bottom: 4px; }
        .repro-title-cell h2 { font-size: 10px; font-weight: normal; opacity: 0.85; }
        .repro-info-cell { display: table-cell; vertical-align: middle; text-align: right; font-size: 9px; opacity: 0.9; width: 100px; }
    </style>
    @include('shared.pdf.flujo-pagina')
</head>
<body>
    {{-- Cabecera estilo REPRO --}}
    <div class="repro-header">
        <div class="repro-header-content">
            <div class="repro-logo-cell">
                <div class="repro-logo-container">
                    <img src="{{ public_path('img/logos/logoreproxelahorizontal.png') }}" alt="REPRO" class="repro-logo">
                </div>
            </div>
            <div class="repro-title-cell">
                <h1>Reporte de Empresas</h1>
                <h2>Sistema de Evaluaciones REPRO Guatemala</h2>
            </div>
            <div class="repro-info-cell">
                <strong>Generado:</strong><br>
                {{ now()->format('d/m/Y') }}<br>
                {{ now()->format('H:i') }}
            </div>
        </div>
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
            <strong>Estado de Empresa:</strong> {{ $filtros['estado'] == '1' ? 'Activas' : ($filtros['estado'] == '0' ? 'Inactivas' : 'Todas') }}
        </div>
    </div>

    <div class="stats">
        <table class="stats-table">
            <tr>
                <td>
                    <div class="stat-label">Total Empresas</div>
                    <div class="stat-value">{{ $stats['total_empresas'] }}</div>
                </td>
                <td>
                    <div class="stat-label">Total Órdenes</div>
                    <div class="stat-value" style="color: #0dcaf0;">{{ $stats['total_ordenes'] }}</div>
                </td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25%;">Empresa</th>
                <th style="width: 12%;">NIT</th>
                <th style="width: 20%;">Email</th>
                <th style="width: 12%;">Teléfono</th>
                <th style="width: 10%;">Órdenes</th>
                <th style="width: 10%;">Completadas</th>
                <th style="width: 8%;">Estado de Empresa</th>
                <th style="width: 8%;">Registro</th>
            </tr>
        </thead>
        <tbody>
            @forelse($empresas as $empresa)
                <tr>
                    <td><strong>{{ Str::limit($empresa->nombre, 30) }}</strong></td>
                    <td>{{ $empresa->nit ?? 'N/A' }}</td>
                    <td>{{ Str::limit($empresa->email ?? 'N/A', 25) }}</td>
                    <td>{{ $empresa->telefono ?? 'N/A' }}</td>
                    <td style="text-align: center;">
                        <span class="badge badge-primary">{{ $empresa->ordenes_count }}</span>
                    </td>
                    <td style="text-align: center;">
                        <span class="badge badge-success">{{ $empresa->ordenes_completadas_count ?? 0 }}</span>
                    </td>
                    <td>
                        <span class="badge badge-{{ $empresa->estado == 1 ? 'success' : 'secondary' }}">
                            {{ $empresa->estado == 1 ? 'Activa' : 'Inactiva' }}
                        </span>
                    </td>
                    <td>{{ $empresa->created_at->format('d/m/Y') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 20px;">
                        No se encontraron empresas con los filtros seleccionados
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <div class="footer-content">
            <div class="footer-left">
                REPRO Guatemala - Reporte de Empresas
            </div>
            <div class="footer-right">
                Página 1 | Generado: {{ now()->format('d/m/Y H:i') }}
            </div>
        </div>
    </div>
</body>
</html>
