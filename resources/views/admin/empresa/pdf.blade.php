<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Listado de Empresas</title>
    <style>
        @page {
            margin: 15px;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 5px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo-container {
            text-align: center;
            margin-bottom: 10px;
        }
        .company-logo {
            max-height: 60px;
            max-width: 90%;
            display: inline-block;
        }
        h1 {
            font-size: 16px;
            margin: 5px 0;
            color: #333;
        }
        .date {
            font-size: 9px;
            color: #666;
            margin-top: 5px;
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
            background-color: #f2f2f2;
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
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 8px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 5px;
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
    <div class="header">
        @if(isset($imagen) && $imagen && file_exists($imagen))
            <div class="logo-container">
                <img src="{{ $imagen }}" alt="Logo de REPRO" class="company-logo">
            </div>
        @endif
        <h1>{{ $titulo }}</h1>
        <div class="date">Generado el {{ date('d/m/Y H:i:s') }}</div>
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
        <p>
            REPRO - Sistema de Gestión de Evaluaciones de Polígrafo<br>
            Documento generado automáticamente
        </p>
    </div>
</body>
</html>
