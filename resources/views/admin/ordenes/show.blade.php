@extends(session('layout', 'layouts.admin'))
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
        <div class="alert alert-danger alert-dismissible fade show" role="alert" id="alert-programar-cita">
            <i class="bi bi-exclamation-triangle me-2"></i>
            @if(session('programar_evaluado_id'))
                <strong>No se pudo programar la cita:</strong>
            @endif
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @elseif($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert" id="alert-programar-cita">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>No se pudo programar la cita:</strong>
            <ul class="mb-0 mt-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i>{{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        @if($orden->archivada)
        <div class="alert alert-secondary">
            <i class="bi bi-archive me-2"></i>
            Esta orden está <strong>archivada</strong>
            @if($orden->archivada_at)
                desde {{ $orden->archivada_at->format('d/m/Y H:i') }}
            @endif
            @if($orden->archivadaPor)
                por {{ $orden->archivadaPor->name }}
            @endif
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
                                <i class="bi bi-file-pdf"></i> Orden de Servicio
                            </a>

                            @if(!in_array($orden->estado, ['entregado', 'cancelado']) && !$orden->archivada && (Auth::user()->hasAnyRole(['admin', 'repro']) || (Auth::user()->role_as == 1 && $orden->empresa_id == Auth::user()->empresa_id && $orden->estado === 'orden_recibida')))
                            <a href="{{ route('ordenes.edit', $orden) }}" class="btn btn-warning btn-sm">
                                <i class="bi bi-pencil"></i> Editar
                            </a>
                            @endif

                            @if(Auth::user()->role_as >= 3 && !$orden->archivada)
                            <form action="{{ route('ordenes.archivar', $orden) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Archivar la orden {{ $orden->codigo_orden }}? El expediente se conserva pero dejará de aparecer en los listados.')">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-archive"></i> Archivar
                                </button>
                            </form>
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
                                <label class="form-label fw-bold">Estado de Orden</label>
                                <div>
                                    <span class="badge fs-6 bg-{{ $orden->estado_color }}">
                                        {{ $orden->estado_human }}
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

                        @if($orden->sede)
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Sede Responsable</label>
                                <div><i class="bi bi-geo-alt"></i> {{ $orden->sede->nombre }}</div>
                            </div>
                        </div>
                        @endif

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
                                        $poligrafistasAsignados = $orden->evaluados->whereNotNull('poligrafista_id')->pluck('poligrafista.name')->unique();
                                    @endphp
                                    @if($poligrafistasAsignados->isNotEmpty())
                                        @foreach($poligrafistasAsignados as $polAsignado)
                                            <span class="badge bg-info me-1">{{ $polAsignado }}</span>
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
                                <label class="form-label fw-bold">Fecha de Creación</label>
                                <div>{{ $orden->created_at ? $orden->created_at->format('d/m/Y H:i') : 'N/A' }}</div>
                            </div>
                        </div>

                        @if($orden->instrucciones_generales)
                        <div class="mb-3">
                            <label class="form-label fw-bold">Instrucciones Generales</label>
                            <div class="bg-light p-3 rounded border-start border-4 border-info">{{ $orden->instrucciones_generales }}</div>
                        </div>
                        @endif

                        @if($orden->observaciones_internas && Auth::user()->role_as >= 2)
                        <div class="mb-3">
                            <label class="form-label fw-bold">Observaciones Internas <small class="text-muted">(solo REPRO)</small></label>
                            <div class="bg-light p-3 rounded border-start border-4 border-warning">{{ $orden->observaciones_internas }}</div>
                        </div>
                        @endif

                        @if($orden->requerimientos_generales)
                        <div class="mb-3">
                            <label class="form-label fw-bold">Requerimientos del Cliente</label>
                            <div class="bg-light p-3 rounded border-start border-4 border-primary">{{ $orden->requerimientos_generales }}</div>
                        </div>
                        @endif

                    </div>
                </div>
            </div>

            <!-- Panel de Estado de Orden -->
            <div class="col-xl-4">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Control de Estado de Orden</div>
                    </div>
                    <div class="card-body">

                        @if(Auth::user()->hasAnyRole(['admin', 'repro']))
                        <form action="{{ route('ordenes.cambiar-estado', ['orden' => $orden]) }}" method="POST">
                            @csrf
                            @method('PATCH')

                            <div class="mb-3">
                                <label class="form-label">Cambiar Estado de Orden</label>
                                <select class="form-select" name="nuevo_estado" required>
                                    <option value="">Seleccionar nuevo estado de orden...</option>
                                    @foreach($estados as $key => $label)
                                        @if($key !== $orden->estado)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endif
                                    @endforeach
                                </select>
                                <small class="text-muted mt-1 d-block">
                                    <i class="bi bi-info-circle"></i> El estado de la orden se actualiza automáticamente. Cambio manual solo si es necesario.
                                </small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Observaciones del cambio de estado de orden</label>
                                <textarea class="form-control" name="observaciones" rows="2" placeholder="Motivo del cambio de estado de orden..."></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-arrow-repeat"></i> Cambiar Estado de Orden
                            </button>
                        </form>

                        <!-- Control de visibilidad de resultados para empresa -->
                        <hr>
                        <div class="mb-0">
                            <label class="form-label fw-bold">
                                <i class="bi bi-eye{{ $orden->resultados_visibles_empresa ? '' : '-slash' }}"></i>
                                Resultados para Empresa
                            </label>
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="small {{ $orden->resultadosDisponiblesParaEmpresa() ? 'text-success' : 'text-muted' }}">
                                    @if($orden->resultadosDisponiblesParaEmpresa())
                                        <i class="bi bi-check-circle"></i> Disponibles
                                    @elseif($orden->resultados_visibles_empresa && $orden->estado !== 'entregado')
                                        <i class="bi bi-hourglass-split"></i> Activo (pendiente entrega)
                                    @else
                                        <i class="bi bi-x-circle"></i> Ocultos
                                    @endif
                                </span>
                                @if(Auth::user()->role_as >= 3)
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                           id="toggleResultados"
                                           {{ $orden->resultados_visibles_empresa ? 'checked' : '' }}
                                           onchange="confirmarToggleResultados(this)">
                                </div>
                                @endif
                            </div>
                            <small class="text-muted d-block mt-1">
                                @if($orden->resultadosDisponiblesParaEmpresa())
                                    <i class="bi bi-unlock text-success"></i> La empresa puede ver los resultados.
                                @elseif($orden->resultados_visibles_empresa && $orden->estado !== 'entregado')
                                    <i class="bi bi-info-circle text-info"></i> Switch activo. Se mostrarán cuando el estado sea "Entregado".
                                @else
                                    <i class="bi bi-lock text-warning"></i> La empresa NO puede ver los resultados.
                                @endif
                            </small>
                        </div>

                        @if(Auth::user()->role_as >= 3)
                        <form id="form-toggle-resultados" action="{{ route('ordenes.toggle-resultados-visibles', $orden) }}" method="POST" class="d-none">
                            @csrf
                            @method('PATCH')
                        </form>
                        @endif
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
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div class="card-title mb-0">Evaluados Asignados ({{ $orden->evaluados->count() }})</div>
                        <div>
                            <button class="btn btn-outline-secondary btn-sm" onclick="toggleAllAccordions(true)">
                                <i class="bi bi-arrows-expand"></i> Expandir todos
                            </button>
                            <button class="btn btn-outline-secondary btn-sm" onclick="toggleAllAccordions(false)">
                                <i class="bi bi-arrows-collapse"></i> Colapsar todos
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="accordion" id="accordionEvaluados">
                            @php $dpisMultiServicio = $orden->evaluados->groupBy('dpi')->filter(fn($g) => $g->count() > 1)->keys(); @endphp
                            @foreach($orden->evaluados as $index => $evaluado)
                            @php $cuestionario = $evaluado->cuestionario; @endphp
                            @php $bgAlt = $index % 2 !== 0 ? 'background-color: #f4f5f7;' : ''; @endphp
                            <div class="accordion-item" style="{{ $bgAlt }}">
                                <h2 class="accordion-header" id="heading-evaluado-{{ $evaluado->id }}">
                                    <button class="accordion-button collapsed" type="button"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#collapse-evaluado-{{ $evaluado->id }}"
                                            aria-expanded="false"
                                            aria-controls="collapse-evaluado-{{ $evaluado->id }}"
                                            style="{{ $bgAlt }}">
                                        <div class="d-flex align-items-center justify-content-between flex-grow-1 me-3 flex-wrap gap-2">
                                            <div>
                                                <strong>{{ $evaluado->nombre }} {{ $evaluado->apellidos }}</strong>
                                                <span class="text-muted ms-2">DPI: <code>{{ $evaluado->dpi }}</code></span>
                                                @if($dpisMultiServicio->contains($evaluado->dpi))
                                                    <span class="badge bg-info ms-1" title="Este evaluado tiene múltiples servicios en esta orden"><i class="bi bi-layers"></i> Multi-servicio</span>
                                                @endif
                                            </div>
                                            <div class="d-flex gap-2 align-items-center">
                                                <span class="badge
                                                    @if($evaluado->tipo_servicio == 'poligrafo') bg-primary
                                                    @elseif($evaluado->tipo_servicio == 'vsa') bg-info
                                                    @else bg-warning
                                                    @endif">
                                                    @if($evaluado->tipo_servicio == 'poligrafo') Polígrafo
                                                    @elseif($evaluado->tipo_servicio == 'vsa') VSA
                                                    @else Socioeconómico
                                                    @endif
                                                </span>
                                                <span class="badge bg-{{ $evaluado->estado_evaluacion_color }}" title="Estado de Evaluación">
                                                    <i class="bi bi-clipboard-check"></i> {{ $evaluado->estado_evaluacion_texto }}
                                                </span>
                                                <span class="badge bg-{{ $evaluado->estado_formulario_color }}" title="Estado de Formulario">
                                                    <i class="bi bi-file-text"></i> {{ \App\Models\EvaluadoOrden::estadosFormularioDisponibles()[$evaluado->estado_formulario] ?? ucfirst($evaluado->estado_formulario) }}
                                                </span>
                                                @if($evaluado->modalidad)
                                                    <span class="badge bg-{{ $evaluado->modalidad === 'virtual' ? 'purple' : 'secondary' }}" title="Modalidad">
                                                        <i class="bi bi-{{ $evaluado->modalidad === 'virtual' ? 'camera-video' : 'building' }}"></i> {{ ucfirst($evaluado->modalidad) }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-light text-muted border" title="Modalidad sin definir">
                                                        <i class="bi bi-question-circle"></i> Sin modalidad
                                                    </span>
                                                @endif
                                                @if($evaluado->documentos->count() > 0)
                                                    <span class="badge bg-secondary" title="Documentos"><i class="bi bi-folder2-open"></i> {{ $evaluado->documentos->count() }}</span>
                                                @endif
                                                @if($evaluado->resultado)
                                                    <span class="badge bg-{{ $evaluado->resultado_color }}">{{ $evaluado->resultado_texto }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </button>
                                </h2>
                                <div id="collapse-evaluado-{{ $evaluado->id }}"
                                     class="accordion-collapse collapse"
                                     aria-labelledby="heading-evaluado-{{ $evaluado->id }}"
                                     data-bs-parent="#accordionEvaluados">
                                    <div class="accordion-body">

                                        {{-- Información general del evaluado --}}
                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <small class="text-muted d-block">Formulario</small>
                                                <span class="badge bg-secondary">
                                                    @if($evaluado->tipo_formulario == 'preempleo') Pre-empleo
                                                    @elseif($evaluado->tipo_formulario == 'periodica') Periódica
                                                    @else Específica
                                                    @endif
                                                </span>
                                            </div>
                                            <div class="col-md-4">
                                                <small class="text-muted d-block">Puesto a Evaluar</small>
                                                <span>{{ $evaluado->puesto_evaluar ?: '—' }}</span>
                                            </div>
                                            <div class="col-md-4">
                                                <small class="text-muted d-block">Sede REPRO</small>
                                                <span>{{ $evaluado->sede?->nombre ?: '—' }}</span>
                                            </div>
                                            <div class="col-md-4">
                                                <small class="text-muted d-block">Sede/Región Empresa</small>
                                                <span>{{ $evaluado->sede_region_empresa ?: '—' }}</span>
                                            </div>
                                            <div class="col-md-4">
                                                <small class="text-muted d-block">Programación</small>
                                                @if($evaluado->fecha_programada)
                                                    <i class="bi bi-calendar"></i> {{ \Carbon\Carbon::parse($evaluado->fecha_programada)->format('d/m/Y') }}
                                                    <br><small class="text-muted">
                                                        <i class="bi bi-clock"></i>
                                                        {{ \Carbon\Carbon::parse($evaluado->fecha_programada)->format('h:i A') }}
                                                        @if($evaluado->fecha_hora_fin)
                                                            - {{ \Carbon\Carbon::parse($evaluado->fecha_hora_fin)->format('h:i A') }}
                                                        @endif
                                                    </small>
                                                    @if($evaluado->sede)
                                                        <br><small class="text-muted"><i class="bi bi-geo-alt"></i> {{ $evaluado->sede->nombre }}</small>
                                                    @endif
                                                @else
                                                    <span class="text-muted">Sin programar</span>
                                                @endif
                                                {{-- Modalidad siempre visible, independiente de si hay cita --}}
                                                <br><small>
                                                    @if($evaluado->modalidad)
                                                        <span class="badge bg-{{ $evaluado->modalidad === 'virtual' ? 'purple' : 'secondary' }}">
                                                            <i class="bi bi-{{ $evaluado->modalidad === 'virtual' ? 'camera-video' : 'building' }}"></i> {{ ucfirst($evaluado->modalidad) }}
                                                        </span>
                                                    @else
                                                        <span class="badge bg-light text-muted border"><i class="bi bi-question-circle"></i> Sin modalidad</span>
                                                    @endif
                                                </small>
                                                @if($evaluado->poligrafista)
                                                    <br><small class="text-muted"><i class="bi bi-person"></i> {{ $evaluado->poligrafista->name }}</small>
                                                @endif
                                                @if($evaluado->responsable)
                                                    <br><small class="text-muted"><i class="bi bi-person-check"></i> Resp: {{ $evaluado->responsable->name }}</small>
                                                @endif
                                                @if(Auth::user()->role_as >= 2)
                                                    <br>
                                                    @if($evaluado->fecha_programada)
                                                        <a href="{{ route('calendario.dia', ['fecha' => \Carbon\Carbon::parse($evaluado->fecha_programada)->format('Y-m-d')]) }}"
                                                           class="btn btn-outline-info btn-sm mt-1" title="Ver en calendario">
                                                            <i class="bi bi-calendar3"></i> Ver en calendario
                                                        </a>
                                                    @endif
                                                    <button type="button" class="btn btn-outline-success btn-sm mt-1"
                                                            data-bs-toggle="modal" data-bs-target="#modalProgramarEv{{ $evaluado->id }}"
                                                            title="{{ $evaluado->fecha_programada ? 'Reprogramar cita' : 'Programar cita' }}">
                                                        <i class="bi bi-calendar-plus"></i> {{ $evaluado->fecha_programada ? 'Reprogramar' : 'Programar cita' }}
                                                    </button>
                                                @endif
                                            </div>
                                            <div class="col-md-4">
                                                <small class="text-muted d-block">Contacto</small>
                                                @if($evaluado->email)
                                                    <i class="bi bi-envelope"></i> {{ $evaluado->email }}<br>
                                                @endif
                                                @if($evaluado->telefono)
                                                    <i class="bi bi-telephone"></i> {{ $evaluado->telefono }}<br>
                                                @endif
                                                @if($evaluado->direccion)
                                                    <i class="bi bi-geo-alt"></i> {{ $evaluado->direccion }}<br>
                                                @endif
                                                @if(!$evaluado->email && !$evaluado->telefono && !$evaluado->direccion)
                                                    <span class="text-muted">Sin contacto</span>
                                                @endif
                                            </div>
                                        </div>

                                        @if($evaluado->observaciones)
                                        <div class="row mb-3">
                                            <div class="col-12">
                                                <small class="text-muted d-block">Observaciones del Evaluado</small>
                                                <div class="bg-light p-2 rounded border-start border-3 border-info small">{{ $evaluado->observaciones }}</div>
                                            </div>
                                        </div>
                                        @endif

                                        {{-- Form para editar observación (solo colaborador/admin) --}}
                                        @if(Auth::user()->role_as >= 2)
                                        <div class="row mb-3">
                                            <div class="col-12">
                                                <details class="text-sm">
                                                    <summary class="text-muted small" style="cursor:pointer;">
                                                        <i class="bi bi-pencil-square"></i>
                                                        {{ $evaluado->observaciones ? 'Editar observación' : 'Agregar observación (visible para empresa)' }}
                                                    </summary>
                                                    <form action="{{ route('evaluados.actualizar-observacion', $evaluado) }}" method="POST" class="mt-2">
                                                        @csrf @method('PATCH')
                                                        <textarea class="form-control form-control-sm" name="observaciones" rows="3"
                                                                  placeholder="Observación visible para la empresa..."
                                                                  maxlength="2000">{{ $evaluado->observaciones }}</textarea>
                                                        <div class="d-flex gap-2 mt-1">
                                                            <button type="submit" class="btn btn-sm btn-success">
                                                                <i class="bi bi-check-lg"></i> Guardar
                                                            </button>
                                                            @if($evaluado->observaciones)
                                                            <button type="submit" name="observaciones" value=""
                                                                    class="btn btn-sm btn-outline-secondary"
                                                                    onclick="return confirm('¿Eliminar la observación?');">
                                                                <i class="bi bi-trash"></i> Borrar
                                                            </button>
                                                            @endif
                                                        </div>
                                                    </form>
                                                </details>
                                            </div>
                                        </div>
                                        @endif

                                        {{-- Estados del evaluado --}}
                                        @php
                                            $nombresEval = \App\Models\EvaluadoOrden::estadosEvaluacionDisponibles();
                                            $nombresForm = \App\Models\EvaluadoOrden::estadosFormularioDisponibles();
                                            $nombresProg = \App\Models\EvaluadoOrden::estadosProgramacionDisponibles();
                                            $transicionesEval = Auth::user()->role_as >= 2
                                                ? (\App\Models\EvaluadoOrden::transicionesEvaluacion()[$evaluado->estado_evaluacion] ?? [])
                                                : [];
                                            $transicionesForm = Auth::user()->role_as >= 2
                                                ? $evaluado->transicionesFormularioDisponibles()
                                                : [];
                                            $esSaltoSocioJotform = Auth::user()->role_as >= 2
                                                && $evaluado->puedeMarcarFormularioCompletadoManualSocio()
                                                && ! in_array(
                                                    'formulario_completado_y_recibido',
                                                    \App\Models\EvaluadoOrden::transicionesFormulario()[$evaluado->estado_formulario] ?? [],
                                                    true
                                                );
                                            $transicionesProg = Auth::user()->role_as >= 2
                                                ? (\App\Models\EvaluadoOrden::transicionesProgramacion()[$evaluado->estado_programacion] ?? [])
                                                : [];
                                        @endphp
                                        <div class="row mb-3 g-2">
                                            {{-- Estado de Evaluación --}}
                                            <div class="col-md-4">
                                                <small class="text-muted d-block mb-1"><i class="bi bi-clipboard2-check"></i> Estado de Evaluación</small>
                                                <span class="badge bg-{{ $evaluado->estado_evaluacion_color }}">{{ $evaluado->estado_evaluacion_texto }}</span>
                                                @if(Auth::user()->role_as >= 2 && count($transicionesEval) > 0)
                                                <form action="{{ route('evaluados.cambiar-estado', $evaluado->id) }}" method="POST" class="mt-1">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="tipo_estado" value="evaluacion">
                                                    <div class="d-flex gap-1 align-items-center">
                                                        <select name="nuevo_estado" class="form-select form-select-sm" style="max-width: 180px;" required>
                                                            <option value="">Cambiar a...</option>
                                                            @foreach($transicionesEval as $estado)
                                                                <option value="{{ $estado }}">{{ $nombresEval[$estado] ?? ucfirst($estado) }}</option>
                                                            @endforeach
                                                        </select>
                                                        <button type="submit" class="btn btn-outline-primary btn-sm" title="Cambiar estado evaluación">
                                                            <i class="bi bi-arrow-right-circle"></i>
                                                        </button>
                                                    </div>
                                                    <details class="mt-1">
                                                        <summary class="text-muted" style="font-size:0.75rem;cursor:pointer;">+ Motivo / observación (opcional)</summary>
                                                        <textarea name="observacion" class="form-control form-control-sm mt-1" rows="2" maxlength="1000" placeholder="Ej: Candidato reagendó por viaje..."></textarea>
                                                    </details>
                                                </form>
                                                @endif
                                            </div>

                                            {{-- Estado de Formulario --}}
                                            <div class="col-md-4">
                                                <small class="text-muted d-block mb-1"><i class="bi bi-file-earmark-text"></i> Estado de Formulario</small>
                                                <span class="badge bg-{{ $evaluado->estado_formulario_color }}">{{ $nombresForm[$evaluado->estado_formulario] ?? ucfirst($evaluado->estado_formulario) }}</span>
                                                @if($evaluado->completado_at)
                                                    <small class="text-muted ms-1 d-block">{{ \Carbon\Carbon::parse($evaluado->completado_at)->format('d/m/Y H:i') }}</small>
                                                @elseif($evaluado->token_expira_at)
                                                    <small class="text-muted ms-1 d-block">Expira: {{ \Carbon\Carbon::parse($evaluado->token_expira_at)->format('d/m/Y') }}</small>
                                                @endif
                                                @if(Auth::user()->role_as >= 2 && count($transicionesForm) > 0)
                                                <form action="{{ route('evaluados.cambiar-estado', $evaluado->id) }}" method="POST" class="mt-1">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="tipo_estado" value="formulario">
                                                    <div class="d-flex gap-1 align-items-center">
                                                        <select name="nuevo_estado" class="form-select form-select-sm" style="max-width: 180px;" required>
                                                            <option value="">Cambiar a...</option>
                                                            @foreach($transicionesForm as $estado)
                                                                <option value="{{ $estado }}">
                                                                    {{ $nombresForm[$estado] ?? ucfirst($estado) }}
                                                                    @if($estado === 'formulario_completado_y_recibido' && $esSaltoSocioJotform)
                                                                        (manual / Jotform)
                                                                    @endif
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <button type="submit" class="btn btn-outline-primary btn-sm" title="Cambiar estado formulario">
                                                            <i class="bi bi-arrow-right-circle"></i>
                                                        </button>
                                                    </div>
                                                    @if($esSaltoSocioJotform)
                                                        <small class="text-muted d-block mt-1">Socioeconómico: puede marcar Completado sin llenar el formulario REPRO (workaround Jotform).</small>
                                                    @endif
                                                    <details class="mt-1">
                                                        <summary class="text-muted" style="font-size:0.75rem;cursor:pointer;">+ Motivo / observación (opcional)</summary>
                                                        <textarea name="observacion" class="form-control form-control-sm mt-1" rows="2" maxlength="1000" placeholder="Ej: Candidato confirmó recepción del formulario..."></textarea>
                                                    </details>
                                                </form>
                                                @endif
                                            </div>

                                            {{-- Estado de Programación --}}
                                            <div class="col-md-4">
                                                <small class="text-muted d-block mb-1"><i class="bi bi-calendar-check"></i> Estado de Programación</small>
                                                @php $estadoProg = $nombresProg[$evaluado->estado_programacion] ?? ucfirst($evaluado->estado_programacion ?? '—'); @endphp
                                                @php
                                                    $colorProg = match($evaluado->estado_programacion ?? '') {
                                                        'programado'        => 'success',
                                                        'proceso_realizado' => 'primary',
                                                        'reprogramado'      => 'warning',
                                                        'inasistencia'      => 'danger',
                                                        'desistio'          => 'dark',
                                                        'cancelado'         => 'danger',
                                                        'contactando'       => 'info',
                                                        'contactado'        => 'secondary',
                                                        default             => 'secondary',
                                                    };
                                                @endphp
                                                <span class="badge bg-{{ $colorProg }}">{{ $estadoProg }}</span>
                                                @if(Auth::user()->role_as >= 2 && count($transicionesProg) > 0)
                                                <form action="{{ route('evaluados.cambiar-estado', $evaluado->id) }}" method="POST" class="mt-1">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="tipo_estado" value="programacion">
                                                    <div class="d-flex gap-1 align-items-center">
                                                        <select name="nuevo_estado" class="form-select form-select-sm" style="max-width: 180px;" required>
                                                            <option value="">Cambiar a...</option>
                                                            @foreach($transicionesProg as $estado)
                                                                <option value="{{ $estado }}">{{ $nombresProg[$estado] ?? ucfirst($estado) }}</option>
                                                            @endforeach
                                                        </select>
                                                        <button type="submit" class="btn btn-outline-primary btn-sm" title="Cambiar estado programación">
                                                            <i class="bi bi-arrow-right-circle"></i>
                                                        </button>
                                                    </div>
                                                    <details class="mt-1">
                                                        <summary class="text-muted" style="font-size:0.75rem;cursor:pointer;">+ Motivo / observación (opcional)</summary>
                                                        <textarea name="observacion" class="form-control form-control-sm mt-1" rows="2" maxlength="1000" placeholder="Ej: Inasistencia sin previo aviso..."></textarea>
                                                    </details>
                                                </form>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Historial de cambios de estado --}}
                                        @if(Auth::user()->role_as >= 2)
                                        @php $historial = $evaluado->historialEstados()->with('usuario')->get(); @endphp
                                        @if($historial->isNotEmpty())
                                        <div class="mb-3">
                                            <details>
                                                <summary class="text-muted small" style="cursor:pointer;">
                                                    <i class="bi bi-clock-history"></i> Historial de cambios ({{ $historial->count() }})
                                                </summary>
                                                <div class="table-responsive mt-2">
                                                    <table class="table table-sm table-bordered small mb-0">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>Fecha</th>
                                                                <th>Campo</th>
                                                                <th>Anterior</th>
                                                                <th>Nuevo</th>
                                                                <th>Usuario</th>
                                                                <th>Observación</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($historial as $entrada)
                                                            <tr>
                                                                <td class="text-nowrap">{{ $entrada->created_at->format('d/m/Y H:i') }}</td>
                                                                <td>
                                                                    @php
                                                                        $campoLabel = match($entrada->campo) {
                                                                            'estado_evaluacion'   => 'Estado de Evaluación',
                                                                            'estado_formulario'   => 'Estado de Formulario',
                                                                            'estado_programacion' => 'Estado de Programación',
                                                                            'estado_orden'        => 'Estado de Orden',
                                                                            'modalidad'           => 'Modalidad',
                                                                            default               => ucfirst($entrada->campo),
                                                                        };
                                                                    @endphp
                                                                    <span class="badge bg-light text-dark border">{{ $campoLabel }}</span>
                                                                </td>
                                                                <td class="text-muted">{{ $entrada->estado_anterior ?: '—' }}</td>
                                                                <td><strong>{{ $entrada->estado_nuevo }}</strong></td>
                                                                <td>{{ $entrada->usuario?->name ?? 'Sistema' }}</td>
                                                                <td class="text-muted">{{ $entrada->observacion ?: '—' }}</td>
                                                            </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </details>
                                        </div>
                                        @endif
                                        @endif

                                        {{-- Acciones del evaluado --}}
                                        <div class="mb-3">
                                            <div class="btn-group" role="group">
                                                @if($cuestionario)
                                                    @if(Auth::user()->hasAnyRole(['admin', 'repro']))
                                                        <a href="{{ route('admin.cuestionarios.show', $cuestionario->id) }}"
                                                           class="btn btn-outline-info btn-sm" title="Ver Cuestionario">
                                                            <i class="bi bi-eye"></i> Ver Cuestionario
                                                        </a>
                                                        <a href="{{ route('admin.cuestionarios.pdf', $cuestionario->id) }}"
                                                           class="btn btn-outline-danger btn-sm" title="Imprimir PDF" target="_blank">
                                                            <i class="bi bi-file-pdf"></i> PDF
                                                        </a>
                                                        <a href="{{ route('admin.cuestionarios.edit', $cuestionario->id) }}"
                                                           class="btn btn-outline-warning btn-sm" title="Editar Cuestionario">
                                                            <i class="bi bi-pencil"></i> Editar
                                                        </a>
                                                    @endif
                                                @else
                                                    <span class="btn btn-outline-secondary btn-sm disabled">Sin cuestionario</span>
                                                @endif

                                                @if(!$evaluado->cuestionario_completado)
                                                    <a href="{{ route('cuestionario.mostrar', $evaluado->token_unico) }}"
                                                       class="btn btn-outline-primary btn-sm" title="Enlace del Evaluado" target="_blank">
                                                        <i class="bi bi-link-45deg"></i> Enlace
                                                    </a>
                                                    <button type="button" class="btn btn-outline-secondary btn-sm"
                                                            onclick="copiarEnlaceEvaluado('{{ route('cuestionario.mostrar', $evaluado->token_unico) }}')"
                                                            title="Copiar enlace al portapapeles">
                                                        <i class="bi bi-clipboard"></i>
                                                    </button>

                                                    @if($evaluado->email)
                                                    <form action="{{ route('evaluados.reenviar-correo', $evaluado->id) }}"
                                                          method="POST" class="d-inline"
                                                          onsubmit="return confirm('¿Enviar correo a {{ $evaluado->email }}?');">
                                                        @csrf
                                                        <button type="submit" class="btn btn-outline-success btn-sm"
                                                                title="Reenviar correo a {{ $evaluado->email }}">
                                                            <i class="bi bi-envelope"></i> Correo
                                                        </button>
                                                    </form>
                                                    @endif
                                                @endif

                                                @if($evaluado->cuestionario_completado && Auth::user()->role_as >= 2)
                                                <form action="{{ route('evaluados.rehabilitar-cuestionario', $evaluado->id) }}"
                                                      method="POST" class="d-inline"
                                                      onsubmit="return confirm('¿Rehabilitar el cuestionario de {{ $evaluado->nombre }}? Esto permitirá que vuelva a llenarlo con un nuevo enlace.');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-warning btn-sm" title="Rehabilitar cuestionario">
                                                        <i class="bi bi-arrow-counterclockwise"></i> Rehabilitar
                                                    </button>
                                                </form>
                                                @elseif(!$evaluado->cuestionario_completado && $cuestionario && Auth::user()->role_as >= 2)
                                                <form action="{{ route('evaluados.deshabilitar-cuestionario', $evaluado->id) }}"
                                                      method="POST" class="d-inline"
                                                      onsubmit="return confirm('¿Deshabilitar el cuestionario de {{ $evaluado->nombre }}? Esto bloqueará el acceso y marcará el cuestionario como completado.');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-danger btn-sm" title="Deshabilitar cuestionario">
                                                        <i class="bi bi-lock"></i> Deshabilitar
                                                    </button>
                                                </form>
                                                @endif
                                            </div>
                                        </div>

                                        <hr>

                                        {{-- Documentos del evaluado --}}
                                        @include('admin.ordenes._documentos_evaluado', ['evaluado' => $evaluado])

                                        <hr>

                                        {{-- Archivos de Resultado --}}
                                        <div class="mt-3">
                                            <h6 class="mb-3">
                                                <i class="bi bi-file-earmark-check"></i> Archivos de Resultado
                                                @if($evaluado->resultado)
                                                    — <span class="badge bg-{{ $evaluado->resultado_color }}">{{ $evaluado->resultado_texto }}</span>
                                                @endif
                                            </h6>

                                            <div class="row">
                                                {{-- Resultado Preliminar --}}
                                                <div class="col-md-6">
                                                    <div class="card border-info mb-2">
                                                        <div class="card-body py-2 px-3">
                                                            <h6 class="card-subtitle mb-2 text-info"><i class="bi bi-file-earmark-arrow-up"></i> Resultado Preliminar</h6>
                                                            @if($evaluado->tieneResultadoPreliminar())
                                                                <div class="d-flex align-items-center justify-content-between">
                                                                    <div>
                                                                        <i class="bi bi-check-circle text-success"></i> Subido
                                                                        @if($evaluado->resultado_preliminar_at)
                                                                            <small class="text-muted">{{ $evaluado->resultado_preliminar_at->format('d/m/Y H:i') }}</small>
                                                                        @endif
                                                                    </div>
                                                                    <div>
                                                                        <a href="{{ route('evaluados.descargar-resultado-archivo', [$evaluado->id, 'preliminar']) }}"
                                                                           class="btn btn-sm btn-outline-info" title="Descargar">
                                                                            <i class="bi bi-download"></i>
                                                                        </a>
                                                                        @if(Auth::user()->role_as >= 2)
                                                                        <form action="{{ route('evaluados.eliminar-resultado-archivo', [$evaluado->id, 'preliminar']) }}"
                                                                              method="POST" class="d-inline"
                                                                              onsubmit="return confirm('¿Eliminar resultado preliminar?')">
                                                                            @csrf
                                                                            @method('DELETE')
                                                                            <button class="btn btn-sm btn-outline-danger" title="Eliminar">
                                                                                <i class="bi bi-trash"></i>
                                                                            </button>
                                                                        </form>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            @else
                                                                @if(Auth::user()->role_as >= 2)
                                                                <form action="{{ route('evaluados.subir-resultado-archivo', $evaluado->id) }}"
                                                                      method="POST" enctype="multipart/form-data" class="d-flex gap-2 align-items-end">
                                                                    @csrf
                                                                    <input type="hidden" name="tipo_resultado" value="preliminar">
                                                                    <input type="file" name="archivo" class="form-control form-control-sm" accept=".pdf,.doc,.docx" capture="environment" required>
                                                                    <button type="submit" class="btn btn-sm btn-info text-white">
                                                                        <i class="bi bi-upload"></i>
                                                                    </button>
                                                                </form>
                                                                @else
                                                                    <span class="text-muted"><i class="bi bi-dash-circle"></i> No disponible</span>
                                                                @endif
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- Resultado Final --}}
                                                <div class="col-md-6">
                                                    <div class="card border-success mb-2">
                                                        <div class="card-body py-2 px-3">
                                                            <h6 class="card-subtitle mb-2 text-success"><i class="bi bi-file-earmark-check"></i> Resultado Final</h6>
                                                            @if($evaluado->tieneResultadoFinal())
                                                                <div class="d-flex align-items-center justify-content-between">
                                                                    <div>
                                                                        <i class="bi bi-check-circle text-success"></i> Subido
                                                                        @if($evaluado->resultado_final_at)
                                                                            <small class="text-muted">{{ $evaluado->resultado_final_at->format('d/m/Y H:i') }}</small>
                                                                        @endif
                                                                    </div>
                                                                    <div>
                                                                        <a href="{{ route('evaluados.descargar-resultado-archivo', [$evaluado->id, 'final']) }}"
                                                                           class="btn btn-sm btn-outline-success" title="Descargar">
                                                                            <i class="bi bi-download"></i>
                                                                        </a>
                                                                        @if(Auth::user()->role_as >= 3)
                                                                        <form action="{{ route('evaluados.eliminar-resultado-archivo', [$evaluado->id, 'final']) }}"
                                                                              method="POST" class="d-inline"
                                                                              onsubmit="return confirm('¿Eliminar informe final? Esto permite reemplazarlo.')">
                                                                            @csrf
                                                                            @method('DELETE')
                                                                            <button class="btn btn-sm btn-outline-danger" title="Eliminar (solo admin)">
                                                                                <i class="bi bi-trash"></i>
                                                                            </button>
                                                                        </form>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            @else
                                                                @if(Auth::user()->role_as >= 2)
                                                                <form action="{{ route('evaluados.subir-resultado-archivo', $evaluado->id) }}"
                                                                      method="POST" enctype="multipart/form-data" class="d-flex gap-2 align-items-end">
                                                                    @csrf
                                                                    <input type="hidden" name="tipo_resultado" value="final">
                                                                    <input type="file" name="archivo" class="form-control form-control-sm" accept=".pdf,.doc,.docx" capture="environment" required>
                                                                    <button type="submit" class="btn btn-sm btn-success">
                                                                        <i class="bi bi-upload"></i>
                                                                    </button>
                                                                </form>
                                                                @else
                                                                    <span class="text-muted"><i class="bi bi-dash-circle"></i> No disponible</span>
                                                                @endif
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Informe Preliminar (Editor de texto enriquecido) --}}
                                        @if(Auth::user()->role_as >= 2 || ($orden->resultados_visibles_empresa && $evaluado->texto_informe_preliminar))
                                        <div class="card border-info mt-3">
                                            <div class="card-header bg-info bg-opacity-10 py-2">
                                                <h6 class="mb-0 text-info">
                                                    <i class="bi bi-file-earmark-text"></i> Informe Preliminar / Observaciones
                                                    @if($evaluado->texto_informe_preliminar)
                                                        <span class="badge bg-success ms-2">Redactado</span>
                                                    @else
                                                        <span class="badge bg-secondary ms-2">Sin redactar</span>
                                                    @endif
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                @if(Auth::user()->role_as >= 2)
                                                    {{-- Editor para admin/repro --}}
                                                    <form action="{{ route('evaluados.guardar-informe-preliminar', $evaluado->id) }}"
                                                          method="POST" class="informe-preliminar-form">
                                                        @csrf
                                                        @method('PATCH')
                                                        <div id="editor-preliminar-{{ $evaluado->id }}" style="min-height: 150px; max-height: 400px; overflow-y: auto;">
                                                            {!! $evaluado->texto_informe_preliminar !!}
                                                        </div>
                                                        <input type="hidden" name="texto_informe_preliminar"
                                                               id="hidden-preliminar-{{ $evaluado->id }}"
                                                               value="{{ $evaluado->texto_informe_preliminar }}">
                                                        <div class="mt-2 d-flex justify-content-end">
                                                            <button type="submit" class="btn btn-sm btn-info text-white">
                                                                <i class="bi bi-save"></i> Guardar informe
                                                            </button>
                                                        </div>
                                                    </form>
                                                @else
                                                    {{-- Vista de solo lectura para empresa --}}
                                                    @if($evaluado->texto_informe_preliminar)
                                                        <div class="border rounded p-3 bg-light">
                                                            {!! $evaluado->texto_informe_preliminar !!}
                                                        </div>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                        @endif

                                    </div>

                                    {{-- Modal Programar/Reprogramar cita --}}
                                    @if(Auth::user()->role_as >= 2)
                                    <div class="modal fade" id="modalProgramarEv{{ $evaluado->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form action="{{ $evaluado->fecha_programada ? route('calendario.reprogramar', $evaluado) : route('calendario.programar') }}" method="POST">
                                                    @csrf
                                                    @if($evaluado->fecha_programada)
                                                        @method('PATCH')
                                                    @endif
                                                    <input type="hidden" name="evaluado_orden_id" value="{{ $evaluado->id }}">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">
                                                            <i class="bi bi-calendar-plus"></i>
                                                            {{ $evaluado->fecha_programada ? 'Reprogramar' : 'Programar' }} cita — {{ $evaluado->nombre }} {{ $evaluado->apellidos }}
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        @php
                                                            $fechaModal = old('fecha', $evaluado->fecha_programada
                                                                ? \Carbon\Carbon::parse($evaluado->fecha_programada)->format('Y-m-d')
                                                                : now()->format('Y-m-d'));
                                                            $horaInicioModal = old('hora_inicio', $evaluado->fecha_programada
                                                                ? \Carbon\Carbon::parse($evaluado->fecha_programada)->format('H:i')
                                                                : '09:00');
                                                            $horaFinModal = old('hora_fin', $evaluado->fecha_hora_fin
                                                                ? \Carbon\Carbon::parse($evaluado->fecha_hora_fin)->format('H:i')
                                                                : '10:00');
                                                            $sedeModal = old('sede_id', $evaluado->sede_id ?? $orden->sede_id);
                                                            $poligrafistaModal = old('poligrafista_id', $evaluado->poligrafista_id ?? (Auth::user()->role_as >= 2 ? Auth::id() : null));
                                                            $responsableModal = old('responsable_id', $evaluado->responsable_id);
                                                        @endphp
                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">Fecha</label>
                                                            <input type="date" name="fecha" class="form-control" required
                                                                   min="{{ now()->format('Y-m-d') }}"
                                                                   value="{{ $fechaModal }}">
                                                        </div>
                                                        <div class="row mb-3">
                                                            <div class="col-6">
                                                                <label class="form-label fw-bold">Hora inicio</label>
                                                                <select name="hora_inicio" class="form-select hora-inicio-programar" required>
                                                                    @for($h = 8; $h < 18; $h++)
                                                                        @for($m = 0; $m < 60; $m += 30)
                                                                            @php $horaOpt = sprintf('%02d:%02d', $h, $m); @endphp
                                                                            <option value="{{ $horaOpt }}"
                                                                                {{ $horaInicioModal == $horaOpt ? 'selected' : '' }}>
                                                                                {{ \Carbon\Carbon::parse($horaOpt)->format('h:i A') }}
                                                                            </option>
                                                                        @endfor
                                                                    @endfor
                                                                </select>
                                                            </div>
                                                            <div class="col-6">
                                                                <label class="form-label fw-bold">Hora fin</label>
                                                                <select name="hora_fin" class="form-select hora-fin-programar" required>
                                                                    @for($h = 8; $h <= 18; $h++)
                                                                        @for($m = 0; $m < 60; $m += 30)
                                                                            @php
                                                                                $horaOpt = sprintf('%02d:%02d', $h, $m);
                                                                                if ($h == 18 && $m > 0) continue;
                                                                            @endphp
                                                                            <option value="{{ $horaOpt }}"
                                                                                {{ $horaFinModal == $horaOpt ? 'selected' : '' }}>
                                                                                {{ \Carbon\Carbon::parse($horaOpt)->format('h:i A') }}
                                                                            </option>
                                                                        @endfor
                                                                    @endfor
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">Sede</label>
                                                            <select name="sede_id" class="form-select" required>
                                                                <option value="">Seleccionar sede...</option>
                                                                @foreach($sedes as $sede)
                                                                    <option value="{{ $sede->id }}" {{ (string) $sedeModal === (string) $sede->id ? 'selected' : '' }}>
                                                                        {{ $sede->nombre }} (Cap: {{ $sede->capacidad ?? 1 }})
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">Modalidad</label>
                                                            @php
                                                                $modalidadDefault = old('modalidad', $evaluado->modalidad);
                                                                if (!$modalidadDefault) {
                                                                    $modalidadDefault = match($evaluado->tipo_servicio) {
                                                                        'poligrafo' => 'presencial',
                                                                        'vsa' => 'virtual',
                                                                        default => null,
                                                                    };
                                                                }
                                                            @endphp
                                                            <select name="modalidad" class="form-select">
                                                                <option value="">Sin definir</option>
                                                                <option value="presencial" {{ $modalidadDefault == 'presencial' ? 'selected' : '' }}>Presencial</option>
                                                                <option value="virtual" {{ $modalidadDefault == 'virtual' ? 'selected' : '' }}>Virtual</option>
                                                            </select>
                                                            <small class="text-muted">La modalidad se guarda al guardar la orden o al programar la cita.</small>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">Poligrafista / Evaluador</label>
                                                            <select name="poligrafista_id" class="form-select">
                                                                <option value="">Sin asignar</option>
                                                                @foreach($poligrafistas as $pol)
                                                                    <option value="{{ $pol->id }}" {{ (string) $poligrafistaModal === (string) $pol->id ? 'selected' : '' }}>
                                                                        {{ $pol->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">Responsable del Proceso</label>
                                                            <select name="responsable_id" class="form-select">
                                                                <option value="">Sin asignar</option>
                                                                @foreach($poligrafistas as $pol)
                                                                    <option value="{{ $pol->id }}" {{ (string) $responsableModal === (string) $pol->id ? 'selected' : '' }}>
                                                                        {{ $pol->name }} {{ $pol->cargo ? '('.$pol->cargo.')' : '' }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                        <button type="submit" class="btn btn-success">
                                                            <i class="bi bi-check-circle"></i> {{ $evaluado->fecha_programada ? 'Reprogramar' : 'Programar' }}
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    @endif

                                </div>
                            </div>
                            @endforeach
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
<link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">
<style>
/* Forzar tamaños correctos de los iconos Quill (conflicto con CSS del template) */
.ql-toolbar.ql-snow {
    border: 1px solid #ced4da;
    border-radius: 4px 4px 0 0;
    padding: 6px 8px;
}
.ql-container.ql-snow {
    border: 1px solid #ced4da;
    border-top: none;
    border-radius: 0 0 4px 4px;
    font-family: inherit;
    font-size: 14px;
}
.ql-snow .ql-editor {
    min-height: 150px;
    max-height: 400px;
    overflow-y: auto;
}
.ql-snow .ql-toolbar button,
.ql-snow.ql-toolbar button {
    width: 28px !important;
    height: 24px !important;
    padding: 3px 5px !important;
    display: inline-block !important;
    background: transparent;
    border: none;
}
.ql-snow .ql-toolbar button svg,
.ql-snow.ql-toolbar button svg {
    width: 18px !important;
    height: 18px !important;
    float: none !important;
}
.ql-snow .ql-picker {
    height: 24px !important;
    font-size: 14px;
}
.ql-snow .ql-picker-label {
    padding-left: 8px;
    padding-right: 2px;
}
.ql-snow .ql-picker-label svg,
.ql-snow .ql-picker-options svg {
    width: 18px !important;
    height: 18px !important;
}
.ql-snow .ql-stroke {
    stroke: #444;
    stroke-width: 2;
    fill: none;
}
.ql-snow .ql-fill,
.ql-snow .ql-stroke.ql-fill {
    fill: #444;
}
.ql-snow.ql-toolbar button:hover .ql-stroke,
.ql-snow .ql-toolbar button:hover .ql-stroke,
.ql-snow.ql-toolbar button.ql-active .ql-stroke,
.ql-snow .ql-toolbar button.ql-active .ql-stroke {
    stroke: #06c;
}
.ql-snow.ql-toolbar button:hover .ql-fill,
.ql-snow .ql-toolbar button:hover .ql-fill,
.ql-snow.ql-toolbar button.ql-active .ql-fill,
.ql-snow .ql-toolbar button.ql-active .ql-fill {
    fill: #06c;
}
</style>
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

@push('scripts')
<script>
function confirmarToggleResultados(checkbox) {
    const estaActivo = checkbox.checked;
    const mensaje = estaActivo
        ? '¿Está seguro de ACTIVAR la visibilidad de resultados para la empresa?\n\nLa empresa podrá ver los resultados de los cuestionarios completados.'
        : '¿Está seguro de DESACTIVAR la visibilidad de resultados para la empresa?\n\nLa empresa NO podrá ver los resultados de los cuestionarios.';

    if (confirm(mensaje)) {
        document.getElementById('form-toggle-resultados').submit();
    } else {
        // Revertir el cambio del checkbox
        checkbox.checked = !estaActivo;
    }
}

// Función para expandir/colapsar todos los accordions de evaluados
function toggleAllAccordions(expand) {
    const accordionItems = document.querySelectorAll('#accordionEvaluados .accordion-collapse');
    accordionItems.forEach(function(item) {
        const bsCollapse = new bootstrap.Collapse(item, { toggle: false });
        if (expand) {
            bsCollapse.show();
        } else {
            bsCollapse.hide();
        }
    });
}

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
<script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
<script>
// Inicializar editores Quill para informe preliminar
document.querySelectorAll('[id^="editor-preliminar-"]').forEach(function(editorEl) {
    const evaluadoId = editorEl.id.replace('editor-preliminar-', '');
    const hiddenInput = document.getElementById('hidden-preliminar-' + evaluadoId);

    const quill = new Quill(editorEl, {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ 'header': [2, 3, false] }],
                ['bold', 'italic', 'underline'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['clean']
            ]
        }
    });

    // Restaurar contenido si ya existe
    if (hiddenInput.value) {
        quill.clipboard.dangerouslyPasteHTML(hiddenInput.value);
    }

    // Antes de enviar el form, copiar HTML al input oculto
    const form = editorEl.closest('form.informe-preliminar-form');
    if (form) {
        form.addEventListener('submit', function() {
            hiddenInput.value = quill.root.innerHTML;
        });
    }
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Ajustar hora fin si queda igual o antes que hora inicio (modal programar en orden)
    document.querySelectorAll('.modal').forEach(function(modal) {
        const inicio = modal.querySelector('.hora-inicio-programar');
        const fin = modal.querySelector('.hora-fin-programar');
        if (!inicio || !fin) return;

        function asegurarHoraFinValida() {
            const opciones = Array.from(fin.options).map(function(o) { return o.value; });
            const idxInicio = opciones.indexOf(inicio.value);
            if (idxInicio === -1) return;
            const idxFin = opciones.indexOf(fin.value);
            if (idxFin <= idxInicio && opciones[idxInicio + 1]) {
                fin.value = opciones[idxInicio + 1];
            }
        }

        inicio.addEventListener('change', asegurarHoraFinValida);
        asegurarHoraFinValida();
    });

@if(session('programar_evaluado_id') || session('error') || $errors->any())
    // Limpiar backdrop huérfano tras validación fallida
    document.querySelectorAll('.modal-backdrop').forEach(function(el) { el.remove(); });
    document.body.classList.remove('modal-open');
    document.body.style.removeProperty('overflow');
    document.body.style.removeProperty('padding-right');

    document.querySelectorAll('.modal.show').forEach(function(modal) {
        modal.classList.remove('show');
        modal.style.display = '';
        modal.setAttribute('aria-hidden', 'true');
    });

    const alertEl = document.getElementById('alert-programar-cita');
    if (alertEl) {
        alertEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
@endif
});
</script>
@endpush
