{{-- E5 — Antecedentes recientes (solo aspecto judicial; sin complementaria) --}}
@php
    use App\Support\AntecedentesJudiciales;
    $respAnt = $respuestasExistentes ?? [];
@endphp

<div class="alert alert-info">
    <i class="fas fa-shield-alt"></i>
    <strong>Antecedentes recientes — aspecto judicial</strong>
</div>

@include('cuestionario.secciones.partials.preguntas-textarea', [
    'titulo' => AntecedentesJudiciales::TITULO_BLOQUE,
    'badge' => 'Confidencial',
    'preguntas' => AntecedentesJudiciales::PREGUNTAS,
    'respuestas' => $respAnt,
])

@include('cuestionario.partials.informacion-importante', [
    'tipoFormulario' => $cuestionario->tipo_formulario ?? 'periodica',
])

<div class="form-group">
    <label for="informacion_adicional_final" class="form-label">Si desea agregar alguna información adicional, escríbala aquí</label>
    <textarea class="form-control" id="informacion_adicional_final" name="informacion_adicional_final" rows="4">{{ old('informacion_adicional_final', $respAnt['informacion_adicional_final'] ?? '') }}</textarea>
</div>
