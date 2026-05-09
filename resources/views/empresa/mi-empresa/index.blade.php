@extends('layouts.empresa')
@section('content')

<!-- Content wrapper scroll start -->
<div class="content-wrapper-scroll">

    <!-- Main header starts -->
    <div class="main-header d-flex align-items-center justify-content-between position-relative">
        <div class="d-flex align-items-center justify-content-center">
            <div class="page-icon">
                <i class="bi bi-building"></i>
            </div>
            <div class="page-title">
                <h5>Mi Empresa</h5>
            </div>
        </div>
        <div class="d-flex align-items-end d-none d-sm-block">
            <h6 class="float-end text-light" id="reloj"></h6>
        </div>
    </div>
    <!-- Main header ends -->

    <!-- Content wrapper start -->
    <div class="content-wrapper">

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <!-- Estadísticas -->
        <div class="row gx-3 mb-3">
            <div class="col-sm-6 col-md-4 col-lg-3 col-xl-2">
                <div class="card bg-success-subtle text-center">
                    <div class="card-body py-3">
                        <p class="text-muted mb-1 small text-uppercase">Órdenes</p>
                        <h3 class="mb-0 fw-bold text-success">{{ $stats['total_ordenes'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-md-4 col-lg-3 col-xl-2">
                <div class="card bg-warning-subtle text-center">
                    <div class="card-body py-3">
                        <p class="text-muted mb-1 small text-uppercase">Pendientes</p>
                        <h3 class="mb-0 fw-bold text-warning">{{ $stats['ordenes_pendientes'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-md-4 col-lg-3 col-xl-2">
                <div class="card bg-info-subtle text-center">
                    <div class="card-body py-3">
                        <p class="text-muted mb-1 small text-uppercase">En Proceso</p>
                        <h3 class="mb-0 fw-bold text-info">{{ $stats['ordenes_proceso'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-md-4 col-lg-3 col-xl-2">
                <div class="card bg-primary-subtle text-center">
                    <div class="card-body py-3">
                        <p class="text-muted mb-1 small text-uppercase">Completadas</p>
                        <h3 class="mb-0 fw-bold text-primary">{{ $stats['ordenes_completadas'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-md-4 col-lg-3 col-xl-2">
                <div class="card bg-secondary-subtle text-center">
                    <div class="card-body py-3">
                        <p class="text-muted mb-1 small text-uppercase">Evaluados</p>
                        <h3 class="mb-0 fw-bold text-secondary">{{ $stats['total_evaluados'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-md-4 col-lg-3 col-xl-2">
                <div class="card bg-success-subtle text-center">
                    <div class="card-body py-3">
                        <p class="text-muted mb-1 small text-uppercase">Cuestionarios</p>
                        <h3 class="mb-0 fw-bold text-success">{{ $stats['cuestionarios_completados'] }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Información de la empresa -->
        <div class="row gx-3 mb-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            @if ($empresa->logo)
                                <img src="{{ asset('assets/imgs/empresas/'.$empresa->logo) }}" alt="Logo" class="img-thumbnail me-3" style="height: 60px;">
                            @else
                                <div class="avatar avatar-lg me-3">
                                    <div class="avatar-title bg-success-subtle text-success rounded">
                                        <i class="bi bi-building" style="font-size: 2rem;"></i>
                                    </div>
                                </div>
                            @endif
                            <div>
                                <h5 class="card-title mb-0">{{ $empresa->nombre }}</h5>
                                <span class="badge {{ $empresa->estado ? 'bg-success' : 'bg-danger' }}">
                                    {{ $empresa->getEstadoTexto() }}
                                </span>
                            </div>
                        </div>
                        @if(Auth::user()->principal == 1)
                        <a href="{{ route('empresa.mi-empresa.edit') }}" class="btn btn-success">
                            <i class="bi bi-pencil"></i> Editar
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Row start -->
        <div class="row gx-3">
            <!-- Información general -->
            <div class="col-lg-6">
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="bi bi-info-circle text-success"></i> Información General</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted small">NIT:</label>
                                <div class="fw-medium">{{ $empresa->nit ?? 'No definido' }}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted small">Teléfono:</label>
                                <div class="fw-medium">{{ $empresa->telefono ?? 'No definido' }}</div>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label text-muted small">Email:</label>
                                <div class="fw-medium">
                                    @if($empresa->email)
                                        <a href="mailto:{{ $empresa->email }}">{{ $empresa->email }}</a>
                                    @else
                                        No definido
                                    @endif
                                </div>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label text-muted small">Dirección:</label>
                                <div class="fw-medium">{{ $empresa->direccion ?? 'No definida' }}</div>
                            </div>
                            @if($empresa->sitio_web)
                            <div class="col-12 mb-3">
                                <label class="form-label text-muted small">Sitio Web:</label>
                                <div class="fw-medium">
                                    <a href="{{ $empresa->sitio_web }}" target="_blank" rel="noopener">
                                        {{ $empresa->sitio_web }} <i class="bi bi-box-arrow-up-right"></i>
                                    </a>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información de contacto -->
            <div class="col-lg-6">
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="bi bi-person-lines-fill text-success"></i> Contacto Principal</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted small">Nombre:</label>
                                <div class="fw-medium">{{ $empresa->contacto_nombre ?? 'No definido' }}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted small">Cargo:</label>
                                <div class="fw-medium">{{ $empresa->contacto_cargo ?? 'No definido' }}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted small">Teléfono:</label>
                                <div class="fw-medium">{{ $empresa->contacto_telefono ?? 'No definido' }}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted small">Email:</label>
                                <div class="fw-medium">
                                    @if($empresa->contacto_email)
                                        <a href="mailto:{{ $empresa->contacto_email }}">{{ $empresa->contacto_email }}</a>
                                    @else
                                        No definido
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Descripción y notas -->
            @if($empresa->descripcion || $empresa->notas)
            <div class="col-lg-12">
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="bi bi-card-text text-success"></i> Información Adicional</h6>
                    </div>
                    <div class="card-body">
                        @if($empresa->descripcion)
                        <div class="mb-3">
                            <label class="form-label text-muted small">Descripción:</label>
                            <p class="mb-0">{{ $empresa->descripcion }}</p>
                        </div>
                        @endif
                        @if($empresa->notas)
                        <div>
                            <label class="form-label text-muted small">Notas:</label>
                            <p class="mb-0">{{ $empresa->notas }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- Usuarios de la empresa -->
            <div class="col-lg-12">
                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="card-title mb-0"><i class="bi bi-people text-success"></i> Usuarios de la Empresa ({{ $stats['usuarios_activos'] }})</h6>
                        @if(Auth::user()->principal == 1)
                        <a href="{{ route('empresa.usuarios') }}" class="btn btn-sm btn-outline-success">
                            <i class="bi bi-gear"></i> Gestionar
                        </a>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Email</th>
                                        <th>Cargo</th>
                                        <th>Tipo</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($empresa->usuariosActivos as $usuario)
                                    <tr>
                                        <td>{{ $usuario->name }}</td>
                                        <td>{{ $usuario->email }}</td>
                                        <td>{{ $usuario->cargo ?? '-' }}</td>
                                        <td>
                                            @if($usuario->principal == 1)
                                                <span class="badge bg-success">Principal</span>
                                            @else
                                                <span class="badge bg-secondary">Usuario</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{ $usuario->estado ? 'bg-success' : 'bg-danger' }}">
                                                {{ $usuario->estado ? 'Activo' : 'Inactivo' }}
                                            </span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No hay usuarios registrados</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Row end -->

    </div>
    <!-- Content wrapper end -->

</div>
<!-- Content wrapper scroll end -->

@endsection
