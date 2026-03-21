{{-- Sección de documentos para empresa (subir papelería, descargar, ver estado) --}}
{{-- Variables: $evaluado (EvaluadoOrden con documentos cargados) --}}

<div class="card mt-2 border" id="docs-empresa-{{ $evaluado->id }}">
    <div class="card-header py-2 d-flex justify-content-between align-items-center">
        <small class="fw-bold">
            <i class="bi bi-folder2-open"></i> Documentos
            @if($evaluado->documentos->count() > 0)
                <span class="badge bg-primary ms-1">{{ $evaluado->documentos->count() }}</span>
            @endif
        </small>
        <button class="btn btn-primary btn-sm py-0" type="button" data-bs-toggle="collapse"
                data-bs-target="#upload-empresa-{{ $evaluado->id }}">
            <i class="bi bi-cloud-upload"></i> Subir
        </button>
    </div>

    {{-- Formulario de subida --}}
    <div class="collapse" id="upload-empresa-{{ $evaluado->id }}">
        <div class="card-body border-bottom bg-light py-2">
            <form action="{{ route('documentos-evaluado.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="evaluado_orden_id" value="{{ $evaluado->id }}">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label mb-0"><small>Tipo</small></label>
                        <select name="tipo_documento" class="form-select form-select-sm" required>
                            <option value="">Seleccione...</option>
                            @foreach(\App\Models\DocumentoEvaluado::tiposDocumento() as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label mb-0"><small>Archivo (máx. 10 MB)</small></label>
                        <input type="file" name="archivo" class="form-control form-control-sm"
                               accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" capture="environment" required>
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
    @if($evaluado->documentos->count() > 0)
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Tipo</th>
                        <th>Archivo</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($evaluado->documentos as $documento)
                    <tr>
                        <td>
                            <small>{{ $documento->tipo_documento_texto }}</small>
                        </td>
                        <td>
                            <small>{{ Str::limit($documento->nombre_original, 25) }}</small>
                            <br><small class="text-muted">{{ $documento->tamano_legible }}</small>
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
                            <a href="{{ route('documentos-evaluado.download', $documento) }}"
                               class="btn btn-outline-primary btn-sm py-0" title="Descargar">
                                <i class="bi bi-download"></i>
                            </a>
                            @if($documento->subido_por_user_id === Auth::id() && $documento->estado_verificacion === 'pendiente')
                                <form action="{{ route('documentos-evaluado.destroy', $documento) }}"
                                      method="POST" class="d-inline"
                                      onsubmit="return confirm('¿Eliminar este documento?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm py-0" title="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="card-body py-2">
            <small class="text-muted"><i class="bi bi-info-circle"></i> No hay documentos subidos.</small>
        </div>
    @endif
</div>
