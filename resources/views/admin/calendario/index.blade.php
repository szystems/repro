@extends('layouts.admin')
@section('content')

<div class="content-wrapper-scroll">

    <div class="main-header d-flex align-items-center justify-content-between position-relative">
        <div class="d-flex align-items-center justify-content-center">
            <div class="page-icon">
                <i class="bi bi-calendar3"></i>
            </div>
            <div class="page-title">
                <h5>Calendario de Programación</h5>
            </div>
        </div>
        <div class="d-flex align-items-end d-none d-sm-block">
            <h6 class="float-end text-light" id="reloj"></h6>
        </div>
    </div>

    <div class="content-wrapper">

        {{-- Mensajes de sesión --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Filtros --}}
        <div class="row gx-3 mb-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title"><i class="bi bi-funnel"></i> Filtros</div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('calendario.index') }}" method="GET" class="row g-2 align-items-end">
                            <input type="hidden" name="mes" value="{{ $mes }}">
                            <input type="hidden" name="anio" value="{{ $anio }}">
                            <div class="col-md-2">
                                <label class="form-label">Sede</label>
                                <select name="sede_id" class="form-select form-select-sm">
                                    <option value="">Todas las sedes</option>
                                    @foreach($sedes as $sede)
                                        <option value="{{ $sede->id }}" {{ $sedeId == $sede->id ? 'selected' : '' }}>{{ $sede->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Encargado</label>
                                <select name="encargado_id" class="form-select form-select-sm">
                                    <option value="">Todos</option>
                                    @foreach($poligrafistas as $pol)
                                        <option value="{{ $pol->id }}" {{ ($encargadoId ?? null) == $pol->id ? 'selected' : '' }}>{{ $pol->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Tipo de servicio</label>
                                <select name="tipo_servicio" class="form-select form-select-sm">
                                    <option value="">Todos</option>
                                    <option value="poligrafo" {{ $tipoServicio == 'poligrafo' ? 'selected' : '' }}>Polígrafo</option>
                                    <option value="vsa" {{ $tipoServicio == 'vsa' ? 'selected' : '' }}>VSA</option>
                                    <option value="socioeconomico" {{ $tipoServicio == 'socioeconomico' ? 'selected' : '' }}>Socioeconómico</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Empresa</label>
                                <select name="empresa_id" class="form-select form-select-sm">
                                    <option value="">Todas</option>
                                    @foreach($empresas as $empresa)
                                        <option value="{{ $empresa->id }}" {{ ($empresaId ?? null) == $empresa->id ? 'selected' : '' }}>{{ $empresa->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Desde</label>
                                <input type="date" name="fecha_desde" class="form-control form-control-sm" value="{{ $fechaDesde ?? '' }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Hasta</label>
                                <input type="date" name="fecha_hasta" class="form-control form-control-sm" value="{{ $fechaHasta ?? '' }}">
                            </div>
                            <div class="col-12 d-flex flex-wrap gap-2">
                                <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-search"></i> Filtrar</button>
                                <a href="{{ route('calendario.index', ['mes' => $mes, 'anio' => $anio]) }}" class="btn btn-sm btn-outline-secondary">Limpiar</a>
                                @if(Auth::user() && \App\Support\ExportacionesSupport::puedeExportarInformes(Auth::user()))
                                    <button type="submit" formaction="{{ route('calendario.excel') }}" class="btn btn-sm btn-success">
                                        <i class="bi bi-file-earmark-excel"></i> Excel
                                    </button>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Navegación Mes --}}
        @php
            $mesAnterior = $fecha->copy()->subMonth();
            $mesSiguiente = $fecha->copy()->addMonth();
            $filtrosQuery = http_build_query(array_filter([
                'sede_id' => $sedeId,
                'poligrafista_id' => $poligrafistaId,
                'encargado_id' => $encargadoId ?? null,
                'tipo_servicio' => $tipoServicio,
                'empresa_id' => $empresaId ?? null,
            ]));
        @endphp

        <div class="row gx-3 mb-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <a href="{{ route('calendario.index', ['mes' => $mesAnterior->month, 'anio' => $mesAnterior->year]) }}{{ $filtrosQuery ? '&'.$filtrosQuery : '' }}"
                           class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-chevron-left"></i> {{ $mesAnterior->translatedFormat('F') }}
                        </a>
                        <h5 class="mb-0 text-capitalize">
                            <i class="bi bi-calendar3"></i>
                            {{ $fecha->translatedFormat('F Y') }}
                        </h5>
                        <a href="{{ route('calendario.index', ['mes' => $mesSiguiente->month, 'anio' => $mesSiguiente->year]) }}{{ $filtrosQuery ? '&'.$filtrosQuery : '' }}"
                           class="btn btn-sm btn-outline-secondary">
                            {{ $mesSiguiente->translatedFormat('F') }} <i class="bi bi-chevron-right"></i>
                        </a>
                    </div>
                    <div class="card-body p-0">
                        {{-- Leyenda --}}
                        <div class="d-flex gap-3 p-2 border-bottom bg-light">
                            <small><span class="badge bg-primary">&nbsp;</span> Polígrafo</small>
                            <small><span class="badge bg-info">&nbsp;</span> VSA</small>
                            <small><span class="badge bg-warning text-dark">&nbsp;</span> Socioeconómico</small>
                        </div>

                        {{-- Calendario --}}
                        <table class="table table-bordered mb-0" style="table-layout: fixed;">
                            <thead class="table-light">
                                <tr class="text-center">
                                    <th>Lunes</th>
                                    <th>Martes</th>
                                    <th>Miércoles</th>
                                    <th>Jueves</th>
                                    <th>Viernes</th>
                                    <th class="text-muted">Sábado</th>
                                    <th class="text-muted">Domingo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    // Construir semanas (Lunes=1 ... Domingo=7)
                                    $primerDia = $inicioMes->copy();
                                    // Retroceder al lunes de la semana donde empieza el mes
                                    while ($primerDia->dayOfWeekIso != 1) {
                                        $primerDia->subDay();
                                    }
                                    $cursor = $primerDia->copy();
                                @endphp

                                @while($cursor <= $finMes || $cursor->dayOfWeekIso != 1)
                                <tr>
                                    @for($d = 0; $d < 7; $d++)
                                        @php
                                            $diaStr = $cursor->format('Y-m-d');
                                            $esMesActual = $cursor->month == $fecha->month;
                                            $esHoy = $cursor->isToday();
                                            $info = $citasPorDia[$diaStr] ?? null;

                                            $filtrosDia = http_build_query(array_filter([
                                                'sede_id' => $sedeId,
                                                'poligrafista_id' => $poligrafistaId,
                                                'encargado_id' => $encargadoId ?? null,
                                                'tipo_servicio' => $tipoServicio,
                                                'empresa_id' => $empresaId ?? null,
                                            ]));
                                        @endphp
                                        <td class="p-1 {{ !$esMesActual ? 'bg-light text-muted' : '' }} {{ $esHoy ? 'border-primary border-2' : '' }}"
                                            style="height: 90px; vertical-align: top; cursor: pointer;"
                                            onclick="window.location='{{ route('calendario.dia', ['fecha' => $diaStr]) }}{{ $filtrosDia ? '?'.$filtrosDia : '' }}'">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <span class="fw-bold {{ $esHoy ? 'text-primary' : '' }}">{{ $cursor->day }}</span>
                                                @if($info && $info['total'] > 0)
                                                    <span class="badge bg-dark rounded-pill">{{ $info['total'] }}</span>
                                                @endif
                                            </div>
                                            @if($info)
                                                <div class="mt-1">
                                                    @if($info['poligrafo'] > 0)
                                                        <small class="d-block"><span class="badge bg-primary badge-sm">{{ $info['poligrafo'] }}</span> <span class="d-none d-lg-inline">Pol.</span></small>
                                                    @endif
                                                    @if($info['vsa'] > 0)
                                                        <small class="d-block"><span class="badge bg-info badge-sm">{{ $info['vsa'] }}</span> <span class="d-none d-lg-inline">VSA</span></small>
                                                    @endif
                                                    @if($info['socioeconomico'] > 0)
                                                        <small class="d-block"><span class="badge bg-warning text-dark badge-sm">{{ $info['socioeconomico'] }}</span> <span class="d-none d-lg-inline">Socio.</span></small>
                                                    @endif
                                                </div>
                                            @endif
                                        </td>
                                        @php $cursor->addDay(); @endphp
                                    @endfor
                                </tr>
                                @endwhile
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- Historial de candidatos del periodo (incluye procesos en curso) --}}
<div class="content-wrapper pt-0">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0 d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <span>
                            <i class="bi bi-clock-history me-2"></i>Historial de candidatos —
                            @if(!empty($fechaDesde) || !empty($fechaHasta))
                                {{ \Carbon\Carbon::parse($inicioHist)->format('d/m/Y') }} – {{ \Carbon\Carbon::parse($finHist)->format('d/m/Y') }}
                            @else
                                {{ ucfirst($fecha->translatedFormat('F Y')) }}
                            @endif
                            <span class="badge bg-secondary ms-2">{{ $historial->count() }}</span>
                        </span>
                        @if(Auth::user() && \App\Support\ExportacionesSupport::puedeExportarInformes(Auth::user()))
                            <a href="{{ route('calendario.excel', request()->query()) }}" class="btn btn-sm btn-success">
                                <i class="bi bi-file-earmark-excel"></i> Excel
                            </a>
                        @endif
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Candidato</th>
                                    <th>Empresa</th>
                                    <th>Sede</th>
                                    <th>Programó</th>
                                    <th>Encargado</th>
                                    <th>Tipo</th>
                                    <th>Fecha</th>
                                    <th>Estado de Evaluación</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($historial as $h)
                                @php
                                    $estadoColores = [
                                        'completado'  => 'success',
                                        'informe_final_enviado' => 'dark',
                                        'inasistencia'=> 'warning',
                                        'desistio'    => 'secondary',
                                        'cancelado'   => 'danger',
                                    ];
                                    $estadoColor = $estadoColores[$h->estado_evaluacion] ?? 'secondary';
                                @endphp
                                <tr>
                                    <td>{{ $h->nombre }} {{ $h->apellidos }}</td>
                                    <td class="small">{{ $h->orden->empresa->nombre ?? '—' }}</td>
                                    <td class="small">{{ $h->sede->nombre ?? '—' }}</td>
                                    <td class="small">{{ $h->poligrafo ? $h->poligrafo->name : '—' }}</td>
                                    <td class="small">{{ $h->responsable ? $h->responsable->name : 'Sin asignar' }}</td>
                                    <td><span class="badge bg-primary">{{ ucfirst($h->tipo_servicio ?? '—') }}</span></td>
                                    <td class="small text-muted">{{ $h->fecha_programada ? \Carbon\Carbon::parse($h->fecha_programada)->format('d/m/Y H:i') : '—' }}</td>
                                    <td><span class="badge bg-{{ $estadoColor }}">{{ ucfirst(str_replace('_', ' ', $h->estado_evaluacion)) }}</span></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-muted text-center py-3">No hay candidatos con los filtros seleccionados.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
