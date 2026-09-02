@php
    $contenidoAutorizacion = $contenidoAutorizacion ?? \App\Support\AutorizacionesLegales::renderHtml($evaluado);
@endphp

<div class="border rounded p-3 mb-4" style="max-height: 400px; overflow-y: auto; background: #fafafa;">
    {!! $contenidoAutorizacion !!}
</div>
