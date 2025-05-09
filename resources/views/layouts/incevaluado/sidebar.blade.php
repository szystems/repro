<!-- Sidebar start -->
<div class="evaluado-sidebar">
    <div class="list-group shadow-sm mb-4">
        <a href="{{ url('dashboard') }}" class="list-group-item list-group-item-action {{ Request::is('dashboard') ? 'active' : '' }}">
            <i class="bi bi-house-door-fill me-2"></i> Inicio
        </a>
        <a href="{{ url('mis-evaluaciones') }}" class="list-group-item list-group-item-action {{ Request::is('mis-evaluaciones*') ? 'active' : '' }}">
            <i class="bi bi-clipboard-check me-2"></i> Mis Evaluaciones
        </a>
        <a href="{{ url('llenar-cuestionario') }}" class="list-group-item list-group-item-action {{ Request::is('llenar-cuestionario*') ? 'active' : '' }}">
            <i class="bi bi-pencil-square me-2"></i> Completar Cuestionario
        </a>
        <a href="{{ url('show-user/'.Auth::user()->id) }}" class="list-group-item list-group-item-action {{ Request::is('show-user/'.Auth::user()->id) ? 'active' : '' }}">
            <i class="bi bi-person-circle me-2"></i> Mi Perfil
        </a>
    </div>

    <!-- Información del usuario -->
    <div class="card mb-4">
        <div class="card-header bg-secondary text-white">
            <h6 class="mb-0"><i class="bi bi-person-badge me-2"></i>Mi Información</h6>
        </div>
        <div class="card-body">
            <div class="text-center mb-3">
                @if(Auth::user()->fotografia)
                    <img src="{{ asset('assets/imgs/users/'.Auth::user()->fotografia) }}" class="rounded-circle mb-2" width="80" height="80">
                @else
                    <img src="{{ asset('assets/imgs/users/usericon4.png') }}" class="rounded-circle mb-2" width="80" height="80">
                @endif
                <h6 class="mb-0">{{ Auth::user()->name }}</h6>
                <p class="text-muted small">{{ Auth::user()->email }}</p>
            </div>

            <div class="d-grid">
                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                    <i class="bi bi-key me-1"></i> Cambiar Contraseña
                </button>
            </div>
        </div>
    </div>

    <!-- Ayuda y soporte -->
    <div class="card bg-light">
        <div class="card-body">
            <h6><i class="bi bi-question-circle me-2"></i>¿Necesitas ayuda?</h6>
            <p class="small mb-2">Si tienes dudas sobre el proceso de evaluación, contacta con el personal autorizado.</p>
            <div class="d-grid">
                <a href="{{ url('contacto') }}" class="btn btn-sm btn-secondary">
                    <i class="bi bi-envelope me-1"></i> Contactar Soporte
                </a>
            </div>
        </div>
    </div>
</div>
<!-- Sidebar end -->

<style>
    .evaluado-sidebar .list-group-item.active {
        background-color: #6c757d;
        border-color: #6c757d;
        color: #fff;
    }

    .evaluado-sidebar .list-group-item:hover:not(.active) {
        background-color: #e9ecef;
    }

    @media (max-width: 767.98px) {
        .evaluado-sidebar {
            margin-bottom: 2rem;
        }
    }
</style>
