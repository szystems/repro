<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Autorización — {{ $cuestionario->evaluadoOrden->nombre }} {{ $cuestionario->evaluadoOrden->apellidos }}</title>
    <style>
        @page {
            size: portrait;
            margin: 15mm;
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

        .repro-header h1 {
            margin: 0;
            font-size: 16px;
        }

        .repro-header p {
            margin: 4px 0 0;
            font-size: 10px;
            opacity: 0.9;
        }

        .meta-box {
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 10px 12px;
            margin-bottom: 15px;
            font-size: 9px;
        }

        .seccion {
            margin-bottom: 12px;
            page-break-inside: avoid;
        }

        .seccion-titulo {
            background-color: #000555;
            color: white;
            padding: 6px 10px;
            font-weight: bold;
            font-size: 11px;
            border-radius: 4px 4px 0 0;
        }

        .autorizacion-cuerpo {
            padding: 10px;
            font-size: 9px;
            line-height: 1.6;
            border: 1px solid #ddd;
            border-top: none;
        }

        .autorizacion-documento {
            page-break-inside: avoid;
        }

        .firma-container {
            margin-top: 15px;
            text-align: center;
            page-break-inside: avoid;
        }

        .firma-imagen {
            max-height: 80px;
            max-width: 250px;
            border: 1px solid #ccc;
            padding: 5px;
        }

        .firma-texto {
            margin-top: 8px;
            font-size: 9px;
        }

        .firma-responsable {
            margin-top: 24px;
            text-align: center;
            page-break-inside: avoid;
        }

        .firma-responsable-linea {
            display: inline-block;
            width: 250px;
            border-top: 2px solid #000555;
            padding-top: 8px;
        }

        .firma-responsable-nombre {
            font-size: 10px;
            font-weight: bold;
            color: #000555;
        }

        .firma-responsable-cargo {
            font-size: 9px;
            color: #666;
        }

        .firma-responsable-rol {
            font-size: 8px;
            color: #999;
            margin-top: 2px;
        }

        .infornet-fecha {
            font-size: 8px;
            color: #666;
            padding: 0 10px;
        }

        .texto-vacio {
            color: #666;
            font-style: italic;
            padding: 20px;
            text-align: center;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 8px;
            color: #666;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="repro-header">
        <h1>Autorización y Términos</h1>
        <p>Documento independiente del cuestionario — REPRO Guatemala</p>
    </div>

    <div class="meta-box">
        <strong>Candidato:</strong> {{ $cuestionario->evaluadoOrden->nombre }} {{ $cuestionario->evaluadoOrden->apellidos }}<br>
        <strong>DPI:</strong> {{ $cuestionario->evaluadoOrden->dpi }}<br>
        <strong>Orden:</strong> {{ $cuestionario->evaluadoOrden->orden->codigo_orden ?? 'N/A' }}<br>
        <strong>Empresa:</strong> {{ $cuestionario->evaluadoOrden->orden->empresa->nombre ?? 'N/A' }}<br>
        <strong>Servicio:</strong> {{ ucfirst($cuestionario->evaluadoOrden->tipo_servicio ?? 'N/A') }}
    </div>

    @include('shared.cuestionario.pdf-autorizacion-contenido', ['cuestionario' => $cuestionario])

    <div class="footer">
        <p><strong>REPRO Guatemala</strong></p>
        <p>Generado el {{ now()->format('d/m/Y H:i') }} | Cuestionario #{{ $cuestionario->id }}</p>
    </div>
</body>
</html>
