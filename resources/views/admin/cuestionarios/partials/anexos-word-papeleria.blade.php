{{-- Checkboxes de papelería para anexos del informe Word --}}
@if($puedeGestionarNotasEvaluador ?? false)
@php
    $seleccionados = old('word_anexos_papeleria', $anexosPapeleriaSeleccionados ?? \App\Support\InformeWordAnexosPapeleria::tiposSeleccionados($cuestionario->evaluado_orden_id));
    $tiposAnexoDisponibles = $tiposAnexoDisponibles ?? [];
@endphp
<div class="card mt-4 border-info">
    <div class="card-header bg-info bg-opacity-10">
        <h6 class="mb-0">
            <i class="bi bi-paperclip"></i> Anexos de papelería en informe Word
        </h6>
        <small class="text-muted d-block mt-1">
            Marque solo los documentos que deben ir en ANEXOS. Si no marca ninguno, el Word no carga ni procesa la papelería del candidato.
            Las imágenes se insertan (si no son demasiado pesadas); los PDF se listan por nombre para no saturar el servidor al generar el archivo.
        </small>
    </div>
    <div class="card-body">
        @if($tiposAnexoDisponibles === [])
            <p class="text-muted mb-0">
                No hay papelería subida en esta orden. Suba DPI u otros documentos en la orden para poder marcarlos como anexos del Word.
            </p>
        @endif
        @if(!($soloLectura ?? false))
            <input type="hidden" name="_word_anexos_papeleria" value="1">
        @endif
        <div class="row">
            @foreach($tiposAnexoDisponibles as $tipo => $etiqueta)
                <div class="col-md-6 mb-2">
                    <div class="form-check">
                        <input class="form-check-input"
                               type="checkbox"
                               id="anexo_papeleria_{{ $tipo }}"
                               name="word_anexos_papeleria[]"
                               value="{{ $tipo }}"
                               @checked(in_array($tipo, (array) $seleccionados, true))
                               @disabled($soloLectura ?? false)>
                        <label class="form-check-label" for="anexo_papeleria_{{ $tipo }}">
                            {{ $etiqueta }}
                        </label>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endif
