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
            <a href="{{ url('configs') }}" class="header-action-link" data-bs-toggle="tooltip" data-bs-placement="bottom"
                data-bs-title="Configuración">
                <i class="bi bi-gear fs-5"></i>
            </a>
            @endif

            <!-- <a href="{{ url('ventas') }}" class="header-action-link" data-bs-toggle="tooltip" data-bs-placement="bottom"
                data-bs-title="Ventas">
                <i class="bi bi-cash-stack fs-5"></i>
            </a>

            <a href="{{ url('inventario') }}" class="header-action-link" data-bs-toggle="tooltip" data-bs-placement="bottom"
                data-bs-title="Inventario">
                <i class="bi bi-box-seam fs-5"></i>
            </a>  -->
        </div>
        <!-- Header actions end -->

        <!-- Header profile start -->
        <div class="header-profile d-flex align-items-center">
            <!-- Reloj y fecha -->
            <span class="navbar-text d-none d-lg-block me-3">
                <span class="badge bg-light text-success" id="reloj"></span>
            </span>
            <!-- Información de usuario y menú desplegable -->
            <div class="dropdown">
                <a class="nav-link dropdown-toggle text-primary d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    @if(Auth::user()->fotografia)
                        <img src="{{ asset('assets/imgs/users/'.Auth::user()->fotografia) }}" class="rounded-circle me-2" width="32" height="32" alt="{{ Auth::user()->name }}">
                    @else
                        <img src="{{ asset('assets/imgs/users/usericon4.png') }}" class="rounded-circle me-2" width="32" height="32" alt="Usuario">
                    @endif
                    <span class="d-none d-md-inline">{{ Auth::user()->name }}</span>
                </a>
                <div class="dropdown-menu dropdown-menu-end">
                    <div class="px-3 py-2 mb-2">
                        <div class="d-flex align-items-center">
                            @if(Auth::user()->fotografia)
                                <img src="{{ asset('assets/imgs/users/'.Auth::user()->fotografia) }}" class="rounded-circle me-2" width="42" height="42" alt="{{ Auth::user()->name }}">
                            @else
                                <img src="{{ asset('assets/imgs/users/usericon4.png') }}" class="rounded-circle me-2" width="42" height="42" alt="Usuario">
                            @endif
                            <div>
                                <h6 class="mb-0">{{ Auth::user()->name }}</h6>
                                <small class="text-muted">{{ Auth::user()->email }}</small>
                                <div class="mt-1">
                                    <span class="badge bg-success">
                                        {{ Auth::user()->cargo ?? 'Usuario Empresa' }}
                                    </span>
                                    @if(Auth::user()->principal == 1)
                                        <span class="badge bg-warning text-dark">Principal</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="{{ url('show-user/'.Auth::user()->id) }}">
                        <i class="bi bi-person-fill me-2"></i> Mi Perfil
                    </a>
                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                        <i class="bi bi-key-fill me-2"></i> Cambiar Contraseña
                    </a>
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
        const toggleSidebar = document.getElementById('toggle-sidebar');
        const pageWrapper = document.querySelector('.page-wrapper');

        if (toggleSidebar && pageWrapper) {
            toggleSidebar.addEventListener('click', function() {
                pageWrapper.classList.toggle('toggled');
            });
        } else {
            console.error('No se encontró el botón toggle-sidebar o el contenedor page-wrapper.');
        }
    });
</script>
