@extends('layouts.admin')
@section('content')

<!-- Content wrapper scroll start -->
<div class="content-wrapper-scroll">

    <!-- Main header starts -->
    <div class="main-header d-flex align-items-center justify-content-between position-relative">
        <div class="d-flex align-items-center justify-content-center">
            <div class="page-icon">
                <i class="bi bi-building-add"></i>
            </div>
            <div class="page-title">
                <h5>Nueva Empresa</h5>
            </div>
        </div>
        <div class="d-flex align-items-end d-none d-sm-block">
            <h6 class="float-end text-light" id="reloj"></h6>
        </div>
    </div>
    <!-- Main header ends -->

    <!-- Content wrapper start -->
    <div class="content-wrapper">

        <!-- Row start -->
        <div class="row gx-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-8">
                                <h5 class="card-title">Registrar Nueva Empresa</h5>
                            </div>
                            <div class="col-4 text-end">
                                <a href="{{ url('empresas') }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-arrow-left"></i> Volver al listado
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        @if (count($errors)>0)
                            <div class="alert alert-danger mb-4" role="alert">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{$error}}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ url('insert-empresa') }}" method="POST" enctype="multipart/form-data" id="empresaForm">
                            @csrf
                            <div class="row">
                                <div class="col-lg-8">
                                    <!-- Información general de la empresa -->
                                    <div class="card mb-3">
                                        <div class="card-header">
                                            <h6 class="mb-0">Información General</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="nombre" class="form-label">Nombre de la Empresa <span class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="bi bi-building"></i></span>
                                                        <input type="text" name="nombre" id="nombre" class="form-control" value="{{ old('nombre') }}" required>
                                                    </div>
                                                    @if ($errors->has('nombre'))
                                                        <div class="text-danger mt-1">{{ $errors->first('nombre') }}</div>
                                                    @endif
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="nit" class="form-label">NIT</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="bi bi-file-earmark-text"></i></span>
                                                        <input type="text" name="nit" id="nit" class="form-control" value="{{ old('nit') }}">
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="telefono" class="form-label">Teléfono</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                                        <input type="text" name="telefono" id="telefono" class="form-control" value="{{ old('telefono') }}">
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="email" class="form-label">Email</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                                        <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}">
                                                    </div>
                                                </div>
                                                <div class="col-md-12 mb-3">
                                                    <label for="direccion" class="form-label">Dirección</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                                                        <textarea name="direccion" id="direccion" class="form-control" rows="2">{{ old('direccion') }}</textarea>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="sitio_web" class="form-label">Sitio Web</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="bi bi-globe"></i></span>
                                                        <input type="url" name="sitio_web" id="sitio_web" class="form-control" placeholder="https://www.ejemplo.com" value="{{ old('sitio_web') }}">
                                                    </div>
                                                </div>
                                                <div class="col-md-12 mb-3">
                                                    <label for="descripcion" class="form-label">Descripción</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="bi bi-card-text"></i></span>
                                                        <textarea name="descripcion" id="descripcion" class="form-control" rows="3">{{ old('descripcion') }}</textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Información de contacto principal -->
                                    <div class="card mb-3">
                                        <div class="card-header">
                                            <h6 class="mb-0">Información de Contacto Principal</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="contacto_nombre" class="form-label">Nombre del Contacto</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                                                        <input type="text" name="contacto_nombre" id="contacto_nombre" class="form-control" value="{{ old('contacto_nombre') }}">
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="contacto_cargo" class="form-label">Cargo</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="bi bi-briefcase"></i></span>
                                                        <input type="text" name="contacto_cargo" id="contacto_cargo" class="form-control" value="{{ old('contacto_cargo') }}">
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="contacto_telefono" class="form-label">Teléfono de Contacto</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                                        <input type="text" name="contacto_telefono" id="contacto_telefono" class="form-control" value="{{ old('contacto_telefono') }}">
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="contacto_email" class="form-label">Email de Contacto</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                                        <input type="email" name="contacto_email" id="contacto_email" class="form-control" value="{{ old('contacto_email') }}">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Notas adicionales -->
                                    <div class="card mb-3">
                                        <div class="card-header">
                                            <h6 class="mb-0">Notas Adicionales</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-12">
                                                    <textarea name="notas" id="notas" class="form-control" rows="4" placeholder="Información adicional relevante sobre la empresa...">{{ old('notas') }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <!-- Logo de la empresa -->
                                    <div class="card mb-3">
                                        <div class="card-header">
                                            <h6 class="mb-0">Logo de la Empresa</h6>
                                        </div>
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <img id="preview" src="{{ asset('assets/imgs/placeholder-image.png') }}" alt="Vista previa del logo" class="img-fluid rounded border" style="max-height: 200px;">
                                            </div>
                                            <input type="file" name="logo" id="logo" class="form-control" accept="image/*" style="display: none;">
                                            <label for="logo" class="btn btn-outline-primary">
                                                <i class="bi bi-upload"></i> Seleccionar Logo
                                            </label>
                                            <p class="text-muted small mt-2">Formatos permitidos: JPG, PNG, GIF. Máximo 2MB.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-12 text-center">
                                    <button type="button" class="btn btn-danger" onclick="window.location.href='{{ url('empresas') }}'">
                                        <i class="bi bi-x-circle"></i> Cancelar
                                    </button>
                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-save"></i> Guardar Empresa
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- Row end -->

    </div>
    <!-- Content wrapper end -->
</div>
<!-- Content wrapper scroll end -->

@push('scripts')
<script>
    $(document).ready(function() {
        // Vista previa de la imagen
        $("#logo").change(function() {
            const file = this.files[0];
            if (file) {
                let reader = new FileReader();
                reader.onload = function(event) {
                    $("#preview").attr("src", event.target.result);
                };
                reader.readAsDataURL(file);
            }
        });

        // Validación del formulario
        $("#empresaForm").submit(function(e) {
            const nombre = $("#nombre").val().trim();
            
            if (nombre === "") {
                e.preventDefault();
                alert("El nombre de la empresa es obligatorio");
                $("#nombre").focus();
                return false;
            }

            // Validación de email si está presente
            const email = $("#email").val().trim();
            if (email !== "") {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    e.preventDefault();
                    alert("Por favor ingrese un correo electrónico válido");
                    $("#email").focus();
                    return false;
                }
            }

            // Validación de email de contacto si está presente
            const contactoEmail = $("#contacto_email").val().trim();
            if (contactoEmail !== "") {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(contactoEmail)) {
                    e.preventDefault();
                    alert("Por favor ingrese un correo electrónico de contacto válido");
                    $("#contacto_email").focus();
                    return false;
                }
            }

            // Validación de URL si está presente
            const sitioWeb = $("#sitio_web").val().trim();
            if (sitioWeb !== "") {
                try {
                    new URL(sitioWeb);
                } catch (_) {
                    e.preventDefault();
                    alert("Por favor ingrese una URL válida para el sitio web");
                    $("#sitio_web").focus();
                    return false;
                }
            }
            
            return true;
        });
    });
</script>
@endpush

@endsection
