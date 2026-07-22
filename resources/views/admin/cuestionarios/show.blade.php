@extends('layouts.admin')

@section('title', 'Detalle del Cuestionario #' . $cuestionario->id)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            {{-- Header con acciones --}}
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0">
                            <i class="bi bi-clipboard-check"></i> 
                            Cuestionario #{{ $cuestionario->id }}
                        </h3>
                        <small class="text-muted">
                            {{ $cuestionario->evaluadoOrden->nombre }} {{ $cuestionario->evaluadoOrden->apellidos }}
                        </small>
                    </div>
                    <div class="btn-group">
                        <a href="{{ route('admin.cuestionarios.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Volver al Listado
                        </a>
                        <a href="{{ route('admin.cuestionarios.edit', $cuestionario) }}" class="btn btn-primary">
                            <i class="bi bi-pencil"></i> Editar
                        </a>
                        <a href="{{ route('admin.cuestionarios.pdf', $cuestionario) }}" class="btn btn-success" target="_blank">
                            <i class="bi bi-file-earmark-pdf"></i> Generar PDF
                        </a>
                    </div>
                </div>
            </div>
            
            @if(!empty($cambiosPrecarga))
            <div class="card mb-4 border-warning">
                <div class="card-header bg-warning bg-opacity-10">
                    <h6 class="mb-0"><i class="bi bi-pencil-square"></i> Cambios respecto a la orden (precarga)</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Campo</th>
                                    <th>Valor en orden</th>
                                    <th>Valor en formulario</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cambiosPrecarga as $cambio)
                                <tr>
                                    <td>{{ $etiquetasPrecarga[$cambio['campo']] ?? $cambio['campo'] }}</td>
                                    <td class="text-muted">{{ $cambio['valor_orden'] ?: '—' }}</td>
                                    <td><strong>{{ $cambio['valor_actual'] }}</strong></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
            
            {{-- Información general --}}
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                @switch($cuestionario->estado)
                                    @case('pendiente')
                                        <i class="bi bi-clock fs-1 text-warning"></i>
                                        <h5 class="mt-2 text-warning">Pendiente</h5>
                                        @break
                                    @case('en_progreso')
                                        <i class="bi bi-pencil-square fs-1 text-info"></i>
                                        <h5 class="mt-2 text-info">En Progreso</h5>
                                        @break
                                    @case('completado')
                                        <i class="bi bi-check-circle fs-1 text-success"></i>
                                        <h5 class="mt-2 text-success">Completado</h5>
                                        @break
                                @endswitch
                            </div>
                            <div class="progress mb-3" style="height: 25px;">
                                @php $progreso = $cuestionario->calcularProgreso(); @endphp
                                <div class="progress-bar 
                                    @if($progreso < 25) bg-danger 
                                    @elseif($progreso < 75) bg-warning 
                                    @else bg-success @endif" 
                                     role="progressbar" 
                                     style="width: {{ $progreso }}%">
                                    {{ $progreso }}%
                                </div>
                            </div>
                            <p class="mb-0">{{ $cuestionario->seccion_actual }} de {{ $cuestionario->total_secciones }} secciones completadas</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-9">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6><i class="bi bi-person"></i> Información del Evaluado</h6>
                                    <table class="table table-sm">
                                        <tr>
                                            <td><strong>Nombre:</strong></td>
                                            <td>{{ $cuestionario->evaluadoOrden->nombre }} {{ $cuestionario->evaluadoOrden->apellidos }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>DPI:</strong></td>
                                            <td>{{ $cuestionario->evaluadoOrden->dpi }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Email:</strong></td>
                                            <td>{{ $cuestionario->evaluadoOrden->email ?? 'No proporcionado' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Teléfono:</strong></td>
                                            <td>{{ $cuestionario->evaluadoOrden->telefono ?? 'No proporcionado' }}</td>
                                        </tr>
                                    </table>
                                </div>
                                
                                <div class="col-md-6">
                                    <h6><i class="bi bi-building"></i> Información de la Evaluación</h6>
                                    <table class="table table-sm">
                                        <tr>
                                            <td><strong>Empresa:</strong></td>
                                            <td>{{ $cuestionario->evaluadoOrden->orden->empresa->nombre }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Puesto:</strong></td>
                                            <td>{{ $cuestionario->evaluadoOrden->puesto_evaluar ?? 'No especificado' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Orden #:</strong></td>
                                            <td>{{ $cuestionario->evaluadoOrden->orden->id }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Tipo de Servicio:</strong></td>
                                            <td>{{ ucfirst($cuestionario->evaluadoOrden->tipo_servicio) }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <h6><i class="bi bi-calendar3"></i> Fechas Importantes</h6>
                                    <table class="table table-sm">
                                        <tr>
                                            <td><strong>Creado:</strong></td>
                                            <td>{{ $cuestionario->created_at->format('d/m/Y H:i:s') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Última actualización:</strong></td>
                                            <td>{{ $cuestionario->updated_at->format('d/m/Y H:i:s') }}</td>
                                        </tr>
                                        @if($cuestionario->completado_at)
                                            <tr>
                                                <td><strong>Completado:</strong></td>
                                                <td class="text-success">{{ $cuestionario->completado_at->format('d/m/Y H:i:s') }}</td>
                                            </tr>
                                        @endif
                                    </table>
                                </div>
                                
                                <div class="col-md-6">
                                    <h6><i class="bi bi-link-45deg"></i> Acceso del Evaluado</h6>
                                    <table class="table table-sm">
                                        <tr>
                                            <td><strong>Enlace:</strong></td>
                                            <td>
                                                <a href="{{ route('cuestionario.mostrar', $cuestionario->evaluadoOrden->token_unico) }}" 
                                                   target="_blank" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-box-arrow-up-right"></i> Abrir Cuestionario
                                                </a>
                                                <button class="btn btn-sm btn-outline-secondary ms-1" 
                                                        onclick="copiarEnlace('{{ route('cuestionario.mostrar', $cuestionario->evaluadoOrden->token_unico) }}')"
                                                        title="Copiar enlace al portapapeles">
                                                    <i class="bi bi-clipboard"></i> Copiar
                                                </button>
                                            </td>
                                        </tr>
                                        @if($cuestionario->evaluadoOrden->token_expira_at)
                                            @php
                                                $fechaExpira = $cuestionario->evaluadoOrden->token_expira_at;
                                                $diasRestantes = now()->diffInDays($fechaExpira, false);
                                                $yaExpiro = $fechaExpira->isPast();
                                                $cuestionarioCompletado = $cuestionario->completado;
                                            @endphp
                                            <tr>
                                                <td><strong>Expira:</strong></td>
                                                <td>
                                                    @if($cuestionarioCompletado)
                                                        <span class="text-success">
                                                            <i class="bi bi-check-circle"></i> Completado
                                                        </span>
                                                    @elseif($yaExpiro)
                                                        <span class="text-danger">
                                                            <i class="bi bi-x-circle"></i> Expirado el {{ $fechaExpira->format('d/m/Y H:i') }}
                                                        </span>
                                                    @elseif($diasRestantes <= 3)
                                                        <span class="text-danger">
                                                            <i class="bi bi-exclamation-triangle"></i> {{ $fechaExpira->format('d/m/Y H:i') }}
                                                            <small>({{ $diasRestantes }} días restantes)</small>
                                                        </span>
                                                    @elseif($diasRestantes <= 7)
                                                        <span class="text-warning">
                                                            <i class="bi bi-clock"></i> {{ $fechaExpira->format('d/m/Y H:i') }}
                                                            <small>({{ $diasRestantes }} días restantes)</small>
                                                        </span>
                                                    @else
                                                        <span class="text-success">
                                                            <i class="bi bi-clock"></i> {{ $fechaExpira->format('d/m/Y H:i') }}
                                                            <small>({{ $diasRestantes }} días restantes)</small>
                                                        </span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Contenido de las secciones --}}
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-list-ul"></i> Contenido del Cuestionario
                            </h5>
                        </div>
                        <div class="card-body">
                            {{-- Navegación por pestañas --}}
                            <ul class="nav nav-tabs" id="seccionesTabs" role="tablist">
                                @php
                                    $totalSeccionesForm = count($secciones);
                                @endphp
                                @for($i = 1; $i <= $totalSeccionesForm; $i++)
                                    @php
                                        $nombreSeccion = $secciones[$i] ?? 'Sección ' . $i;
                                        $completada = $cuestionario->progreso_secciones[$i] ?? false;
                                    @endphp
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link {{ $i == 1 ? 'active' : '' }} {{ $completada ? 'text-success' : 'text-muted' }}" 
                                                id="seccion{{ $i }}-tab" 
                                                data-bs-toggle="tab" 
                                                data-bs-target="#seccion{{ $i }}" 
                                                type="button" 
                                                role="tab"
                                                title="{{ $nombreSeccion }}">
                                            @if($completada)
                                                <i class="bi bi-check-circle-fill"></i>
                                            @else
                                                <i class="bi bi-circle"></i>
                                            @endif
                                            {{ $i }}. {{ $nombreSeccion }}
                                        </button>
                                    </li>
                                @endfor
                            </ul>
                            
                            {{-- Contenido de las pestañas --}}
                            <div class="tab-content mt-3" id="seccionesTabContent">
                                @for($i = 1; $i <= $totalSeccionesForm; $i++)
                                    <div class="tab-pane fade {{ $i == 1 ? 'show active' : '' }}" 
                                         id="seccion{{ $i }}" 
                                         role="tabpanel">
                                        @if(View::exists('admin.cuestionarios.partials.seccion_' . $i))
                                            @include('admin.cuestionarios.partials.seccion_' . $i, [
                                                'cuestionario' => $cuestionario,
                                                'respuestas' => $cuestionario->obtenerRespuestasSeccion($i),
                                                'tablas' => $cuestionario->getTablasPorNumeroSeccion($i),
                                                'completada' => $cuestionario->progreso_secciones[$i] ?? false,
                                                'nombreSeccion' => $secciones[$i] ?? 'Sección ' . $i,
                                                'fotoCandidatoUrl' => $i === 1 ? ($fotoCandidatoUrl ?? null) : null,
                                            ])
                                        @else
                                            {{-- Vista genérica para secciones sin partial específico --}}
                                            <div class="alert alert-info">
                                                <h6><i class="bi bi-info-circle"></i> {{ $secciones[$i] ?? 'Sección ' . $i }}</h6>
                                                @php $respuestasSeccion = $cuestionario->obtenerRespuestasSeccion($i); @endphp
                                                @if(count($respuestasSeccion) > 0)
                                                    <table class="table table-sm table-striped mt-2">
                                                        @foreach($respuestasSeccion as $campo => $valor)
                                                            <tr>
                                                                <td class="fw-bold" style="width: 30%;">{{ ucfirst(str_replace('_', ' ', $campo)) }}:</td>
                                                                <td>{{ is_array($valor) ? json_encode($valor) : $valor }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </table>
                                                @else
                                                    <p class="text-muted mb-0">No hay datos registrados en esta sección.</p>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                @endfor
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Firma digital --}}
            @if($cuestionario->firma_digital)
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="bi bi-pen"></i> Firma Digital
                                </h5>
                            </div>
                            <div class="card-body text-center">
                                <img src="{{ $cuestionario->firma_digital }}" 
                                     alt="Firma Digital" 
                                     class="img-fluid border rounded p-3" 
                                     style="max-height: 200px; background: white;">
                                <p class="mt-2 text-muted">
                                    Firmado el {{ $cuestionario->completado_at?->format('d/m/Y \a \l\a\s H:i:s') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Documentos del evaluado --}}
            <div class="row mt-4">
                <div class="col-12">
                    @include('admin.ordenes._documentos_evaluado', ['evaluado' => $cuestionario->evaluadoOrden])
                </div>
            </div>
            
            {{-- Observaciones administrativas --}}
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-sticky"></i> Observaciones Administrativas
                            </h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.cuestionarios.update', $cuestionario) }}" method="POST">
                                @csrf
                                @method('PUT')
                                
                                <div class="form-group">
                                    <label for="observaciones_repro" class="form-label">Notas internas</label>
                                    <textarea class="form-control" 
                                              id="observaciones_repro" 
                                              name="observaciones_repro" 
                                              rows="4" 
                                              placeholder="Agregue observaciones, notas o comentarios sobre este cuestionario...">{{ $cuestionario->observaciones_repro }}</textarea>
                                    <small class="form-text text-muted">
                                        Estas observaciones son solo para uso interno de REPRO
                                    </small>
                                </div>
                                
                                @include('admin.cuestionarios.partials.notas-evaluador')

                @include('admin.cuestionarios.partials.tablas-informe-preempleo', ['soloLectura' => true])
                                
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-floppy"></i> Guardar Observaciones
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function copiarEnlace(texto) {
    // Método moderno con fallback
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(texto).then(function() {
            mostrarNotificacion('success', 'Enlace copiado al portapapeles');
        }).catch(function(err) {
            console.error('Error al copiar: ', err);
            copiarFallback(texto);
        });
    } else {
        // Fallback para contextos no seguros (HTTP)
        copiarFallback(texto);
    }
}

function copiarFallback(texto) {
    const textArea = document.createElement('textarea');
    textArea.value = texto;
    textArea.style.position = 'fixed';
    textArea.style.left = '-999999px';
    textArea.style.top = '-999999px';
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
        const exitoso = document.execCommand('copy');
        if (exitoso) {
            mostrarNotificacion('success', 'Enlace copiado al portapapeles');
        } else {
            mostrarNotificacion('error', 'No se pudo copiar el enlace');
        }
    } catch (err) {
        console.error('Error al copiar: ', err);
        mostrarNotificacion('error', 'Error al copiar el enlace');
    }
    
    document.body.removeChild(textArea);
}

function mostrarNotificacion(tipo, mensaje) {
    if (typeof Swal !== 'undefined') {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
        
        Toast.fire({
            icon: tipo,
            title: mensaje
        });
    } else {
        alert(mensaje);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Mejorar navegación por pestañas
    const tabs = document.querySelectorAll('#seccionesTabs button[data-bs-toggle="tab"]');
    tabs.forEach(tab => {
        tab.addEventListener('shown.bs.tab', function(event) {
            // Opcional: trackear qué sección está viendo el admin
            const seccionId = event.target.getAttribute('data-bs-target');
            console.log('Visualizando: ' + seccionId);
        });
    });
});
</script>
@endpush

@push('styles')
<style>
.nav-tabs .nav-link.text-success {
    border-color: #28a745;
}

.nav-tabs .nav-link.active.text-success {
    color: #28a745 !important;
    border-bottom-color: #28a745;
}

.table-sm td {
    border-top: none;
    padding: 0.3rem;
}

.progress {
    border-radius: 10px;
}

code {
    font-size: 0.9em;
    padding: 0.2rem 0.4rem;
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 0.25rem;
}
</style>
@endpush