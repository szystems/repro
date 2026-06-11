@extends('layouts.cuestionario')

@section('title', 'Estado de tu proceso - REPRO')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7 col-md-9">

        {{-- Encabezado --}}
        <div class="form-card">
            <div class="form-header" style="background: linear-gradient(135deg, #000555 0%, #1a1a6b 100%);">
                <i class="fas fa-clipboard-list fa-2x mb-2"></i>
                <h2 class="mb-1" style="font-size:1.4rem;">Estado de tu proceso</h2>
                <p class="mb-0" style="font-size:0.9rem; opacity:0.85;">
                    {{ $evaluado->nombre }} {{ $evaluado->apellidos }}
                </p>
            </div>

            <div class="form-content">

                @if($cancelado)
                <div class="alert alert-danger d-flex align-items-center gap-2 mb-4">
                    <i class="fas fa-times-circle fa-lg"></i>
                    <div>
                        <strong>Proceso detenido</strong><br>
                        <small>Tu proceso ha sido cancelado o está detenido. Contacta a la empresa solicitante o a REPRO para más información.</small>
                    </div>
                </div>
                @endif

                {{-- Timeline de 4 pasos --}}
                <div class="repro-timeline">

                    {{-- Paso 1: Formulario --}}
                    @php
                        $p1Done  = $evaluado->estado_formulario === 'formulario_completado_y_recibido';
                        $p1Active = !$p1Done && $pasoActivo === 1;
                    @endphp
                    <div class="timeline-step {{ $p1Done ? 'done' : ($p1Active ? 'active' : 'pending') }}">
                        <div class="timeline-icon">
                            @if($p1Done)
                                <i class="fas fa-check"></i>
                            @elseif($p1Active)
                                <i class="fas fa-pen-alt"></i>
                            @else
                                <span>1</span>
                            @endif
                        </div>
                        <div class="timeline-body">
                            <div class="timeline-title">Formulario</div>
                            @if($p1Done)
                                <div class="timeline-desc text-success"><i class="fas fa-check-circle me-1"></i>Recibido y registrado correctamente.</div>
                                @if($evaluado->completado_at)
                                    <div class="timeline-meta">{{ \Carbon\Carbon::parse($evaluado->completado_at)->format('d/m/Y') }}</div>
                                @endif
                            @elseif($p1Active)
                                <div class="timeline-desc">Debes completar el formulario para continuar el proceso.</div>
                                <a href="{{ route('cuestionario.mostrar', $token) }}" class="btn btn-sm btn-primary mt-2">
                                    <i class="fas fa-pen-alt me-1"></i> Completar formulario
                                </a>
                            @else
                                <div class="timeline-desc text-muted">Pendiente</div>
                            @endif
                        </div>
                    </div>

                    {{-- Paso 2: Evaluación programada --}}
                    @php
                        $p2Done   = in_array($evaluado->estado_programacion, ['proceso_realizado']);
                        $p2Active = !$p2Done && in_array($evaluado->estado_programacion, ['programado', 'reprogramado']);
                    @endphp
                    <div class="timeline-step {{ $p2Done ? 'done' : ($p2Active ? 'active' : 'pending') }}">
                        <div class="timeline-icon">
                            @if($p2Done)
                                <i class="fas fa-check"></i>
                            @elseif($p2Active)
                                <i class="fas fa-calendar-check"></i>
                            @else
                                <span>2</span>
                            @endif
                        </div>
                        <div class="timeline-body">
                            <div class="timeline-title">Evaluación</div>
                            @if($p2Done)
                                <div class="timeline-desc text-success"><i class="fas fa-check-circle me-1"></i>Evaluación realizada.</div>
                            @elseif($p2Active)
                                <div class="timeline-desc">
                                    Tu cita ha sido agendada.
                                    @if($evaluado->fecha_programada)
                                        <strong>{{ \Carbon\Carbon::parse($evaluado->fecha_programada)->translatedFormat('l d \d\e F \d\e Y') }}</strong>
                                        a las {{ \Carbon\Carbon::parse($evaluado->fecha_programada)->format('h:i A') }}.
                                        @if($evaluado->modalidad)
                                            <br><small class="text-muted">Modalidad: {{ ucfirst($evaluado->modalidad) }}</small>
                                        @endif
                                    @endif
                                </div>
                            @elseif($p1Done)
                                <div class="timeline-desc text-muted">Próximamente te contactaremos para agendar tu cita.</div>
                            @else
                                <div class="timeline-desc text-muted">Pendiente</div>
                            @endif
                        </div>
                    </div>

                    {{-- Paso 3: En revisión / resultado preliminar --}}
                    @php
                        $p3States = ['en_revision', 'resultado_preliminar'];
                        $p3Done   = $evaluado->estado_evaluacion === 'informe_final_enviado';
                        $p3Active = !$p3Done && in_array($evaluado->estado_evaluacion, $p3States);
                    @endphp
                    <div class="timeline-step {{ $p3Done ? 'done' : ($p3Active ? 'active' : 'pending') }}">
                        <div class="timeline-icon">
                            @if($p3Done)
                                <i class="fas fa-check"></i>
                            @elseif($p3Active)
                                <i class="fas fa-search"></i>
                            @else
                                <span>3</span>
                            @endif
                        </div>
                        <div class="timeline-body">
                            <div class="timeline-title">Revisión de resultados</div>
                            @if($p3Done || $p3Active)
                                <div class="timeline-desc {{ $p3Done ? 'text-success' : '' }}">
                                    @if($p3Done)
                                        <i class="fas fa-check-circle me-1"></i>Revisión completada.
                                    @else
                                        Nuestro equipo está revisando los resultados de tu evaluación.
                                    @endif
                                </div>
                            @else
                                <div class="timeline-desc text-muted">Pendiente</div>
                            @endif
                        </div>
                    </div>

                    {{-- Paso 4: Informe final / Papelería validada --}}
                    @php
                        $p4Done = $evaluado->estado_evaluacion === 'informe_final_enviado';
                    @endphp
                    <div class="timeline-step {{ $p4Done ? 'done' : 'pending' }}">
                        <div class="timeline-icon">
                            @if($p4Done)
                                <i class="fas fa-check"></i>
                            @else
                                <span>4</span>
                            @endif
                        </div>
                        <div class="timeline-body">
                            <div class="timeline-title">Informe final</div>
                            @if($p4Done)
                                <div class="timeline-desc text-success">
                                    <i class="fas fa-check-circle me-1"></i>El informe ha sido enviado a la empresa solicitante.
                                </div>
                            @else
                                <div class="timeline-desc text-muted">El informe final será enviado a la empresa una vez completado el proceso.</div>
                            @endif
                        </div>
                    </div>

                </div>{{-- /timeline --}}

                {{-- Info empresa --}}
                <div class="mt-4 p-3 bg-light rounded border">
                    <div class="row g-2 small text-muted">
                        <div class="col-6">
                            <i class="fas fa-building me-1"></i>
                            <strong>Empresa:</strong><br>
                            {{ $evaluado->orden->empresa->nombre_comercial ?? '—' }}
                        </div>
                        <div class="col-6">
                            <i class="fas fa-briefcase me-1"></i>
                            <strong>Puesto:</strong><br>
                            {{ $evaluado->puesto_evaluar ?? '—' }}
                        </div>
                    </div>
                </div>

                {{-- Aviso de confidencialidad --}}
                <div class="mt-3 text-center text-muted small">
                    <i class="fas fa-shield-alt me-1"></i>
                    Tu información es tratada de forma confidencial por REPRO Guatemala.
                </div>

                {{-- Link al formulario completado si aplica --}}
                @if($evaluado->cuestionario_completado)
                <div class="mt-3 text-center">
                    <a href="{{ route('cuestionario.completado', $token) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-file-alt me-1"></i> Ver confirmación del formulario
                    </a>
                </div>
                @endif

            </div>{{-- /form-content --}}
        </div>{{-- /form-card --}}

    </div>
</div>
@endsection

@push('styles')
<style>
/* ---- Timeline de 4 pasos ---- */
.repro-timeline {
    position: relative;
    padding: 0;
    margin: 0;
}

.repro-timeline::before {
    content: '';
    position: absolute;
    left: 20px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-step {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    padding: 14px 0 14px 0;
    position: relative;
}

.timeline-icon {
    flex-shrink: 0;
    width: 42px;
    height: 42px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    font-weight: 700;
    border: 2px solid #dee2e6;
    background: #fff;
    position: relative;
    z-index: 1;
}

.timeline-step.done .timeline-icon {
    background: #198754;
    border-color: #198754;
    color: #fff;
}

.timeline-step.active .timeline-icon {
    background: #0d6efd;
    border-color: #0d6efd;
    color: #fff;
    box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.18);
}

.timeline-step.pending .timeline-icon {
    background: #f8f9fa;
    border-color: #dee2e6;
    color: #adb5bd;
}

.timeline-body {
    padding-top: 8px;
    flex: 1;
}

.timeline-title {
    font-weight: 600;
    font-size: 1rem;
    margin-bottom: 2px;
}

.timeline-step.done .timeline-title {
    color: #198754;
}

.timeline-step.active .timeline-title {
    color: #0d6efd;
}

.timeline-step.pending .timeline-title {
    color: #6c757d;
}

.timeline-desc {
    font-size: 0.87rem;
    line-height: 1.45;
}

.timeline-meta {
    font-size: 0.78rem;
    color: #6c757d;
    margin-top: 2px;
}
</style>
@endpush
