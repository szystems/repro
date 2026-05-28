<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Informe de Candidatos — {{ $orden->codigo_orden }}</title>
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
        .repro-header {
            background-color: #000555;
            color: white;
            padding: 12px 20px;
            margin-bottom: 15px;
            border-radius: 6px;
        }
        .repro-header-content { display: table; width: 100%; }
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
        .repro-logo { max-height: 40px; max-width: 150px; display: block; }
        .repro-title-cell {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
        }
        .repro-header h1 { margin: 0; font-size: 16px; font-weight: bold; color: #ffb000; text-transform: uppercase; letter-spacing: 1px; }
        .repro-header h2 { margin: 4px 0 0 0; font-size: 11px; font-weight: normal; color: #ffcc33; }
        .repro-info-cell { display: table-cell; vertical-align: middle; text-align: right; width: 120px; font-size: 9px; }
        .repro-info-cell span { color: #ffcc33; }

        .info-general {
            background: linear-gradient(135deg, #ffb000 0%, #ffcc33 100%);
            border: 2px solid #000555;
            border-radius: 6px;
            padding: 10px 12px;
            margin-bottom: 15px;
        }
        .info-table { width: 100%; border-collapse: collapse; }
        .info-table td { padding: 3px 8px; font-size: 9px; vertical-align: top; }
        .info-label { font-weight: bold; color: #000555; width: 100px; }
        .info-value { color: #1a1a6b; }

        .candidato-bloque {
            margin-bottom: 18px;
            page-break-inside: avoid;
            border: 1px solid #ddd;
            border-radius: 6px;
            overflow: hidden;
        }
        .candidato-header {
            background-color: #000555;
            color: #ffb000;
            padding: 8px 12px;
            font-weight: bold;
            font-size: 11px;
        }
        .candidato-body { padding: 10px 12px; }
        .datos-row { display: table; width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        .datos-col { display: table-cell; padding: 0 6px 4px 0; vertical-align: top; }
        .dato-label { font-size: 8px; color: #666; display: block; margin-bottom: 1px; }
        .dato-valor { font-size: 10px; color: #222; }

        .resultado-box {
            margin-top: 8px;
            border-top: 2px solid #ffb000;
            padding-top: 8px;
        }
        .resultado-titulo {
            font-weight: bold;
            color: #000555;
            font-size: 10px;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 10px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-aprobado        { background-color: #28a745; color: white; }
        .badge-no_aprobado     { background-color: #dc3545; color: white; }
        .badge-pendiente       { background-color: #ffc107; color: #212529; }
        .badge-inconcluso      { background-color: #6c757d; color: white; }
        .badge-aprobado_con_obs{ background-color: #17a2b8; color: white; }
        .badge-aprobado_excepcion { background-color: #6f42c1; color: white; }
        .badge-tipo_a          { background-color: #28a745; color: white; }
        .badge-a_condicionado  { background-color: #fd7e14; color: white; }
        .badge-tipo_b          { background-color: #dc3545; color: white; }
        .badge-tipo_c          { background-color: #343a40; color: white; }

        .notas-box {
            background: #f8f9fa;
            border-left: 3px solid #ffb000;
            padding: 6px 10px;
            margin-top: 6px;
            font-size: 9px;
            color: #444;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 8px;
            color: #000555;
            border-top: 2px solid #ffb000;
            padding-top: 10px;
        }
        .sin-resultado {
            color: #888;
            font-style: italic;
            font-size: 9px;
        }
    </style>
</head>
<body>
    {{-- Cabecera --}}
    <div class="repro-header">
        <div class="repro-header-content">
            <div class="repro-logo-cell">
                <div class="repro-logo-container">
                    <img src="{{ public_path('img/logos/logoreproxelahorizontal.png') }}" alt="REPRO" class="repro-logo">
                </div>
            </div>
            <div class="repro-title-cell">
                <h1>Informe de Candidatos</h1>
                <h2>{{ $orden->codigo_orden }}</h2>
            </div>
            <div class="repro-info-cell">
                <span>Fecha:</span> {{ now()->format('d/m/Y') }}
            </div>
        </div>
    </div>

    {{-- Resumen de la orden --}}
    <div class="info-general">
        <table class="info-table">
            <tr>
                <td class="info-label">Empresa:</td>
                <td class="info-value"><strong>{{ $orden->empresa?->nombre ?? '—' }}</strong></td>
                <td class="info-label">Orden:</td>
                <td class="info-value"><strong>{{ $orden->codigo_orden }}</strong></td>
            </tr>
            <tr>
                <td class="info-label">Sede REPRO:</td>
                <td class="info-value">{{ $orden->sede?->nombre ?? '—' }}</td>
                <td class="info-label">Total candidatos:</td>
                <td class="info-value">{{ $orden->evaluados->count() }}</td>
            </tr>
            <tr>
                <td class="info-label">Fecha solicitud:</td>
                <td class="info-value">{{ \Carbon\Carbon::parse($orden->fecha_solicitud)->format('d/m/Y') }}</td>
                <td class="info-label">Estado orden:</td>
                <td class="info-value">{{ $estados[$orden->estado] ?? ucfirst($orden->estado) }}</td>
            </tr>
        </table>
    </div>

    {{-- Candidatos --}}
    @forelse($orden->evaluados as $index => $evaluado)
    @php
        $resultadoTextos = [
            'pendiente'          => 'Pendiente',
            'aprobado'           => 'Aprobado',
            'aprobado_con_obs'   => 'Aprobado con Observaciones',
            'aprobado_excepcion' => 'Aprobado por Excepción',
            'no_aprobado'        => 'No Aprobado',
            'inconcluso'         => 'Inconcluso',
            'tipo_a'             => 'Tipo A',
            'a_condicionado'     => 'A Condicionado',
            'tipo_b'             => 'Tipo B',
            'tipo_c'             => 'Tipo C',
        ];
        $resultado = $evaluado->resultado ?? 'pendiente';
    @endphp
    <div class="candidato-bloque">
        <div class="candidato-header">
            {{ $index + 1 }}. {{ $evaluado->nombre }} {{ $evaluado->apellidos }}
        </div>
        <div class="candidato-body">
            <div class="datos-row">
                <div class="datos-col" style="width: 25%;">
                    <span class="dato-label">DPI</span>
                    <span class="dato-valor">{{ $evaluado->dpi }}</span>
                </div>
                <div class="datos-col" style="width: 25%;">
                    <span class="dato-label">Servicio</span>
                    <span class="dato-valor">
                        @if($evaluado->tipo_servicio == 'poligrafo') Polígrafo
                        @elseif($evaluado->tipo_servicio == 'vsa') VSA
                        @else Socioeconómico
                        @endif
                    </span>
                </div>
                <div class="datos-col" style="width: 25%;">
                    <span class="dato-label">Formulario</span>
                    <span class="dato-valor">
                        @if($evaluado->tipo_formulario == 'preempleo') Pre-empleo
                        @elseif($evaluado->tipo_formulario == 'periodica') Periódica
                        @else Específica
                        @endif
                    </span>
                </div>
                <div class="datos-col" style="width: 25%;">
                    <span class="dato-label">Fecha evaluación</span>
                    <span class="dato-valor">
                        @if($evaluado->fecha_realizada)
                            {{ \Carbon\Carbon::parse($evaluado->fecha_realizada)->format('d/m/Y') }}
                        @elseif($evaluado->fecha_programada)
                            {{ \Carbon\Carbon::parse($evaluado->fecha_programada)->format('d/m/Y') }}
                            <small style="color:#888;">(programada)</small>
                        @else
                            —
                        @endif
                    </span>
                </div>
            </div>

            @if($evaluado->puesto_evaluar || $evaluado->sede || $evaluado->sede_region_empresa)
            <div class="datos-row">
                @if($evaluado->puesto_evaluar)
                <div class="datos-col" style="width: 50%;">
                    <span class="dato-label">Puesto a evaluar</span>
                    <span class="dato-valor">{{ $evaluado->puesto_evaluar }}</span>
                </div>
                @endif
                @if($evaluado->sede_region_empresa)
                <div class="datos-col" style="width: 50%;">
                    <span class="dato-label">Sede/Región Empresa</span>
                    <span class="dato-valor">{{ $evaluado->sede_region_empresa }}</span>
                </div>
                @endif
                @if($evaluado->sede)
                <div class="datos-col" style="width: 50%;">
                    <span class="dato-label">Sede REPRO</span>
                    <span class="dato-valor">{{ $evaluado->sede->nombre }}</span>
                </div>
                @endif
            </div>
            @endif

            <div class="resultado-box">
                <div class="resultado-titulo">Resultado</div>
                @if($resultado !== 'pendiente')
                    <span class="badge badge-{{ $resultado }}">
                        {{ $resultadoTextos[$resultado] ?? ucfirst($resultado) }}
                    </span>
                    @if($evaluado->resultado_final_at)
                        <small style="color:#666; margin-left: 8px;">
                            {{ \Carbon\Carbon::parse($evaluado->resultado_final_at)->format('d/m/Y') }}
                        </small>
                    @endif
                @else
                    <span class="sin-resultado">Evaluación pendiente de resultado</span>
                @endif

                @if($evaluado->observaciones)
                    <div class="notas-box" style="margin-top: 6px;">
                        <strong>Observaciones:</strong> {{ $evaluado->observaciones }}
                    </div>
                @endif
                @if(($mostrarInformePreliminar ?? true) && $evaluado->texto_informe_preliminar)
                    <div class="notas-box" style="margin-top: 6px; padding: 8px; border-left: 3px solid #17a2b8; background:#f6fbfd;">
                        <strong style="color:#17a2b8;">Informe Preliminar:</strong>
                        <div style="margin-top: 4px; font-size: 10px; line-height: 1.4;">
                            {!! $evaluado->texto_informe_preliminar !!}
                        </div>
                    </div>
                @endif

        </div>
    </div>
    @empty
    <p style="text-align:center; color: #888;">No hay candidatos asignados a esta orden.</p>
    @endforelse

    <div class="footer">
        <p><strong>{{ $config->nombre_empresa ?? 'REPRO Guatemala' }}</strong></p>
        <p>Documento generado el {{ now()->format('d/m/Y H:i') }} | Orden: {{ $orden->codigo_orden }}</p>
        <p style="color: #ffb000;">Este documento es confidencial y de uso exclusivo para el cliente.</p>
    </div>
</body>
</html>
