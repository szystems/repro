<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Orden {{ $orden->codigo_orden }}</title>
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

        /* Info de la orden */
        .info-general {
            background: linear-gradient(135deg, #ffb000 0%, #ffcc33 100%);
            border: 2px solid #000555;
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 15px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 4px 8px;
            font-size: 9px;
            vertical-align: top;
        }

        .info-label {
            font-weight: bold;
            color: #000555;
            width: 120px;
        }

        .info-value {
            color: #1a1a6b;
        }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .badge-success { background-color: #28a745; color: white; }
        .badge-warning { background-color: #ffc107; color: #212529; }
        .badge-danger { background-color: #dc3545; color: white; }
        .badge-info { background-color: #17a2b8; color: white; }
        .badge-primary { background-color: #000555; color: #ffb000; }
        .badge-secondary { background-color: #6c757d; color: white; }

        /* Secciones */
        .seccion {
            margin-bottom: 15px;
            page-break-inside: avoid;
        }

        .seccion-titulo {
            background-color: #000555;
            color: #ffb000;
            padding: 8px 12px;
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 8px;
            border-radius: 4px;
            letter-spacing: 0.5px;
        }

        .datos-table {
            width: 100%;
            border-collapse: collapse;
        }

        .datos-table th,
        .datos-table td {
            border: 1px solid #ddd;
            padding: 5px 8px;
            font-size: 9px;
            text-align: left;
            vertical-align: top;
        }

        .datos-table th {
            background-color: #f0f4ff;
            font-weight: bold;
            color: #000555;
            border-left: 3px solid #ffb000;
        }

        .datos-table td {
            background-color: #fff;
        }

        .datos-table thead th {
            background-color: #000555;
            color: #ffb000;
            border-left: none;
            text-align: center;
        }

        .datos-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .text-center {
            text-align: center;
        }

        .text-muted {
            color: #6c757d;
            font-style: italic;
        }

        /* Observaciones */
        .observaciones-box {
            background: linear-gradient(135deg, #fff3cd 0%, #ffe69c 100%);
            border: 2px solid #ffb000;
            border-radius: 6px;
            padding: 10px;
            margin-top: 10px;
        }

        .observaciones-titulo {
            font-weight: bold;
            color: #000555;
            margin-bottom: 5px;
            font-size: 10px;
        }

        /* Footer */
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 8px;
            color: #000555;
            border-top: 2px solid #ffb000;
            padding-top: 10px;
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
                <h1>Orden de Servicio</h1>
                <h2>{{ $orden->codigo_orden }}</h2>
            </div>
            <div class="repro-info-cell">
                <span>Fecha:</span> {{ now()->format('d/m/Y') }}
            </div>
        </div>
    </div>

    {{-- Información General de la Orden --}}
    <div class="info-general">
        <table class="info-table">
            <tr>
                <td class="info-label">Código:</td>
                <td class="info-value"><strong>{{ $orden->codigo_orden }}</strong></td>
                <td class="info-label">Estado:</td>
                <td class="info-value">
                    <span class="badge 
                        @if($orden->estado == 'solicitud') badge-secondary
                        @elseif($orden->estado == 'en_proceso') badge-primary
                        @elseif($orden->estado == 'entregado') badge-success
                        @elseif($orden->estado == 'cancelado') badge-danger
                        @else badge-info
                        @endif">
                        {{ $estados[$orden->estado] ?? ucfirst($orden->estado) }}
                    </span>
                </td>
            </tr>
            <tr>
                <td class="info-label">Empresa:</td>
                <td class="info-value"><strong>{{ $orden->empresa->nombre ?? 'N/A' }}</strong></td>
                <td class="info-label">Creado por:</td>
                <td class="info-value">{{ $orden->creador->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="info-label">Fecha Solicitud:</td>
                <td class="info-value">{{ $orden->fecha_solicitud ? \Carbon\Carbon::parse($orden->fecha_solicitud)->format('d/m/Y') : 'N/A' }}</td>
                <td class="info-label">Fecha Creación:</td>
                <td class="info-value">{{ $orden->created_at ? $orden->created_at->format('d/m/Y H:i') : 'N/A' }}</td>
            </tr>
            <tr>
                <td class="info-label">Prioridad:</td>
                <td class="info-value">
                    <span class="badge 
                        @if($orden->prioridad == 'urgente') badge-danger
                        @elseif($orden->prioridad == 'alta') badge-warning
                        @else badge-secondary
                        @endif">
                        {{ ucfirst($orden->prioridad ?? 'Normal') }}
                    </span>
                </td>
                <td class="info-label">Total Evaluados:</td>
                <td class="info-value"><strong>{{ $orden->evaluados->count() }}</strong></td>
            </tr>
        </table>
    </div>

    {{-- Tipos de Servicio y Formulario --}}
    <div class="seccion">
        <div class="seccion-titulo">Resumen de Servicios</div>
        <table class="datos-table">
            <tr>
                <th style="width: 25%;">Tipos de Servicio</th>
                <td>
                    @php
                        $tiposUnicos = $orden->evaluados->pluck('tipo_servicio')->unique();
                    @endphp
                    @foreach($tiposUnicos as $tipo)
                        <span class="badge 
                            @if($tipo == 'poligrafo') badge-primary
                            @elseif($tipo == 'vsa') badge-info
                            @else badge-warning
                            @endif">
                            @if($tipo == 'poligrafo') Polígrafo
                            @elseif($tipo == 'vsa') VSA
                            @else Socioeconómico
                            @endif
                        </span>
                    @endforeach
                    @if($tiposUnicos->isEmpty())
                        <span class="text-muted">Sin evaluados</span>
                    @endif
                </td>
            </tr>
            <tr>
                <th>Tipos de Formulario</th>
                <td>
                    @php
                        $formulariosUnicos = $orden->evaluados->pluck('tipo_formulario')->unique();
                    @endphp
                    @foreach($formulariosUnicos as $formulario)
                        <span class="badge badge-secondary">
                            @if($formulario == 'preempleo') Pre-empleo
                            @elseif($formulario == 'periodica') Periódica
                            @else Específica
                            @endif
                        </span>
                    @endforeach
                    @if($formulariosUnicos->isEmpty())
                        <span class="text-muted">Sin evaluados</span>
                    @endif
                </td>
            </tr>
            <tr>
                <th>Polígrafos Asignados</th>
                <td>
                    @php
                        $poligrafistas = $orden->evaluados->whereNotNull('poligrafista_id')->pluck('poligrafista.name')->unique();
                    @endphp
                    @if($poligrafistas->isNotEmpty())
                        @foreach($poligrafistas as $poligrafista)
                            <span class="badge badge-info">{{ $poligrafista }}</span>
                        @endforeach
                    @else
                        <span class="text-muted">Sin asignar</span>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    {{-- Instrucciones Generales --}}
    @if($orden->instrucciones_generales)
    <div class="seccion">
        <div class="observaciones-box">
            <div class="observaciones-titulo">
                <i class="bi bi-info-circle"></i> Instrucciones Generales
            </div>
            <div>{{ $orden->instrucciones_generales }}</div>
        </div>
    </div>
    @endif

    {{-- Observaciones Internas --}}
    @if($orden->observaciones_internas)
    <div class="seccion">
        <div class="observaciones-box">
            <div class="observaciones-titulo">
                Observaciones Internas
            </div>
            <div>{{ $orden->observaciones_internas }}</div>
        </div>
    </div>
    @endif

    {{-- Lista de Evaluados --}}
    @if($orden->evaluados->count() > 0)
    <div class="seccion">
        <div class="seccion-titulo">Evaluados Asignados ({{ $orden->evaluados->count() }})</div>
        <table class="datos-table">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 22%;">Nombre</th>
                    <th style="width: 13%;">DPI</th>
                    <th style="width: 13%;">Servicio</th>
                    <th style="width: 13%;">Programación</th>
                    <th style="width: 12%;">Responsable</th>
                    <th style="width: 12%;">Contacto</th>
                    <th style="width: 10%;">Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orden->evaluados as $index => $evaluado)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $evaluado->nombre }}</strong>
                        @if($evaluado->apellidos)
                            <br><small>{{ $evaluado->apellidos }}</small>
                        @endif
                        @if($evaluado->puesto_evaluar)
                            <br><small style="color:#666;">{{ $evaluado->puesto_evaluar }}</small>
                        @endif
                        @if($evaluado->sede_region_empresa)
                            <br><small style="color:#888;">Sede/Región Empresa: {{ $evaluado->sede_region_empresa }}</small>
                        @endif
                        @if($evaluado->sede)
                            <br><small style="color:#888;">Sede REPRO: {{ $evaluado->sede->nombre }}</small>
                        @endif
                    </td>
                    <td>{{ $evaluado->dpi }}</td>
                    <td>
                        <span class="badge 
                            @if($evaluado->tipo_servicio == 'poligrafo') badge-primary
                            @elseif($evaluado->tipo_servicio == 'vsa') badge-info
                            @else badge-warning
                            @endif">
                            @if($evaluado->tipo_servicio == 'poligrafo') Polígrafo
                            @elseif($evaluado->tipo_servicio == 'vsa') VSA
                            @else Socioec.
                            @endif
                        </span>
                        <br>
                        <small>
                            @if($evaluado->tipo_formulario == 'preempleo') Pre-empleo
                            @elseif($evaluado->tipo_formulario == 'periodica') Periódica
                            @else Específica
                            @endif
                        </small>
                    </td>
                    <td>
                        @if($evaluado->fecha_programada)
                            {{ \Carbon\Carbon::parse($evaluado->fecha_programada)->format('d/m/Y') }}
                        @else
                            <span class="text-muted">Pendiente</span>
                        @endif
                        @if($evaluado->poligrafista)
                            <br><small>{{ $evaluado->poligrafista->name }}</small>
                        @endif
                    </td>
                    <td>
                        @if($evaluado->responsable)
                            {{ $evaluado->responsable->name }}
                            @if($evaluado->responsable->cargo)
                                <br><small>{{ $evaluado->responsable->cargo }}</small>
                            @endif
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        @if($evaluado->email)
                            {{ $evaluado->email }}
                        @endif
                        @if($evaluado->telefono)
                            <br>{{ $evaluado->telefono }}
                        @endif
                    </td>
                    <td class="text-center">
                        <span class="badge 
                            @if($evaluado->estado_evaluacion == 'completado') badge-success
                            @elseif($evaluado->estado_evaluacion == 'en_proceso') badge-primary
                            @elseif($evaluado->estado_evaluacion == 'programado') badge-info
                            @else badge-warning
                            @endif">
                            {{ ucfirst($evaluado->estado_evaluacion ?? 'Pendiente') }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="seccion">
        <div class="seccion-titulo">Evaluados</div>
        <p class="text-muted text-center">No hay evaluados asignados a esta orden.</p>
    </div>
    @endif

    {{-- Pie de página --}}
    <div class="footer">
        <p><strong>REPRO Guatemala</strong></p>
        <p>Documento generado el {{ now()->format('d/m/Y H:i') }} | Orden: {{ $orden->codigo_orden }}</p>
        <p style="color: #ffb000;">Este documento es confidencial y de uso exclusivo para fines administrativos.</p>
    </div>
</body>
</html>
