<!-- Navbar start -->
<nav class="navbar navbar-expand-lg navbar-dark bg-secondary">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="{{ url('/dashboard') }}">
            <div class="border border-primary rounded p-2" style="background-color: #f8f9fa; width: 100%;">
                <img src="{{ asset('img/logos/logoreproxelahorizontal.png') }}" class="d-none d-md-block img-fluid" style="max-height: 50px;" alt="Repro" />
                <img src="{{ asset('img/logos/logo.png') }}" class="d-block d-md-none mx-auto" style="height: 36px; max-height: 50px;" alt="Repro" />
            </div>
            <span class="ms-2 d-none d-md-inline">PORTAL EVALUADO</span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('dashboard') ? 'active' : '' }}" href="{{ url('/dashboard') }}">
                        <i class="bi bi-house-door-fill"></i> Inicio
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ Request::is('mis-evaluaciones*') ? 'active' : '' }}" href="{{ url('/mis-evaluaciones') }}">
                        <i class="bi bi-clipboard-check"></i> Mis Evaluaciones
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ Request::is('llenar-cuestionario*') ? 'active' : '' }}" href="{{ url('/llenar-cuestionario') }}">
                        <i class="bi bi-pencil-square"></i> Cuestionario
                    </a>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                        @if(Auth::user()->fotografia)
                            <img src="{{ asset('assets/imgs/users/'.Auth::user()->fotografia) }}" class="rounded-circle me-2" width="30" height="30">
                        @else
                            <img src="{{ asset('assets/imgs/users/usericon4.png') }}" class="rounded-circle me-2" width="30" height="30">
                        @endif
                        <span class="d-none d-md-inline">{{ Auth::user()->name }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <div class="dropdown-item-text">
                                <div class="d-flex align-items-center">
                                    @if(Auth::user()->fotografia)
                                        <img src="{{ asset('assets/imgs/users/'.Auth::user()->fotografia) }}" class="rounded-circle me-2" width="40" height="40">
                                    @else
                                        <img src="{{ asset('assets/imgs/users/usericon4.png') }}" class="rounded-circle me-2" width="40" height="40">
                                    @endif
                                    <div>
                                        <strong>{{ Auth::user()->name }}</strong>
                                        <div class="small text-muted">{{ Auth::user()->email }}</div>
                                        <div class="small">
                                            <span class="badge bg-secondary">Evaluado</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="{{ url('show-user/'.Auth::user()->id) }}"><i class="bi bi-person"></i> Mi Perfil</a></li>
                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#changePasswordModal"><i class="bi bi-key"></i> Cambiar Contraseña</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="{{ route('logout') }}"
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
<!-- Navbar end -->
