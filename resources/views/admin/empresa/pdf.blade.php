<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Listado de Empresas</title>
    <style>
        @page {
            size: portrait;
            margin: 12mm 15mm 15mm 15mm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 0;
            color: #333;
        }

        /* Cabecera estilo REPRO */
        .repro-header {
            background-color: #000555;
            color: white;
            padding: 12px 20px;
            margin-bottom: 15px;
            border-radius: 6px;
        }

        .repro-header-content {
            display: table;
            width: 100%;
        }

        .repro-logo-cell {
            display: table-cell;
            vertical-align: middle;
            width: 180px;
        }

        .repro-logo-container {
            background-color: #f8f9fa;
            border: 1px solid #000555;
            border-radius: 6px;
            padding: 8px 12px;
            display: inline-block;
        }

        .repro-logo {
            max-height: 40px;
            max-width: 150px;
            display: block;
        }

        .repro-title-cell {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
        }

        .repro-header h1 {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
            color: #ffb000;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .repro-header h2 {
            margin: 4px 0 0 0;
            font-size: 11px;
            font-weight: normal;
            color: #ffcc33;
        }

        .repro-info-cell {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
            width: 120px;
            font-size: 9px;
        }

        .repro-info-cell span {
            color: #ffcc33;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 5px;
            font-size: 9px;
        }
        th {
            background-color: #000555;
            color: #ffb000;
            text-align: left;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-center {
            text-align: center;
        }
        .badge {
            display: inline-block;
            padding: 3px 6px;
            font-size: 8px;
            border-radius: 3px;
            color: #fff;
        }
        .badge-success {
            background-color: #28a745;
        }
        .badge-danger {
            background-color: #dc3545;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 8px;
            color: #000555;
            border-top: 2px solid #ffb000;
            padding-top: 10px;
        }
        .text-muted {
            color: #6c757d;
        }
        .logo-cell {
            width: 60px;
            text-align: center;
        }
        .empresa-logo {
            max-width: 50px;
            max-height: 30px;
        }
        .no-logo {
            color: #999;
            font-style: italic;
            font-size: 8px;
        }
    </style>
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
                <h1>{{ $titulo ?? 'Listado de Empresas' }}</h1>
                <h2>Reporte del Sistema</h2>
            </div>
            <div class="repro-info-cell">
                <span>Fecha:</span> {{ now()->format('d/m/Y') }}
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="logo-cell">Logo</th>
                <th>Nombre</th>
                <th>NIT</th>
                <th>Contacto</th>
                <th>Sitio Web</th>
                <th>Usuarios</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($empresas as $empresa)
                <tr>
                    <td class="logo-cell">
                        @if($empresa->logo && file_exists(public_path('assets/imgs/empresas/'.$empresa->logo)))
                            <img src="{{ public_path('assets/imgs/empresas/'.$empresa->logo) }}" alt="Logo" class="empresa-logo">
                        @else
                            <span class="no-logo">Sin logo</span>
                        @endif
                    </td>
                    <td>
                        <strong>{{ $empresa->nombre }}</strong>
                    </td>
                    <td>{{ $empresa->nit ?? 'No definido' }}</td>
                    <td>
                        @if($empresa->telefono)
                            <div>Tel: {{ $empresa->telefono }}</div>
                        @endif
                        @if($empresa->email)
                            <div>{{ $empresa->email }}</div>
                        @endif
                    </td>
                    <td>{{ $empresa->sitio_web ?? 'No definido' }}</td>
                    <td class="text-center">{{ $empresa->getTotalUsuarios() }}</td>
                    <td class="text-center">
                        <span class="badge {{ $empresa->estado ? 'badge-success' : 'badge-danger' }}">
                            {{ $empresa->getEstadoTexto() }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted">No hay empresas registradas</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p><strong>REPRO Guatemala</strong></p>
        <p>Documento generado el {{ now()->format('d/m/Y H:i') }}</p>
        <p style="color: #ffb000;">Este documento es confidencial y de uso exclusivo para fines administrativos.</p>
    </div>
</body>
</html>
