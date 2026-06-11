@extends(session('layout', 'layouts.admin'))

@push('styles')
<style>
    /* Asegurar que los avatares sean círculos perfectos */
    .avatar {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        flex-shrink: 0 !important;
    }
    .avatar-sm {
        width: 32px !important;
        height: 32px !important;
        min-width: 32px !important;
        min-height: 32px !important;
    }
    .avatar-md {
        width: 40px !important;
        height: 40px !important;
        min-width: 40px !important;
        min-height: 40px !important;
    }
    .avatar-lg {
        width: 48px !important;
        height: 48px !important;
        min-width: 48px !important;
        min-height: 48px !important;
    }
    .avatar .avatar-title {
        width: 100% !important;
        height: 100% !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-weight: 600;
        aspect-ratio: 1 / 1 !important;
    }
    .avatar .avatar-title.rounded-circle {
        border-radius: 50% !important;
    }
</style>
@endpush

@section('content')

<!-- Content wrapper start -->
<div class="content-wrapper">

    <!-- Row start -->
    <div class="row">
        <div class="col-xxl-12">
            <div class="page-header">
                <h3 class="page-title">Panel de Control</h3>
                <div>
                    <span class="badge bg-primary-transparent" id="reloj"></span>
                </div>
            </div>
        </div>
    </div>
    <!-- Row end -->

    <!-- Row start - Mensaje de bienvenida -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert
                @if(Auth::user()->role_as == 3) alert-danger
                @elseif(Auth::user()->role_as == 2) alert-info
                @elseif(Auth::user()->role_as == 1) alert-success
                @else alert-secondary
                @endif">
                <div class="d-flex align-items-center">
                    <div class="me-3" style="width: 48px; height: 48px; min-width: 48px; flex-shrink: 0;">
                        <div class="bg-white d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; border-radius: 50%;">
                            <i class="bi
                                @if(Auth::user()->role_as == 3) bi-shield-fill text-danger
                                @elseif(Auth::user()->role_as == 2) bi-briefcase-fill text-info
                                @elseif(Auth::user()->role_as == 1) bi-building-fill text-success
                                @else bi-person-fill text-secondary
                                @endif fs-3"></i>
                        </div>
                    </div>
                    <div>
                        <h5 class="mb-1">Bienvenido/a, {{ Auth::user()->name }}</h5>
                        <p class="mb-0 small text-white" style="opacity: 0.9;">
                            @if(Auth::user()->role_as == 3)
                                Administrador del sistema REPRO Guatemala
                            @elseif(Auth::user()->role_as == 2)
                                Personal operativo de REPRO
                            @elseif(Auth::user()->role_as == 1)
                                {{ $empresa->nombre ?? 'Usuario de empresa' }}
                            @else
                                Sistema de evaluaciones REPRO
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================
         DASHBOARD PARA ADMIN Y REPRO
    ======================================== --}}
    @if(Auth::user()->role_as >= 2)

    <!-- Tarjetas de estadísticas principales -->
    <div class="row mb-4">
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
            <div class="card border-start border-4 border-primary">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted mb-1 small text-uppercase">Órdenes Totales</p>
                            <h3 class="mb-0 fw-bold text-primary">{{ $totalOrdenes ?? 0 }}</h3>
                            <small class="text-muted">
                                <i class="bi bi-clock"></i> {{ $ordenesPendientes ?? 0 }} pendientes
                            </small>
                        </div>
                        <div class="avatar avatar-lg">
                            <div class="avatar-title bg-primary-transparent rounded-circle">
                                <i class="bi bi-file-earmark-text text-primary fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
            <div class="card border-start border-4 border-success">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted mb-1 small text-uppercase">Evaluados</p>
                            <h3 class="mb-0 fw-bold text-success">{{ $totalEvaluados ?? 0 }}</h3>
                            <small class="{{ ($variacionEvaluados ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                                <i class="bi bi-{{ ($variacionEvaluados ?? 0) >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                                {{ abs($variacionEvaluados ?? 0) }}% vs mes anterior
                            </small>
                        </div>
                        <div class="avatar avatar-lg">
                            <div class="avatar-title bg-success-transparent rounded-circle">
                                <i class="bi bi-people text-success fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
            <div class="card border-start border-4 border-info">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted mb-1 small text-uppercase">Cuestionarios</p>
                            <h3 class="mb-0 fw-bold text-info">{{ $cuestionariosCompletados ?? 0 }}</h3>
                            <small class="text-warning">
                                <i class="bi bi-hourglass-split"></i> {{ $cuestionariosPendientes ?? 0 }} pendientes
                            </small>
                        </div>
                        <div class="avatar avatar-lg">
                            <div class="avatar-title bg-info-transparent rounded-circle">
                                <i class="bi bi-clipboard-check text-info fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
            <div class="card border-start border-4 border-warning">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted mb-1 small text-uppercase">Empresas Activas</p>
                            <h3 class="mb-0 fw-bold text-warning">{{ $totalEmpresas ?? 0 }}</h3>
                            <small class="text-muted">
                                <i class="bi bi-person-badge"></i> {{ $totalUsuarios ?? 0 }} usuarios
                            </small>
                        </div>
                        <div class="avatar avatar-lg">
                            <div class="avatar-title bg-warning-transparent rounded-circle">
                                <i class="bi bi-building text-warning fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Segunda fila: Gráficos y estados -->
    <div class="row mb-4">
        <!-- Órdenes por mes -->
        <div class="col-xl-8 col-lg-7">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-graph-up me-2"></i>Órdenes por Mes
                    </h5>
                    <span class="badge bg-primary">Últimos 6 meses</span>
                </div>
                <div class="card-body">
                    <div style="height: 220px; position: relative;">
                        <canvas id="ordenesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estados de órdenes -->
        <div class="col-xl-4 col-lg-5">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-pie-chart me-2"></i>Estado de Órdenes
                    </h5>
                </div>
                <div class="card-body">
                    @php
                        $estadosLabels = [
                            'orden_recibida' => ['label' => 'Orden Recibida', 'color' => 'secondary'],
                            'en_proceso'     => ['label' => 'En Proceso',     'color' => 'primary'],
                            'entregado'      => ['label' => 'Entregado',      'color' => 'success'],
                            'cancelado'      => ['label' => 'Cancelado',      'color' => 'danger'],
                        ];
                    @endphp

                    @forelse($ordenesPorEstado ?? [] as $estado => $total)
                        @php
                            $info = $estadosLabels[$estado] ?? ['label' => ucfirst($estado), 'color' => 'secondary'];
                            $porcentaje = $totalOrdenes > 0 ? round(($total / $totalOrdenes) * 100) : 0;
                        @endphp
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="small">{{ $info['label'] }}</span>
                                <span class="small fw-bold">{{ $total }}</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-{{ $info['color'] }}" role="progressbar"
                                     style="width: {{ $porcentaje }}%"></div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            No hay órdenes registradas
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Tercera fila: Servicios y últimas órdenes -->
    <div class="row mb-4">
        <!-- Evaluados por tipo de servicio -->
        <div class="col-xl-4 col-lg-5">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-diagram-3 me-2"></i>Por Tipo de Servicio
                    </h5>
                </div>
                <div class="card-body">
                    @php
                        $servicios = [
                            'poligrafo' => ['label' => 'Polígrafo', 'icon' => 'bi-activity', 'color' => 'primary'],
                            'vsa' => ['label' => 'VSA', 'icon' => 'bi-soundwave', 'color' => 'info'],
                            'socioeconomico' => ['label' => 'Socioeconómico', 'icon' => 'bi-file-earmark-person', 'color' => 'warning'],
                        ];
                        $totalServicio = array_sum($evaluadosPorServicio ?? []);
                    @endphp

                    <div class="row g-3">
                        @foreach($servicios as $key => $servicio)
                            @php
                                $cantidad = $evaluadosPorServicio[$key] ?? 0;
                                $porcentaje = $totalServicio > 0 ? round(($cantidad / $totalServicio) * 100) : 0;
                            @endphp
                            <div class="col-12">
                                <div class="card bg-{{ $servicio['color'] }}-transparent border-0 mb-0">
                                    <div class="card-body py-3">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3" style="width: 40px; height: 40px; min-width: 40px; flex-shrink: 0;">
                                                <div class="bg-{{ $servicio['color'] }} d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; border-radius: 50%;">
                                                    <i class="bi {{ $servicio['icon'] }} text-white"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-0">{{ $servicio['label'] }}</h6>
                                                <small class="text-muted">{{ $porcentaje }}% del total</small>
                                            </div>
                                            <div>
                                                <h4 class="mb-0 text-{{ $servicio['color'] }}">{{ $cantidad }}</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Últimas órdenes -->
        <div class="col-xl-8 col-lg-7">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-clock-history me-2"></i>Últimas Órdenes
                    </h5>
                    <a href="{{ route('ordenes.index') }}" class="btn btn-sm btn-outline-primary">
                        Ver todas <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Código</th>
                                    <th>Empresa</th>
                                    <th>Evaluados</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ultimasOrdenes ?? [] as $orden)
                                    <tr style="cursor:pointer" onclick="window.location='{{ route('ordenes.show', $orden) }}'">
                                        <td>
                                            <a href="{{ route('ordenes.show', $orden) }}" class="fw-bold text-primary text-decoration-none" onclick="event.stopPropagation()">
                                                {{ $orden->codigo_orden }}
                                            </a>
                                        </td>
                                        <td>{{ Str::limit($orden->empresa->nombre ?? 'N/A', 20) }}</td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $orden->evaluados->count() }}</span>
                                            @if($orden->evaluados->isNotEmpty())
                                                <div class="small text-muted mt-1">{{ $orden->evaluados->first()->nombre_completo }}</div>
                                                @if($orden->evaluados->count() > 1)
                                                    <div class="small text-muted">+{{ $orden->evaluados->count() - 1 }} más</div>
                                                @endif
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $estadoInfo = $estadosLabels[$orden->estado] ?? ['label' => $orden->estado, 'color' => 'secondary'];
                                            @endphp
                                            <span class="badge bg-{{ $estadoInfo['color'] }}">
                                                {{ $estadoInfo['label'] }}
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $orden->created_at->format('d/m/Y') }}</small>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">
                                            <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                            No hay órdenes registradas
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cuarta fila: Top empresas y accesos rápidos -->
    <div class="row mb-4">
        <!-- Top empresas -->
        <div class="col-xl-4 col-lg-5">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-trophy me-2"></i>Top Empresas
                    </h5>
                </div>
                <div class="card-body">
                    @forelse($topEmpresas ?? [] as $index => $emp)
                        <div class="d-flex align-items-center mb-3">
                            <div class="me-3" style="width: 32px; height: 32px; min-width: 32px; flex-shrink: 0;">
                                <div class="bg-{{ $index == 0 ? 'warning' : ($index == 1 ? 'secondary' : 'light') }} text-{{ $index <= 1 ? 'white' : 'dark' }} d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; border-radius: 50%; font-size: 14px;">
                                    {{ $index + 1 }}
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-0">{{ Str::limit($emp->nombre, 25) }}</h6>
                                <small class="text-muted">{{ $emp->ordenes_count }} órdenes</small>
                            </div>
                            <div class="progress" style="width: 60px; height: 6px;">
                                @php
                                    $maxOrdenes = $topEmpresas->first()->ordenes_count ?? 1;
                                    $porcentaje = $maxOrdenes > 0 ? ($emp->ordenes_count / $maxOrdenes) * 100 : 0;
                                @endphp
                                <div class="progress-bar bg-primary" style="width: {{ $porcentaje }}%"></div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-building fs-3 d-block mb-2"></i>
                            Sin datos de empresas
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Accesos rápidos -->
        <div class="col-xl-8 col-lg-7">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-lightning me-2"></i>Accesos Rápidos
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3 col-sm-6">
                            <a href="{{ route('ordenes.create') }}" class="text-decoration-none">
                                <div class="card bg-primary-transparent border-0 h-100 mb-0">
                                    <div class="card-body text-center py-4">
                                        <i class="bi bi-plus-circle text-primary fs-1 mb-2 d-block"></i>
                                        <h6 class="mb-0">Nueva Orden</h6>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <a href="{{ route('ordenes.index') }}" class="text-decoration-none">
                                <div class="card bg-success-transparent border-0 h-100 mb-0">
                                    <div class="card-body text-center py-4">
                                        <i class="bi bi-list-ul text-success fs-1 mb-2 d-block"></i>
                                        <h6 class="mb-0">Ver Órdenes</h6>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <a href="{{ route('admin.cuestionarios.index') }}" class="text-decoration-none">
                                <div class="card bg-info-transparent border-0 h-100 mb-0">
                                    <div class="card-body text-center py-4">
                                        <i class="bi bi-clipboard-data text-info fs-1 mb-2 d-block"></i>
                                        <h6 class="mb-0">Cuestionarios</h6>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <a href="{{ url('empresas') }}" class="text-decoration-none">
                                <div class="card bg-warning-transparent border-0 h-100 mb-0">
                                    <div class="card-body text-center py-4">
                                        <i class="bi bi-building text-warning fs-1 mb-2 d-block"></i>
                                        <h6 class="mb-0">Empresas</h6>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @endif
    {{-- FIN DASHBOARD ADMIN/REPRO --}}

    {{-- ========================================
         DASHBOARD PARA USUARIOS DE EMPRESA
    ======================================== --}}
    @if(Auth::user()->role_as == 1)

    <!-- Tarjetas de estadísticas para empresa -->
    <div class="row mb-4">
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
            <div class="card border-start border-4 border-primary">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted mb-1 small text-uppercase">Mis Órdenes</p>
                            <h3 class="mb-0 fw-bold text-primary">{{ $totalOrdenes ?? 0 }}</h3>
                            <small class="text-muted">
                                <i class="bi bi-calendar-month"></i> {{ $ordenesEsteMes ?? 0 }} este mes
                            </small>
                        </div>
                        <div class="avatar avatar-lg">
                            <div class="avatar-title bg-primary-transparent rounded-circle">
                                <i class="bi bi-file-earmark-text text-primary fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
            <div class="card border-start border-4 border-warning">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted mb-1 small text-uppercase">Pendientes</p>
                            <h3 class="mb-0 fw-bold text-warning">{{ $ordenesPendientes ?? 0 }}</h3>
                            <small class="text-muted">
                                <i class="bi bi-clock"></i> En proceso
                            </small>
                        </div>
                        <div class="avatar avatar-lg">
                            <div class="avatar-title bg-warning-transparent rounded-circle">
                                <i class="bi bi-hourglass-split text-warning fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
            <div class="card border-start border-4 border-success">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted mb-1 small text-uppercase">Evaluados</p>
                            <h3 class="mb-0 fw-bold text-success">{{ $totalEvaluados ?? 0 }}</h3>
                            <small class="text-muted">
                                <i class="bi bi-check-circle"></i> {{ $cuestionariosCompletados ?? 0 }} completados
                            </small>
                        </div>
                        <div class="avatar avatar-lg">
                            <div class="avatar-title bg-success-transparent rounded-circle">
                                <i class="bi bi-people text-success fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
            <div class="card border-start border-4 border-info">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted mb-1 small text-uppercase">Cuestionarios</p>
                            <h3 class="mb-0 fw-bold text-info">{{ $cuestionariosCompletados ?? 0 }}</h3>
                            <small class="text-muted">
                                <i class="bi bi-clipboard-check"></i> Completados
                            </small>
                        </div>
                        <div class="avatar avatar-lg">
                            <div class="avatar-title bg-info-transparent rounded-circle">
                                <i class="bi bi-clipboard-data text-info fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Accesos rápidos y últimas órdenes -->
    <div class="row mb-4">
        <div class="col-xl-4 col-lg-5">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-lightning me-2"></i>Acciones Rápidas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-3">
                        <a href="{{ route('ordenes.create') }}" class="btn btn-primary btn-lg">
                            <i class="bi bi-plus-circle me-2"></i>Nueva Orden de Evaluación
                        </a>
                        <a href="{{ route('empresa.ordenes.index') }}" class="btn btn-outline-primary">
                            <i class="bi bi-list-ul me-2"></i>Ver Mis Órdenes
                        </a>
                    </div>
                    {{-- C4: Botones WhatsApp sedes activas --}}
                    @if(!empty($sedesContacto) && $sedesContacto->isNotEmpty())
                    <hr class="my-3">
                    <p class="small text-muted mb-2"><i class="bi bi-headset me-1"></i>Contactar una sede:</p>
                    <div class="d-grid gap-2">
                        @foreach($sedesContacto as $sedeContacto)
                        <a href="https://wa.me/{{ preg_replace('/\D+/', '', $sedeContacto->whatsapp) }}"
                           target="_blank" rel="noopener noreferrer"
                           class="btn btn-success btn-sm">
                            <i class="bi bi-whatsapp me-1"></i>{{ $sedeContacto->nombre }}
                        </a>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-xl-8 col-lg-7">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-clock-history me-2"></i>Mis Últimas Órdenes
                    </h5>
                    <a href="{{ route('empresa.ordenes.index') }}" class="btn btn-sm btn-outline-primary">
                        Ver todas <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Código</th>
                                    <th>Evaluados</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $estadosLabels = [
                                        'orden_recibida' => ['label' => 'Orden Recibida', 'color' => 'secondary'],
                                        'en_proceso'     => ['label' => 'En Proceso',     'color' => 'primary'],
                                        'entregado'      => ['label' => 'Entregado',      'color' => 'success'],
                                        'cancelado'      => ['label' => 'Cancelado',      'color' => 'danger'],
                                    ];
                                @endphp
                                @forelse($ultimasOrdenes ?? [] as $orden)
                                    <tr style="cursor:pointer" onclick="window.location='{{ route('empresa.ordenes.show', $orden) }}'">
                                        <td>
                                            <a href="{{ route('empresa.ordenes.show', $orden) }}" class="fw-bold text-primary text-decoration-none" onclick="event.stopPropagation()">
                                                {{ $orden->codigo_orden }}
                                            </a>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $orden->evaluados->count() }}</span>
                                            @if($orden->evaluados->isNotEmpty())
                                                <div class="small text-muted mt-1">{{ $orden->evaluados->first()->nombre_completo }}</div>
                                                @if($orden->evaluados->count() > 1)
                                                    <div class="small text-muted">+{{ $orden->evaluados->count() - 1 }} más</div>
                                                @endif
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $estadoInfo = $estadosLabels[$orden->estado] ?? ['label' => $orden->estado, 'color' => 'secondary'];
                                            @endphp
                                            <span class="badge bg-{{ $estadoInfo['color'] }}">
                                                {{ $estadoInfo['label'] }}
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $orden->created_at->format('d/m/Y') }}</small>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">
                                            <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                            No tienes órdenes registradas
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @endif
    {{-- FIN DASHBOARD EMPRESA --}}

</div>
<!-- Content wrapper end -->

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        @if(Auth::user()->role_as >= 2 && isset($ordenesPorMes))
        // Gráfico de órdenes por mes
        const ctx = document.getElementById('ordenesChart');
        if (ctx) {
            new Chart(ctx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: {!! json_encode(array_column($ordenesPorMes, 'mes')) !!},
                    datasets: [{
                        label: 'Órdenes',
                        data: {!! json_encode(array_column($ordenesPorMes, 'total')) !!},
                        backgroundColor: 'rgba(0, 5, 85, 0.7)',
                        borderColor: '#000555',
                        borderWidth: 1,
                        borderRadius: 4,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 500
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1,
                                precision: 0
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }
        @endif
    });
</script>
@endpush
