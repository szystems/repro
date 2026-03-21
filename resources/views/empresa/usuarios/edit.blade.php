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
                <h5>Editar Usuario</h5>
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

        <div class="row gx-3">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="bi bi-person-gear text-success"></i> Editar: {{ $usuario->name }}</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('empresa.usuarios.update', $usuario) }}" method="POST">
                            @csrf
                            @method('PUT')
                            
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="name" class="form-label">Nombre Completo <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', $usuario->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email', $usuario->email) }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="telefono" class="form-label">Teléfono</label>
                                    <input type="tel" class="form-control @error('telefono') is-invalid @enderror" 
                                           id="telefono" name="telefono" value="{{ old('telefono', $usuario->telefono) }}">
                                    @error('telefono')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="cargo" class="form-label">Cargo</label>
                                    <input type="text" class="form-control @error('cargo') is-invalid @enderror" 
                                           id="cargo" name="cargo" value="{{ old('cargo', $usuario->cargo) }}"
                                           placeholder="Ej: Recursos Humanos, Gerente, etc.">
                                    @error('cargo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="estado" class="form-label">Estado <span class="text-danger">*</span></label>
                                    <select class="form-select @error('estado') is-invalid @enderror" id="estado" name="estado" required>
                                        <option value="1" {{ old('estado', $usuario->estado) == 1 ? 'selected' : '' }}>Activo</option>
                                        <option value="0" {{ old('estado', $usuario->estado) == 0 ? 'selected' : '' }}>Inactivo</option>
                                    </select>
                                    @error('estado')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Permisos del sub-usuario --}}
                            @php
                                $permisosUsuario = $usuario->permisos ? (is_array($usuario->permisos) ? $usuario->permisos : json_decode($usuario->permisos, true)) : [];
                                if (!is_array($permisosUsuario)) $permisosUsuario = [];
                            @endphp
                            <div class="card bg-light mb-3">
                                <div class="card-body">
                                    <h6 class="card-title mb-2"><i class="bi bi-shield-check text-success"></i> Permisos del usuario</h6>
                                    <p class="form-text mb-2">Seleccione qué puede hacer este usuario:</p>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" value="ver_ordenes" name="permisos_empresa[]" id="perm_ver_ordenes" {{ in_array('ver_ordenes', $permisosUsuario) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="perm_ver_ordenes">Ver órdenes</label>
                                            </div>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" value="crear_ordenes" name="permisos_empresa[]" id="perm_crear_ordenes" {{ in_array('crear_ordenes', $permisosUsuario) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="perm_crear_ordenes">Crear órdenes</label>
                                            </div>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" value="ver_resultados" name="permisos_empresa[]" id="perm_ver_resultados" {{ in_array('ver_resultados', $permisosUsuario) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="perm_ver_resultados">Ver resultados</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" value="descargar_pdf" name="permisos_empresa[]" id="perm_descargar_pdf" {{ in_array('descargar_pdf', $permisosUsuario) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="perm_descargar_pdf">Descargar PDFs</label>
                                            </div>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" value="subir_documentos" name="permisos_empresa[]" id="perm_subir_documentos" {{ in_array('subir_documentos', $permisosUsuario) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="perm_subir_documentos">Subir documentos</label>
                                            </div>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" value="ver_reportes" name="permisos_empresa[]" id="perm_ver_reportes" {{ in_array('ver_reportes', $permisosUsuario) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="perm_ver_reportes">Ver reportes</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <h6 class="mb-3"><i class="bi bi-key text-warning"></i> Cambiar Contraseña (opcional)</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">Nueva Contraseña</label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                           id="password" name="password" minlength="8">
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Dejar en blanco para mantener la contraseña actual</div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="password_confirmation" class="form-label">Confirmar Nueva Contraseña</label>
                                    <input type="password" class="form-control" 
                                           id="password_confirmation" name="password_confirmation" minlength="8">
                                </div>
                            </div>

                            <hr>

                            <div class="d-flex justify-content-between">
                                <a href="{{ route('empresa.usuarios') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left"></i> Volver
                                </a>
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check-lg"></i> Guardar Cambios
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body text-center">
                        @if($usuario->fotografia)
                            <img src="{{ asset('assets/imgs/users/'.$usuario->fotografia) }}" 
                                 alt="{{ $usuario->name }}" 
                                 class="rounded-circle mb-3" 
                                 style="width: 100px; height: 100px; object-fit: cover;">
                        @else
                            <div class="avatar avatar-xl mx-auto mb-3">
                                <div class="avatar-title bg-success-subtle text-success rounded-circle" style="font-size: 2rem;">
                                    {{ strtoupper(substr($usuario->name, 0, 1)) }}
                                </div>
                            </div>
                        @endif
                        <h6>{{ $usuario->name }}</h6>
                        <p class="text-muted small mb-2">{{ $usuario->email }}</p>
                        <span class="badge {{ $usuario->estado ? 'bg-success' : 'bg-danger' }}">
                            {{ $usuario->estado ? 'Activo' : 'Inactivo' }}
                        </span>
                    </div>
                </div>

                <div class="card bg-warning-subtle mt-3">
                    <div class="card-body">
                        <h6 class="card-title text-warning"><i class="bi bi-exclamation-triangle"></i> Zona de Peligro</h6>
                        <p class="card-text small">
                            Si eliminas este usuario, perderá acceso al sistema de forma permanente.
                        </p>
                        <form action="{{ route('empresa.usuarios.destroy', $usuario) }}" method="POST"
                              onsubmit="return confirm('¿Está seguro de eliminar este usuario? Esta acción no se puede deshacer.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                                <i class="bi bi-trash"></i> Eliminar Usuario
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!-- Content wrapper end -->

</div>
<!-- Content wrapper scroll end -->

@endsection
