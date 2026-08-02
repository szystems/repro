{{-- Sección 4: Deudas y datos económicos (POLIGRAFO PRESENCIAL) --}}

@php
    use App\Support\SituacionEconomicaCampos;
    $respEco = $respuestasExistentes ?? [];
    $tipoForm = $cuestionario->tipo_formulario ?? 'preempleo';
    $esSocio = $tipoForm === 'socioeconomico';
@endphp

<div class="alert alert-info">
    <i class="fas fa-info-circle"></i>
    <strong>Deudas y datos económicos</strong>
    <div class="small mt-1 mb-0"><span class="badge bg-secondary">Confidencial</span> Uso interno de REPRO — no se incluye automáticamente en el informe a la empresa.</div>
</div>

<h5 class="mb-3">Deudas</h5>

<div class="form-group">
    <label for="tiene_deudas" class="form-label">¿Tiene deudas actuales? <span class="required">*</span></label>
    <select class="form-control @error('tiene_deudas') is-invalid @enderror" id="tiene_deudas" name="tiene_deudas" required>
        <option value="">Seleccione...</option>
        <option value="si" {{ old('tiene_deudas', $respEco['tiene_deudas'] ?? '') == 'si' ? 'selected' : '' }}>Sí</option>
        <option value="no" {{ old('tiene_deudas', $respEco['tiene_deudas'] ?? '') == 'no' ? 'selected' : '' }}>No</option>
    </select>
    @error('tiene_deudas')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<x-campo-condicional trigger="tiene_deudas" show-when="si">
    <x-tabla-dinamica
        name="deudas"
        titulo="Detalle de deudas"
        :columnas="\App\Support\TablaDinamica::columnasDeudas()"
        :filas="$tablasExistentes['deudas'] ?? []"
        :minFilas="1"
        textoAgregar="Agregar deuda"
        textoEliminar="Quitar deuda"
    />
    <div class="form-group mt-3">
        <label for="detalle_deudas" class="form-label">Observaciones sobre deudas</label>
        <textarea class="form-control @error('detalle_deudas') is-invalid @enderror"
                  id="detalle_deudas"
                  name="detalle_deudas"
                  rows="3">{{ old('detalle_deudas', $respEco['detalle_deudas'] ?? '') }}</textarea>
        @error('detalle_deudas')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</x-campo-condicional>

<hr class="my-4">
<h5 class="mb-3">Datos económicos</h5>

<div class="form-group">
    <label for="econ_es_fiador" class="form-label">{{ SituacionEconomicaCampos::LABEL_ES_FIADOR }} <span class="required">*</span></label>
    <select class="form-control @error('econ_es_fiador') is-invalid @enderror" id="econ_es_fiador" name="econ_es_fiador" required>
        <option value="no" {{ old('econ_es_fiador', $respEco['econ_es_fiador'] ?? '') === 'no' ? 'selected' : '' }}>No</option>
        <option value="si" {{ old('econ_es_fiador', $respEco['econ_es_fiador'] ?? '') === 'si' ? 'selected' : '' }}>Sí</option>
    </select>
    @error('econ_es_fiador')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<x-campo-condicional trigger="econ_es_fiador" show-when="si">
    <div class="form-group">
        <label for="econ_detalle_es_fiador" class="form-label">Amplíe la información <span class="required">*</span></label>
        <textarea class="form-control @error('econ_detalle_es_fiador') is-invalid @enderror"
                  id="econ_detalle_es_fiador"
                  name="econ_detalle_es_fiador"
                  rows="3"
                  required>{{ old('econ_detalle_es_fiador', $respEco['econ_detalle_es_fiador'] ?? '') }}</textarea>
        @error('econ_detalle_es_fiador')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</x-campo-condicional>

<div class="form-group">
    <label for="econ_problemas_bancarios" class="form-label">{{ SituacionEconomicaCampos::LABEL_PROBLEMAS_BANCARIOS }} <span class="required">*</span></label>
    <select class="form-control @error('econ_problemas_bancarios') is-invalid @enderror" id="econ_problemas_bancarios" name="econ_problemas_bancarios" required>
        <option value="no" {{ old('econ_problemas_bancarios', $respEco['econ_problemas_bancarios'] ?? '') === 'no' ? 'selected' : '' }}>No</option>
        <option value="si" {{ old('econ_problemas_bancarios', $respEco['econ_problemas_bancarios'] ?? '') === 'si' ? 'selected' : '' }}>Sí</option>
    </select>
    @error('econ_problemas_bancarios')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<x-campo-condicional trigger="econ_problemas_bancarios" show-when="si">
    <div class="form-group">
        <label for="econ_detalle_problemas_bancarios" class="form-label">Amplíe la información <span class="required">*</span></label>
        <textarea class="form-control @error('econ_detalle_problemas_bancarios') is-invalid @enderror"
                  id="econ_detalle_problemas_bancarios"
                  name="econ_detalle_problemas_bancarios"
                  rows="3"
                  required>{{ old('econ_detalle_problemas_bancarios', $respEco['econ_detalle_problemas_bancarios'] ?? '') }}</textarea>
        @error('econ_detalle_problemas_bancarios')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</x-campo-condicional>

<div class="form-group">
    <label for="econ_tipo_vivienda_detalle" class="form-label">{{ SituacionEconomicaCampos::LABEL_VIVIENDA }} <span class="required">*</span></label>
    <textarea class="form-control @error('econ_tipo_vivienda_detalle') is-invalid @enderror"
              id="econ_tipo_vivienda_detalle"
              name="econ_tipo_vivienda_detalle"
              rows="2"
              required
              placeholder="Ej: Propio / Alquilo Q2,500 mensuales">{{ old('econ_tipo_vivienda_detalle', $respEco['econ_tipo_vivienda_detalle'] ?? '') }}</textarea>
    @error('econ_tipo_vivienda_detalle')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="form-group">
    <label for="econ_dependientes_detalle" class="form-label">{{ SituacionEconomicaCampos::LABEL_DEPENDIENTES }} <span class="required">*</span></label>
    <textarea class="form-control @error('econ_dependientes_detalle') is-invalid @enderror"
              id="econ_dependientes_detalle"
              name="econ_dependientes_detalle"
              rows="2"
              required>{{ old('econ_dependientes_detalle', $respEco['econ_dependientes_detalle'] ?? '') }}</textarea>
    @error('econ_dependientes_detalle')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="form-group">
    <label for="econ_ingresos_adicionales_detalle" class="form-label">{{ SituacionEconomicaCampos::LABEL_INGRESOS_ADICIONALES }} <span class="required">*</span></label>
    <textarea class="form-control @error('econ_ingresos_adicionales_detalle') is-invalid @enderror"
              id="econ_ingresos_adicionales_detalle"
              name="econ_ingresos_adicionales_detalle"
              rows="2"
              required>{{ old('econ_ingresos_adicionales_detalle', $respEco['econ_ingresos_adicionales_detalle'] ?? '') }}</textarea>
    @error('econ_ingresos_adicionales_detalle')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="econ_posee_propiedades" class="form-label">{{ SituacionEconomicaCampos::LABEL_PROPIEDADES }} <span class="required">*</span></label>
            <select class="form-control @error('econ_posee_propiedades') is-invalid @enderror" id="econ_posee_propiedades" name="econ_posee_propiedades" required>
                <option value="no" {{ old('econ_posee_propiedades', $respEco['econ_posee_propiedades'] ?? '') === 'no' ? 'selected' : '' }}>No</option>
                <option value="si" {{ old('econ_posee_propiedades', $respEco['econ_posee_propiedades'] ?? '') === 'si' ? 'selected' : '' }}>Sí</option>
            </select>
            @error('econ_posee_propiedades')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <x-campo-condicional trigger="econ_posee_propiedades" show-when="si">
            <div class="form-group">
                <label for="econ_detalle_propiedades" class="form-label">Amplíe la información <span class="required">*</span></label>
                <textarea class="form-control @error('econ_detalle_propiedades') is-invalid @enderror"
                          id="econ_detalle_propiedades"
                          name="econ_detalle_propiedades"
                          rows="3"
                          required>{{ old('econ_detalle_propiedades', $respEco['econ_detalle_propiedades'] ?? '') }}</textarea>
                @error('econ_detalle_propiedades')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </x-campo-condicional>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="econ_posee_vehiculos" class="form-label">{{ SituacionEconomicaCampos::LABEL_VEHICULOS }} <span class="required">*</span></label>
            <select class="form-control @error('econ_posee_vehiculos') is-invalid @enderror" id="econ_posee_vehiculos" name="econ_posee_vehiculos" required>
                <option value="no" {{ old('econ_posee_vehiculos', $respEco['econ_posee_vehiculos'] ?? '') === 'no' ? 'selected' : '' }}>No</option>
                <option value="si" {{ old('econ_posee_vehiculos', $respEco['econ_posee_vehiculos'] ?? '') === 'si' ? 'selected' : '' }}>Sí</option>
            </select>
            @error('econ_posee_vehiculos')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <x-campo-condicional trigger="econ_posee_vehiculos" show-when="si">
            <div class="form-group">
                <label for="econ_detalle_vehiculos" class="form-label">Describa los vehículos <span class="required">*</span></label>
                <textarea class="form-control @error('econ_detalle_vehiculos') is-invalid @enderror"
                          id="econ_detalle_vehiculos"
                          name="econ_detalle_vehiculos"
                          rows="3"
                          required>{{ old('econ_detalle_vehiculos', $respEco['econ_detalle_vehiculos'] ?? '') }}</textarea>
                @error('econ_detalle_vehiculos')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </x-campo-condicional>
    </div>
</div>

<div class="form-group">
    <label for="econ_demandas_deudas" class="form-label">{{ SituacionEconomicaCampos::LABEL_DEMANDAS }} <span class="required">*</span></label>
    <select class="form-control @error('econ_demandas_deudas') is-invalid @enderror" id="econ_demandas_deudas" name="econ_demandas_deudas" required>
        <option value="no" {{ old('econ_demandas_deudas', $respEco['econ_demandas_deudas'] ?? '') === 'no' ? 'selected' : '' }}>No</option>
        <option value="si" {{ old('econ_demandas_deudas', $respEco['econ_demandas_deudas'] ?? '') === 'si' ? 'selected' : '' }}>Sí</option>
    </select>
    @error('econ_demandas_deudas')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<x-campo-condicional trigger="econ_demandas_deudas" show-when="si">
    <div class="form-group">
        <label for="econ_detalle_demandas" class="form-label">Amplíe la información <span class="required">*</span></label>
        <textarea class="form-control @error('econ_detalle_demandas') is-invalid @enderror"
                  id="econ_detalle_demandas"
                  name="econ_detalle_demandas"
                  rows="3"
                  required>{{ old('econ_detalle_demandas', $respEco['econ_detalle_demandas'] ?? '') }}</textarea>
        @error('econ_detalle_demandas')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</x-campo-condicional>

@if($esSocio)
<div class="form-group">
    <label for="econ_patrimonio_aprox" class="form-label">{{ SituacionEconomicaCampos::LABEL_PATRIMONIO_SOCIO }} <span class="required">*</span></label>
    <textarea class="form-control @error('econ_patrimonio_aprox') is-invalid @enderror"
              id="econ_patrimonio_aprox"
              name="econ_patrimonio_aprox"
              rows="2"
              required>{{ old('econ_patrimonio_aprox', $respEco['econ_patrimonio_aprox'] ?? '') }}</textarea>
    @error('econ_patrimonio_aprox')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
@endif

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="econ_pretension_salarial" class="form-label">{{ SituacionEconomicaCampos::LABEL_PRETENSION }} <span class="required">*</span></label>
            <input type="number" class="form-control @error('econ_pretension_salarial') is-invalid @enderror"
                   id="econ_pretension_salarial" name="econ_pretension_salarial"
                   value="{{ old('econ_pretension_salarial', $respEco['econ_pretension_salarial'] ?? '') }}" min="0" step="0.01" required>
            @error('econ_pretension_salarial')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="econ_gastos_mensuales_aprox" class="form-label">{{ SituacionEconomicaCampos::LABEL_GASTOS_MENSUALES }} <span class="required">*</span></label>
            <input type="number" class="form-control @error('econ_gastos_mensuales_aprox') is-invalid @enderror"
                   id="econ_gastos_mensuales_aprox" name="econ_gastos_mensuales_aprox"
                   value="{{ old('econ_gastos_mensuales_aprox', $respEco['econ_gastos_mensuales_aprox'] ?? '') }}" min="0" step="0.01" required>
            @error('econ_gastos_mensuales_aprox')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
</div>

<div class="form-group">
    <label for="econ_problemas_sat" class="form-label">{{ SituacionEconomicaCampos::LABEL_SAT }} <span class="required">*</span></label>
    <select class="form-control @error('econ_problemas_sat') is-invalid @enderror" id="econ_problemas_sat" name="econ_problemas_sat" required>
        <option value="no" {{ old('econ_problemas_sat', $respEco['econ_problemas_sat'] ?? '') === 'no' ? 'selected' : '' }}>No</option>
        <option value="si" {{ old('econ_problemas_sat', $respEco['econ_problemas_sat'] ?? '') === 'si' ? 'selected' : '' }}>Sí</option>
    </select>
    @error('econ_problemas_sat')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<x-campo-condicional trigger="econ_problemas_sat" show-when="si">
    <div class="form-group">
        <label for="econ_detalle_sat" class="form-label">Amplíe la información <span class="required">*</span></label>
        <textarea class="form-control @error('econ_detalle_sat') is-invalid @enderror"
                  id="econ_detalle_sat"
                  name="econ_detalle_sat"
                  rows="3"
                  required>{{ old('econ_detalle_sat', $respEco['econ_detalle_sat'] ?? '') }}</textarea>
        @error('econ_detalle_sat')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</x-campo-condicional>
