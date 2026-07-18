@php
    $tipoMsg = $tipoFormulario ?? ($cuestionario->tipo_formulario ?? 'preempleo');
    $parrafo = \App\Support\MensajesInformacionImportante::parrafo($tipoMsg);
@endphp
<div class="alert alert-warning mt-4">
    <h6><i class="fas fa-info-circle"></i> Información importante</h6>
    <p class="mb-0 small">{{ $parrafo }}</p>
</div>
