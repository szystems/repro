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
                        @include('partials._ayuda_contextual', ['class' => 'me-2'])
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
                  enctype="multipart/form-data"
                  novalidate>
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

                                @if(Auth::user()->role_as >= 2)
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="sede_region_empresa" class="form-label">Agencia / región empresa</label>
                                            <input type="text"
                                                   class="form-control @error('sede_region_empresa') is-invalid @enderror"
                                                   id="sede_region_empresa"
                                                   name="sede_region_empresa"
                                                   value="{{ old('sede_region_empresa', $cuestionario->evaluadoOrden->sede_region_empresa) }}"
                                                   maxlength="100"
                                                   placeholder="Ej: Regional Norte, Quetzaltenango">
                                            @error('sede_region_empresa')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="text-muted">Se refleja en el formulario del candidato y en el informe Word.</small>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                
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
                
                {{-- Navegación por secciones para editar (PDF del candidato, no el Word) --}}
                <div class="card seccion-editar-cuestionario border-danger">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0 text-uppercase">
                            <i class="bi bi-list-ul"></i> Editar contenido de cuestionario
                            <span class="badge bg-light text-danger ms-2">{{ ucfirst($cuestionario->tipo_formulario) }}</span>
                        </h5>
                        <small class="d-block mt-1">
                            PDF de lo que llenó el candidato. No es la redacción del informe Word.
                        </small>
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
                                    @include('shared.cuestionario.seccion-edicion', [
                                        'cuestionario' => $cuestionario,
                                        'numeroSeccion' => $numSeccion,
                                        'nombreSeccion' => $nombreSeccion,
                                        'respuestas' => $cuestionario->obtenerRespuestasSeccion($numSeccion),
                                        'fotoCandidatoUrl' => $numSeccion === 1 ? ($fotoCandidatoUrl ?? null) : null,
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
                @include('admin.cuestionarios.partials.inicio-redaccion-word')
                @include('admin.cuestionarios.partials.resultado-word-detalle')

                @include('admin.cuestionarios.partials.tablas-informe-preempleo', ['soloLectura' => false])
                @include('admin.cuestionarios.partials.narrativas-word-evaluador')
                @include('admin.cuestionarios.partials.anexos-word-papeleria', ['soloLectura' => false])
                @include('admin.cuestionarios.partials.preguntas-poligraficas-word', ['soloLectura' => false])
                
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
                                @if(Auth::user()->role_as >= 2)
                                <a href="{{ route('admin.cuestionarios.informe-word-borrador', $cuestionario) }}"
                                   class="btn btn-outline-primary"
                                   target="_blank"
                                   rel="noopener noreferrer">
                                    <i class="bi bi-download"></i> Descargar borrador Word
                                </a>
                                @endif
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

            @include('admin.ordenes._documentos_evaluado', ['evaluado' => $cuestionario->evaluadoOrden])
        </div>
    </div>
</div>

{{-- Modal de Vista Previa — G1.3 informe REPRO --}}
@php
    $evaluadoOrden = $cuestionario->evaluadoOrden;
@endphp
<div class="modal fade" id="modalVistaPrevia" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Vista previa — Informe de evaluación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="contenidoVistaPrevia">
                <p class="text-muted mb-3">
                    Se guardó un borrador antes de abrir esta vista. Revise el informe aquí en pantalla; si está correcto, use los botones al pie para descargar o generar el documento.
                </p>

                @if($bloquesWordCompletos ?? false)
                    <div class="alert alert-success py-2">
                        <i class="bi bi-check-circle"></i> Los seis bloques de redacción del informe Word están completos.
                    </div>
                @else
                    <div class="alert alert-warning py-2">
                        <i class="bi bi-exclamation-triangle"></i>
                        Faltan bloques de redacción para el informe Word final:
                        <strong>{{ implode(', ', $bloquesWordFaltantes ?? []) }}</strong>
                    </div>
                @endif

                @if($evaluadoOrden && Auth::user()->role_as >= 2)
                <div class="d-flex justify-content-end mb-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="btnRecargarVistaWord">
                        <i class="bi bi-arrow-clockwise"></i> Regenerar vista previa
                    </button>
                </div>
                <div class="border rounded mb-3 bg-light">
                    <div class="px-3 py-2 border-bottom bg-white">
                        <strong><i class="bi bi-file-earmark-word"></i> Vista previa del borrador Word</strong>
                    </div>
                    <div id="wordPreviewLoading" class="p-4 text-center text-muted">
                        <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                        Generando vista previa del informe…
                    </div>
                    <div id="wordPreviewHtml" class="p-3 bg-white d-none" style="max-height: 480px; overflow: auto;"></div>
                    <div id="wordPreviewError" class="p-3 text-danger d-none"></div>
                </div>
                @endif

                @if(($informeFinalEsPdf ?? false) && ($tieneInformeFinal ?? false))
                <div class="mb-2"><strong>Informe final subido (PDF)</strong></div>
                <div class="border rounded overflow-hidden" style="height: 360px;">
                    <iframe data-src="{{ route('admin.cuestionarios.informe-final-preview', $cuestionario) }}"
                            title="Vista previa informe final PDF"
                            class="w-100 h-100 border-0"
                            loading="lazy"></iframe>
                </div>
                @elseif($tieneInformeFinal ?? false)
                    <p class="text-muted small mb-0">
                        El informe final subido no es PDF; use «Descargar informe final» para abrirlo.
                    </p>
                @endif
            </div>
            <div class="modal-footer flex-wrap">
                @if($evaluadoOrden && Auth::user()->role_as >= 2)
                <a href="{{ route('admin.cuestionarios.informe-word-borrador', $cuestionario) }}"
                   class="btn btn-primary"
                   target="_blank"
                   rel="noopener noreferrer"
                   id="btnDescargarWordBorrador">
                    <i class="bi bi-download"></i> Descargar borrador Word
                </a>
                @endif
                @if(($tieneInformeFinal ?? false) && $evaluadoOrden)
                <a href="{{ route('evaluados.descargar-resultado-archivo', [$evaluadoOrden, 'final']) }}"
                   class="btn btn-success"
                   target="_blank"
                   rel="noopener noreferrer">
                    <i class="bi bi-download"></i> Descargar informe final (PDF)
                </a>
                @endif
                @if($evaluadoOrden)
                <a href="{{ route('ordenes.show', $evaluadoOrden->orden_id) }}#heading-evaluado-{{ $evaluadoOrden->id }}"
                   class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-box-arrow-up-right"></i> Ir a la orden (subir final)
                </a>
                @endif
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- Servido desde el propio dominio: con el CDN la vista previa quedaba cargando indefinidamente
     cuando la red del cliente lo bloqueaba. --}}
<script src="{{ asset('js/mammoth.browser.min.js') }}?v={{ is_file(public_path('js/mammoth.browser.min.js')) ? filemtime(public_path('js/mammoth.browser.min.js')) : time() }}"></script>
<script src="{{ asset('js/foto-candidato.js') }}?v={{ filemtime(public_path('js/foto-candidato.js')) }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formEditarCuestionario');
    const btnGuardarBorrador = document.getElementById('btnGuardarBorrador');
    const btnVistaPrevia = document.getElementById('btnVistaPrevia');
    
    @if(session('success'))
        mostrarNotificacion(@json(session('success')), 'success');
    @endif
    
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
        if (window.TablaDinamica && typeof window.TablaDinamica.syncAll === 'function') {
            window.TablaDinamica.syncAll();
        }

        const formData = new FormData(form);
        formData.append('_method', 'PUT');
        formData.append('guardar_borrador', '1');
        const abortBorrador = new AbortController();
        const timeoutBorrador = setTimeout(function() {
            abortBorrador.abort();
        }, 12000);

        return fetch('{{ route("admin.cuestionarios.update", $cuestionario) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData,
            signal: abortBorrador.signal
        })
        .finally(function() {
            clearTimeout(timeoutBorrador);
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                formaCambiada = false;
                mostrarNotificacion('Borrador guardado correctamente', 'success');
            }
            return data;
        })
        .catch(error => {
            console.error('Error al guardar borrador:', error);
            mostrarNotificacion('No se pudo guardar el borrador', 'error');
            throw error;
        });
    }
    
    function mostrarNotificacion(mensaje, tipo = 'info') {
        if (typeof Swal !== 'undefined' && typeof Swal.mixin === 'function') {
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
            return;
        }

        console.log('[REPRO]', tipo, mensaje);
    }
    
    // Eventos
    btnGuardarBorrador.addEventListener('click', function() {
        guardarBorrador();
    });
    
    btnVistaPrevia.addEventListener('click', function() {
        const modalEl = document.getElementById('modalVistaPrevia');
        if (! modalEl || typeof bootstrap === 'undefined') {
            return;
        }
        // Abrir ya: el PUT del socio (cientos de campos) en iPage puede no terminar
        // y el cliente veía que «no se abre» la vista previa.
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
        guardarBorrador().catch(function() {});
    });

    const modalVistaPrevia = document.getElementById('modalVistaPrevia');
    const wordPreviewUrl = @json(route('admin.cuestionarios.informe-word-preview', $cuestionario));
    let cargandoVistaPrevia = false;
    let abortVistaPrevia = null;

    function cargarVistaPreviaWord() {
        if (cargandoVistaPrevia) {
            return;
        }

        const loading = document.getElementById('wordPreviewLoading');
        const htmlDiv = document.getElementById('wordPreviewHtml');
        const errorDiv = document.getElementById('wordPreviewError');
        if (!loading || !htmlDiv) {
            return;
        }

        if (typeof mammoth === 'undefined') {
            loading.classList.add('d-none');
            if (errorDiv) {
                errorDiv.textContent = 'No se pudo cargar el visor de documentos. Recargue la página o use «Descargar borrador Word».';
                errorDiv.classList.remove('d-none');
            }
            document.getElementById('btnDescargarWordBorrador')?.classList.remove('d-none');
            return;
        }

        if (abortVistaPrevia) {
            abortVistaPrevia.abort();
        }
        abortVistaPrevia = new AbortController();
        const timeoutId = setTimeout(function() {
            abortVistaPrevia?.abort();
        }, 90000);

        cargandoVistaPrevia = true;
        loading.classList.remove('d-none');
        htmlDiv.classList.add('d-none');
        errorDiv?.classList.add('d-none');
        htmlDiv.innerHTML = '';

        fetch(wordPreviewUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
            },
            credentials: 'same-origin',
            signal: abortVistaPrevia.signal
        })
        .then(function(response) {
            if (!response.ok) {
                throw new Error('No se pudo generar la vista previa (HTTP ' + response.status + ').');
            }
            return response.arrayBuffer();
        })
        .then(function(buffer) {
            return new Promise(function(resolve, reject) {
                window.setTimeout(function() {
                    mammoth.convertToHtml({ arrayBuffer: buffer }).then(resolve).catch(reject);
                }, 0);
            });
        })
        .then(function(result) {
            htmlDiv.innerHTML = result.value;
            htmlDiv.classList.remove('d-none');
            loading.classList.add('d-none');
            const btnDescargar = document.getElementById('btnDescargarWordBorrador');
            if (btnDescargar) {
                btnDescargar.classList.remove('d-none');
            }
        })
        .catch(function(error) {
            if (error.name === 'AbortError') {
                if (errorDiv) {
                    errorDiv.textContent = 'La vista previa tardó demasiado. Use «Regenerar vista previa» o descargue el borrador Word.';
                    errorDiv.classList.remove('d-none');
                }
                loading.classList.add('d-none');
                return;
            }
            loading.classList.add('d-none');
            if (errorDiv) {
                errorDiv.textContent = error.message || 'Error al cargar la vista previa.';
                errorDiv.classList.remove('d-none');
            }
        })
        .finally(function() {
            clearTimeout(timeoutId);
            cargandoVistaPrevia = false;
        });
    }

    if (modalVistaPrevia) {
        modalVistaPrevia.addEventListener('shown.bs.modal', function() {
            cargarVistaPreviaWord();
            modalVistaPrevia.querySelector('iframe[data-src]')?.setAttribute(
                'src',
                modalVistaPrevia.querySelector('iframe[data-src]')?.getAttribute('data-src') || ''
            );
        });
    }

    document.getElementById('btnRecargarVistaWord')?.addEventListener('click', cargarVistaPreviaWord);
    
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
        if (window.TablaDinamica && typeof window.TablaDinamica.removeEmptyRowsAll === 'function') {
            window.TablaDinamica.removeEmptyRowsAll();
        } else if (window.TablaDinamica && typeof window.TablaDinamica.syncAll === 'function') {
            window.TablaDinamica.syncAll();
        }
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

/* El cuestionario del candidato queda en rojo (pedido Stephany 24-ago), no amarillo. */
body.modo-correccion .seccion-editar-cuestionario {
    border-left: 4px solid #dc3545;
}

body.modo-correccion .seccion-editar-cuestionario .form-control {
    border-left: 3px solid #dc3545;
}
</style>
@endpush