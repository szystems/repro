@extends('layouts.empresa')
@section('content')
<div class="content-wrapper-scroll">
    <div class="main-header d-flex align-items-center justify-content-between position-relative">
        <div class="d-flex align-items-center justify-content-center">
            <div class="page-icon">
                <i class="bi bi-list-ul"></i>
            </div>
            <div class="page-title">
                <h5>Mis Órdenes</h5>
            </div>
        </div>
        <div class="d-flex align-items-end d-none d-sm-block">
            <h6 class="float-end text-light" id="reloj"></h6>
        </div>
    </div>
    <div class="content-wrapper">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="card-title mb-0">Listado de Órdenes</span>
                <a href="{{ route('ordenes.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle"></i> Nueva Solicitud
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Estado</th>
                                <th>Fecha Solicitud</th>
                                <th>Fecha Creación</th>
                                <th>Evaluados</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ordenes as $orden)
                            <tr>
                                <td>
                                    <strong>{{ $orden->codigo_orden }}</strong>
                                    @if($orden->evaluados->count() > 0)
                                        @php $primero = $orden->evaluados->first(); @endphp
                                        <br><small class="text-muted">{{ trim($primero->nombre . ' ' . $primero->apellidos) }}@if($orden->evaluados_count > 1) <span class="badge bg-secondary">+{{ $orden->evaluados_count - 1 }}</span>@endif</small>
                                    @endif
                                </td>
                                <td><span class="badge bg-{{ $orden->estado_color }}">{{ $orden->estado_human }}</span></td>
                                <td>{{ $orden->fecha_solicitud ? \Carbon\Carbon::parse($orden->fecha_solicitud)->format('d/m/Y') : '-' }}</td>
                                <td>{{ $orden->created_at ? $orden->created_at->format('d/m/Y') : '-' }}</td>
                                <td>{{ $orden->evaluados_count }}</td>
                                <td>
                                    <a href="{{ route('empresa.ordenes.show', $orden) }}" class="btn btn-outline-primary btn-sm" title="Ver Detalle">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if(Auth::user()->hasPermission('ordenes.editar') && !in_array($orden->estado, ['entregado', 'cancelado']) && in_array($orden->estado, ['solicitud', 'autorizacion']))
                                    <a href="{{ route('ordenes.edit', $orden) }}" class="btn btn-outline-warning btn-sm" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @endif
                                    @if(Auth::user()->hasPermission('ordenes.eliminar') && !in_array($orden->estado, ['en_proceso', 'preliminar', 'final', 'entregado']))
                                    <form action="{{ route('ordenes.destroy', $orden) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('¿Está seguro de eliminar esta orden?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                    <a href="{{ route('ordenes.pdf', $orden) }}" class="btn btn-outline-danger btn-sm" title="Descargar PDF" target="_blank">
                                        <i class="bi bi-file-pdf"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">No hay órdenes registradas</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-center mt-3">
                    {{ $ordenes->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
