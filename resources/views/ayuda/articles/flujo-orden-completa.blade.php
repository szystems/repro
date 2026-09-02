<div class="ayuda-articulo">
    <p class="lead">Este flujo describe el ciclo de vida completo de una evaluación, desde la solicitud hasta la entrega de resultados.</p>

    <h5 id="diagrama"><i class="bi bi-diagram-3 me-2"></i>Diagrama del flujo</h5>
    @include('ayuda.partials.flujo-diagrama')

    <h5 id="pasos"><i class="bi bi-list-ol me-2"></i>Pasos detallados</h5>
    <div class="ayuda-flujo-steps">
        <div class="ayuda-paso"><span class="ayuda-paso-num">1</span><div><strong>Cliente crea la orden</strong><br>Portal cliente → Nueva Orden → datos de evaluados, tipos de servicio y formulario.</div></div>
        <div class="ayuda-paso"><span class="ayuda-paso-num">2</span><div><strong>REPRO recibe y procesa</strong><br>La orden aparece en Órdenes de Evaluación con estado «Orden recibida».</div></div>
        <div class="ayuda-paso"><span class="ayuda-paso-num">3</span><div><strong>Envío de enlace al candidato</strong><br>Copiar enlace, reenviar por correo o WhatsApp. Vigencia mínima 30 días.</div></div>
        <div class="ayuda-paso"><span class="ayuda-paso-num">4</span><div><strong>Candidato llena el cuestionario</strong><br>Verifica DPI → instrucciones → secciones del formulario → envío final.</div></div>
        <div class="ayuda-paso"><span class="ayuda-paso-num">5</span><div><strong>REPRO revisa y evalúa</strong><br>Edita respuestas si necesario, genera informe Word, programa cita si aplica.</div></div>
        <div class="ayuda-paso"><span class="ayuda-paso-num">6</span><div><strong>Documentos y papelería</strong><br>Cliente o REPRO sube DPI, antecedentes, etc. REPRO verifica documentos.</div></div>
        <div class="ayuda-paso"><span class="ayuda-paso-num">7</span><div><strong>Resultados</strong><br>REPRO sube resultado preliminar/final y controla visibilidad hacia el cliente.</div></div>
        <div class="ayuda-paso"><span class="ayuda-paso-num">8</span><div><strong>Cliente consulta</strong><br>Detalle de orden → descarga resultados cuando REPRO los habilita.</div></div>
    </div>

    <h5 id="estados"><i class="bi bi-flag me-2"></i>Estados clave a monitorear</h5>
    <div class="row g-2">
        <div class="col-md-4">
            <div class="border rounded p-2 h-100">
                <strong><i class="bi bi-flag text-primary me-1"></i>Evaluación</strong>
                <p class="small mb-0 text-muted">Progreso operativo del evaluado.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="border rounded p-2 h-100">
                <strong><i class="bi bi-ui-checks text-info me-1"></i>Formulario</strong>
                <p class="small mb-0 text-muted">Si el candidato llenó o no el cuestionario.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="border rounded p-2 h-100">
                <strong><i class="bi bi-calendar-event text-secondary me-1"></i>Programación</strong>
                <p class="small mb-0 text-muted">Cita agendada o pendiente.</p>
            </div>
        </div>
    </div>
</div>
