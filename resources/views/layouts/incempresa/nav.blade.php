<!-- Navbar start -->
<nav class="navbar navbar-expand-lg navbar-dark bg-success">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="{{ url('/dashboard') }}">
            <img src="{{ asset('assets/imgs/logos/logo-light.png') }}" class="img-fluid" alt="REPRO" height="40">
            <span class="ms-2 d-none d-md-inline">PORTAL EMPRESAS</span>
        </a>

        <div class="ms-auto d-flex align-items-center">
            <!-- Reloj y fecha -->
            <span class="navbar-text d-none d-lg-block me-3">
                <span class="badge bg-light text-success" id="reloj"></span>
            </span>

            <!-- Información de usuario y menú desplegable -->
            <div class="dropdown">
                <a class="nav-link dropdown-toggle text-white d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
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
                        <i class="bi bi-box-arrow-right me-2"></i> Cerrar Sesión
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </div>
            </div>
        </div>
    </div>
</nav>
<!-- Navbar end -->
