@extends('layouts.empresa')
@section('content')

<!-- Content wrapper scroll start -->
<div class="content-wrapper-scroll">

    <!-- Main header starts -->
    <div class="main-header d-flex align-items-center justify-content-between position-relative">
        <div class="d-flex align-items-center justify-content-center">
            <div class="page-icon">
                <i class="bi bi-people"></i>
            </div>
            <div class="page-title">
                <h5>Usuarios de la Empresa</h5>
            </div>
        </div>
        <div class="d-flex align-items-end d-none d-sm-block">
            <h6 class="float-end text-light" id="reloj"></h6>
        </div>
    </div>
    <!-- Main header ends -->

    <!-- Content wrapper start -->
    <div class="content-wrapper">

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <div class="row gx-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="card-title mb-0">
                            <i class="bi bi-people text-success"></i> Usuarios de {{ $empresa->nombre }}
                        </h6>
                        <a href="{{ route('empresa.usuarios.create') }}" class="btn btn-success">
                            <i class="bi bi-person-plus"></i> Nuevo Usuario
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Email</th>
                                        <th>Teléfono</th>
                                        <th>Cargo</th>
                                        <th>Tipo</th>
                                        <th>Estado de Usuario</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($usuarios as $usuario)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($usuario->fotografia)
                                                    <img src="{{ asset('assets/imgs/users/'.$usuario->fotografia) }}" 
                                                         alt="{{ $usuario->name }}" 
                                                         class="rounded-circle me-2" 
                                                         style="width: 35px; height: 35px; object-fit: cover;">
                                                @else
                                                    <div class="avatar avatar-sm me-2">
                                                        <div class="avatar-title bg-success-subtle text-success rounded-circle">
                                                            {{ strtoupper(substr($usuario->name, 0, 1)) }}
                                                        </div>
                                                    </div>
                                                @endif
                                                <span>{{ $usuario->name }}</span>
                                            </div>
                                        </td>
                                        <td>{{ $usuario->email }}</td>
                                        <td>{{ $usuario->telefono ?? '-' }}</td>
                                        <td>{{ $usuario->cargo ?? '-' }}</td>
                                        <td>
                                            @if($usuario->principal == 1)
                                                <span class="badge bg-success"><i class="bi bi-star-fill"></i> Principal</span>
                                            @else
                                                <span class="badge bg-secondary">Usuario</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{ $usuario->estado ? 'bg-success' : 'bg-danger' }}">
                                                {{ $usuario->estado ? 'Activo' : 'Inactivo' }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @if($usuario->principal != 1)
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('empresa.usuarios.edit', $usuario) }}" 
                                                       class="btn btn-outline-primary" title="Editar">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <form action="{{ route('empresa.usuarios.destroy', $usuario) }}" 
                                                          method="POST" class="d-inline"
                                                          onsubmit="return confirm('¿Está seguro de eliminar este usuario?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger" title="Eliminar">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            @else
                                                <span class="text-muted small">Usuario principal</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            <i class="bi bi-people display-6 d-block mb-2 text-muted"></i>
                                            No hay usuarios adicionales registrados
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($usuarios->hasPages())
                        <div class="d-flex justify-content-center mt-3">
                            {{ $usuarios->links() }}
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!-- Content wrapper end -->

</div>
<!-- Content wrapper scroll end -->

@endsection
