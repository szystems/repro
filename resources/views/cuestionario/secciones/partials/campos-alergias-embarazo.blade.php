{{-- M-F2/F3: alergias + embarazo. $respAnt = respuestas de la sección. --}}
@php
    $respAnt = $respAnt ?? ($respuestasExistentes ?? []);
@endphp

<div class="form-group">
    <label for="salud_alergias" class="form-label">{{ \App\Support\SaludHabitosCampos::LABEL_ALERGIAS }} <span class="required">*</span></label>
    <select class="form-control @error('salud_alergias') is-invalid @enderror" id="salud_alergias" name="salud_alergias" required>
        <option value="">Seleccione...</option>
        <option value="no" {{ old('salud_alergias', $respAnt['salud_alergias'] ?? '') === 'no' ? 'selected' : '' }}>No</option>
        <option value="si" {{ old('salud_alergias', $respAnt['salud_alergias'] ?? '') === 'si' ? 'selected' : '' }}>Sí</option>
    </select>
    @error('salud_alergias')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<x-campo-condicional trigger="salud_alergias" show-when="si">
    <div class="form-group">
        <label for="salud_detalle_alergias" class="form-label">¿Cuáles? <span class="required">*</span></label>
        <textarea class="form-control @error('salud_detalle_alergias') is-invalid @enderror"
                  id="salud_detalle_alergias"
                  name="salud_detalle_alergias"
                  rows="2"
                  required>{{ old('salud_detalle_alergias', $respAnt['salud_detalle_alergias'] ?? '') }}</textarea>
        @error('salud_detalle_alergias')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</x-campo-condicional>

<div class="form-group">
    <label for="salud_embarazada" class="form-label">{{ \App\Support\SaludHabitosCampos::LABEL_EMBARAZADA }} <span class="required">*</span></label>
    <select class="form-control @error('salud_embarazada') is-invalid @enderror" id="salud_embarazada" name="salud_embarazada" required>
        <option value="">Seleccione...</option>
        <option value="no" {{ old('salud_embarazada', $respAnt['salud_embarazada'] ?? '') === 'no' ? 'selected' : '' }}>No</option>
        <option value="si" {{ old('salud_embarazada', $respAnt['salud_embarazada'] ?? '') === 'si' ? 'selected' : '' }}>Sí</option>
    </select>
    <small class="form-text text-muted">Si no aplica, seleccione No.</small>
    @error('salud_embarazada')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
