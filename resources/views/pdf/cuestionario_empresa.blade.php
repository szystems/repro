
@php
    // Se asume que $evaluado es EvaluadoOrden y tiene relación con Cuestionario
    $cuestionario = $evaluado->cuestionario;
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cuestionario {{ ucfirst($evaluado->tipo_formulario ?? 'Socioeconómico') }} - {{ $evaluado->nombre }}</title>
    <style>
        @page {
            size: portrait;
            margin: 12mm 15mm 15mm 15mm;
        }
        body { font-family: Arial, sans-serif; font-size: 10px; margin: 0; padding: 0; color: #333; }
        .repro-header { background-color: #000555; color: white; padding: 12px 20px; margin-bottom: 15px; border-radius: 6px; }
        .repro-header-content { display: table; width: 100%; }
        .repro-logo-cell { display: table-cell; vertical-align: middle; width: 180px; }
        .repro-logo-container { background-color: #f8f9fa; border: 1px solid #000555; border-radius: 6px; padding: 8px 12px; display: inline-block; }
        .repro-logo { max-height: 40px; max-width: 150px; display: block; }
        .repro-title-cell { display: table-cell; vertical-align: middle; text-align: center; }
        .repro-header h1 { margin: 0; font-size: 16px; font-weight: bold; color: #ffb000; text-transform: uppercase; letter-spacing: 1px; }
        .repro-header h2 { margin: 4px 0 0 0; font-size: 11px; font-weight: normal; color: #ffcc33; }
        .repro-info-cell { display: table-cell; vertical-align: middle; text-align: right; width: 180px; font-size: 9px; color: #ffb000; }
        .repro-info-cell strong { color: #ffb000; }
        .info-general { background: linear-gradient(135deg, #ffb000 0%, #ffcc33 100%); border: 2px solid #000555; border-radius: 6px; padding: 12px; margin-bottom: 15px; }
        .info-table { width: 100%; border-collapse: collapse; }
        .info-table td { padding: 4px 8px; font-size: 9px; vertical-align: top; }
        .info-label { font-weight: bold; color: #000555; width: 120px; }
        .info-value { color: #1a1a6b; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 8px; font-weight: bold; text-transform: uppercase; }
        .badge-success { background-color: #28a745; color: white; }
        .badge-warning { background-color: #000555; color: #ffb000; }
        .badge-danger { background-color: #dc3545; color: white; }
        .badge-secondary { background-color: #6c757d; color: white; }
        .seccion { margin-bottom: 15px; page-break-inside: auto; }
        .seccion-titulo { background: linear-gradient(135deg, #000555 0%, #1a1a6b 100%); color: #ffb000; padding: 8px 12px; font-weight: bold; font-size: 11px; margin-bottom: 8px; border-radius: 4px 4px 0 0; letter-spacing: 0.5px; }
        .datos-table { width: 100%; border-collapse: collapse; }
        .datos-table th, .datos-table td { border: 1px solid #ddd; padding: 5px 8px; font-size: 9px; text-align: left; vertical-align: top; }
        .datos-table th { background-color: #f0f4ff; font-weight: bold; color: #000555; width: 35%; border-left: 3px solid #ffb000; }
        .datos-table td { background-color: #fff; }
        .datos-table td.vacio { color: #999; font-style: italic; }
        .firma-container { text-align: center; margin: 20px 0; page-break-inside: avoid; }
        .firma-imagen { max-width: 250px; max-height: 120px; border: 2px solid #000555; padding: 8px; background: white; border-radius: 4px; }
        .firma-texto { margin-top: 8px; font-size: 9px; color: #000555; }
        .observaciones-box { background: linear-gradient(135deg, #fff3cd 0%, #ffe69c 100%); border: 2px solid #ffb000; border-radius: 6px; padding: 10px; margin-top: 10px; }
        .observaciones-titulo { font-weight: bold; color: #000555; margin-bottom: 5px; font-size: 10px; }
        .footer { margin-top: 20px; text-align: center; font-size: 8px; color: #000555; border-top: 2px solid #ffb000; padding-top: 10px; }
        .footer strong { color: #000555; }
        .page-break { page-break-before: always; }
        .subseccion-titulo {
            background-color: #f0f4ff;
            border-left: 4px solid #ffb000;
            color: #000555;
            font-weight: bold;
            font-size: 10px;
            margin: 10px 0 6px 0;
            padding: 6px 10px;
        }
    </style>
    @include('shared.pdf.flujo-pagina')
</head>
<body>
    <div class="repro-header">
        <div class="repro-header-content">
            <div class="repro-logo-cell">
                <div class="repro-logo-container">
                    <img src="{{ public_path('img/logos/logoreproxelahorizontal.png') }}" alt="REPRO" class="repro-logo">
                </div>
            </div>
            <div class="repro-title-cell">
                <h1>Cuestionario {{ ucfirst($evaluado->tipo_formulario ?? 'Socioeconómico') }}</h1>
                <h2>Evaluación {{ ucfirst($evaluado->tipo_servicio ?? 'N/A') }}</h2>
            </div>
            <div class="repro-info-cell">
                <span style="color: #ffcc33;">ID:</span> <span style="color: #ffb000;">{{ $cuestionario->id ?? $evaluado->id }}</span><br>
                <span style="color: #ffcc33;">Fecha:</span> <span style="color: #ffb000;">{{ now()->format('d/m/Y') }}</span>
            </div>
        </div>
    </div>

    <div class="info-general">
        <table class="info-table">
            <tr>
                <td class="info-label">Evaluado:</td>
                <td class="info-value" colspan="3">
                    <strong>{{ $evaluado->nombre }} {{ $evaluado->apellidos }}</strong>
                </td>
            </tr>
            <tr>
                <td class="info-label">DPI:</td>
                <td class="info-value">{{ $evaluado->dpi }}</td>
                <td class="info-label">Empresa:</td>
                <td class="info-value">{{ $evaluado->orden->empresa->nombre ?? '' }}</td>
            </tr>
            <tr>
                <td class="info-label">Puesto a Evaluar:</td>
                <td class="info-value">{{ $evaluado->puesto_evaluar ?? 'No especificado' }}</td>
                <td class="info-label">Tipo de Servicio:</td>
                <td class="info-value">{{ ucfirst($evaluado->tipo_servicio ?? 'N/A') }}</td>
            </tr>
            <tr>
                <td class="info-label">Progreso del cuestionario:</td>
                <td class="info-value">
                    <span class="badge {{ $evaluado->cuestionario_completado ? 'badge-success' : 'badge-secondary' }}">
                        {{ $evaluado->cuestionario_completado ? 'Completado' : 'Pendiente' }}
                    </span>
                </td>
                <td class="info-label">Orden:</td>
                <td class="info-value">{{ $evaluado->orden->codigo_orden ?? '#' . $evaluado->orden_id }}</td>
            </tr>
            <tr>
                <td class="info-label">Fecha Creación:</td>
                <td class="info-value">{{ $evaluado->created_at ? \Carbon\Carbon::parse($evaluado->created_at)->format('d/m/Y H:i') : '' }}</td>
                <td class="info-label">Fecha Completado:</td>
                <td class="info-value">{{ $evaluado->cuestionario_completado_at ? \Carbon\Carbon::parse($evaluado->cuestionario_completado_at)->format('d/m/Y H:i') : 'Pendiente' }}</td>
            </tr>
        </table>
    </div>

    @if($cuestionario && method_exists($cuestionario, 'getSeccionesConfig'))
        @include('shared.cuestionario.pdf-secciones-empresa', ['evaluado' => $evaluado])
    @endif

    {{-- Documentos Verificados --}}
    @if($evaluado->documentos->count() > 0)
        <div class="seccion">
            <div class="seccion-titulo">Documentos del Evaluado</div>
            <table class="datos-table">
                <thead>
                    <tr>
                        <th style="background-color: #000555; color: #ffb000; border-left: none; text-align: center; width: 5%;">#</th>
                        <th style="background-color: #000555; color: #ffb000; border-left: none; text-align: center; width: 40%;">Tipo de Documento</th>
                        <th style="background-color: #000555; color: #ffb000; border-left: none; text-align: center; width: 35%;">Archivo</th>
                        <th style="background-color: #000555; color: #ffb000; border-left: none; text-align: center; width: 20%;">Estado de Verificación</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($evaluado->documentos as $index => $doc)
                        <tr>
                            <td style="text-align: center;">{{ $index + 1 }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $doc->tipo_documento)) }}</td>
                            <td>{{ $doc->nombre_original }}</td>
                            <td style="text-align: center;">
                                <span class="badge {{ $doc->estado_verificacion == 'aprobado' ? 'badge-success' : ($doc->estado_verificacion == 'rechazado' ? 'badge-danger' : 'badge-warning') }}">
                                    {{ ucfirst($doc->estado_verificacion ?? 'Pendiente') }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Autorización y Términos --}}
    @if($cuestionario && $cuestionario->acepta_terminos)
        <div class="seccion">
            <div class="seccion-titulo">Autorización y Términos</div>
            <div style="padding: 10px; font-size: 9px; line-height: 1.6;">
                <h3 style="text-align: center; font-size: 11px; color: #000555; margin-bottom: 10px;">AUTORIZACIÓN PARA EVALUACIÓN</h3>
                <p>Yo, <strong>{{ $evaluado->nombre }} {{ $evaluado->apellidos }}</strong>, identificado(a) con DPI número <strong>{{ $evaluado->dpi }}</strong>, por medio de la presente autorizo libre y voluntariamente a <strong>REPRO Guatemala</strong> para que realice la siguiente evaluación.</p>
                <p>Declaro que participo de manera voluntaria, he sido informado(a) sobre el procedimiento, y autorizo la recopilación y procesamiento de mis datos personales exclusivamente para los fines de esta evaluación.</p>
            </div>
            @if($cuestionario->firma_digital)
                <div class="firma-container">
                    <img src="{{ $cuestionario->firma_digital }}" alt="Firma Digital" class="firma-imagen">
                    <div class="firma-texto">
                        <strong>{{ $evaluado->nombre }} {{ $evaluado->apellidos }}</strong><br>
                        Firmado digitalmente el {{ $cuestionario->completado_at ? $cuestionario->completado_at->format('d/m/Y \a \l\a\s H:i:s') : 'N/A' }}
                    </div>
                </div>
            @endif
            @if($evaluado->responsable)
                <div style="margin-top: 30px; text-align: center;">
                    <div style="display: inline-block; width: 250px; border-top: 2px solid #000555; padding-top: 8px;">
                        <div style="font-size: 10px; font-weight: bold; color: #000555;">{{ $evaluado->responsable->name }}</div>
                        @if($evaluado->responsable->cargo)
                            <div style="font-size: 9px; color: #666;">{{ $evaluado->responsable->cargo }}</div>
                        @endif
                        <div style="font-size: 8px; color: #999; margin-top: 2px;">Responsable del Proceso — REPRO Guatemala</div>
                    </div>
                </div>
            @endif
        </div>
    @endif

    <div class="footer">
        <p><strong>REPRO Guatemala</strong></p>
        <p>Documento generado el {{ now()->format('d/m/Y H:i') }} | Cuestionario ID: {{ $cuestionario->id ?? $evaluado->id }}</p>
        <p style="color: #ffb000;">Este documento es confidencial y de uso exclusivo para fines de evaluación laboral.</p>
    </div>
</body>
</html>
