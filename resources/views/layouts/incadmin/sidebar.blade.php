<!-- Sidebar wrapper start -->
<nav class="sidebar-wrapper" id="sidebar">
    <!-- Sidebar header starts -->
    <div class="sidebar-header">
        {{-- <div class="sidebar-logo">
            <a href="{{ url('/dashboard') }}">
                <img src="{{ asset('img/logos/logoreproxelahorizontal.png') }}" alt="Logo" class="img-fluid" width="120">
            </a>
        </div> --}}
        {{-- <br>
        <div class="sidebar-toggle d-none d-md-flex">
            <button type="button" id="sidebarCollapse" class="btn btn-sm btn-light">
                <i class="bi bi-arrow-left-right"></i>
            </button>
        </div> --}}
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
                        <i class="bi bi-house"></i>
                        <span class="menu-text">Panel de Control</span>
                    </a>
                </li>

                <!-- Módulo de Evaluaciones - Para todos excepto usuarios evaluados -->
                @if(Auth::user()->role_as >= 1)
                <li class="menu-category">Evaluaciones</li>
                <li class="{{ Request::is('cuestionarios','show-cuestionario/*','add-cuestionario','edit-cuestionario/*') ? 'active-page-link':''  }}">
                    <a href="{{ url('cuestionarios') }}">
                        <i class="bi bi-card-checklist"></i>
                        <span class="menu-text">Gestión de Cuestionario – Candidatos</span>
                    </a>
                </li>

                @if(Auth::user()->role_as >= 2)
                <li class="{{ Request::is('cuestionarios/historial-dpi') ? 'active-page-link':''  }}">
                    <a href="{{ route('admin.cuestionarios.historial-dpi') }}">
                        <i class="bi bi-search"></i>
                        <span class="menu-text">Historial por DPI</span>
                    </a>
                </li>
                @endif

                <!-- Órdenes de evaluación - Solo para usuarios empresa y superiores -->
                <li class="{{ Request::is('ordenes','show-orden/*','add-orden','edit-orden/*') ? 'active-page-link':''  }}">
                    <a href="{{ url('ordenes') }}">
                        <i class="bi bi-file-earmark-text"></i>
                        <span class="menu-text">Órdenes de Evaluación</span>
                    </a>
                </li>
                @endif

                <!-- Módulo de Empresas (para Admin y usuarios de Repro) -->
                @if(Auth::user()->role_as >= 2)
                <li class="menu-category">Empresas</li>
                <li class="{{ Request::is('empresas','show-empresa/*','add-empresa','edit-empresa/*') ? 'active-page-link':''  }}">
                    <a href="{{ url('empresas') }}">
                        <i class="bi bi-building"></i>
                        <span class="menu-text">Empresas</span>
                    </a>
                </li>
                @endif

                <!-- Módulo de Sedes (solo REPRO, role_as >= 3) -->
                @if(Auth::user()->role_as >= 3)
                <li class="{{ Request::is('sedes','show-sede/*','add-sede','edit-sede/*') ? 'active-page-link':''  }}">
                    <a href="{{ url('sedes') }}">
                        <i class="bi bi-geo-alt-fill"></i>
                        <span class="menu-text">Sedes REPRO</span>
                    </a>
                </li>
                @endif

                <!-- Módulo de Calendario de Programación (role_as >= 2) -->
                @if(Auth::user()->role_as >= 2)
                <li class="menu-category">Programación</li>
                <li class="{{ Request::is('calendario','calendario/*') ? 'active-page-link':''  }}">
                    <a href="{{ url('calendario') }}">
                        <i class="bi bi-calendar3"></i>
                        <span class="menu-text">Calendario</span>
                    </a>
                </li>
                @endif

                <!-- Módulo de Reportes -->
                @if(Auth::user()->role_as >= 1)
                <li class="menu-category">Reportes</li>
                <li class="sidebar-dropdown">
                    <a href="#" class="{{ Request::is('reportes/*') ? 'active-dropdown':''  }}">
                        <i class="bi bi-graph-up"></i>
                        <span class="menu-text">Informes y Estadísticas</span>
                        <i class="bi bi-chevron-down menu-arrow"></i>
                    </a>
                    <div class="sidebar-submenu">
                        <ul>
                            <li class="{{ Request::is('reportes/evaluaciones') ? 'active-page-link':''  }}">
                                <a href="{{ url('reportes/evaluaciones') }}"><i class="bi bi-file-bar-graph"></i> Estadísticas de Evaluaciones</a>
                            </li>
                            @if(Auth::user()->role_as >= 2)
                            <li class="{{ Request::is('reportes/empresas') ? 'active-page-link':''  }}">
                                <a href="{{ url('reportes/empresas') }}"><i class="bi bi-building-fill-check"></i> Empresas y Evaluaciones</a>
                            </li>
                            @endif
                        </ul>
                    </div>
                </li>
                @endif

                <!-- Administración -->
                @if(Auth::user()->role_as >= 3)
                <li class="menu-category">Administración</li>
                <li class="sidebar-dropdown">
                    <a href="#" class="{{ Request::is('users','show-user/*','add-user','edit-user/*') ? 'active-dropdown':''  }}">
                        <i class="bi bi-shield-shaded"></i>
                        <span class="menu-text">Seguridad</span>
                        <i class="bi bi-chevron-down menu-arrow"></i>
                    </a>
                    <div class="sidebar-submenu">
                        <ul>
                            <li class="{{ Request::is('users','show-user/*','add-user','edit-user/*') ? 'active-page-link':''  }}">
                                <a href="{{ url('users') }}"><i class="bi bi-people"></i> Usuarios</a>
                            </li>
                            <li class="{{ Request::is('admin/roles','admin/roles/*') ? 'active-page-link':''  }}">
                                <a href="{{ url('admin/roles') }}"><i class="bi bi-shield-check"></i> Roles y Permisos</a>
                            </li>
                        </ul>
                    </div>
                </li>
                <li class="{{ Request::is('config') ? 'active-page-link':''  }}">
                    <a href="{{ url('config') }}">
                        <i class="bi bi-gear"></i>
                        <span class="menu-text">Configuración</span>
                    </a>
                </li>
                <li class="{{ Request::is('finanzas') ? 'active-page-link':''  }}">
                    <a href="{{ url('finanzas') }}">
                        <i class="bi bi-cash-stack"></i>
                        <span class="menu-text">Finanzas</span>
                    </a>
                </li>
                @elseif(Auth::user()->role_as >= 2)
                <li class="menu-category">Administración</li>
                <li class="{{ Request::is('finanzas') ? 'active-page-link':''  }}">
                    <a href="{{ url('finanzas') }}">
                        <i class="bi bi-cash-stack"></i>
                        <span class="menu-text">Finanzas</span>
                    </a>
                </li>
                @endif

                <!-- Mis evaluaciones - Para evaluados -->
                @if(Auth::user()->role_as == 0)
                <li class="menu-category">Mis Evaluaciones</li>
                <li class="{{ Request::is('mis-evaluaciones','show-evaluacion/*') ? 'active-page-link':''  }}">
                    <a href="{{ url('mis-evaluaciones') }}">
                        <i class="bi bi-clipboard-data"></i>
                        <span class="menu-text">Ver Mis Evaluaciones</span>
                    </a>
                </li>
                <li class="{{ Request::is('llenar-cuestionario') ? 'active-page-link':''  }}">
                    <a href="{{ url('llenar-cuestionario') }}">
                        <i class="bi bi-pencil-square"></i>
                        <span class="menu-text">Completar Cuestionario</span>
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
