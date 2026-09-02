@php
    $ayudaCtx = \App\Support\AyudaSupport::articuloContextual(auth()->user(), request()->path());
@endphp
@if($ayudaCtx)
<a href="{{ route('ayuda.show', $ayudaCtx['slug']) }}"
   class="btn btn-outline-info btn-sm btn-ayuda-contextual {{ $class ?? '' }}"
   title="Ayuda: {{ $ayudaCtx['titulo'] }}">
    <i class="bi bi-question-circle"></i>
    @if(empty($iconOnly))<span class="d-none d-md-inline ms-1">Ayuda</span>@endif
</a>
@endif
