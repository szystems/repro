@extends(session('layout', 'layouts.admin'))

@section('title', 'Centro de Notificaciones')

@section('content')
<div class="content-wrapper-scroll">

    <div class="main-header d-flex align-items-center justify-content-between position-relative">
        <div class="d-flex align-items-center justify-content-center">
            <div class="page-icon">
                <i class="bi bi-bell"></i>
            </div>
            <div class="page-title">
                <h5>Centro de Notificaciones</h5>
                @if($totalNoLeidas > 0)
                    <p class="text-muted mb-0">{{ $totalNoLeidas }} sin leer</p>
                @endif
            </div>
        </div>
        @if($totalNoLeidas > 0)
        <div>
            <form method="POST" action="{{ route('notificaciones.leer-todas') }}" id="formMarcarTodas">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-check2-all"></i> Marcar todas como leídas
                </button>
            </form>
        </div>
        @endif
    </div>

    <div class="content-wrapper">
        <div class="row gx-3">
            <div class="col-12">

                {{-- Filtros --}}
                <div class="card mb-3">
                    <div class="card-body py-3">
                        <form method="GET" action="{{ route('notificaciones.centro') }}">
                            <div class="row g-2 align-items-end">
                                <div class="col-md-2">
                                    <label class="form-label">Estado</label>
                                    <select class="form-select" name="estado">
                                        <option value="">Todas</option>
                                        <option value="no_leida" {{ request('estado') == 'no_leida' ? 'selected' : '' }}>No leídas</option>
                                        <option value="leida" {{ request('estado') == 'leida' ? 'selected' : '' }}>Leídas</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Tipo</label>
                                    <select class="form-select" name="tipo">
                                        <option value="">Todos</option>
                                        <option value="OrdenCreada" {{ request('tipo') == 'OrdenCreada' ? 'selected' : '' }}>Orden creada</option>
                                        <option value="ResultadosDisponibles" {{ request('tipo') == 'ResultadosDisponibles' ? 'selected' : '' }}>Resultados disponibles</option>
                                        <option value="EvaluadoAsignado" {{ request('tipo') == 'EvaluadoAsignado' ? 'selected' : '' }}>Evaluado asignado</option>
                                        <option value="CuestionarioCompletado" {{ request('tipo') == 'CuestionarioCompletado' ? 'selected' : '' }}>Cuestionario completado</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Desde</label>
                                    <input type="date" class="form-control" name="fecha_desde" value="{{ request('fecha_desde') }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Hasta</label>
                                    <input type="date" class="form-control" name="fecha_hasta" value="{{ request('fecha_hasta') }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Buscar</label>
                                    <input type="text" class="form-control" name="buscar" value="{{ request('buscar') }}" placeholder="Texto del mensaje...">
                                </div>
                                <div class="col-md-1">
                                    <div class="d-flex gap-1">
                                        <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i></button>
                                        <a href="{{ route('notificaciones.centro') }}" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Lista --}}
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div class="card-title">
                            {{ $notificaciones->total() }} notificacion{{ $notificaciones->total() != 1 ? 'es' : '' }}
                            @if(request()->hasAny(['estado','tipo','fecha_desde','fecha_hasta','buscar']))
                                <span class="badge bg-info ms-1">Filtrado</span>
                            @endif
                        </div>
                    </div>
                    <div class="card-body p-0">
                        @forelse($notificaciones as $notificacion)
                            @php
                                $data = $notificacion->data;
                                $leida = $notificacion->read_at !== null;
                                $icono = $data['icono'] ?? 'bi-bell';
                                $color = $data['color'] ?? 'secondary';
                                $mensaje = $data['mensaje'] ?? '(Sin mensaje)';
                                $url = $data['url'] ?? null;
                            @endphp
                            <div class="d-flex align-items-start gap-3 px-4 py-3 border-bottom {{ $leida ? '' : 'bg-light' }}"
                                 id="notif-{{ $notificacion->id }}">
                                <div class="mt-1">
                                    <span class="text-{{ $color }}">
                                        <i class="bi {{ $icono }} fs-4"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="{{ $leida ? 'text-muted' : 'fw-semibold' }}">
                                        {{ $mensaje }}
                                    </div>
                                    <div class="small text-muted mt-1">
                                        <i class="bi bi-clock"></i>
                                        {{ $notificacion->created_at->format('d/m/Y H:i') }}
                                        &middot;
                                        {{ $notificacion->created_at->diffForHumans() }}
                                        @if(!$leida)
                                            &middot; <span class="badge bg-warning text-dark">No leída</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="d-flex gap-2 align-items-center">
                                    @if($url)
                                        <a href="{{ $url }}"
                                           class="btn btn-sm btn-outline-primary notif-ver-link"
                                           data-id="{{ $notificacion->id }}"
                                           data-url="{{ $url }}">
                                            <i class="bi bi-arrow-right"></i> Ver
                                        </a>
                                    @endif
                                    @if(!$leida)
                                        <button class="btn btn-sm btn-outline-secondary notif-marcar-leida"
                                                data-id="{{ $notificacion->id }}">
                                            <i class="bi bi-check"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted py-5">
                                <i class="bi bi-bell-slash fs-1 d-block mb-2"></i>
                                No hay notificaciones con los filtros seleccionados.
                            </div>
                        @endforelse
                    </div>
                    @if($notificaciones->hasPages())
                    <div class="card-footer d-flex justify-content-center">
                        {{ $notificaciones->links() }}
                    </div>
                    @endif
                </div>

            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Marcar individual como leída (sin navegar)
    document.querySelectorAll('.notif-marcar-leida').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            fetch('{{ url("notificaciones") }}/' + id + '/leer', {
                method: 'PATCH',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
            }).then(() => {
                const row = document.getElementById('notif-' + id);
                if (row) {
                    row.classList.remove('bg-light');
                    row.querySelector('.fw-semibold')?.classList.replace('fw-semibold', 'text-muted');
                    this.remove();
                    const badge = row.querySelector('.badge.bg-warning');
                    if (badge) { badge.remove(); }
                }
            });
        });
    });

    // "Ver" — marcar como leída y navegar
    document.querySelectorAll('.notif-ver-link').forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            const id = this.dataset.id;
            const url = this.dataset.url;
            fetch('{{ url("notificaciones") }}/' + id + '/leer', {
                method: 'PATCH',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
            }).then(() => { window.location.href = url; });
        });
    });

    // Marcar todas — submit AJAX sin redirigir
    document.getElementById('formMarcarTodas')?.addEventListener('submit', function (e) {
        e.preventDefault();
        fetch(this.action, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
        }).then(() => { window.location.reload(); });
    });
});
</script>
@endpush
@endsection
