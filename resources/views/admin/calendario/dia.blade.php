@extends('layouts.admin')
@section('content')

<div class="content-wrapper-scroll">

    <div class="main-header d-flex align-items-center justify-content-between position-relative">
        <div class="d-flex align-items-center justify-content-center">
            <div class="page-icon">
                <i class="bi bi-calendar-day"></i>
            </div>
            <div class="page-title">
                <h5>Agenda del {{ $fechaCarbon->translatedFormat('l j \d\e F Y') }}</h5>
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
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                @foreach($errors->all() as $error)
                    {{ $error }}<br>
                @endforeach
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Navegación día + botón volver --}}
        @php
            $diaAnterior = $fechaCarbon->copy()->subDay()->format('Y-m-d');
            $diaSiguiente = $fechaCarbon->copy()->addDay()->format('Y-m-d');
            $filtrosQuery = http_build_query(array_filter([
                'sede_id' => $sedeId,
                'poligrafista_id' => $poligrafistaId,
                'tipo_servicio' => $tipoServicio,
            ]));
        @endphp

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <a href="{{ route('calendario.index', ['mes' => $fechaCarbon->month, 'anio' => $fechaCarbon->year]) }}{{ $filtrosQuery ? '&'.$filtrosQuery : '' }}"
                   class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Volver al mes
                </a>
                <a href="{{ route('calendario.dia', ['fecha' => $diaAnterior]) }}{{ $filtrosQuery ? '?'.$filtrosQuery : '' }}"
                   class="btn btn-sm btn-outline-secondary ms-1">
                    <i class="bi bi-chevron-left"></i>
                </a>
                <a href="{{ route('calendario.dia', ['fecha' => $diaSiguiente]) }}{{ $filtrosQuery ? '?'.$filtrosQuery : '' }}"
                   class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </div>
            <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalProgramar"
                    onclick="prepararModalDesdeCalendario()">
                <i class="bi bi-plus-circle"></i> Programar cita
            </button>
        </div>

        {{-- Filtros --}}
        <div class="row gx-3 mb-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-header py-2">
                        <div class="card-title mb-0"><i class="bi bi-funnel"></i> Filtros</div>
                    </div>
                    <div class="card-body py-2">
                        <form action="{{ route('calendario.dia', ['fecha' => $fecha]) }}" method="GET" class="row g-2 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label form-label-sm mb-0">Sede</label>
                                <select name="sede_id" class="form-select form-select-sm">
                                    <option value="">Todas</option>
                                    @foreach($sedes as $sede)
                                        <option value="{{ $sede->id }}" {{ $sedeId == $sede->id ? 'selected' : '' }}>{{ $sede->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label form-label-sm mb-0">Poligrafista</label>
                                <select name="poligrafista_id" class="form-select form-select-sm">
                                    <option value="">Todos</option>
                                    @foreach($poligrafistas as $pol)
                                        <option value="{{ $pol->id }}" {{ $poligrafistaId == $pol->id ? 'selected' : '' }}>{{ $pol->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label form-label-sm mb-0">Tipo</label>
                                <select name="tipo_servicio" class="form-select form-select-sm">
                                    <option value="">Todos</option>
                                    <option value="poligrafo" {{ $tipoServicio == 'poligrafo' ? 'selected' : '' }}>Polígrafo</option>
                                    <option value="vsa" {{ $tipoServicio == 'vsa' ? 'selected' : '' }}>VSA</option>
                                    <option value="socioeconomico" {{ $tipoServicio == 'socioeconomico' ? 'selected' : '' }}>Socioeconómico</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-search"></i> Filtrar</button>
                                <a href="{{ route('calendario.dia', ['fecha' => $fecha]) }}" class="btn btn-sm btn-outline-secondary">Limpiar</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Leyenda --}}
        <div class="d-flex gap-3 mb-2">
            <small><span class="badge bg-primary">&nbsp;</span> Polígrafo</small>
            <small><span class="badge bg-info">&nbsp;</span> VSA</small>
            <small><span class="badge bg-warning text-dark">&nbsp;</span> Socioeconómico</small>
        </div>

        {{-- Agenda por slots --}}
        <div class="row gx-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-body p-0">
                        <table class="table table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 100px;" class="text-center">Hora</th>
                                    <th>Citas programadas</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($slots as $slot)
                                @php
                                    $slotInicio = $fecha . ' ' . $slot['hora'] . ':00';
                                    $slotFin = \Carbon\Carbon::parse($slotInicio)->addMinutes(30)->format('Y-m-d H:i:s');
                                    // Citas que cubren este slot (su inicio < fin del slot Y su fin > inicio del slot)
                                    $citasEnSlot = $citas->filter(function ($c) use ($slotInicio, $slotFin) {
                                        $cInicio = \Carbon\Carbon::parse($c->fecha_programada);
                                        $cFin = $c->fecha_hora_fin ? \Carbon\Carbon::parse($c->fecha_hora_fin) : $cInicio->copy()->addHours(2);
                                        return $cInicio->format('Y-m-d H:i:s') < $slotFin && $cFin->format('Y-m-d H:i:s') > $slotInicio;
                                    });
                                    $tieneSlotPasado = \Carbon\Carbon::parse($slotInicio)->isPast();
                                @endphp
                                <tr class="{{ $tieneSlotPasado ? 'bg-light' : '' }}">
                                    <td class="text-center align-middle fw-bold {{ $tieneSlotPasado ? 'text-muted' : '' }}">
                                        {{ $slot['label'] }}
                                    </td>
                                    <td class="p-2">
                                        @if($citasEnSlot->count() > 0)
                                            @foreach($citasEnSlot as $cita)
                                            @php
                                                $colorClase = match($cita->tipo_servicio) {
                                                    'poligrafo' => 'bg-primary',
                                                    'vsa' => 'bg-info',
                                                    'socioeconomico' => 'bg-warning text-dark',
                                                    default => 'bg-secondary',
                                                };
                                                $horaInicioCita = \Carbon\Carbon::parse($cita->fecha_programada)->format('h:i A');
                                                $horaFinCita = $cita->fecha_hora_fin ? \Carbon\Carbon::parse($cita->fecha_hora_fin)->format('h:i A') : '--';
                                                // Solo mostrar detalle si este es el slot de inicio
                                                $esSlotInicio = \Carbon\Carbon::parse($cita->fecha_programada)->format('H:i') == $slot['hora'];
                                            @endphp
                                                @if($esSlotInicio)
                                                @php
                                                    $esInasistencia = $cita->estado_evaluacion === 'inasistencia';
                                                    $cardExtraClass = $esInasistencia ? 'opacity-75' : '';
                                                @endphp
                                                <div class="d-flex align-items-center justify-content-between mb-1 p-2 rounded {{ $colorClase }} bg-opacity-10 border-start border-4 {{ $esInasistencia ? 'border-danger' : str_replace('bg-', 'border-', explode(' ', $colorClase)[0]) }} {{ $cardExtraClass }}">
                                                    <div>
                                                        <strong>{{ $cita->nombre }} {{ $cita->apellidos }}</strong>
                                                        <small class="text-muted ms-2">{{ $horaInicioCita }} - {{ $horaFinCita }}</small>
                                                        <br>
                                                        <small>
                                                            <span class="badge {{ $colorClase }} badge-sm">{{ $cita->tipo_servicio_texto }}</span>
                                                            <span class="badge bg-{{ $cita->estado_evaluacion_color }} badge-sm" title="Estado evaluación">{{ $cita->estado_evaluacion_texto }}</span>
                                                            @if($cita->sede)
                                                                <i class="bi bi-geo-alt"></i> {{ $cita->sede->nombre }}
                                                            @endif
                                                            @if($cita->modalidad)
                                                                <span class="badge bg-{{ $cita->modalidad == 'presencial' ? 'info' : 'purple' }} badge-sm">{{ ucfirst($cita->modalidad) }}</span>
                                                            @endif
                                                            @if($cita->poligrafo)
                                                                <i class="bi bi-person ms-1"></i> {{ $cita->poligrafo->name }}
                                                            @endif
                                                            @if($cita->orden && $cita->orden->empresa)
                                                                <i class="bi bi-building ms-1"></i> {{ $cita->orden->empresa->nombre }}
                                                            @endif
                                                        </small>
                                                        @php
                                                            $transEval = \App\Models\EvaluadoOrden::transicionesEvaluacion()[$cita->estado_evaluacion] ?? [];
                                                            $nombresEval = \App\Models\EvaluadoOrden::estadosEvaluacionDisponibles();
                                                        @endphp
                                                        @if(count($transEval) > 0)
                                                        <form action="{{ route('evaluados.cambiar-estado', $cita->id) }}" method="POST" class="d-inline-flex align-items-center gap-1 mt-1">
                                                            @csrf
                                                            @method('PATCH')
                                                            <input type="hidden" name="tipo_estado" value="evaluacion">
                                                            <select name="nuevo_estado" class="form-select form-select-sm py-0" style="max-width: 155px; font-size: 0.75rem;" required>
                                                                <option value="">Estado...</option>
                                                                @foreach($transEval as $est)
                                                                    <option value="{{ $est }}">{{ $nombresEval[$est] ?? ucfirst($est) }}</option>
                                                                @endforeach
                                                            </select>
                                                            <button type="submit" class="btn btn-outline-primary btn-sm py-0" title="Cambiar estado">
                                                                <i class="bi bi-arrow-right-circle"></i>
                                                            </button>
                                                        </form>
                                                        @endif
                                                    </div>
                                                    <div class="btn-group btn-group-sm">
                                                        <button class="btn btn-outline-primary btn-sm" title="Reprogramar"
                                                                data-bs-toggle="modal" data-bs-target="#modalProgramar"
                                                                onclick="prepararModalReprogramar({{ $cita->id }}, '{{ $cita->fecha_programada ? \Carbon\Carbon::parse($cita->fecha_programada)->format('Y-m-d') : '' }}', '{{ $cita->fecha_programada ? \Carbon\Carbon::parse($cita->fecha_programada)->format('H:i') : '' }}', '{{ $cita->fecha_hora_fin ? \Carbon\Carbon::parse($cita->fecha_hora_fin)->format('H:i') : '' }}', {{ $cita->poligrafista_id ?? 'null' }}, {{ $cita->sede_id ?? 'null' }}, '{{ $cita->modalidad ?? '' }}', {{ $cita->responsable_id ?? 'null' }})">
                                                            <i class="bi bi-pencil"></i>
                                                        </button>
                                                        <form action="{{ route('calendario.cancelar', $cita->id) }}" method="POST"
                                                              onsubmit="return confirm('¿Cancelar esta cita?');" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-outline-danger btn-sm" title="Cancelar cita">
                                                                <i class="bi bi-x-circle"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                                @else
                                                {{-- Slot de continuación: indicador sutil --}}
                                                <div class="p-1 rounded {{ $colorClase }} bg-opacity-10 border-start border-4 {{ str_replace('bg-', 'border-', explode(' ', $colorClase)[0]) }} mb-1">
                                                    <small class="text-muted"><i class="bi bi-arrow-up"></i> {{ $cita->nombre }} {{ $cita->apellidos }} (cont.)</small>
                                                </div>
                                                @endif
                                            @endforeach
                                        @endif
                                        {{-- Botón Agendar: siempre visible en slots futuros (el anti-traslape se valida en backend) --}}
                                        @if(!$tieneSlotPasado)
                                        <button class="btn btn-sm btn-outline-success {{ $citasEnSlot->count() > 0 ? 'mt-1' : 'w-100' }} py-0"
                                                data-bs-toggle="modal" data-bs-target="#modalProgramar"
                                                onclick="prepararModalDesdeSlot('{{ $slot['hora'] }}')">
                                            <small><i class="bi bi-plus"></i> Agendar</small>
                                        </button>
                                        @elseif($citasEnSlot->count() === 0)
                                        <small class="text-muted">—</small>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Registro histórico: reprogramados desde este día --}}
        @if($citasHistoricas->isNotEmpty())
        <div class="row gx-3 mt-3">
            <div class="col-12">
                <div class="card border-warning">
                    <div class="card-header py-2 bg-warning bg-opacity-10">
                        <div class="card-title mb-0 text-warning">
                            <i class="bi bi-clock-history"></i>
                            Reprogramados desde este día ({{ $citasHistoricas->count() }})
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Candidato</th>
                                    <th>Hora original</th>
                                    <th>Nueva fecha</th>
                                    <th>Empresa</th>
                                    <th>Tipo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($citasHistoricas as $cita)
                                <tr>
                                    <td>
                                        <strong>{{ $cita->nombre }} {{ $cita->apellidos }}</strong><br>
                                        <small class="text-muted">{{ $cita->dpi }}</small>
                                    </td>
                                    <td>
                                        <small>{{ $cita->fecha_programada_original ? \Carbon\Carbon::parse($cita->fecha_programada_original)->format('h:i A') : '—' }}</small>
                                    </td>
                                    <td>
                                        <small>{{ $cita->fecha_programada ? \Carbon\Carbon::parse($cita->fecha_programada)->translatedFormat('d M Y h:i A') : '—' }}</small>
                                    </td>
                                    <td>
                                        <small>{{ $cita->orden && $cita->orden->empresa ? $cita->orden->empresa->nombre : '—' }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary badge-sm">{{ $cita->tipo_servicio_texto }}</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif

    </div>
</div>

{{-- Modal Programar / Reprogramar Cita --}}
<div class="modal fade" id="modalProgramar" tabindex="-1" aria-labelledby="modalProgramarLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formProgramar" method="POST" action="{{ route('calendario.programar') }}">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalProgramarLabel">
                        <i class="bi bi-calendar-plus"></i> Programar Cita
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    {{-- Evaluado --}}
                    <div class="mb-3" id="divEvaluado">
                        <label class="form-label fw-bold">Evaluado</label>
                        <select name="evaluado_orden_id" id="evaluado_orden_id" class="form-select" required>
                            <option value="">Seleccionar evaluado...</option>
                            @foreach($evaluadosPendientes as $ev)
                                <option value="{{ $ev->id }}">
                                    {{ $ev->nombre }} {{ $ev->apellidos }} — DPI: {{ $ev->dpi }}
                                    @if($ev->orden && $ev->orden->empresa)
                                        ({{ $ev->orden->empresa->nombre }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Solo evaluados pendientes de programar.</small>
                    </div>

                    {{-- Fecha --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold">Fecha</label>
                        <input type="date" name="fecha" id="modalFecha" class="form-control"
                               value="{{ $fecha }}" required min="{{ now()->format('Y-m-d') }}">
                    </div>

                    {{-- Hora inicio / fin --}}
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold">Hora inicio</label>
                            <select name="hora_inicio" id="modalHoraInicio" class="form-select" required>
                                @for($h = 8; $h < 18; $h++)
                                    @for($m = 0; $m < 60; $m += 30)
                                        @php $hora = sprintf('%02d:%02d', $h, $m); @endphp
                                        <option value="{{ $hora }}">{{ \Carbon\Carbon::parse($hora)->format('h:i A') }}</option>
                                    @endfor
                                @endfor
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold">Hora fin</label>
                            <select name="hora_fin" id="modalHoraFin" class="form-select" required>
                                @for($h = 8; $h <= 18; $h++)
                                    @for($m = 0; $m < 60; $m += 30)
                                        @php
                                            $hora = sprintf('%02d:%02d', $h, $m);
                                            if ($h == 18 && $m > 0) continue;
                                        @endphp
                                        <option value="{{ $hora }}">{{ \Carbon\Carbon::parse($hora)->format('h:i A') }}</option>
                                    @endfor
                                @endfor
                            </select>
                        </div>
                    </div>

                    {{-- Sede --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold">Sede</label>
                        <select name="sede_id" id="modalSedeId" class="form-select" required>
                            <option value="">Seleccionar sede...</option>
                            @foreach($sedes as $sede)
                                <option value="{{ $sede->id }}">{{ $sede->nombre }} (Cap: {{ $sede->capacidad ?? 'N/A' }})</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Modalidad --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold">Modalidad</label>
                        <select name="modalidad" id="modalModalidad" class="form-select">
                            <option value="">Sin definir</option>
                            <option value="presencial">Presencial</option>
                            <option value="virtual">Virtual</option>
                        </select>
                        <small class="text-muted" id="modalModalidadHint" style="display:none">
                            <i class="bi bi-info-circle"></i> Modalidad cargada del evaluado. Puede cambiarse si es necesario.
                        </small>
                    </div>

                    {{-- Poligrafista --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold">Poligrafista / Evaluador</label>
                        <select name="poligrafista_id" id="modalPoligrafistaId" class="form-select" required>
                            <option value="">Seleccionar evaluador...</option>
                            @foreach($poligrafistas as $pol)
                                <option value="{{ $pol->id }}">{{ $pol->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Responsable --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold">Responsable del Proceso</label>
                        <select name="responsable_id" id="modalResponsableId" class="form-select">
                            <option value="">Sin asignar</option>
                            @foreach($poligrafistas as $pol)
                                <option value="{{ $pol->id }}">{{ $pol->name }} {{ $pol->cargo ? '('.$pol->cargo.')' : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success" id="btnSubmitProgramar">
                        <i class="bi bi-check-circle"></i> Programar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Mapa de modalidad por evaluado (id → modalidad) para precargar al seleccionar
    const evaluadoModalidadMap = {
        @foreach($evaluadosPendientes as $ev)
        {{ $ev->id }}: '{{ $ev->modalidad ?? '' }}',
        @endforeach
    };

    // Al cambiar el evaluado seleccionado, precargar su modalidad guardada
    document.addEventListener('DOMContentLoaded', function () {
        const evalSelect = document.getElementById('evaluado_orden_id');
        const modalidadSelect = document.getElementById('modalModalidad');
        const modalidadHint = document.getElementById('modalModalidadHint');

        if (evalSelect) {
            evalSelect.addEventListener('change', function () {
                const evaluadoId = parseInt(this.value);
                if (evaluadoId && evaluadoModalidadMap[evaluadoId] !== undefined) {
                    const mod = evaluadoModalidadMap[evaluadoId];
                    modalidadSelect.value = mod || '';
                    // Mostrar hint solo si ya tenía modalidad definida
                    if (mod) {
                        modalidadHint.style.display = '';
                    } else {
                        modalidadHint.style.display = 'none';
                    }
                } else {
                    modalidadSelect.value = '';
                    modalidadHint.style.display = 'none';
                }
            });
        }
    });

    /**
     * Preparar modal desde el botón general "Programar cita"
     */
    function prepararModalDesdeCalendario() {
        document.getElementById('formProgramar').action = '{{ route("calendario.programar") }}';
        document.getElementById('formMethod').value = 'POST';
        document.getElementById('modalProgramarLabel').innerHTML = '<i class="bi bi-calendar-plus"></i> Programar Cita';
        document.getElementById('btnSubmitProgramar').innerHTML = '<i class="bi bi-check-circle"></i> Programar';
        document.getElementById('divEvaluado').style.display = 'block';
        document.getElementById('evaluado_orden_id').required = true;
        document.getElementById('modalFecha').value = '{{ $fecha }}';
        document.getElementById('modalHoraInicio').value = '08:00';
        document.getElementById('modalHoraFin').value = '10:00';
        document.getElementById('modalSedeId').value = '{{ $sedeId ?? "" }}';
        document.getElementById('modalPoligrafistaId').value = '{{ $poligrafistaId ?? "" }}';
        document.getElementById('modalModalidad').value = '';
        document.getElementById('evaluado_orden_id').value = '';
        document.getElementById('modalModalidadHint').style.display = 'none';
    }

    /**
     * Preparar modal desde un slot vacío
     */
    function prepararModalDesdeSlot(hora) {
        prepararModalDesdeCalendario();
        document.getElementById('modalHoraInicio').value = hora;
        // Calcular hora fin (+2h por defecto)
        var parts = hora.split(':');
        var h = parseInt(parts[0]) + 2;
        if (h > 18) h = 18;
        var fin = (h < 10 ? '0' : '') + h + ':' + parts[1];
        document.getElementById('modalHoraFin').value = fin;
    }

    /**
     * Preparar modal para reprogramar una cita existente
     */
    function prepararModalReprogramar(evaluadoId, fecha, horaInicio, horaFin, poligrafistaId, sedeId, modalidad, responsableId) {
        document.getElementById('formProgramar').action = '/calendario/evaluados/' + evaluadoId + '/reprogramar';
        document.getElementById('formMethod').value = 'PATCH';
        document.getElementById('modalProgramarLabel').innerHTML = '<i class="bi bi-pencil"></i> Reprogramar Cita';
        document.getElementById('btnSubmitProgramar').innerHTML = '<i class="bi bi-check-circle"></i> Reprogramar';
        // Ocultar selector de evaluado (ya está seleccionado)
        document.getElementById('divEvaluado').style.display = 'none';
        document.getElementById('evaluado_orden_id').required = false;
        document.getElementById('evaluado_orden_id').value = evaluadoId;
        document.getElementById('modalFecha').value = fecha;
        document.getElementById('modalHoraInicio').value = horaInicio;
        document.getElementById('modalHoraFin').value = horaFin;
        if (poligrafistaId) document.getElementById('modalPoligrafistaId').value = poligrafistaId;
        if (sedeId) document.getElementById('modalSedeId').value = sedeId;
        document.getElementById('modalModalidad').value = modalidad || '';
        document.getElementById('modalResponsableId').value = responsableId || '';
    }
</script>

@endsection
