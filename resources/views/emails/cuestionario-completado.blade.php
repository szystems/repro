<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cuestionario Completado - REPRO</title>
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
            background: linear-gradient(90deg, #28a745, #218838);
            color: white;
            padding: 20px;
            text-align: center;
        }
        .success-banner .icon {
            font-size: 48px;
            margin-bottom: 10px;
        }
        .success-banner h3 {
            margin: 0;
            font-size: 22px;
        }
        .content {
            padding: 30px;
        }
        .evaluado-card {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 10px;
            padding: 25px;
            margin: 20px 0;
            border-left: 5px solid #28a745;
        }
        .evaluado-name {
            font-size: 24px;
            font-weight: bold;
            color: #000555;
            margin-bottom: 15px;
        }
        .evaluado-details {
            display: table;
            width: 100%;
        }
        .detail-row {
            display: table-row;
        }
        .detail-label {
            display: table-cell;
            padding: 8px 15px 8px 0;
            color: #666;
            font-weight: bold;
            width: 40%;
        }
        .detail-value {
            display: table-cell;
            padding: 8px 0;
            color: #333;
        }
        .orden-info {
            background-color: #e8f4f8;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .orden-info h4 {
            color: #000555;
            margin-top: 0;
            margin-bottom: 15px;
        }
        .cta-section {
            text-align: center;
            margin: 30px 0;
        }
        .btn {
            display: inline-block;
            padding: 15px 40px;
            background-color: #000555;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            font-size: 16px;
        }
        .btn:hover {
            background-color: #000333;
        }
        .next-steps {
            background-color: #fff3cd;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .next-steps h4 {
            color: #856404;
            margin-top: 0;
        }
        .next-steps ul {
            margin: 0;
            padding-left: 20px;
        }
        .next-steps li {
            margin-bottom: 8px;
        }
        .footer {
            background: #f9f9f9;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #eee;
        }
        .footer a {
            color: #000555;
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
            <div class="icon">✅</div>
            <h3>Cuestionario Completado</h3>
        </div>
        
        <div class="content">
            <p>Le informamos que el siguiente evaluado ha completado su cuestionario:</p>
            
            <div class="evaluado-card">
                <div class="evaluado-name">{{ $evaluado->nombre }} {{ $evaluado->apellidos }}</div>
                <div class="evaluado-details">
                    <div class="detail-row">
                        <span class="detail-label">📧 Email:</span>
                        <span class="detail-value">{{ $evaluado->email ?? 'No proporcionado' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">📱 Teléfono:</span>
                        <span class="detail-value">{{ $evaluado->telefono ?? 'No proporcionado' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">🪪 DPI:</span>
                        <span class="detail-value">{{ $evaluado->dpi ?? 'No proporcionado' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">📋 Tipo de Servicio:</span>
                        <span class="detail-value">{{ ucfirst($evaluado->tipo_servicio ?? 'N/A') }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">📝 Tipo de Formulario:</span>
                        <span class="detail-value">{{ ucfirst($evaluado->tipo_formulario ?? 'N/A') }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">✅ Completado:</span>
                        <span class="detail-value">{{ $fechaCompletado }}</span>
                    </div>
                </div>
            </div>
            
            <div class="orden-info">
                <h4>📦 Información de la Orden</h4>
                <div class="evaluado-details">
                    <div class="detail-row">
                        <span class="detail-label">Código:</span>
                        <span class="detail-value">{{ $orden->codigo_orden ?? 'N/A' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Empresa:</span>
                        <span class="detail-value">{{ $empresa }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Estado de Orden:</span>
                        <span class="detail-value">{{ ucfirst($orden->estado ?? 'N/A') }}</span>
                    </div>
                </div>
            </div>
            
            <div class="cta-section">
                <a href="{{ route('dashboard') }}" class="btn">VER EN EL SISTEMA</a>
            </div>
            
            <div class="next-steps">
                <h4>📌 Próximos pasos:</h4>
                <ul>
                    <li>Revisar las respuestas del cuestionario en el sistema</li>
                    <li>Programar la siguiente fase de evaluación si aplica</li>
                    <li>Actualizar el estado de la orden según corresponda</li>
                </ul>
            </div>
        </div>
        
        <div class="footer">
            <p>Este es un correo automático del sistema REPRO.</p>
            <p>No responda a este correo.</p>
            <p>&copy; {{ date('Y') }} REPRO Guatemala. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>
