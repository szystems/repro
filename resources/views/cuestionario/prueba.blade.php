<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prueba Cuestionario</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 50px; }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <h1>🎯 Prueba de Cuestionario</h1>
    
    <div class="success">
        <h2>✅ ¡Funciona!</h2>
        <p>El token es válido y el controlador está funcionando correctamente.</p>
    </div>
    
    <h3>Información del Evaluado:</h3>
    <ul>
        <li><strong>Token:</strong> {{ $token }}</li>
        <li><strong>Nombre:</strong> {{ $evaluado->nombre ?? 'N/A' }} {{ $evaluado->apellidos ?? '' }}</li>
        <li><strong>DPI:</strong> {{ $evaluado->dpi ?? 'N/A' }}</li>
        <li><strong>Empresa:</strong> {{ $evaluado->orden->empresa->nombre ?? 'N/A' }}</li>
        <li><strong>Token expira:</strong> {{ $evaluado->token_expira_at ?? 'N/A' }}</li>
        <li><strong>Completado:</strong> {{ $evaluado->cuestionario_completado ? 'Sí' : 'No' }}</li>
    </ul>
    
    <div style="margin-top: 30px; padding: 20px; background: #f0f0f0; border-radius: 5px;">
        <h4>Próximos pasos:</h4>
        <ol>
            <li>El token está funcionando ✅</li>
            <li>La vista se carga correctamente ✅</li>
            <li>Ahora puedes continuar con la vista de verificación de identidad</li>
        </ol>
    </div>
    
    <div style="margin-top: 20px;">
        <a href="{{ route('cuestionario.mostrar', $token) }}" style="background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
            🔄 Intentar de Nuevo
        </a>
    </div>
</body>
</html>