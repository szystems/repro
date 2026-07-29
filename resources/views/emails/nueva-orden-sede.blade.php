<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Nueva orden asignada a su sede - REPRO</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: #000555;
            color: #fff;
            padding: 25px;
            text-align: center;
        }
        .header h2 {
            margin: 0;
            font-size: 24px;
        }
        .header .subtitle {
            color: #ffb000;
            font-size: 14px;
            margin-top: 5px;
        }
        .content {
            padding: 30px;
        }
        .greeting {
            font-size: 20px;
            color: #000555;
            margin-bottom: 20px;
        }
        .info-box {
            background-color: #f8f9fa;
            border-left: 4px solid #ffb000;
            padding: 15px 20px;
            margin: 20px 0;
        }
        .info-box p {
            margin: 5px 0;
        }
        .info-box strong {
            color: #000555;
        }
        .footer {
            background: #f4f4f4;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>REPRO</h2>
            <div class="subtitle">Nueva Orden Asignada a su Sede</div>
        </div>

        <div class="content">
            <p class="greeting">Se ha creado una nueva orden de evaluación asignada a su sede.</p>

            <div class="info-box">
                <p><strong>Código de Orden:</strong> {{ $orden->codigo_orden }}</p>
                <p><strong>Empresa:</strong> {{ $empresa->nombre ?? 'N/A' }}</p>
                <p><strong>Sede:</strong> {{ $sede->nombre ?? 'N/A' }}</p>
                <p><strong>Evaluados:</strong> {{ $orden->evaluados->count() }}</p>
                @php
                    $servicios = $orden->evaluados->pluck('tipo_servicio')->filter()->unique()->map(fn($s) => ucfirst($s))->join(', ');
                @endphp
                @if($servicios)
                    <p><strong>Tipos de Servicio:</strong> {{ $servicios }}</p>
                @endif
                <p><strong>Fecha de Solicitud:</strong> {{ \Carbon\Carbon::parse($orden->fecha_solicitud)->format('d/m/Y') }}</p>
                @if($orden->prioridad && $orden->prioridad != 'normal')
                    <p><strong>Prioridad:</strong> {{ ucfirst($orden->prioridad) }}</p>
                @endif
            </div>

            <p>Por favor, ingrese al sistema para revisar los detalles y proceder con la programación de las evaluaciones.</p>
        </div>

        <div class="footer">
            <p>Este es un correo automático del sistema REPRO. No responda a este mensaje.</p>
        </div>
    </div>
</body>
</html>
