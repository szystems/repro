<!-- App Footer start -->
<div class="app-footer">
    <div class="footer-content">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
            <div class="footer-left">
                <span class="d-inline-block">
                    &copy; {{ date('Y') }} <strong>Repro</strong> - Todos los derechos reservados
                </span>
                <span class="d-inline-block ms-2 badge bg-info-transparent text-info">
                    <i class="bi bi-code-slash"></i> v1.0.0
                </span>
            </div>

            <div class="footer-links d-flex flex-wrap gap-3 justify-content-center">
                <a href="{{ url('dashboard') }}">
                    <i class="bi bi-house-door"></i> Inicio
                </a>
                @if (Auth::user()->role_as != 1)
                <a href="{{ url('config') }}">
                    <i class="bi bi-gear"></i> Configuración
                </a>
                @endif
            </div>

            <div class="footer-right text-md-end">
                <span>{{ __('Desarrollado por') }}
                    <a href="https://www.szystems.com" class="fw-bold" target="_blank" rel="noopener">
                        Szystems <i class="bi bi-box-arrow-up-right small"></i>
                    </a>
                </span>
            </div>
        </div>
    </div>
</div>
<!-- App footer end -->

<style>
.app-footer {
    padding: 1rem;
    border-top: 1px solid rgba(0, 0, 0, 0.1);
    background-color: #f8f9fa;
    margin-top: auto;
    position: relative;
    bottom: 0;
    width: 100%;
}

.app-footer .footer-content {
    max-width: 100%;
    margin: 0 auto;
}

.app-footer a {
    color: #495057;
    text-decoration: none;
    transition: color 0.2s ease;
}

.app-footer a:hover {
    color: #0d6efd;
}

@media (max-width: 767.98px) {
    .app-footer .footer-content > div {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
    }

    .app-footer .footer-left,
    .app-footer .footer-right {
        margin-bottom: 0.5rem;
    }

    .app-footer .footer-links {
        margin: 0.5rem 0;
    }
}
</style>
