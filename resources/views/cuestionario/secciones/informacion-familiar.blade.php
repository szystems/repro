{{-- Sección 2: Información Familiar --}}

@php
    use App\Support\InformacionFamiliarPadres;
    $resp = $respuestasExistentes ?? [];
    $conviveSeleccionados = InformacionFamiliarPadres::normalizarConviveCon(old('convive_con', $resp['convive_con'] ?? null));
@endphp

<div class="alert alert-info">
    <i class="fas fa-info-circle"></i>
    <strong>Información sobre su núcleo familiar y estado socioeconómico</strong>
</div>

<h6 class="text-primary mb-3"><i class="fas fa-user-friends"></i> Padres</h6>

<div class="form-group mb-4">
    <label class="form-label d-block">¿Con quién vive actualmente?</label>
    <div class="row">
        @foreach(InformacionFamiliarPadres::CONVIVE_OPCIONES as $clave => $etiqueta)
            <div class="col-md-4 col-lg-2">
                <div class="form-check">
                    <input class="form-check-input"
                           type="checkbox"
                           name="convive_con[]"
                           id="convive_{{ $clave }}"
                           value="{{ $clave }}"
                           {{ in_array($clave, $conviveSeleccionados, true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="convive_{{ $clave }}">{{ $etiqueta }}</label>
                </div>
            </div>
        @endforeach
    </div>
    @error('convive_con')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
</div>

@include('cuestionario.secciones.partials.datos-progenitor', [
    'prefijo' => 'padre',
    'titulo' => 'Padre',
    'respuestas' => $resp,
])

@include('cuestionario.secciones.partials.datos-progenitor', [
    'prefijo' => 'madre',
    'titulo' => 'Madre',
    'respuestas' => $resp,
])

<hr class="my-4">

<hr class="my-4">

<h6 class="text-primary mb-3"><i class="fas fa-child"></i> Hijos</h6>

<div class="row">    <div class="col-lg-6">
        <div class="form-group">
            <label for="estado_civil_detalle" class="form-label">
                Estado Civil <span class="required">*</span>
            </label>
            <select class="form-control @error('estado_civil_detalle') is-invalid @enderror" 
                    id="estado_civil_detalle" 
                    name="estado_civil_detalle" 
                    required>
                <option value="">Seleccione...</option>
                <option value="soltero" {{ old('estado_civil_detalle', $respuestasExistentes['estado_civil_detalle'] ?? '') == 'soltero' ? 'selected' : '' }}>Soltero(a)</option>
                <option value="casado" {{ old('estado_civil_detalle', $respuestasExistentes['estado_civil_detalle'] ?? '') == 'casado' ? 'selected' : '' }}>Casado(a)</option>
                <option value="union_libre" {{ old('estado_civil_detalle', $respuestasExistentes['estado_civil_detalle'] ?? '') == 'union_libre' ? 'selected' : '' }}>Unión Libre</option>
                <option value="divorciado" {{ old('estado_civil_detalle', $respuestasExistentes['estado_civil_detalle'] ?? '') == 'divorciado' ? 'selected' : '' }}>Divorciado(a)</option>
                <option value="viudo" {{ old('estado_civil_detalle', $respuestasExistentes['estado_civil_detalle'] ?? '') == 'viudo' ? 'selected' : '' }}>Viudo(a)</option>
            </select>
            @error('estado_civil_detalle')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="form-group">
            <label for="tiene_hijos" class="form-label">
                ¿Tiene hijos? <span class="required">*</span>
            </label>
            <select class="form-control @error('tiene_hijos') is-invalid @enderror" 
                    id="tiene_hijos" 
                    name="tiene_hijos" 
                    required>
                <option value="">Seleccione...</option>
                <option value="si" {{ old('tiene_hijos', $respuestasExistentes['tiene_hijos'] ?? '') == 'si' ? 'selected' : '' }}>Sí</option>
                <option value="no" {{ old('tiene_hijos', $respuestasExistentes['tiene_hijos'] ?? '') == 'no' ? 'selected' : '' }}>No</option>
            </select>
            @error('tiene_hijos')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<x-campo-condicional trigger="tiene_hijos" show-when="si" id="seccion_hijos">
    <div class="row">
        <div class="col-lg-4">
            <div class="form-group">
                <label for="numero_hijos" class="form-label">
                    Número de hijos
                </label>
                <input type="number" 
                       class="form-control @error('numero_hijos') is-invalid @enderror" 
                       id="numero_hijos" 
                       name="numero_hijos" 
                       value="{{ old('numero_hijos', $respuestasExistentes['numero_hijos'] ?? '') }}"
                       min="0" 
                       max="20"
                       data-condicional-required-trigger="tiene_hijos"
                       data-condicional-required-when="si">
                @error('numero_hijos')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="form-group">
                <label for="hijos_menores" class="form-label">
                    Hijos menores de edad
                </label>
                <input type="number" 
                       class="form-control @error('hijos_menores') is-invalid @enderror" 
                       id="hijos_menores" 
                       name="hijos_menores" 
                       value="{{ old('hijos_menores', $respuestasExistentes['hijos_menores'] ?? '') }}"
                       min="0" 
                       max="20">
                @error('hijos_menores')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="form-group">
                <label for="hijos_dependientes" class="form-label">
                    Hijos que dependen económicamente
                </label>
                <input type="number" 
                       class="form-control @error('hijos_dependientes') is-invalid @enderror" 
                       id="hijos_dependientes" 
                       name="hijos_dependientes" 
                       value="{{ old('hijos_dependientes', $respuestasExistentes['hijos_dependientes'] ?? '') }}"
                       min="0" 
                       max="20">
                @error('hijos_dependientes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <x-tabla-dinamica
        name="hijos"
        titulo="Detalle de hijos"
        :columnas="\App\Support\TablaDinamica::columnasHijos()"
        :filas="$tablasExistentes['hijos'] ?? []"
        :minFilas="1"
        textoAgregar="Agregar hijo"
        textoEliminar="Quitar hijo"
    />

    <p class="text-muted small mb-0">
        Si indicó que tiene hijos, complete al menos una fila con nombre, edad y si vive con usted.
    </p>
</x-campo-condicional>

@php $tipoFormFam = $cuestionario->tipo_formulario ?? 'preempleo'; @endphp
@if(! in_array($tipoFormFam, ['periodica', 'especifica'], true))
<hr class="my-4">
<h6 class="text-primary mb-3"><i class="fas fa-users"></i> Hermanos</h6>

<div class="row">
    <div class="col-lg-6">
        <div class="form-group">
            <label for="tiene_hermanos" class="form-label">¿Tiene hermanos? <span class="required">*</span></label>
            <select class="form-control @error('tiene_hermanos') is-invalid @enderror"
                    id="tiene_hermanos" name="tiene_hermanos" required>
                <option value="">Seleccione...</option>
                <option value="si" {{ old('tiene_hermanos', $respuestasExistentes['tiene_hermanos'] ?? '') == 'si' ? 'selected' : '' }}>Sí</option>
                <option value="no" {{ old('tiene_hermanos', $respuestasExistentes['tiene_hermanos'] ?? '') == 'no' ? 'selected' : '' }}>No</option>
            </select>
            @error('tiene_hermanos')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
</div>

<x-campo-condicional trigger="tiene_hermanos" show-when="si" id="seccion_hermanos">
    <x-tabla-dinamica
        name="hermanos"
        titulo="Detalle de hermanos"
        :columnas="\App\Support\TablaDinamica::columnasHermanos()"
        :filas="$tablasExistentes['hermanos'] ?? []"
        :minFilas="1"
        textoAgregar="Agregar hermano"
        textoEliminar="Quitar hermano"
    />
</x-campo-condicional>
@endif

@include('cuestionario.secciones.partials.datos-pareja-actual', ['respuestas' => $resp])

@if(! in_array($tipoFormFam, ['periodica', 'especifica'], true))
    @include('cuestionario.secciones.partials.datos-expareja', ['respuestas' => $resp])
@endif

<div class="form-group">
    <label for="observaciones_familiares" class="form-label">
        Observaciones o información adicional sobre su situación familiar
    </label>
    <textarea class="form-control @error('observaciones_familiares') is-invalid @enderror" 
              id="observaciones_familiares" 
              name="observaciones_familiares" 
              rows="4"
              placeholder="Puede incluir información relevante sobre su núcleo familiar...">{{ old('observaciones_familiares', $respuestasExistentes['observaciones_familiares'] ?? '') }}</textarea>
    @error('observaciones_familiares')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const seccionHijos = document.getElementById('seccion_hijos');
    const numeroHijos = document.getElementById('numero_hijos');
    const hijosMenores = document.getElementById('hijos_menores');
    const hijosDependientes = document.getElementById('hijos_dependientes');

    function asegurarFilaHijosInicial() {
        const tablaWrapper = seccionHijos?.querySelector('[data-tabla-dinamica]');
        if (!tablaWrapper) return;

        const filas = tablaWrapper.querySelectorAll('.tabla-dinamica-body .tabla-dinamica-row');
        if (filas.length === 0) {
            tablaWrapper.querySelector('.tabla-dinamica-add')?.click();
        }
    }

    seccionHijos?.addEventListener('condicional:shown', asegurarFilaHijosInicial);

    function validarHijosMenores() {
        const total = parseInt(numeroHijos.value, 10) || 0;
        const menores = parseInt(hijosMenores.value, 10) || 0;

        if (menores > total) {
            hijosMenores.value = total;
        }
    }

    function validarHijosDependientes() {
        const total = parseInt(numeroHijos.value, 10) || 0;
        const dependientes = parseInt(hijosDependientes.value, 10) || 0;

        if (dependientes > total) {
            hijosDependientes.value = total;
        }
    }

    numeroHijos?.addEventListener('change', function() {
        validarHijosMenores();
        validarHijosDependientes();
    });
    hijosMenores?.addEventListener('change', validarHijosMenores);
    hijosDependientes?.addEventListener('change', validarHijosDependientes);

    if (seccionHijos && !seccionHijos.classList.contains('d-none')) {
        asegurarFilaHijosInicial();
    }
});
</script>
@endpush
