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

                <!-- Dashboard -->
                <li class="{{ Request::is('dashboard') ? 'active-page-link':''  }}">
                    <a href="{{ url('/dashboard') }}">
                        <i class="bi bi-house-fill"></i>
                        <span class="menu-text">Panel Principal</span>
                    </a>
                </li>

                <!-- Módulo de Órdenes -->
                <li class="menu-category">Órdenes</li>
                <li class="{{ Request::is('ordenes') && !Request::is('ordenes/create') ? 'active-page-link':''  }}">
                    <a href="{{ route('empresa.ordenes.index') }}">
                        <i class="bi bi-file-earmark-text"></i>
                        <span class="menu-text">Mis Órdenes</span>
                    </a>
                </li>
                <li class="{{ Request::is('ordenes/create') ? 'active-page-link':''  }}">
                    <a href="{{ route('ordenes.create') }}">
                        <i class="bi bi-file-plus-fill"></i>
                        <span class="menu-text">Nueva Orden</span>
                    </a>
                </li>

                <!-- Cuestionarios (solo lectura) -->
                <li class="menu-category">Cuestionarios</li>
                <li class="{{ Request::is('empresa/cuestionarios*') ? 'active-page-link':''  }}">
                    <a href="{{ url('empresa/cuestionarios') }}">
                        <i class="bi bi-clipboard-check"></i>
                        <span class="menu-text">Estado de Procesos</span>
                    </a>
                </li>

                <!-- Módulo de Reportes -->
                <li class="menu-category">Reportes</li>
                <li class="{{ Request::is('reportes/evaluaciones') ? 'active-page-link':''  }}">
                    <a href="{{ url('reportes/evaluaciones') }}">
                        <i class="bi bi-graph-up"></i>
                        <span class="menu-text">Mis Reportes</span>
                    </a>
                </li>

                <!-- Mi Empresa -->
                <li class="menu-category">Mi Empresa</li>
                <li class="{{ Request::is('empresa/mi-empresa') && !Request::is('empresa/mi-empresa/editar') ? 'active-page-link':''  }}">
                    <a href="{{ url('empresa/mi-empresa') }}">
                        <i class="bi bi-building"></i>
                        <span class="menu-text">Información</span>
                    </a>
                </li>

                <!-- Usuarios de empresa (solo para usuarios principales) -->
                @if(Auth::user()->principal == 1)
                <li class="{{ Request::is('empresa/usuarios*') ? 'active-page-link':''  }}">
                    <a href="{{ url('empresa/usuarios') }}">
                        <i class="bi bi-people"></i>
                        <span class="menu-text">Usuarios</span>
                    </a>
                </li>
                @endif

                <!-- WhatsApp sedes REPRO -->
                @if(isset($sedesWhatsApp) && $sedesWhatsApp->isNotEmpty())
                <li class="menu-category">Contacto</li>
                <li style="position:relative">
                    <div class="whatsapp-popup" id="whatsapp-submenu">
                        @foreach($sedesWhatsApp as $sedeWa)
                        <a href="https://wa.me/{{ preg_replace('/\D+/', '', $sedeWa->whatsapp) }}" target="_blank" rel="noopener noreferrer">
                            <i class="bi bi-whatsapp"></i> {{ $sedeWa->nombre }}
                        </a>
                        @endforeach
                    </div>
                    <a href="#" class="whatsapp-sidebar-btn" id="whatsapp-toggle">
                        <i class="bi bi-whatsapp"></i>
                        <span class="menu-text">WhatsApp</span>
                        <i class="bi bi-plus-lg wa-arrow"></i>
                    </a>
                </li>
                @endif
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

        // Toggle dropdown WhatsApp
        const waToggle = document.getElementById('whatsapp-toggle');
        if (waToggle) {
            waToggle.addEventListener('click', function(e) {
                e.preventDefault();
                const submenu = document.getElementById('whatsapp-submenu');
                const isOpen = submenu.style.display === 'block';
                submenu.style.display = isOpen ? 'none' : 'block';
                this.classList.toggle('open', !isOpen);
            });
            // Cerrar al hacer clic fuera
            document.addEventListener('click', function(e) {
                if (!waToggle.contains(e.target)) {
                    const submenu = document.getElementById('whatsapp-submenu');
                    if (submenu) { submenu.style.display = 'none'; }
                    waToggle.classList.remove('open');
                }
            });
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
    .sidebar-wrapper .whatsapp-sidebar-btn {
        background: #25D366 !important;
        color: #fff !important;
        border-radius: 6px;
        margin: 4px 10px;
        padding: 7px 12px !important;
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 600;
        font-size: 0.85rem;
    }
    .sidebar-wrapper .whatsapp-sidebar-btn:hover {
        background: #128C7E !important;
        color: #fff !important;
    }
    .sidebar-wrapper .whatsapp-sidebar-btn .wa-arrow {
        margin-left: auto;
        font-size: 0.9rem;
        transition: transform 0.2s;
    }
    .sidebar-wrapper .whatsapp-sidebar-btn.open .wa-arrow {
        transform: rotate(45deg);
    }
    .whatsapp-popup {
        display: none;
        position: absolute;
        bottom: calc(100% + 6px);
        left: 10px;
        right: 10px;
        background: #fff !important;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        z-index: 9999;
        overflow: hidden;
        border: 1px solid #d4edda;
    }
    .whatsapp-popup a {
        display: flex !important;
        align-items: center;
        gap: 8px;
        padding: 7px 12px !important;
        color: #1a1a1a !important;
        background: #fff !important;
        text-decoration: none;
        font-size: 0.83rem;
        border-bottom: 1px solid #f0f0f0 !important;
        border-radius: 0 !important;
        margin: 0 !important;
    }
    .whatsapp-popup a:last-child { border-bottom: none !important; }
    .whatsapp-popup a:hover {
        background: #e8f8ef !important;
        color: #128C7E !important;
    }
    .whatsapp-popup a i { color: #25D366 !important; }
</style>
