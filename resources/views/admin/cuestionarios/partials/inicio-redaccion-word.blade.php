{{-- Separador visual: a partir de aquí se redacta el informe Word, no el PDF del candidato. --}}
@if(($puedeGestionarNotasEvaluador ?? false) || (($informePreempleoActivo ?? false) && ($puedeGestionarInformePreempleo ?? false)))
<div class="alert alert-primary mt-4 mb-0 seccion-inicio-redaccion-word" id="inicio-redaccion-informe-word" role="region" aria-label="Inicio de redacción de informe en Word">
    <h5 class="mb-1 text-uppercase">
        <i class="bi bi-file-earmark-word"></i>
        Inicio de redacción de informe en Word
    </h5>
    <p class="mb-0 small">
        Desde aquí se editan el resultado (un campo para 1ª y última hoja), las tablas y la redacción que van al informe Word.
        Lo de arriba en rojo es el contenido que llenó el candidato.
    </p>
</div>
@endif
