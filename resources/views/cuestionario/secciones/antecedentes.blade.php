{{-- Sección 5: Salud, hábitos, aspecto judicial e información complementaria --}}

<div class="alert alert-info">
    <i class="fas fa-info-circle"></i>
    <strong>Salud, Hábitos y Aspectos Complementarios</strong>
    <div class="small mt-1 mb-0">Responda con honestidad. Las secciones marcadas como confidenciales son de uso interno de REPRO.</div>
</div>

@php
    use App\Support\AntecedentesJudiciales;
    use App\Support\InformacionComplementaria;
    use App\Support\SaludHabitosCampos;
    $respAnt = $respuestasExistentes ?? [];
    $sustanciasSel = old('sustancias_usadas', SaludHabitosCampos::sustanciasDesdeAlmacenamiento($respAnt['sustancias_usadas'] ?? null));
    if (is_string($sustanciasSel)) {
        $sustanciasSel = SaludHabitosCampos::sustanciasDesdeAlmacenamiento($sustanciasSel);
    }
@endphp

<hr class="my-4">
<h5 class="mb-3">{{ SaludHabitosCampos::TITULO_SALUD }}</h5>
<p class="text-muted small"><span class="badge bg-secondary">Confidencial</span> Debe responder usted. Uso interno de REPRO — no se incluye automáticamente en el informe a la empresa.</p>

<div class="form-group">
    <label for="salud_preocupaciones" class="form-label">{{ SaludHabitosCampos::LABEL_PREOCUPACIONES }} <span class="required">*</span></label>
    <textarea class="form-control" id="salud_preocupaciones" name="salud_preocupaciones" rows="2" required>{{ old('salud_preocupaciones', $respAnt['salud_preocupaciones'] ?? '') }}</textarea>
</div>

<div class="form-group">
    <label for="salud_estado_general" class="form-label">{{ SaludHabitosCampos::LABEL_ESTADO_GENERAL }} <span class="required">*</span></label>
    <select class="form-control" id="salud_estado_general" name="salud_estado_general" required>
        <option value="">Seleccione...</option>
        @foreach(SaludHabitosCampos::ESTADOS_GENERAL as $op => $et)
            <option value="{{ $op }}" {{ old('salud_estado_general', SaludHabitosCampos::normalizarEstadoGeneral($respAnt['salud_estado_general'] ?? '')) === $op ? 'selected' : '' }}>{{ $et }}</option>
        @endforeach
    </select>
</div>

<div class="form-group">
    <label for="salud_atencion_psicologica" class="form-label">{{ SaludHabitosCampos::LABEL_PSICOLOGICA }} <span class="required">*</span></label>
    <select class="form-control" id="salud_atencion_psicologica" name="salud_atencion_psicologica" required>
        <option value="">Seleccione...</option>
        <option value="no" {{ old('salud_atencion_psicologica', $respAnt['salud_atencion_psicologica'] ?? '') === 'no' ? 'selected' : '' }}>No</option>
        <option value="si" {{ old('salud_atencion_psicologica', $respAnt['salud_atencion_psicologica'] ?? '') === 'si' ? 'selected' : '' }}>Sí</option>
    </select>
</div>
<x-campo-condicional trigger="salud_atencion_psicologica" show-when="si">
    <div class="form-group">
        <label for="salud_detalle_psicologica" class="form-label">Amplíe la información <span class="required">*</span></label>
        <textarea class="form-control @error('salud_detalle_psicologica') is-invalid @enderror"
                  id="salud_detalle_psicologica"
                  name="salud_detalle_psicologica"
                  rows="3"
                  required>{{ old('salud_detalle_psicologica', $respAnt['salud_detalle_psicologica'] ?? '') }}</textarea>
        @error('salud_detalle_psicologica')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</x-campo-condicional>

<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label for="salud_tipo_sangre" class="form-label">{{ SaludHabitosCampos::LABEL_TIPO_SANGRE }} <span class="required">*</span></label>
            <input type="text" class="form-control @error('salud_tipo_sangre') is-invalid @enderror" id="salud_tipo_sangre" name="salud_tipo_sangre" value="{{ old('salud_tipo_sangre', $respAnt['salud_tipo_sangre'] ?? '') }}" required placeholder="Ej: O+">
            @error('salud_tipo_sangre')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="salud_peso" class="form-label">{{ SaludHabitosCampos::LABEL_PESO }} <span class="required">*</span></label>
            <input type="number" class="form-control" id="salud_peso" name="salud_peso" value="{{ old('salud_peso', $respAnt['salud_peso'] ?? '') }}" required>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="salud_estatura" class="form-label">{{ SaludHabitosCampos::LABEL_ESTATURA }} <span class="required">*</span></label>
            <input type="number" step="0.01" class="form-control" id="salud_estatura" name="salud_estatura" value="{{ old('salud_estatura', $respAnt['salud_estatura'] ?? '') }}" required>
        </div>
    </div>
</div>

<div class="form-group">
    <label for="salud_practica_deporte" class="form-label">{{ SaludHabitosCampos::LABEL_DEPORTE }} <span class="required">*</span></label>
    <select class="form-control @error('salud_practica_deporte') is-invalid @enderror" id="salud_practica_deporte" name="salud_practica_deporte" required>
        <option value="">Seleccione...</option>
        <option value="no" {{ old('salud_practica_deporte', $respAnt['salud_practica_deporte'] ?? '') === 'no' ? 'selected' : '' }}>No</option>
        <option value="si" {{ old('salud_practica_deporte', $respAnt['salud_practica_deporte'] ?? '') === 'si' ? 'selected' : '' }}>Sí</option>
    </select>
    @error('salud_practica_deporte')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<x-campo-condicional trigger="salud_practica_deporte" show-when="si">
    <div class="form-group">
        <label for="salud_detalle_deporte" class="form-label">¿Qué deporte practica?</label>
        <textarea class="form-control @error('salud_detalle_deporte') is-invalid @enderror"
                  id="salud_detalle_deporte"
                  name="salud_detalle_deporte"
                  rows="2">{{ old('salud_detalle_deporte', $respAnt['salud_detalle_deporte'] ?? '') }}</textarea>
        @error('salud_detalle_deporte')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</x-campo-condicional>

<div class="form-group">
    <label for="salud_tratamiento_medico" class="form-label">{{ SaludHabitosCampos::LABEL_TRATAMIENTO_MEDICO }} <span class="required">*</span></label>
    <select class="form-control @error('salud_tratamiento_medico') is-invalid @enderror" id="salud_tratamiento_medico" name="salud_tratamiento_medico" required>
        <option value="">Seleccione...</option>
        <option value="no" {{ old('salud_tratamiento_medico', $respAnt['salud_tratamiento_medico'] ?? '') === 'no' ? 'selected' : '' }}>No</option>
        <option value="si" {{ old('salud_tratamiento_medico', $respAnt['salud_tratamiento_medico'] ?? '') === 'si' ? 'selected' : '' }}>Sí</option>
    </select>
    @error('salud_tratamiento_medico')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<x-campo-condicional trigger="salud_tratamiento_medico" show-when="si">
    <div class="form-group">
        <label for="salud_detalle_tratamiento" class="form-label">Detalle del tratamiento <span class="required">*</span></label>
        <textarea class="form-control @error('salud_detalle_tratamiento') is-invalid @enderror"
                  id="salud_detalle_tratamiento"
                  name="salud_detalle_tratamiento"
                  rows="3"
                  required>{{ old('salud_detalle_tratamiento', $respAnt['salud_detalle_tratamiento'] ?? '') }}</textarea>
        @error('salud_detalle_tratamiento')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</x-campo-condicional>

<div class="form-group">
    <label for="salud_hospitalizaciones" class="form-label">{{ SaludHabitosCampos::LABEL_HOSPITALIZACIONES }} <span class="required">*</span></label>
    <select class="form-control @error('salud_hospitalizaciones') is-invalid @enderror" id="salud_hospitalizaciones" name="salud_hospitalizaciones" required>
        <option value="">Seleccione...</option>
        <option value="no" {{ old('salud_hospitalizaciones', $respAnt['salud_hospitalizaciones'] ?? '') === 'no' ? 'selected' : '' }}>No</option>
        <option value="si" {{ old('salud_hospitalizaciones', $respAnt['salud_hospitalizaciones'] ?? '') === 'si' ? 'selected' : '' }}>Sí</option>
    </select>
    @error('salud_hospitalizaciones')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<x-campo-condicional trigger="salud_hospitalizaciones" show-when="si">
    <div class="form-group">
        <label for="salud_detalle_hospitalizaciones" class="form-label">Detalle <span class="required">*</span></label>
        <textarea class="form-control @error('salud_detalle_hospitalizaciones') is-invalid @enderror"
                  id="salud_detalle_hospitalizaciones"
                  name="salud_detalle_hospitalizaciones"
                  rows="3"
                  required>{{ old('salud_detalle_hospitalizaciones', $respAnt['salud_detalle_hospitalizaciones'] ?? '') }}</textarea>
        @error('salud_detalle_hospitalizaciones')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</x-campo-condicional>

@include('cuestionario.secciones.partials.campos-alergias-embarazo')

<div class="form-group">
    <label for="tiene_tatuajes" class="form-label">{{ SaludHabitosCampos::LABEL_TATUAJES_PERFORACIONES }} <span class="required">*</span></label>
    <select class="form-control" id="tiene_tatuajes" name="tiene_tatuajes" required>
        <option value="">Seleccione...</option>
        <option value="no" {{ old('tiene_tatuajes', $respAnt['tiene_tatuajes'] ?? '') === 'no' ? 'selected' : '' }}>No</option>
        <option value="si" {{ old('tiene_tatuajes', $respAnt['tiene_tatuajes'] ?? '') === 'si' ? 'selected' : '' }}>Sí</option>
    </select>
    <input type="hidden" name="tiene_perforaciones" value="{{ old('tiene_perforaciones', $respAnt['tiene_perforaciones'] ?? 'no') }}">
</div>
<x-campo-condicional trigger="tiene_tatuajes" show-when="si">
    <x-tabla-dinamica name="tatuajes" titulo="Tatuajes" :columnas="\App\Support\TablaDinamica::columnasTatuajes()" :filas="$tablasExistentes['tatuajes'] ?? []" :minFilas="1" textoAgregar="Agregar tatuaje" textoEliminar="Quitar" />
    <x-tabla-dinamica name="perforaciones" titulo="Perforaciones" :columnas="\App\Support\TablaDinamica::columnasPerforaciones()" :filas="$tablasExistentes['perforaciones'] ?? []" :minFilas="0" textoAgregar="Agregar perforación" textoEliminar="Quitar" />
</x-campo-condicional>

<div class="form-group">
    <label for="salud_intento_suicidio" class="form-label">{{ SaludHabitosCampos::LABEL_SUICIDIO }} <span class="required">*</span></label>
    <textarea class="form-control" id="salud_intento_suicidio" name="salud_intento_suicidio" rows="2" required>{{ old('salud_intento_suicidio', $respAnt['salud_intento_suicidio'] ?? '') }}</textarea>
</div>

<div class="form-group">
    <label for="salud_ausencias_enfermedad" class="form-label">{{ SaludHabitosCampos::LABEL_AUSENCIAS_ENFERMEDAD }} <span class="required">*</span></label>
    <textarea class="form-control @error('salud_ausencias_enfermedad') is-invalid @enderror"
              id="salud_ausencias_enfermedad"
              name="salud_ausencias_enfermedad"
              rows="2"
              required>{{ old('salud_ausencias_enfermedad', is_string($respAnt['salud_ausencias_enfermedad'] ?? null) ? $respAnt['salud_ausencias_enfermedad'] : ($respAnt['salud_detalle_ausencias'] ?? '')) }}</textarea>
    @error('salud_ausencias_enfermedad')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<hr class="my-3">
<h5 class="mb-3">{{ SaludHabitosCampos::TITULO_HABITOS }}</h5>
@foreach(SaludHabitosCampos::HABITOS as $campo => $etiqueta)
<div class="form-group">
    <label for="{{ $campo }}" class="form-label">{{ $etiqueta }} <span class="required">*</span></label>
    <input type="text" class="form-control" id="{{ $campo }}" name="{{ $campo }}"
           value="{{ old($campo, $respAnt[$campo] ?? '') }}" required>
</div>
@endforeach

<hr class="my-3">
<h5 class="mb-2">{{ SaludHabitosCampos::TITULO_SUSTANCIAS }}</h5>
<p class="text-muted small">{{ SaludHabitosCampos::INTRO_SUSTANCIAS }}</p>
<div class="form-group">
    <label class="form-label d-block">Marque las sustancias que conoce, ha experimentado o usado</label>
    @foreach(SaludHabitosCampos::SUSTANCIAS as $k => $et)
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="sustancias_usadas[]" id="sust_{{ $k }}" value="{{ $k }}"
                   {{ in_array($k, $sustanciasSel, true) ? 'checked' : '' }}>
            <label class="form-check-label" for="sust_{{ $k }}">{{ $et }}</label>
        </div>
    @endforeach
</div>
@foreach(SaludHabitosCampos::SUSTANCIAS_PREGUNTAS as $campo => $etiqueta)
<div class="form-group">
    <label for="{{ $campo }}" class="form-label">{{ $etiqueta }}</label>
    <textarea class="form-control" id="{{ $campo }}" name="{{ $campo }}" rows="2">{{ old($campo, $respAnt[$campo] ?? '') }}</textarea>
</div>
@endforeach

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
