
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
        .badge-secondary { background-color: #6c757d; color: white; }
        .seccion { margin-bottom: 15px; page-break-inside: avoid; }
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
        .seccion { page-break-inside: auto; }
    </style>
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
                <td class="info-label">Estado:</td>
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
        @php
            $seccionesConfig = $cuestionario->getSeccionesConfig();
            $etiquetasCampos = [
                // ... (puedes copiar el mapeo de etiquetas del PDF de REPRO aquí) ...
            ];
        @endphp
        @foreach($seccionesConfig as $numeroSeccion => $nombreSeccion)
            <div class="seccion">
                <div class="seccion-titulo">
                    Sección {{ $numeroSeccion }}: {{ $nombreSeccion }}
                </div>
                @php
                    $respuestasSeccion = $cuestionario->obtenerRespuestasSeccion($numeroSeccion);
                @endphp
                @if(count($respuestasSeccion) > 0)
                    <table class="datos-table">
                        @foreach($respuestasSeccion as $campo => $valor)
                            @php
                                $etiqueta = $etiquetasCampos[$campo] ?? ucfirst(str_replace('_', ' ', $campo));
                                $valorFormateado = $valor;
                                if (empty($valor)) {
                                    $valorFormateado = null;
                                } elseif (in_array(strtolower($valor), ['si', 'sí', '1', 'true'])) {
                                    $valorFormateado = 'Sí';
                                } elseif (in_array(strtolower($valor), ['no', '0', 'false'])) {
                                    $valorFormateado = 'No';
                                } elseif ((str_contains($campo, 'ingreso') || str_contains($campo, 'gasto') ||
                                    str_contains($campo, 'salario') || str_contains($campo, 'monto') ||
                                    str_contains($campo, 'balance') || str_contains($campo, 'total')) && is_numeric($valor)) {
                                    $valorFormateado = 'Q' . number_format((float)$valor, 2);
                                } else {
                                    $valorFormateado = ucfirst(str_replace('_', ' ', $valor));
                                }
                            @endphp
                            <tr>
                                <th>{{ $etiqueta }}:</th>
                                <td class="{{ empty($valorFormateado) ? 'vacio' : '' }}">
                                    {!! $valorFormateado ? nl2br(e($valorFormateado)) : 'No proporcionado' !!}
                                </td>
                            </tr>
                        @endforeach
                    </table>
                @else
                    <table class="datos-table">
                        <tr>
                            <td class="vacio" style="text-align: center;" colspan="2">
                                Esta sección no tiene respuestas registradas.
                            </td>
                        </tr>
                    </table>
                @endif
            </div>
        @endforeach
    @endif

    <div class="footer">
        <p><strong>REPRO Guatemala</strong></p>
        <p>Documento generado el {{ now()->format('d/m/Y H:i') }} | Cuestionario ID: {{ $cuestionario->id ?? $evaluado->id }}</p>
        <p style="color: #ffb000;">Este documento es confidencial y de uso exclusivo para fines de evaluación laboral.</p>
    </div>
</body>
</html>
