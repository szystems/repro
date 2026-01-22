@extends('layouts.admin')
@section('content')

<!-- Content wrapper scroll start -->
<div class="content-wrapper-scroll">

    <!-- Main header starts -->
    <div class="main-header d-flex align-items-center justify-content-between position-relative">
        <div class="d-flex align-items-center justify-content-center">
            <div class="page-icon">
                <i class="bi bi-file-earmark-text"></i>
            </div>
            <div class="page-title">
                <h5>Orden: {{ $orden->codigo_orden }}</h5>
            </div>
        </div>
        <div class="d-flex align-items-end d-none d-sm-block">
            <h6 class="float-end text-light" id="reloj"></h6>
        </div>
    </div>
    <!-- Main header ends -->

    <!-- Content wrapper start -->
    <div class="content-wrapper">

        <!-- Messages -->
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i>{{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <div class="row gx-3">
            <!-- Información de la Orden -->
            <div class="col-xl-8">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Detalles de la Orden</div>
                        <div class="card-options">
                            <a href="{{ route('ordenes.index') }}" class="btn btn-outline-secondary btn-sm me-1">
                                <i class="bi bi-arrow-left"></i> Volver
                            </a>
                            <a href="{{ route('ordenes.pdf', $orden) }}" class="btn btn-danger btn-sm me-1" target="_blank">
                                <i class="bi bi-file-pdf"></i> PDF
                            </a>
                            
                            @if(Auth::user()->hasAnyRole(['admin', 'repro']) || (Auth::user()->hasRole('empresa') && $orden->empresa_id == Auth::user()->empresa_id && in_array($orden->estado, ['solicitud', 'programacion'])))
                            <a href="{{ route('ordenes.edit', $orden) }}" class="btn btn-warning btn-sm">
                                <i class="bi bi-pencil"></i> Editar
                            </a>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Código de Orden</label>
                                <div class="fs-4 text-primary">{{ $orden->codigo_orden }}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Estado Actual</label>
                                <div>
                                    <span class="badge fs-6
                                        @if($orden->estado == 'solicitud') bg-secondary
                                        @elseif($orden->estado == 'en_proceso') bg-primary
                                        @elseif($orden->estado == 'entregado') bg-success
                                        @elseif($orden->estado == 'cancelado') bg-danger
                                        @else bg-info
                                        @endif">
                                        {{ $estados[$orden->estado] ?? $orden->estado }}
                                    </span>
                                </div>
                                @if($orden->observaciones)
                                <div class="mt-2">
                                    <small class="text-muted"><i class="bi bi-chat-left-text"></i> {{ $orden->observaciones }}</small>
                                </div>
                                @endif
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Empresa</label>
                                <div>{{ $orden->empresa->nombre ?? 'N/A' }}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Creado por</label>
                                <div>{{ $orden->creador->name ?? 'N/A' }}</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Tipos de Servicio</label>
                                <div>
                                    @php
                                        $tiposUnicos = $orden->evaluados->pluck('tipo_servicio')->unique();
                                    @endphp
                                    @foreach($tiposUnicos as $tipo)
                                        <span class="badge me-1
                                            @if($tipo == 'poligrafo') bg-primary
                                            @elseif($tipo == 'vsa') bg-info
                                            @else bg-warning
                                            @endif">
                                            @if($tipo == 'poligrafo') Polígrafo
                                            @elseif($tipo == 'vsa') VSA (Voice Stress Analysis)
                                            @else Estudio Socioeconómico
                                            @endif
                                        </span>
                                    @endforeach
                                    @if($tiposUnicos->isEmpty())
                                        <span class="text-muted">Sin evaluados asignados</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Tipos de Formulario</label>
                                <div>
                                    @php
                                        $formulariosUnicos = $orden->evaluados->pluck('tipo_formulario')->unique();
                                    @endphp
                                    @foreach($formulariosUnicos as $formulario)
                                        <span class="badge me-1 bg-secondary">
                                            @if($formulario == 'preempleo') Pre-empleo
                                            @elseif($formulario == 'periodica') Periódica
                                            @else Específica
                                            @endif
                                        </span>
                                    @endforeach
                                    @if($formulariosUnicos->isEmpty())
                                        <span class="text-muted">Sin evaluados asignados</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Polígrafos Asignados</label>
                                <div>
                                    @php
                                        $poligrafistas = $orden->evaluados->whereNotNull('poligrafista_id')->pluck('poligrafista.name')->unique();
                                    @endphp
                                    @if($poligrafistas->isNotEmpty())
                                        @foreach($poligrafistas as $poligrafista)
                                            <span class="badge bg-info me-1">{{ $poligrafista }}</span>
                                        @endforeach
                                    @else
                                        <span class="text-muted">Sin asignar</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Prioridad</label>
                                <div>
                                    @if($orden->prioridad)
                                        <span class="badge 
                                            @if($orden->prioridad == 'urgente') bg-danger
                                            @elseif($orden->prioridad == 'alta') bg-warning
                                            @elseif($orden->prioridad == 'normal') bg-info
                                            @else bg-secondary
                                            @endif">
                                            {{ ucfirst($orden->prioridad) }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">Normal</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Fecha de Solicitud</label>
                                <div>{{ $orden->fecha_solicitud ? \Carbon\Carbon::parse($orden->fecha_solicitud)->format('d/m/Y') : 'N/A' }}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Fecha Límite</label>
                                <div>
                                    @if($orden->fecha_limite)
                                        @php
                                            $fechaLimite = \Carbon\Carbon::parse($orden->fecha_limite);
                                            $diasRestantes = (int) now()->diffInDays($fechaLimite, false);
                                            $diasRestantesTexto = abs($diasRestantes);
                                        @endphp
                                        <span class="
                                            @if($diasRestantes < 0) text-danger fw-bold
                                            @elseif($diasRestantes <= 3 && !in_array($orden->estado, ['entregado', 'cancelado'])) text-warning fw-bold
                                            @endif">
                                            {{ $fechaLimite->format('d/m/Y') }}
                                        </span>
                                        @if($diasRestantes < 0)
                                            <span class="badge bg-danger ms-2 fs-6">
                                                <i class="bi bi-exclamation-triangle-fill"></i> 
                                                Vencida hace {{ $diasRestantesTexto }} {{ $diasRestantesTexto == 1 ? 'día' : 'días' }}
                                            </span>
                                        @elseif($diasRestantes == 0 && !in_array($orden->estado, ['entregado', 'cancelado']))
                                            <span class="badge bg-danger ms-2 fs-6 pulse">
                                                <i class="bi bi-alarm-fill"></i> ¡VENCE HOY!
                                            </span>
                                        @elseif($diasRestantes <= 3 && !in_array($orden->estado, ['entregado', 'cancelado']))
                                            <span class="badge bg-warning text-dark ms-2 fs-6">
                                                <i class="bi bi-hourglass-split"></i> 
                                                {{ $diasRestantes }} {{ $diasRestantes == 1 ? 'día restante' : 'días restantes' }}
                                            </span>
                                        @elseif($diasRestantes <= 7 && !in_array($orden->estado, ['entregado', 'cancelado']))
                                            <span class="badge bg-info ms-2 fs-6">
                                                <i class="bi bi-calendar-check"></i> 
                                                {{ $diasRestantes }} {{ $diasRestantes == 1 ? 'día restante' : 'días restantes' }}
                                            </span>
                                        @elseif($diasRestantes > 7)
                                            <span class="badge bg-success ms-2">
                                                <i class="bi bi-check-circle"></i> 
                                                {{ $diasRestantes }} {{ $diasRestantes == 1 ? 'día restante' : 'días restantes' }}
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-muted">Sin definir</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        @if($orden->instrucciones_generales)
                        <div class="mb-3">
                            <label class="form-label fw-bold">Instrucciones Generales</label>
                            <div class="bg-light p-3 rounded border-start border-4 border-info">{{ $orden->instrucciones_generales }}</div>
                        </div>
                        @endif

                        @if($orden->observaciones)
                        <div class="mb-3">
                            <label class="form-label fw-bold">Observaciones</label>
                            <div class="bg-light p-3 rounded border-start border-4 border-warning">{{ $orden->observaciones }}</div>
                        </div>
                        @endif

                    </div>
                </div>
            </div>

            <!-- Panel de Estado -->
            <div class="col-xl-4">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Control de Estado</div>
                    </div>
                    <div class="card-body">
                        
                        @if(Auth::user()->hasAnyRole(['admin', 'repro']))
                        <form action="{{ route('ordenes.cambiar-estado', ['orden' => $orden]) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            
                            <div class="mb-3">
                                <label class="form-label">Cambiar Estado</label>
                                <select class="form-select" name="nuevo_estado" required>
                                    <option value="">Seleccionar nuevo estado...</option>
                                    <option value="solicitud" {{ $orden->estado == 'solicitud' ? 'disabled' : '' }}>Solicitud</option>
                                    <option value="autorizacion" {{ $orden->estado == 'autorizacion' ? 'disabled' : '' }}>Autorización</option>
                                    <option value="requisito" {{ $orden->estado == 'requisito' ? 'disabled' : '' }}>Requisito</option>
                                    <option value="programacion" {{ $orden->estado == 'programacion' ? 'disabled' : '' }}>Programación</option>
                                    <option value="en_proceso" {{ $orden->estado == 'en_proceso' ? 'disabled' : '' }}>En Proceso</option>
                                    <option value="analisis" {{ $orden->estado == 'analisis' ? 'disabled' : '' }}>En Análisis</option>
                                    <option value="preliminar" {{ $orden->estado == 'preliminar' ? 'disabled' : '' }}>Reporte Preliminar</option>
                                    <option value="final" {{ $orden->estado == 'final' ? 'disabled' : '' }}>Reporte Final</option>
                                    <option value="entregado" {{ $orden->estado == 'entregado' ? 'disabled' : '' }}>Entregado</option>
                                    <option value="cancelado" {{ $orden->estado == 'cancelado' ? 'disabled' : '' }}>Cancelado</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Observaciones del cambio</label>
                                <textarea class="form-control" name="observaciones" rows="2" placeholder="Motivo del cambio de estado..."></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-arrow-repeat"></i> Cambiar Estado
                            </button>
                        </form>
                        @else
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            Solo los administradores y polígrafos pueden cambiar el estado de las órdenes.
                        </div>
                        @endif

                    </div>
                </div>

                <!-- Información adicional -->
                <div class="card mt-3">
                    <div class="card-header">
                        <div class="card-title">Información</div>
                    </div>
                    <div class="card-body">
                        <small class="text-muted">
                            <strong>Creado:</strong> {{ $orden->created_at->format('d/m/Y H:i') }}<br>
                            <strong>Actualizado:</strong> {{ $orden->updated_at->format('d/m/Y H:i') }}
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Evaluados -->
        @if($orden->evaluados->count() > 0)
        <div class="row gx-3 mt-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Evaluados Asignados ({{ $orden->evaluados->count() }})</div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>DPI</th>
                                        <th>Servicio/Formulario</th>
                                        <th>Programación</th>
                                        <th>Contacto</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orden->evaluados as $evaluado)
                                    <tr>
                                        <td>
                                            <strong>{{ $evaluado->nombre }}</strong>
                                            @if($evaluado->apellidos)
                                                <br><small class="text-muted">{{ $evaluado->apellidos }}</small>
                                            @endif
                                        </td>
                                        <td><code>{{ $evaluado->dpi }}</code></td>
                                        <td>
                                            <span class="badge 
                                                @if($evaluado->tipo_servicio == 'poligrafo') bg-primary
                                                @elseif($evaluado->tipo_servicio == 'vsa') bg-info
                                                @else bg-warning
                                                @endif">
                                                @if($evaluado->tipo_servicio == 'poligrafo') Polígrafo
                                                @elseif($evaluado->tipo_servicio == 'vsa') VSA
                                                @else Socioeconómico
                                                @endif
                                            </span><br>
                                            <small class="text-muted">
                                                @if($evaluado->tipo_formulario == 'preempleo') Pre-empleo
                                                @elseif($evaluado->tipo_formulario == 'periodica') Periódica
                                                @else Específica
                                                @endif
                                            </small>
                                        </td>
                                        <td>
                                            @if($evaluado->fecha_programada)
                                                <i class="bi bi-calendar"></i> {{ \Carbon\Carbon::parse($evaluado->fecha_programada)->format('d/m/Y') }}<br>
                                            @endif
                                            @if($evaluado->poligrafista)
                                                <small class="text-muted"><i class="bi bi-person"></i> {{ $evaluado->poligrafista->name }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($evaluado->email)
                                                <i class="bi bi-envelope"></i> {{ $evaluado->email }}<br>
                                            @endif
                                            @if($evaluado->telefono)
                                                <i class="bi bi-telephone"></i> {{ $evaluado->telefono }}
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge 
                                                @if($evaluado->estado_evaluacion == 'completado') bg-success
                                                @elseif($evaluado->estado_evaluacion == 'en_proceso') bg-primary
                                                @elseif($evaluado->estado_evaluacion == 'programado') bg-info
                                                @else bg-warning
                                                @endif">
                                                {{ ucfirst($evaluado->estado_evaluacion ?? 'pendiente') }}
                                            </span>
                                            @if($evaluado->cuestionario_completado)
                                                <br><small class="text-muted">{{ $evaluado->completado_at ? \Carbon\Carbon::parse($evaluado->completado_at)->format('d/m/Y H:i') : '' }}</small>
                                            @else
                                                <br><small class="text-muted">Expira: {{ $evaluado->token_expira_at ? \Carbon\Carbon::parse($evaluado->token_expira_at)->format('d/m/Y') : '' }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                @php
                                                    $cuestionario = $evaluado->cuestionario;
                                                @endphp
                                                
                                                @if($cuestionario)
                                                    {{-- Cuestionario existe --}}
                                                    @if(Auth::user()->hasAnyRole(['admin', 'repro']))
                                                        <a href="{{ route('admin.cuestionarios.show', $cuestionario->id) }}" 
                                                           class="btn btn-outline-info btn-sm" 
                                                           title="Ver Cuestionario">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                        <a href="{{ route('admin.cuestionarios.pdf', $cuestionario->id) }}" 
                                                           class="btn btn-outline-danger btn-sm" 
                                                           title="Imprimir PDF"
                                                           target="_blank">
                                                            <i class="bi bi-file-pdf"></i>
                                                        </a>
                                                        <a href="{{ route('admin.cuestionarios.edit', $cuestionario->id) }}" 
                                                           class="btn btn-outline-warning btn-sm" 
                                                           title="Editar Cuestionario">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                    @endif
                                                @else
                                                    {{-- No hay cuestionario --}}
                                                    <span class="text-muted small">Sin cuestionario<br</span>
                                                @endif
                                                
                                                @if(!$evaluado->cuestionario_completado)
                                                    <a href="{{ route('cuestionario.mostrar', $evaluado->token_unico) }}" 
                                                       class="btn btn-outline-primary btn-sm" 
                                                       title="Enlace del Evaluado" 
                                                       target="_blank">
                                                        <i class="bi bi-link-45deg"></i>
                                                    </a>
                                                    
                                                    @if($evaluado->email)
                                                    <form action="{{ route('evaluados.reenviar-correo', $evaluado->id) }}" 
                                                          method="POST" 
                                                          class="d-inline"
                                                          onsubmit="return confirm('¿Enviar correo a {{ $evaluado->email }}?');">
                                                        @csrf
                                                        <button type="submit" 
                                                                class="btn btn-outline-success btn-sm" 
                                                                title="Reenviar correo a {{ $evaluado->email }}">
                                                            <i class="bi bi-envelope"></i>
                                                        </button>
                                                    </form>
                                                    @endif
                                                @endif
                                            </div>
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
        @else
        <div class="row gx-3 mt-3">
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    No hay evaluados asignados a esta orden. 
                    <a href="{{ route('ordenes.edit', $orden) }}" class="alert-link">Haga clic aquí para agregar evaluados</a>.
                </div>
            </div>
        </div>
        @endif

    </div>
    <!-- Content wrapper end -->

</div>
<!-- Content wrapper scroll end -->

@endsection

@push('styles')
<style>
/* Estilos para alertas de fechas límite */
.pulse {
    animation: pulse 1.5s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

/* Mejores colores para los badges de fecha límite */
.badge.bg-danger {
    background-color: #dc3545 !important;
    color: white !important;
    font-weight: bold;
    box-shadow: 0 2px 4px rgba(220, 53, 69, 0.3);
}

.badge.bg-warning {
    background-color: #fd7e14 !important;
    color: white !important;
    font-weight: bold;
    box-shadow: 0 2px 4px rgba(253, 126, 20, 0.3);
}

.badge.bg-info {
    background-color: #17a2b8 !important;
    color: white !important;
    font-weight: bold;
    box-shadow: 0 2px 4px rgba(23, 162, 184, 0.3);
}

.badge.bg-success {
    background-color: #28a745 !important;
    color: white !important;
    font-weight: bold;
    box-shadow: 0 2px 4px rgba(40, 167, 69, 0.3);
}

/* Tamaño más grande para badges críticos */
.badge.fs-6 {
    font-size: 0.875rem !important;
    padding: 0.5rem 0.75rem !important;
}

/* Hover effects para los badges */
.badge:hover {
    transform: translateY(-1px);
    transition: all 0.2s ease;
}
</style>
@endpush