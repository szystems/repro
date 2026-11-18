<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Nuevo comentario - REPRO</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.5; color: #333; background: #f4f4f4; margin: 0; padding: 24px;">
    <div style="max-width: 600px; margin: 0 auto; background: #fff; border-radius: 8px; overflow: hidden;">
        <div style="background: #000555; color: #fff; padding: 20px; text-align: center;">
            <h2 style="margin: 0; font-size: 20px;">REPRO Guatemala</h2>
            <div style="color: #ffb000; font-size: 13px; margin-top: 4px;">Nuevo comentario</div>
        </div>
        <div style="padding: 24px;">
            <p>Se agregó un comentario en el candidato <strong>{{ $evaluado->nombre_completo }}</strong>, orden <strong>{{ $orden->codigo_orden ?? $orden->id }}</strong>.</p>
            <div style="background: #f8f9fa; border-left: 4px solid #000555; padding: 12px 16px; white-space: pre-wrap;">{{ $comentario }}</div>
            <p style="text-align: center; margin-top: 24px;">
                <a href="{{ $urlOrden }}" style="display: inline-block; background: #000555; color: #fff; text-decoration: none; padding: 12px 24px; border-radius: 8px; font-weight: bold;">Ver en el portal</a>
            </p>
        </div>
        <div style="background: #f8f9fa; padding: 16px; text-align: center; font-size: 12px; color: #999;">
            <p style="margin: 0;">Este es un correo automático del sistema REPRO Guatemala. Por favor no responda a este correo.</p>
        </div>
    </div>
</body>
</html>
