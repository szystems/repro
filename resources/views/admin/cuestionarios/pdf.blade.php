<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cuestionario {{ ucfirst($cuestionario->tipo_formulario ?? 'Socioeconómico') }} - {{ $cuestionario->evaluadoOrden->nombre }}</title>
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

        /* Cabecera estilo REPRO con fondo azul */
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
            width: 180px;
            font-size: 9px;
            color: #ffb000;
        }

        .repro-info-cell strong {
            color: #ffb000;
        }

        /* Info del evaluado */
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

        .badge-success { 
            background-color: #28a745; 
            color: white;
        }
        .badge-warning { 
            background-color: #000555; 
            color: #ffb000;
        }
        .badge-danger { 
            background-color: #dc3545; 
            color: white;
        }
        .badge-secondary { 
            background-color: #6c757d; 
            color: white;
        }

        /* Secciones — permiten flujo continuo (evitar huecos enormes en DomPDF) */
        .seccion {
            margin-bottom: 15px;
            page-break-inside: auto;
        }

        .seccion-titulo {
            background: linear-gradient(135deg, #000555 0%, #1a1a6b 100%);
            color: #ffb000;
            padding: 8px 12px;
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 8px;
            border-radius: 4px 4px 0 0;
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
            width: 35%;
            border-left: 3px solid #ffb000;
        }

        .datos-table td {
            background-color: #fff;
        }

        .datos-table td.vacio {
            color: #999;
            font-style: italic;
        }

        /* Firma digital */
        .firma-container {
            text-align: center;
            margin: 20px 0;
            page-break-inside: avoid;
        }

        .firma-imagen {
            max-width: 250px;
            max-height: 120px;
            border: 2px solid #000555;
            padding: 8px;
            background: white;
            border-radius: 4px;
        }

        .firma-texto {
            margin-top: 8px;
            font-size: 9px;
            color: #000555;
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

        .footer strong {
            color: #000555;
        }

        .page-break {
            page-break-before: always;
        }
    </style>
    @include('shared.pdf.flujo-pagina')
</head>
<body>
    {{-- Cabecera estilo REPRO con fondo azul --}}
    <div class="repro-header">
        <div class="repro-header-content">
            <div class="repro-logo-cell">
                <div class="repro-logo-container">
                    <img src="{{ public_path('img/logos/logoreproxelahorizontal.png') }}" alt="REPRO" class="repro-logo">
                </div>
            </div>
            <div class="repro-title-cell">
                <h1>Cuestionario {{ ucfirst($cuestionario->tipo_formulario ?? 'Socioeconómico') }}</h1>
                <h2>Evaluación Socioeconómica</h2>
            </div>
            <div class="repro-info-cell">
                <span style="color: #ffcc33;">ID:</span> <span style="color: #ffb000;">{{ $cuestionario->id }}</span><br>
                <span style="color: #ffcc33;">Fecha:</span> <span style="color: #ffb000;">{{ now()->format('d/m/Y') }}</span>
            </div>
        </div>
    </div>

    {{-- Información General del Evaluado --}}
    <div class="info-general">
        <table class="info-table">
            <tr>
                <td class="info-label">Evaluado:</td>
                <td class="info-value" colspan="3">
                    <strong>{{ $cuestionario->evaluadoOrden->nombre }} {{ $cuestionario->evaluadoOrden->apellidos }}</strong>
                </td>
            </tr>
            <tr>
                <td class="info-label">DPI:</td>
                <td class="info-value">{{ $cuestionario->evaluadoOrden->dpi }}</td>
                <td class="info-label">Empresa:</td>
                <td class="info-value">{{ $cuestionario->evaluadoOrden->orden->empresa->nombre }}</td>
            </tr>
            <tr>
                <td class="info-label">Puesto a Evaluar:</td>
                <td class="info-value">{{ $cuestionario->evaluadoOrden->puesto_evaluar ?? 'No especificado' }}</td>
                <td class="info-label">Tipo de Servicio:</td>
                <td class="info-value">{{ ucfirst($cuestionario->evaluadoOrden->tipo_servicio ?? 'N/A') }}</td>
            </tr>
            <tr>
                <td class="info-label">Estado del cuestionario:</td>
                <td class="info-value">
                    <span class="badge {{ $cuestionario->completado ? 'badge-success' : ($cuestionario->seccion_actual > 1 ? 'badge-warning' : 'badge-secondary') }}">
                        {{ $cuestionario->completado ? 'Completado' : ($cuestionario->seccion_actual > 1 ? 'En Progreso' : 'Pendiente') }}
                    </span>
                </td>
                <td class="info-label">Progreso:</td>
                <td class="info-value">{{ $cuestionario->progreso_porcentaje ?? 0 }}%</td>
            </tr>
            <tr>
                <td class="info-label">Fecha Creación:</td>
                <td class="info-value">{{ $cuestionario->created_at->format('d/m/Y H:i') }}</td>
                <td class="info-label">Fecha Completado:</td>
                <td class="info-value">{{ $cuestionario->completado_at ? $cuestionario->completado_at->format('d/m/Y H:i') : 'Pendiente' }}</td>
            </tr>
        </table>
    </div>

    {{-- Contenido de las Secciones --}}
    @php
        $seccionesConfig = $cuestionario->getSeccionesConfig();
    @endphp

    @foreach($seccionesConfig as $numeroSeccion => $nombreSeccion)
        <div class="seccion">
            <div class="seccion-titulo">
                Sección {{ $numeroSeccion }}: {{ $nombreSeccion }}
            </div>

            @include('shared.cuestionario.pdf-seccion-contenido-admin', [
                'cuestionario' => $cuestionario,
                'numeroSeccion' => $numeroSeccion,
                'soloEmpresa' => false,
            ])
        </div>
    @endforeach

    {{-- Documentos Verificados (8D.2) --}}
    @if($cuestionario->evaluadoOrden->documentos->count() > 0)
        <div class="seccion">
            <div class="seccion-titulo">
                Documentos del Evaluado
            </div>
            <table class="datos-table">
                <thead>
                    <tr>
                        <th style="background-color: #000555; color: #ffb000; border-left: none; text-align: center; width: 5%;">#</th>
                        <th style="background-color: #000555; color: #ffb000; border-left: none; text-align: center; width: 35%;">Tipo de Documento</th>
                        <th style="background-color: #000555; color: #ffb000; border-left: none; text-align: center; width: 30%;">Archivo</th>
                        <th style="background-color: #000555; color: #ffb000; border-left: none; text-align: center; width: 15%;">Estado de Verificación</th>
                        <th style="background-color: #000555; color: #ffb000; border-left: none; text-align: center; width: 15%;">Subido por</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cuestionario->evaluadoOrden->documentos as $index => $doc)
                        <tr>
                            <td style="text-align: center;">{{ $index + 1 }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $doc->tipo_documento)) }}</td>
                            <td>{{ $doc->nombre_original }}</td>
                            <td style="text-align: center;">
                                <span class="badge {{ $doc->estado_verificacion == 'aprobado' ? 'badge-success' : ($doc->estado_verificacion == 'rechazado' ? 'badge-danger' : 'badge-warning') }}">
                                    {{ ucfirst($doc->estado_verificacion ?? 'Pendiente') }}
                                </span>
                            </td>
                            <td style="text-align: center;">{{ ucfirst($doc->subido_por_tipo ?? 'N/A') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Autorización y Términos (8D.1) --}}
    @if($cuestionario->acepta_terminos)
        <div class="seccion">
            <div class="seccion-titulo">
                Autorización y Términos
            </div>
            <div style="padding: 10px; font-size: 9px; line-height: 1.6;">
                @if($cuestionario->texto_autorizacion_html)
                    {!! $cuestionario->texto_autorizacion_html !!}
                @else
                    @include('cuestionario.partials.autorizacion-legal', [
                        'evaluado' => $cuestionario->evaluadoOrden,
                        'contenidoAutorizacion' => \App\Support\AutorizacionesLegales::renderHtml($cuestionario->evaluadoOrden),
                    ])
                @endif
            </div>

            {{-- Firma Digital del Evaluado --}}
            @if($cuestionario->firma_digital)
                <div class="firma-container" style="margin-top: 15px;">
                    <img src="{{ $cuestionario->firma_digital }}" alt="Firma Digital" class="firma-imagen">
                    <div class="firma-texto">
                        <strong>{{ $cuestionario->evaluadoOrden->nombre }} {{ $cuestionario->evaluadoOrden->apellidos }}</strong><br>
                        DPI: {{ $cuestionario->evaluadoOrden->dpi }}<br>
                        Firmado digitalmente el {{ $cuestionario->completado_at ? $cuestionario->completado_at->format('d/m/Y \a \l\a\s H:i:s') : 'N/A' }}
                    </div>
                </div>
            @endif

            {{-- Firma del Responsable (8D.4) --}}
            @if($cuestionario->evaluadoOrden->responsable)
                <div style="margin-top: 30px; text-align: center;">
                    <div style="display: inline-block; width: 250px; border-top: 2px solid #000555; padding-top: 8px;">
                        <div style="font-size: 10px; font-weight: bold; color: #000555;">
                            {{ $cuestionario->evaluadoOrden->responsable->name }}
                        </div>
                        @if($cuestionario->evaluadoOrden->responsable->cargo)
                            <div style="font-size: 9px; color: #666;">
                                {{ $cuestionario->evaluadoOrden->responsable->cargo }}
                            </div>
                        @endif
                        <div style="font-size: 8px; color: #999; margin-top: 2px;">
                            Responsable del Proceso — REPRO Guatemala
                        </div>
                    </div>
                </div>
            @endif
        </div>

        @if($cuestionario->acepta_infornet && $cuestionario->texto_infornet_html)
            <div class="seccion">
                <div class="seccion-titulo">Autorización Infornet</div>
                <div style="padding: 10px; font-size: 9px; line-height: 1.6;">
                    {!! $cuestionario->texto_infornet_html !!}
                </div>
                @if($cuestionario->acepta_infornet_at)
                    <p style="font-size: 8px; color: #666; padding: 0 10px;">
                        Aceptada el {{ $cuestionario->acepta_infornet_at->format('d/m/Y H:i:s') }} (misma firma de autorización principal).
                    </p>
                @endif
            </div>
        @endif
    @else
        {{-- Si no aceptó términos, al menos mostrar la firma si existe --}}
        @if($cuestionario->firma_digital)
            <div class="seccion">
                <div class="seccion-titulo">
                    Firma Digital
                </div>
                <div class="firma-container">
                    <img src="{{ $cuestionario->firma_digital }}" alt="Firma Digital" class="firma-imagen">
                    <div class="firma-texto">
                        Firmado digitalmente el {{ $cuestionario->completado_at ? $cuestionario->completado_at->format('d/m/Y \a \l\a\s H:i:s') : 'N/A' }}
                    </div>
                </div>
            </div>
        @endif
    @endif

    {{-- Observaciones de REPRO --}}
    @if($cuestionario->observaciones_repro)
        <div class="seccion">
            <div class="observaciones-box">
                <div class="observaciones-titulo">
                    Observaciones Administrativas (REPRO)
                </div>
                <div>
                    {!! nl2br(e($cuestionario->observaciones_repro)) !!}
                </div>
            </div>
        </div>
    @endif

    {{-- Pie de página estilo REPRO --}}
    <div class="footer">
        <p><strong>REPRO Guatemala</strong></p>
        <p>Documento generado el {{ now()->format('d/m/Y H:i') }} | Cuestionario ID: {{ $cuestionario->id }}</p>
        <p style="color: #ffb000;">Este documento es confidencial y de uso exclusivo para fines de evaluación laboral.</p>
    </div>
</body>
</html>
