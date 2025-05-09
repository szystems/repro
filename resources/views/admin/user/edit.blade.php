@extends('layouts.admin')
@section('content')

    <!-- Content wrapper scroll start -->
    <div class="content-wrapper-scroll">

        <!-- Main header starts -->
        <div class="main-header d-flex align-items-center justify-content-between position-relative">
            <div class="d-flex align-items-center justify-content-center">
                <div class="page-icon">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div class="page-title">
                    <h5>Usuarios</h5>
                </div>
            </div>
            <!-- Date range start -->
            <div class="d-flex align-items-end d-none d-sm-block">
                <h6 class="float-end text-light" id="reloj"></h6>
            </div>
        </div>
        <!-- Main header ends -->

        <!-- Content wrapper start -->
        <div class="content-wrapper">
            <div class="subscribe-header">
                <img src="{{ asset('dashboardtemplate/design/assets/images/bg.jpg') }}" class="img-fluid w-100" alt="Header" />
            </div>
            <div class="subscriber-body">
                <!-- Row start -->
                <div class="row justify-content-center mt-4">
                    <div class="col-lg-12">
                        <!-- Row start -->
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <div class="position-relative" id="profile-container">
                                    @if ($user->fotografia != null)
                                        <img id="profileImage" src="{{ asset('assets/imgs/users/'.$user->fotografia) }}" class="img-7xx rounded-circle shadow border border-2 border-light" />
                                    @else
                                        <img id="profileImage" src="{{ asset('assets/imgs/users/usericon4.png') }}" class="img-7xx rounded-circle shadow border border-2 border-light" />
                                    @endif
                                    <div class="position-absolute bottom-0 end-0">
                                        <label for="imageUpload" class="btn btn-sm btn-primary rounded-circle">
                                            <i class="bi bi-camera-fill"></i>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <h5 class="text-primary mb-1">Editar Usuario</h5>
                                <h4 class="m-0">{{ $user->name }}</h4>
                                <span class="badge
                                    @if($user->role_as == 0) bg-secondary
                                    @elseif($user->role_as == 1) bg-success
                                    @elseif($user->role_as == 2) bg-info
                                    @elseif($user->role_as == 3) bg-danger
                                    @endif">
                                    {{ $user->getRoleName() }}
                                </span>
                            </div>
                            <div class="col-12 col-md-auto">
                                <div class="btn-group">
                                    <a href="{{ url('show-user/'.$user->id) }}" class="btn btn-outline-secondary">
                                        <i class="bi bi-arrow-left"></i> Volver al perfil
                                    </a>
                                    <a href="{{ url('users') }}" class="btn btn-outline-info">
                                        <i class="bi bi-list"></i> Listado
                                    </a>
                                </div>
                            </div>
                        </div>
                        <!-- Row end -->
                    </div>
                </div>
                <!-- Row end -->

                <!-- Row start -->
                <div class="row justify-content-center mt-4">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title"><i class="bi bi-pencil-square"></i> Editar Información</h5>
                            </div>
                            <div class="card-body">
                                @if (count($errors)>0)
                                    <div class="alert alert-danger" role="alert">
                                        <ul class="mb-0">
                                            @foreach ($errors->all() as $error)
                                                <li>{{$error}}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                @if(session('error'))
                                    <div class="alert alert-danger">
                                        {{ session('error') }}
                                    </div>
                                @endif

                                <form action="{{ url('update-user/'.$user->id) }}" method="POST" enctype="multipart/form-data" id="userForm">
                                    @csrf
                                    @method('PUT')
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="row gx-3">
                                                <!-- Información básica -->
                                                <div class="col-md-6 mb-3">
                                                    <label for="name" class="form-label">Nombre <span class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                                                        <input name="name" type="text" class="form-control" placeholder="Nombre completo" value="{{ $user->name }}" required />
                                                    </div>
                                                    @if ($errors->has('name'))
                                                        <div class="text-danger mt-1">{{ $errors->first('name') }}</div>
                                                    @endif
                                                </div>

                                                <div class="col-md-6 mb-3">
                                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="bi bi-envelope-fill"></i></span>
                                                        <input name="email" type="email" class="form-control" placeholder="correo@ejemplo.com" value="{{ $user->email }}" required />
                                                    </div>
                                                    @if ($errors->has('email'))
                                                        <div class="text-danger mt-1">{{ $errors->first('email') }}</div>
                                                    @endif
                                                </div>

                                                <div class="col-md-6 mb-3">
                                                    <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento <span class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="bi bi-calendar-date"></i></span>
                                                        <input type="date" name="fecha_nacimiento" class="form-control" value="{{ $user->fecha_nacimiento }}" required/>
                                                    </div>
                                                    @if ($errors->has('fecha_nacimiento'))
                                                        <div class="text-danger mt-1">{{ $errors->first('fecha_nacimiento') }}</div>
                                                    @endif
                                                </div>

                                                <!-- Tipo de usuario (solo para administradores) -->
                                                @if($canEditRole)
                                                <div class="col-md-6 mb-3">
                                                    <label for="role_as" class="form-label">Tipo de Usuario <span class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="bi bi-shield-fill"></i></span>
                                                        <select name="role_as" id="role_as" class="form-select" required>
                                                            <option value="0" {{ $user->role_as == 0 ? 'selected' : '' }}>
                                                                Evaluado (Persona a evaluar)
                                                            </option>
                                                            <option value="1" {{ $user->role_as == 1 ? 'selected' : '' }}>
                                                                Empresa (Usuario empresa cliente)
                                                            </option>
                                                            <option value="2" {{ $user->role_as == 2 ? 'selected' : '' }}>
                                                                Repro (Personal de Repro)
                                                            </option>
                                                            <option value="3" {{ $user->role_as == 3 ? 'selected' : '' }}>
                                                                Administrador (Acceso completo)
                                                            </option>
                                                        </select>
                                                    </div>
                                                </div>
                                                @endif

                                                <!-- Checkbox de usuario principal - visible tanto para Empresa como para Repro -->
                                                <div class="principal-check-container col-12 mb-3" style="display: none;">
                                                    <div class="card bg-light mb-3">
                                                        <div class="card-body">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" value="1" name="principal" id="principal" {{ old('principal', $user->principal) == 1 ? 'checked' : '' }}>
                                                                <label class="form-check-label" for="principal">
                                                                    <strong>Usuario principal</strong>
                                                                </label>
                                                                <div class="form-text">
                                                                    El usuario principal tiene permisos para administrar otros usuarios de su empresa o categoría.
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Campos para usuario de empresa -->
                                                <div class="col-12 empresa-fields" style="display: none;">
                                                    <div class="card bg-light mb-3">
                                                        <div class="card-body">
                                                            <h6 class="card-title mb-3">Información de Empresa</h6>
                                                            <div class="row">
                                                                <div class="col-md-12 mb-3">
                                                                    <label for="empresa_id" class="form-label">Empresa <span class="text-danger">*</span></label>
                                                                    <div class="input-group">
                                                                        <span class="input-group-text"><i class="bi bi-building"></i></span>
                                                                        <select name="empresa_id" id="empresa_id" class="form-select" {{ $user->role_as == 1 ? 'required' : '' }}>
                                                                            <option value="">Seleccione la empresa</option>
                                                                            @foreach($empresas as $empresa)
                                                                                <option value="{{ $empresa->id }}" {{ (old('empresa_id', $user->empresa_id) == $empresa->id) ? 'selected' : '' }}>
                                                                                    {{ $empresa->nombre }}
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                    @if ($errors->has('empresa_id'))
                                                                        <div class="text-danger mt-1">{{ $errors->first('empresa_id') }}</div>
                                                                    @endif
                                                                </div>

                                                                <div class="col-md-12 mb-3">
                                                                    <label for="cargo_empresa" class="form-label">Cargo en la empresa</label>
                                                                    <div class="input-group">
                                                                        <span class="input-group-text"><i class="bi bi-briefcase"></i></span>
                                                                        <input name="cargo" id="cargo_empresa" type="text" class="form-control" placeholder="Ej: Gerente de RRHH" value="{{ old('cargo', $user->cargo) }}" />
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Campos para usuario de Repro -->
                                                <div class="col-12 repro-fields" style="display: none;">
                                                    <div class="card bg-light mb-3">
                                                        <div class="card-body">
                                                            <h6 class="card-title mb-3">Información de Personal REPRO</h6>

                                                            <div class="mb-3">
                                                                <label for="cargo_repro" class="form-label">Cargo en Repro <span class="text-danger">*</span></label>
                                                                <div class="input-group">
                                                                    <span class="input-group-text"><i class="bi bi-briefcase"></i></span>
                                                                    <input name="cargo" id="cargo_repro" type="text" class="form-control" placeholder="Ej: Poligrafista" value="{{ old('cargo', $user->cargo) }}" />
                                                                </div>
                                                            </div>

                                                            @if(Auth::user()->role_as == 3) {{-- Solo administradores pueden editar permisos --}}
                                                            <div class="mb-0">
                                                                <label class="form-label">Permisos especiales</label>
                                                                <div class="form-text mb-2">
                                                                    Seleccione los permisos adicionales que tendrá este usuario:
                                                                </div>
                                                                <div class="row">
                                                                    @php
                                                                        $permisos = $user->permisos ? (is_array($user->permisos) ? $user->permisos : json_decode($user->permisos)) : [];
                                                                    @endphp
                                                                    <div class="col-md-4">
                                                                        <div class="form-check">
                                                                            <input class="form-check-input" type="checkbox" value="evaluaciones" name="permisos[]" id="permiso_evaluaciones" {{ in_array('evaluaciones', $permisos) ? 'checked' : '' }}>
                                                                            <label class="form-check-label" for="permiso_evaluaciones">
                                                                                Gestionar evaluaciones
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <div class="form-check">
                                                                            <input class="form-check-input" type="checkbox" value="empresas" name="permisos[]" id="permiso_empresas" {{ in_array('empresas', $permisos) ? 'checked' : '' }}>
                                                                            <label class="form-check-label" for="permiso_empresas">
                                                                                Gestionar empresas
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <div class="form-check">
                                                                            <input class="form-check-input" type="checkbox" value="reportes" name="permisos[]" id="permiso_reportes" {{ in_array('reportes', $permisos) ? 'checked' : '' }}>
                                                                            <label class="form-check-label" for="permiso_reportes">
                                                                                Generar reportes
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Información de contacto -->
                                                <div class="col-12 mt-3">
                                                    <h6 class="border-bottom pb-2">Información de contacto</h6>
                                                </div>

                                                <div class="col-md-6 mb-3">
                                                    <label for="telefono" class="form-label">Teléfono</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="bi bi-telephone-fill"></i></span>
                                                        <input name="telefono" type="text" pattern="[0-9]*" inputmode="numeric" class="form-control" placeholder="Teléfono fijo" value="{{ $user->telefono }}" />
                                                    </div>
                                                    @if ($errors->has('telefono'))
                                                        <div class="text-danger mt-1">{{ $errors->first('telefono') }}</div>
                                                    @endif
                                                </div>

                                                <div class="col-md-6 mb-3">
                                                    <label for="celular" class="form-label">Celular / WhatsApp</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="bi bi-phone-fill"></i></span>
                                                        <input name="celular" type="text" pattern="[0-9]*" inputmode="numeric" class="form-control" placeholder="Número de celular" value="{{ $user->celular }}"/>
                                                    </div>
                                                    @if ($errors->has('celular'))
                                                        <div class="text-danger mt-1">{{ $errors->first('celular') }}</div>
                                                    @endif
                                                </div>

                                                <div class="col-md-12 mb-3">
                                                    <label class="form-label">Dirección</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="bi bi-geo-alt-fill"></i></span>
                                                        <textarea name="direccion" class="form-control" rows="3" placeholder="Dirección completa">{{ $user->direccion }}</textarea>
                                                    </div>
                                                    @if ($errors->has('direccion'))
                                                        <div class="text-danger mt-1">{{ $errors->first('direccion') }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="card mb-3">
                                                <div class="card-header">
                                                    <h6 class="mb-0">Fotografía del Usuario</h6>
                                                </div>
                                                <div class="card-body text-center">
                                                    <div class="image-preview mb-3">
                                                        @if ($user->fotografia != null)
                                                            <img id="preview" src="{{ asset('assets/imgs/users/'.$user->fotografia) }}" class="img-fluid rounded" style="max-height: 200px" alt="Vista previa de la imagen">
                                                        @else
                                                            <img id="preview" src="{{ asset('assets/imgs/users/usericon4.png') }}" class="img-fluid rounded" style="max-height: 200px" alt="Vista previa de la imagen">
                                                        @endif
                                                    </div>
                                                    <input type="file" name="fotografia" id="imageUpload" class="form-control border" accept="image/*" style="display: none;">
                                                    <label for="imageUpload" class="btn btn-outline-primary">
                                                        <i class="bi bi-upload"></i> Cambiar imagen
                                                    </label>
                                                    <p class="text-muted small mt-2">Formatos permitidos: JPG, PNG, GIF. Máximo 3MB.</p>
                                                    @if ($errors->has('fotografia'))
                                                        <div class="text-danger mt-1">{{ $errors->first('fotografia') }}</div>
                                                    @endif
                                                </div>
                                            </div>

                                            @if(Auth::user()->role_as >= 2 && Auth::user()->id != $user->id)
                                            <div class="card mb-3">
                                                <div class="card-header bg-warning">
                                                    <h6 class="mb-0"><i class="bi bi-key"></i> Restablecer contraseña</h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" value="1" id="reset_password" name="reset_password">
                                                        <label class="form-check-label" for="reset_password">
                                                            Generar nueva contraseña
                                                        </label>
                                                    </div>
                                                    <p class="small text-muted mt-2">
                                                        Al marcar esta opción, se generará una nueva contraseña para el usuario
                                                        y se enviará a su correo electrónico.
                                                    </p>
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="d-flex gap-2 justify-content-center mt-4">
                                        <a href="{{ url('show-user/'.$user->id) }}" class="btn btn-danger">
                                            <i class="bi bi-x-circle"></i> Cancelar
                                        </a>
                                        <button type="submit" class="btn btn-success">
                                            <i class="bi bi-check2-square"></i> Guardar Cambios
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Row end -->
            </div>
        </div>
        <!-- Content wrapper end -->
    </div>
    <!-- Content wrapper scroll end -->

    @push('scripts')
    <script>
        $(document).ready(function() {
            console.log("Document ready");

            // Vista previa de imagen
            $("#imageUpload").change(function() {
                const file = this.files[0];
                if (file) {
                    let reader = new FileReader();
                    reader.onload = function(event) {
                        // Actualizar ambas imágenes con animación
                        $("#preview").fadeOut(300, function() {
                            $(this).attr("src", event.target.result).fadeIn(300);
                        });
                        $("#profileImage").fadeOut(300, function() {
                            $(this).attr("src", event.target.result).fadeIn(300);
                        });
                    };
                    reader.readAsDataURL(file);
                } else {
                    // Si no hay archivo seleccionado, volver a la imagen predeterminada o la existente
                    @if ($user->fotografia != null)
                        var defaultImg = "{{ asset('assets/imgs/users/'.$user->fotografia) }}";
                    @else
                        var defaultImg = "{{ asset('assets/imgs/users/usericon4.png') }}";
                    @endif

                    $("#preview").attr("src", defaultImg);
                    $("#profileImage").attr("src", defaultImg);
                }
            });

            // Mostrar/ocultar campos según el tipo de usuario
            function updateFieldsVisibility() {
                var role = $("#role_as").val();
                console.log("Role value:", role);

                // Ocultar todos los campos específicos
                $(".empresa-fields").hide();
                $(".repro-fields").hide();
                $(".principal-check-container").hide();

                // Mostrar campos relevantes según rol seleccionado
                if (role == "1") {
                    console.log("Showing empresa fields");
                    $(".empresa-fields").show();
                    $(".principal-check-container").show();
                    $("#empresa_id").prop('required', true);
                } else {
                    $("#empresa_id").prop('required', false);
                }

                if (role == "2") {
                    $(".repro-fields").show();
                    $(".principal-check-container").show();
                }
            }

            $("#role_as").change(function() {
                var newRole = $(this).val();
                var oldRole = "{{ $user->role_as }}";
                console.log("Role changed from", oldRole, "to", newRole);

                // Mostrar mensaje de advertencia si cambia el rol
                if (newRole != oldRole) {
                    if (!confirm("¿Está seguro de cambiar el tipo de usuario? Algunos datos específicos podrían perderse.")) {
                        $(this).val(oldRole);
                        return;
                    }
                }

                updateFieldsVisibility();
            });

            // Inicializar los campos según el rol actual
            console.log("Initial role:", $("#role_as").val());
            updateFieldsVisibility();

            // Validación del formulario
            $("#userForm").submit(function(e) {
                var email = $("input[name='email']").val();
                var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                if (!emailRegex.test(email)) {
                    e.preventDefault();
                    alert("Por favor ingrese un correo electrónico válido");
                    return false;
                }

                // Validación específica según el tipo de usuario actual
                var currentRole = $("#role_as").val() || "{{ $user->role_as }}";

                // Validar empresa seleccionada para usuarios tipo empresa
                if (currentRole == "1") {
                    if ($("#empresa_id").val() == "") {
                        e.preventDefault();
                        alert("Por favor seleccione una empresa para el usuario tipo Empresa");
                        $("#empresa_id").focus();
                        return false;
                    }
                }

                // Validar cargo para usuarios tipo Repro
                if (currentRole == "2") {
                    // Usar un selector específico para obtener el campo de cargo visible
                    var cargoValue = $("#cargo_repro").val();
                    console.log("Cargo de Repro:", cargoValue);

                    if (!cargoValue || cargoValue.trim() === "") {
                        e.preventDefault();
                        alert("Por favor ingrese el cargo para el usuario de Repro");
                        $("#cargo_repro").focus();
                        return false;
                    }
                }

                return true;
            });

            // Convertir inputs de teléfono a solo números
            $("input[name='telefono'], input[name='celular']").on('input', function() {
                $(this).val($(this).val().replace(/[^0-9]/g, ''));
            });
        });
    </script>
    @endpush

@endsection
