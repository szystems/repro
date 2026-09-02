{{-- E5 — Antecedentes recientes (aspecto judicial + alergias/embarazo M-F2/F3 + tatuajes) --}}
@php
    use App\Support\AntecedentesJudiciales;
    use App\Support\SaludHabitosCampos;
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

<hr class="my-4">
<h5 class="mb-3">{{ SaludHabitosCampos::TITULO_SALUD }}</h5>
<p class="text-muted small"><span class="badge bg-secondary">Confidencial</span> Uso interno de REPRO. Si no aplica embarazo, seleccione No.</p>
@include('cuestionario.secciones.partials.campos-alergias-embarazo')

<div class="form-group mt-4">
    <label for="tiene_tatuajes" class="form-label">{{ SaludHabitosCampos::LABEL_TATUAJES_PERFORACIONES }} <span class="required">*</span></label>
    <select class="form-control" id="tiene_tatuajes" name="tiene_tatuajes" required>
        <option value="">Seleccione...</option>
        <option value="no" {{ old('tiene_tatuajes', $respAnt['tiene_tatuajes'] ?? '') === 'no' ? 'selected' : '' }}>No</option>
        <option value="si" {{ old('tiene_tatuajes', $respAnt['tiene_tatuajes'] ?? '') === 'si' ? 'selected' : '' }}>Sí</option>
    </select>
</div>
<x-campo-condicional trigger="tiene_tatuajes" show-when="si">
    <x-tabla-dinamica
        name="tatuajes"
        titulo="Tatuajes"
        :columnas="\App\Support\TablaDinamica::columnasTatuajes()"
        :filas="$tablasExistentes['tatuajes'] ?? []"
        :minFilas="1"
        textoAgregar="Agregar tatuaje"
        textoEliminar="Quitar"
    />
</x-campo-condicional>

<div class="form-group">
    <label for="informacion_adicional_final" class="form-label">Si desea agregar alguna información adicional, escríbala aquí</label>
    <textarea class="form-control" id="informacion_adicional_final" name="informacion_adicional_final" rows="4">{{ old('informacion_adicional_final', $respAnt['informacion_adicional_final'] ?? '') }}</textarea>
</div>
