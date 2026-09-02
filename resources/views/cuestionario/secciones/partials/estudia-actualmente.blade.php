{{-- ¿Estudia actualmente? + tabla condicional (revisión cliente ago 2026) --}}
@php
    $respEst = $respuestas ?? ($respuestasExistentes ?? []);
    $estudia = old('estudia_actualmente', $respEst['estudia_actualmente'] ?? '');
@endphp

<div class="form-group mt-3">
    <label for="estudia_actualmente" class="form-label">
        ¿Estudia actualmente? <span class="required">*</span>
    </label>
    <select class="form-control @error('estudia_actualmente') is-invalid @enderror"
            id="estudia_actualmente"
            name="estudia_actualmente"
            required>
        <option value="">Seleccione...</option>
        <option value="si" {{ $estudia === 'si' ? 'selected' : '' }}>Sí</option>
        <option value="no" {{ $estudia === 'no' ? 'selected' : '' }}>No</option>
    </select>
    @error('estudia_actualmente')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<x-campo-condicional trigger="estudia_actualmente" show-when="si" id="seccion_estudios_actuales">
    <x-tabla-dinamica
        name="estudios_actuales"
        titulo="Estudios actuales"
        :columnas="\App\Support\TablaDinamica::columnasEstudiosActuales()"
        :filas="$tablasExistentes['estudios_actuales'] ?? []"
        :minFilas="1"
        textoAgregar="Agregar estudio"
        textoEliminar="Quitar estudio"
    />
</x-campo-condicional>
