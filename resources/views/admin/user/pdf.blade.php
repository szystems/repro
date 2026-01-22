<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ isset($titulo) ? $titulo : __('Usuarios') }}</title>
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

        .content {
            margin-top: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 3px;
            font-size: 9px;
        }
        thead {
            background-color: #000555;
            color: #ffb000;
        }
        tbody tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .section-title {
            background-color: #000555;
            color: #ffb000;
            padding: 3px;
            margin: 8px 0 5px;
            font-weight: bold;
            text-align: center;
            font-size: 11px;
            border-radius: 4px;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 8px;
            color: #000555;
            border-top: 2px solid #ffb000;
            padding-top: 10px;
        }
        .text-primary { color: #000555; }
        .text-success { color: #28a745; }
        .text-danger { color: #dc3545; }
        .text-info { color: #17a2b8; }
        .text-warning { color: #ffc107; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-bold { font-weight: bold; }
        .badge {
            display: inline-block;
            padding: 2px 4px;
            border-radius: 3px;
            font-size: 8px;
        }
        .badge-success { background-color: #28a745; color: white; }
        .badge-warning { background-color: #ffc107; color: #212529; }
        .badge-danger { background-color: #dc3545; color: white; }
        .badge-info { background-color: #17a2b8; color: white; }
        .badge-secondary { background-color: #6c757d; color: white; }
        .filters-info {
            clear: both;
            font-size: 8px;
            margin-top: 5px;
            margin-bottom: 5px;
            text-align: center;
            border: 1px solid #ddd;
            padding: 3px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }
        .summary-section {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 5px;
            margin-bottom: 10px;
            border-radius: 4px;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }
        .summary-table th, .summary-table td {
            padding: 2px 4px;
            font-size: 8px;
        }
        .summary-table tr.header-row {
            background-color: #000555;
            color: #ffb000;
            font-weight: bold;
            text-align: center;
        }
        .user-image {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            object-fit: cover;
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
                <h1>{{ isset($titulo) ? $titulo : __('Listado de Usuarios') }}</h1>
                <h2>Reporte del Sistema</h2>
            </div>
            <div class="repro-info-cell">
                <span>Fecha:</span> {{ now()->format('d/m/Y') }}
            </div>
        </div>
    </div>

    <div class="content">
        <!-- Filtros aplicados -->
        <div class="filters-info">
            <strong>Fecha Reporte:</strong> {{ now()->format('d/m/Y') }}
            @if(isset($queryUser) && $queryUser)
                | <strong>Búsqueda:</strong> {{ $queryUser }}
            @endif
            @if(isset($role_filter) && $role_filter !== null && $role_filter !== '')
                | <strong>Rol:</strong>
                {{-- Evaluados ya no son usuarios --}}
                @if($role_filter == '1') Empresa
                @elseif($role_filter == '2') Repro
                @elseif($role_filter == '3') Administrador
                @endif
            @endif
            @if(isset($empresa_filter) && $empresa_filter !== null && $empresa_filter !== '')
                | <strong>Empresa:</strong> {{ \App\Models\Empresa::find($empresa_filter)->nombre ?? 'No especificada' }}
            @endif
        </div>

        <!-- Resumen estadístico -->
        <div class="summary-section">
            <table class="summary-table">
                <tr>
                    <td width="20%" class="text-center">
                        <div class="text-primary text-bold">Total Usuarios</div>
                        <div>{{ count($usuarios) }}</div>
                    </td>
                    {{-- Evaluados ya no son usuarios del sistema --}}
                    <td width="20%" class="text-center">
                        <div class="text-success text-bold">Empresas</div>
                        <div>{{ $usuarios->where('role_as', 1)->count() }}</div>
                    </td>
                    <td width="16%" class="text-center">
                        <div class="text-info text-bold">Repro</div>
                        <div>{{ $usuarios->where('role_as', 2)->count() }}</div>
                    </td>
                    <td width="16%" class="text-center">
                        <div class="text-danger text-bold">Administradores</div>
                        <div>{{ $usuarios->where('role_as', 3)->count() }}</div>
                    </td>
                    <td width="16%" class="text-center">
                        <div class="text-warning text-bold">Usuarios Principales</div>
                        <div>{{ $usuarios->where('principal', 1)->count() }}</div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Tabla de usuarios -->
        <div class="section-title">LISTADO DE USUARIOS</div>
        <table>
            <thead>
                <tr>
                    <th width="5%">ID</th>
                    <th width="20%">Datos Personales</th>
                    <th width="10%">Rol</th>
                    <th width="20%">Empresa / Cargo</th>
                    <th width="20%">Contacto</th>
                    <th width="25%">Dirección</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($usuarios as $usuario)
                    @php
                        $fnacimiento = null;
                        $edad = 0;
                        if ($usuario->fecha_nacimiento != null) {
                            $fnacimiento = date("d-m-Y", strtotime($usuario->fecha_nacimiento));
                            $cumpleanos = new DateTime($usuario->fecha_nacimiento);
                            $hoy = new DateTime();
                            $annos = $hoy->diff($cumpleanos);
                            $edad = $annos->y;
                        }
                    @endphp
                    <tr>
                        <td class="text-center">{{ $usuario->id }}</td>
                        <td>
                            <div class="text-bold">{{ $usuario->name }}</div>
                            <div><small>{{ $fnacimiento }}</small>
                                @if ($edad > 0)
                                    <span class="badge badge-warning">{{ $edad }} años</span>
                                @endif
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="badge
                                @if($usuario->role_as == 1) badge-success
                                @elseif($usuario->role_as == 2) badge-info
                                @elseif($usuario->role_as == 3) badge-danger
                                @else badge-secondary
                                @endif">
                                {{-- Evaluados ya no son usuarios --}}
                                @if($usuario->role_as == 1) Empresa
                                @elseif($usuario->role_as == 2) Repro
                                @elseif($usuario->role_as == 3) Admin
                                @endif
                            </span>
                            @if($usuario->principal == 1)
                                <br><span class="badge badge-warning">Principal</span>
                            @endif
                        </td>
                        <td>
                            @if($usuario->role_as == 1 && isset($usuario->empresa))
                                <div class="text-bold">{{ $usuario->empresa->nombre }}</div>
                                @if($usuario->empresa->nit)
                                    <div><small>NIT: {{ $usuario->empresa->nit }}</small></div>
                                @endif
                            @endif

                            @if($usuario->cargo)
                                <div><small>Cargo: {{ $usuario->cargo }}</small></div>
                            @endif
                        </td>
                        <td>
                            <div>Email: <span class="text-primary">{{ $usuario->email }}</span></div>
                            @if($usuario->telefono)
                                <div>Tel: {{ $usuario->telefono }}</div>
                            @endif
                            @if($usuario->celular)
                                <div>Cel: {{ $usuario->celular }}</div>
                            @endif
                        </td>
                        <td>{{ $usuario->direccion }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p><strong>REPRO Guatemala</strong></p>
        <p>Documento generado el {{ now()->format('d/m/Y H:i') }}</p>
        <p style="color: #ffb000;">Este documento es confidencial y de uso exclusivo para fines administrativos.</p>
    </div>
</body>
</html>
