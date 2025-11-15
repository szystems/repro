@extends('layouts.admin')

@section('content')
    <!-- Content wrapper scroll start -->
    <div class="content-wrapper-scroll">

        <!-- Main header starts -->
        <div class="main-header d-flex align-items-center justify-content-between position-relative">
            <div class="d-flex align-items-center justify-content-center">
                <div class="page-icon">
                    <i class="bi bi-key"></i>
                </div>
                <div class="page-title">
                    <h5>Permisos del Sistema</h5>
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
                                    Permisos por Módulo
                                    <span class="badge bg-secondary">{{ $permissionsByModule->flatten()->count() }} permisos</span>
                                </div>
                                <div>
                                    <a href="{{ url('admin/roles') }}" class="btn btn-outline-secondary">
                                        <i class="bi bi-arrow-left"></i> Volver a Roles
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            @if($permissionsByModule->count() > 0)
                                <div class="row">
                                    @foreach($permissionsByModule as $module => $permissions)
                                    <div class="col-md-6 col-lg-4 mb-4">
                                        <div class="card h-100">
                                            <div class="card-header">
                                                <h6 class="mb-0">
                                                    <i class="bi bi-folder"></i> {{ ucfirst($module) }}
                                                    <span class="badge bg-primary ms-2">{{ $permissions->count() }}</span>
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                @foreach($permissions as $permission)
                                                <div class="mb-3 p-2 border-start border-3 border-primary">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <div>
                                                            <strong>{{ $permission->display_name }}</strong>
                                                            <br><code class="small">{{ $permission->name }}</code>
                                                            @if($permission->description)
                                                                <br><small class="text-muted">{{ $permission->description }}</small>
                                                            @endif
                                                        </div>
                                                        <div>
                                                            @if($permission->roles->count() > 0)
                                                                <span class="badge bg-success" title="Roles asignados">
                                                                    {{ $permission->roles->count() }}
                                                                </span>
                                                            @else
                                                                <span class="badge bg-warning" title="Sin roles asignados">
                                                                    0
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    @if($permission->roles->count() > 0)
                                                        <div class="mt-2">
                                                            <small class="text-muted">Roles:</small>
                                                            @foreach($permission->roles as $role)
                                                                <span class="badge bg-outline-primary me-1">{{ $role->display_name }}</span>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="alert alert-warning text-center" role="alert">
                                    <i class="bi bi-exclamation-triangle me-2"></i> No hay permisos configurados en el sistema.
                                </div>
                            @endif
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