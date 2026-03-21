@extends(session('layout', 'layouts.admin'))
@section('content')

<div class="content-wrapper-scroll">
    <div class="main-header d-flex align-items-center justify-content-between position-relative">
        <div class="d-flex align-items-center justify-content-center">
            <div class="page-icon">
                <i class="bi bi-bar-chart"></i>
            </div>
            <div class="page-title d-none d-md-block">
                <h5>Resumen de Órdenes</h5>
            </div>
        </div>
    </div>

    <div class="content-card-area mt-3">
        {{-- Estadísticas generales --}}
        <div class="row mb-4">
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="bi bi-folder2-open fs-1 text-primary"></i>
                        <h3 class="mt-2">{{ $totalOrdenes }}</h3>
                        <p class="text-muted mb-0">Total Órdenes</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="bi bi-clock-history fs-1 text-warning"></i>
                        <h3 class="mt-2">{{ $ordenesPendientes }}</h3>
                        <p class="text-muted mb-0">Pendientes</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="bi bi-gear fs-1 text-info"></i>
                        <h3 class="mt-2">{{ $ordenesActivas }}</h3>
                        <p class="text-muted mb-0">En Proceso</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="bi bi-check-circle fs-1 text-success"></i>
                        <h3 class="mt-2">{{ $ordenesCompletadas }}</h3>
                        <p class="text-muted mb-0">Completadas</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Evaluados --}}
        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="bi bi-people fs-1 text-primary"></i>
                        <h3 class="mt-2">{{ $totalEvaluados }}</h3>
                        <p class="text-muted mb-0">Total Evaluados</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="bi bi-person-check fs-1 text-success"></i>
                        <h3 class="mt-2">{{ $evaluadosCompletados }}</h3>
                        <p class="text-muted mb-0">Evaluados Completados</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Por Empresa --}}
        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white"><strong>Órdenes por Empresa (Top 10)</strong></div>
                    <div class="card-body">
                        @forelse($porEmpresa as $item)
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>{{ $item->empresa->nombre ?? 'Sin empresa' }}</span>
                                <span class="badge bg-primary">{{ $item->total }}</span>
                            </div>
                        @empty
                            <p class="text-muted">Sin datos.</p>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white"><strong>Órdenes por Sede</strong></div>
                    <div class="card-body">
                        @forelse($porSede as $item)
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>{{ $item->sede->nombre ?? 'Sin sede' }}</span>
                                <span class="badge bg-info">{{ $item->total }}</span>
                            </div>
                        @empty
                            <p class="text-muted">Sin datos de sedes asignadas.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
