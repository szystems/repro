{{-- Sección de documentos para un evaluado dentro del show de la orden --}}
{{-- Variables: $evaluado (EvaluadoOrden con documentos cargados) --}}

<div class="card mt-3" id="documentos-evaluado-{{ $evaluado->id }}">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div class="card-title mb-0">
            <i class="bi bi-folder2-open"></i> Documentos — {{ $evaluado->nombre }} {{ $evaluado->apellidos }}
            @if($evaluado->documentos->count() > 0)
                <span class="badge bg-primary ms-1">{{ $evaluado->documentos->count() }}</span>
            @endif
        </div>
        <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="collapse"
                data-bs-target="#upload-form-{{ $evaluado->id }}">
            <i class="bi bi-cloud-upload"></i> Subir Documento
        </button>
    </div>

    {{-- Formulario de subida (colapsado) --}}
    <div class="collapse" id="upload-form-{{ $evaluado->id }}">
        <div class="card-body border-bottom bg-light">
            <form action="{{ route('documentos-evaluado.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="evaluado_orden_id" value="{{ $evaluado->id }}">

                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Tipo de Documento</label>
                        <select name="tipo_documento" class="form-select form-select-sm" required>
                            <option value="">Seleccione...</option>
                            @foreach(\App\Models\DocumentoEvaluado::tiposDocumento() as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Archivo <small class="text-muted">(máx. 10 MB)</small></label>
                        <input type="file" name="archivo" class="form-control form-control-sm"
                               accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" capture="environment" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Notas <small class="text-muted">(opcional)</small></label>
                        <input type="text" name="notas" class="form-control form-control-sm"
                               placeholder="Notas adicionales..." maxlength="500">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-success btn-sm w-100">
                            <i class="bi bi-upload"></i> Subir
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Lista de documentos --}}
    <div class="card-body p-0">
        @if($evaluado->documentos->count() > 0)
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Tipo</th>
                            <th>Archivo</th>
                            <th>Tamaño</th>
                            <th>Subido por</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($evaluado->documentos as $documento)
                        <tr>
                            <td>
                                @if($documento->es_pdf)
                                    <i class="bi bi-file-pdf text-danger"></i>
                                @elseif($documento->es_imagen)
                                    <i class="bi bi-file-image text-success"></i>
                                @else
                                    <i class="bi bi-file-earmark text-secondary"></i>
                                @endif
                                {{ $documento->tipo_documento_texto }}
                            </td>
                            <td>
                                <small>{{ Str::limit($documento->nombre_original, 30) }}</small>
                                @if($documento->notas)
                                    <br><small class="text-muted fst-italic"><i class="bi bi-chat-left-text"></i> {{ $documento->notas }}</small>
                                @endif
                            </td>
                            <td><small>{{ $documento->tamano_legible }}</small></td>
                            <td>
                                <span class="badge bg-{{ $documento->subido_por_tipo === 'repro' ? 'info' : ($documento->subido_por_tipo === 'empresa' ? 'warning' : 'secondary') }}">
                                    {{ ucfirst($documento->subido_por_tipo) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $documento->estado_verificacion_color }}">
                                    {{ $documento->estado_verificacion_texto }}
                                </span>
                                @if($documento->notas_verificacion)
                                    <br><small class="text-muted">{{ $documento->notas_verificacion }}</small>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    {{-- Vista previa (imágenes y PDF) --}}
                                    @if($documento->es_pdf || $documento->es_imagen)
                                        <button type="button"
                                                class="btn btn-outline-secondary btn-sm btn-preview-doc"
                                                title="Vista previa"
                                                data-url="{{ route('documentos-evaluado.preview', $documento) }}"
                                                data-tipo="{{ $documento->es_imagen ? 'imagen' : 'pdf' }}"
                                                data-nombre="{{ $documento->nombre_original }}"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalPreviewDoc">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    @endif

                                    {{-- Descargar --}}
                                    <a href="{{ route('documentos-evaluado.download', $documento) }}"
                                       class="btn btn-outline-primary btn-sm" title="Descargar">
                                        <i class="bi bi-download"></i>
                                    </a>

                                    {{-- Verificar (solo REPRO) --}}
                                    @if(Auth::user()->role_as >= 2 && $documento->estado_verificacion === 'pendiente')
                                        <form action="{{ route('documentos-evaluado.verificar', $documento) }}"
                                              method="POST" class="d-inline">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="estado_verificacion" value="aprobado">
                                            <button type="submit" class="btn btn-outline-success btn-sm" title="Aprobar">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('documentos-evaluado.verificar', $documento) }}"
                                              method="POST" class="d-inline"
                                              onsubmit="event.preventDefault(); 
                                                  let notas = prompt('Motivo del rechazo:');
                                                  if (notas !== null) { 
                                                      this.querySelector('[name=notas_verificacion]').value = notas;
                                                      this.submit();
                                                  }">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="estado_verificacion" value="rechazado">
                                            <input type="hidden" name="notas_verificacion" value="">
                                            <button type="submit" class="btn btn-outline-danger btn-sm" title="Rechazar">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </form>
                                    @endif

                                    {{-- Eliminar --}}
                                    @if(Auth::user()->role_as >= 2 || $documento->subido_por_user_id === Auth::id())
                                        <form action="{{ route('documentos-evaluado.destroy', $documento) }}"
                                              method="POST" class="d-inline"
                                              onsubmit="return confirm('¿Eliminar este documento?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm" title="Eliminar">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center text-muted py-3">
                <i class="bi bi-folder-x fs-4"></i>
                <p class="mb-0 small">No hay documentos adjuntos</p>
            </div>
        @endif
    </div>
</div>

{{-- Modal Vista Previa (compartido, se reutiliza para todos los documentos) --}}
@once
<div class="modal fade" id="modalPreviewDoc" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title" id="modalPreviewDocTitulo">Vista previa</h6>
                <div class="ms-auto d-flex gap-2 align-items-center">
                    <a href="#" id="btnDescargarPreview" class="btn btn-sm btn-outline-primary" target="_blank">
                        <i class="bi bi-download"></i> Descargar
                    </a>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body p-0" style="min-height: 500px; background: #f0f0f0;">
                <div id="previewContenedor" class="w-100 h-100 d-flex align-items-center justify-content-center" style="min-height: 500px;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('click', function (e) {
    const btn = e.target.closest('.btn-preview-doc');
    if (!btn) { return; }

    const url    = btn.dataset.url;
    const tipo   = btn.dataset.tipo;
    const nombre = btn.dataset.nombre;

    document.getElementById('modalPreviewDocTitulo').textContent = nombre;
    document.getElementById('btnDescargarPreview').href = url.replace('/preview', '/download');

    const contenedor = document.getElementById('previewContenedor');
    contenedor.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div>';

    if (tipo === 'imagen') {
        const img = document.createElement('img');
        img.src = url;
        img.style.maxWidth = '100%';
        img.style.maxHeight = '80vh';
        img.style.objectFit = 'contain';
        img.style.display = 'block';
        img.style.margin = 'auto';
        img.onload = () => { contenedor.innerHTML = ''; contenedor.appendChild(img); };
        img.onerror = () => { contenedor.innerHTML = '<p class="text-danger p-4">No se pudo cargar la imagen.</p>'; };
    } else {
        const iframe = document.createElement('iframe');
        iframe.src = url;
        iframe.style.width = '100%';
        iframe.style.height = '80vh';
        iframe.style.border = 'none';
        iframe.style.display = 'block';
        contenedor.innerHTML = '';
        contenedor.appendChild(iframe);
    }
});
</script>
@endpush
@endonce
