{{-- Vivienda y gastos del hogar — sección económica (revisión cliente ago 2026) --}}
@php
    $respViv = $respuestas ?? ($respuestasExistentes ?? []);
@endphp

<div class="form-group">
    <label for="tipo_vivienda" class="form-label">
        Tipo de vivienda <span class="required">*</span>
    </label>
    <select class="form-control @error('tipo_vivienda') is-invalid @enderror"
            id="tipo_vivienda"
            name="tipo_vivienda"
            required>
        <option value="">Seleccione...</option>
        <option value="propia_pagada" {{ old('tipo_vivienda', $respViv['tipo_vivienda'] ?? '') == 'propia_pagada' ? 'selected' : '' }}>Propia (totalmente pagada)</option>
        <option value="propia_pagando" {{ old('tipo_vivienda', $respViv['tipo_vivienda'] ?? '') == 'propia_pagando' ? 'selected' : '' }}>Propia (pagando hipoteca)</option>
        <option value="alquilada" {{ old('tipo_vivienda', $respViv['tipo_vivienda'] ?? '') == 'alquilada' ? 'selected' : '' }}>Alquilada</option>
        <option value="prestada" {{ old('tipo_vivienda', $respViv['tipo_vivienda'] ?? '') == 'prestada' ? 'selected' : '' }}>Prestada</option>
        <option value="familiar" {{ old('tipo_vivienda', $respViv['tipo_vivienda'] ?? '') == 'familiar' ? 'selected' : '' }}>Casa familiar</option>
        <option value="otro" {{ old('tipo_vivienda', $respViv['tipo_vivienda'] ?? '') == 'otro' ? 'selected' : '' }}>Otro</option>
    </select>
    @error('tipo_vivienda')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<x-campo-condicional trigger="tipo_vivienda" show-when="propia_pagando" id="seccion_vivienda_pagando">
    <div class="row">
        <div class="col-lg-6">
            <div class="form-group">
                <label for="monto_hipoteca" class="form-label">Monto mensual de hipoteca (Q.)</label>
                <input type="number"
                       class="form-control @error('monto_hipoteca') is-invalid @enderror"
                       id="monto_hipoteca"
                       name="monto_hipoteca"
                       value="{{ old('monto_hipoteca', $respViv['monto_hipoteca'] ?? '') }}"
                       min="0"
                       step="0.01">
                @error('monto_hipoteca')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="col-lg-6">
            <div class="form-group">
                <label for="anos_restantes_hipoteca" class="form-label">Años restantes de hipoteca</label>
                <input type="number"
                       class="form-control @error('anos_restantes_hipoteca') is-invalid @enderror"
                       id="anos_restantes_hipoteca"
                       name="anos_restantes_hipoteca"
                       value="{{ old('anos_restantes_hipoteca', $respViv['anos_restantes_hipoteca'] ?? '') }}"
                       min="0"
                       max="50">
                @error('anos_restantes_hipoteca')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>
</x-campo-condicional>

<x-campo-condicional trigger="tipo_vivienda" show-when="alquilada" id="seccion_alquiler">
    <div class="form-group">
        <label for="monto_alquiler" class="form-label">Monto mensual de alquiler (Q.)</label>
        <input type="number"
               class="form-control @error('monto_alquiler') is-invalid @enderror"
               id="monto_alquiler"
               name="monto_alquiler"
               value="{{ old('monto_alquiler', $respViv['monto_alquiler'] ?? '') }}"
               min="0"
               step="0.01">
        @error('monto_alquiler')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</x-campo-condicional>

<div class="form-group">
    <label for="personas_contribuyen_gastos" class="form-label">
        ¿Cuántas personas contribuyen a los gastos del hogar? <span class="required">*</span>
    </label>
    <input type="number"
           class="form-control @error('personas_contribuyen_gastos') is-invalid @enderror"
           id="personas_contribuyen_gastos"
           name="personas_contribuyen_gastos"
           value="{{ old('personas_contribuyen_gastos', $respViv['personas_contribuyen_gastos'] ?? '') }}"
           min="1"
           max="20"
           required>
    <div class="form-text">Incluyéndose a usted mismo si contribuye</div>
    @error('personas_contribuyen_gastos')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
