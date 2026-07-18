@php
    $contenidoAutorizacion = $contenidoAutorizacion ?? \App\Support\AutorizacionesLegales::renderHtml($evaluado);
@endphp

<div class="border rounded p-3 mb-4" style="max-height: 400px; overflow-y: auto; background: #fafafa;">
    {!! $contenidoAutorizacion !!}
    <p class="mt-3 mb-0"><strong>Fecha:</strong> {{ now()->format('d/m/Y') }}</p>
    <p class="mb-0"><strong>Lugar:</strong> Guatemala</p>
</div>
