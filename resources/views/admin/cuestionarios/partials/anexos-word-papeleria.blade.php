{{-- Una casilla por imagen. Dos archivos del mismo tipo (original y validación) se eligen por separado. --}}
@if(($puedeGestionarNotasEvaluador ?? false) || (($informePreempleoActivo ?? false) && ($puedeGestionarInformePreempleo ?? false)))
@php
    $imagenesAnexo = \App\Support\InformeWordAnexosPapeleria::imagenesDisponibles($cuestionario->evaluadoOrden);
    $idsSeleccionados = \App\Support\InformeWordAnexosPapeleria::idsSeleccionados($cuestionario->evaluado_orden_id);
    $tiposSeleccionados = \App\Support\InformeWordAnexosPapeleria::tiposSeleccionados($cuestionario->evaluado_orden_id);
    $etiquetasAnexo = \App\Models\DocumentoEvaluado::tiposDocumento();
    $hayPdf = $cuestionario->evaluadoOrden->documentos->contains(fn ($doc) => $doc->es_pdf);
@endphp
<div class="card mt-4 border-info">
    <div class="card-header bg-info bg-opacity-10">
        <h6 class="mb-0">
            <i class="bi bi-paperclip"></i> Anexos de papelería en informe Word
        </h6>
        <small class="text-muted d-block mt-1">
            Marque las imágenes que deben ir al final del Word, después de tatuajes.
            Si hay dos archivos del mismo documento (el que trajo el candidato y la validación), marque solo el que quiere anexar.
            Los PDF no se pegan en el Word.
        </small>
    </div>
    <div class="card-body">
        @if($imagenesAnexo->isEmpty())
            <p class="text-muted mb-0">
                @if($hayPdf)
                    Esta orden tiene papelería en PDF. El Word solo anexa imágenes (JPG o PNG). Suba la validación como imagen para poder marcarla.
                @else
                    No hay imágenes de papelería en esta orden. Suba JPG o PNG en la orden para poder anexarlos al Word.
                @endif
            </p>
        @endif
        @if(!($soloLectura ?? false))
            <input type="hidden" name="_word_anexos_papeleria" value="1">
        @endif
        <div class="row">
            @foreach($imagenesAnexo as $documento)
                @php
                    $marcado = in_array($documento->id, $idsSeleccionados, true)
                        || ($idsSeleccionados === [] && in_array($documento->tipo_documento, $tiposSeleccionados, true));
                    $previo = old('word_anexos_papeleria');
                    if ($previo !== null) {
                        $marcado = in_array((string) $documento->id, array_map('strval', (array) $previo), true);
                    }
                @endphp
                <div class="col-md-6 mb-2">
                    <div class="form-check">
                        <input class="form-check-input"
                               type="checkbox"
                               id="anexo_papeleria_{{ $documento->id }}"
                               name="word_anexos_papeleria[]"
                               value="{{ $documento->id }}"
                               @checked($marcado)
                               @disabled($soloLectura ?? false)>
                        <label class="form-check-label" for="anexo_papeleria_{{ $documento->id }}">
                            {{ $etiquetasAnexo[$documento->tipo_documento] ?? $documento->tipo_documento }}
                            — {{ $documento->nombre_original }}
                        </label>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endif
