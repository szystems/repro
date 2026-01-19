@extends('layouts.admin')

@section('title', 'Gestión de Cuestionarios')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="mb-0">
                        <i class="bi bi-clipboard-check"></i> Gestión de Cuestionarios Socioeconómicos
                    </h3>
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-primary" id="btnFiltros">
                            <i class="bi bi-funnel"></i> Filtros
                        </button>
                        <button type="button" class="btn btn-success" onclick="exportarExcel()">
                            <i class="bi bi-file-earmark-excel"></i> Exportar Excel
                        </button>
                    </div>
                </div>
                
                <div class="card-body">
                    {{-- Panel de filtros (colapsable) --}}
                    <div class="collapse" id="filtrosPanel">
                        <div class="card card-body mb-4 bg-light">
                            <form method="GET" action="{{ route('admin.cuestionarios.index') }}" id="formFiltros">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="filtro_estado" class="form-label">Estado</label>
                                            <select class="form-control" id="filtro_estado" name="estado">
                                                <option value="">Todos los estados</option>
                                                <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                                <option value="en_progreso" {{ request('estado') == 'en_progreso' ? 'selected' : '' }}>En Progreso</option>
                                                <option value="completado" {{ request('estado') == 'completado' ? 'selected' : '' }}>Completado</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="filtro_empresa" class="form-label">Empresa</label>
                                            <select class="form-control" id="filtro_empresa" name="empresa_id">
                                                <option value="">Todas las empresas</option>
                                                @foreach($empresas as $empresa)
                                                    <option value="{{ $empresa->id }}" {{ request('empresa_id') == $empresa->id ? 'selected' : '' }}>
                                                        {{ $empresa->nombre }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="filtro_fecha_desde" class="form-label">Desde</label>
                                            <input type="date" class="form-control" id="filtro_fecha_desde" name="fecha_desde" value="{{ request('fecha_desde') }}">
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="filtro_fecha_hasta" class="form-label">Hasta</label>
                                            <input type="date" class="form-control" id="filtro_fecha_hasta" name="fecha_hasta" value="{{ request('fecha_hasta') }}">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="filtro_buscar" class="form-label">Buscar</label>
                                            <input type="text" class="form-control" id="filtro_buscar" name="buscar" 
                                                   value="{{ request('buscar') }}" placeholder="Nombre, DPI, teléfono...">
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6 d-flex align-items-end">
                                        <div class="btn-group w-100">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bi bi-search"></i> Aplicar Filtros
                                            </button>
                                            <a href="{{ route('admin.cuestionarios.index') }}" class="btn btn-outline-secondary">
                                                <i class="bi bi-x-lg"></i> Limpiar
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    {{-- Estadísticas rápidas --}}
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h5 class="card-title mb-0">{{ $estadisticas['total'] }}</h5>
                                            <small>Total Cuestionarios</small>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="bi bi-clipboard-check fs-2"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h5 class="card-title mb-0">{{ $estadisticas['pendientes'] }}</h5>
                                            <small>Pendientes</small>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="bi bi-clock fs-2"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h5 class="card-title mb-0">{{ $estadisticas['en_progreso'] }}</h5>
                                            <small>En Progreso</small>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="bi bi-pencil-square fs-2"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h5 class="card-title mb-0">{{ $estadisticas['completados'] }}</h5>
                                            <small>Completados</small>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="bi bi-check-circle fs-2"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Tabla de cuestionarios --}}
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="tablaCuestionarios">
                            <thead class="table-dark">
                                <tr>
                                    <th>
                                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'id', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}" 
                                           class="text-white text-decoration-none">
                                            #ID
                                            @if(request('sort') == 'id')
                                                <i class="bi bi-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                            @else
                                                <i class="bi bi-arrow-down-up"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th>Evaluado</th>
                                    <th>Empresa</th>
                                    <th>Puesto</th>
                                    <th>Estado</th>
                                    <th>Progreso</th>
                                    <th>
                                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}" 
                                           class="text-white text-decoration-none">
                                            Fecha
                                            @if(request('sort') == 'created_at')
                                                <i class="bi bi-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                            @else
                                                <i class="bi bi-arrow-down-up"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($cuestionarios as $cuestionario)
                                    @php
                                        $evaluado = $cuestionario->evaluadoOrden;
                                        $empresa = $evaluado->orden->empresa;
                                        $progreso = $cuestionario->calcularProgreso();
                                    @endphp
                                    <tr>
                                        <td class="font-weight-bold">#{{ $cuestionario->id }}</td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="font-weight-bold">{{ $evaluado->nombre }} {{ $evaluado->apellidos }}</span>
                                                <small class="text-muted">DPI: {{ $evaluado->dpi }}</small>
                                                @if($evaluado->telefono)
                                                    <small class="text-muted">Tel: {{ $evaluado->telefono }}</small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $empresa->nombre }}</span>
                                        </td>
                                        <td>{{ $evaluado->puesto_evaluar ?? 'No especificado' }}</td>
                                        <td>
                                            @switch($cuestionario->estado)
                                                @case('pendiente')
                                                    <span class="badge bg-warning">
                                                        <i class="bi bi-clock"></i> Pendiente
                                                    </span>
                                                    @break
                                                @case('en_progreso')
                                                    <span class="badge bg-info">
                                                        <i class="bi bi-pencil-square"></i> En Progreso
                                                    </span>
                                                    @break
                                                @case('completado')
                                                    <span class="badge bg-success">
                                                        <i class="bi bi-check-circle"></i> Completado
                                                    </span>
                                                    @break
                                                @default
                                                    <span class="badge bg-secondary">Desconocido</span>
                                            @endswitch
                                        </td>
                                        <td>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar 
                                                    @if($progreso < 25) bg-danger 
                                                    @elseif($progreso < 75) bg-warning 
                                                    @else bg-success @endif" 
                                                     role="progressbar" 
                                                     style="width: {{ $progreso }}%">
                                                    {{ $progreso }}%
                                                </div>
                                            </div>
                                            <small class="text-muted">{{ $cuestionario->seccion_actual }}/{{ $cuestionario->total_secciones }} secciones</small>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <small>{{ $cuestionario->created_at->format('d/m/Y') }}</small>
                                                <small class="text-muted">{{ $cuestionario->created_at->format('H:i') }}</small>
                                                @if($cuestionario->completado_at)
                                                    <small class="text-success">
                                                        <i class="bi bi-check"></i> {{ $cuestionario->completado_at->format('d/m/Y H:i') }}
                                                    </small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" 
                                                        data-bs-toggle="dropdown">
                                                    <i class="bi bi-gear"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('admin.cuestionarios.show', $cuestionario) }}">
                                                            <i class="bi bi-eye"></i> Ver Detalles
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('admin.cuestionarios.edit', $cuestionario) }}">
                                                            <i class="bi bi-pencil"></i> 
                                                            @if($cuestionario->estado == 'completado')
                                                                Editar (Completado)
                                                            @else
                                                                Editar
                                                            @endif
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('admin.cuestionarios.pdf', $cuestionario) }}" target="_blank">
                                                            <i class="bi bi-file-earmark-pdf"></i> Generar PDF
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    @if($cuestionario->estado != 'completado')
                                                        <li>
                                                            <form action="{{ route('admin.cuestionarios.completar', $cuestionario) }}" 
                                                                  method="POST" 
                                                                  onsubmit="return confirm('¿Marcar como completado manualmente?')">
                                                                @csrf
                                                                <button type="submit" class="dropdown-item text-success">
                                                                    <i class="bi bi-check-circle"></i> Marcar Completo
                                                                </button>
                                                            </form>
                                                        </li>
                                                    @endif
                                                    <li>
                                                        <a class="dropdown-item text-primary" 
                                                           href="{{ route('cuestionario.mostrar', $evaluado->token_unico) }}" 
                                                           target="_blank">
                                                            <i class="bi bi-box-arrow-up-right"></i> Enlace Evaluado
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="bi bi-inbox fs-1 mb-3 d-block"></i>
                                                <p class="mb-0">No se encontraron cuestionarios con los filtros aplicados</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    {{-- Paginación --}}
                    @if($cuestionarios->hasPages())
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div>
                                <small class="text-muted">
                                    Mostrando {{ $cuestionarios->firstItem() }} a {{ $cuestionarios->lastItem() }} 
                                    de {{ $cuestionarios->total() }} resultados
                                </small>
                            </div>
                            <div>
                                {{ $cuestionarios->appends(request()->query())->links() }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.table th {
    border-top: none;
    font-weight: 600;
}

.progress {
    border-radius: 10px;
}

.badge {
    font-size: 0.75em;
}

.dropdown-menu {
    border: none;
    box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1);
}

.card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.card-header h3 {
    color: white;
}

.btn-outline-primary {
    border-color: white;
    color: white;
}

.btn-outline-primary:hover {
    background-color: white;
    color: #667eea;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnFiltros = document.getElementById('btnFiltros');
    const filtrosPanel = document.getElementById('filtrosPanel');
    
    btnFiltros.addEventListener('click', function() {
        const bsCollapse = new bootstrap.Collapse(filtrosPanel);
        bsCollapse.toggle();
    });
    
    // Auto-aplicar filtros cuando cambian
    const filtros = document.querySelectorAll('#formFiltros input, #formFiltros select');
    filtros.forEach(filtro => {
        if (filtro.type !== 'submit') {
            filtro.addEventListener('change', function() {
                if (this.value !== '' || document.getElementById('filtro_buscar').value !== '') {
                    // Aplicar filtros automáticamente
                    setTimeout(() => {
                        document.getElementById('formFiltros').submit();
                    }, 300);
                }
            });
        }
    });
    
    // Búsqueda en tiempo real (con debounce)
    let searchTimeout;
    const buscarInput = document.getElementById('filtro_buscar');
    
    buscarInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            if (this.value.length >= 3 || this.value.length === 0) {
                document.getElementById('formFiltros').submit();
            }
        }, 500);
    });
});

function exportarExcel() {
    const params = new URLSearchParams(window.location.search);
    params.set('export', 'excel');
    
    window.location.href = `{{ route('admin.cuestionarios.index') }}?${params.toString()}`;
}
</script>
@endpush