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
            <div class="card-header">
                <span class="card-title">Listado de Órdenes</span>
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
