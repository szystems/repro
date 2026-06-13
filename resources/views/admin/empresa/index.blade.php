@extends('layouts.admin')
@section('content')

<!-- Content wrapper scroll start -->
<div class="content-wrapper-scroll">

    <!-- Main header starts -->
    <div class="main-header d-flex align-items-center justify-content-between position-relative">
        <div class="d-flex align-items-center justify-content-center">
            <div class="page-icon">
                <i class="bi bi-building"></i>
            </div>
            <div class="page-title">
                <h5>Empresas</h5>
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
                <div class="card card-background-mask-info">
                    <div class="card-header">
                        <div class="card-title"><i class="bi bi-search"></i> Buscar Empresa</div>
                    </div>
                    <div class="card-body">
                        <form action="{{ url('empresas') }}" method="GET">
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <label class="form-label">Buscar por nombre, NIT, email o teléfono</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-building-fill"></i></span>
                                        <input class="form-control" placeholder="Buscar..." name="search" value="{{ $searchTerm ?? '' }}"/>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label class="form-label">Estado de Empresa</label>
                                    <select name="estado" class="form-select">
                                        <option value="">Todos</option>
                                        <option value="1" {{ isset($estado) && $estado == '1' ? 'selected' : '' }}>Activas</option>
                                        <option value="0" {{ isset($estado) && $estado == '0' ? 'selected' : '' }}>Inactivas</option>
                                    </select>
                                </div>
                                <div class="col-md-3 d-flex align-items-end mb-2">
                                    <div class="btn-group w-100">
                                        <button class="btn btn-primary" type="submit">
                                            <i class="bi bi-search"></i> Buscar
                                        </button>
                                        <a href="{{ url('empresas') }}" class="btn btn-outline-secondary">
                                            <i class="bi bi-x-circle"></i> Limpiar
                                        </a>
                                    </div>
                                </div>
                            </div>

                            @if($searchTerm || (isset($estado) && $estado != ''))
                            <div class="mt-2">
                                <span class="badge bg-info text-dark">Filtros activos:</span>
                                @if($searchTerm)
                                    <span class="badge bg-light text-dark">Búsqueda: {{ $searchTerm }}</span>
                                @endif
                                @if(isset($estado) && $estado != '')
                                    <span class="badge bg-light text-dark">
                                        Estado de Empresa: {{ $estado == '1' ? 'Activas' : 'Inactivas' }}
                                    </span>
                                @endif
                            </div>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- Row end -->

        <!-- Row start -->
        <div class="row gx-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-8">
                                <h5 class="card-title">Listado de Empresas</h5>
                            </div>
                            <div class="col-4 text-end">
                                <div class="btn-group">
                                    <a href="{{ url('add-empresa') }}" class="btn btn-success">
                                        <i class="bi bi-building-add"></i> Nueva Empresa
                                    </a>
                                    <a href="{{ url('pdf-empresas') }}?search={{ $searchTerm ?? '' }}&estado={{ $estado ?? '' }}" target="_blank" class="btn btn-danger">
                                        <i class="bi bi-file-earmark-pdf"></i> PDF
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th>Logo</th>
                                        <th>Nombre</th>
                                        <th>NIT</th>
                                        <th>Contacto</th>
                                        <th>Usuarios</th>
                                        <th>Estado de Empresa</th>
                                        <th width="150">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($empresas as $empresa)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center justify-content-center">
                                                    @if ($empresa->logo)
                                                        <img src="{{ asset('assets/imgs/empresas/'.$empresa->logo) }}" alt="Logo" class="img-thumbnail rounded" style="height: 50px;" />
                                                    @else
                                                        <div class="avatar avatar-md">
                                                            <div class="avatar-title bg-light text-secondary rounded">
                                                                <i class="bi bi-building" style="font-size: 1.5rem;"></i>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <div class="fw-bold">{{ $empresa->nombre }}</div>
                                            </td>
                                            <td>{{ $empresa->nit ?? 'No definido' }}</td>
                                            <td>
                                                @if($empresa->telefono)
                                                    <div><i class="bi bi-telephone"></i> {{ $empresa->telefono }}</div>
                                                @endif
                                                @if($empresa->email)
                                                    <div><i class="bi bi-envelope"></i> {{ $empresa->email }}</div>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-primary">{{ $empresa->getTotalUsuarios() }}</span>
                                            </td>
                                            <td>
                                                <span class="badge {{ $empresa->estado ? 'bg-success' : 'bg-danger' }}">
                                                    {{ $empresa->getEstadoTexto() }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ url('show-empresa/'.$empresa->id) }}" class="btn btn-info" data-bs-toggle="tooltip" title="Ver detalles">
                                                        <i class="bi bi-eye-fill"></i>
                                                    </a>
                                                    <a href="{{ url('edit-empresa/'.$empresa->id) }}" class="btn btn-primary" data-bs-toggle="tooltip" title="Editar">
                                                        <i class="bi bi-pencil-fill"></i>
                                                    </a>
                                                    @if(Auth::user()->role_as == 3)
                                                        @if($empresa->estado == 1)
                                                            <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#disableModal{{ $empresa->id }}" title="Desactivar">
                                                                <i class="bi bi-slash-circle"></i>
                                                            </button>
                                                        @else
                                                            <form action="{{ route('empresas.cambiar-estado', [$empresa->id, 1]) }}" method="POST" class="d-inline">
                                                                @csrf @method('PATCH')
                                                                <button type="submit" class="btn btn-success" data-bs-toggle="tooltip" title="Activar">
                                                                    <i class="bi bi-check-circle"></i>
                                                                </button>
                                                            </form>
                                                        @endif
                                                    @endif
                                                </div>

                                                <!-- Modal de desactivación -->
                                                <div class="modal fade" id="disableModal{{ $empresa->id }}" tabindex="-1" aria-labelledby="disableModalLabel{{ $empresa->id }}" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header bg-warning">
                                                                <h5 class="modal-title" id="disableModalLabel{{ $empresa->id }}">Confirmar desactivación</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p>¿Está seguro que desea desactivar la empresa <strong>{{ $empresa->nombre }}</strong>?</p>
                                                                <p>Esta acción afectará a todos los usuarios vinculados a esta empresa.</p>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                                <form action="{{ route('empresas.cambiar-estado', [$empresa->id, 0]) }}" method="POST" class="d-inline">
                                                                    @csrf @method('PATCH')
                                                                    <button type="submit" class="btn btn-warning">Desactivar</button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center">No hay empresas registradas</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            {{ $empresas->links() }}
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
