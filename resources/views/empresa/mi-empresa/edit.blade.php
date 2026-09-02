@extends('layouts.empresa')
@section('content')

<!-- Content wrapper scroll start -->
<div class="content-wrapper-scroll">

    <!-- Main header starts -->
    <div class="main-header d-flex align-items-center justify-content-between position-relative">
        <div class="d-flex align-items-center justify-content-center">
            <div class="page-icon">
                <i class="bi bi-pencil-square"></i>
            </div>
            <div class="page-title">
                <h5>Editar Mi Empresa</h5>
            </div>
        </div>
        <div class="d-flex align-items-end d-none d-sm-block">
            <h6 class="float-end text-light" id="reloj"></h6>
        </div>
    </div>
    <!-- Main header ends -->

    <!-- Content wrapper start -->
    <div class="content-wrapper">

        @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Por favor corrige los siguientes errores:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <form action="{{ route('empresa.mi-empresa.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row gx-3">
                <!-- Información básica -->
                <div class="col-lg-8">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="card-title mb-0"><i class="bi bi-building text-success"></i> Información de la Empresa</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label for="nombre" class="form-label">Nombre de la Empresa <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('nombre') is-invalid @enderror" 
                                           id="nombre" name="nombre" value="{{ old('nombre', $empresa->nombre) }}" required>
                                    @error('nombre')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="nit" class="form-label">NIT</label>
                                    <input type="text" class="form-control @error('nit') is-invalid @enderror" 
                                           id="nit" name="nit" value="{{ old('nit', $empresa->nit) }}">
                                    @error('nit')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="telefono" class="form-label">Teléfono</label>
                                    <input type="tel" class="form-control @error('telefono') is-invalid @enderror" 
                                           id="telefono" name="telefono" value="{{ old('telefono', $empresa->telefono) }}">
                                    @error('telefono')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email', $empresa->email) }}">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="direccion" class="form-label">Dirección</label>
                                    <textarea class="form-control @error('direccion') is-invalid @enderror" 
                                              id="direccion" name="direccion" rows="2">{{ old('direccion', $empresa->direccion) }}</textarea>
                                    @error('direccion')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="sitio_web" class="form-label">Sitio Web</label>
                                    <input type="url" class="form-control @error('sitio_web') is-invalid @enderror" 
                                           id="sitio_web" name="sitio_web" value="{{ old('sitio_web', $empresa->sitio_web) }}"
                                           placeholder="https://ejemplo.com">
                                    @error('sitio_web')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="descripcion" class="form-label">Descripción</label>
                                    <textarea class="form-control @error('descripcion') is-invalid @enderror" 
                                              id="descripcion" name="descripcion" rows="3">{{ old('descripcion', $empresa->descripcion) }}</textarea>
                                    @error('descripcion')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Información de contacto -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="card-title mb-0"><i class="bi bi-person-lines-fill text-success"></i> Contacto Principal</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="contacto_nombre" class="form-label">Nombre del Contacto</label>
                                    <input type="text" class="form-control @error('contacto_nombre') is-invalid @enderror" 
                                           id="contacto_nombre" name="contacto_nombre" value="{{ old('contacto_nombre', $empresa->contacto_nombre) }}">
                                    @error('contacto_nombre')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="contacto_cargo" class="form-label">Cargo</label>
                                    <input type="text" class="form-control @error('contacto_cargo') is-invalid @enderror" 
                                           id="contacto_cargo" name="contacto_cargo" value="{{ old('contacto_cargo', $empresa->contacto_cargo) }}">
                                    @error('contacto_cargo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="contacto_telefono" class="form-label">Teléfono</label>
                                    <input type="tel" class="form-control @error('contacto_telefono') is-invalid @enderror" 
                                           id="contacto_telefono" name="contacto_telefono" value="{{ old('contacto_telefono', $empresa->contacto_telefono) }}">
                                    @error('contacto_telefono')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="contacto_email" class="form-label">Email</label>
                                    <input type="email" class="form-control @error('contacto_email') is-invalid @enderror" 
                                           id="contacto_email" name="contacto_email" value="{{ old('contacto_email', $empresa->contacto_email) }}">
                                    @error('contacto_email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="card-title mb-0"><i class="bi bi-people text-success"></i> Visibilidad entre reclutadores</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="modo_visibilidad_reclutadores" class="form-label">Modo de acceso para trabajadores</label>
                                <select class="form-select @error('modo_visibilidad_reclutadores') is-invalid @enderror"
                                        id="modo_visibilidad_reclutadores" name="modo_visibilidad_reclutadores">
                                    @foreach(\App\Support\EmpresaVisibilidadReclutadoresSupport::modosDisponibles() as $valor => $etiqueta)
                                    <option value="{{ $valor }}" {{ old('modo_visibilidad_reclutadores', $empresa->modo_visibilidad_reclutadores ?? 'compartido') === $valor ? 'selected' : '' }}>
                                        {{ $etiqueta }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('modo_visibilidad_reclutadores')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted d-block mt-2">
                                    En procesos marcados como <strong>confidenciales</strong>, solo el gerente RRHH y el reclutador asignado pueden ver la orden.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Logo y acciones -->
                <div class="col-lg-4">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="card-title mb-0"><i class="bi bi-image text-success"></i> Logo de la Empresa</h6>
                        </div>
                        <div class="card-body text-center">
                            @if ($empresa->logo)
                                <img src="{{ asset('assets/imgs/empresas/'.$empresa->logo) }}" 
                                     alt="Logo actual" class="img-thumbnail mb-3" style="max-height: 150px;">
                                <p class="text-muted small mb-3">Logo actual</p>
                            @else
                                <div class="avatar avatar-xl mb-3 mx-auto">
                                    <div class="avatar-title bg-success-subtle text-success rounded" style="font-size: 3rem;">
                                        <i class="bi bi-building"></i>
                                    </div>
                                </div>
                                <p class="text-muted small mb-3">Sin logo</p>
                            @endif
                            <div class="mb-3">
                                <label for="logo" class="form-label">Cambiar Logo</label>
                                <input type="file" class="form-control @error('logo') is-invalid @enderror" 
                                       id="logo" name="logo" accept="image/*">
                                @error('logo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Formatos: JPG, PNG, GIF. Máx: 2MB</div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="bi bi-check-lg"></i> Guardar Cambios
                                </button>
                                <a href="{{ route('empresa.mi-empresa') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-lg"></i> Cancelar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

    </div>
    <!-- Content wrapper end -->

</div>
<!-- Content wrapper scroll end -->

@endsection
