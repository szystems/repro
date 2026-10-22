@extends(session('layout', 'layouts.admin'))

@section('content')
<div class="content-wrapper">
    <!-- Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <h3 class="page-title">
                    <i class="bi bi-file-bar-graph me-2"></i>INFORMES DE EMPRESAS
                </h3>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">INFORMES DE EMPRESAS</li>
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
                                <label class="form-label">Fecha de la prueba (desde)</label>
                                <input type="date" name="fecha_inicio" class="form-control"
                                       value="{{ request('fecha_inicio') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Fecha de la prueba (hasta)</label>
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
                                <label class="form-label">Sede</label>
                                <select name="sede_id" class="form-select">
                                    <option value="">Todas las sedes</option>
                                    @foreach($sedes as $sede)
                                        <option value="{{ $sede->id }}" {{ (string) request('sede_id') === (string) $sede->id ? 'selected' : '' }}>
                                            {{ $sede->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
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
                    <p class="text-muted small mb-0 mt-3">
                        El mes y las fechas filtran el día en que se realizó el proceso (calendario), no el día en que se creó el registro.
                        El Excel incluye resultado, puesto, encargado y esa fecha para cruzar lo facturado con lo que sí se hizo.
                    </p>
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
                        @if(\App\Support\ExportacionesSupport::puedeExportarInformes(Auth::user()))
                        <a href="{{ route('reportes.evaluaciones.excel', request()->query()) }}"
                           class="btn btn-success btn-sm">
                            <i class="bi bi-file-excel me-1"></i>Exportar Excel
                        </a>
                        @endif
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
                                    <th>Resultado</th>
                                    <th>Puesto</th>
                                    <th>Encargado</th>
                                    <th>Fecha de la prueba</th>
                                    <th class="text-center">Informe</th>
                                    <th class="text-center">Papelería</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($evaluados as $evaluado)
                                    <tr>
                                        <td>
                                            <a href="{{ \App\Support\OrdenPortalSupport::urlDetalle(Auth::user(), $evaluado->orden_id) }}" class="text-primary fw-bold">
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
                                        <td>
                                            @if(Auth::user()->role_as == 1 && ! $evaluado->resultadosDisponiblesParaEmpresa())
                                                No liberado
                                            @else
                                                {{ $evaluado->resultado_texto }}
                                            @endif
                                        </td>
                                        <td>{{ $evaluado->puesto_evaluar ?: '—' }}</td>
                                        <td>{{ $evaluado->responsable->name ?? 'Sin asignar' }}</td>
                                        <td>
                                            @if($evaluado->fecha_programada)
                                                {{ $evaluado->fecha_programada->format('d/m/Y') }}
                                                <span class="text-muted">{{ $evaluado->fecha_programada->format('H:i') }}</span>
                                            @else
                                                <span class="text-muted">Sin programar</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @php $disponible = $evaluado->resultadosDisponiblesParaEmpresa(); @endphp
                                            @if($disponible && Auth::user()->role_as == 1)
                                                @if($evaluado->archivo_resultado_final)
                                                    <a href="{{ route('evaluados.descargar-resultado-archivo', [$evaluado, 'final']) }}"
                                                       class="btn btn-sm btn-success"
                                                       title="Descargar Informe Final"
                                                       target="_blank">
                                                        <i class="bi bi-file-earmark-arrow-down"></i>
                                                    </a>
                                                @elseif($evaluado->archivo_resultado_preliminar)
                                                    <a href="{{ route('evaluados.descargar-resultado-archivo', [$evaluado, 'preliminar']) }}"
                                                       class="btn btn-sm btn-outline-info"
                                                       title="Descargar Informe Preliminar"
                                                       target="_blank">
                                                        <i class="bi bi-file-earmark-arrow-down"></i>
                                                    </a>
                                                @elseif(filled($evaluado->texto_informe_preliminar))
                                                    <a href="{{ route('empresa.cuestionarios.show', $evaluado) }}"
                                                       class="btn btn-sm btn-primary"
                                                       title="Ver informe de evaluación">
                                                        <i class="bi bi-file-earmark-text"></i>
                                                    </a>
                                                @else
                                                    <a href="{{ route('empresa.cuestionarios.show', $evaluado) }}"
                                                       class="btn btn-sm btn-primary"
                                                       title="Ver informe de evaluación">
                                                        <i class="bi bi-file-earmark-text"></i>
                                                    </a>
                                                @endif
                                            @elseif($disponible && Auth::user()->role_as >= 2)
                                                @if($evaluado->archivo_resultado_final)
                                                    <a href="{{ route('evaluados.descargar-resultado-archivo', [$evaluado, 'final']) }}"
                                                       class="btn btn-sm btn-success"
                                                       title="Descargar Informe Final"
                                                       target="_blank">
                                                        <i class="bi bi-file-earmark-arrow-down"></i>
                                                    </a>
                                                @elseif($evaluado->archivo_resultado_preliminar)
                                                    <a href="{{ route('evaluados.descargar-resultado-archivo', [$evaluado, 'preliminar']) }}"
                                                       class="btn btn-sm btn-outline-info"
                                                       title="Descargar Informe Preliminar"
                                                       target="_blank">
                                                        <i class="bi bi-file-earmark-arrow-down"></i>
                                                    </a>
                                                @elseif($evaluado->cuestionario)
                                                    <a href="{{ route('admin.cuestionarios.pdf', $evaluado->cuestionario) }}"
                                                       class="btn btn-sm btn-outline-secondary"
                                                       title="PDF formulario candidato (referencia)"
                                                       target="_blank">
                                                        <i class="bi bi-ui-checks"></i>
                                                    </a>
                                                @else
                                                    <a href="{{ route('empresa.cuestionarios.show', $evaluado) }}"
                                                       class="btn btn-sm btn-primary"
                                                       title="Ver informe de evaluación">
                                                        <i class="bi bi-file-earmark-text"></i>
                                                    </a>
                                                @endif
                                            @else
                                                <span class="badge bg-light text-muted border" title="El informe se habilitará cuando REPRO marque los resultados como disponibles">
                                                    <i class="bi bi-clock-history"></i> En proceso
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($evaluado->orden_id)
                                                <a href="{{ \App\Support\OrdenPortalSupport::urlDetalle(Auth::user(), $evaluado->orden_id, '#heading-evaluado-'.$evaluado->id) }}"
                                                   class="btn btn-sm btn-outline-secondary"
                                                   title="Ver papelería y documentos del candidato">
                                                    <i class="bi bi-folder2-open"></i>
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="16" class="text-center py-4 text-muted">
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
