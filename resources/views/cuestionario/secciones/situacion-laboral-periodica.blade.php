{{-- E5 — Sección laboral Periódica / Específica (PDF CREACIÓN FORMULARIOS DE SISTEMA) --}}
@php
    use App\Support\HistorialAcademico;
    use App\Support\HistorialLaboralPeriodico;

    $resp = $respuestasExistentes ?? [];
    $tipoForm = $cuestionario->tipo_formulario ?? 'periodica';
    $esEspecifica = $tipoForm === 'especifica';
    $campoAdicional = HistorialLaboralPeriodico::CAMPO_INFORMACION_ADICIONAL;
    $ultimoNivel = old('ultimo_nivel_academico', $resp['ultimo_nivel_academico'] ?? 'ninguno');
    $filasAcademicas = HistorialAcademico::filasParaFormulario($ultimoNivel, $tablasExistentes['formacion_academica'] ?? []);
    $labelPregunta1 = HistorialLaboralPeriodico::labelPregunta1($esEspecifica);
@endphp

<div class="alert alert-info">
    <i class="fas fa-briefcase"></i>
    <strong>
        @if($esEspecifica)
            Información laboral y descripción del caso a evaluar
        @else
            Información laboral — evaluación periódica
        @endif
    </strong>
</div>

<hr class="my-3">
<h5 class="mb-3">Formación académica</h5>
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

@if($esEspecifica)
    <p class="text-muted small mb-3">
        En evaluación específica solo se solicita el último grado académico (sin historial completo por nivel).
    </p>
@else
    <x-campo-condicional trigger="ultimo_nivel_academico" hide-when="ninguno" id="seccion_formacion_academica">
        <p class="text-muted small mb-2">
            Complete una fila por cada nivel académico desde primaria hasta el último nivel que seleccionó arriba.
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

    @push('scripts')
    <script>
        window.formacionAcademicaNiveles = @json(HistorialAcademico::NIVELES);
    </script>
    <script src="{{ asset('js/formacion-academica.js') }}?v={{ filemtime(public_path('js/formacion-academica.js')) }}"></script>
    @endpush
@endif

<hr class="my-4">
<h5 class="mb-3">Empleo actual</h5>
<div class="form-group">
    <label for="tiene_empleo_actual" class="form-label">
        ¿Tiene empleo actual o ha trabajado alguna vez? <span class="required">*</span>
    </label>
    <select class="form-control @error('tiene_empleo_actual') is-invalid @enderror"
            id="tiene_empleo_actual" name="tiene_empleo_actual" required>
        <option value="">Seleccione...</option>
        <option value="si" {{ old('tiene_empleo_actual', $resp['tiene_empleo_actual'] ?? '') === 'si' ? 'selected' : '' }}>Sí</option>
        <option value="no" {{ old('tiene_empleo_actual', $resp['tiene_empleo_actual'] ?? '') === 'no' ? 'selected' : '' }}>No, nunca he trabajado</option>
    </select>
    @error('tiene_empleo_actual')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<x-campo-condicional trigger="tiene_empleo_actual" show-when="si" id="seccion_empleo_actual">
    <x-tabla-dinamica
        name="empleo_actual"
        titulo="Tabla de información laboral"
        :columnas="\App\Support\TablaDinamica::columnasEmpleoActualPeriodico()"
        :filas="$tablasExistentes['empleo_actual'] ?? []"
        :minFilas="1"
        :permitirAgregar="false"
        :permitirEliminar="false"
        textoAgregar="Agregar fila"
        textoEliminar="Quitar fila"
    />
</x-campo-condicional>

<div class="alert alert-light border small mt-2" id="aviso_sin_empleo" style="display: none;">
    Si nunca ha trabajado, en las preguntas siguientes puede responder <strong>N/A</strong> cuando no apliquen.
</div>

<h5 class="mt-4 mb-3">Preguntas complementarias de su empleo actual</h5>
<p class="text-muted small">
    <span class="badge bg-secondary">Confidencial</span>
    Debe responder con sinceridad. Esta información es confidencial para REPRO y no se incluye automáticamente en el informe entregado a la empresa.
</p>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const sel = document.getElementById('tiene_empleo_actual');
    const aviso = document.getElementById('aviso_sin_empleo');
    function syncAviso() {
        if (!sel || !aviso) return;
        aviso.style.display = sel.value === 'no' ? 'block' : 'none';
    }
    if (sel) {
        sel.addEventListener('change', syncAviso);
        syncAviso();
    }
});
</script>
@endpush

{{-- E5.5: pregunta 1 con espacio amplio en Específica --}}
<div class="form-group">
    <label for="periodico_01" class="form-label">
        1. {{ $labelPregunta1 }} <span class="required">*</span>
    </label>
    <textarea class="form-control @error('periodico_01') is-invalid @enderror"
              id="periodico_01"
              name="periodico_01"
              rows="{{ $esEspecifica ? 12 : 3 }}"
              @if($esEspecifica) style="min-height: 220px;" @endif
              required>{{ old('periodico_01', $resp['periodico_01'] ?? '') }}</textarea>
    @error('periodico_01')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

@include('cuestionario.secciones.partials.preguntas-textarea', [
    'titulo' => '',
    'badge' => null,
    'inicioNumero' => 2,
    'preguntas' => HistorialLaboralPeriodico::preguntasDesdeLaSegunda(),
    'respuestas' => $resp,
])

<div class="form-group mt-3">
    <label for="{{ $campoAdicional['key'] }}" class="form-label">{{ $campoAdicional['label'] }} <span class="required">*</span></label>
    <textarea class="form-control @error($campoAdicional['key']) is-invalid @enderror"
              id="{{ $campoAdicional['key'] }}"
              name="{{ $campoAdicional['key'] }}"
              rows="3"
              required>{{ old($campoAdicional['key'], $resp[$campoAdicional['key']] ?? '') }}</textarea>
    @error($campoAdicional['key'])<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
