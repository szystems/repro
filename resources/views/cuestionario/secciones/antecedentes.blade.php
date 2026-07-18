{{-- Sección 5: Antecedentes y Referencias --}}

<div class="alert alert-info">
    <i class="fas fa-info-circle"></i>
    <strong>Referencias personales, laborales y antecedentes relevantes</strong>
</div>

<h5 class="mb-3">Referencias Personales</h5>

<div class="row">
    <div class="col-lg-6">
        <div class="form-group">
            <label for="referencia1_nombre" class="form-label">
                Referencia Personal #1 - Nombre Completo <span class="required">*</span>
            </label>
            <input type="text" 
                   class="form-control @error('referencia1_nombre') is-invalid @enderror" 
                   id="referencia1_nombre" 
                   name="referencia1_nombre" 
                   value="{{ old('referencia1_nombre', $respuestasExistentes['referencia1_nombre'] ?? '') }}"
                   required
                   maxlength="100">
            @error('referencia1_nombre')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="form-group">
            <label for="referencia1_telefono" class="form-label">
                Teléfono de Referencia #1 <span class="required">*</span>
            </label>
            <input type="tel" 
                   class="form-control @error('referencia1_telefono') is-invalid @enderror" 
                   id="referencia1_telefono" 
                   name="referencia1_telefono" 
                   value="{{ old('referencia1_telefono', $respuestasExistentes['referencia1_telefono'] ?? '') }}"
                   required
                   maxlength="15">
            @error('referencia1_telefono')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="form-group">
    <label for="referencia1_relacion" class="form-label">
        Relación con Referencia #1 <span class="required">*</span>
    </label>
    <input type="text" 
           class="form-control @error('referencia1_relacion') is-invalid @enderror" 
           id="referencia1_relacion" 
           name="referencia1_relacion" 
           value="{{ old('referencia1_relacion', $respuestasExistentes['referencia1_relacion'] ?? '') }}"
           required
           maxlength="50"
           placeholder="Ej: Amigo, Vecino, Conocido, etc.">
    @error('referencia1_relacion')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="form-group">
            <label for="referencia2_nombre" class="form-label">
                Referencia Personal #2 - Nombre Completo <span class="required">*</span>
            </label>
            <input type="text" 
                   class="form-control @error('referencia2_nombre') is-invalid @enderror" 
                   id="referencia2_nombre" 
                   name="referencia2_nombre" 
                   value="{{ old('referencia2_nombre', $respuestasExistentes['referencia2_nombre'] ?? '') }}"
                   required
                   maxlength="100">
            @error('referencia2_nombre')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="form-group">
            <label for="referencia2_telefono" class="form-label">
                Teléfono de Referencia #2 <span class="required">*</span>
            </label>
            <input type="tel" 
                   class="form-control @error('referencia2_telefono') is-invalid @enderror" 
                   id="referencia2_telefono" 
                   name="referencia2_telefono" 
                   value="{{ old('referencia2_telefono', $respuestasExistentes['referencia2_telefono'] ?? '') }}"
                   required
                   maxlength="15">
            @error('referencia2_telefono')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="form-group">
    <label for="referencia2_relacion" class="form-label">
        Relación con Referencia #2 <span class="required">*</span>
    </label>
    <input type="text" 
           class="form-control @error('referencia2_relacion') is-invalid @enderror" 
           id="referencia2_relacion" 
           name="referencia2_relacion" 
           value="{{ old('referencia2_relacion', $respuestasExistentes['referencia2_relacion'] ?? '') }}"
           required
           maxlength="50"
           placeholder="Ej: Familiar, Compañero de estudios, etc.">
    @error('referencia2_relacion')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<h5 class="mt-4 mb-3">Antecedentes</h5>

<div class="form-group">
    <label for="antecedentes_penales" class="form-label">
        ¿Ha tenido problemas legales o antecedentes penales? <span class="required">*</span>
    </label>
    <select class="form-control @error('antecedentes_penales') is-invalid @enderror" 
            id="antecedentes_penales" 
            name="antecedentes_penales" 
            required>
        <option value="">Seleccione...</option>
        <option value="no" {{ old('antecedentes_penales', $respuestasExistentes['antecedentes_penales'] ?? '') == 'no' ? 'selected' : '' }}>No</option>
        <option value="si" {{ old('antecedentes_penales', $respuestasExistentes['antecedentes_penales'] ?? '') == 'si' ? 'selected' : '' }}>Sí</option>
    </select>
    @error('antecedentes_penales')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div id="seccion_antecedentes" class="d-none">
    <div class="form-group">
        <label for="detalle_antecedentes" class="form-label">
            Detalle de Antecedentes
        </label>
        <textarea class="form-control @error('detalle_antecedentes') is-invalid @enderror" 
                  id="detalle_antecedentes" 
                  name="detalle_antecedentes" 
                  rows="4"
                  placeholder="Describa brevemente la situación, fechas aproximadas y resolución...">{{ old('detalle_antecedentes', $respuestasExistentes['detalle_antecedentes'] ?? '') }}</textarea>
        @error('detalle_antecedentes')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="form-group">
    <label for="despedido_trabajo" class="form-label">
        ¿Ha sido despedido de algún trabajo? <span class="required">*</span>
    </label>
    <select class="form-control @error('despedido_trabajo') is-invalid @enderror" 
            id="despedido_trabajo" 
            name="despedido_trabajo" 
            required>
        <option value="">Seleccione...</option>
        <option value="no" {{ old('despedido_trabajo', $respuestasExistentes['despedido_trabajo'] ?? '') == 'no' ? 'selected' : '' }}>No</option>
        <option value="si" {{ old('despedido_trabajo', $respuestasExistentes['despedido_trabajo'] ?? '') == 'si' ? 'selected' : '' }}>Sí</option>
    </select>
    @error('despedido_trabajo')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div id="seccion_despido" class="d-none">
    <div class="form-group">
        <label for="motivo_despido" class="form-label">
            Motivo del Despido
        </label>
        <textarea class="form-control @error('motivo_despido') is-invalid @enderror" 
                  id="motivo_despido" 
                  name="motivo_despido" 
                  rows="3"
                  placeholder="Explique las circunstancias del despido...">{{ old('motivo_despido', $respuestasExistentes['motivo_despido'] ?? '') }}</textarea>
        @error('motivo_despido')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="form-group">
    <label for="consume_alcohol" class="form-label">
        ¿Consume bebidas alcohólicas? <span class="required">*</span>
    </label>
    <select class="form-control @error('consume_alcohol') is-invalid @enderror" 
            id="consume_alcohol" 
            name="consume_alcohol" 
            required>
        <option value="">Seleccione...</option>
        <option value="no" {{ old('consume_alcohol', $respuestasExistentes['consume_alcohol'] ?? '') == 'no' ? 'selected' : '' }}>No</option>
        <option value="ocasionalmente" {{ old('consume_alcohol', $respuestasExistentes['consume_alcohol'] ?? '') == 'ocasionalmente' ? 'selected' : '' }}>Ocasionalmente</option>
        <option value="socialmente" {{ old('consume_alcohol', $respuestasExistentes['consume_alcohol'] ?? '') == 'socialmente' ? 'selected' : '' }}>Socialmente</option>
        <option value="frecuentemente" {{ old('consume_alcohol', $respuestasExistentes['consume_alcohol'] ?? '') == 'frecuentemente' ? 'selected' : '' }}>Frecuentemente</option>
    </select>
    @error('consume_alcohol')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label for="consume_drogas" class="form-label">
        ¿Ha consumido sustancias controladas o drogas? <span class="required">*</span>
    </label>
    <select class="form-control @error('consume_drogas') is-invalid @enderror" 
            id="consume_drogas" 
            name="consume_drogas" 
            required>
        <option value="">Seleccione...</option>
        <option value="nunca" {{ old('consume_drogas', $respuestasExistentes['consume_drogas'] ?? '') == 'nunca' ? 'selected' : '' }}>Nunca</option>
        <option value="pasado" {{ old('consume_drogas', $respuestasExistentes['consume_drogas'] ?? '') == 'pasado' ? 'selected' : '' }}>En el pasado</option>
        <option value="ocasionalmente" {{ old('consume_drogas', $respuestasExistentes['consume_drogas'] ?? '') == 'ocasionalmente' ? 'selected' : '' }}>Ocasionalmente</option>
        <option value="frecuentemente" {{ old('consume_drogas', $respuestasExistentes['consume_drogas'] ?? '') == 'frecuentemente' ? 'selected' : '' }}>Frecuentemente</option>
    </select>
    @error('consume_drogas')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label for="problemas_salud_mental" class="form-label">
        ¿Ha recibido tratamiento psicológico o psiquiátrico? <span class="required">*</span>
    </label>
    <select class="form-control @error('problemas_salud_mental') is-invalid @enderror" 
            id="problemas_salud_mental" 
            name="problemas_salud_mental" 
            required>
        <option value="">Seleccione...</option>
        <option value="no" {{ old('problemas_salud_mental', $respuestasExistentes['problemas_salud_mental'] ?? '') == 'no' ? 'selected' : '' }}>No</option>
        <option value="si" {{ old('problemas_salud_mental', $respuestasExistentes['problemas_salud_mental'] ?? '') == 'si' ? 'selected' : '' }}>Sí</option>
    </select>
    @error('problemas_salud_mental')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div id="seccion_salud_mental" class="d-none">
    <div class="form-group">
        <label for="detalle_salud_mental" class="form-label">
            Detalle del Tratamiento
        </label>
        <textarea class="form-control @error('detalle_salud_mental') is-invalid @enderror" 
                  id="detalle_salud_mental" 
                  name="detalle_salud_mental" 
                  rows="3"
                  placeholder="Tipo de tratamiento, duración aproximada, motivo...">{{ old('detalle_salud_mental', $respuestasExistentes['detalle_salud_mental'] ?? '') }}</textarea>
        @error('detalle_salud_mental')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="form-group">
    <label for="observaciones_adicionales" class="form-label">
        Información adicional que considere relevante
    </label>
    <textarea class="form-control @error('observaciones_adicionales') is-invalid @enderror" 
              id="observaciones_adicionales" 
              name="observaciones_adicionales" 
              rows="4"
              placeholder="Cualquier información adicional que considere importante mencionar...">{{ old('observaciones_adicionales', $respuestasExistentes['observaciones_adicionales'] ?? '') }}</textarea>
    @error('observaciones_adicionales')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
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
<h5 class="mb-3">Salud y hábitos</h5>
<p class="text-muted small"><span class="badge bg-secondary">Confidencial</span> Debe responder usted. Uso interno de REPRO — no se incluye automáticamente en el informe a la empresa.</p>
<div class="form-group">
    <label for="salud_preocupaciones" class="form-label">Preocupaciones de salud <span class="required">*</span></label>
    <textarea class="form-control" id="salud_preocupaciones" name="salud_preocupaciones" rows="2" required>{{ old('salud_preocupaciones', $respAnt['salud_preocupaciones'] ?? '') }}</textarea>
</div>
<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label for="salud_estado_general" class="form-label">Estado general <span class="required">*</span></label>
            <select class="form-control" id="salud_estado_general" name="salud_estado_general" required>
                @foreach(['excelente','bueno','regular','malo'] as $op)
                    <option value="{{ $op }}" {{ old('salud_estado_general', $respAnt['salud_estado_general'] ?? '') === $op ? 'selected' : '' }}>{{ ucfirst($op) }}</option>
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
            <label for="salud_peso" class="form-label">Peso (kg) <span class="required">*</span></label>
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
            <label for="salud_atencion_psicologica" class="form-label">¿Atención psicológica? <span class="required">*</span></label>
            <select class="form-control" id="salud_atencion_psicologica" name="salud_atencion_psicologica" required>
                <option value="no" {{ old('salud_atencion_psicologica', $respAnt['salud_atencion_psicologica'] ?? '') === 'no' ? 'selected' : '' }}>No</option>
                <option value="si" {{ old('salud_atencion_psicologica', $respAnt['salud_atencion_psicologica'] ?? '') === 'si' ? 'selected' : '' }}>Sí</option>
            </select>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="salud_situacion_emocional" class="form-label">Situación emocional <span class="required">*</span></label>
            <input type="text" class="form-control" id="salud_situacion_emocional" name="salud_situacion_emocional"
                   value="{{ old('salud_situacion_emocional', $respAnt['salud_situacion_emocional'] ?? '') }}" required>
        </div>
    </div>
</div>
<x-campo-condicional trigger="salud_atencion_psicologica" show-when="si">
    <div class="form-group">
        <label for="salud_detalle_psicologica" class="form-label">Detalle de atención psicológica <span class="required">*</span></label>
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
            <label for="salud_ideacion_dano" class="form-label">¿Ha tenido pensamientos de hacerse daño o dañar a otros? <span class="required">*</span></label>
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
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="habito_tiempo_libre" class="form-label">Actividades en tiempo libre <span class="required">*</span></label>
            <input type="text" class="form-control" id="habito_tiempo_libre" name="habito_tiempo_libre"
                   value="{{ old('habito_tiempo_libre', $respAnt['habito_tiempo_libre'] ?? '') }}" required>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="habito_alcohol_frecuencia" class="form-label">Frecuencia de consumo de alcohol <span class="required">*</span></label>
            <select class="form-control @error('habito_alcohol_frecuencia') is-invalid @enderror" id="habito_alcohol_frecuencia" name="habito_alcohol_frecuencia" required>
                @foreach(['nunca' => 'Nunca', 'ocasional' => 'Ocasional', 'regular' => 'Regular', 'frecuente' => 'Frecuente'] as $op => $et)
                    <option value="{{ $op }}" {{ old('habito_alcohol_frecuencia', $respAnt['habito_alcohol_frecuencia'] ?? '') === $op ? 'selected' : '' }}>{{ $et }}</option>
                @endforeach
            </select>
            @error('habito_alcohol_frecuencia')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label for="habito_alcohol_excesos" class="form-label">¿Excesos de alcohol? <span class="required">*</span></label>
            <select class="form-control" id="habito_alcohol_excesos" name="habito_alcohol_excesos" required>
                <option value="no" {{ old('habito_alcohol_excesos', $respAnt['habito_alcohol_excesos'] ?? '') === 'no' ? 'selected' : '' }}>No</option>
                <option value="si" {{ old('habito_alcohol_excesos', $respAnt['habito_alcohol_excesos'] ?? '') === 'si' ? 'selected' : '' }}>Sí</option>
            </select>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="habito_alcohol_laboral" class="form-label">¿Alcohol en horario laboral? <span class="required">*</span></label>
            <select class="form-control" id="habito_alcohol_laboral" name="habito_alcohol_laboral" required>
                <option value="no" {{ old('habito_alcohol_laboral', $respAnt['habito_alcohol_laboral'] ?? '') === 'no' ? 'selected' : '' }}>No</option>
                <option value="si" {{ old('habito_alcohol_laboral', $respAnt['habito_alcohol_laboral'] ?? '') === 'si' ? 'selected' : '' }}>Sí</option>
            </select>
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group">
            <label for="habito_tabaco" class="form-label">¿Tabaco? <span class="required">*</span></label>
            <select class="form-control" id="habito_tabaco" name="habito_tabaco" required>
                <option value="no" {{ old('habito_tabaco', $respAnt['habito_tabaco'] ?? '') === 'no' ? 'selected' : '' }}>No</option>
                <option value="si" {{ old('habito_tabaco', $respAnt['habito_tabaco'] ?? '') === 'si' ? 'selected' : '' }}>Sí</option>
            </select>
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group">
            <label for="habito_juegos_azar" class="form-label">¿Juegos de azar? <span class="required">*</span></label>
            <select class="form-control" id="habito_juegos_azar" name="habito_juegos_azar" required>
                <option value="no" {{ old('habito_juegos_azar', $respAnt['habito_juegos_azar'] ?? '') === 'no' ? 'selected' : '' }}>No</option>
                <option value="si" {{ old('habito_juegos_azar', $respAnt['habito_juegos_azar'] ?? '') === 'si' ? 'selected' : '' }}>Sí</option>
            </select>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="tiene_tatuajes" class="form-label">¿Tiene tatuajes? <span class="required">*</span></label>
            <select class="form-control" id="tiene_tatuajes" name="tiene_tatuajes" required>
                <option value="no" {{ old('tiene_tatuajes', $respAnt['tiene_tatuajes'] ?? '') === 'no' ? 'selected' : '' }}>No</option>
                <option value="si" {{ old('tiene_tatuajes', $respAnt['tiene_tatuajes'] ?? '') === 'si' ? 'selected' : '' }}>Sí</option>
            </select>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="tiene_perforaciones" class="form-label">¿Tiene perforaciones? <span class="required">*</span></label>
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
<div class="form-group">
    <label class="form-label d-block">Sustancias de uso recreativo</label>
    @foreach(SaludHabitosCampos::SUSTANCIAS as $k => $et)
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="sustancias_usadas[]" id="sust_{{ $k }}" value="{{ $k }}"
                   {{ in_array($k, $sustanciasSel, true) ? 'checked' : '' }}>
            <label class="form-check-label" for="sust_{{ $k }}">{{ $et }}</label>
        </div>
    @endforeach
</div>

@include('cuestionario.secciones.partials.preguntas-textarea', [
    'titulo' => 'Aspecto judicial',
    'badge' => 'Confidencial',
    'preguntas' => AntecedentesJudiciales::PREGUNTAS,
    'respuestas' => $respAnt,
])

@include('cuestionario.secciones.partials.preguntas-textarea', [
    'titulo' => 'Información complementaria (informe)',
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const antecedentesPenales = document.getElementById('antecedentes_penales');
    const seccionAntecedentes = document.getElementById('seccion_antecedentes');
    const despedidoTrabajo = document.getElementById('despedido_trabajo');
    const seccionDespido = document.getElementById('seccion_despido');
    const saludMental = document.getElementById('problemas_salud_mental');
    const seccionSaludMental = document.getElementById('seccion_salud_mental');
    
    // Mostrar/ocultar sección de antecedentes penales
    function toggleSeccionAntecedentes() {
        if (antecedentesPenales.value === 'si') {
            seccionAntecedentes.classList.remove('d-none');
        } else {
            seccionAntecedentes.classList.add('d-none');
            document.getElementById('detalle_antecedentes').value = '';
        }
    }
    
    // Mostrar/ocultar sección de despido
    function toggleSeccionDespido() {
        if (despedidoTrabajo.value === 'si') {
            seccionDespido.classList.remove('d-none');
        } else {
            seccionDespido.classList.add('d-none');
            document.getElementById('motivo_despido').value = '';
        }
    }
    
    // Mostrar/ocultar sección de salud mental
    function toggleSeccionSaludMental() {
        if (saludMental.value === 'si') {
            seccionSaludMental.classList.remove('d-none');
        } else {
            seccionSaludMental.classList.add('d-none');
            document.getElementById('detalle_salud_mental').value = '';
        }
    }
    
    // Event listeners
    antecedentesPenales.addEventListener('change', toggleSeccionAntecedentes);
    despedidoTrabajo.addEventListener('change', toggleSeccionDespido);
    saludMental.addEventListener('change', toggleSeccionSaludMental);
    
    // Formatear teléfonos
    const telefonos = document.querySelectorAll('input[type="tel"]');
    telefonos.forEach(function(telefono) {
        telefono.addEventListener('input', function() {
            // Permitir solo números, espacios, guiones, paréntesis y signo +
            this.value = this.value.replace(/[^0-9+\-\s()]/g, '');
        });
    });
    
    // Inicializar estado al cargar
    toggleSeccionAntecedentes();
    toggleSeccionDespido();
    toggleSeccionSaludMental();
});
</script>
@endpush