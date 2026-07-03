{{-- E1.8 — Notas internas del evaluador (solo REPRO/ADMIN) --}}
@if($puedeGestionarNotasEvaluador ?? false)
<div class="card mt-4 border-primary">
    <div class="card-header bg-primary bg-opacity-10">
        <h6 class="mb-0">
            <i class="bi bi-journal-text"></i> Notas internas del evaluador
            <span class="badge bg-primary ms-2">Solo REPRO</span>
        </h6>
        <small class="text-muted d-block mt-1">
            Análisis y observaciones para el informe. No son visibles para la empresa ni para el candidato.
        </small>
    </div>
    <div class="card-body">
        <div class="accordion" id="accordionNotasEvaluador">
            @foreach($bloquesNotasEvaluador as $index => $bloque)
                @php
                    $slug = $bloque['slug'];
                    $valor = old('evaluador_notas.'.$slug, $notasEvaluador[$slug] ?? '');
                @endphp
                <div class="accordion-item">
                    <h2 class="accordion-header" id="notaHeading{{ $index }}">
                        <button class="accordion-button {{ $index > 0 ? 'collapsed' : '' }}"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#notaCollapse{{ $index }}"
                                aria-expanded="{{ $index === 0 ? 'true' : 'false' }}">
                            {{ $bloque['numero'] }}. {{ $bloque['titulo'] }}
                            @if(!empty($notasEvaluador[$slug] ?? null))
                                <span class="badge bg-success ms-2">Con notas</span>
                            @endif
                        </button>
                    </h2>
                    <div id="notaCollapse{{ $index }}"
                         class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}"
                         data-bs-parent="#accordionNotasEvaluador">
                        <div class="accordion-body">
                            @if($soloLectura ?? false)
                                <div class="bg-light border rounded p-3 mb-0" style="white-space: pre-wrap;">{{ $valor ?: '— Sin notas —' }}</div>
                            @else
                                <textarea class="form-control"
                                          name="evaluador_notas[{{ $slug }}]"
                                          rows="4"
                                          placeholder="Observaciones, análisis o conclusiones sobre {{ $bloque['titulo'] }}…">{{ $valor }}</textarea>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endif
