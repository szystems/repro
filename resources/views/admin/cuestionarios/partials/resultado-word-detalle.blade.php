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
    $preguntasDi = \App\Support\InformeWordPreguntasPoligraficas::textoConclusionDi(
        \App\Support\InformeWordPreguntasPoligraficas::filasGuardadas($evaluadoInforme->id)
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
            <small class="text-muted d-block mb-2">
                Las preguntas marcadas DI se copian solas a la primera hoja y al final del Word, cada una en su renglón. No hace falta escribirlas aquí. Use el cuadro solo para un dato que no esté en la tabla.
            </small>
            <div class="bg-light border rounded p-2 mb-2 js-preguntas-di @if($preguntasDi === '' || $resultadoActual !== 'no_aprobado') d-none @endif" style="white-space: pre-wrap;">{{ $preguntasDi }}</div>
            @if($soloLectura ?? false)
                <div class="bg-light border rounded p-3 mb-0" style="white-space: pre-wrap;">{{ $detalleMentira ?: '— Sin detalle —' }}</div>
            @else
                <textarea class="form-control"
                          id="word_resultado_mentira"
                          name="evaluador_notas[{{ \App\Support\InformeWordResultado::NOTA_INDICACION_MENTIRA }}]"
                          rows="2"
                          placeholder="Ej: un dato que no esté en la tabla de preguntas.">{{ $detalleMentira }}</textarea>
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
                Solo aparece lo que escriba aquí. El asistente corrige ese texto y puede dejarlo en viñetas; no agrega datos del formulario. Si deja este espacio vacío, el cuadro se entrega en blanco.
            </small>
            @if($soloLectura ?? false)
                <div class="bg-light border rounded p-3 mb-0" style="white-space: pre-wrap;">{{ $observacionesWord ?: '— Sin observaciones —' }}</div>
            @else
                <textarea class="form-control"
                          id="word_observaciones"
                          name="evaluador_notas[{{ \App\Support\InformeWordBloquesEvaluador::NOTA_OBSERVACIONES }}]"
                          rows="3"
                          placeholder="Ej: pendiente el envío de antecedentes. Tiene actas administrativas.">{{ $observacionesWord }}</textarea>
                @include('admin.cuestionarios.partials.asistente-redaccion-boton', [
                    'slug' => \App\Support\InformeWordBloquesEvaluador::NOTA_OBSERVACIONES,
                    'titulo' => 'Observaciones de la primera hoja',
                ])
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
    var listaDi = document.querySelector('.js-preguntas-di');
    function textoDi() {
        var lineas = [];
        document.querySelectorAll('#tablaPreguntasPoligraficas tbody tr').forEach(function (tr) {
            if (tr.classList.contains('fila-vacia-poligrafica')) {
                return;
            }
            var resultado = tr.querySelector('[name$="[resultado]"]');
            var pregunta = tr.querySelector('[name$="[pregunta]"]');
            if (!resultado || !pregunta) {
                return;
            }
            var marca = (resultado.value || '').replace(/\s+/g, '').toUpperCase();
            if (marca === '' || marca.indexOf('NDI') === 0) {
                return;
            }
            var esDi = marca === 'DI' || marca.indexOf('DI') === 0 || marca.indexOf('INDICACIONDEMENTIRA') !== -1;
            var texto = (pregunta.value || '').trim();
            if (!esDi || texto === '') {
                return;
            }
            var numero = tr.querySelector('td');
            var n = numero ? parseInt(numero.textContent, 10) : lineas.length + 1;
            if (!n) {
                n = lineas.length + 1;
            }
            lineas.push('R' + n + '. ' + texto);
        });
        return lineas.join('\n');
    }
    function pintarDi() {
        if (!listaDi) {
            return;
        }
        var texto = sel.value === 'no_aprobado' ? textoDi() : '';
        listaDi.textContent = texto;
        listaDi.classList.toggle('d-none', texto === '');
    }
    function sync() {
        if (mentira) mentira.style.display = sel.value === 'no_aprobado' ? '' : 'none';
        if (excepcion) excepcion.style.display = sel.value === 'aprobado_excepcion' ? '' : 'none';
        pintarDi();
    }
    sel.addEventListener('change', sync);
    document.addEventListener('change', function (evento) {
        if (evento.target && evento.target.closest && evento.target.closest('#tablaPreguntasPoligraficas')) {
            pintarDi();
        }
    });
    document.addEventListener('input', function (evento) {
        if (evento.target && evento.target.closest && evento.target.closest('#tablaPreguntasPoligraficas')) {
            pintarDi();
        }
    });
    sync();
});
</script>
@endunless
@endif
