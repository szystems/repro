@extends('layouts.empresa')
@section('content')

<!-- Content wrapper scroll start -->
<div class="content-wrapper-scroll">

    <!-- Main header starts -->
    <div class="main-header d-flex align-items-center justify-content-between position-relative">
        <div class="d-flex align-items-center justify-content-center">
            <div class="page-icon">
                <i class="bi bi-clipboard-data"></i>
            </div>
            <div class="page-title">
                <h5>Detalle de Cuestionario</h5>
            </div>
        </div>
        <div class="d-flex align-items-end d-none d-sm-block">
            <h6 class="float-end text-light" id="reloj"></h6>
        </div>
    </div>
    <!-- Main header ends -->

    <!-- Content wrapper start -->
    <div class="content-wrapper">

        <div class="row gx-3 mb-3">
            <div class="col-12">
                <a href="{{ route('empresa.cuestionarios') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Volver al listado
                </a>
            </div>
        </div>

        <div class="row gx-3">
            <!-- Información del evaluado -->
            <div class="col-lg-6">
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="bi bi-person text-success"></i> Información del Evaluado</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted small">Nombre:</label>
                                <div class="fw-medium">{{ $evaluado->nombre }} {{ $evaluado->apellidos }}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted small">DPI:</label>
                                <div class="fw-medium">{{ $evaluado->dpi ?? 'No registrado' }}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted small">Email:</label>
                                <div class="fw-medium">
                                    <a href="mailto:{{ $evaluado->email }}">{{ $evaluado->email }}</a>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted small">Teléfono:</label>
                                <div class="fw-medium">{{ $evaluado->telefono ?? 'No registrado' }}</div>
                            </div>
                            @if($evaluado->puesto_aplicar)
                            <div class="col-12 mb-3">
                                <label class="form-label text-muted small">Puesto al que aplica:</label>
                                <div class="fw-medium">{{ $evaluado->puesto_aplicar }}</div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estado del cuestionario -->
            <div class="col-lg-6">
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="bi bi-clipboard-check text-success"></i> Estado del Cuestionario</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted small">Estado del cuestionario:</label>
                                <div>
                                    @if($evaluado->cuestionario_completado)
                                        <span class="badge bg-success p-2">
                                            <i class="bi bi-check-circle"></i> Completado
                                        </span>
                                    @else
                                        <span class="badge bg-warning text-dark p-2">
                                            <i class="bi bi-clock"></i> Pendiente
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted small">Orden:</label>
                                <div class="fw-medium">
                                    <a href="{{ route('ordenes.show', $evaluado->orden) }}" class="text-decoration-none">
                                        {{ $evaluado->orden->codigo_orden ?? '#'.$evaluado->orden_id }}
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted small">Tipo de Servicio:</label>
                                <div>
                                    <span class="badge bg-info">{{ $evaluado->tipo_servicio ?? 'N/A' }}</span>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted small">Formulario:</label>
                                <div>
                                    <span class="badge bg-secondary">{{ $evaluado->tipo_formulario ?? 'N/A' }}</span>
                                </div>
                            </div>
                            @if($evaluado->cuestionario_completado && $evaluado->cuestionario_completado_at)
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted small">Completado el:</label>
                                <div class="fw-medium">{{ \Carbon\Carbon::parse($evaluado->cuestionario_completado_at)->format('d/m/Y H:i') }}</div>
                            </div>
                            @endif
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted small">Token expira:</label>
                                <div class="fw-medium">
                                    @if($evaluado->token_expira_at)
                                        {{ \Carbon\Carbon::parse($evaluado->token_expira_at)->format('d/m/Y H:i') }}
                                        @if(\Carbon\Carbon::parse($evaluado->token_expira_at)->isPast())
                                            <span class="badge bg-danger ms-1">Expirado</span>
                                        @endif
                                    @else
                                        Sin expiración
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información adicional si está completado -->
            @if($evaluado->cuestionario_completado)
                @if($evaluado->resultadosDisponiblesParaEmpresa())
                <div class="col-12">
                    <div class="card mb-3">
                        <div class="card-header bg-success-subtle">
                            <h6 class="card-title mb-0 text-success"><i class="bi bi-check-circle"></i> Cuestionario Completado - Resultados Disponibles</h6>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-success mb-3">
                                <i class="bi bi-check-circle-fill"></i>
                                <strong>¡Los resultados están disponibles!</strong><br>
                                El evaluado ha completado exitosamente el cuestionario y los resultados han sido aprobados para su visualización.
                            </div>
                            <div class="d-flex gap-2 flex-wrap mb-3">
                                <a href="{{ route('empresa.ordenes.show', $evaluado->orden) }}" class="btn btn-primary">
                                    <i class="bi bi-eye"></i> Ver Orden Completa
                                </a>
                                <a href="{{ route('ordenes.pdf', $evaluado->orden) }}" class="btn btn-danger" target="_blank">
                                    <i class="bi bi-file-pdf"></i> Descargar PDF de la Orden
                                </a>
                                @if($evaluado->cuestionario)
                                <a href="{{ route('empresa.cuestionarios.pdf', $evaluado) }}" class="btn btn-danger" target="_blank">
                                    <i class="bi bi-file-earmark-pdf"></i> Descargar PDF del Cuestionario
                                </a>
                                @endif
                            </div>

                            @php
                                $cuestionario = $evaluado->cuestionario;
                                $secciones = $cuestionario && method_exists($cuestionario, 'getSeccionesConfig') ? $cuestionario->getSeccionesConfig() : [];
                            @endphp
                            @if($cuestionario && $secciones)
                            <ul class="nav nav-tabs" id="seccionesTabs" role="tablist">
                                @foreach($secciones as $i => $nombreSeccion)
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link {{ $loop->first ? 'active' : '' }}" id="seccion{{ $i }}-tab" data-bs-toggle="tab" data-bs-target="#seccion{{ $i }}" type="button" role="tab" title="{{ $nombreSeccion }}">
                                            {{ $i }}. {{ $nombreSeccion }}
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                            <div class="tab-content mt-3" id="seccionesTabContent">
                                @foreach($secciones as $i => $nombreSeccion)
                                    <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="seccion{{ $i }}" role="tabpanel">
                                        @include('shared.cuestionario.seccion-lectura', [
                                            'cuestionario' => $cuestionario,
                                            'numeroSeccion' => $i,
                                            'nombreSeccion' => $nombreSeccion,
                                        ])
                                    </div>
                                @endforeach
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @else
                <div class="col-12">
                    <div class="card mb-3">
                        <div class="card-header bg-secondary">
                            <h6 class="card-title mb-0 text-white"><i class="bi bi-lock"></i> Resultados en Proceso</h6>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-secondary mb-0">
                                <i class="bi bi-hourglass-split"></i>
                                <strong>El cuestionario ha sido completado.</strong><br>
                                @if($evaluado->orden->estado !== 'entregado')
                                    La orden aún está en proceso. Los resultados estarán disponibles cuando la orden sea entregada.
                                @else
                                    Los resultados están siendo revisados por el equipo de REPRO y estarán disponibles próximamente.
                                @endif
                                <hr class="my-2">
                                <small class="text-muted">
                                    <i class="bi bi-info-circle"></i> Si necesita los resultados con urgencia, por favor contacte a su ejecutivo de cuenta.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            @else
            <div class="col-12">
                <div class="card mb-3">
                    <div class="card-header bg-warning-subtle">
                        <h6 class="card-title mb-0 text-warning"><i class="bi bi-clock"></i> Cuestionario Pendiente</h6>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning mb-0">
                            <i class="bi bi-exclamation-triangle"></i>
                            El evaluado aún no ha completado el cuestionario.
                            @if($evaluado->correo_enviado)
                                El correo con el enlace fue enviado el {{ \Carbon\Carbon::parse($evaluado->correo_enviado_at)->format('d/m/Y H:i') }}.
                            @else
                                El correo con el enlace no ha sido enviado.
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

    </div>
    <!-- Content wrapper end -->

</div>
<!-- Content wrapper scroll end -->

@endsection
