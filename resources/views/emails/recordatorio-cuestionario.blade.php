<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Recordatorio: Complete su cuestionario - REPRO</title>
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
        .urgent-banner {
            background: linear-gradient(90deg, #dc3545, #c82333);
            color: white;
            padding: 15px;
            text-align: center;
            font-weight: bold;
            font-size: 16px;
        }
        .warning-banner {
            background: linear-gradient(90deg, #ffc107, #e0a800);
            color: #333;
            padding: 15px;
            text-align: center;
            font-weight: bold;
            font-size: 16px;
        }
        .content {
            padding: 30px;
        }
        .greeting {
            font-size: 20px;
            color: #000555;
            margin-bottom: 20px;
        }
        .countdown-box {
            background: linear-gradient(135deg, #fff3cd, #ffeeba);
            border: 2px solid #ffc107;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
        }
        .countdown-box.urgent {
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
            border-color: #dc3545;
        }
        .countdown-number {
            font-size: 48px;
            font-weight: bold;
            color: #856404;
        }
        .countdown-box.urgent .countdown-number {
            color: #721c24;
        }
        .countdown-label {
            font-size: 18px;
            color: #666;
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
            padding: 18px 50px;
            background-color: #dc3545;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            font-size: 18px;
            text-transform: uppercase;
        }
        .btn:hover {
            background-color: #c82333;
        }
        .btn.normal {
            background-color: #000555;
        }
        .btn.normal:hover {
            background-color: #000333;
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
        
        @if($diasRestantes <= 1)
        <div class="urgent-banner">
            ⚠️ ¡URGENTE! Su cuestionario expira {{ $diasRestantes == 0 ? 'HOY' : 'MAÑANA' }}
        </div>
        @elseif($diasRestantes <= 3)
        <div class="warning-banner">
            ⏰ Recordatorio: Quedan {{ $diasRestantes }} días para completar su cuestionario
        </div>
        @endif
        
        <div class="content">
            <p class="greeting">Estimado(a) {{ $evaluado->nombre }} {{ $evaluado->apellidos }},</p>
            
            <p>Le recordamos que tiene pendiente completar su cuestionario de evaluación solicitado por <strong>{{ $empresa }}</strong>.</p>
            
            <div class="countdown-box {{ $diasRestantes <= 1 ? 'urgent' : '' }}">
                <div class="countdown-number">{{ $diasRestantes }}</div>
                <div class="countdown-label">{{ $diasRestantes == 1 ? 'día restante' : 'días restantes' }}</div>
            </div>
            
            <div class="info-box">
                <p><strong>Tipo de evaluación:</strong> {{ ucfirst($evaluado->tipo_servicio ?? 'N/A') }}</p>
                <p><strong>Fecha límite:</strong> {{ $fechaExpiracion ?? 'No especificada' }}</p>
            </div>
            
            <div class="cta-section">
                <a href="{{ $urlCuestionario }}" class="btn {{ $diasRestantes > 1 ? 'normal' : '' }}">
                    COMPLETAR CUESTIONARIO AHORA
                </a>
            </div>
            
            @if($diasRestantes <= 1)
            <p style="text-align: center; color: #dc3545; font-weight: bold;">
                ⚠️ Si no completa el cuestionario antes de la fecha límite, el enlace dejará de funcionar y deberá solicitar uno nuevo.
            </p>
            @endif
            
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
