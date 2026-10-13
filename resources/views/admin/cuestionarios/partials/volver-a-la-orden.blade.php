@php
    $evaluadoOrdenLink = $cuestionario->evaluadoOrden ?? null;
@endphp
@if($evaluadoOrdenLink)
<a href="{{ route('ordenes.show', $evaluadoOrdenLink->orden_id) }}#resultado-final-{{ $evaluadoOrdenLink->id }}"
   class="btn btn-outline-primary {{ $class ?? '' }}"
   title="Regresar a la orden de este candidato para subir el informe">
    <i class="bi bi-arrow-return-left"></i> Volver a la orden
</a>
@endif
