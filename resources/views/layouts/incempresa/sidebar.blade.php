<!-- Sidebar wrapper start -->
<nav class="sidebar-wrapper" id="sidebar">
    <!-- Sidebar header starts -->
    <div class="sidebar-header">
        <div class="sidebar-logo text-center pt-3 pb-3">
            @if(Auth::user()->empresa && Auth::user()->empresa->logo)
                <img src="{{ asset('assets/imgs/empresas/'.Auth::user()->empresa->logo) }}" alt="{{ Auth::user()->empresa->nombre }}" class="img-fluid" style="max-height: 60px;">
            @else
                <img src="{{ asset('assets/imgs/logos/logo.png') }}" alt="REPRO" class="img-fluid" style="max-height: 50px;">
            @endif
        </div>
    </div>
    <!-- Sidebar header ends -->

    <!-- Sidebar menu starts -->
    <div class="sidebar-menu">
        <div class="sidebarMenuScroll">
            <ul>
                <!-- Perfil del usuario -->
                <li class="active">
                    <a href="{{ url('show-user/'.Auth::user()->id) }}">
                        <span class="avatar">
                            @if (Auth::user()->fotografia != null)
                                <img src="{{ asset('assets/imgs/users/'.Auth::user()->fotografia) }}" alt="Usuario" class="img-thumbnail rounded-4 border-success m-2 img-fluid" style="height: 40px;"/>
                            @else
                                <img src="{{ asset('assets/imgs/users/usericon4.png') }}" alt="Usuario" class="img-thumbnail rounded-4 border-success m-2 img-fluid" style="height: 40px;"/>
                            @endif
                            <span class="status online"></span>
                        </span>
                        @php
                            $usuario = Auth::user()->name;
                            $nombre = explode(' ', trim($usuario));
                        @endphp
                        <span class="menu-text"><u><strong> {{ ucwords($nombre[0]) }}</strong></u></span>
                    </a>
                </li>
                <li class="menu-separator">
                    <hr>
                </li>

                <li class="{{ Request::is('dashboard') ? 'active-page-link':''  }}">
                    <a href="{{ url('/dashboard') }}">
                        <i class="bi bi-house-fill"></i>
                        <span class="menu-text">Panel Principal</span>
                    </a>
                </li>

                <!-- Módulo de Evaluaciones -->
                <li class="menu-category">Evaluaciones</li>
                <li class="{{ Request::is('evaluaciones','show-evaluacion/*') ? 'active-page-link':''  }}">
                    <a href="{{ url('evaluaciones') }}">
                        <i class="bi bi-clipboard-data"></i>
                        <span class="menu-text">Ver Evaluaciones</span>
                    </a>
                </li>
                <li class="{{ Request::is('ordenes','show-orden/*','add-orden','edit-orden/*') ? 'active-page-link':''  }}">
                    <a href="{{ url('ordenes') }}">
                        <i class="bi bi-file-earmark-text"></i>
                        <span class="menu-text">Órdenes de Evaluación</span>
                    </a>
                </li>
                <li class="{{ Request::is('add-orden') ? 'active-page-link':''  }}">
                    <a href="{{ url('add-orden') }}">
                        <i class="bi bi-file-plus-fill"></i>
                        <span class="menu-text">Nueva Orden</span>
                    </a>
                </li>

                <!-- Módulo de Evaluados -->
                <li class="menu-category">Personas</li>
                <li class="{{ Request::is('evaluados','show-evaluado/*','add-evaluado','edit-evaluado/*') ? 'active-page-link':''  }}">
                    <a href="{{ url('evaluados') }}">
                        <i class="bi bi-person-vcard"></i>
                        <span class="menu-text">Personas Evaluadas</span>
                    </a>
                </li>
                <li class="{{ Request::is('add-evaluado') ? 'active-page-link':''  }}">
                    <a href="{{ url('add-evaluado') }}">
                        <i class="bi bi-person-plus"></i>
                        <span class="menu-text">Nuevo Evaluado</span>
                    </a>
                </li>

                <!-- Módulo de Reportes -->
                <li class="menu-category">Reportes</li>
                <li class="{{ Request::is('reportes/empresa') ? 'active-page-link':''  }}">
                    <a href="{{ url('reportes/empresa') }}">
                        <i class="bi bi-graph-up"></i>
                        <span class="menu-text">Estadísticas</span>
                    </a>
                </li>

                <!-- Administradores de empresa (solo para usuarios principales) -->
                @if(Auth::user()->principal == 1)
                <li class="menu-category">Administración</li>
                <li class="{{ Request::is('empresa-users') ? 'active-page-link':''  }}">
                    <a href="{{ url('empresa-users') }}">
                        <i class="bi bi-people"></i>
                        <span class="menu-text">Usuarios de la Empresa</span>
                    </a>
                </li>
                @endif

                <!-- Información de Empresa -->
                <li class="menu-category">Mi Empresa</li>
                <li class="{{ Request::is('mi-empresa') ? 'active-page-link':''  }}">
                    <a href="{{ url('mi-empresa') }}">
                        <i class="bi bi-building"></i>
                        <span class="menu-text">Información de Empresa</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <!-- Sidebar menu ends -->

    <!-- Sidebar footer starts -->
    <div class="sidebar-footer">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <span class="small text-muted"><a href="https://szystems.com" target="_blank" rel="noopener noreferrer">Szystems v1.0.0</a></span>
            </div>
            <div>
                <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                   class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Cerrar sesión">
                    <i class="bi bi-power"></i>
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </div>
        </div>
    </div>
    <!-- Sidebar footer ends -->
</nav>
<!-- Sidebar wrapper end -->

<!-- JavaScript para el sidebar collapse -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebarCollapse = document.getElementById('sidebarCollapse');
        if (sidebarCollapse) {
            sidebarCollapse.addEventListener('click', function() {
                document.getElementById('sidebar').classList.toggle('collapsed');
                // Guardar el estado en localStorage
                if (document.getElementById('sidebar').classList.contains('collapsed')) {
                    localStorage.setItem('sidebar-collapsed', 'true');
                } else {
                    localStorage.setItem('sidebar-collapsed', 'false');
                }
            });
        }

        // Restaurar el estado del sidebar
        if (localStorage.getItem('sidebar-collapsed') === 'true') {
            document.getElementById('sidebar').classList.add('collapsed');
        }
    });
</script>

<!-- Añadir estos estilos en su archivo CSS o en un bloque de estilo -->
<style>
    .sidebar-wrapper {
        transition: all 0.3s ease;
    }
    .sidebar-wrapper .menu-category {
        font-size: 0.8rem;
        color: #6c757d;
        font-weight: 600;
        padding: 12px 15px 5px;
        text-transform: uppercase;
    }
    .sidebar-wrapper .menu-separator {
        padding: 0 15px;
    }
    .sidebar-wrapper.collapsed {
        width: 70px;
    }
    .sidebar-wrapper.collapsed .menu-text,
    .sidebar-wrapper.collapsed .menu-category,
    .sidebar-wrapper.collapsed .menu-arrow,
    .sidebar-wrapper.collapsed .sidebar-submenu,
    .sidebar-wrapper.collapsed .sidebar-logo img,
    .sidebar-wrapper.collapsed .sidebar-footer span {
        display: none;
    }
    .sidebar-wrapper.collapsed .sidebar-toggle {
        text-align: center;
        width: 100%;
    }
    .sidebar-wrapper.collapsed + .content-wrapper {
        margin-left: 70px;
    }
    .sidebar-wrapper .sidebar-footer {
        padding: 10px 15px;
        border-top: 1px solid rgba(0,0,0,0.1);
        position: absolute;
        bottom: 0;
        width: 100%;
    }
    .sidebar-wrapper .active-dropdown i.menu-arrow {
        transform: rotate(180deg);
    }
</style>
