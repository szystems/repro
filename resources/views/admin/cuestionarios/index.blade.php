@extends('layouts.admin')

@section('title', 'Gestión de Cuestionario – Candidatos')

@section('content')
<div class="content-wrapper-scroll">

    <div class="main-header d-flex align-items-center justify-content-between position-relative">
        <div class="d-flex align-items-center justify-content-center">
            <div class="page-icon">
                <i class="bi bi-clipboard-check"></i>
            </div>
            <div class="page-title">
                <h5>Gestión de Cuestionario – Candidatos</h5>
            </div>
        </div>
        <div class="d-flex align-items-end d-none d-sm-block">
            <h6 class="float-end text-light" id="reloj"></h6>
        </div>
    </div>

    <div class="content-wrapper">
        <div class="row gx-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div class="card-title">
                            <i class="bi bi-clipboard-check"></i> Cuestionarios
                        </div>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnFiltros">
                            <i class="bi bi-funnel-fill"></i> Filtrar
                        </button>
                    </div>

                <div class="card-body">
                    {{-- Accesos rápidos --}}
                    <div class="mb-3 d-flex gap-2 flex-wrap">
                        <a href="{{ route('admin.cuestionarios.index') }}" class="btn btn-sm {{ !request()->hasAny(['estado','tipo_servicio','sede_id','empresa_id','fecha_desde','fecha_hasta','buscar']) ? 'btn-primary' : 'btn-outline-primary' }}">
                            <i class="bi bi-list-ul"></i> Todos
                        </a>
                        <a href="{{ route('admin.cuestionarios.index', ['estado' => 'pendiente']) }}" class="btn btn-sm {{ request('estado') == 'pendiente' ? 'btn-warning' : 'btn-outline-warning' }}">
                            <i class="bi bi-hourglass"></i> Pendientes
                        </a>
                        <a href="{{ route('admin.cuestionarios.index', ['estado' => 'en_progreso']) }}" class="btn btn-sm {{ request('estado') == 'en_progreso' ? 'btn-info' : 'btn-outline-info' }}">
                            <i class="bi bi-pencil-square"></i> En Progreso
                        </a>
                        <a href="{{ route('admin.cuestionarios.index', ['estado' => 'completado']) }}" class="btn btn-sm {{ request('estado') == 'completado' ? 'btn-success' : 'btn-outline-success' }}">
                            <i class="bi bi-check-circle"></i> Completados
                        </a>
                    </div>
                    <div class="collapse" id="filtrosPanel">
                        <div class="card card-body mb-4 bg-light">
                            <form method="GET" action="{{ route('admin.cuestionarios.index') }}" id="formFiltros">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="filtro_estado" class="form-label">Progreso del cuestionario</label>
                                            <select class="form-control" id="filtro_estado" name="estado">
                                                <option value="">Todos</option>
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

                                <div class="row mt-2">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="filtro_tipo_servicio" class="form-label">Tipo de Servicio</label>
                                            <select class="form-control" id="filtro_tipo_servicio" name="tipo_servicio">
                                                <option value="">Todos los servicios</option>
                                                <option value="poligrafo" {{ request('tipo_servicio') == 'poligrafo' ? 'selected' : '' }}>Polígrafo</option>
                                                <option value="vsa" {{ request('tipo_servicio') == 'vsa' ? 'selected' : '' }}>VSA</option>
                                                <option value="socioeconomico" {{ request('tipo_servicio') == 'socioeconomico' ? 'selected' : '' }}>Socioeconómico</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="filtro_sede_id" class="form-label">Sede</label>
                                            <select class="form-control" id="filtro_sede_id" name="sede_id">
                                                <option value="">Todas las sedes</option>
                                                @foreach($sedes as $sede)
                                                    <option value="{{ $sede->id }}" {{ request('sede_id') == $sede->id ? 'selected' : '' }}>
                                                        {{ $sede->nombre }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="filtro_buscar" class="form-label">Buscar</label>
                                            <input type="text" class="form-control" id="filtro_buscar" name="buscar"
                                                   value="{{ request('buscar') }}" placeholder="Nombre, DPI, teléfono...">
                                        </div>
                                    </div>

                                    <div class="col-md-2 d-flex align-items-end">
                                        <div class="btn-group w-100">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bi bi-search"></i> Filtrar
                                            </button>
                                            <a href="{{ route('admin.cuestionarios.index') }}" class="btn btn-outline-secondary">
                                                <i class="bi bi-x-lg"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Estadísticas rápidas --}}
                    <div class="row mb-4">
                        <div class="col-md-2">
                            <div class="card bg-primary text-white">
                                <div class="card-body py-2">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h5 class="card-title mb-0">{{ $estadisticas['total'] }}</h5>
                                            <small>Total</small>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="bi bi-clipboard-check fs-3"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="card bg-warning text-white">
                                <div class="card-body py-2">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h5 class="card-title mb-0">{{ $estadisticas['pendientes'] }}</h5>
                                            <small>Pendientes</small>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="bi bi-clock fs-3"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="card bg-info text-white">
                                <div class="card-body py-2">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h5 class="card-title mb-0">{{ $estadisticas['en_progreso'] }}</h5>
                                            <small>En Progreso</small>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="bi bi-pencil-square fs-3"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="card bg-success text-white">
                                <div class="card-body py-2">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h5 class="card-title mb-0">{{ $estadisticas['completados'] }}</h5>
                                            <small>Completados</small>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="bi bi-check-circle fs-3"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="card bg-secondary text-white">
                                <div class="card-body py-2">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h5 class="card-title mb-0">{{ $estadisticas['completados_hoy'] }}</h5>
                                            <small>Hoy</small>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="bi bi-calendar-check fs-3"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="card bg-purple text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                <div class="card-body py-2">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            @php
                                                $tasaCompletado = $estadisticas['total'] > 0
                                                    ? round(($estadisticas['completados'] / $estadisticas['total']) * 100, 1)
                                                    : 0;
                                            @endphp
                                            <h5 class="card-title mb-0 text-white">{{ $tasaCompletado }}%</h5>
                                            <small class="text-white">Tasa Completado</small>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="bi bi-percent fs-3 text-white"></i>
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
                                    <th>Orden</th>
                                    <th>Evaluado</th>
                                    <th>Contacto</th>
                                    <th>Empresa</th>
                                    <th>Sede</th>
                                    <th>Servicio / Formulario</th>
                                    <th>Estado de Formulario</th>
                                    <th>Estado de Programación</th>
                                    <th>Estado de Evaluación</th>
                                    <th>Progreso cuestionario</th>
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
                                @forelse($evaluados as $evaluado)
                                    @php
                                        $cuestionario = $evaluado->cuestionario;
                                        $orden = $evaluado->orden;
                                        $empresa = $orden->empresa;
                                        $progreso = $cuestionario
                                            ? $cuestionario->calcularProgreso()
                                            : ($evaluado->cuestionario_completado ? 100 : 0);
                                    @endphp
                                    <tr>
                                        <td class="font-weight-bold">#{{ $evaluado->id }}</td>
                                        <td>
                                            <a href="{{ route('ordenes.show', $orden) }}"
                                               class="text-decoration-none"
                                               title="Ver orden {{ $orden->codigo_orden }}">
                                                <span class="badge bg-dark">
                                                    <i class="bi bi-folder2-open"></i> {{ $orden->codigo_orden }}
                                                </span>
                                            </a>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="font-weight-bold">{{ $evaluado->nombre }} {{ $evaluado->apellidos }}</span>
                                                <small class="text-muted">DPI: {{ $evaluado->dpi }}</small>
                                                <small class="text-muted">Puesto: {{ $evaluado->puesto_evaluar ?? 'No especificado' }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                @if($evaluado->email)
                                                    <small><i class="bi bi-envelope"></i> {{ $evaluado->email }}</small>
                                                @else
                                                    <small class="text-muted"><i class="bi bi-envelope"></i> Sin email</small>
                                                @endif
                                                @if($evaluado->telefono)
                                                    <small><i class="bi bi-telephone"></i> {{ $evaluado->telefono }}</small>
                                                @endif
                                                @if($evaluado->celular && $evaluado->celular != $evaluado->telefono)
                                                    <small><i class="bi bi-phone"></i> {{ $evaluado->celular }}</small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $empresa->nombre }}</span>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                @if($orden->sede)
                                                    <i class="bi bi-geo-alt"></i> {{ $orden->sede->nombre }}
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </small>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                @if($evaluado->tipo_servicio)
                                                    <span class="badge bg-primary mb-1">{{ ucfirst(str_replace('_', ' ', $evaluado->tipo_servicio)) }}</span>
                                                @endif
                                                @if($evaluado->tipo_formulario)
                                                    <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $evaluado->tipo_formulario)) }}</span>
                                                @else
                                                    <span class="badge bg-light text-dark">{{ ucfirst(str_replace('_', ' ', $evaluado->tipo_formulario ?? 'Estándar')) }}</span>
                                                @endif
                                            </div>
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
                                            <small class="text-muted">
                                                @if($cuestionario)
                                                    {{ $cuestionario->seccion_actual }}/{{ $cuestionario->total_secciones }} secciones
                                                @else
                                                    Sin iniciar
                                                @endif
                                            </small>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <small>{{ $evaluado->created_at->format('d/m/Y') }}</small>
                                                <small class="text-muted">{{ $evaluado->created_at->format('H:i') }}</small>
                                                @if($evaluado->completado_at)
                                                    <small class="text-success">
                                                        <i class="bi bi-check"></i> {{ $evaluado->completado_at->format('d/m/Y H:i') }}
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
                                                    @if($cuestionario)
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
                                                    @else
                                                    <li>
                                                        <span class="dropdown-item-text text-muted small">
                                                            <i class="bi bi-info-circle"></i> El candidato aún no abre el formulario
                                                        </span>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    @endif
                                                    <li>
                                                        <a class="dropdown-item text-primary"
                                                           href="{{ route('cuestionario.mostrar', $evaluado->token_unico) }}"
                                                           target="_blank">
                                                            <i class="bi bi-box-arrow-up-right"></i> Enlace Evaluado
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <button type="button" class="dropdown-item text-secondary"
                                                                onclick="copiarEnlaceEvaluado('{{ route('cuestionario.mostrar', $evaluado->token_unico) }}')">
                                                            <i class="bi bi-clipboard"></i> Copiar Enlace
                                                        </button>
                                                    </li>
                                                    @if($evaluado->email && (!$cuestionario || !$cuestionario->completado))
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <form action="{{ route('evaluados.reenviar-correo', $evaluado) }}"
                                                                  method="POST"
                                                                  onsubmit="return confirm('¿Reenviar correo con enlace del cuestionario a {{ $evaluado->email }}?')">
                                                                @csrf
                                                                <button type="submit" class="dropdown-item text-info">
                                                                    <i class="bi bi-envelope-arrow-up"></i> Reenviar Correo
                                                                </button>
                                                            </form>
                                                        </li>
                                                    @endif
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="13" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="bi bi-inbox fs-1 mb-3 d-block"></i>
                                                <p class="mb-0">No se encontraron candidatos con los filtros aplicados</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Paginación --}}
                    @if($evaluados->hasPages())
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div>
                                <small class="text-muted">
                                    Mostrando {{ $evaluados->firstItem() }} a {{ $evaluados->lastItem() }}
                                    de {{ $evaluados->total() }} resultados
                                </small>
                            </div>
                            <div>
                                {{ $evaluados->appends(request()->query())->links() }}
                            </div>
                        </div>
                    @endif
                </div>
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

.card-header .btn-outline-primary {
    border-color: white;
    color: white;
}

.card-header .btn-outline-primary:hover {
    background-color: white;
    color: #667eea;
}</style>
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

// Función para copiar enlace del evaluado al portapapeles
function copiarEnlaceEvaluado(url) {
    navigator.clipboard.writeText(url).then(function() {
        // Mostrar notificación temporal
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
    }).catch(function(err) {
        alert('Error al copiar: ' + err);
    });
}
</script>
@endpush
