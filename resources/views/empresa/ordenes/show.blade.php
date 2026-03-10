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
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="card-title">Detalles de la Orden</span>
                <a href="{{ route('ordenes.pdf', $orden) }}" class="btn btn-danger btn-sm" target="_blank">
                    <i class="bi bi-file-pdf"></i> PDF
                </a>
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
                            <span class="badge fs-6 bg-success">{{ $orden->estado }}</span>
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
            <div class="card-title p-3">Evaluados Asignados ({{ $orden->evaluados->count() }})</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>DPI</th>
                                <th>Servicio/Formulario</th>
                                <th>Contacto</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orden->evaluados as $evaluado)
                            <tr>
                                <td><strong>{{ $evaluado->nombre }}</strong></td>
                                <td><code>{{ $evaluado->dpi }}</code></td>
                                <td>
                                    <span class="badge bg-primary">{{ $evaluado->tipo_servicio }}</span><br>
                                    <small class="text-muted">{{ $evaluado->tipo_formulario }}</small>
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
                                    <span class="badge bg-success">{{ ucfirst($evaluado->estado_evaluacion ?? 'pendiente') }}</span>
                                    @if($evaluado->cuestionario_completado)
                                        <br><small class="text-muted">{{ $evaluado->completado_at ? \Carbon\Carbon::parse($evaluado->completado_at)->format('d/m/Y H:i') : '' }}</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        @if($orden->estado === 'entregado' && $orden->resultados_visibles_empresa)
                                            <a href="{{ route('empresa.cuestionarios.show', $evaluado) }}" class="btn btn-outline-success btn-sm" title="Ver Cuestionario">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('empresa.cuestionarios.pdf', $evaluado) }}" class="btn btn-outline-primary btn-sm" title="Descargar PDF del Cuestionario" target="_blank">
                                                <i class="bi bi-file-earmark-pdf"></i>
                                            </a>
                                        @endif
                                        @if(!$evaluado->cuestionario_completado)
                                            <a href="{{ route('cuestionario.mostrar', $evaluado->token_unico) }}"
                                               class="btn btn-outline-primary btn-sm"
                                               title="Enlace del Evaluado"
                                               target="_blank">
                                                <i class="bi bi-link-45deg"></i>
                                            </a>
                                            <button type="button"
                                                    class="btn btn-outline-secondary btn-sm"
                                                    onclick="copiarEnlaceEvaluado('{{ route('cuestionario.mostrar', $evaluado->token_unico) }}')"
                                                    title="Copiar enlace al portapapeles">
                                                <i class="bi bi-clipboard"></i>
                                            </button>
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
@endsection

@push('scripts')
<script>
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
