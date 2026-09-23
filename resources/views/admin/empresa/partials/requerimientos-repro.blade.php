@php
    $textoRequerimientos = old('requerimientos_repro', isset($empresa) ? ($empresa->requerimientos_repro ?? '') : '');
@endphp
<div class="card mb-3">
    <div class="card-header">
        <h6 class="mb-0">Requerimientos especiales</h6>
    </div>
    <div class="card-body">
        <p class="text-muted small">
            Solicitudes fijas de esta empresa. Solo las ve REPRO, en esta ficha, en la orden y en el cuestionario.
            No salen en el informe ni en el portal de la empresa.
        </p>
        <textarea class="form-control" name="requerimientos_repro" id="requerimientos_repro" rows="4" maxlength="5000" placeholder="Por ejemplo: pedir constancia laboral de los últimos dos empleos.">{{ $textoRequerimientos }}</textarea>
    </div>
</div>
