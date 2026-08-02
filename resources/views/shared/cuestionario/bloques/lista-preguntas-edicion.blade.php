{{-- Edición de preguntas con respuestas_campo[slug][key] --}}
@props(['bloques' => [], 'respuestas' => [], 'slug' => ''])

@foreach($bloques as $bloque)
    <div class="card mb-3">
        <div class="card-header">
            <h6 class="mb-0">{{ $bloque['titulo'] }}</h6>
            @if(!empty($bloque['badge']))
                <span class="badge bg-secondary mt-1">{{ $bloque['badge'] }}</span>
            @endif
        </div>
        <div class="card-body">
            @foreach($bloque['preguntas'] as $i => $pregunta)
                @php $valor = old('respuestas_campo.'.$slug.'.'.$pregunta['key'], $respuestas[$pregunta['key']] ?? ''); @endphp
                <div class="form-group mb-3">
                    <label class="form-label">{{ $i + 1 }}. {{ $pregunta['label'] }}</label>
                    <textarea class="form-control"
                              name="respuestas_campo[{{ $slug }}][{{ $pregunta['key'] }}]"
                              rows="3">{{ $valor }}</textarea>
                </div>
            @endforeach
        </div>
    </div>
@endforeach
