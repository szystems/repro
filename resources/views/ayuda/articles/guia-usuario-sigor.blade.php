<div class="ayuda-articulo">
    <p class="lead">Guía de usuario SIGOR enviada por REPRO. Puede leerla aquí en el navegador y, si la necesita, descargarla.</p>

    <div class="d-flex flex-wrap gap-2 mb-3">
        <a href="{{ route('ayuda.guia-sigor') }}" target="_blank" rel="noopener noreferrer" class="btn btn-primary btn-sm">
            <i class="bi bi-eye me-1"></i> Abrir en el navegador
        </a>
        <a href="{{ route('ayuda.guia-sigor.descargar') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-download me-1"></i> Descargar PDF
        </a>
    </div>

    <div class="border rounded overflow-hidden bg-light">
        <iframe
            src="{{ route('ayuda.guia-sigor') }}"
            title="Guía de usuario SIGOR"
            style="width: 100%; height: 80vh; border: 0;"
        ></iframe>
    </div>
</div>
