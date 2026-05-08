@extends('layouts.admin')

@section('content')
<div class="content-wrapper">
    <!-- Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <h3 class="page-title">
                    <i class="bi bi-building-fill-check me-2"></i>Reporte de Empresas
                </h3>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Reporte de Empresas</li>
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
                    <form method="GET" action="{{ route('reportes.empresas') }}" id="filtroEmpresasForm">
                        <div class="row g-3">
                            <div class="col-md-2">
                                <label class="form-label">Mes Rápido</label>
                                <select name="mes" class="form-select" onchange="aplicarFiltroMesEmpresas(this)">
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
                            <div class="col-md-3">
                                <label class="form-label">Estado Empresa</label>
                                <select name="estado" class="form-select">
                                    <option value="">Todas</option>
                                    <option value="1" {{ request('estado') === '1' ? 'selected' : '' }}>Activas</option>
                                    <option value="0" {{ request('estado') === '0' ? 'selected' : '' }}>Inactivas</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Empresa</label>
                                <select name="empresa_id" class="form-select">
                                    <option value="">Todas</option>
                                    @foreach($todasEmpresas as $id => $nombre)
                                        <option value="{{ $id }}" {{ request('empresa_id') == $id ? 'selected' : '' }}>{{ $nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Sede</label>
                                <select name="sede_id" class="form-select">
                                    <option value="">Todas las sedes</option>
                                    @foreach($todasSedes as $id => $nombre)
                                        <option value="{{ $id }}" {{ request('sede_id') == $id ? 'selected' : '' }}>{{ $nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search me-1"></i>Filtrar
                                </button>
                                <a href="{{ route('reportes.empresas') }}" class="btn btn-outline-secondary ms-2">
                                    <i class="bi bi-x-circle me-1"></i>Limpiar
                                </a>
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
                            <p class="text-muted mb-1 small text-uppercase">Total Empresas</p>
                            <h3 class="mb-0 text-primary">{{ $stats['total_empresas'] }}</h3>
                        </div>
                        <div class="me-3" style="width: 48px; height: 48px;">
                            <div class="bg-primary d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; border-radius: 50%;">
                                <i class="bi bi-building text-white fs-4"></i>
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
                            <p class="text-muted mb-1 small text-uppercase">Empresas Activas</p>
                            <h3 class="mb-0 text-success">{{ $stats['empresas_activas'] }}</h3>
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
            <div class="card border-start border-4 border-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted mb-1 small text-uppercase">Total Órdenes</p>
                            <h3 class="mb-0 text-info">{{ $stats['total_ordenes'] }}</h3>
                        </div>
                        <div class="me-3" style="width: 48px; height: 48px;">
                            <div class="bg-info d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; border-radius: 50%;">
                                <i class="bi bi-file-earmark-text text-white fs-4"></i>
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
                            <p class="text-muted mb-1 small text-uppercase">Total Evaluados</p>
                            <h3 class="mb-0 text-warning">{{ $stats['total_evaluados'] }}</h3>
                        </div>
                        <div class="me-3" style="width: 48px; height: 48px;">
                            <div class="bg-warning d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; border-radius: 50%;">
                                <i class="bi bi-people text-white fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Botones de exportación y tabla -->
    @if($ranking->isNotEmpty())
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-trophy me-2 text-warning"></i>Ranking de empresas (por procesos entregados)
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width:50px">#</th>
                                    <th>Empresa</th>
                                    <th class="text-center">Procesos entregados</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($ranking as $i => $emp)
                                <tr>
                                    <td class="text-center fw-bold">{{ $i + 1 }}</td>
                                    <td><a href="{{ url('show-empresa/' . $emp->id) }}">{{ $emp->nombre }}</a></td>
                                    <td class="text-center">
                                        <span class="badge bg-success">{{ $emp->ordenes_entregadas }}</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-table me-2"></i>Listado de Empresas
                    </h5>
                    <div class="btn-group">
                        <a href="{{ route('reportes.empresas.pdf', request()->query()) }}" 
                           class="btn btn-danger btn-sm" target="_blank">
                            <i class="bi bi-file-pdf me-1"></i>Exportar PDF
                        </a>
                        <a href="{{ route('reportes.empresas.excel', request()->query()) }}" 
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
                                    <th>Empresa</th>
                                    <th>NIT</th>
                                    <th>Contacto</th>
                                    <th class="text-center">Órdenes</th>
                                    <th class="text-center">Completadas</th>
                                    <th class="text-center">Pendientes</th>
                                    <th>Estado</th>
                                    <th>Registro</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($empresas as $empresa)
                                    <tr>
                                        <td>
                                            <a href="{{ url('show-empresa/' . $empresa->id) }}" class="text-primary fw-bold">
                                                {{ $empresa->nombre }}
                                            </a>
                                        </td>
                                        <td>{{ $empresa->nit ?? 'N/A' }}</td>
                                        <td>
                                            <small>
                                                {{ $empresa->contacto_nombre ?? 'N/A' }}<br>
                                                <span class="text-muted">{{ $empresa->email ?? '' }}</span>
                                            </small>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary">{{ $empresa->ordenes_count }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-success">{{ $empresa->ordenes_completadas_count }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-warning">{{ $empresa->ordenes_pendientes_count }}</span>
                                        </td>
                                        <td>
                                            @if($empresa->estado == 1)
                                                <span class="badge bg-success">Activa</span>
                                            @else
                                                <span class="badge bg-secondary">Inactiva</span>
                                            @endif
                                        </td>
                                        <td>{{ $empresa->created_at->format('d/m/Y') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">
                                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                            No se encontraron empresas con los filtros seleccionados
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($empresas->hasPages())
                <div class="card-footer">
                    {{ $empresas->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function aplicarFiltroMesEmpresas(select) {
    if (!select.value) return;
    const [year, month] = select.value.split('-');
    const inicio = `${year}-${month}-01`;
    const lastDay = new Date(year, month, 0).getDate();
    const fin = `${year}-${month}-${String(lastDay).padStart(2, '0')}`;
    const form = document.getElementById('filtroEmpresasForm');
    form.querySelector('[name="fecha_inicio"]').value = inicio;
    form.querySelector('[name="fecha_fin"]').value = fin;
}
</script>
@endpush
