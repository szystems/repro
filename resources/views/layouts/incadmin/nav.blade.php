<!-- Page header starts -->
<div class="page-header">

    <div class="toggle-sidebar m-3" id="toggle-sidebar">
        <i class="bi bi-list"></i>
    </div>

    <!-- Sidebar brand starts -->
    <div class="brand">
        <a href="{{ url('dashboard') }}" class="logo mb-3 mt-1 align-self-center d-flex justify-content-center">
            <div class="border border-primary rounded p-2" style="background-color: #f8f9fa; width: 100%;">
                <img src="{{ asset('img/logos/logoreproxelahorizontal.png') }}" class="d-none d-md-block img-fluid" alt="Repro" />
                <img src="{{ asset('img/logos/logo.png') }}" class="d-block d-md-none mx-auto" style="height: 36px;" alt="Repro" />
            </div>
        </a>
    </div>
    <!-- Sidebar brand ends -->

    <!-- Header actions container start -->
    <div class="header-actions-container">

        <!-- Header actions start -->
        <div class="header-actions d-flex gap-3">
            @include('partials._notificaciones_bell')

            @if(Auth::user()->role_as != 1)
            <a href="{{ url('config') }}" class="header-action-link" data-bs-toggle="tooltip" data-bs-placement="bottom"
                data-bs-title="Configuración">
                <i class="bi bi-gear fs-5"></i>
            </a>
            @endif

            {{-- <a href="{{ url('ventas') }}" class="header-action-link" data-bs-toggle="tooltip" data-bs-placement="bottom"
                data-bs-title="Ventas">
                <i class="bi bi-cash-stack fs-5"></i>
            </a>

            <a href="{{ url('inventario') }}" class="header-action-link" data-bs-toggle="tooltip" data-bs-placement="bottom"
                data-bs-title="Inventario">
                <i class="bi bi-box-seam fs-5"></i>
            </a> --}}
        </div>
        <!-- Header actions end -->

        <!-- Header profile start -->
        <div class="header-profile d-flex align-items-center">
            <div class="dropdown">
                <a href="#" id="userSettings" class="user-settings" data-bs-toggle="dropdown" aria-haspopup="true">
                    @php
                        $usuario = Auth::user()->name;
                        $nombre = explode(' ', trim($usuario));
                        $rolUsuario = Auth::user()->role_as == 1 ? 'Administrador' : 'Usuario';
                    @endphp
                    <span class="user-name d-none d-md-block">{{ ucwords($nombre[0]) }} <small class="text-muted">({{ $rolUsuario }})</small></span>
                    <span class="avatar">
                        @if (Auth::user()->fotografia != null)
                            <img src="{{ asset('assets/imgs/users/'.Auth::user()->fotografia) }}" alt="Usuario" class="img-fluid rounded-circle" />
                        @else
                            <img src="{{ asset('assets/imgs/users/usericon4.png') }}" alt="Usuario" class="img-fluid rounded-circle" />
                        @endif
                        <span class="status online"></span>
                    </span>
                </a>
                <div class="dropdown-menu dropdown-menu-end shadow-lg" aria-labelledby="userSettings">
                    <div class="dropdown-header d-flex align-items-center">
                        <div class="user-avatar me-3">
                            @if (Auth::user()->fotografia != null)
                                <img src="{{ asset('assets/imgs/users/'.Auth::user()->fotografia) }}" alt="Usuario" class="img-fluid rounded-circle" width="40" />
                            @else
                                <img src="{{ asset('assets/imgs/users/usericon4.png') }}" alt="Usuario" class="img-fluid rounded-circle" width="40" />
                            @endif
                        </div>
                        <div>
                            <h6 class="mb-0">{{ Auth::user()->name }}</h6>
                            <p class="mb-0 small text-muted">{{ Auth::user()->email }}</p>
                        </div>
                    </div>
                    <div class="dropdown-divider"></div>
                    <div class="header-profile-actions">
                        <a href="{{ url('show-user/' . Auth::id()) }}" class="dropdown-item">
                            <i class="bi bi-person-lines-fill"></i>&ensp;Mi Perfil
                        </a>
                        <a href="{{ url('edit-user/' . Auth::id()) }}" class="dropdown-item">
                            <i class="bi bi-pencil-square"></i>&ensp;Editar Perfil
                        </a>
                        @if(Auth::user()->role_as != 1)
                        <a href="{{ url('config') }}" class="dropdown-item">
                            <i class="bi bi-gear"></i>&ensp;Configuración
                        </a>
                        @endif
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-danger" href="{{ route('logout') }}"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="bi bi-box-arrow-right"></i>&ensp;Cerrar Sesión
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- Header profile end -->

    </div>
    <!-- Header actions container end -->

</div>
<!-- Page header ends -->

<style>
.header-action-link {
    color: #495057;
    padding: 0.5rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}

.header-action-link:hover {
    background-color: #e9ecef;
    color: #0d6efd;
}

.header-profile-actions .dropdown-item {
    padding: 0.5rem 1rem;
}

.header-profile-actions .dropdown-item:hover {
    background-color: #f8f9fa;
}

.dropdown-header {
    padding: 1rem;
}

.user-settings:hover {
    text-decoration: none;
}

.user-name {
    margin-right: 0.5rem;
}

/* Corrección para dispositivos móviles */
@media (max-width: 767.98px) {
    .header-actions {
        gap: 0.5rem !important;
    }

    .header-action-link {
        padding: 0.25rem;
    }
}
</style>

<!-- Initialize tooltips -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
