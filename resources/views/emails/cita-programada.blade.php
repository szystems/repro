<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $reprogramada ? 'Cita reprogramada' : 'Cita programada' }} - REPRO</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 8px; overflow: hidden; }
        .header { background: #000555; color: #fff; padding: 25px; text-align: center; }
        .header h2 { margin: 0; }
        .header .subtitle { color: #ffb000; font-size: 14px; margin-top: 5px; }
        .content { padding: 30px; }
        .info-box { background-color: #f8f9fa; border-left: 4px solid #ffb000; padding: 15px 20px; margin: 20px 0; }
        .info-box p { margin: 5px 0; }
        .footer { background: #f4f4f4; padding: 20px; text-align: center; font-size: 12px; color: #999; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>REPRO Guatemala</h2>
            <div class="subtitle">{{ $reprogramada ? 'Cita reprogramada' : 'Cita de evaluación' }}</div>
        </div>
        <div class="content">
            <p>Estimado(a) {{ $evaluado->nombre }} {{ $evaluado->apellidos }},</p>
            @if($reprogramada)
                <p>Su cita de evaluación solicitada por <strong>{{ $empresa }}</strong> fue <strong>reprogramada</strong>.</p>
            @else
                <p>Se programó su cita de evaluación solicitada por <strong>{{ $empresa }}</strong>.</p>
            @endif
            <div class="info-box">
                <p><strong>Fecha:</strong> {{ $fecha ?? 'N/A' }}</p>
                <p><strong>Hora:</strong> {{ $horaInicio ?? 'N/A' }}{{ $horaFin ? ' a '.$horaFin : '' }}</p>
                <p><strong>Sede:</strong> {{ $sede }}</p>
                <p><strong>Modalidad:</strong> {{ $modalidad }}</p>
            </div>
            <p>Si no puede asistir, contacte a REPRO para reprogramar.</p>
        </div>
        <div class="footer">
            <p>Este es un correo automático del sistema REPRO. No responda a este mensaje.</p>
        </div>
    </div>
</body>
</html>
