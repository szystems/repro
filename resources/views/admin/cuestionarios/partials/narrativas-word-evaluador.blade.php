{{-- Redacción narrativa del informe Word (6 bloques obligatorios antes del informe final) --}}
@if($puedeGestionarNotasEvaluador ?? false)
<div class="card mt-4 border-warning">
    <div class="card-header bg-warning bg-opacity-10">
        <h6 class="mb-0">
            <i class="bi bi-file-earmark-word"></i> Redacción del informe Word
            <span class="badge bg-warning text-dark ms-2">Obligatorio para informe final</span>
        </h6>
        <small class="text-muted d-block mt-1">
            Estos seis bloques alimentan el informe Word. Deben completarse antes de subir el informe final al cliente.
        </small>
    </div>
    <div class="card-body">
        @foreach(\App\Support\InformeWordBloquesEvaluador::BLOQUES as $index => $bloque)
            @php
                $slug = $bloque['slug'];
                $valor = old('evaluador_notas.'.$slug, $notasEvaluador[$slug] ?? '');
            @endphp
            <div class="mb-3">
                <label class="form-label fw-semibold" for="word_bloque_{{ $slug }}">
                    {{ $index + 1 }}. {{ $bloque['titulo'] }}
                    @if($valor !== '')
                        <span class="badge bg-success ms-1">Completo</span>
                    @else
                        <span class="badge bg-secondary ms-1">Pendiente</span>
                    @endif
                </label>
                @if($soloLectura ?? false)
                    <div class="bg-light border rounded p-3 mb-0" style="white-space: pre-wrap;">{{ $valor ?: '— Sin redacción —' }}</div>
                @else
                    <textarea class="form-control"
                              id="word_bloque_{{ $slug }}"
                              name="evaluador_notas[{{ $slug }}]"
                              rows="5"
                              placeholder="Redacción del evaluador para {{ $bloque['titulo'] }}…">{{ $valor }}</textarea>
                @endif
            </div>
        @endforeach
    </div>
</div>
@endif
