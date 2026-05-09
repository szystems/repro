@extends('layouts.admin')
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
                <h5>Detalles de Empresa</h5>
            </div>
        </div>
        <div class="d-flex align-items-end d-none d-sm-block">
            <h6 class="float-end text-light" id="reloj"></h6>
        </div>
    </div>
    <!-- Main header ends -->

    <!-- Content wrapper start -->
    <div class="content-wrapper">

        @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <!-- Row start -->
        <div class="row gx-3 mb-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-8 d-flex align-items-center">
                                @if ($empresa->logo)
                                    <img src="{{ asset('assets/imgs/empresas/'.$empresa->logo) }}" alt="Logo" class="img-thumbnail me-3" style="height: 60px;">
                                @else
                                    <div class="avatar avatar-lg me-3">
                                        <div class="avatar-title bg-light text-secondary rounded">
                                            <i class="bi bi-building" style="font-size: 2rem;"></i>
                                        </div>
                                    </div>
                                @endif
                                <h5 class="card-title mb-0">{{ $empresa->nombre }}</h5>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="btn-group">
                                    <a href="{{ url('empresas') }}" class="btn btn-outline-secondary">
                                        <i class="bi bi-arrow-left"></i> Volver
                                    </a>
                                    <a href="{{ url('edit-empresa/'.$empresa->id) }}" class="btn btn-primary">
                                        <i class="bi bi-pencil"></i> Editar
                                    </a>
                                    <a href="{{ url('pdf-empresa/'.$empresa->id) }}" target="_blank" class="btn btn-danger">
                                        <i class="bi bi-file-earmark-pdf"></i> PDF
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Row end -->

        <!-- Row start -->
        <div class="row gx-3">
            <div class="col-lg-12">
                <!-- Información general -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="bi bi-info-circle"></i> Información General</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">NIT:</label>
                                <div class="fw-medium">{{ $empresa->nit ?? 'No definido' }}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Estado:</label>
                                <div>
                                    <span class="badge {{ $empresa->estado ? 'bg-success' : 'bg-danger' }} p-2">
                                        {{ $empresa->getEstadoTexto() }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Teléfono:</label>
                                <div class="fw-medium">{{ $empresa->telefono ?? 'No definido' }}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Email:</label>
                                <div class="fw-medium">
                                    @if($empresa->email)
                                        <a href="mailto:{{ $empresa->email }}">{{ $empresa->email }}</a>
                                    @else
                                        No definido
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label text-muted">Dirección:</label>
                                <div class="fw-medium">{{ $empresa->direccion ?? 'No definida' }}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Sitio Web:</label>
                                <div class="fw-medium">
                                    @if($empresa->sitio_web)
                                        <a href="{{ $empresa->sitio_web }}" target="_blank">{{ $empresa->sitio_web }}</a>
                                    @else
                                        No definido
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Fecha de Registro:</label>
                                <div class="fw-medium">{{ $empresa->getCreatedAtFormateada() }}</div>
                            </div>
                            @if($empresa->descripcion)
                            <div class="col-md-12 mb-3">
                                <label class="form-label text-muted">Descripción:</label>
                                <div class="fw-medium">{{ $empresa->descripcion }}</div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Contacto principal -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="bi bi-person-lines-fill"></i> Contacto Principal</h6>
                    </div>
                    <div class="card-body">
                        @if($empresa->contacto_nombre || $empresa->contacto_cargo || $empresa->contacto_telefono || $empresa->contacto_email)
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Nombre:</label>
                                <div class="fw-medium">{{ $empresa->contacto_nombre ?? 'No definido' }}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Cargo:</label>
                                <div class="fw-medium">{{ $empresa->contacto_cargo ?? 'No definido' }}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Teléfono:</label>
                                <div class="fw-medium">{{ $empresa->contacto_telefono ?? 'No definido' }}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Email:</label>
                                <div class="fw-medium">
                                    @if($empresa->contacto_email)
                                        <a href="mailto:{{ $empresa->contacto_email }}">{{ $empresa->contacto_email }}</a>
                                    @else
                                        No definido
                                    @endif
                                </div>
                            </div>
                        </div>
                        @else
                        <p class="text-muted mb-0">No se ha definido información de contacto principal.</p>
                        @endif
                    </div>
                </div>

                <!-- Notas adicionales -->
                @if($empresa->notas)
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="bi bi-journal-text"></i> Notas Adicionales</h6>
                    </div>
                    <div class="card-body">
                        <div class="fw-medium">{{ $empresa->notas }}</div>
                    </div>
                </div>
                @endif
            </div>

            <div class="col-lg-12">
                <!-- Sección de Usuarios de la Empresa -->
                <div class="card mb-4">
                    <div class="card-header">
                        <div class="d-flex align-items-center justify-content-between">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-people-fill"></i> Usuarios de la Empresa
                                <span class="badge bg-primary ms-2">{{ count($usuarios) }}</span>
                            </h5>
                            @if(Auth::user()->role_as >= 2)
                            <a href="{{ url('add-user?empresa_id='.$empresa->id) }}" class="btn btn-sm btn-success">
                                <i class="bi bi-person-plus-fill"></i> Agregar Usuario
                            </a>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        @if(count($usuarios) > 0)
                            <div class="row g-3">
                                @foreach($usuarios as $usuario)
                                <div class="col-xl-4 col-lg-6 col-md-6">
                                    <div class="card border shadow-sm h-100">
                                        <div class="card-body p-0">
                                            <div class="row g-0">
                                                <!-- Foto de usuario - tamaño fijo -->
                                                <div class="col-4 border-end">
                                                    <div class="d-flex align-items-center justify-content-center h-100 p-2">
                                                        <div style="width: 80px; height: 80px; overflow: hidden; border-radius: 50%;">
                                                            @if($usuario->fotografia)
                                                                <img src="{{ asset('assets/imgs/users/'.$usuario->fotografia) }}"
                                                                     class="img-fluid w-100 h-100"
                                                                     style="object-fit: cover;"
                                                                     alt="{{ $usuario->name }}">
                                                            @else
                                                                <img src="{{ asset('assets/imgs/users/usericon4.png') }}"
                                                                     class="img-fluid w-100 h-100"
                                                                     style="object-fit: cover;"
                                                                     alt="Default">
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Información del usuario - contenido flexible -->
                                                <div class="col-8">
                                                    <div class="p-2">
                                                        <h6 class="mb-1 text-truncate" title="{{ $usuario->name }}">
                                                            {{ $usuario->name }}
                                                        </h6>
                                                        <div class="small text-muted mb-2">
                                                            <div class="text-truncate" title="{{ $usuario->email }}">
                                                                <i class="bi bi-envelope-fill"></i> {{ $usuario->email }}
                                                            </div>
                                                            @if($usuario->celular)
                                                                <div><i class="bi bi-phone-fill"></i> {{ $usuario->celular }}</div>
                                                            @endif
                                                            <div class="mt-1">
                                                                <span class="badge bg-light text-dark">{{ $usuario->cargo ?? 'No especificado' }}</span>
                                                                @if($usuario->principal == 1)
                                                                    <span class="badge bg-warning text-dark">Principal</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-footer p-2 bg-light">
                                            <div class="btn-group btn-group-sm w-100">
                                                <a href="{{ url('show-user/'.$usuario->id) }}" class="btn btn-outline-primary">
                                                    <i class="bi bi-eye-fill"></i> Ver
                                                </a>
                                                @if(Auth::user()->role_as >= 2 || Auth::user()->id == $usuario->id)
                                                <a href="{{ url('edit-user/'.$usuario->id) }}" class="btn btn-outline-secondary">
                                                    <i class="bi bi-pencil-fill"></i> Editar
                                                </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> No hay usuarios registrados para esta empresa.
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Logo de la empresa -->
                @if($empresa->logo)
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="bi bi-image"></i> Logo de la Empresa</h6>
                    </div>
                    <div class="card-body text-center">
                        <img src="{{ asset('assets/imgs/empresas/'.$empresa->logo) }}" alt="Logo" class="img-fluid rounded" style="max-height: 300px;">
                    </div>
                </div>
                @endif
            </div>
        </div>
        <!-- Row end -->

    </div>
    <!-- Content wrapper end -->
</div>
<!-- Content wrapper scroll end -->

@endsection
