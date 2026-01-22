<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ha sido asignado para evaluación - REPRO</title>
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
        .warning {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
        }
        .warning-icon {
            color: #856404;
            font-weight: bold;
        }
        .steps {
            background-color: #e8f4f8;
            border-radius: 5px;
            padding: 20px;
            margin: 20px 0;
        }
        .steps h4 {
            color: #000555;
            margin-top: 0;
        }
        .steps ol {
            margin: 0;
            padding-left: 20px;
        }
        .steps li {
            margin-bottom: 10px;
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
        .link-alt {
            font-size: 12px;
            color: #666;
            word-break: break-all;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>REPRO Guatemala</h2>
            <div class="subtitle">Sistema de Evaluaciones</div>
        </div>
        
        <div class="content">
            <p class="greeting">Estimado(a) {{ $evaluado->nombre }} {{ $evaluado->apellidos }},</p>
            
            <p>Ha sido asignado(a) para completar un cuestionario de evaluación solicitado por <strong>{{ $empresa }}</strong>.</p>
            
            <div class="info-box">
                <p><strong>Tipo de evaluación:</strong> {{ ucfirst($evaluado->tipo_servicio ?? 'N/A') }}</p>
                <p><strong>Tipo de formulario:</strong> {{ ucfirst($evaluado->tipo_formulario ?? 'N/A') }}</p>
                @if($evaluado->puesto_evaluar)
                <p><strong>Puesto a evaluar:</strong> {{ $evaluado->puesto_evaluar }}</p>
                @endif
                <p><strong>Fecha límite:</strong> {{ $fechaExpiracion ?? 'No especificada' }}</p>
            </div>
            
            <div class="cta-section">
                <a href="{{ $urlCuestionario }}" class="btn">ACCEDER AL CUESTIONARIO</a>
            </div>
            
            <div class="steps">
                <h4>📋 Instrucciones:</h4>
                <ol>
                    <li>Haga clic en el botón "ACCEDER AL CUESTIONARIO" arriba</li>
                    <li>Complete todas las secciones del formulario con información veraz</li>
                    <li>Revise sus respuestas antes de enviar</li>
                    <li>Una vez enviado, recibirá una confirmación</li>
                </ol>
            </div>
            
            <div class="warning">
                <p><span class="warning-icon">⚠️ Importante:</span></p>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>Este enlace es personal e intransferible</li>
                    <li>El enlace expira el <strong>{{ $fechaExpiracion }}</strong></li>
                    <li>No comparta este enlace con terceros</li>
                    <li>Complete el cuestionario en un lugar privado y tranquilo</li>
                </ul>
            </div>
            
            <p class="link-alt">
                Si el botón no funciona, copie y pegue este enlace en su navegador:<br>
                <a href="{{ $urlCuestionario }}">{{ $urlCuestionario }}</a>
            </p>
        </div>
        
        <div class="footer">
            <p>Este es un correo automático del sistema REPRO.</p>
            <p>Si tiene alguna duda, contacte a su departamento de Recursos Humanos.</p>
            <p>&copy; {{ date('Y') }} REPRO Guatemala. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>
