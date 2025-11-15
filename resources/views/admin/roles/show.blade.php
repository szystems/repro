@extends('layouts.admin')

@section('content')
    <!-- Content wrapper scroll start -->
    <div class="content-wrapper-scroll">

        <!-- Main header starts -->
        <div class="main-header d-flex align-items-center justify-content-between position-relative">
            <div class="d-flex align-items-center justify-content-center">
                <div class="page-icon">
                    <i class="bi bi-shield-check"></i>
                </div>
                <div class="page-title">
                    <h5>Detalles del Rol</h5>
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
                                    <h5 class="mb-0">{{ $role->display_name }}</h5>
                                    <small class="text-muted">{{ $role->name }}</small>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="{{ url('admin/roles/'.$role->id.'/edit') }}" class="btn btn-warning">
                                        <i class="bi bi-pencil-fill"></i> Editar
                                    </a>
                                    <a href="{{ url('admin/roles') }}" class="btn btn-outline-secondary">
                                        <i class="bi bi-arrow-left"></i> Volver
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6><i class="bi bi-info-circle"></i> Información General</h6>
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Nombre:</strong></td>
                                            <td>{{ $role->name }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Nombre Visible:</strong></td>
                                            <td>{{ $role->display_name }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Descripción:</strong></td>
                                            <td>{{ $role->description ?: 'Sin descripción' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Usuarios Asignados:</strong></td>
                                            <td>
                                                <span class="badge bg-secondary">{{ $role->users->count() }}</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Permisos:</strong></td>
                                            <td>
                                                <span class="badge bg-primary">{{ $role->permissions->count() }}</span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h6><i class="bi bi-people"></i> Usuarios con este Rol</h6>
                                    @if($role->users->count() > 0)
                                        <div class="list-group">
                                            @foreach($role->users as $user)
                                            <div class="list-group-item d-flex align-items-center">
                                                @if($user->fotografia)
                                                    <img src="{{ asset('assets/imgs/users/'.$user->fotografia) }}" class="img-thumbnail rounded-circle me-2" style="width: 40px; height: 40px;">
                                                @else
                                                    <img src="{{ asset('assets/imgs/users/usericon4.png') }}" class="img-thumbnail rounded-circle me-2" style="width: 40px; height: 40px;">
                                                @endif
                                                <div>
                                                    <strong>{{ $user->name }}</strong><br>
                                                    <small class="text-muted">{{ $user->email }}</small>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="alert alert-info">
                                            <i class="bi bi-info-circle"></i> No hay usuarios asignados a este rol.
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-12">
                                    <h6><i class="bi bi-key"></i> Permisos del Rol</h6>
                                    @if($role->permissions->count() > 0)
                                        @php
                                            $permissionsByModule = $role->permissions->groupBy('module');
                                        @endphp
                                        <div class="row">
                                            @foreach($permissionsByModule as $module => $permissions)
                                            <div class="col-md-6 col-lg-4 mb-3">
                                                <div class="card">
                                                    <div class="card-header">
                                                        <h6 class="mb-0">
                                                            <i class="bi bi-folder"></i> {{ ucfirst($module) }}
                                                            <span class="badge bg-primary ms-2">{{ $permissions->count() }}</span>
                                                        </h6>
                                                    </div>
                                                    <div class="card-body">
                                                        @foreach($permissions as $permission)
                                                        <div class="d-flex align-items-center mb-2">
                                                            <i class="bi bi-check-circle text-success me-2"></i>
                                                            <div>
                                                                <strong>{{ $permission->display_name }}</strong>
                                                                @if($permission->description)
                                                                    <br><small class="text-muted">{{ $permission->description }}</small>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="alert alert-warning">
                                            <i class="bi bi-exclamation-triangle"></i> Este rol no tiene permisos asignados.
                                        </div>
                                    @endif
                                </div>
                            </div>
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