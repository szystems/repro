@extends(session('layout', 'layouts.admin'))

@section('content')
<div class="content-wrapper">
    <!-- Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <h3 class="page-title">
                    <i class="bi bi-file-bar-graph me-2"></i>Reporte de Evaluaciones
                </h3>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Reporte de Evaluaciones</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-funnel me-2"></i>Filtros
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('reportes.evaluaciones') }}" id="filtroForm">
                        <div class="row g-3">
                            <div class="col-md-2">
                                <label class="form-label">Mes Rápido</label>
                                <select name="mes" class="form-select" onchange="aplicarFiltroMes(this)">
                                    <option value="">Seleccionar...</option>
                                    @for($i = 0; $i < 12; $i++)
                                        @php $mesDate = now()->subMonths($i); @endphp
                                        <option value="{{ $mesDate->format('Y-m') }}" {{ request('mes') == $mesDate->format('Y-m') ? 'selected' : '' }}>
                                            {{ ucfirst($mesDate->translatedFormat('F Y')) }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Fecha Inicio</label>
                                <input type="date" name="fecha_inicio" class="form-control"
                                       value="{{ request('fecha_inicio') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Fecha Fin</label>
                                <input type="date" name="fecha_fin" class="form-control"
                                       value="{{ request('fecha_fin') }}">
                            </div>
                            @if(Auth::user()->role_as >= 2)
                            <div class="col-md-3">
                                <label class="form-label">Empresa</label>
                                <select name="empresa_id" class="form-select">
                                    <option value="">Todas las empresas</option>
                                    @foreach($empresas as $empresa)
                                        <option value="{{ $empresa->id }}" {{ request('empresa_id') == $empresa->id ? 'selected' : '' }}>
                                            {{ $empresa->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                            <div class="col-md-2">
                                <label class="form-label">Tipo de Servicio</label>
                                <select name="tipo_servicio" class="form-select">
                                    <option value="">Todos</option>
                                    <option value="poligrafo" {{ request('tipo_servicio') == 'poligrafo' ? 'selected' : '' }}>Polígrafo</option>
                                    <option value="vsa" {{ request('tipo_servicio') == 'vsa' ? 'selected' : '' }}>VSA</option>
                                    <option value="socioeconomico" {{ request('tipo_servicio') == 'socioeconomico' ? 'selected' : '' }}>Socioeconómico</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Progreso cuestionario</label>
                                <select name="estado" class="form-select">
                                    <option value="">Todos</option>
                                    <option value="completado" {{ request('estado') == 'completado' ? 'selected' : '' }}>Completados</option>
                                    <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>Pendientes</option>
                                </select>
                            </div>
                            <div class="col-md-1 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-start border-4 border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted mb-1 small text-uppercase">Total Evaluados</p>
                            <h3 class="mb-0 text-primary">{{ $stats['total'] }}</h3>
                        </div>
                        <div class="me-3" style="width: 48px; height: 48px;">
                            <div class="bg-primary d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; border-radius: 50%;">
                                <i class="bi bi-people text-white fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-4 border-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted mb-1 small text-uppercase">Completados</p>
                            <h3 class="mb-0 text-success">{{ $stats['completados'] }}</h3>
                        </div>
                        <div class="me-3" style="width: 48px; height: 48px;">
                            <div class="bg-success d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; border-radius: 50%;">
                                <i class="bi bi-check-circle text-white fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-4 border-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted mb-1 small text-uppercase">Pendientes</p>
                            <h3 class="mb-0 text-warning">{{ $stats['pendientes'] }}</h3>
                        </div>
                        <div class="me-3" style="width: 48px; height: 48px;">
                            <div class="bg-warning d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; border-radius: 50%;">
                                <i class="bi bi-hourglass-split text-white fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-4 border-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted mb-1 small text-uppercase">% Completado</p>
                            <h3 class="mb-0 text-info">{{ $stats['total'] > 0 ? round(($stats['completados'] / $stats['total']) * 100, 1) : 0 }}%</h3>
                        </div>
                        <div class="me-3" style="width: 48px; height: 48px;">
                            <div class="bg-info d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; border-radius: 50%;">
                                <i class="bi bi-percent text-white fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Botones de exportación y tabla -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-table me-2"></i>Listado de Evaluados
                    </h5>
                    <div class="btn-group">
                        <a href="{{ route('reportes.evaluaciones.pdf', request()->query()) }}"
                           class="btn btn-danger btn-sm" target="_blank">
                            <i class="bi bi-file-pdf me-1"></i>Exportar PDF
                        </a>
                        <a href="{{ route('reportes.evaluaciones.excel', request()->query()) }}"
                           class="btn btn-success btn-sm">
                            <i class="bi bi-file-excel me-1"></i>Exportar Excel
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Código Orden</th>
                                    <th>Empresa</th>
                                    <th>Evaluado</th>
                                    <th>DPI</th>
                                    <th>Teléfono</th>
                                    <th>Servicio</th>
                                    <th>Formulario</th>
                                    <th>Estado de Formulario</th>
                                    <th>Estado de Programación</th>
                                    <th>Estado de Evaluación</th>
                                    <th>Fecha</th>
                                    <th class="text-center">Informe</th>
                                    <th class="text-center">Papelería</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($evaluados as $evaluado)
                                    <tr>
                                        <td>
                                            <a href="{{ route('ordenes.show', $evaluado->orden_id) }}" class="text-primary fw-bold">
                                                {{ $evaluado->orden->codigo_orden ?? 'N/A' }}
                                            </a>
                                        </td>
                                        <td>{{ Str::limit($evaluado->orden->empresa->nombre ?? 'N/A', 25) }}</td>
                                        <td>{{ $evaluado->nombre }} {{ $evaluado->apellidos }}</td>
                                        <td>{{ $evaluado->dpi }}</td>
                                        <td>{{ $evaluado->telefono ?? $evaluado->celular ?? '—' }}</td>
                                        <td>
                                            @php
                                                $servicioColors = [
                                                    'poligrafo' => 'primary',
                                                    'vsa' => 'info',
                                                    'socioeconomico' => 'warning',
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $servicioColors[$evaluado->tipo_servicio] ?? 'secondary' }}">
                                                {{ ucfirst($evaluado->tipo_servicio) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                {{ $evaluado->tipo_formulario_texto ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $evaluado->estado_formulario_color }}">
                                                {{ $evaluado->estado_formulario_texto }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $evaluado->estado_programacion_color }}">
                                                {{ $evaluado->estado_programacion_texto }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $evaluado->estado_evaluacion_color }}">
                                                {{ $evaluado->estado_evaluacion_texto }}
                                            </span>
                                        </td>
                                        <td>{{ $evaluado->created_at->format('d/m/Y') }}</td>
                                        <td class="text-center">
                                            @php $disponible = $evaluado->resultadosDisponiblesParaEmpresa(); @endphp
                                            @if($disponible && Auth::user()->role_as == 1)
                                                <a href="{{ route('empresa.cuestionarios.pdf', $evaluado) }}"
                                                   class="btn btn-sm btn-danger"
                                                   title="Descargar informe PDF del evaluado"
                                                   target="_blank">
                                                    <i class="bi bi-file-earmark-pdf"></i>
                                                </a>
                                            @elseif($disponible && Auth::user()->role_as >= 2 && $evaluado->cuestionario)
                                                <a href="{{ route('admin.cuestionarios.pdf', $evaluado->cuestionario) }}"
                                                   class="btn btn-sm btn-danger"
                                                   title="Descargar informe PDF del evaluado"
                                                   target="_blank">
                                                    <i class="bi bi-file-earmark-pdf"></i>
                                                </a>
                                            @else
                                                <span class="badge bg-light text-muted border" title="El informe se habilitará cuando REPRO marque los resultados como disponibles">
                                                    <i class="bi bi-clock-history"></i> En proceso
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($evaluado->orden_id)
                                                <a href="{{ route('ordenes.show', $evaluado->orden_id) }}#heading-evaluado-{{ $evaluado->id }}"
                                                   class="btn btn-sm btn-outline-secondary"
                                                   title="Ver papelería y documentos del candidato">
                                                    <i class="bi bi-folder2-open"></i>
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="12" class="text-center py-4 text-muted">
                                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                            No se encontraron evaluados con los filtros seleccionados
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($evaluados->hasPages())
                <div class="card-footer">
                    {{ $evaluados->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function aplicarFiltroMes(select) {
    if (!select.value) return;
    const [year, month] = select.value.split('-');
    const inicio = `${year}-${month}-01`;
    const lastDay = new Date(year, month, 0).getDate();
    const fin = `${year}-${month}-${String(lastDay).padStart(2, '0')}`;
    const form = document.getElementById('filtroForm');
    form.querySelector('[name="fecha_inicio"]').value = inicio;
    form.querySelector('[name="fecha_fin"]').value = fin;
}
</script>
@endpush
