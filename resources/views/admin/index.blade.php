@extends(session('layout', 'layouts.admin'))

@section('content')

<!-- Content wrapper start -->
<div class="content-wrapper">

    <!-- Row start -->
    <div class="row">
        <div class="col-xxl-12">
            <div class="page-header">
                <h3 class="page-title">Panel de Control</h3>
                <div>
                    <span class="badge bg-primary-transparent" id="reloj"></span>
                </div>
            </div>
        </div>
    </div>
    <!-- Row end -->

    <!-- Row start -->
    <div class="row mb-4">
        <div class="col-12">
            <!-- Mensaje de bienvenida adaptado al tipo de usuario -->
            <div class="alert
                @if(Auth::user()->role_as == 3) alert-danger
                @elseif(Auth::user()->role_as == 2) alert-info
                @elseif(Auth::user()->role_as == 1) alert-success
                @else alert-secondary
                @endif">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-lg me-3">
                        <div class="avatar-title rounded-circle bg-white">
                            <i class="bi
                                @if(Auth::user()->role_as == 3) bi-shield-fill text-danger
                                @elseif(Auth::user()->role_as == 2) bi-briefcase-fill text-info
                                @elseif(Auth::user()->role_as == 1) bi-building-fill text-success
                                @else bi-person-fill text-secondary
                                @endif fs-3"></i>
                        </div>
                    </div>
                    <div>
                        <h5>Bienvenido/a, {{ Auth::user()->name }}</h5>
                        <p class="mb-0">
                            @if(Auth::user()->role_as == 3)
                                Como administrador, tienes acceso completo al sistema de gestión de REPRO.
                            @elseif(Auth::user()->role_as == 2)
                                Como personal de REPRO, puedes gestionar las evaluaciones de polígrafo y las empresas cliente.
                            @elseif(Auth::user()->role_as == 1)
                                Como usuario de empresa, puedes gestionar las evaluaciones de polígrafo para tu organización.
                            @else
                                Bienvenido al sistema de evaluaciones de REPRO. Aquí podrás ver y completar cuestionarios.
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row start - Accesos rápidos -->
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <div class="row">
                        <div class="col">
                            <h5 class="card-title">Accesos Rápidos</h5>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <!-- Accesos para administradores y personal de Repro -->
                        @if(Auth::user()->role_as >= 2)
                        <div class="col-xxl-3 col-xl-3 col-lg-4 col-md-6 col-sm-6">
                            <a href="{{ url('add-evaluacion') }}" class="text-decoration-none">
                                <div class="card border border-light shadow-hover mb-0 h-100">
                                    <div class="card-body text-center py-4 d-flex flex-column align-items-center justify-content-center">
                                        <div class="avatar avatar-lg mb-3 mx-auto">
                                            <div class="avatar-title bg-primary-transparent rounded-circle d-flex align-items-center justify-content-center" style="width: 85px; height: 85px;">
                                                <i class="bi bi-clipboard-plus text-primary" style="font-size: 2.5rem !important;"></i>
                                            </div>
                                        </div>
                                        <h5 class="mb-0 fw-semibold">Nueva Evaluación</h5>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-xxl-3 col-xl-3 col-lg-4 col-md-6 col-sm-6">
                            <a href="{{ url('empresas') }}" class="text-decoration-none">
                                <div class="card border border-light shadow-hover mb-0 h-100">
                                    <div class="card-body text-center py-4 d-flex flex-column align-items-center justify-content-center">
                                        <div class="avatar avatar-lg mb-3 mx-auto">
                                            <div class="avatar-title bg-success-transparent rounded-circle d-flex align-items-center justify-content-center" style="width: 85px; height: 85px;">
                                                <i class="bi bi-building text-success" style="font-size: 2.5rem !important;"></i>
                                            </div>
                                        </div>
                                        <h5 class="mb-0 fw-semibold">Gestionar Empresas</h5>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-xxl-3 col-xl-3 col-lg-4 col-md-6 col-sm-6">
                            <a href="{{ url('users') }}" class="text-decoration-none">
                                <div class="card border border-light shadow-hover mb-0 h-100">
                                    <div class="card-body text-center py-4 d-flex flex-column align-items-center justify-content-center">
                                        <div class="avatar avatar-lg mb-3 mx-auto">
                                            <div class="avatar-title bg-info-transparent rounded-circle d-flex align-items-center justify-content-center" style="width: 85px; height: 85px;">
                                                <i class="bi bi-people text-info" style="font-size: 2.5rem !important;"></i>
                                            </div>
                                        </div>
                                        <h5 class="mb-0 fw-semibold">Gestionar Usuarios</h5>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-xxl-3 col-xl-3 col-lg-4 col-md-6 col-sm-6">
                            <a href="{{ url('reportes/evaluaciones') }}" class="text-decoration-none">
                                <div class="card border border-light shadow-hover mb-0 h-100">
                                    <div class="card-body text-center py-4 d-flex flex-column align-items-center justify-content-center">
                                        <div class="avatar avatar-lg mb-3 mx-auto">
                                            <div class="avatar-title bg-warning-transparent rounded-circle d-flex align-items-center justify-content-center" style="width: 85px; height: 85px;">
                                                <i class="bi bi-graph-up text-warning" style="font-size: 2.5rem !important;"></i>
                                            </div>
                                        </div>
                                        <h5 class="mb-0 fw-semibold">Ver Reportes</h5>
                                    </div>
                                </div>
                            </a>
                        </div>
                        @endif

                        <!-- Accesos para usuarios de empresa -->
                        @if(Auth::user()->role_as == 1)
                        <div class="col-xxl-4 col-xl-4 col-lg-4 col-md-6 col-sm-6">
                            <a href="{{ url('add-orden') }}" class="text-decoration-none">
                                <div class="card border border-light shadow-hover mb-0 h-100">
                                    <div class="card-body text-center py-4 d-flex flex-column align-items-center justify-content-center">
                                        <div class="avatar avatar-lg mb-3 mx-auto">
                                            <div class="avatar-title bg-primary-transparent rounded-circle d-flex align-items-center justify-content-center" style="width: 85px; height: 85px;">
                                                <i class="bi bi-file-earmark-plus text-primary" style="font-size: 2.5rem !important;"></i>
                                            </div>
                                        </div>
                                        <h5 class="mb-0 fw-semibold">Nueva Orden de Evaluación</h5>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-xxl-4 col-xl-4 col-lg-4 col-md-6 col-sm-6">
                            <a href="{{ url('evaluaciones') }}" class="text-decoration-none">
                                <div class="card border border-light shadow-hover mb-0 h-100">
                                    <div class="card-body text-center py-4 d-flex flex-column align-items-center justify-content-center">
                                        <div class="avatar avatar-lg mb-3 mx-auto">
                                            <div class="avatar-title bg-success-transparent rounded-circle d-flex align-items-center justify-content-center" style="width: 85px; height: 85px;">
                                                <i class="bi bi-clipboard-data text-success" style="font-size: 2.5rem !important;"></i>
                                            </div>
                                        </div>
                                        <h5 class="mb-0 fw-semibold">Ver Evaluaciones</h5>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-xxl-4 col-xl-4 col-lg-4 col-md-6 col-sm-6">
                            <a href="{{ url('evaluados') }}" class="text-decoration-none">
                                <div class="card border border-light shadow-hover mb-0 h-100">
                                    <div class="card-body text-center py-4 d-flex flex-column align-items-center justify-content-center">
                                        <div class="avatar avatar-lg mb-3 mx-auto">
                                            <div class="avatar-title bg-info-transparent rounded-circle d-flex align-items-center justify-content-center" style="width: 85px; height: 85px;">
                                                <i class="bi bi-person-vcard text-info" style="font-size: 2.5rem !important;"></i>
                                            </div>
                                        </div>
                                        <h5 class="mb-0 fw-semibold">Personas Evaluadas</h5>
                                    </div>
                                </div>
                            </a>
                        </div>
                        @endif

                        <!-- Accesos para personas evaluadas -->
                        @if(Auth::user()->role_as == 0)
                        <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-6 col-sm-6">
                            <a href="{{ url('mis-evaluaciones') }}" class="text-decoration-none">
                                <div class="card border border-light shadow-hover mb-0 h-100">
                                    <div class="card-body text-center py-4 d-flex flex-column align-items-center justify-content-center">
                                        <div class="avatar avatar-lg mb-3 mx-auto">
                                            <div class="avatar-title bg-primary-transparent rounded-circle d-flex align-items-center justify-content-center" style="width: 85px; height: 85px;">
                                                <i class="bi bi-clipboard-check text-primary" style="font-size: 2.5rem !important;"></i>
                                            </div>
                                        </div>
                                        <h5 class="mb-0 fw-semibold">Ver Mis Evaluaciones</h5>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-6 col-sm-6">
                            <a href="{{ url('llenar-cuestionario') }}" class="text-decoration-none">
                                <div class="card border border-light shadow-hover mb-0 h-100">
                                    <div class="card-body text-center py-4 d-flex flex-column align-items-center justify-content-center">
                                        <div class="avatar avatar-lg mb-3 mx-auto">
                                            <div class="avatar-title bg-success-transparent rounded-circle d-flex align-items-center justify-content-center" style="width: 85px; height: 85px;">
                                                <i class="bi bi-pencil-square text-success" style="font-size: 2.5rem !important;"></i>
                                            </div>
                                        </div>
                                        <h5 class="mb-0 fw-semibold">Completar Cuestionario</h5>
                                    </div>
                                </div>
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Row end -->

    <!-- Información resumen según tipo de usuario -->
    @if(Auth::user()->role_as >= 1)
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Resumen de Actividad</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Contenido del resumen que variará según el rol del usuario -->
                        <div class="col-md-12">
                            <p class="text-center text-muted">
                                Esta sección mostrará estadísticas y datos relevantes según su rol en el sistema
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>
<!-- Content wrapper end -->

@endsection
