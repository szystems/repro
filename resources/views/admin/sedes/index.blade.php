@extends('layouts.admin')
@section('content')

<div class="content-wrapper-scroll">

    <div class="main-header d-flex align-items-center justify-content-between position-relative">
        <div class="d-flex align-items-center justify-content-center">
            <div class="page-icon">
                <i class="bi bi-geo-alt"></i>
            </div>
            <div class="page-title">
                <h5>Sedes REPRO</h5>
            </div>
        </div>
        <div class="d-flex align-items-end d-none d-sm-block">
            <h6 class="float-end text-light" id="reloj"></h6>
        </div>
    </div>

    <div class="content-wrapper">

        {{-- Mensajes de sesión --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Filtros --}}
        <div class="row gx-3 mb-3">
            <div class="col-12">
                <div class="card card-background-mask-info">
                    <div class="card-header">
                        <div class="card-title"><i class="bi bi-search"></i> Buscar Sede</div>
                    </div>
                    <div class="card-body">
                        <form action="{{ url('sedes') }}" method="GET">
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <label class="form-label">Buscar por nombre, dirección o teléfono</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-geo-alt-fill"></i></span>
                                        <input class="form-control" placeholder="Buscar..." name="search" value="{{ $searchTerm ?? '' }}"/>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label class="form-label">Estado</label>
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
                                        <a href="{{ url('sedes') }}" class="btn btn-outline-secondary">
                                            <i class="bi bi-x-circle"></i> Limpiar
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabla --}}
        <div class="row gx-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div class="card-title"><i class="bi bi-list-ul"></i> Listado de Sedes</div>
                        <a href="{{ route('sedes.create') }}" class="btn btn-success btn-sm">
                            <i class="bi bi-plus-circle"></i> Nueva Sede
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Nombre</th>
                                        <th>Dirección</th>
                                        <th>Teléfono</th>
                                        <th class="text-center">Capacidad</th>
                                        <th class="text-center">Evaluados</th>
                                        <th class="text-center">Estado</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @forelse($sedes as $sede)
                                    <tr>
                                        <td class="text-muted small">{{ $sede->id }}</td>
                                        <td class="fw-semibold">{{ $sede->nombre }}</td>
                                        <td class="text-muted small">{{ $sede->direccion ?? '—' }}</td>
                                        <td>{{ $sede->telefono ?? '—' }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary">{{ $sede->capacidad }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info text-dark">{{ $sede->evaluados_count }}</span>
                                        </td>
                                        <td class="text-center">
                                            @if($sede->estado)
                                                <span class="badge bg-success">Activa</span>
                                            @else
                                                <span class="badge bg-danger">Inactiva</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('sedes.show', $sede->id) }}" class="btn btn-outline-info" title="Ver detalle">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route('sedes.edit', $sede->id) }}" class="btn btn-outline-warning" title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                @if($sede->estado)
                                                    <form action="{{ route('sedes.cambiar-estado', [$sede->id, 0]) }}" method="POST" class="d-inline">
                                                        @csrf @method('PATCH')
                                                        <button type="submit" class="btn btn-outline-secondary" title="Desactivar"
                                                                onclick="return confirm('¿Desactivar esta sede?')">
                                                            <i class="bi bi-toggle-off"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <form action="{{ route('sedes.cambiar-estado', [$sede->id, 1]) }}" method="POST" class="d-inline">
                                                        @csrf @method('PATCH')
                                                        <button type="submit" class="btn btn-outline-success" title="Activar">
                                                            <i class="bi bi-toggle-on"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                                @if($sede->evaluados_count === 0)
                                                    <form action="{{ route('sedes.destroy', $sede->id) }}" method="POST" class="d-inline">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger" title="Eliminar"
                                                                onclick="return confirm('¿Eliminar la sede {{ $sede->nombre }}?')">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            <i class="bi bi-inbox fs-4 d-block mb-2"></i>
                                            No se encontraron sedes.
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if($sedes->hasPages())
                        <div class="card-footer">
                            {{ $sedes->withQueryString()->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
