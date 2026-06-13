@extends('layouts.empresa')
@section('content')
<div class="content-wrapper-scroll">
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
    <div class="content-wrapper">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> {{ session('success') }}
                @if(session('mostrar_papeleria'))
                    <hr>
                    <strong>Próximo paso:</strong>
                    Suba ahora la papelería de cada evaluado en la sección
                    <a href="#seccion-evaluados" class="alert-link">Evaluados Asignados</a>.
                @endif
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="card-title">Detalles de la Orden</span>
                @if(Auth::user()->hasPermission('ordenes.editar') && $orden->estado === 'orden_recibida')
                <a href="{{ route('ordenes.edit', $orden) }}" class="btn btn-warning btn-sm me-1">
                    <i class="bi bi-pencil"></i> Editar
                </a>
                @endif
                <a href="{{ route('ordenes.pdf', $orden) }}" class="btn btn-danger btn-sm me-1" target="_blank">
                    <i class="bi bi-file-pdf"></i> Orden de Servicio
                </a>
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
                            <span class="badge fs-6 bg-{{ $orden->estado_color }}">{{ $orden->estado_human }}</span>
                        </div>
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
                            @php $tiposUnicos = $orden->evaluados->pluck('tipo_servicio')->unique(); @endphp
                            @foreach($tiposUnicos as $tipo)
                                <span class="badge me-1 bg-primary">{{ $tipo }}</span>
                            @endforeach
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Tipos de Formulario</label>
                        <div>
                            @php $formulariosUnicos = $orden->evaluados->pluck('tipo_formulario')->unique(); @endphp
                            @foreach($formulariosUnicos as $formulario)
                                <span class="badge me-1 bg-secondary">{{ $formulario }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-title p-3" id="seccion-evaluados">Evaluados Asignados ({{ $orden->evaluados->count() }})</div>
            <div class="card-body">
                @foreach($orden->evaluados as $evaluado)
                <div class="border rounded p-3 mb-3">
                    <div class="row align-items-center">
                        <div class="col-md-2">
                            <strong>{{ $evaluado->nombre }} {{ $evaluado->apellidos }}</strong>
                            <br><code>{{ $evaluado->dpi }}</code>
                        </div>
                        <div class="col-md-2">
                            <span class="badge bg-primary">{{ $evaluado->tipo_servicio }}</span>
                            <br><small class="text-muted">{{ $evaluado->tipo_formulario }}</small>
                        </div>
                        <div class="col-md-2">
                            @if($evaluado->puesto_evaluar)
                                <small><i class="bi bi-briefcase"></i> {{ $evaluado->puesto_evaluar }}</small><br>
                            @endif
                            @if($evaluado->sede_region_empresa)
                                <small><i class="bi bi-building"></i> {{ $evaluado->sede_region_empresa }}</small><br>
                            @endif
                            @if($evaluado->sede)
                                <small><i class="bi bi-geo-alt"></i> {{ $evaluado->sede->nombre }}</small>
                            @endif
                            @if(!$evaluado->puesto_evaluar && !$evaluado->sede_region_empresa && !$evaluado->sede)
                                <small class="text-muted">—</small>
                            @endif
                        </div>
                        <div class="col-md-2">
                            @if($evaluado->email)
                                <small><i class="bi bi-envelope"></i> {{ $evaluado->email }}</small><br>
                            @endif
                            @if($evaluado->telefono)
                                <small><i class="bi bi-telephone"></i> {{ $evaluado->telefono }}</small><br>
                            @endif
                            @if($evaluado->direccion)
                                <small><i class="bi bi-geo-alt"></i> {{ $evaluado->direccion }}</small>
                            @endif
                        </div>
                        <div class="col-md-2">
                            <span class="badge bg-{{ $evaluado->estado_evaluacion_color }}">{{ $evaluado->estado_evaluacion_texto }}</span>
                            <br><span class="badge bg-{{ $evaluado->estado_formulario_color }}">Form: {{ ucfirst($evaluado->estado_formulario ?? 'pendiente') }}</span>
                            @if($evaluado->cuestionario_completado)
                                <br><small class="text-muted">{{ $evaluado->completado_at ? \Carbon\Carbon::parse($evaluado->completado_at)->format('d/m/Y H:i') : '' }}</small>
                            @endif
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="btn-group" role="group">
                                @if($orden->resultados_visibles_empresa)
                                    @if($evaluado->archivo_resultado_final)
                                        <a href="{{ route('evaluados.descargar-resultado-archivo', [$evaluado, 'final']) }}" class="btn btn-success btn-sm" title="Descargar Informe Final" target="_blank">
                                            <i class="bi bi-file-earmark-arrow-down"></i> Informe Final
                                        </a>
                                    @endif
                                    @if($evaluado->archivo_resultado_preliminar)
                                        <a href="{{ route('evaluados.descargar-resultado-archivo', [$evaluado, 'preliminar']) }}" class="btn btn-outline-info btn-sm" title="Descargar Informe Preliminar" target="_blank">
                                            <i class="bi bi-file-earmark-arrow-down"></i> Preliminar
                                        </a>
                                    @endif
                                    @if($evaluado->cuestionario_completado)
                                        <a href="{{ route('empresa.cuestionarios.show', $evaluado) }}" class="btn btn-outline-success btn-sm" title="Ver Cuestionario">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('empresa.cuestionarios.pdf', $evaluado) }}" class="btn btn-outline-primary btn-sm" title="Descargar PDF Cuestionario" target="_blank">
                                            <i class="bi bi-file-earmark-pdf"></i>
                                        </a>
                                    @endif
                                @endif
                                @if(!$evaluado->cuestionario_completado)
                                    <a href="{{ route('cuestionario.mostrar', $evaluado->token_unico) }}"
                                       class="btn btn-outline-primary btn-sm" title="Enlace del Evaluado" target="_blank">
                                        <i class="bi bi-link-45deg"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-secondary btn-sm"
                                            onclick="copiarEnlaceEvaluado('{{ route('cuestionario.mostrar', $evaluado->token_unico) }}')"
                                            title="Copiar enlace">
                                        <i class="bi bi-clipboard"></i>
                                    </button>
                                    @if($evaluado->email)
                                        <form action="{{ route('evaluados.reenviar-correo', $evaluado) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('¿Reenviar enlace a {{ $evaluado->email }}?')">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-warning btn-sm" title="Reenviar enlace">
                                                <i class="bi bi-envelope-arrow-up"></i>
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                    @if($evaluado->observaciones)
                        <div class="mt-1"><small class="text-info"><i class="bi bi-chat-left-text"></i> {{ $evaluado->observaciones }}</small></div>
                    @endif

                    {{-- Informes disponibles para empresa --}}
                    @if($orden->resultados_visibles_empresa)
                        {{-- Informe Final (texto del resultado definitivo) --}}
                        @if($evaluado->archivo_resultado_final)
                        <div class="card border-success mt-2">
                            <div class="card-header bg-success bg-opacity-10 py-2">
                                <h6 class="mb-0 text-success">
                                    <i class="bi bi-file-earmark-check"></i> Informe Final
                                    <span class="badge bg-success ms-2">Disponible</span>
                                </h6>
                            </div>
                            <div class="card-body py-2">
                                <a href="{{ route('evaluados.descargar-resultado-archivo', [$evaluado, 'final']) }}" class="btn btn-success btn-sm" target="_blank">
                                    <i class="bi bi-download"></i> Descargar Informe Final
                                </a>
                            </div>
                        </div>
                        @endif

                        {{-- Informe Preliminar / Observaciones (texto enriquecido) --}}
                        @if($evaluado->texto_informe_preliminar)
                        <div class="card border-info mt-2">
                            <div class="card-header bg-info bg-opacity-10 py-2">
                                <h6 class="mb-0 text-info">
                                    <i class="bi bi-file-earmark-text"></i> Informe Preliminar / Observaciones
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="border rounded p-3 bg-light">
                                    {!! $evaluado->texto_informe_preliminar !!}
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- Archivo Preliminar (PDF descargable si existe) --}}
                        @if(!$evaluado->archivo_resultado_final && $evaluado->archivo_resultado_preliminar)
                        <div class="card border-info mt-2">
                            <div class="card-header bg-info bg-opacity-10 py-2">
                                <h6 class="mb-0 text-info">
                                    <i class="bi bi-file-earmark-arrow-down"></i> Informe Preliminar
                                </h6>
                            </div>
                            <div class="card-body py-2">
                                <a href="{{ route('evaluados.descargar-resultado-archivo', [$evaluado, 'preliminar']) }}" class="btn btn-outline-info btn-sm" target="_blank">
                                    <i class="bi bi-download"></i> Descargar Preliminar
                                </a>
                            </div>
                        </div>
                        @endif
                    @endif

                    @if($historialVisibleEmpresa ?? false)
                        @include('partials._historial_estados_evaluado', ['evaluado' => $evaluado, 'paraEmpresa' => true])
                    @endif

                    {{-- Sección de documentos/papelería --}}
                    @include('empresa.ordenes._documentos_evaluado', ['evaluado' => $evaluado])
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
@if(session('mostrar_papeleria'))
    // Scroll automático a la sección de evaluados tras crear la orden
    document.addEventListener('DOMContentLoaded', function () {
        const seccion = document.getElementById('seccion-evaluados');
        if (seccion) {
            setTimeout(() => seccion.scrollIntoView({ behavior: 'smooth', block: 'start' }), 600);
        }
    });
@endif

function copiarEnlaceEvaluado(url) {
    function mostrarExito() {
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
    }

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(url).then(mostrarExito).catch(function() {
            copiarFallback(url);
        });
    } else {
        copiarFallback(url);
    }
}

function copiarFallback(url) {
    const textarea = document.createElement('textarea');
    textarea.value = url;
    textarea.style.position = 'fixed';
    textarea.style.opacity = '0';
    document.body.appendChild(textarea);
    textarea.select();
    try {
        document.execCommand('copy');
        const toast = document.createElement('div');
        toast.className = 'position-fixed bottom-0 end-0 p-3';
        toast.style.zIndex = '9999';
        toast.innerHTML = `
            <div class="toast show" role="alert">
                <div class="toast-header bg-success text-white">
                    <i class="bi bi-check-circle me-2"></i>
                    <strong class="me-auto">Enlace copiado</strong>
                </div>
                <div class="toast-body">El enlace ha sido copiado al portapapeles.</div>
            </div>
        `;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    } catch (err) {
        prompt('Copie este enlace manualmente:', url);
    }
    document.body.removeChild(textarea);
}
</script>
@endpush
