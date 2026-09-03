@props([
    'trigger',
    'showWhen' => null,
    'hideWhen' => null,
    'clearOnHide' => true,
])

<div {{ $attributes->class(['d-none']) }}
     data-condicional
     data-condicional-trigger="{{ $trigger }}"
     @if($showWhen !== null) data-condicional-show-when="{{ $showWhen }}" @endif
     @if($hideWhen !== null) data-condicional-hide-when="{{ $hideWhen }}" @endif
     @if($clearOnHide) data-condicional-clear-on-hide="1" @endif>
    {{ $slot }}
</div>

@once
    @push('scripts')
        <script src="{{ \App\Support\PublicAsset::url('js/campos-condicionales.js') }}"></script>
    @endpush
@endonce
