@extends('layouts.admin')

@section('title', 'Editar Cuestionario #' . $cuestionario->id)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            {{-- Header --}}
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0">
                            <i class="bi bi-pencil"></i> 
                            Editar Cuestionario #{{ $cuestionario->id }}
                            @if($cuestionario->estado == 'completado')
                                <span class="badge bg-warning ms-2">
                                    <i class="bi bi-exclamation-triangle"></i> COMPLETADO - Edición para Correcciones
                                </span>
                            @endif
                        </h3>
                        <small class="text-muted">
                            {{ $cuestionario->evaluadoOrden->nombre }} {{ $cuestionario->evaluadoOrden->apellidos }}
                        </small>
                        @if($cuestionario->estado == 'completado')
                            <div class="alert alert-warning alert-sm mt-2 mb-0" role="alert">
                                <i class="bi bi-info-circle"></i>
                                <strong>Modo Corrección:</strong> Este cuestionario está completado. Edite solo para corregir errores ortográficos o información incorrecta antes de generar informes.
                            </div>
                        @endif
                    </div>
                    <div class="btn-group">
                        <a href="{{ route('admin.cuestionarios.show', $cuestionario) }}" class="btn btn-outline-secondary">
                            <i class="bi bi-eye"></i> Ver Detalle
                        </a>
                        <a href="{{ route('admin.cuestionarios.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Volver al Listado
                        </a>
                    </div>
                </div>
            </div>
            
            <form action="{{ route('admin.cuestionarios.update', $cuestionario) }}" 
                  method="POST" 
                  id="formEditarCuestionario"
                  enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                {{-- Progreso y Estado --}}
                <div class="row mb-4">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Estado del Cuestionario</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="estado" class="form-label">Estado del cuestionario</label>
                                            <select class="form-control @error('estado') is-invalid @enderror" 
                                                    id="estado" 
                                                    name="estado">
                                                <option value="pendiente" {{ $cuestionario->estado == 'pendiente' ? 'selected' : '' }}>
                                                    Pendiente
                                                </option>
                                                <option value="en_progreso" {{ $cuestionario->estado == 'en_progreso' ? 'selected' : '' }}>
                                                    En Progreso
                                                </option>
                                                <option value="completado" {{ $cuestionario->estado == 'completado' ? 'selected' : '' }}>
                                                    Completado
                                                </option>
                                            </select>
                                            @error('estado')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="completado_at" class="form-label">Fecha de Completado</label>
                                            <input type="datetime-local" 
                                                   class="form-control @error('completado_at') is-invalid @enderror" 
                                                   id="completado_at" 
                                                   name="completado_at"
                                                   value="{{ $cuestionario->completado_at ? $cuestionario->completado_at->format('Y-m-d\TH:i') : '' }}">
                                            @error('completado_at')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                
                                {{-- Progreso por secciones --}}
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <label class="form-label">Progreso por Secciones:</label>
                                        @php $seccionesConfig = $cuestionario->getSeccionesConfig(); @endphp
                                        <div class="row">
                                            @foreach($seccionesConfig as $numSeccion => $nombreSeccion)
                                                <div class="col-md-4 mb-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input" 
                                                               type="checkbox" 
                                                               id="seccion_{{ $numSeccion }}_completada" 
                                                               name="progreso_secciones[{{ $numSeccion }}]" 
                                                               value="1"
                                                               {{ ($cuestionario->progreso_secciones[$numSeccion] ?? false) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="seccion_{{ $numSeccion }}_completada">
                                                            {{ $numSeccion }}. {{ $nombreSeccion }}
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body text-center">
                                @php $progreso = $cuestionario->calcularProgreso(); @endphp
                                <h5>Progreso Actual</h5>
                                <div class="progress mb-3" style="height: 25px;">
                                    <div class="progress-bar bg-info" 
                                         role="progressbar" 
                                         style="width: {{ $progreso }}%">
                                        {{ $progreso }}%
                                    </div>
                                </div>
                                <p class="mb-0">{{ $cuestionario->seccion_actual }} de {{ $cuestionario->total_secciones }} secciones</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                {{-- Navegación por secciones para editar --}}
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-list-ul"></i> Editar Contenido del Cuestionario
                            <span class="badge bg-info ms-2">{{ ucfirst($cuestionario->tipo_formulario) }}</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        @php $seccionesConfig = $cuestionario->getSeccionesConfig(); @endphp
                        {{-- Pestañas de navegación --}}
                        <ul class="nav nav-tabs" id="seccionesEditarTabs" role="tablist">
                            @foreach($seccionesConfig as $numSeccion => $nombreSeccion)
                                @php $completada = $cuestionario->progreso_secciones[$numSeccion] ?? false; @endphp
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link {{ $numSeccion == 1 ? 'active' : '' }} {{ $completada ? 'text-success' : 'text-muted' }}" 
                                            id="editarSeccion{{ $numSeccion }}-tab" 
                                            data-bs-toggle="tab" 
                                            data-bs-target="#editarSeccion{{ $numSeccion }}" 
                                            type="button" 
                                            role="tab">
                                        @if($completada)
                                            <i class="bi bi-check-circle-fill"></i>
                                        @else
                                            <i class="bi bi-circle"></i>
                                        @endif
                                        {{ $numSeccion }}. {{ $nombreSeccion }}
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                        
                        {{-- Contenido de las pestañas --}}
                        <div class="tab-content mt-3" id="seccionesEditarTabContent">
                            @foreach($seccionesConfig as $numSeccion => $nombreSeccion)
                                <div class="tab-pane fade {{ $numSeccion == 1 ? 'show active' : '' }}" 
                                     id="editarSeccion{{ $numSeccion }}" 
                                     role="tabpanel">
                                    @include('admin.cuestionarios.partials.editar_seccion_' . $numSeccion, [
                                        'respuestas' => $cuestionario->obtenerRespuestasSeccion($numSeccion),
                                        'seccion' => $numSeccion,
                                        'tipoFormulario' => $cuestionario->tipo_formulario,
                                        'nombreSeccion' => $nombreSeccion
                                    ])
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                
                {{-- Observaciones Administrativas --}}
                <div class="card mt-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-sticky"></i> Observaciones Administrativas
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="observaciones_repro" class="form-label">Notas internas</label>
                            <textarea class="form-control @error('observaciones_repro') is-invalid @enderror" 
                                      id="observaciones_repro" 
                                      name="observaciones_repro" 
                                      rows="4"
                                      placeholder="Agregue observaciones, notas o comentarios sobre este cuestionario...">{{ old('observaciones_repro', $cuestionario->observaciones_repro) }}</textarea>
                            @error('observaciones_repro')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Estas observaciones son solo para uso interno de REPRO
                            </small>
                        </div>
                    </div>
                </div>
                
                @include('admin.cuestionarios.partials.notas-evaluador')
                
                {{-- Botones de acción --}}
                <div class="card mt-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <button type="button" class="btn btn-outline-warning" id="btnGuardarBorrador">
                                    <i class="bi bi-floppy"></i> Guardar como Borrador
                                </button>
                                <button type="button" class="btn btn-outline-info" id="btnVistaPrevia">
                                    <i class="bi bi-eye"></i> Vista Previa
                                </button>
                            </div>
                            
                            <div>
                                <a href="{{ route('admin.cuestionarios.show', $cuestionario) }}" 
                                   class="btn btn-secondary">
                                    <i class="bi bi-x-lg"></i> Cancelar
                                </a>
                                @if($cuestionario->estado == 'completado')
                                    <button type="submit" class="btn btn-warning" id="btnGuardarCambios">
                                        <i class="bi bi-pencil"></i> Guardar Correcciones
                                    </button>
                                @else
                                    <button type="submit" class="btn btn-success" id="btnGuardarCambios">
                                        <i class="bi bi-check-lg"></i> Guardar Cambios
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal de Vista Previa --}}
<div class="modal fade" id="modalVistaPrevia" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Vista Previa del Cuestionario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="contenidoVistaPrevia">
                {{-- Contenido cargado dinámicamente --}}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formEditarCuestionario');
    const btnGuardarBorrador = document.getElementById('btnGuardarBorrador');
    const btnVistaPrevia = document.getElementById('btnVistaPrevia');
    const btnGuardarCambios = document.getElementById('btnGuardarCambios');
    
    // Activar modo corrección si el cuestionario está completado
    @if($cuestionario->estado == 'completado')
        document.body.classList.add('modo-correccion');
    @endif
    
    // Auto-guardar cada 30 segundos
    let autoGuardarInterval;
    
    function iniciarAutoGuardado() {
        autoGuardarInterval = setInterval(function() {
            guardarBorrador();
        }, 30000); // 30 segundos
    }
    
    function detenerAutoGuardado() {
        if (autoGuardarInterval) {
            clearInterval(autoGuardarInterval);
        }
    }
    
    function guardarBorrador() {
        const formData = new FormData(form);
        formData.append('guardar_borrador', '1');
        
        fetch('{{ route("admin.cuestionarios.update", $cuestionario) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mostrarNotificacion('Borrador guardado automáticamente', 'success');
            }
        })
        .catch(error => {
            console.error('Error al guardar borrador:', error);
        });
    }
    
    function mostrarNotificacion(mensaje, tipo = 'info') {
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
    }
    
    // Eventos
    btnGuardarBorrador.addEventListener('click', function() {
        guardarBorrador();
    });
    
    btnVistaPrevia.addEventListener('click', function() {
        // Aquí implementarías la vista previa
        const modal = new bootstrap.Modal(document.getElementById('modalVistaPrevia'));
        document.getElementById('contenidoVistaPrevia').innerHTML = '<p>Cargando vista previa...</p>';
        modal.show();
    });
    
    // Actualizar estado automáticamente según progreso
    const checkboxesSecciones = document.querySelectorAll('input[name^="progreso_secciones"]');
    const selectEstado = document.getElementById('estado');
    
    checkboxesSecciones.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const seccionesCompletadas = document.querySelectorAll('input[name^="progreso_secciones"]:checked').length;
            
            if (seccionesCompletadas === 0) {
                selectEstado.value = 'pendiente';
            } else if (seccionesCompletadas === 5) {
                selectEstado.value = 'completado';
                // Establecer fecha de completado si no existe
                const fechaCompletado = document.getElementById('completado_at');
                if (!fechaCompletado.value) {
                    fechaCompletado.value = new Date().toISOString().slice(0, 16);
                }
            } else {
                selectEstado.value = 'en_progreso';
            }
        });
    });
    
    // Manejar cambio de estado
    selectEstado.addEventListener('change', function() {
        const fechaCompletado = document.getElementById('completado_at');
        
        if (this.value === 'completado' && !fechaCompletado.value) {
            fechaCompletado.value = new Date().toISOString().slice(0, 16);
        } else if (this.value !== 'completado') {
            fechaCompletado.value = '';
        }
    });
    
    // Iniciar auto-guardado
    iniciarAutoGuardado();
    
    // Detener auto-guardado al salir
    window.addEventListener('beforeunload', function() {
        detenerAutoGuardado();
    });
    
    // Confirmar si hay cambios sin guardar
    let formaCambiada = false;
    
    form.addEventListener('input', function() {
        formaCambiada = true;
    });
    
    window.addEventListener('beforeunload', function(e) {
        if (formaCambiada) {
            e.preventDefault();
            e.returnValue = '¿Está seguro de salir? Los cambios no guardados se perderán.';
        }
    });
    
    form.addEventListener('submit', function() {
        formaCambiada = false;
        detenerAutoGuardado();
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

.form-group {
    margin-bottom: 1rem;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.progress {
    border-radius: 10px;
}

.btn-group .btn {
    margin-left: 0.25rem;
}

.tab-content {
    min-height: 400px;
}

.form-control:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

/* Estilos para modo corrección */
.alert-sm {
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
}

.badge.bg-warning {
    color: #000;
}

.card-header .alert-warning {
    background-color: rgba(255, 193, 7, 0.1);
    border: 1px solid rgba(255, 193, 7, 0.3);
    color: #856404;
}

/* Highlight para formularios en modo corrección */
body.modo-correccion .form-control {
    border-left: 3px solid #ffc107;
}

body.modo-correccion .card {
    border-left: 4px solid #ffc107;
}
</style>
@endpush