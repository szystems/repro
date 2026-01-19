<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cuestionario Socioeconómico - {{ $cuestionario->evaluadoOrden->nombre }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #2c3e50;
        }
        
        .header h2 {
            margin: 5px 0;
            font-size: 14px;
            color: #7f8c8d;
        }
        
        .info-general {
            background-color: #f8f9fa;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        
        .info-label {
            font-weight: bold;
            color: #2c3e50;
        }
        
        .seccion {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        
        .seccion-titulo {
            background-color: #3498db;
            color: white;
            padding: 10px;
            margin-bottom: 15px;
            font-weight: bold;
            font-size: 14px;
        }
        
        .pregunta {
            margin-bottom: 15px;
            padding: 10px;
            border-left: 3px solid #3498db;
            background-color: #f8f9fa;
        }
        
        .pregunta-texto {
            font-weight: bold;
            margin-bottom: 5px;
            color: #2c3e50;
        }
        
        .respuesta {
            padding: 8px;
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 3px;
            min-height: 20px;
        }
        
        .respuesta.vacia {
            color: #999;
            font-style: italic;
        }
        
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #7f8c8d;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        
        .estado-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .estado-completado {
            background-color: #27ae60;
            color: white;
        }
        
        .estado-progreso {
            background-color: #f39c12;
            color: white;
        }
        
        .estado-pendiente {
            background-color: #95a5a6;
            color: white;
        }
        
        .progreso-bar {
            width: 100%;
            height: 20px;
            background-color: #ecf0f1;
            border-radius: 10px;
            overflow: hidden;
            margin: 10px 0;
        }
        
        .progreso-fill {
            height: 100%;
            background-color: #27ae60;
            text-align: center;
            line-height: 20px;
            color: white;
            font-size: 11px;
            font-weight: bold;
        }
        
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>CUESTIONARIO SOCIOECONÓMICO</h1>
        <h2>REPRO Guatemala - Evaluación Psicológica</h2>
    </div>

    <div class="info-general">
        <div class="info-row">
            <span class="info-label">Evaluado:</span>
            <span>{{ $cuestionario->evaluadoOrden->nombre }} {{ $cuestionario->evaluadoOrden->apellidos }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">DPI:</span>
            <span>{{ $cuestionario->evaluadoOrden->dpi }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Empresa:</span>
            <span>{{ $cuestionario->evaluadoOrden->orden->empresa->nombre }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Puesto a Evaluar:</span>
            <span>{{ $cuestionario->evaluadoOrden->puesto_evaluar ?? 'No especificado' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Estado:</span>
            <span class="estado-badge {{ $cuestionario->completado ? 'estado-completado' : ($cuestionario->seccion_actual > 1 ? 'estado-progreso' : 'estado-pendiente') }}">
                {{ $cuestionario->completado ? 'Completado' : ($cuestionario->seccion_actual > 1 ? 'En Progreso' : 'Pendiente') }}
            </span>
        </div>
        <div class="info-row">
            <span class="info-label">Progreso:</span>
            <div style="flex: 1; margin-left: 20px;">
                <div class="progreso-bar">
                    <div class="progreso-fill" style="width: {{ $cuestionario->calcularProgreso() }}%">
                        {{ $cuestionario->calcularProgreso() }}%
                    </div>
                </div>
            </div>
        </div>
        <div class="info-row">
            <span class="info-label">Fecha de Creación:</span>
            <span>{{ $cuestionario->created_at->format('d/m/Y H:i') }}</span>
        </div>
        @if($cuestionario->completado_at)
        <div class="info-row">
            <span class="info-label">Fecha de Completado:</span>
            <span>{{ $cuestionario->completado_at->format('d/m/Y H:i') }}</span>
        </div>
        @endif
    </div>

    @php
        $secciones = [
            1 => 'Información Personal y Familiar',
            2 => 'Información Académica y Laboral', 
            3 => 'Información Económica y Patrimonial',
            4 => 'Referencias Personales y Familiares',
            5 => 'Declaraciones y Consentimientos'
        ];
    @endphp

    @foreach($secciones as $numeroSeccion => $tituloSeccion)
        @if($numeroSeccion > 1)
            <div class="page-break"></div>
        @endif
        
        <div class="seccion">
            <div class="seccion-titulo">
                Sección {{ $numeroSeccion }}: {{ $tituloSeccion }}
            </div>

            @if(isset($respuestasPorSeccion[$numeroSeccion]) && $respuestasPorSeccion[$numeroSeccion]->count() > 0)
                @foreach($respuestasPorSeccion[$numeroSeccion] as $respuesta)
                    <div class="pregunta">
                        <div class="pregunta-texto">
                            {{ $respuesta->pregunta_texto ?? $respuesta->campo }}
                        </div>
                        <div class="respuesta {{ empty($respuesta->valor) ? 'vacia' : '' }}">
                            {{ $respuesta->valor ?: 'Sin respuesta' }}
                        </div>
                    </div>
                @endforeach
            @else
                <div class="pregunta">
                    <div class="respuesta vacia">
                        Esta sección no tiene respuestas registradas.
                    </div>
                </div>
            @endif
        </div>
    @endforeach

    @if($cuestionario->observaciones_repro)
        <div class="page-break"></div>
        <div class="seccion">
            <div class="seccion-titulo">
                Observaciones de REPRO
            </div>
            <div class="pregunta">
                <div class="respuesta">
                    {{ $cuestionario->observaciones_repro }}
                </div>
            </div>
        </div>
    @endif

    <div class="footer">
        <p><strong>REPRO Guatemala - Evaluaciones Psicológicas Profesionales</strong></p>
        <p>Documento generado el {{ now()->format('d/m/Y H:i') }}</p>
        <p>ID del Cuestionario: {{ $cuestionario->id }} | Token: {{ $cuestionario->evaluadoOrden->token_unico }}</p>
    </div>
</body>
</html>