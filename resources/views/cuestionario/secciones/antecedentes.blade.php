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
    <label for="salud_preocupaciones" class="form-label">¿Cuál es el problema personal o situación que actualmente le genera mayor preocupación? <span class="required">*</span></label>
    <textarea class="form-control" id="salud_preocupaciones" name="salud_preocupaciones" rows="2" required>{{ old('salud_preocupaciones', $respAnt['salud_preocupaciones'] ?? '') }}</textarea>
</div>
<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label for="salud_estado_general" class="form-label">¿Cómo considera su estado general de salud actual? <span class="required">*</span></label>
            <select class="form-control" id="salud_estado_general" name="salud_estado_general" required>
                @foreach(SaludHabitosCampos::ESTADOS_GENERAL as $op => $et)
                    <option value="{{ $op }}" {{ old('salud_estado_general', SaludHabitosCampos::normalizarEstadoGeneral($respAnt['salud_estado_general'] ?? '')) === $op ? 'selected' : '' }}>{{ $et }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="salud_tipo_sangre" class="form-label">Tipo de sangre <span class="required">*</span></label>
            <input type="text" class="form-control @error('salud_tipo_sangre') is-invalid @enderror" id="salud_tipo_sangre" name="salud_tipo_sangre" value="{{ old('salud_tipo_sangre', $respAnt['salud_tipo_sangre'] ?? '') }}" required placeholder="Ej: O+">
            @error('salud_tipo_sangre')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group">
            <label for="salud_peso" class="form-label">Peso (libras) <span class="required">*</span></label>
            <input type="number" class="form-control" id="salud_peso" name="salud_peso" value="{{ old('salud_peso', $respAnt['salud_peso'] ?? '') }}" required>
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group">
            <label for="salud_estatura" class="form-label">Estatura (m) <span class="required">*</span></label>
            <input type="number" step="0.01" class="form-control" id="salud_estatura" name="salud_estatura" value="{{ old('salud_estatura', $respAnt['salud_estatura'] ?? '') }}" required>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="salud_atencion_psicologica" class="form-label">¿Ha recibido atención psicológica o psiquiátrica? <span class="required">*</span></label>
            <select class="form-control" id="salud_atencion_psicologica" name="salud_atencion_psicologica" required>
                <option value="no" {{ old('salud_atencion_psicologica', $respAnt['salud_atencion_psicologica'] ?? '') === 'no' ? 'selected' : '' }}>No</option>
                <option value="si" {{ old('salud_atencion_psicologica', $respAnt['salud_atencion_psicologica'] ?? '') === 'si' ? 'selected' : '' }}>Sí</option>
            </select>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="salud_situacion_emocional" class="form-label">¿Ha atravesado alguna situación emocional o personal que haya afectado significativamente su bienestar o sus actividades diarias? <span class="required">*</span></label>
            <textarea class="form-control" id="salud_situacion_emocional" name="salud_situacion_emocional" rows="2" required>{{ old('salud_situacion_emocional', $respAnt['salud_situacion_emocional'] ?? '') }}</textarea>
        </div>
    </div>
</div>
<x-campo-condicional trigger="salud_atencion_psicologica" show-when="si">
    <div class="form-group">
        <label for="salud_detalle_psicologica" class="form-label">Amplíe la información sobre la atención psicológica o psiquiátrica recibida <span class="required">*</span></label>
        <textarea class="form-control @error('salud_detalle_psicologica') is-invalid @enderror"
                  id="salud_detalle_psicologica"
                  name="salud_detalle_psicologica"
                  rows="3"
                  required
                  placeholder="Motivo, duración, institución o profesional que lo atendió...">{{ old('salud_detalle_psicologica', $respAnt['salud_detalle_psicologica'] ?? '') }}</textarea>
        @error('salud_detalle_psicologica')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</x-campo-condicional>
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="salud_ideacion_dano" class="form-label">¿Ha llegado a pensar en hacerse daño o en no continuar con su vida debido a alguna situación personal o emocional? <span class="required">*</span></label>
            <select class="form-control @error('salud_ideacion_dano') is-invalid @enderror" id="salud_ideacion_dano" name="salud_ideacion_dano" required>
                <option value="no" {{ old('salud_ideacion_dano', $respAnt['salud_ideacion_dano'] ?? '') === 'no' ? 'selected' : '' }}>No</option>
                <option value="si" {{ old('salud_ideacion_dano', $respAnt['salud_ideacion_dano'] ?? '') === 'si' ? 'selected' : '' }}>Sí</option>
            </select>
            @error('salud_ideacion_dano')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <x-campo-condicional trigger="salud_ideacion_dano" show-when="si">
            <div class="form-group">
                <label for="salud_detalle_ideacion" class="form-label">Detalle <span class="required">*</span></label>
                <textarea class="form-control @error('salud_detalle_ideacion') is-invalid @enderror"
                          id="salud_detalle_ideacion"
                          name="salud_detalle_ideacion"
                          rows="3"
                          required
                          placeholder="Cuándo ocurrió, si recibió ayuda, situación actual...">{{ old('salud_detalle_ideacion', $respAnt['salud_detalle_ideacion'] ?? '') }}</textarea>
                @error('salud_detalle_ideacion')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </x-campo-condicional>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="salud_practica_deporte" class="form-label">¿Practica algún deporte? <span class="required">*</span></label>
            <select class="form-control @error('salud_practica_deporte') is-invalid @enderror" id="salud_practica_deporte" name="salud_practica_deporte" required>
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
                          rows="2"
                          placeholder="Deporte, frecuencia, club o equipo...">{{ old('salud_detalle_deporte', $respAnt['salud_detalle_deporte'] ?? '') }}</textarea>
                @error('salud_detalle_deporte')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </x-campo-condicional>
    </div>
</div>
<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label for="salud_tratamiento_medico" class="form-label">¿Recibe tratamiento médico? <span class="required">*</span></label>
            <select class="form-control @error('salud_tratamiento_medico') is-invalid @enderror" id="salud_tratamiento_medico" name="salud_tratamiento_medico" required>
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
                          required
                          placeholder="Diagnóstico, medicamentos, médico tratante...">{{ old('salud_detalle_tratamiento', $respAnt['salud_detalle_tratamiento'] ?? '') }}</textarea>
                @error('salud_detalle_tratamiento')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </x-campo-condicional>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="salud_hospitalizaciones" class="form-label">¿Ha tenido hospitalizaciones? <span class="required">*</span></label>
            <select class="form-control @error('salud_hospitalizaciones') is-invalid @enderror" id="salud_hospitalizaciones" name="salud_hospitalizaciones" required>
                <option value="no" {{ old('salud_hospitalizaciones', $respAnt['salud_hospitalizaciones'] ?? '') === 'no' ? 'selected' : '' }}>No</option>
                <option value="si" {{ old('salud_hospitalizaciones', $respAnt['salud_hospitalizaciones'] ?? '') === 'si' ? 'selected' : '' }}>Sí</option>
            </select>
            @error('salud_hospitalizaciones')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <x-campo-condicional trigger="salud_hospitalizaciones" show-when="si">
            <div class="form-group">
                <label for="salud_detalle_hospitalizaciones" class="form-label">Detalle de hospitalizaciones <span class="required">*</span></label>
                <textarea class="form-control @error('salud_detalle_hospitalizaciones') is-invalid @enderror"
                          id="salud_detalle_hospitalizaciones"
                          name="salud_detalle_hospitalizaciones"
                          rows="3"
                          required
                          placeholder="Motivo, fecha aproximada, hospital...">{{ old('salud_detalle_hospitalizaciones', $respAnt['salud_detalle_hospitalizaciones'] ?? '') }}</textarea>
                @error('salud_detalle_hospitalizaciones')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </x-campo-condicional>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="salud_ausencias_enfermedad" class="form-label">¿Ausencias por enfermedad? <span class="required">*</span></label>
            <select class="form-control @error('salud_ausencias_enfermedad') is-invalid @enderror" id="salud_ausencias_enfermedad" name="salud_ausencias_enfermedad" required>
                <option value="no" {{ old('salud_ausencias_enfermedad', $respAnt['salud_ausencias_enfermedad'] ?? '') === 'no' ? 'selected' : '' }}>No</option>
                <option value="si" {{ old('salud_ausencias_enfermedad', $respAnt['salud_ausencias_enfermedad'] ?? '') === 'si' ? 'selected' : '' }}>Sí</option>
            </select>
            @error('salud_ausencias_enfermedad')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <x-campo-condicional trigger="salud_ausencias_enfermedad" show-when="si">
            <div class="form-group">
                <label for="salud_detalle_ausencias" class="form-label">Detalle de ausencias <span class="required">*</span></label>
                <textarea class="form-control @error('salud_detalle_ausencias') is-invalid @enderror"
                          id="salud_detalle_ausencias"
                          name="salud_detalle_ausencias"
                          rows="3"
                          required
                          placeholder="Motivo, duración, año aproximado...">{{ old('salud_detalle_ausencias', $respAnt['salud_detalle_ausencias'] ?? '') }}</textarea>
                @error('salud_detalle_ausencias')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </x-campo-condicional>
    </div>
</div>
<div class="form-group">
    <label for="salud_intento_suicidio" class="form-label">¿Ha intentado suicidarse alguna vez? ¿Por qué motivo? <span class="required">*</span></label>
    <textarea class="form-control" id="salud_intento_suicidio" name="salud_intento_suicidio" rows="2" required>{{ old('salud_intento_suicidio', $respAnt['salud_intento_suicidio'] ?? '') }}</textarea>
</div>
<hr class="my-3">
<h5 class="mb-3">{{ SaludHabitosCampos::TITULO_HABITOS }}</h5>
<div class="form-group">
    <label for="habito_tiempo_libre" class="form-label">¿Qué hace en sus tiempos libres? <span class="required">*</span></label>
    <input type="text" class="form-control" id="habito_tiempo_libre" name="habito_tiempo_libre"
           value="{{ old('habito_tiempo_libre', $respAnt['habito_tiempo_libre'] ?? '') }}" required>
</div>
@foreach([
    'habito_bares_frecuencia' => '¿A cada cuánto visita bares o discotecas?',
    'habito_alcohol_ultimo' => '¿Cuándo fue la última vez que consumió bebidas alcohólicas? ¿Qué y cuánto consumió?',
    'habito_alcohol_mensual' => '¿Cuántas veces consume bebidas alcohólicas al mes?',
    'habito_alcohol_detenido' => '¿Cuándo fue la última vez que estuvo detenido por consumir bebidas alcohólicas?',
    'habito_alcohol_laboral' => 'En el último año, ¿cuántas veces se presentó a laborar en estado de ebriedad o resaca?',
    'habito_alcohol_despido' => '¿En qué empleo fue despedido por excederse en el consumo de alcohol?',
    'habito_tabaco' => '¿Con qué frecuencia fuma?',
    'habito_juegos_azar' => '¿Qué juegos de azar practica? ¿Con qué frecuencia?',
] as $campo => $etiqueta)
<div class="form-group">
    <label for="{{ $campo }}" class="form-label">{{ $etiqueta }} <span class="required">*</span></label>
    <input type="text" class="form-control" id="{{ $campo }}" name="{{ $campo }}"
           value="{{ old($campo, $respAnt[$campo] ?? '') }}" required>
</div>
@endforeach
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="tiene_tatuajes" class="form-label">¿Posee tatuajes? <span class="required">*</span></label>
            <select class="form-control" id="tiene_tatuajes" name="tiene_tatuajes" required>
                <option value="no" {{ old('tiene_tatuajes', $respAnt['tiene_tatuajes'] ?? '') === 'no' ? 'selected' : '' }}>No</option>
                <option value="si" {{ old('tiene_tatuajes', $respAnt['tiene_tatuajes'] ?? '') === 'si' ? 'selected' : '' }}>Sí</option>
            </select>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="tiene_perforaciones" class="form-label">¿Posee aretes, perforaciones o piercings? <span class="required">*</span></label>
            <select class="form-control" id="tiene_perforaciones" name="tiene_perforaciones" required>
                <option value="no" {{ old('tiene_perforaciones', $respAnt['tiene_perforaciones'] ?? '') === 'no' ? 'selected' : '' }}>No</option>
                <option value="si" {{ old('tiene_perforaciones', $respAnt['tiene_perforaciones'] ?? '') === 'si' ? 'selected' : '' }}>Sí</option>
            </select>
        </div>
    </div>
</div>
<x-campo-condicional trigger="tiene_tatuajes" show-when="si">
    <x-tabla-dinamica name="tatuajes" titulo="Tatuajes" :columnas="\App\Support\TablaDinamica::columnasTatuajes()" :filas="$tablasExistentes['tatuajes'] ?? []" :minFilas="1" textoAgregar="Agregar tatuaje" textoEliminar="Quitar" />
</x-campo-condicional>
<x-campo-condicional trigger="tiene_perforaciones" show-when="si">
    <x-tabla-dinamica name="perforaciones" titulo="Perforaciones" :columnas="\App\Support\TablaDinamica::columnasPerforaciones()" :filas="$tablasExistentes['perforaciones'] ?? []" :minFilas="1" textoAgregar="Agregar" textoEliminar="Quitar" />
</x-campo-condicional>
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
@foreach([
    'sustancia_experiencia' => '¿Cómo ha sido su experiencia?',
    'sustancia_ultima_vez' => '¿Cuándo fue la última vez que experimentó?',
    'sustancia_ultimos_6_meses' => '¿En los últimos 6 meses cuántas veces consumió?',
    'sustancia_familiar_consume' => '¿Tiene algún amigo o familiar que las consuma?',
    'sustancia_consumo_frente' => '¿Cuándo fue la última vez que consumieron frente a usted?',
    'sustancia_guardo_transporto' => '¿Cuándo fue la última vez que guardó, transportó o vendió alguna droga ilegal?',
    'sustancia_mejora_animo' => '¿Alguna de ellas le ayuda a mejorar su salud o estado de ánimo? ¿Cuál?',
] as $campo => $etiqueta)
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
