@extends('layouts.admin')

@section('content')
    <!-- Content wrapper scroll start -->
    <div class="content-wrapper-scroll">

        <!-- Main header starts -->
        <div class="main-header d-flex align-items-center justify-content-between position-relative">
            <div class="d-flex align-items-center justify-content-center">
                <div class="page-icon">
                    <i class="bi bi-shield-plus"></i>
                </div>
                <div class="page-title">
                    <h5>Crear Nuevo Rol</h5>
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
                                    <h5 class="mb-0"><i class="bi bi-shield-plus"></i> Información del Rol</h5>
                                </div>
                                <div>
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

                            <form action="{{ url('admin/roles') }}" method="POST" id="roleForm">
                                @csrf
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="row gx-3">
                                            <!-- Información básica del rol -->
                                            <div class="col-md-6 mb-3">
                                                <label for="name" class="form-label">Nombre del Rol <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bi bi-tag"></i></span>
                                                    <input name="name" type="text" class="form-control" placeholder="nombre_rol" value="{{ old('name') }}" required />
                                                </div>
                                                <div class="form-text">Nombre interno del rol (sin espacios, minúsculas)</div>
                                                @if ($errors->has('name'))
                                                    <div class="text-danger mt-1">{{ $errors->first('name') }}</div>
                                                @endif
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label for="display_name" class="form-label">Nombre Visible <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bi bi-eye"></i></span>
                                                    <input name="display_name" type="text" class="form-control" placeholder="Nombre para mostrar" value="{{ old('display_name') }}" required />
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
                                                    <textarea name="description" class="form-control" rows="3" placeholder="Descripción del rol y sus responsabilidades">{{ old('description') }}</textarea>
                                                </div>
                                                @if ($errors->has('description'))
                                                    <div class="text-danger mt-1">{{ $errors->first('description') }}</div>
                                                @endif
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label for="level" class="form-label">Nivel del Rol <span class="text-danger">*</span></label>
                                                <select name="level" id="level" class="form-select" required>
                                                    <option value="">— Seleccione el nivel —</option>
                                                    <option value="1" {{ old('level') == 1 ? 'selected' : '' }}>1 — Empresa (cliente)</option>
                                                    <option value="2" {{ old('level') == 2 ? 'selected' : '' }}>2 — Colaborador REPRO</option>
                                                    <option value="3" {{ old('level') == 3 ? 'selected' : '' }}>3 — Administrador</option>
                                                </select>
                                                <div class="form-text">
                                                    Determina qué tipo de usuario usará este rol y filtra los permisos disponibles.
                                                </div>
                                                @if ($errors->has('level'))
                                                    <div class="text-danger mt-1">{{ $errors->first('level') }}</div>
                                                @endif
                                            </div>

                                            <!-- Permisos -->
                                            <div class="col-12 mt-3">
                                                <h6 class="border-bottom pb-2"><i class="bi bi-key"></i> Permisos del Rol</h6>
                                                <div class="form-text mb-3">
                                                    Seleccione los permisos que tendrán los usuarios con este rol:
                                                    <span id="permisos-filtro-nota" class="badge bg-info ms-2 d-none">
                                                        <i class="bi bi-funnel"></i> Mostrando solo permisos para el nivel seleccionado
                                                    </span>
                                                </div>

                                                @if(isset($permissions) && $permissions->count() > 0)
                                                    @php $empresaModulesJson = json_encode($empresaModules ?? []); @endphp
                                                    @foreach($permissions as $module => $modulePermissions)
                                                    @php $minLevel = in_array($module, $empresaModules ?? []) ? 1 : 2; @endphp
                                                    <div class="card mb-3 permission-module-card" data-module="{{ $module }}" data-min-level="{{ $minLevel }}">
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
                                                                        <input class="form-check-input permission-checkbox" type="checkbox" value="{{ $permission->id }}" name="permissions[]" id="permission_{{ $permission->id }}" data-module="{{ $module }}" {{ (old('permissions') && in_array($permission->id, old('permissions'))) ? 'checked' : '' }}>
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
                                                <h6 class="mb-0"><i class="bi bi-info-circle"></i> Información</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="alert alert-info">
                                                    <h6><i class="bi bi-lightbulb"></i> Consejos:</h6>
                                                    <ul class="small mb-0">
                                                        <li>Use nombres descriptivos para los roles</li>
                                                        <li>Asigne solo los permisos necesarios</li>
                                                        <li>Los permisos por módulo facilitan la gestión</li>
                                                        <li>Puede editar los permisos después</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card">
                                            <div class="card-header">
                                                <h6 class="mb-0"><i class="bi bi-check2-square"></i> Resumen</h6>
                                            </div>
                                            <div class="card-body">
                                                <div id="permissions-summary">
                                                    <p class="text-muted small">Seleccione permisos para ver el resumen</p>
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
                                        <i class="bi bi-check2-square"></i> Crear Rol
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
            // Filtrar módulos de permisos según el nivel seleccionado
            function filterModulesByLevel(level) {
                level = parseInt(level) || 0;
                $('.permission-module-card').each(function() {
                    const minLevel = parseInt($(this).data('min-level'));
                    if (level > 0 && level < minLevel) {
                        $(this).hide();
                        // Desmarcar permisos ocultos para no enviarlos
                        $(this).find('.permission-checkbox').prop('checked', false);
                        $(this).find('.module-checkbox').prop('checked', false);
                    } else {
                        $(this).show();
                    }
                });
                if (level > 0 && level < 3) {
                    $('#permisos-filtro-nota').removeClass('d-none');
                } else {
                    $('#permisos-filtro-nota').addClass('d-none');
                }
                updateSummary();
            }

            // Filtrar al cambiar el nivel
            $('#level').change(function() {
                filterModulesByLevel($(this).val());
            });

            // Aplicar filtro inicial si hay valor old()
            const initialLevel = $('#level').val();
            if (initialLevel) {
                filterModulesByLevel(initialLevel);
            }

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
                    summaryHtml = '<p class="text-muted small">Seleccione permisos para ver el resumen</p>';
                }
                
                $('#permissions-summary').html(summaryHtml);
            }

            // Generar nombre interno automáticamente
            $('input[name="display_name"]').on('input', function() {
                const displayName = $(this).val();
                const internalName = displayName
                    .toLowerCase()
                    .replace(/\s+/g, '_')
                    .replace(/[^a-z0-9_]/g, '');
                
                if ($('input[name="name"]').val() === '') {
                    $('input[name="name"]').val(internalName);
                }
            });

            // Validación del formulario
            $('#roleForm').submit(function(e) {
                const selectedPermissions = $('.permission-checkbox:checked').length;
                
                if (selectedPermissions === 0) {
                    e.preventDefault();
                    alert('Debe seleccionar al menos un permiso para el rol');
                    return false;
                }
                
                return true;
            });

            // Inicializar el resumen
            updateSummary();
        });
    </script>
    @endpush
@endsection