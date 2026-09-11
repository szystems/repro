<div class="ayuda-articulo">
    <p class="lead">Qué correos manda el portal, a quién y qué cambió en el mensaje al candidato. El remitente en producción es <strong>noreply@reprogt.com</strong>. Nadie lee esa bandeja: las respuestas deben ir a REPRO o a RRHH de la empresa.</p>

    <h5 id="candidato"><i class="bi bi-person me-2"></i>Correo al candidato</h5>
    <p>Al crear o agregar un evaluado con email, el candidato recibe el enlace del cuestionario. El recordatorio diario (08:00) se envía si faltan 3 o 1 días de vigencia y el formulario sigue incompleto.</p>
    <ul>
        <li>Incluye el <strong>puesto que solicita</strong>.</li>
        <li>Pide completarlo <strong>lo antes posible</strong>. Ya no muestra recuadro de «30 días restantes» ni una fecha límite.</li>
        <li>El enlace nuevo (o al pulsar <strong>Habilitar enlace</strong>) dura <strong>15 días</strong>. Los enlaces ya emitidos conservan su vencimiento original.</li>
        <li>El último día puede verse un aviso URGENTE en el portal; no es un countdown de 30 días.</li>
        <li>No se manda si el cuestionario ya está completo, el enlace está deshabilitado o la orden está cancelada.</li>
    </ul>
    <p>También puede reenviarse desde el detalle de la orden o el listado de candidatos. Alternativa: botón verde <strong>WhatsApp al candidato</strong> (si hay teléfono).</p>

    <h5 id="cita"><i class="bi bi-calendar-event me-2"></i>Cita</h5>
    <p>Al programar o reprogramar, el candidato recibe correo según tipo de servicio (VSA / Polígrafo / Socio) y modalidad (virtual o presencial).</p>

    <h5 id="usuarios"><i class="bi bi-person-plus me-2"></i>Alta de usuario</h5>
    <ul>
        <li>REPRO crea un usuario en Administración → Usuarios: llega correo con usuario y contraseña.</li>
        <li>El titular crea un trabajador en <strong>Mi Empresa → Usuarios</strong>: ahora también llega ese correo. Ya no hace falta pasar por «recuperar contraseña».</li>
    </ul>

    <h5 id="orden-sede"><i class="bi bi-building me-2"></i>Nueva orden (solo si la crea el cliente)</h5>
    <p>Si la empresa crea la orden y elige sede, el personal REPRO de esa sede recibe aviso. Si la orden la crea REPRO, ese correo <strong>no</strong> se manda (evita ruido interno).</p>

    <h5 id="resultados"><i class="bi bi-file-earmark-pdf me-2"></i>Resultados a la empresa</h5>
    <p>Cuando REPRO marca resultados o el informe preliminar como visibles, el correo (y la campana de esos avisos) no se manda a toda la empresa. Reclutador asignado y proceso confidencial son independientes:</p>
    <ul>
        <li>Si hay <strong>reclutador asignado</strong>, llega <strong>solo a esa persona</strong>, aunque el proceso no sea confidencial.</li>
        <li>Si no hay reclutador y la orden la creó la empresa, llega <strong>solo a quien la creó</strong>.</li>
        <li>Si la creó REPRO y no hay reclutador, llega al <strong>titular</strong> (gerente RRHH). Si no hay titular activo, se usa el correo de la ficha de la empresa.</li>
        <li>Si el proceso es <strong>confidencial</strong>, nunca se avisa a alguien que no pueda ver esa orden en SIGOR. El gerente sigue viendo la orden en el portal; no recibe el correo si no es el responsable.</li>
    </ul>

    <h5 id="campana"><i class="bi bi-bell me-2"></i>Solo campana (sin correo)</h5>
    <ul>
        <li>Orden creada → REPRO y empresa (notificación en el portal).</li>
        <li>Candidato terminó el cuestionario → REPRO y empresa (campana). El correo de «cuestionario completado» ya no se envía.</li>
    </ul>

    @include('ayuda.partials.callout', [
        'tipo' => 'info',
        'titulo' => 'Si no llega el correo:',
        'contenido' => 'Pida revisar spam y que el email del candidato o del responsable esté bien escrito. El correo de resultados llega al reclutador asignado (o a quien creó la orden / al titular), no a toda la empresa. Si REPRO ve un aviso de límite diario, el servicio de correo (Resend, 100/día) se saturó: el portal sigue; los mails se reanudan mañana. El enlace del candidato también se puede copiar o mandar por WhatsApp.',
    ])
</div>
