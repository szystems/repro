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
                                    <img id="profileImage" src="{{ asset('assets/imgs/users/usericon4.png') }}" class="img-7xx rounded-circle shadow border border-2 border-light" />
                                    <div class="position-absolute bottom-0 end-0">
                                        <label for="imageUpload" class="btn btn-sm btn-primary rounded-circle">
                                            <i class="bi bi-camera-fill"></i>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <h5 class="text-primary mb-1">Crear Nuevo Usuario</h5>
                                <p class="text-muted">Complete todos los campos requeridos (*)</p>
                            </div>
                            <div class="col-12 col-md-auto">
                                <div class="btn-group">
                                    <a href="{{ url('users') }}" class="btn btn-outline-secondary">
                                        <i class="bi bi-arrow-left"></i> Volver al listado
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
                                <h5 class="card-title"><i class="bi bi-person-plus-fill"></i> Información del Usuario</h5>
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

                                <form action="{{ url('insert-user') }}" method="POST" enctype="multipart/form-data" id="userForm">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="row gx-3">
                                                <!-- Información básica -->
                                                <div class="col-md-6 mb-3">
                                                    <label for="name" class="form-label">Nombre <span class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                                                        <input name="name" type="text" class="form-control" placeholder="Nombre completo" value="{{ old('name') }}" required />
                                                    </div>
                                                    @if ($errors->has('name'))
                                                        <div class="text-danger mt-1">{{ $errors->first('name') }}</div>
                                                    @endif
                                                </div>

                                                <div class="col-md-6 mb-3">
                                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="bi bi-envelope-fill"></i></span>
                                                        <input name="email" type="email" class="form-control" placeholder="correo@ejemplo.com" value="{{ old('email') }}" required />
                                                    </div>
                                                    @if ($errors->has('email'))
                                                        <div class="text-danger mt-1">{{ $errors->first('email') }}</div>
                                                    @endif
                                                </div>

                                                <div class="col-md-6 mb-3">
                                                    <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento <span class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="bi bi-calendar-date"></i></span>
                                                        <input type="date" name="fecha_nacimiento" class="form-control" value="{{ old('fecha_nacimiento') }}" required/>
                                                    </div>
                                                    @if ($errors->has('fecha_nacimiento'))
                                                        <div class="text-danger mt-1">{{ $errors->first('fecha_nacimiento') }}</div>
                                                    @endif
                                                </div>

                                                <div class="col-md-6 mb-3">
                                                    <label for="documento_identidad" class="form-label">Documento de Identidad</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="bi bi-card-text"></i></span>
                                                        <input name="documento_identidad" type="text" class="form-control" placeholder="Número de documento" value="{{ old('documento_identidad') }}" />
                                                    </div>
                                                    @if ($errors->has('documento_identidad'))
                                                        <div class="text-danger mt-1">{{ $errors->first('documento_identidad') }}</div>
                                                    @endif
                                                </div>

                                                <div class="col-md-6 mb-3">
                                                    <label for="tipo_documento" class="form-label">Tipo de Documento</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="bi bi-card-list"></i></span>
                                                        <select name="tipo_documento" class="form-select">
                                                            <option value="">Seleccione tipo de documento</option>
                                                            <option value="DPI" {{ old('tipo_documento') == 'DPI' ? 'selected' : '' }}>DPI</option>
                                                            <option value="Pasaporte" {{ old('tipo_documento') == 'Pasaporte' ? 'selected' : '' }}>Pasaporte</option>
                                                            <option value="Licencia" {{ old('tipo_documento') == 'Licencia' ? 'selected' : '' }}>Licencia</option>
                                                        </select>
                                                    </div>
                                                    @if ($errors->has('tipo_documento'))
                                                        <div class="text-danger mt-1">{{ $errors->first('tipo_documento') }}</div>
                                                    @endif
                                                </div>

                                                <!-- Tipo de usuario -->
                                                <div class="col-md-6 mb-3">
                                                    <label for="role_as" class="form-label">Tipo de Usuario <span class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="bi bi-shield-fill"></i></span>
                                                        <select name="role_as" id="role_as" class="form-select" required>
                                                            <option value="" disabled selected>Seleccione tipo de usuario</option>

                                                            {{-- NOTA: Evaluados ya NO son usuarios del sistema --}}
                                                            {{-- Se crean en tabla evaluados_orden al generar órdenes --}}

                                                            @if($canCreateEmpresa)
                                                            <option value="1" {{ old('role_as') == '1' ? 'selected' : '' }}>
                                                                Empresa (Usuario empresa cliente)
                                                            </option>
                                                            @endif

                                                            @if($canCreateRepro)
                                                            <option value="2" {{ old('role_as') == '2' ? 'selected' : '' }}>
                                                                Repro (Personal de Repro)
                                                            </option>
                                                            @endif

                                                            @if($canCreateAdmin)
                                                            <option value="3" {{ old('role_as') == '3' ? 'selected' : '' }}>
                                                                Administrador (Acceso completo)
                                                            </option>
                                                            @endif
                                                        </select>
                                                    </div>
                                                </div>

                                                <!-- Información para Administrador -->
                                                <div class="col-12 admin-info" style="display: none;">
                                                    <div class="alert alert-danger">
                                                        <h6><i class="bi bi-shield-fill-exclamation"></i> Permisos de Administrador</h6>
                                                        <p class="mb-0">Este usuario tendrá <strong>ACCESO COMPLETO</strong> al sistema:</p>
                                                        <ul class="small mb-0 mt-2">
                                                            <li>Todos los permisos de evaluaciones, órdenes y resultados</li>
                                                            <li>Gestión completa de empresas y usuarios</li>
                                                            <li>Administración de roles y permisos</li>
                                                            <li>Configuración del sistema</li>
                                                            <li>Acceso a todos los reportes y estadísticas</li>
                                                        </ul>
                                                        <div class="mt-2">
                                                            <small><strong>⚠️ Nota:</strong> Los administradores pueden gestionar todos los aspectos del sistema.</small>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Checkbox de usuario principal - visible tanto para Empresa como para Repro -->
                                                <div class="principal-check-container col-12 mb-3" style="display: none;">
                                                    <div class="card bg-light mb-3">
                                                        <div class="card-body">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" value="1" name="principal" id="principal" {{ old('principal') ? 'checked' : '' }}>
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
                                                                        <select name="empresa_id" id="empresa_id" class="form-select">
                                                                            <option value="">Seleccione la empresa</option>
                                                                            @foreach($empresas as $empresa)
                                                                                <option value="{{ $empresa->id }}" {{ old('empresa_id') == $empresa->id || (isset($empresa_id) && $empresa_id == $empresa->id) ? 'selected' : '' }}>
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
                                                                        <input name="cargo" id="cargo_empresa" type="text" class="form-control" placeholder="Ej: Gerente de RRHH" value="{{ old('cargo') }}" />
                                                                    </div>
                                                                </div>

                                                                <div class="col-md-12 mb-3">
                                                                    <div class="form-check">
                                                                        <input class="form-check-input" type="checkbox" value="1" name="principal" id="principal" {{ old('principal') ? 'checked' : '' }}>
                                                                        <label class="form-check-label" for="principal">
                                                                            Usuario principal de la empresa
                                                                        </label>
                                                                        <div class="form-text">
                                                                            El usuario principal tiene permisos para administrar otros usuarios de su empresa.
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="col-md-12">
                                                                    <div class="alert alert-success">
                                                                        <h6><i class="bi bi-building-check"></i> Permisos de Usuario Empresa</h6>
                                                                        <p class="mb-0">Este usuario tendrá automáticamente los permisos de <strong>Usuario Empresa</strong>:</p>
                                                                        <ul class="small mb-0 mt-2">
                                                                            <li>Crear y ver órdenes de evaluación</li>
                                                                            <li>Ver y descargar resultados de sus evaluaciones</li>
                                                                            <li>Gestionar usuarios de su empresa</li>
                                                                            <li>Ver evaluaciones de su empresa</li>
                                                                        </ul>
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
                                                            <div class="row">
                                                                <div class="col-md-12 mb-3">
                                                                    <label for="cargo_repro" class="form-label">Cargo en Repro <span class="text-danger">*</span></label>
                                                                    <div class="input-group">
                                                                        <span class="input-group-text"><i class="bi bi-briefcase"></i></span>
                                                                        <input name="cargo" id="cargo_repro" type="text" class="form-control" placeholder="Ej: Poligrafista" value="{{ old('cargo') }}" />
                                                                    </div>
                                                                </div>

                                                                <div class="col-md-12 mb-3">
                                                                    <div class="alert alert-info">
                                                                        <h6><i class="bi bi-shield-check"></i> Permisos Automáticos</h6>
                                                                        <p class="mb-0">Este usuario tendrá automáticamente todos los permisos de <strong>Personal Repro</strong>, que incluyen:</p>
                                                                        <ul class="small mb-0 mt-2">
                                                                            <li>Ver y gestionar evaluaciones y pruebas de polígrafo</li>
                                                                            <li>Ver resultados y generar reportes</li>
                                                                            <li>Ver información de empresas y usuarios</li>
                                                                            <li>Gestionar órdenes de evaluación</li>
                                                                        </ul>
                                                                    </div>
                                                                </div>
                                                            </div>
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
                                                        <input name="telefono" type="text" pattern="[0-9]*" inputmode="numeric" class="form-control" placeholder="Teléfono fijo" value="{{ old('telefono') }}" />
                                                    </div>
                                                    @if ($errors->has('telefono'))
                                                        <div class="text-danger mt-1">{{ $errors->first('telefono') }}</div>
                                                    @endif
                                                </div>

                                                <div class="col-md-6 mb-3">
                                                    <label for="celular" class="form-label">Celular / WhatsApp</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="bi bi-phone-fill"></i></span>
                                                        <input name="celular" type="text" pattern="[0-9]*" inputmode="numeric" class="form-control" placeholder="Número de celular" value="{{ old('celular') }}"/>
                                                    </div>
                                                    @if ($errors->has('celular'))
                                                        <div class="text-danger mt-1">{{ $errors->first('celular') }}</div>
                                                    @endif
                                                </div>

                                                <div class="col-md-12 mb-3">
                                                    <label class="form-label">Dirección</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="bi bi-geo-alt-fill"></i></span>
                                                        <textarea name="direccion" class="form-control" rows="3" placeholder="Dirección completa">{{ old('direccion') }}</textarea>
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
                                                        <img id="preview" src="{{ asset('assets/imgs/users/usericon4.png') }}" class="img-fluid rounded" style="max-height: 200px" alt="Vista previa de la imagen">
                                                    </div>
                                                    <input type="file" name="fotografia" id="imageUpload" class="form-control border" accept="image/*" value="{{ old('fotografia') }}" style="display: none;">
                                                    <label for="imageUpload" class="btn btn-outline-primary">
                                                        <i class="bi bi-upload"></i> Seleccionar imagen
                                                    </label>
                                                    <p class="text-muted small mt-2">Formatos permitidos: JPG, PNG, GIF. Máximo 3MB.</p>
                                                    @if ($errors->has('fotografia'))
                                                        <div class="text-danger mt-1">{{ $errors->first('fotografia') }}</div>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="card">
                                                <div class="card-header bg-warning">
                                                    <h6 class="mb-0"><i class="bi bi-key"></i> Nota sobre la contraseña</h6>
                                                </div>
                                                <div class="card-body">
                                                    <p class="small">
                                                        El sistema generará automáticamente una contraseña temporal para el nuevo usuario.
                                                    </p>
                                                    <p class="small">
                                                        Esta contraseña será enviada al correo electrónico que ingrese en el formulario.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex gap-2 justify-content-center mt-4">
                                        <a href="{{ url('users') }}" class="btn btn-danger">
                                            <i class="bi bi-x-circle"></i> Cancelar
                                        </a>
                                        <button type="submit" class="btn btn-success">
                                            <i class="bi bi-check2-square"></i> Guardar Usuario
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
            // Vista previa de imagen
            $("#imageUpload").change(function() {
                const file = this.files[0];
                if (file) {
                    let reader = new FileReader();
                    reader.onload = function(event) {
                        $("#preview").fadeOut(300, function() {
                            $(this).attr("src", event.target.result).fadeIn(300);
                        });
                        $("#profileImage").fadeOut(300, function() {
                            $(this).attr("src", event.target.result).fadeIn(300);
                        });
                    };
                    reader.readAsDataURL(file);
                } else {
                    $("#preview").attr("src", "{{ asset('assets/imgs/users/usericon4.png') }}");
                    $("#profileImage").attr("src", "{{ asset('assets/imgs/users/usericon4.png') }}");
                }
            });

            // Mostrar/ocultar campos según el tipo de usuario
            function updateFieldVisibility() {
                var role = $("#role_as").val();
                console.log("Role changed to:", role);

                // Ocultar todos los campos específicos primero
                $(".empresa-fields").hide();
                $(".repro-fields").hide();
                $(".admin-info").hide();
                $(".principal-check-container").hide();

                // Mostrar campos según el rol seleccionado
                if (role == "1") { // Empresa
                    $(".empresa-fields").show();
                    $(".principal-check-container").show();
                    $("#empresa_id").prop('required', true);
                } else {
                    $("#empresa_id").prop('required', false);
                }

                if (role == "2") { // Repro
                    $(".repro-fields").show();
                    $(".principal-check-container").show();
                }

                if (role == "3") { // Administrador
                    $(".admin-info").show();
                }
            }

            $("#role_as").change(function() {
                updateFieldVisibility();
            });

            // Inicializar los campos según el rol seleccionado al cargar
            updateFieldVisibility();

            // Si hay un valor preseleccionado para empresa_id o role_as, configurar correctamente
            @if((old('role_as') == 1) || (isset($empresa_id) && $empresa_id))
                $("#role_as").val("1");
                updateFieldVisibility();
            @endif

            // Validación del formulario
            $("#userForm").submit(function(e) {
                var email = $("input[name='email']").val();
                var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                var isValid = true;

                if (!emailRegex.test(email)) {
                    e.preventDefault();
                    alert("Por favor ingrese un correo electrónico válido");
                    isValid = false;
                }

                // Validar campos específicos según el rol
                var role = $("#role_as").val();

                if (role == "1" && $("#empresa_id").val() == "") {
                    e.preventDefault();
                    alert("Por favor seleccione una empresa para el usuario tipo Empresa");
                    $("#empresa_id").focus();
                    isValid = false;
                }

                if (role == "2" && $(".repro-fields:visible input[name='cargo']").val() == "") {
                    e.preventDefault();
                    alert("Por favor ingrese el cargo para el usuario de Repro");
                    $(".repro-fields input[name='cargo']").focus();
                    isValid = false;
                }

                return isValid;
            });

            // Convertir inputs de teléfono a solo números
            $("input[name='telefono'], input[name='celular']").on('input', function() {
                $(this).val($(this).val().replace(/[^0-9]/g, ''));
            });
        });
    </script>
    @endpush

@endsection
