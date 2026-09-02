@php
    $inputName = "{$name}[{$index}][{$col['key']}]";
    $inputId = str_replace(['[', ']'], ['_', ''], $inputName);
    $required = ($col['required'] ?? false) ? 'required' : '';
    $errorKey = "{$name}.{$index}.{$col['key']}";
@endphp

@if(($col['readonly'] ?? false) && ! empty($col['options']))
    @php
        $etiquetaNivel = $col['options'][$valor] ?? $valor;
    @endphp
    <input type="hidden"
           name="{{ $inputName }}"
           value="{{ $valor }}">
    <span class="form-control-plaintext form-control-sm py-0">{{ $etiquetaNivel }}</span>
@elseif(($col['type'] ?? 'text') === 'date_range')
    @include('components.partials.tabla-dinamica-campo-date-range', [
        'name' => $name,
        'index' => $index,
        'col' => $col,
        'valor' => $valor,
    ])
@elseif(($col['type'] ?? 'text') === 'select')
    <select class="form-control form-control-sm @error($errorKey) is-invalid @enderror"
            id="{{ $inputId }}"
            name="{{ $inputName }}"
            {{ $required }}>
        <option value="">Seleccione...</option>
        @foreach($col['options'] ?? [] as $optVal => $optLabel)
            <option value="{{ $optVal }}" {{ (string) $valor === (string) $optVal ? 'selected' : '' }}>{{ $optLabel }}</option>
        @endforeach
    </select>
@elseif(($col['type'] ?? 'text') === 'digits')
    <input type="text"
           inputmode="numeric"
           pattern="[0-9]*"
           class="form-control form-control-sm tabla-dinamica-input-digits @error($errorKey) is-invalid @enderror"
           id="{{ $inputId }}"
           name="{{ $inputName }}"
           value="{{ $valor }}"
           @if(isset($col['max'])) maxlength="{{ $col['max'] }}" @endif
           {{ $required }}>
@elseif(($col['type'] ?? 'text') === 'currency')
    <input type="text"
           class="form-control form-control-sm @error($errorKey) is-invalid @enderror"
           id="{{ $inputId }}"
           name="{{ $inputName }}"
           value="{{ $valor }}"
           placeholder="Ej: Q3500"
           @if(isset($col['max'])) maxlength="{{ $col['max'] }}" @endif
           {{ $required }}>
@else
    <input type="{{ $col['type'] ?? 'text' }}"
           class="form-control form-control-sm @error($errorKey) is-invalid @enderror"
           id="{{ $inputId }}"
           name="{{ $inputName }}"
           value="{{ $valor }}"
           @if(isset($col['max'])) maxlength="{{ $col['max'] }}" @endif
           @if(($col['type'] ?? '') === 'number')
               @if(isset($col['min'])) min="{{ $col['min'] }}" @endif
               @if(isset($col['max'])) max="{{ $col['max'] }}" @endif
           @endif
           {{ $required }}>
@endif
@error($errorKey)
    <div class="invalid-feedback d-block">{{ $message }}</div>
@enderror
