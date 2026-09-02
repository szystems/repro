@extends('layouts.empresa')
@section('content')

<!-- Content wrapper scroll start -->
<div class="content-wrapper-scroll">

    <!-- Main header starts -->
    <div class="main-header d-flex align-items-center justify-content-between position-relative">
        <div class="d-flex align-items-center justify-content-center">
            <div class="page-icon">
                <i class="bi bi-person-plus"></i>
            </div>
            <div class="page-title">
                <h5>Nuevo Usuario</h5>
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
                        <h6 class="card-title mb-0"><i class="bi bi-person-plus text-success"></i> Datos del Usuario</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('empresa.usuarios.store') }}" method="POST">
                            @csrf
                            
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="name" class="form-label">Nombre Completo <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email') }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="telefono" class="form-label">Teléfono</label>
                                    <input type="tel" class="form-control @error('telefono') is-invalid @enderror" 
                                           id="telefono" name="telefono" value="{{ old('telefono') }}">
                                    @error('telefono')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label for="cargo" class="form-label">Cargo</label>
                                    <input type="text" class="form-control @error('cargo') is-invalid @enderror" 
                                           id="cargo" name="cargo" value="{{ old('cargo') }}"
                                           placeholder="Ej: Recursos Humanos, Gerente, etc.">
                                    @error('cargo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">Contraseña <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                           id="password" name="password" required minlength="8">
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Mínimo 8 caracteres</div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="password_confirmation" class="form-label">Confirmar Contraseña <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" 
                                           id="password_confirmation" name="password_confirmation" required minlength="8">
                                </div>
                            </div>

                            {{-- Permisos del sub-usuario --}}
                            @php
                                $permisosSeleccionados = old('permisos_empresa', \App\Support\EmpresaPermisosSupport::permisosDefaultTrabajador());
                            @endphp
                            <div class="card bg-light mb-3">
                                <div class="card-body">
                                    <h6 class="card-title mb-2"><i class="bi bi-shield-check text-success"></i> Permisos del usuario</h6>
                                    <p class="form-text mb-2">Perfil <strong>trabajador</strong> (reclutador/asistente): por defecto puede ver órdenes, editar las propias, gestionar papelería y descargar PDF de orden de servicio. Solo marque «Crear órdenes» si debe actuar como usuario principal.</p>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" value="ver_ordenes" name="permisos_empresa[]" id="perm_ver_ordenes" {{ in_array('ver_ordenes', $permisosSeleccionados, true) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="perm_ver_ordenes">Ver órdenes</label>
                                            </div>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" value="crear_ordenes" name="permisos_empresa[]" id="perm_crear_ordenes" {{ in_array('crear_ordenes', $permisosSeleccionados, true) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="perm_crear_ordenes">Crear órdenes <span class="text-muted">(solo si aplica)</span></label>
                                            </div>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" value="ver_resultados" name="permisos_empresa[]" id="perm_ver_resultados" {{ in_array('ver_resultados', $permisosSeleccionados, true) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="perm_ver_resultados">Ver resultados</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" value="descargar_pdf" name="permisos_empresa[]" id="perm_descargar_pdf" {{ in_array('descargar_pdf', $permisosSeleccionados, true) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="perm_descargar_pdf">Descargar PDFs (orden de servicio)</label>
                                            </div>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" value="subir_documentos" name="permisos_empresa[]" id="perm_subir_documentos" {{ in_array('subir_documentos', $permisosSeleccionados, true) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="perm_subir_documentos">Subir papelería</label>
                                            </div>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" value="editar_ordenes" name="permisos_empresa[]" id="perm_editar_ordenes" {{ in_array('editar_ordenes', $permisosSeleccionados, true) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="perm_editar_ordenes">Editar / cancelar órdenes propias</label>
                                            </div>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" value="descargar_documentos" name="permisos_empresa[]" id="perm_descargar_documentos" {{ in_array('descargar_documentos', $permisosSeleccionados, true) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="perm_descargar_documentos">Ver y descargar papelería</label>
                                            </div>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" value="ver_reportes" name="permisos_empresa[]" id="perm_ver_reportes" {{ in_array('ver_reportes', $permisosSeleccionados, true) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="perm_ver_reportes">Ver reportes</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <div class="d-flex justify-content-between">
                                <a href="{{ route('empresa.usuarios') }}" class="btn btn-outline-secondary" id="btn-volver">
                                    <i class="bi bi-arrow-left"></i> Volver
                                </a>
                                <button type="submit" class="btn btn-success" id="btn-crear-usuario">
                                    <span class="btn-text">
                                        <i class="bi bi-check-lg"></i> Crear Usuario
                                    </span>
                                    <span class="btn-loading d-none">
                                        <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                        Procesando...
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card bg-success-subtle">
                    <div class="card-body">
                        <h6 class="card-title"><i class="bi bi-info-circle"></i> Información</h6>
                        <p class="card-text small">
                            Los <strong>trabajadores</strong> (reclutadores/asistentes) acceden al panel con los permisos que marque abajo.
                        </p>
                        <ul class="small mb-0">
                            <li>Ver órdenes y estado de procesos</li>
                            <li>Editar/cancelar órdenes propias (si aplica)</li>
                            <li>Subir y descargar papelería del candidato</li>
                            <li>Descargar PDF de orden de servicio</li>
                        </ul>
                        <p class="small text-muted mt-2 mb-0">Crear órdenes nuevas queda reservado al <strong>usuario principal</strong>, salvo que usted lo habilite explícitamente.</p>
                        <hr>
                        <p class="card-text small text-muted mb-0">
                            <i class="bi bi-shield-check"></i> Solo el usuario principal puede gestionar otros usuarios.
                        </p>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!-- Content wrapper end -->

</div>
<!-- Content wrapper scroll end -->

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form[action*="empresa.usuarios.store"]');
    const btnCrear = document.getElementById('btn-crear-usuario');
    const btnVolver = document.getElementById('btn-volver');
    let formSubmitting = false;
    
    if (form && btnCrear) {
        form.addEventListener('submit', function(e) {
            if (formSubmitting) {
                e.preventDefault();
                return false;
            }
            
            formSubmitting = true;
            btnCrear.disabled = true;
            btnCrear.querySelector('.btn-text').classList.add('d-none');
            btnCrear.querySelector('.btn-loading').classList.remove('d-none');
            
            if (btnVolver) {
                btnVolver.classList.add('disabled');
                btnVolver.style.pointerEvents = 'none';
            }
            
            return true;
        });
    }
});
</script>
@endpush
