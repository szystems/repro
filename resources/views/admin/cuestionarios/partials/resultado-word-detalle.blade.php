{{-- Un resultado → 1ª y última hoja del Word (M-P3 / M-S3) --}}
@if($puedeGestionarNotasEvaluador ?? false)
@php
    $evaluadoInforme = $cuestionario->evaluadoOrden;
    $esSocioResultado = \App\Support\InformeWordResultado::esSocio($evaluadoInforme);
    $opcionesResultado = \App\Support\InformeWordResultado::opcionesInforme($evaluadoInforme);
    $resultadoActual = old('resultado_informe', $evaluadoInforme->resultado ?? '');
    $detalleMentira = old(
        'evaluador_notas.'.\App\Support\InformeWordResultado::NOTA_INDICACION_MENTIRA,
        $notasEvaluador[\App\Support\InformeWordResultado::NOTA_INDICACION_MENTIRA] ?? ''
    );
    $detalleExcepcion = old(
        'evaluador_notas.'.\App\Support\InformeWordResultado::NOTA_ASPECTO_EXCEPCION,
        $notasEvaluador[\App\Support\InformeWordResultado::NOTA_ASPECTO_EXCEPCION] ?? ''
    );
    $observacionesWord = old(
        'evaluador_notas.'.\App\Support\InformeWordBloquesEvaluador::NOTA_OBSERVACIONES,
        $notasEvaluador[\App\Support\InformeWordBloquesEvaluador::NOTA_OBSERVACIONES] ?? ''
    );
@endphp
<div class="card mt-4 border-primary" id="card-resultado-informe-word">
    <div class="card-header bg-primary bg-opacity-10">
        <h6 class="mb-0">
            <i class="bi bi-clipboard-check"></i>
            @if($esSocioResultado)
                Clasificación del estudio (primera y última hoja)
            @else
                Resultado de evaluación (primera y última hoja)
            @endif
        </h6>
        <small class="text-muted d-block mt-1">
            Un solo campo: al generar el Word queda solo esa opción, con el color de la plantilla.
        </small>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <label class="form-label fw-semibold" for="resultado_informe">
                @if($esSocioResultado)
                    Clasificación
                @else
                    Resultado
                @endif
            </label>
            @if($soloLectura ?? false)
                <div class="bg-light border rounded p-3 mb-0">
                    {{ $opcionesResultado[$resultadoActual] ?? ($resultadoActual !== '' ? $evaluadoInforme->resultado_texto : '— Sin seleccionar —') }}
                </div>
            @else
                <select class="form-select" id="resultado_informe" name="resultado_informe">
                    <option value="">— Seleccionar —</option>
                    @foreach($opcionesResultado as $valor => $etiqueta)
                        <option value="{{ $valor }}" @selected($resultadoActual === $valor)>{{ $etiqueta }}</option>
                    @endforeach
                </select>
            @endif
        </div>

        @unless($esSocioResultado)
        <div class="mb-3 js-resultado-detalle-mentira" @if($resultadoActual !== 'no_aprobado') style="display:none" @endif>
            <label class="form-label fw-semibold" for="word_resultado_mentira">
                {{ \App\Support\InformeWordResultado::ETIQUETA_MENTIRA }}
            </label>
            @if($soloLectura ?? false)
                <div class="bg-light border rounded p-3 mb-0" style="white-space: pre-wrap;">{{ $detalleMentira ?: '— Sin detalle —' }}</div>
            @else
                <textarea class="form-control"
                          id="word_resultado_mentira"
                          name="evaluador_notas[{{ \App\Support\InformeWordResultado::NOTA_INDICACION_MENTIRA }}]"
                          rows="2"
                          placeholder="Ej: preguntas 3 y 7 sobre manejo de efectivo.">{{ $detalleMentira }}</textarea>
            @endif
        </div>

        <div class="mb-3 js-resultado-detalle-excepcion" @if($resultadoActual !== 'aprobado_excepcion') style="display:none" @endif>
            <label class="form-label fw-semibold" for="word_resultado_excepcion">
                {{ \App\Support\InformeWordResultado::ETIQUETA_EXCEPCION }}
            </label>
            @if($soloLectura ?? false)
                <div class="bg-light border rounded p-3 mb-0" style="white-space: pre-wrap;">{{ $detalleExcepcion ?: '— Sin detalle —' }}</div>
            @else
                <textarea class="form-control"
                          id="word_resultado_excepcion"
                          name="evaluador_notas[{{ \App\Support\InformeWordResultado::NOTA_ASPECTO_EXCEPCION }}]"
                          rows="2"
                          placeholder="Ej: faltante de producto no reportado en empleo anterior.">{{ $detalleExcepcion }}</textarea>
            @endif
        </div>
        @endunless

        <div class="mb-0">
            <label class="form-label fw-semibold" for="word_observaciones">
                @if($esSocioResultado)
                    Aspectos a considerar
                @else
                    Observaciones del evaluador
                @endif
                <span class="badge bg-secondary ms-1">Primera hoja</span>
            </label>
            <small class="text-muted d-block mb-2">
                Solo aparece lo que escriba aquí. Si deja este espacio vacío, el cuadro se entrega en blanco.
            </small>
            @if($soloLectura ?? false)
                <div class="bg-light border rounded p-3 mb-0" style="white-space: pre-wrap;">{{ $observacionesWord ?: '— Sin observaciones —' }}</div>
            @else
                <textarea class="form-control"
                          id="word_observaciones"
                          name="evaluador_notas[{{ \App\Support\InformeWordBloquesEvaluador::NOTA_OBSERVACIONES }}]"
                          rows="3"
                          placeholder="Ej: se recomienda validar constancia de estudios y referencia del último empleo.">{{ $observacionesWord }}</textarea>
            @endif
        </div>
    </div>
</div>
@unless(($soloLectura ?? false) || $esSocioResultado)
<script>
document.addEventListener('DOMContentLoaded', function () {
    var sel = document.getElementById('resultado_informe');
    if (!sel) return;
    var mentira = document.querySelector('.js-resultado-detalle-mentira');
    var excepcion = document.querySelector('.js-resultado-detalle-excepcion');
    function sync() {
        if (mentira) mentira.style.display = sel.value === 'no_aprobado' ? '' : 'none';
        if (excepcion) excepcion.style.display = sel.value === 'aprobado_excepcion' ? '' : 'none';
    }
    sel.addEventListener('change', sync);
    sync();
});
</script>
@endunless
@endif
