@extends('layouts.admin')
@section('content')

<div class="content-wrapper-scroll">

    <div class="main-header d-flex align-items-center justify-content-between position-relative">
        <div class="d-flex align-items-center justify-content-center">
            <div class="page-icon">
                <i class="bi bi-search"></i>
            </div>
            <div class="page-title">
                <h5>Historial por DPI o nombre</h5>
            </div>
        </div>
        <div class="d-flex align-items-center">
            @include('partials._ayuda_contextual')
        </div>
    </div>

    <div class="content-wrapper">

        {{-- Formulario de búsqueda --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form method="GET" action="{{ route('admin.cuestionarios.historial-dpi') }}" class="row g-3 align-items-end">
                            <div class="col-md-8">
                                <label class="form-label fw-bold">DPI o nombre del candidato</label>
                                <input type="text" name="buscar" class="form-control"
                                       placeholder="DPI (13 dígitos) o nombre/apellidos"
                                       value="{{ $buscar }}"
                                       minlength="2" maxlength="100" required>
                                <small class="text-muted">Puede buscar por DPI completo o por parte del nombre o apellidos</small>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-search me-1"></i> Buscar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Resultados --}}
        @if($buscar)
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-clock-history me-2"></i>
                            Resultados para: <code>{{ $buscar }}</code>
                            <span class="badge bg-secondary ms-2">{{ $historial->count() }} {{ $historial->count() == 1 ? 'registro' : 'registros' }}</span>
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        @if($historial->isEmpty())
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1"></i>
                                <p class="mt-2">No se encontraron registros para esta búsqueda.</p>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>DPI</th>
                                            <th>Empresa</th>
                                            <th>Orden</th>
                                            <th>Servicio</th>
                                            <th>Estado de Evaluación</th>
                                            <th>Progreso del cuestionario</th>
                                            <th>Fecha</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($historial as $evaluado)
                                        <tr>
                                            <td><strong>{{ $evaluado->nombre }} {{ $evaluado->apellidos }}</strong></td>
                                            <td><code>{{ $evaluado->dpi ?? '—' }}</code></td>
                                            <td>{{ $evaluado->orden->empresa->nombre ?? 'N/A' }}</td>
                                            <td>
                                                <a href="{{ route('ordenes.show', $evaluado->orden_id) }}" class="text-decoration-none">
                                                    {{ $evaluado->orden->codigo_orden ?? 'N/A' }}
                                                </a>
                                            </td>
                                            <td>
                                                <span class="badge 
                                                    @if($evaluado->tipo_servicio == 'poligrafo') bg-primary
                                                    @elseif($evaluado->tipo_servicio == 'vsa') bg-info
                                                    @else bg-warning
                                                    @endif">
                                                    {{ ucfirst($evaluado->tipo_servicio) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $evaluado->estado_evaluacion_color }}">
                                                    {{ $evaluado->estado_evaluacion_texto }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($evaluado->cuestionario_completado)
                                                    <span class="badge bg-success"><i class="bi bi-check-circle"></i> Completado</span>
                                                @else
                                                    <span class="badge bg-secondary">Pendiente</span>
                                                @endif
                                            </td>
                                            <td>{{ $evaluado->created_at->format('d/m/Y') }}</td>
                                            <td>
                                                <a href="{{ route('ordenes.show', $evaluado->orden_id) }}" 
                                                   class="btn btn-outline-primary btn-sm" title="Ver Orden">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                @if($evaluado->cuestionario)
                                                <a href="{{ route('admin.cuestionarios.show', $evaluado->cuestionario->id) }}" 
                                                   class="btn btn-outline-info btn-sm" title="Ver Cuestionario">
                                                    <i class="bi bi-clipboard-check"></i>
                                                </a>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif

    </div>
</div>

@endsection
