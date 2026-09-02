<div class="ayuda-articulo">
    <p class="lead">La pantalla de detalle de orden (<code>/ordenes/{id}</code>) concentra toda la gestión de cada candidato evaluado.</p>

    <h5 id="vista-simulada"><i class="bi bi-display me-2"></i>Vista simulada</h5>
    @include('ayuda.partials.mock.screen', [
        'titulo' => 'ORD-2026-0042 — Detalle de orden',
        'subtitulo' => 'PRUEBA 1 · Polígrafo',
        'innerView' => 'ayuda.partials.mock.orden-evaluado',
    ])

    <h5 id="cabecera"><i class="bi bi-card-heading me-2"></i>Cabecera de la orden</h5>
    <ul>
        <li>Código de orden, empresa, sede, tipos de servicio y formulario.</li>
        <li>Botones: <strong>Volver</strong>, <strong>Orden de Servicio</strong> (PDF), <strong>Archivar</strong> (solo administrador). Guía: <a href="{{ route('ayuda.show', 'archivar-ordenes') }}">Archivar órdenes</a>.</li>
        <li><strong>Expandir todos / Colapsar todos</strong> — abre o cierra todos los evaluados a la vez.</li>
    </ul>

    <h5 id="accordion"><i class="bi bi-person-badge me-2"></i>Accordion de cada evaluado</h5>
    <p>Al expandir un evaluado verá las secciones principales. Use el acordeón inferior para el detalle de cada bloque.</p>

    <div class="accordion mb-3" id="detalleEvaluadoAyuda">
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#det-estados">
                    <i class="bi bi-flag me-2"></i>Estados (evaluación, formulario, programación)
                </button>
            </h2>
            <div id="det-estados" class="accordion-collapse collapse show" data-bs-parent="#detalleEvaluadoAyuda">
                <div class="accordion-body">
                    <ul class="mb-0">
                        <li><strong>Estado de evaluación</strong> — Cancelado, Desistió, En Proceso, Entregado…</li>
                        <li><strong>Estado de formulario</strong> — @include('ayuda.partials.estado-badge', ['tipo' => 'pendiente']) @include('ayuda.partials.estado-badge', ['tipo' => 'link_enviado']) @include('ayuda.partials.estado-badge', ['tipo' => 'vencido'])</li>
                        <li><strong>Estado de programación</strong> — Sin programar, Programado, Contactado…</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#det-enlace">
                    <i class="bi bi-link-45deg me-2"></i>Enlace, documentos y resultados
                </button>
            </h2>
            <div id="det-enlace" class="accordion-collapse collapse" data-bs-parent="#detalleEvaluadoAyuda">
                <div class="accordion-body">
                    <ul class="mb-0">
                        <li><strong>Enlace del candidato</strong> — copiar, correo, invalidar/habilitar. Ver guía <a href="{{ route('ayuda.show', 'enlaces-candidato') }}">Enlaces del candidato</a>.</li>
                        <li><strong>Documentos</strong> — subir, verificar, vista previa y descarga.</li>
                        <li><strong>Informe Word</strong> — descarga directa del .docx.</li>
                        <li><strong>Archivos de resultado</strong> — preliminar y final (PDF).</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#det-interno">
                    <i class="bi bi-shield-lock me-2"></i>Información interna REPRO
                </button>
            </h2>
            <div id="det-interno" class="accordion-collapse collapse" data-bs-parent="#detalleEvaluadoAyuda">
                <div class="accordion-body">
                    <ul class="mb-0">
                        <li><strong>Programación</strong> — botón «Programar cita» si no hay cita asignada.</li>
                        <li><strong>Informe preliminar / observaciones</strong> — redacción interna REPRO (no visible al cliente).</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <h5 id="observacion"><i class="bi bi-chat-left-text me-2"></i>Observación visible para la empresa</h5>
    <p>Campo editable que el cliente puede ver en su portal. Use para comunicar avances sin datos confidenciales.</p>

    <h5 id="archivar"><i class="bi bi-archive me-2"></i>Archivar la orden</h5>
    <p>Solo el administrador. Al archivar, la orden y sus evaluados dejan de salir en candidatos, historial DPI, calendario y reportes. El expediente se conserva. Detalle: <a href="{{ route('ayuda.show', 'archivar-ordenes') }}">Archivar órdenes</a>.</p>
</div>
