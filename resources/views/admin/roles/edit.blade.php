@extends('layouts.admin')

@section('content')
    <!-- Content wrapper scroll start -->
    <div class="content-wrapper-scroll">

        <!-- Main header starts -->
        <div class="main-header d-flex align-items-center justify-content-between position-relative">
            <div class="d-flex align-items-center justify-content-center">
                <div class="page-icon">
                    <i class="bi bi-shield-exclamation"></i>
                </div>
                <div class="page-title">
                    <h5>Editar Rol</h5>
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

            <!-- Row start -->
            <div class="row gx-3">
                <div class="col-sm-12 col-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-0"><i class="bi bi-shield-exclamation"></i> Editando: {{ $role->display_name }}</h5>
                                </div>
                                <div class="d-flex gap-2 align-items-center">
                                    @include('partials._ayuda_contextual')
                                    <a href="{{ url('admin/roles') }}" class="btn btn-outline-secondary">
                                        <i class="bi bi-arrow-left"></i> Volver al listado
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            @if (count($errors) > 0)
                                <div class="alert alert-danger" role="alert">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if(session('error'))
                                <div class="alert alert-danger">
                                    {{ session('error') }}
                                </div>
                            @endif

                            <form action="{{ url('admin/roles/'.$role->id) }}" method="POST" id="roleForm">
                                @csrf
                                @method('PUT')
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="row gx-3">
                                            <!-- Información básica del rol -->
                                            <div class="col-md-6 mb-3">
                                                <label for="name" class="form-label">Nombre del Rol <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bi bi-tag"></i></span>
                                                    <input name="name" type="text" class="form-control" placeholder="nombre_rol" value="{{ old('name', $role->name) }}" required {{ in_array($role->name, ['admin', 'repro', 'empresa', 'evaluado']) ? 'readonly' : '' }} />
                                                </div>
                                                @if(in_array($role->name, ['admin', 'repro', 'empresa', 'evaluado']))
                                                    <div class="form-text text-warning">Este es un rol del sistema y no se puede cambiar el nombre</div>
                                                @else
                                                    <div class="form-text">Nombre interno del rol (sin espacios, minúsculas)</div>
                                                @endif
                                                @if ($errors->has('name'))
                                                    <div class="text-danger mt-1">{{ $errors->first('name') }}</div>
                                                @endif
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label for="display_name" class="form-label">Nombre Visible <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bi bi-eye"></i></span>
                                                    <input name="display_name" type="text" class="form-control" placeholder="Nombre para mostrar" value="{{ old('display_name', $role->display_name) }}" required />
                                                </div>
                                                <div class="form-text">Nombre que verán los usuarios</div>
                                                @if ($errors->has('display_name'))
                                                    <div class="text-danger mt-1">{{ $errors->first('display_name') }}</div>
                                                @endif
                                            </div>

                                            <div class="col-md-12 mb-3">
                                                <label for="description" class="form-label">Descripción</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bi bi-file-text"></i></span>
                                                    <textarea name="description" class="form-control" rows="3" placeholder="Descripción del rol y sus responsabilidades">{{ old('description', $role->description) }}</textarea>
                                                </div>
                                                @if ($errors->has('description'))
                                                    <div class="text-danger mt-1">{{ $errors->first('description') }}</div>
                                                @endif
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label for="level" class="form-label">Nivel del Rol <span class="text-danger">*</span></label>
                                                @if(in_array($role->name, ['admin', 'repro', 'empresa', 'evaluado']))
                                                    <input type="hidden" name="level" value="{{ $role->level }}">
                                                    <input type="text" class="form-control" value="{{ ['0' => 'Evaluado', '1' => 'Empresa', '2' => 'Colaborador REPRO', '3' => 'Administrador'][$role->level] ?? 'Desconocido' }}" readonly>
                                                    <div class="form-text text-warning">El nivel de los roles del sistema no se puede modificar.</div>
                                                @else
                                                    <select name="level" id="level" class="form-select" required>
                                                        <option value="1" {{ old('level', $role->level) == 1 ? 'selected' : '' }}>1 — Empresa (cliente)</option>
                                                        <option value="2" {{ old('level', $role->level) == 2 ? 'selected' : '' }}>2 — Colaborador REPRO</option>
                                                        <option value="3" {{ old('level', $role->level) == 3 ? 'selected' : '' }}>3 — Administrador</option>
                                                    </select>
                                                    <div class="form-text">Cambiarlo ajusta los permisos disponibles.</div>
                                                @endif
                                            </div>

                                            <!-- Permisos -->
                                            <div class="col-12 mt-3">
                                                <h6 class="border-bottom pb-2"><i class="bi bi-key"></i> Permisos del Rol</h6>
                                                <div class="form-text mb-3">
                                                    Modifique los permisos que tendrán los usuarios con este rol:
                                                    @if($role->level == 1)
                                                        <span class="badge bg-info ms-2">
                                                            <i class="bi bi-funnel"></i> Mostrando solo permisos relevantes para roles de empresa
                                                        </span>
                                                    @endif
                                                </div>

                                                @if(isset($permissions) && $permissions->count() > 0)
                                                    @foreach($permissions as $module => $modulePermissions)
                                                    <div class="card mb-3">
                                                        <div class="card-header">
                                                            <div class="form-check">
                                                                <input class="form-check-input module-checkbox" type="checkbox" id="module_{{ $module }}" data-module="{{ $module }}">
                                                                <label class="form-check-label fw-semibold" for="module_{{ $module }}">
                                                                    <i class="bi bi-folder"></i> {{ ucfirst($module) }}
                                                                    <small class="text-muted">({{ $modulePermissions->count() }} permisos)</small>
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="row">
                                                                @foreach($modulePermissions as $permission)
                                                                <div class="col-md-6 col-lg-4 mb-2">
                                                                    <div class="form-check">
                                                                        <input class="form-check-input permission-checkbox" type="checkbox" value="{{ $permission->id }}" name="permissions[]" id="permission_{{ $permission->id }}" data-module="{{ $module }}" {{ (old('permissions') ? in_array($permission->id, old('permissions')) : in_array($permission->id, $rolePermissions)) ? 'checked' : '' }}>
                                                                        <label class="form-check-label" for="permission_{{ $permission->id }}">
                                                                            {{ $permission->display_name }}
                                                                            @if($permission->description)
                                                                                <br><small class="text-muted">{{ $permission->description }}</small>
                                                                            @endif
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endforeach
                                                @else
                                                    <div class="alert alert-warning">
                                                        <i class="bi bi-exclamation-triangle"></i>
                                                        No hay permisos disponibles en el sistema.
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="card mb-3">
                                            <div class="card-header">
                                                <h6 class="mb-0"><i class="bi bi-info-circle"></i> Estado del Rol</h6>
                                            </div>
                                            <div class="card-body">
                                                <table class="table table-sm table-borderless">
                                                    <tr>
                                                        <td><strong>Usuarios:</strong></td>
                                                        <td><span class="badge bg-secondary">{{ $role->users->count() }}</span></td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Permisos:</strong></td>
                                                        <td><span class="badge bg-primary">{{ $role->permissions->count() }}</span></td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Tipo:</strong></td>
                                                        <td>
                                                            @if(in_array($role->name, ['admin', 'repro', 'empresa', 'evaluado']))
                                                                <span class="badge bg-warning text-dark">Sistema</span>
                                                            @else
                                                                <span class="badge bg-success">Personalizado</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>

                                        <div class="card">
                                            <div class="card-header">
                                                <h6 class="mb-0"><i class="bi bi-check2-square"></i> Resumen de Cambios</h6>
                                            </div>
                                            <div class="card-body">
                                                <div id="permissions-summary">
                                                    <p class="text-muted small">Los cambios se mostrarán aquí</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex gap-2 justify-content-center mt-4">
                                    <a href="{{ url('admin/roles') }}" class="btn btn-danger">
                                        <i class="bi bi-x-circle"></i> Cancelar
                                    </a>
                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-check2-square"></i> Actualizar Rol
                                    </button>
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
            // Manejar selección de módulos completos
            $('.module-checkbox').change(function() {
                const module = $(this).data('module');
                const isChecked = $(this).is(':checked');
                
                $(`.permission-checkbox[data-module="${module}"]`).prop('checked', isChecked);
                updateSummary();
            });

            // Manejar selección individual de permisos
            $('.permission-checkbox').change(function() {
                const module = $(this).data('module');
                const totalInModule = $(`.permission-checkbox[data-module="${module}"]`).length;
                const checkedInModule = $(`.permission-checkbox[data-module="${module}"]:checked`).length;
                
                // Actualizar el checkbox del módulo
                const moduleCheckbox = $(`.module-checkbox[data-module="${module}"]`);
                if (checkedInModule === 0) {
                    moduleCheckbox.prop('checked', false).prop('indeterminate', false);
                } else if (checkedInModule === totalInModule) {
                    moduleCheckbox.prop('checked', true).prop('indeterminate', false);
                } else {
                    moduleCheckbox.prop('checked', false).prop('indeterminate', true);
                }
                
                updateSummary();
            });

            // Actualizar resumen de permisos seleccionados
            function updateSummary() {
                const selectedPermissions = $('.permission-checkbox:checked');
                const modules = {};
                
                selectedPermissions.each(function() {
                    const module = $(this).data('module');
                    if (!modules[module]) {
                        modules[module] = 0;
                    }
                    modules[module]++;
                });
                
                let summaryHtml = '';
                if (Object.keys(modules).length > 0) {
                    summaryHtml = '<h6 class="small">Permisos seleccionados:</h6>';
                    for (const [module, count] of Object.entries(modules)) {
                        summaryHtml += `<div class="badge bg-primary me-1 mb-1">${module}: ${count}</div>`;
                    }
                    summaryHtml += `<div class="mt-2"><strong>Total: ${selectedPermissions.length} permisos</strong></div>`;
                } else {
                    summaryHtml = '<p class="text-warning small">¡Atención! No hay permisos seleccionados</p>';
                }
                
                $('#permissions-summary').html(summaryHtml);
            }

            // Inicializar estado de los checkboxes de módulo
            $('.module-checkbox').each(function() {
                const module = $(this).data('module');
                const totalInModule = $(`.permission-checkbox[data-module="${module}"]`).length;
                const checkedInModule = $(`.permission-checkbox[data-module="${module}"]:checked`).length;
                
                if (checkedInModule === 0) {
                    $(this).prop('checked', false).prop('indeterminate', false);
                } else if (checkedInModule === totalInModule) {
                    $(this).prop('checked', true).prop('indeterminate', false);
                } else {
                    $(this).prop('checked', false).prop('indeterminate', true);
                }
            });

            // Inicializar el resumen
            updateSummary();
        });
    </script>
    @endpush
@endsection