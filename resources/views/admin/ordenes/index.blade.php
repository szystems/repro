@extends(session('layout', 'layouts.admin'))
@section('content')

<!-- Content wrapper scroll start -->
<div class="content-wrapper-scroll">

    <!-- Main header starts -->
    <div class="main-header d-flex align-items-center justify-content-between position-relative">
        <div class="d-flex align-items-center justify-content-center">
            <div class="page-icon">
                <i class="bi bi-file-earmark-text"></i>
            </div>
            <div class="page-title">
                <h5>Órdenes de Evaluación</h5>
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
                        <div class="card-title"><i class="bi bi-search"></i> Filtrar Órdenes</div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('ordenes.index') }}" method="GET">
                            <div class="row">
                                <div class="col-md-3 mb-2">
                                    <label class="form-label">Buscar por código o empresa</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                                        <input class="form-control" placeholder="Buscar..." name="buscar" value="{{ request('buscar') }}"/>
                                    </div>
                                </div>
                                
                                @if(Auth::user()->hasAnyRole(['admin', 'repro']))
                                <div class="col-md-3 mb-2">
                                    <label class="form-label">Empresa</label>
                                    <select class="form-select" name="empresa_id">
                                        <option value="">Todas las empresas</option>
                                        @foreach($empresas as $empresa)
                                        <option value="{{ $empresa->id }}" {{ request('empresa_id') == $empresa->id ? 'selected' : '' }}>
                                            {{ $empresa->nombre }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                @endif

                                <div class="col-md-2 mb-2">
                                    <label class="form-label">Estado</label>
                                    <select class="form-select" name="estado">
                                        <option value="">Todos los estados</option>
                                        @foreach($estados as $key => $valor)
                                        <option value="{{ $key }}" {{ request('estado') == $key ? 'selected' : '' }}>
                                            {{ $valor }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-2 mb-2">
                                    <label class="form-label">Tipo de Servicio</label>
                                    <select class="form-select" name="tipo_servicio">
                                        <option value="">Todos los tipos</option>
                                        @foreach($tiposServicio as $key => $valor)
                                        <option value="{{ $key }}" {{ request('tipo_servicio') == $key ? 'selected' : '' }}>
                                            {{ $valor }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>

                                @if(Auth::user()->role_as >= 2 && $sedes->count())
                                <div class="col-md-2 mb-2">
                                    <label class="form-label">Sede</label>
                                    <select class="form-select" name="sede_id">
                                        <option value="">Todas las sedes</option>
                                        @foreach($sedes as $sede)
                                        <option value="{{ $sede->id }}" {{ request('sede_id') == $sede->id ? 'selected' : '' }}>
                                            {{ $sede->nombre }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                @endif

                                <div class="col-md-2 mb-2">
                                    <label class="form-label">Desde</label>
                                    <input type="date" class="form-control" name="fecha_desde" value="{{ request('fecha_desde') }}">
                                </div>

                                <div class="col-md-2 mb-2">
                                    <label class="form-label">Hasta</label>
                                    <input type="date" class="form-control" name="fecha_hasta" value="{{ request('fecha_hasta') }}">
                                </div>

                                <div class="col-md-2 mb-2">
                                    <label class="form-label">&nbsp;</label>
                                    <div class="d-flex gap-1">
                                        <button type="submit" class="btn btn-info flex-grow-1"><i class="bi bi-search"></i> Buscar</button>
                                        <a href="{{ route('ordenes.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
                                    </div>
                                </div>
                            </div>
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
                        <div class="card-title">Lista de Órdenes</div>
                        <div class="card-options">
                            <a href="{{ route('ordenes.create') }}" class="btn btn-primary btn-sm">
                                <i class="bi bi-plus-circle"></i> Nueva Orden
                            </a>
                        </div>
                    </div>
                    <div class="card-body">

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

                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Empresa</th>
                                        <th>Tipos de Servicio</th>
                                        <th>Estado</th>
                                        <th>Evaluados</th>
                                        <th>Fechas</th>
                                        @if(Auth::user()->role_as >= 2)
                                        <th>Prioridad</th>
                                        @endif
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
                                                <br><small class="text-muted">{{ trim($primero->nombre . ' ' . $primero->apellidos) }}@if($orden->evaluados->count() > 1) <span class="badge bg-secondary">+{{ $orden->evaluados->count() - 1 }}</span>@endif</small>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $orden->empresa->nombre ?? 'N/A' }}
                                            @if($orden->sede)
                                                <br><small class="text-muted"><i class="bi bi-geo-alt"></i> {{ $orden->sede->nombre }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $tiposUnicos = $orden->evaluados->pluck('tipo_servicio')->unique();
                                            @endphp
                                            @foreach($tiposUnicos as $tipo)
                                                <span class="badge me-1
                                                    @if($tipo == 'poligrafo') bg-primary
                                                    @elseif($tipo == 'vsa') bg-info
                                                    @else bg-warning
                                                    @endif">
                                                    @if($tipo == 'poligrafo') Polígrafo
                                                    @elseif($tipo == 'vsa') VSA
                                                    @else Socioeconómico
                                                    @endif
                                                </span>
                                            @endforeach
                                            @if($tiposUnicos->isEmpty())
                                                <span class="badge bg-secondary">Sin definir</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $orden->estado_color }}">
                                                {{ $orden->estado_human }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">{{ $orden->evaluados_count ?? $orden->evaluados->count() }}</span>
                                        </td>
                                        <td>
                                            <div class="small">
                                                <strong>Solicitud:</strong> {{ $orden->fecha_solicitud ? \Carbon\Carbon::parse($orden->fecha_solicitud)->format('d/m/Y') : 'N/A' }}<br>
                                                <strong>Creación:</strong> {{ $orden->created_at ? $orden->created_at->format('d/m/Y') : 'N/A' }}
                                            </div>
                                        </td>
                                        @if(Auth::user()->role_as >= 2)
                                        <td>
                                            @if($orden->prioridad)
                                                <span class="badge 
                                                    @if($orden->prioridad == 'urgente') bg-danger
                                                    @elseif($orden->prioridad == 'alta') bg-warning
                                                    @elseif($orden->prioridad == 'normal') bg-info
                                                    @else bg-secondary
                                                    @endif">
                                                    {{ ucfirst($orden->prioridad) }}
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">Normal</span>
                                            @endif
                                        </td>
                                        @endif
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('ordenes.show', $orden) }}" class="btn btn-outline-info" title="Ver detalles">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route('ordenes.pdf', $orden) }}" class="btn btn-outline-danger" title="Imprimir PDF" target="_blank">
                                                    <i class="bi bi-file-pdf"></i>
                                                </a>
                                                
                                                @if(!in_array($orden->estado, ['entregado', 'cancelado']) && (Auth::user()->hasAnyRole(['admin', 'repro']) || (Auth::user()->hasRole('empresa') && $orden->empresa_id == Auth::user()->empresa_id && in_array($orden->estado, ['solicitud', 'autorizacion']))))
                                                <a href="{{ route('ordenes.edit', $orden) }}" class="btn btn-outline-warning" title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                @endif

                                                @if(Auth::user()->hasRole('admin'))
                                                <form action="{{ route('ordenes.destroy', $orden) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('¿Está seguro de eliminar esta orden?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger" title="Eliminar">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center">
                                            <div class="alert alert-info mb-0">
                                                <i class="bi bi-info-circle"></i> No hay órdenes registradas
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginación -->
                        @if($ordenes->hasPages())
                        <div class="d-flex justify-content-center mt-3">
                            {{ $ordenes->appends(request()->query())->links() }}
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

@push('scripts')
<script>
    // Auto-submit form on select change for better UX
    document.querySelectorAll('select[name="empresa_id"], select[name="estado"], select[name="tipo_servicio"]').forEach(function(select) {
        select.addEventListener('change', function() {
            this.form.submit();
        });
    });
</script>
@endpush