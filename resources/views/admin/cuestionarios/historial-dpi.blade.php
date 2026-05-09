@extends('layouts.admin')
@section('content')

<div class="content-wrapper-scroll">

    <div class="main-header d-flex align-items-center justify-content-between position-relative">
        <div class="d-flex align-items-center justify-content-center">
            <div class="page-icon">
                <i class="bi bi-search"></i>
            </div>
            <div class="page-title">
                <h5>Historial por DPI</h5>
            </div>
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
                                <label class="form-label fw-bold">Número de DPI</label>
                                <input type="text" name="dpi" class="form-control" 
                                       placeholder="Ingrese los 13 dígitos del DPI" 
                                       value="{{ $dpi }}" 
                                       pattern="[0-9]{13}" maxlength="13" required>
                                <small class="text-muted">13 dígitos sin espacios ni guiones</small>
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
        @if($dpi)
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-clock-history me-2"></i>
                            Historial del DPI: <code>{{ $dpi }}</code>
                            <span class="badge bg-secondary ms-2">{{ $historial->count() }} {{ $historial->count() == 1 ? 'registro' : 'registros' }}</span>
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        @if($historial->isEmpty())
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1"></i>
                                <p class="mt-2">No se encontraron registros para este DPI.</p>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Empresa</th>
                                            <th>Orden</th>
                                            <th>Servicio</th>
                                            <th>Estado Evaluación</th>
                                            <th>Cuestionario</th>
                                            <th>Fecha</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($historial as $evaluado)
                                        <tr>
                                            <td><strong>{{ $evaluado->nombre }} {{ $evaluado->apellidos }}</strong></td>
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
