<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>REPRO - Contraseña Restablecida</title>
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
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            background: #053B50;
            color: #fff;
            padding: 15px;
            text-align: center;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }
        .content {
            padding: 20px;
        }
        .footer {
            background: #f9f9f9;
            padding: 15px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-bottom-left-radius: 8px;
            border-bottom-right-radius: 8px;
        }
        h1 {
            color: #176B87;
        }
        .creds {
            background-color: #f9f9f9;
            padding: 15px;
            margin: 20px 0;
            border-left: 4px solid #E74C3C;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #176B87;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 15px;
        }
        .warning {
            color: #E74C3C;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>REPRO - Sistema de Evaluaciones</h2>
        </div>
        <div class="content">
            <h1>Contraseña Restablecida</h1>

            <p>Hola, {{ $user->name }}:</p>

            <p>Se ha generado una nueva contraseña para su cuenta en el sistema REPRO.</p>

            <div class="creds">
                <p><strong>Nuevos datos de acceso:</strong></p>
                <p>Email: <strong>{{ $user->email }}</strong></p>
                <p>Nueva contraseña: <strong>{{ $password }}</strong></p>
                <p class="warning"><em>Por seguridad, le recomendamos cambiar esta contraseña después de iniciar sesión.</em></p>
            </div>

            <p>Para ingresar al sistema con su nueva contraseña, haga clic en el siguiente enlace:</p>

            <p><a href="{{ url('/login') }}" class="btn">Ingresar al Sistema</a></p>

            <p>Si usted no solicitó este cambio de contraseña, por favor contacte al administrador del sistema inmediatamente.</p>

            <p>Saludos cordiales,<br>Equipo REPRO</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} REPRO - Sistema de Evaluaciones. Todos los derechos reservados.</p>
            <p>Este correo es generado automáticamente, por favor no responda a este mensaje.</p>
        </div>
    </div>
</body>
</html>
