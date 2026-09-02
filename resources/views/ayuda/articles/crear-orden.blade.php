<div class="ayuda-articulo">
    <p class="lead">Solicite evaluaciones para uno o más candidatos desde el portal cliente.</p>

    <h5 id="vista-simulada"><i class="bi bi-display me-2"></i>Vista simulada</h5>
    @include('ayuda.partials.mock.screen', [
        'titulo' => 'Nueva Orden de Evaluación',
        'subtitulo' => 'Paso 1 de 3 — Datos de la orden',
        'innerView' => 'ayuda.partials.mock.form-crear-orden',
    ])

    <h5 id="acceso"><i class="bi bi-door-open me-2"></i>Acceso</h5>
    <p>Menú <strong>Órdenes → Nueva Orden</strong> (<code>/ordenes/create</code>). Requiere permiso de crear órdenes.</p>

    <h5 id="pasos"><i class="bi bi-list-ol me-2"></i>Paso a paso</h5>
    <div class="ayuda-flujo-steps">
        <div class="ayuda-paso"><span class="ayuda-paso-num">1</span><div><strong>Datos de la orden</strong><br>Sede REPRO responsable, tipos de servicio (polígrafo, VSA, etc.), tipos de formulario, prioridad e instrucciones.</div></div>
        <div class="ayuda-paso"><span class="ayuda-paso-num">2</span><div><strong>Evaluados</strong><br>Agregue candidatos con nombre, DPI, puesto, contacto y modalidad (presencial/virtual).</div></div>
        <div class="ayuda-paso"><span class="ayuda-paso-num">3</span><div><strong>Revisión</strong><br>Confirme datos antes de enviar.</div></div>
        <div class="ayuda-paso"><span class="ayuda-paso-num">4</span><div><strong>Envío</strong><br>La orden queda en estado «Orden recibida» y REPRO la procesa.</div></div>
    </div>

    <h5 id="despues"><i class="bi bi-check2-circle me-2"></i>Después de crear</h5>
    <ul>
        <li>Cada evaluado recibe un enlace único de cuestionario.</li>
        <li>Puede copiar el enlace desde el detalle de la orden y enviarlo al candidato.</li>
        <li>REPRO también puede reenviar el enlace por correo.</li>
    </ul>

    @include('ayuda.partials.callout', [
        'tipo' => 'warning',
        'titulo' => 'Importante:',
        'contenido' => 'El candidato debe llenar el formulario personalmente. Usted puede subir papelería adjunta, pero no sustituye al cuestionario en línea.',
    ])
</div>
