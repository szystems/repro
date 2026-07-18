{{-- Bloque de preguntas textarea desde spec PHP --}}
@props(['preguntas' => [], 'respuestas' => [], 'titulo' => '', 'badge' => null, 'nota' => null, 'inicioNumero' => 1, 'rows' => 3])

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
        $numero = (int) $inicioNumero + $i;
        $filas = (int) ($pregunta['rows'] ?? $rows);
    @endphp
    <div class="form-group">
        <label for="{{ $key }}" class="form-label">
            {{ $numero }}. {{ $pregunta['label'] }} <span class="required">*</span>
        </label>
        <textarea class="form-control @error($key) is-invalid @enderror"
                  id="{{ $key }}" name="{{ $key }}" rows="{{ $filas }}" required>{{ $valor }}</textarea>
        @error($key)<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
@endforeach
