{{-- Bloque de preguntas textarea desde spec PHP --}}
@props(['preguntas' => [], 'respuestas' => [], 'titulo' => '', 'badge' => null, 'nota' => null])

@if($titulo !== '')
<h5 class="mt-4 mb-3">{{ $titulo }}</h5>
@endif
@if($badge)
<p class="text-muted small">
    <span class="badge bg-secondary">{{ $badge }}</span>
    {{ $nota ?? 'Debe responder usted con sinceridad. Esta información es confidencial para REPRO y no se incluye automáticamente en el informe entregado a la empresa.' }}
</p>
@endif

@foreach($preguntas as $i => $pregunta)
    @php
        $key = $pregunta['key'];
        $valor = old($key, $respuestas[$key] ?? '');
    @endphp
    <div class="form-group">
        <label for="{{ $key }}" class="form-label">
            {{ ($i + 1) }}. {{ $pregunta['label'] }} <span class="required">*</span>
        </label>
        <textarea class="form-control @error($key) is-invalid @enderror"
                  id="{{ $key }}" name="{{ $key }}" rows="3" required>{{ $valor }}</textarea>
        @error($key)<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
@endforeach
