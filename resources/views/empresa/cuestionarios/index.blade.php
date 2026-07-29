@extends('layouts.empresa')
@section('content')

<!-- Content wrapper scroll start -->
<div class="content-wrapper-scroll">

    <!-- Main header starts -->
    <div class="main-header d-flex align-items-center justify-content-between position-relative">
        <div class="d-flex align-items-center justify-content-center">
            <div class="page-icon">
                <i class="bi bi-clipboard-check"></i>
            </div>
            <div class="page-title">
                <h5>Estado de Procesos</h5>
            </div>
        </div>
        <div class="d-flex align-items-end d-none d-sm-block">
            <h6 class="float-end text-light" id="reloj"></h6>
        </div>
    </div>
    <!-- Main header ends -->

    <!-- Content wrapper start -->
    <div class="content-wrapper">

        <!-- Estadísticas -->
        <div class="row gx-3 mb-3">
            <div class="col-sm-6 col-md-4">
                <div class="card bg-secondary-subtle text-center">
                    <div class="card-body py-3">
                        <p class="text-muted mb-1 small text-uppercase">Total</p>
                        <h3 class="mb-0 fw-bold text-secondary">{{ $stats['total'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-md-4">
                <div class="card bg-success-subtle text-center">
                    <div class="card-body py-3">
                        <p class="text-muted mb-1 small text-uppercase">Completados</p>
                        <h3 class="mb-0 fw-bold text-success">{{ $stats['completados'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-md-4">
                <div class="card bg-warning-subtle text-center">
                    <div class="card-body py-3">
                        <p class="text-muted mb-1 small text-uppercase">Pendientes</p>
                        <h3 class="mb-0 fw-bold text-warning">{{ $stats['pendientes'] }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="row gx-3 mb-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('empresa.cuestionarios') }}" method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label for="buscar" class="form-label small">Buscar</label>
                                <input type="text" class="form-control form-control-sm" id="buscar" name="buscar"
                                       value="{{ request('buscar') }}" placeholder="Nombre, email, DPI...">
                            </div>
                            <div class="col-md-3">
                                <label for="orden_id" class="form-label small">Orden</label>
                                <select class="form-select form-select-sm" id="orden_id" name="orden_id">
                                    <option value="">Todas las órdenes</option>
                                    @foreach($ordenes as $orden)
                                        <option value="{{ $orden->id }}" {{ request('orden_id') == $orden->id ? 'selected' : '' }}>
                                            {{ $orden->codigo_orden ?? '#'.$orden->id }} - {{ $orden->created_at->format('d/m/Y') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="estado" class="form-label small">Progreso del cuestionario</label>
                                <select class="form-select form-select-sm" id="estado" name="estado">
                                    <option value="">Todos</option>
                                    <option value="completado" {{ request('estado') == 'completado' ? 'selected' : '' }}>Completados</option>
                                    <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>Pendientes</option>
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-success btn-sm me-2">
                                    <i class="bi bi-search"></i> Filtrar
                                </button>
                                <a href="{{ route('empresa.cuestionarios') }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-x"></i> Limpiar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Listado de cuestionarios -->
        <div class="row gx-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="bi bi-list-check text-success"></i> Cuestionarios</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                        <tr>
                                            <th>Evaluado</th>
                                            <th>Email</th>
                                            <th>DPI</th>
                                            <th>Orden</th>
                                            <th>Servicio</th>
                                            <th>Progreso del cuestionario</th>
                                            <th>Fecha</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                </thead>
                                <tbody>
                                    @forelse($cuestionarios as $cuestionario)
                                        <tr>
                                        <td>
                                            <strong>{{ $cuestionario->nombre }} {{ $cuestionario->apellidos }}</strong>
                                        </td>
                                        <td>{{ $cuestionario->email }}</td>
                                        <td>{{ $cuestionario->dpi ?? '-' }}</td>
                                        <td>
                                            <a href="{{ route('empresa.ordenes.show', $cuestionario->orden) }}" class="text-decoration-none">
                                                {{ $cuestionario->orden->codigo_orden ?? '#'.$cuestionario->orden_id }}
                                            </a>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $cuestionario->tipo_servicio ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            @if($cuestionario->cuestionario_completado)
                                                @if($cuestionario->resultadosDisponiblesParaEmpresa())
                                                    <span class="badge bg-success">
                                                        <i class="bi bi-check-circle"></i> Completado
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary" title="Resultados pendientes de entrega">
                                                        <i class="bi bi-lock"></i> En proceso
                                                    </span>
                                                @endif
                                            @else
                                                <span class="badge bg-warning text-dark">
                                                    <i class="bi bi-clock"></i> Pendiente
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($cuestionario->cuestionario_completado && $cuestionario->cuestionario_completado_at)
                                                {{ \Carbon\Carbon::parse($cuestionario->cuestionario_completado_at)->format('d/m/Y H:i') }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('empresa.cuestionarios.show', $cuestionario) }}"
                                                   class="btn btn-outline-success" title="Ver detalle">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                @if(!$cuestionario->cuestionario_completado)
                                                    <a href="{{ route('cuestionario.mostrar', $cuestionario->token_unico) }}"
                                                       class="btn btn-outline-primary"
                                                       title="Enlace del Evaluado"
                                                       target="_blank">
                                                        <i class="bi bi-link-45deg"></i>
                                                    </a>
                                                    <button type="button"
                                                            class="btn btn-outline-secondary"
                                                            onclick="copiarEnlaceEvaluado('{{ route('cuestionario.mostrar', $cuestionario->token_unico) }}')"
                                                            title="Copiar enlace al portapapeles">
                                                        <i class="bi bi-clipboard"></i>
                                                    </button>
                                                @endif
                                                @if($cuestionario->cuestionario_completado && $cuestionario->resultadosDisponiblesParaEmpresa())
                                                    <a href="{{ route('empresa.ordenes.show', $cuestionario->orden) }}"
                                                       class="btn btn-outline-danger" title="Descargar PDF de la Orden" target="_blank">
                                                        <i class="bi bi-file-pdf"></i>
                                                    </a>
                                                    <a href="{{ route('empresa.cuestionarios.pdf', $cuestionario) }}"
                                                       class="btn btn-outline-primary" title="Descargar PDF del Cuestionario" target="_blank">
                                                        <i class="bi bi-file-earmark-pdf"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            <i class="bi bi-clipboard-x display-6 d-block mb-2 text-muted"></i>
                                            No se encontraron cuestionarios
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($cuestionarios->hasPages())
                        <div class="d-flex justify-content-center mt-3">
                            {{ $cuestionarios->withQueryString()->links() }}
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

@push('scripts')
<script>
function copiarEnlaceEvaluado(url) {
    function mostrarExito() {
        const toast = document.createElement('div');
        toast.className = 'position-fixed bottom-0 end-0 p-3';
        toast.style.zIndex = '9999';
        toast.innerHTML = `
            <div class="toast show" role="alert">
                <div class="toast-header bg-success text-white">
                    <i class="bi bi-check-circle me-2"></i>
                    <strong class="me-auto">Enlace copiado</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">
                    El enlace ha sido copiado al portapapeles.
                </div>
            </div>
        `;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(url).then(mostrarExito).catch(function() {
            copiarFallback(url);
        });
    } else {
        copiarFallback(url);
    }
}

function copiarFallback(url) {
    const textarea = document.createElement('textarea');
    textarea.value = url;
    textarea.style.position = 'fixed';
    textarea.style.opacity = '0';
    document.body.appendChild(textarea);
    textarea.select();
    try {
        document.execCommand('copy');
        const toast = document.createElement('div');
        toast.className = 'position-fixed bottom-0 end-0 p-3';
        toast.style.zIndex = '9999';
        toast.innerHTML = `
            <div class="toast show" role="alert">
                <div class="toast-header bg-success text-white">
                    <i class="bi bi-check-circle me-2"></i>
                    <strong class="me-auto">Enlace copiado</strong>
                </div>
                <div class="toast-body">El enlace ha sido copiado al portapapeles.</div>
            </div>
        `;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    } catch (err) {
        prompt('Copie este enlace manualmente:', url);
    }
    document.body.removeChild(textarea);
}
</script>
@endpush
