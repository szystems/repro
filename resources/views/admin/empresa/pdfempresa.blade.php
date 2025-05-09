<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ficha de Empresa: {{ $empresa->nombre }}</title>
    <style>
        @page {
            size: portrait;
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
            margin-bottom: 15px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
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
            padding: 4px 5px;
            font-size: 9px;
            line-height: 1.2;
        }
        th {
            background-color: #f2f2f2;
            text-align: left;
            font-weight: bold;
            height: auto !important;
            max-height: 20px !important;
        }
        .section {
            margin-bottom: 15px;
        }
        .section-title {
            background-color: #4a89dc;
            color: white;
            padding: 5px;
            font-weight: bold;
            font-size: 10px;
            margin-bottom: 10px;
        }
        .logo-empresa {
            max-width: 150px;
            max-height: 150px;
            display: block;
            margin: 0 auto;
            border: 1px solid #ddd;
            padding: 5px;
        }
        .info-block {
            margin-bottom: 8px;
        }
        .info-label {
            font-weight: bold;
            color: #555;
            margin-bottom: 1px;
        }
        .info-value {
            margin: 0;
        }
        .user-list {
            margin-top: 10px;
        }
        .user-item {
            margin-bottom: 3px;
            padding-bottom: 3px;
            border-bottom: 1px dotted #ddd;
        }
        .badge {
            display: inline-block;
            padding: 2px 5px;
            font-size: 8px;
            color: white;
            border-radius: 3px;
            margin-left: 5px;
        }
        .badge-success {
            background-color: #28a745;
        }
        .badge-warning {
            background-color: #ffc107;
            color: #212529;
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
        .text-center {
            text-align: center;
        }
        .text-muted {
            color: #6c757d;
        }
        /* Corregir problemas de layout */
        .row::after {
            content: "";
            display: table;
            clear: both;
        }
    </style>
</head>
<body>
    <div class="header">
        @if(isset($logoConfigPath) && $logoConfigPath && file_exists($logoConfigPath))
            <div class="logo-container">
                <img src="{{ $logoConfigPath }}" alt="Logo de REPRO" class="company-logo">
            </div>
        @endif
        <h1>FICHA DE EMPRESA</h1>
        <div class="date">Generado el {{ date('d/m/Y H:i:s') }}</div>
    </div>

    <!-- Información General -->
    <div class="section">
        <div class="section-title">INFORMACIÓN GENERAL</div>

        <!-- Nombre de empresa - Destacado -->
        <div class="info-block" style="margin-bottom: 12px;">
            <div class="info-label">Nombre de la Empresa:</div>
            <div style="font-size: 12px; font-weight: bold; margin-top: 2px;">{{ $empresa->nombre }}</div>
        </div>

        <!-- Información básica en dos columnas -->
        <div class="row" style="display: flex; flex-wrap: wrap;">
            <div style="width: 65%; float: left; padding-right: 10px;">
                <table cellpadding="0" cellspacing="0">
                    <tr>
                        <th style="width: 30%; height: 18px;">NIT</th>
                        <td style="width: 70%;">{{ $empresa->nit ?? 'No definido' }}</td>
                    </tr>
                    <tr>
                        <th style="height: 18px;">Teléfono</th>
                        <td>{{ $empresa->telefono ?? 'No definido' }}</td>
                    </tr>
                    <tr>
                        <th style="height: 18px;">Email</th>
                        <td>{{ $empresa->email ?? 'No definido' }}</td>
                    </tr>
                    <tr>
                        <th style="height: 18px;">Dirección</th>
                        <td>{{ $empresa->direccion ?? 'No definida' }}</td>
                    </tr>
                    <tr>
                        <th style="height: 18px;">Sitio Web</th>
                        <td>{{ $empresa->sitio_web ?? 'No definido' }}</td>
                    </tr>
                    <tr>
                        <th style="height: 18px;">Estado</th>
                        <td>
                            <span class="badge {{ $empresa->estado ? 'badge-success' : 'badge-danger' }}">
                                {{ $empresa->getEstadoTexto() }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th style="height: 18px;">Fecha de Registro</th>
                        <td>{{ $empresa->getCreatedAtFormateada() }}</td>
                    </tr>
                </table>
            </div>

            <!-- Logo de empresa -->
            <div style="width: 30%; float: right; text-align: center;">
                @if(isset($logoEmpresaPath) && $logoEmpresaPath && file_exists($logoEmpresaPath))
                    <img src="{{ $logoEmpresaPath }}" alt="Logo de la empresa" class="logo-empresa">
                @else
                    <div style="width: 100px; height: 100px; margin: 0 auto; border: 1px dashed #ccc; display: flex; align-items: center; justify-content: center;">
                        <span class="text-muted">Sin logo</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Descripción -->
    @if($empresa->descripcion)
    <div class="section">
        <div class="section-title">DESCRIPCIÓN</div>
        <p style="margin: 5px 0;">{{ $empresa->descripcion }}</p>
    </div>
    @endif

    <!-- Contacto Principal -->
    <div class="section">
        <div class="section-title">CONTACTO PRINCIPAL</div>
        @if($empresa->contacto_nombre || $empresa->contacto_cargo || $empresa->contacto_telefono || $empresa->contacto_email)
            <table cellpadding="0" cellspacing="0">
                <tr>
                    <th style="width: 30%; height: 18px;">Nombre</th>
                    <td style="width: 70%;">{{ $empresa->contacto_nombre ?? 'No definido' }}</td>
                </tr>
                <tr>
                    <th style="height: 18px;">Cargo</th>
                    <td>{{ $empresa->contacto_cargo ?? 'No definido' }}</td>
                </tr>
                <tr>
                    <th style="height: 18px;">Teléfono</th>
                    <td>{{ $empresa->contacto_telefono ?? 'No definido' }}</td>
                </tr>
                <tr>
                    <th style="height: 18px;">Email</th>
                    <td>{{ $empresa->contacto_email ?? 'No definido' }}</td>
                </tr>
            </table>
        @else
            <p class="text-muted" style="margin: 5px 0;">No se ha definido información de contacto principal.</p>
        @endif
    </div>

    <!-- Usuarios Asociados -->
    <div class="section">
        <div class="section-title">USUARIOS ASOCIADOS ({{ count($usuarios) }})</div>
        @if(count($usuarios) > 0)
            <table cellpadding="0" cellspacing="0">
                <thead>
                    <tr>
                        <th style="width: 40%; height: 18px;">Nombre</th>
                        <th style="width: 35%; height: 18px;">Email</th>
                        <th style="width: 25%; height: 18px;">Rol</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($usuarios as $usuario)
                        <tr>
                            <td>
                                {{ $usuario->name }}
                                @if($usuario->principal == 1)
                                    <span class="badge badge-warning">Principal</span>
                                @endif
                            </td>
                            <td>{{ $usuario->email }}</td>
                            <td>{{ $usuario->cargo ?? $usuario->getRoleName() }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="text-muted" style="margin: 5px 0;">No hay usuarios registrados para esta empresa.</p>
        @endif
    </div>

    <!-- Notas adicionales -->
    @if($empresa->notas)
    <div class="section">
        <div class="section-title">NOTAS ADICIONALES</div>
        <p style="margin: 5px 0;">{{ $empresa->notas }}</p>
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>
            REPRO - Sistema de Gestión de Evaluaciones de Polígrafo<br>
            Documento generado automáticamente
        </p>
    </div>
</body>
</html>
