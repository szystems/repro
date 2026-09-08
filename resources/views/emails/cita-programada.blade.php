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
        .section-title { color: #000555; font-size: 14px; margin: 22px 0 8px; }
        .conditions { margin: 0; padding-left: 20px; }
        .conditions li { margin-bottom: 6px; }
        .warning { background-color: #fff3cd; border: 1px solid #ffc107; border-radius: 5px; padding: 15px; margin: 20px 0; }
        .footer { background: #f4f4f4; padding: 20px; text-align: center; font-size: 12px; color: #999; }
        .footer a { color: #000555; }
        .link-alt { font-size: 12px; color: #666; word-break: break-all; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>REPRO Guatemala</h2>
            <div class="subtitle">{{ $reprogramada ? 'Cita reprogramada' : 'Confirmación de cita' }}</div>
        </div>
        <div class="content">
            <p>Estimado(a) candidato(a): <strong>{{ $evaluado->nombre }} {{ $evaluado->apellidos }}</strong></p>

            @if($plantilla === 'socioeconomico')
                <p>Reciba un cordial saludo.</p>
                @if($reprogramada)
                    <p>Por este medio le informamos que su cita para realizar la <strong>{{ $tituloServicio }}</strong>, solicitada por la empresa <strong>{{ $empresa }}</strong>, fue <strong>reprogramada</strong>.</p>
                @else
                    <p>Por este medio le compartimos la confirmación de su cita para realizar la <strong>{{ $tituloServicio }}</strong>, solicitada por la empresa <strong>{{ $empresa }}</strong>.</p>
                @endif
            @elseif($reprogramada)
                <p>Reciba un cordial saludo. Por este medio le informamos que su cita para la <strong>{{ $tituloServicio }}</strong>, solicitada por <strong>{{ $empresa }}</strong>, fue <strong>reprogramada</strong>.</p>
            @else
                <p>Reciba un cordial saludo. Por este medio le confirmamos su cita para la <strong>{{ $tituloServicio }}</strong>, solicitada por <strong>{{ $empresa }}</strong>.</p>
            @endif

            @if($plantilla === 'socioeconomico')
                <div class="info-box">
                    <p>💼 <strong>Puesto al que aplica:</strong> {{ $puesto }}</p>
                    <p>📅 <strong>Fecha:</strong> {{ $fecha ?? 'N/A' }}</p>
                    <p>🕐 <strong>Hora:</strong> {{ $horaInicio ?? 'N/A' }}{{ $horaFin ? ' a '.$horaFin : '' }}</p>
                </div>
                <p class="section-title">MODALIDAD DE LA ENTREVISTA</p>
                @if($esVirtual)
                    <p>💻 <strong>Modalidad:</strong> Virtual</p>
                @else
                    <p>🏢 <strong>Modalidad:</strong> Presencial</p>
                    @if($mostrarSede)
                        <p>📍 <strong>Sede:</strong> {{ $sede }}</p>
                        <p>📌 <strong>Dirección:</strong> {{ $direccion }}</p>
                        @if(!empty($enlaceMaps))
                            <p><a href="{{ $enlaceMaps }}">Ver ubicación</a></p>
                        @endif
                    @endif
                @endif
            @else
                <div class="info-box">
                    <p>👔 <strong>Puesto al que aplica:</strong> {{ $puesto }}</p>
                    <p>📅 <strong>Fecha:</strong> {{ $fecha ?? 'N/A' }}</p>
                    <p>🕒 <strong>Hora:</strong> {{ $horaInicio ?? 'N/A' }}{{ $horaFin ? ' a '.$horaFin : '' }}</p>
                    <p>{{ $esVirtual ? '💻' : '📍' }} <strong>Modalidad:</strong> {{ $modalidad }}</p>
                    @if($mostrarSede)
                        <p>📍 <strong>Sede:</strong> {{ $sede }}</p>
                        <p>📍 <strong>Dirección:</strong> {{ $direccion }}</p>
                        @if(!empty($enlaceMaps))
                            <p><a href="{{ $enlaceMaps }}">Ver ubicación</a></p>
                        @endif
                    @endif
                </div>
            @endif

            @if($plantilla === 'vsa')
                <p class="section-title">CONDICIONES PARA REALIZAR SU PRUEBA VSA</p>
                <ul class="conditions">
                    <li>Debe estar en un lugar cerrado y privado, sin interrupciones.</li>
                    <li>Debe permanecer solo(a) durante la prueba.</li>
                    <li>No debe haber ruido ni distracciones.</li>
                    <li>No realizar la prueba al aire libre ni dentro de un vehículo.</li>
                    <li>Contar con una buena conexión a internet.</li>
                    <li>El micrófono y el audio de su dispositivo deben funcionar correctamente.</li>
                    <li>No debe presentar gripe ni síntomas respiratorios. Si está enfermo(a), contacte a REPRO para reprogramar.</li>
                    <li>Debe contar con disponibilidad de tiempo para completar la prueba.</li>
                </ul>
                <p class="section-title">DOCUMENTACIÓN PENDIENTE</p>
                <p>Adjunte sus documentos a través del enlace del formulario. Deben estar vigentes, completos y legibles.</p>
            @elseif($plantilla === 'socioeconomico')
                <p class="section-title">CONDICIONES PARA REALIZAR SU ENTREVISTA</p>
                @if($esVirtual)
                    <p><strong>Si su entrevista es VIRTUAL:</strong></p>
                    <ul class="conditions">
                        <li>Ubíquese en un lugar cerrado, privado y tranquilo, donde pueda conversar con comodidad.</li>
                        <li>Permanezca solo(a) durante la entrevista, para garantizar la privacidad y confidencialidad de la información.</li>
                        <li>Evite lugares con ruido, interrupciones o distracciones.</li>
                        <li>No realice la entrevista al aire libre, en vehículos o mientras se encuentra en movimiento.</li>
                        <li>Asegúrese de contar con buena conexión a internet durante toda la entrevista.</li>
                        <li>Utilice un dispositivo con cámara, micrófono y audio funcionando correctamente.</li>
                        <li>Procure ubicarse en un espacio con buena iluminación.</li>
                        <li>Tenga a la mano la documentación o información que pueda ser requerida durante la entrevista.</li>
                        <li>Cuente con disponibilidad de tiempo para completar el proceso sin interrupciones.</li>
                    </ul>
                @else
                    <p><strong>Si su entrevista es PRESENCIAL:</strong></p>
                    <ul class="conditions">
                        <li>Preséntese puntualmente en la sede indicada.</li>
                        <li>Cuente con disponibilidad de tiempo para completar la entrevista.</li>
                        <li>Tenga disponible la información necesaria para responder las consultas realizadas durante el proceso.</li>
                    </ul>
                @endif
                <p class="section-title">DOCUMENTACIÓN PENDIENTE</p>
                @if($esVirtual)
                    <p>Si su entrevista es virtual y aún tiene papelería pendiente, deberá adjuntarla directamente en el enlace que le fue enviado para completar su información.</p>
                @else
                    <p>Si su entrevista es presencial, puede adjuntar la documentación pendiente en el enlace enviado o llevarla el día de su entrevista.</p>
                @endif
                <p>Por favor, verifique que los documentos sean vigentes, completos y legibles.</p>
            @else
                <p class="section-title">CONDICIONES PARA REALIZAR SU PRUEBA DE POLÍGRAFO</p>
                <ul class="conditions">
                    <li>Descanse bien la noche anterior.</li>
                    <li>Aliméntese de forma normal; no debe presentarse en ayunas.</li>
                    <li>No ingiera alcohol.</li>
                    <li>Si toma medicamentos recetados, continúe su tratamiento e infórmelo al evaluador.</li>
                    <li>Use ropa cómoda.</li>
                    <li>Debe contar con disponibilidad de tiempo y presentarse puntual.</li>
                    <li>No debe presentar gripe, fiebre u otro malestar. Si está enfermo(a), contacte a REPRO para reprogramar.</li>
                </ul>
                <p class="section-title">DOCUMENTACIÓN PENDIENTE</p>
                <p>Adjunte sus documentos a través del enlace del formulario, o llévelos el día de la evaluación. Deben estar vigentes, completos y legibles.</p>
                <p>La prueba es voluntaria y requiere de su colaboración.</p>
            @endif

            @if($urlCuestionario)
                <p class="link-alt">Enlace para adjuntar documentos:<br>
                    <a href="{{ $urlCuestionario }}">{{ $urlCuestionario }}</a>
                </p>
            @endif

            @if($plantilla === 'socioeconomico')
                <div class="warning">
                    ⚠️ <strong>Importante:</strong> Si las condiciones indicadas no se cumplen, la entrevista podrá ser reprogramada para garantizar el adecuado desarrollo del proceso.
                </div>
                <p>La información proporcionada durante la entrevista será manejada bajo políticas de confidencialidad.</p>
                <p>Si tiene alguna duda o inconveniente, con gusto estamos a la orden.</p>
                <p><strong>REPRO</strong></p>
            @else
                <div class="warning">
                    Si no se cumplen las condiciones, la evaluación podrá reprogramarse.
                </div>
                <p>Atentamente,<br><strong>REPRO</strong></p>
            @endif
            @if($whatsappUrl)
                <p>WhatsApp: <a href="{{ $whatsappUrl }}">{{ $whatsappUrl }}</a></p>
            @endif
        </div>
        <div class="footer">
            <p>Este es un correo automático del sistema REPRO. No responda a este mensaje.</p>
        </div>
    </div>
</body>
</html>
