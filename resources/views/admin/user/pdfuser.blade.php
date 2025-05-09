<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ficha de Usuario: {{ $usuario->name }}</title>
    <style>
        @page {
            size: portrait;
            margin: 10px;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 5px;
        }

        .company-logo {
            max-height: 70px;
            max-width: 90%;
            margin: 5px auto;
            display: block;
            text-align: center;
        }

        .content {
            margin-top: 10px;
        }

        .logo-container {
            text-align: center;
            margin-bottom: 10px;
            padding-top: 5px;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
        }

        .header h1 {
            margin: 0;
            color: #2c3e50;
            font-size: 16px;
        }

        .header p {
            margin: 5px 0;
            font-size: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 5px;
            font-size: 10px;
        }

        thead {
            background-color: #007bff;
            color: white;
        }

        tbody tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .section-title {
            background-color: #007bff;
            color: white;
            padding: 5px;
            margin: 10px 0 5px;
            font-weight: bold;
            text-align: center;
            font-size: 12px;
        }

        .footer {
            margin-top: 10px;
            text-align: center;
            font-size: 8px;
            color: #6c757d;
        }

        .text-primary { color: #007bff; }
        .text-success { color: #28a745; }
        .text-danger { color: #dc3545; }
        .text-info { color: #17a2b8; }
        .text-warning { color: #ffc107; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-bold { font-weight: bold; }

        .badge {
            display: inline-block;
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 9px;
        }

        .badge-success { background-color: #28a745; color: white; }
        .badge-warning { background-color: #ffc107; color: #212529; }
        .badge-danger { background-color: #dc3545; color: white; }
        .badge-info { background-color: #17a2b8; color: white; }
        .badge-secondary { background-color: #6c757d; color: white; }

        .user-photo-container {
            text-align: center;
            margin-top: 20px;
            margin-bottom: 10px;
            page-break-inside: avoid;
        }

        .user-photo {
            max-width: 200px;
            max-height: 200px;
            border: 3px solid #ddd;
            border-radius: 10px;
        }

        .info-table td {
            font-size: 10px;
            padding: 7px;
        }

        .info-table th {
            font-size: 10px;
            background-color: #17a2b8;
            color: white;
            padding: 7px;
        }
    </style>
</head>
<body>
    <div class="content">
        <!-- Logo y encabezado -->
        <div class="logo-container">
            @if(isset($imagen) && $imagen && file_exists($imagen))
                <img src="{{ $imagen }}" alt="Logo de la empresa" class="company-logo">
            @endif
        </div>

        <!-- Cabecera con título -->
        <div class="header">
            <h1>FICHA DE USUARIO</h1>
            <p>{{ isset($config) ? $config->nombre_negocio : '' }} |
               {{ isset($config) ? $config->telefono : '' }}</p>
        </div>

        <!-- Información básica del usuario -->
        <div class="section-title">DATOS GENERALES</div>
        <table class="info-table">
            <tr>
                <th width="20%">ID Usuario</th>
                <td width="30%">{{ $usuario->id }}</td>
                <th width="20%">Tipo de Usuario</th>
                <td width="30%">
                    <span class="badge
                        @if($usuario->role_as == 0) badge-secondary
                        @elseif($usuario->role_as == 1) badge-success
                        @elseif($usuario->role_as == 2) badge-info
                        @elseif($usuario->role_as == 3) badge-danger
                        @endif">
                        {{ $usuario->getRoleName() }}
                    </span>
                    @if($usuario->principal == 1)
                        <span class="badge badge-warning">Usuario Principal</span>
                    @endif
                </td>
            </tr>
            <tr>
                <th>Nombre Completo</th>
                <td><strong>{{ $usuario->name }}</strong></td>
                <th>Fecha de Registro</th>
                <td>{{ date('d/m/Y', strtotime($usuario->created_at)) }}</td>
            </tr>
        </table>

        <!-- Sección específica por rol -->
        @if($usuario->role_as == 1 && isset($usuario->empresa))
        <div class="section-title bg-success">INFORMACIÓN DE EMPRESA</div>
        <table class="info-table">
            <tr>
                <th width="20%">Empresa</th>
                <td width="30%"><strong>{{ $usuario->empresa->nombre }}</strong></td>
                <th width="20%">NIT</th>
                <td width="30%">{{ $usuario->empresa->nit ?? 'No disponible' }}</td>
            </tr>
            @if($usuario->cargo)
            <tr>
                <th>Cargo</th>
                <td colspan="3">{{ $usuario->cargo }}</td>
            </tr>
            @endif
        </table>
        @endif

        @if($usuario->role_as == 2 && $usuario->cargo)
        <div class="section-title bg-info">INFORMACIÓN PROFESIONAL</div>
        <table class="info-table">
            <tr>
                <th width="20%">Cargo</th>
                <td width="80%"><strong>{{ $usuario->cargo }}</strong></td>
            </tr>
            @if(!empty($usuario->permisos))
            <tr>
                <th>Permisos</th>
                <td>
                    @php
                        $permisos = is_array($usuario->permisos) ? $usuario->permisos : json_decode($usuario->permisos);
                    @endphp
                    @if(is_array($permisos) || is_object($permisos))
                        @foreach($permisos as $permiso)
                            <span class="badge badge-secondary">{{ $permiso }}</span>
                        @endforeach
                    @endif
                </td>
            </tr>
            @endif
        </table>
        @endif

        <!-- Sección de Información Personal -->
        <div class="section-title">INFORMACIÓN PERSONAL</div>
        <table class="info-table">
            <tr>
                <th width="20%">Fecha de Nacimiento</th>
                <td width="30%">
                    @php
                        $fnacimiento = null;
                        $edad = 0;
                        if ($usuario->fecha_nacimiento != null) {
                            $fnacimiento = date("d/m/Y", strtotime($usuario->fecha_nacimiento));
                            $cumpleanos = new DateTime($usuario->fecha_nacimiento);
                            $hoy = new DateTime();
                            $annos = $hoy->diff($cumpleanos);
                            $edad = $annos->y;
                        }
                    @endphp
                    {{ $fnacimiento ?? 'No disponible' }}
                    @if ($edad > 0)
                        <span class="badge badge-warning">{{ $edad }} años</span>
                    @endif
                </td>
                <th width="20%">Estado</th>
                <td width="30%">
                    <span class="badge {{ $usuario->estado ? 'badge-success' : 'badge-danger' }}">
                        {{ $usuario->estado ? 'Activo' : 'Inactivo' }}
                    </span>
                </td>
            </tr>
            <tr>
                <th>Email</th>
                <td colspan="3">{{ $usuario->email }}</td>
            </tr>
        </table>

        <!-- Sección de Contacto -->
        <div class="section-title">INFORMACIÓN DE CONTACTO</div>
        <table class="info-table">
            <tr>
                <th width="20%">Teléfono</th>
                <td width="30%">{{ $usuario->telefono ?: 'No disponible' }}</td>
                <th width="20%">Celular</th>
                <td width="30%">{{ $usuario->celular ?: 'No disponible' }}</td>
            </tr>
            <tr>
                <th>Dirección</th>
                <td colspan="3">{{ $usuario->direccion ?: 'No disponible' }}</td>
            </tr>
        </table>

        <!-- Sección de acceso al sistema -->
        <div class="section-title">ACCESO AL SISTEMA</div>
        <table class="info-table">
            <tr>
                <th width="30%">Email de acceso</th>
                <td width="70%">{{ $usuario->email }}</td>
            </tr>
            <tr>
                <th>Permisos</th>
                <td>
                    <span class="badge
                        @if($usuario->role_as == 0) badge-secondary
                        @elseif($usuario->role_as == 1) badge-success
                        @elseif($usuario->role_as == 2) badge-info
                        @elseif($usuario->role_as == 3) badge-danger
                        @endif">
                        {{ $usuario->getRoleName() }}
                    </span>
                </td>
            </tr>
            <tr>
                <th>Estado Cuenta</th>
                <td>
                    <span class="badge {{ $usuario->estado ? 'badge-success' : 'badge-danger' }}">
                        {{ $usuario->estado ? 'Activa' : 'Desactivada' }}
                    </span>
                </td>
            </tr>
        </table>

        <!-- Imagen del usuario al final del documento -->
        <div class="user-photo-container">
            <div class="section-title">FOTOGRAFÍA DEL USUARIO</div>
            @php
                $hasUserImage = false;
                if ($usuario->fotografia && file_exists($pathuser . $usuario->fotografia)) {
                    $hasUserImage = true;
                }
            @endphp

            @if($hasUserImage)
                <img src="{{ $pathuser . $usuario->fotografia }}" class="user-photo" alt="Fotografía del usuario">
            @elseif(file_exists($defaultImagePath))
                <img src="{{ $defaultImagePath }}" class="user-photo" alt="Imagen por defecto">
            @else
                <div style="border: 1px dashed #ccc; padding: 15px; width: 150px; height: 150px; margin: 0 auto; display: flex; align-items: center; justify-content: center;">
                    <p style="margin: 0; color: #777;">Imagen no disponible</p>
                </div>
            @endif
        </div>
    </div>

    <div class="footer">
        <p>Documento generado el {{ now()->format('d/m/Y H:i:s') }} | {{ isset($config) ? $config->nombre_negocio : 'Sistema de Gestión' }}</p>
    </div>
</body>
</html>
