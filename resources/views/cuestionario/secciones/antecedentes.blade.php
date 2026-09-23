{{-- Sección 5: Salud, hábitos, aspecto judicial e información complementaria --}}

<div class="alert alert-info">
    <i class="fas fa-info-circle"></i>
    <strong>Salud, Hábitos y Aspectos Complementarios</strong>
    <div class="small mt-1 mb-0">Responda con honestidad. Las secciones marcadas como confidenciales son de uso interno de REPRO.</div>
</div>

@php
    use App\Support\AntecedentesJudiciales;
    use App\Support\InformacionComplementaria;
    $respAnt = $respuestasExistentes ?? [];
@endphp

@include('cuestionario.secciones.partials.campos-salud-habitos')

@include('cuestionario.secciones.partials.preguntas-textarea', [
    'titulo' => AntecedentesJudiciales::TITULO_BLOQUE,
    'badge' => 'Confidencial',
    'preguntas' => AntecedentesJudiciales::PREGUNTAS,
    'respuestas' => $respAnt,
])

@include('cuestionario.secciones.partials.preguntas-textarea', [
    'titulo' => InformacionComplementaria::TITULO_BLOQUE,
    'preguntas' => InformacionComplementaria::PREGUNTAS,
    'respuestas' => $respAnt,
])

@include('cuestionario.partials.informacion-importante', [
    'tipoFormulario' => $cuestionario->tipo_formulario ?? 'preempleo',
])

<div class="form-group">
    <label for="informacion_adicional_final" class="form-label">Si desea agregar alguna información adicional, escríbala aquí</label>
    <textarea class="form-control" id="informacion_adicional_final" name="informacion_adicional_final" rows="4">{{ old('informacion_adicional_final', $respAnt['informacion_adicional_final'] ?? '') }}</textarea>
</div>
