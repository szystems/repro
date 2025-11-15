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
                    <h5>Gestión de Roles y Permisos</h5>
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
                                    Roles del Sistema
                                    <span class="badge bg-secondary">{{ $roles->count() }} roles</span>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="{{ url('admin/roles/permissions') }}" class="btn btn-info me-2">
                                        <i class="bi bi-key"></i> Ver Permisos
                                    </a>
                                    <a href="{{ url('admin/roles/create') }}" class="btn btn-success">
                                        <i class="bi bi-plus-square"></i> Crear Rol
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            @if($roles->count() > 0)
                            <div class="table-responsive">
                                <table class="table align-middle table-striped">
                                    <thead>
                                        <tr>
                                            <th>Rol</th>
                                            <th class="text-center">Permisos</th>
                                            <th class="text-center">Usuarios</th>
                                            <th>Descripción</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($roles as $role)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="bi bi-shield-fill me-2 text-primary"></i>
                                                    <div>
                                                        <strong>{{ $role->display_name }}</strong><br>
                                                        <small class="text-muted">{{ $role->name }}</small>
                                                        @if(in_array($role->name, ['admin', 'repro', 'empresa', 'evaluado']))
                                                            <span class="badge bg-warning text-dark ms-1">Sistema</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-info">{{ $role->permissions->count() }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-secondary">{{ $role->users->count() }}</span>
                                            </td>
                                            <td>
                                                <small>{{ $role->description ?? 'Sin descripción' }}</small>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ url('admin/roles/'.$role->id) }}" class="btn btn-info">
                                                        <i class="bi bi-eye-fill"></i>
                                                    </a>
                                                    <a href="{{ url('admin/roles/'.$role->id.'/edit') }}" class="btn btn-warning">
                                                        <i class="bi bi-pencil-fill"></i>
                                                    </a>
                                                    @if(!in_array($role->name, ['admin', 'repro', 'empresa', 'evaluado']))
                                                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal-{{ $role->id }}">
                                                        <i class="bi bi-trash-fill"></i>
                                                    </button>
                                                    @else
                                                    <button disabled type="button" class="btn btn-secondary">
                                                        <i class="bi bi-trash-fill"></i>
                                                    </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>

                                        <!-- Modal de confirmación de eliminación -->
                                        @if(!in_array($role->name, ['admin', 'repro', 'empresa', 'evaluado']))
                                        <div class="modal fade" id="deleteModal-{{ $role->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Confirmar Eliminación</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>¿Está seguro de que desea eliminar el rol <strong>{{ $role->display_name }}</strong>?</p>
                                                        <div class="alert alert-warning">
                                                            <i class="bi bi-exclamation-triangle"></i>
                                                            Esta acción no se puede deshacer.
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                        <form action="{{ url('admin/roles/'.$role->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger">Eliminar</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <div class="alert alert-info text-center" role="alert">
                                <i class="bi bi-info-circle me-2"></i> No hay roles definidos en el sistema.
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