{{-- Sección 3: Historial Laboral --}}

<div class="alert alert-info">
    <i class="fas fa-info-circle"></i>
    <strong>Información sobre su experiencia laboral y situación actual</strong>
</div>

@php
    use App\Support\HistorialAcademico;
    use App\Support\HistorialLaboralIntegridad;
    $resp = $respuestasExistentes ?? [];
    $ultimoNivel = old('ultimo_nivel_academico', $resp['ultimo_nivel_academico'] ?? 'ninguno');
    $filasAcademicas = HistorialAcademico::filasParaFormulario($ultimoNivel, $tablasExistentes['formacion_academica'] ?? []);
@endphp

<h5 class="mt-2 mb-3">Formación académica</h5>
<div class="form-group">
    <label for="ultimo_nivel_academico" class="form-label">Último nivel académico alcanzado <span class="required">*</span></label>
    <select class="form-control @error('ultimo_nivel_academico') is-invalid @enderror" id="ultimo_nivel_academico" name="ultimo_nivel_academico" required>
        <option value="ninguno" {{ $ultimoNivel === 'ninguno' ? 'selected' : '' }}>Ninguno</option>
        @foreach(HistorialAcademico::NIVELES as $k => $et)
            <option value="{{ $k }}" {{ $ultimoNivel === $k ? 'selected' : '' }}>{{ $et }}</option>
        @endforeach
    </select>
    @error('ultimo_nivel_academico')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<x-campo-condicional trigger="ultimo_nivel_academico" hide-when="ninguno" id="seccion_formacion_academica">
    <p class="text-muted small mb-2">
        {{ HistorialAcademico::textoAyudaFilas() }}
    </p>
    <x-tabla-dinamica
        name="formacion_academica"
        titulo="Detalle por nivel académico"
        :columnas="\App\Support\TablaDinamica::columnasFormacionAcademica()"
        :filas="$filasAcademicas"
        :minFilas="max(1, count($filasAcademicas))"
        :permitirAgregar="false"
        :permitirEliminar="false"
    />
</x-campo-condicional>

@include('cuestionario.secciones.partials.estudia-actualmente', [
    'respuestasExistentes' => $resp,
    'tablasExistentes' => $tablasExistentes ?? [],
])

@push('scripts')
<script>
        window.formacionAcademicaNiveles = @json(HistorialAcademico::NIVELES);
        window.formacionAcademicaVisibles = @json(HistorialAcademico::mapaNivelesVisibles());
</script>
<script src="{{ \App\Support\PublicAsset::url('js/formacion-academica.js') }}"></script>
@endpush

<hr class="my-4">
<h5 class="mb-3">Historial de empleos</h5>
<p class="text-muted small mb-2">
    EMPLEOS: (colocar todos los empleos, aunque hayan sido periodos cortos, temporales, informales o aunque no tenga constancia laboral)
</p>

<div class="form-group">
    <label for="experiencia_previa" class="form-label">{{ HistorialLaboralIntegridad::LABEL_EXPERIENCIA_PREVIA }} <span class="required">*</span></label>
    <select class="form-control @error('experiencia_previa') is-invalid @enderror" id="experiencia_previa" name="experiencia_previa" required>
        <option value="">Seleccione...</option>
        <option value="si" {{ old('experiencia_previa', $resp['experiencia_previa'] ?? '') === 'si' ? 'selected' : '' }}>Sí</option>
        <option value="no" {{ old('experiencia_previa', $resp['experiencia_previa'] ?? '') === 'no' ? 'selected' : '' }}>No</option>
    </select>
    @error('experiencia_previa')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<x-campo-condicional trigger="experiencia_previa" show-when="si" id="seccion_empleos">
    <x-tabla-dinamica
        name="empleos"
        titulo="Historial de empleos"
        :columnas="\App\Support\TablaDinamica::columnasEmpleosPreempleo()"
        :filas="$tablasExistentes['empleos'] ?? []"
        :minFilas="1"
        textoAgregar="Agregar empleo"
        textoEliminar="Quitar empleo"
    />
</x-campo-condicional>

<div class="form-group">
    <label for="observaciones_laborales" class="form-label">{{ HistorialLaboralIntegridad::LABEL_OBSERVACIONES_LABORALES }}</label>
    <textarea class="form-control" id="observaciones_laborales" name="observaciones_laborales" rows="3">{{ old('observaciones_laborales', $resp['observaciones_laborales'] ?? '') }}</textarea>
</div>

@include('cuestionario.secciones.partials.preguntas-textarea', [
    'titulo' => HistorialLaboralIntegridad::TITULO_BLOQUE,
    'badge' => 'Confidencial',
    'preguntas' => HistorialLaboralIntegridad::PREGUNTAS,
    'respuestas' => $resp,
])
