@php
    $tiposDocumento = $tiposDocumento ?? \App\Models\DocumentoEvaluado::tiposDocumentoParaEvaluado($evaluadoOrden ?? $evaluado);
    $puedeSubir = $puedeSubirDocumentos ?? ($evaluadoOrden ?? $evaluado)->puedeSubirDocumentosConEnlace();
    $evaluadoDoc = $evaluadoOrden ?? $evaluado;
    $tipoCuestionario = ($evaluadoDoc->cuestionario->tipo_formulario ?? null)
        ?? $evaluadoDoc->tipoFormularioCuestionario();
@endphp

<div class="documentos-candidato-panel mt-4 text-start">
    <div class="section-title">
        <i class="fas fa-paperclip"></i>
        @if($evaluadoDoc->cuestionario_completado ?? false)
            Documentación adicional
        @else
            Documentos (opcional)
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show py-2" role="alert">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    <div class="alert alert-info py-2">
        <i class="fas fa-info-circle"></i>
        @if(($evaluadoDoc->tipo_servicio ?? '') === 'socioeconomico')
            <strong>Documentos sugeridos:</strong> DPI, constancia laboral y recibo de luz (máx. 10 MB c/u).
        @elseif(in_array($tipoCuestionario, ['periodica', 'especifica'], true))
            <strong>Documento:</strong> adjunte su DPI si aún no lo hizo (máx. 10 MB).
        @else
            <strong>Documentos opcionales:</strong> DPI, constancias u otros relevantes (máx. 10 MB c/u).
        @endif
        @if($evaluadoDoc->cuestionario_completado ?? false)
            <br><small>Puede usar el mismo enlace para subir papelería pendiente mientras esté vigente
            (hasta el {{ $evaluadoDoc->token_expira_at?->format('d/m/Y') ?? '—' }}).</small>
        @endif
    </div>

    @if(! in_array($tipoCuestionario, ['periodica', 'especifica', 'socioeconomico'], true))
        <div class="alert alert-warning py-2">
            <i class="fas fa-exclamation-triangle"></i>
            Si no cuenta con toda la papelería al completar el formulario, podrá utilizar este mismo enlace durante los próximos 30 días para adjuntar documentación pendiente (DPI, antecedentes penales, constancias laborales, etc.).
        </div>
    @endif

    @if($evaluadoDoc->documentos->count() > 0)
        <div class="mb-3">
            <h6 class="mb-2">Documentos cargados</h6>
            <ul class="list-group list-group-flush">
                @foreach($evaluadoDoc->documentos as $doc)
                    <li class="list-group-item d-flex flex-column flex-sm-row justify-content-between align-items-sm-start gap-1 py-2 px-2">
                        <span class="small">
                            <i class="fas fa-file"></i>
                            {{ $doc->tipo_documento_texto }}
                            <br class="d-sm-none">
                            <small class="text-muted">{{ $doc->nombre_original }}</small>
                            @if($doc->notas_verificacion && $doc->estado_verificacion === 'rechazado')
                                <br><small class="text-danger"><i class="fas fa-exclamation-circle"></i> {{ $doc->notas_verificacion }}</small>
                            @endif
                        </span>
                        <span class="badge bg-{{ $doc->estado_verificacion_color }} align-self-start">{{ $doc->estado_verificacion_texto }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($puedeSubir)
        <form action="{{ route('cuestionario.subir-documento', $token) }}" method="POST" enctype="multipart/form-data"
              class="border rounded p-3 bg-light zona-pegar-papeleria" tabindex="0"
              data-file-input="archivo-papeleria-candidato">
            @csrf
            <div class="row g-2">
                <div class="col-12">
                    <label class="form-label mb-1">Tipo de documento</label>
                    <select name="tipo_documento" class="form-select form-select-sm" required>
                        <option value="">Seleccione...</option>
                        @foreach($tiposDocumento as $key => $label)
                            <option value="{{ $key }}" @selected(old('tipo_documento') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label mb-1">Archivo <small class="text-muted">(máx. 10 MB)</small></label>
                    <input type="file" name="archivo" id="archivo-papeleria-candidato"
                           class="form-control form-control-sm @error('archivo') is-invalid @enderror"
                           accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,image/*" capture="environment" required>
                    <button type="button" class="btn btn-outline-secondary btn-sm mt-1 btn-tomar-foto"
                            data-target="archivo-papeleria-candidato">
                        <i class="fas fa-camera"></i> Tomar foto
                    </button>
                    <small class="text-muted d-block">En celular o tablet puede tomar la foto. También puede pegar una imagen (Ctrl+V).</small>
                    @error('archivo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-outline-primary btn-sm w-100">
                        <i class="fas fa-upload"></i> Subir documento
                    </button>
                </div>
            </div>
        </form>
    @else
        <div class="alert alert-secondary py-2 mb-0">
            <i class="fas fa-clock"></i> El plazo para subir documentos con este enlace ha finalizado.
        </div>
    @endif
</div>
@include('shared.papeleria-captura-js')
