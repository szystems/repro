<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Resultados Disponibles - REPRO</title>
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
        .success-banner {
            background: linear-gradient(90deg, #17a2b8, #138496);
            color: white;
            padding: 20px;
            text-align: center;
        }
        .success-banner h3 {
            margin: 0;
            font-size: 22px;
        }
        .content {
            padding: 30px;
        }
        .orden-card {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 10px;
            padding: 25px;
            margin: 20px 0;
            border-left: 5px solid #17a2b8;
        }
        .orden-code {
            font-size: 20px;
            font-weight: bold;
            color: #000555;
            margin-bottom: 10px;
        }
        .detail-row {
            padding: 6px 0;
            border-bottom: 1px solid #eee;
        }
        .detail-label {
            color: #666;
            font-weight: bold;
        }
        .evaluado-list {
            margin: 15px 0;
        }
        .evaluado-item {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 12px 15px;
            margin-bottom: 8px;
        }
        .evaluado-name {
            font-weight: bold;
            color: #000555;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            color: white;
        }
        .badge-success { background: #28a745; }
        .badge-warning { background: #ffc107; color: #333; }
        .badge-danger { background: #dc3545; }
        .badge-info { background: #17a2b8; }
        .badge-secondary { background: #6c757d; }
        .cta-button {
            display: inline-block;
            background: #000555;
            color: #fff !important;
            text-decoration: none;
            padding: 14px 35px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            margin: 20px 0;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #999;
            border-top: 1px solid #eee;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>REPRO Guatemala</h2>
            <div class="subtitle">Sistema de Evaluaciones</div>
        </div>

        <div class="success-banner">
            <h3>&#128203; Resultados Disponibles</h3>
            <p style="margin: 5px 0 0 0;">Los resultados de su orden ya están disponibles para consulta</p>
        </div>

        <div class="content">
            <p>Estimado cliente,</p>

            <p>Le informamos que los resultados de la siguiente orden de evaluación ya están disponibles para su consulta:</p>

            <div class="orden-card">
                <div class="orden-code">Orden: {{ $orden->codigo_orden }}</div>
                <div class="detail-row">
                    <span class="detail-label">Empresa:</span> {{ $empresa }}
                </div>
                <div class="detail-row">
                    <span class="detail-label">Evaluados:</span> {{ $cantidadEvaluados }}
                </div>
                <div class="detail-row">
                    <span class="detail-label">Fecha:</span> {{ $orden->created_at->format('d/m/Y') }}
                </div>
            </div>

            @if($evaluados->count() > 0)
            <h4 style="color: #000555;">Evaluados incluidos:</h4>
            <div class="evaluado-list">
                @foreach($evaluados as $evaluado)
                <div class="evaluado-item">
                    <span class="evaluado-name">{{ $evaluado->nombre }} {{ $evaluado->apellidos }}</span>
                    <br>
                    <small style="color: #666;">
                        {{ $evaluado->tipo_servicio_texto }} &middot; {{ $evaluado->dpi }}
                    </small>
                    @if($evaluado->resultado)
                        <br>
                        <span class="badge badge-{{ $evaluado->resultado_color }}">
                            {{ $evaluado->resultado_texto }}
                        </span>
                    @endif
                </div>
                @endforeach
            </div>
            @endif

            <p>Puede acceder a los resultados detallados ingresando a su cuenta en el sistema REPRO:</p>

            <div style="text-align: center;">
                <a href="{{ url('/login') }}" class="cta-button">
                    Ver Resultados
                </a>
            </div>

            <p style="margin-top: 25px; font-size: 14px; color: #666;">
                Si tiene alguna consulta sobre los resultados, no dude en contactarnos.
            </p>
        </div>

        <div class="footer">
            <p>Este es un correo automático del sistema REPRO Guatemala.</p>
            <p>Por favor no responda a este correo.</p>
            <p>&copy; {{ date('Y') }} REPRO Guatemala. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>
