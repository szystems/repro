<div class="ayuda-articulo">
    <p class="lead">Cada evaluado en una orden tiene un enlace único para acceder al cuestionario. REPRO y el cliente pueden gestionar ese enlace desde el detalle de la orden.</p>

    <h5 id="vista-simulada"><i class="bi bi-display me-2"></i>Vista simulada</h5>
    @include('ayuda.partials.mock.screen', [
        'titulo' => 'Detalle de orden — Enlace del candidato',
        'subtitulo' => 'ORD-2026-0042 · Juan Pérez López',
        'innerView' => 'ayuda.partials.mock.botones-enlace',
    ])

    <h5 id="vigencia"><i class="bi bi-calendar me-2"></i>Vigencia del enlace</h5>
    <ul>
        <li>Al crear un evaluado, el enlace tiene vigencia según configuración del sistema (mínimo <strong>30 días</strong>).</li>
        <li>En el detalle de la orden verá <em>Vence: DD/MM/AAAA</em> junto al enlace.</li>
        <li>Si el enlace fue invalidado, aparece el botón <strong>Habilitar enlace</strong> con la duración configurada (ej. 31 días).</li>
    </ul>
    <p class="mb-0">Estados típicos del formulario:
        @include('ayuda.partials.estado-badge', ['tipo' => 'link_enviado'])
        @include('ayuda.partials.estado-badge', ['tipo' => 'vencido'])
        @include('ayuda.partials.estado-badge', ['tipo' => 'recibido'])
    </p>

    <h5 id="acciones"><i class="bi bi-hand-index me-2"></i>Acciones disponibles</h5>
    <p>Use la tabla de botones al final de este artículo como referencia rápida. Los números en rojo de la vista simulada coinciden con la tabla.</p>

    <h5 id="flujo-vencido"><i class="bi bi-arrow-repeat me-2"></i>Flujo típico: enlace vencido o bloqueado</h5>
    <div class="ayuda-flujo-steps">
        <div class="ayuda-paso"><span class="ayuda-paso-num">1</span><div>Identifique al evaluado con estado @include('ayuda.partials.estado-badge', ['tipo' => 'vencido'])</div></div>
        <div class="ayuda-paso"><span class="ayuda-paso-num">2</span><div>Confirme con REPRO si debe rehabilitarse el enlace.</div></div>
        <div class="ayuda-paso"><span class="ayuda-paso-num">3</span><div>Pulse <strong>Habilitar enlace</strong> — se extiende la vigencia y se conserva el progreso parcial del candidato.</div></div>
        <div class="ayuda-paso"><span class="ayuda-paso-num">4</span><div>Reenvíe el enlace al candidato por correo o copiándolo manualmente.</div></div>
    </div>

    @include('ayuda.partials.callout', [
        'tipo' => 'info',
        'contenido' => '<strong>Invalidar</strong> bloquea temporalmente. <strong>Marcar recibido</strong> cierra definitivamente el acceso del candidato.',
    ])
</div>
