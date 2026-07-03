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

@include('cuestionario.secciones.partials.datos-pareja-actual', ['respuestas' => $resp])

@include('cuestionario.secciones.partials.datos-expareja', ['respuestas' => $resp])

<div class="row">
    <div class="col-lg-6">
        <div class="form-group">
            <label for="personas_hogar" class="form-label">
                Número de personas en el hogar <span class="required">*</span>
            </label>
            <input type="number" 
                   class="form-control @error('personas_hogar') is-invalid @enderror" 
                   id="personas_hogar" 
                   name="personas_hogar" 
                   value="{{ old('personas_hogar', $respuestasExistentes['personas_hogar'] ?? '') }}"
                   min="1" 
                   max="50"
                   required>
            <div class="form-text">Incluyéndose a usted mismo</div>
            @error('personas_hogar')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="form-group">
            <label for="dependientes_economicos" class="form-label">
                Número de dependientes económicos <span class="required">*</span>
            </label>
            <input type="number" 
                   class="form-control @error('dependientes_economicos') is-invalid @enderror" 
                   id="dependientes_economicos" 
                   name="dependientes_economicos" 
                   value="{{ old('dependientes_economicos', $respuestasExistentes['dependientes_economicos'] ?? '') }}"
                   min="0" 
                   max="20"
                   required>
            <div class="form-text">Personas que dependen de sus ingresos</div>
            @error('dependientes_economicos')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="form-group">
    <label for="tipo_vivienda" class="form-label">
        Tipo de vivienda <span class="required">*</span>
    </label>
    <select class="form-control @error('tipo_vivienda') is-invalid @enderror" 
            id="tipo_vivienda" 
            name="tipo_vivienda" 
            required>
        <option value="">Seleccione...</option>
        <option value="propia_pagada" {{ old('tipo_vivienda', $respuestasExistentes['tipo_vivienda'] ?? '') == 'propia_pagada' ? 'selected' : '' }}>Propia (totalmente pagada)</option>
        <option value="propia_pagando" {{ old('tipo_vivienda', $respuestasExistentes['tipo_vivienda'] ?? '') == 'propia_pagando' ? 'selected' : '' }}>Propia (pagando hipoteca)</option>
        <option value="alquilada" {{ old('tipo_vivienda', $respuestasExistentes['tipo_vivienda'] ?? '') == 'alquilada' ? 'selected' : '' }}>Alquilada</option>
        <option value="prestada" {{ old('tipo_vivienda', $respuestasExistentes['tipo_vivienda'] ?? '') == 'prestada' ? 'selected' : '' }}>Prestada</option>
        <option value="familiar" {{ old('tipo_vivienda', $respuestasExistentes['tipo_vivienda'] ?? '') == 'familiar' ? 'selected' : '' }}>Casa familiar</option>
        <option value="otro" {{ old('tipo_vivienda', $respuestasExistentes['tipo_vivienda'] ?? '') == 'otro' ? 'selected' : '' }}>Otro</option>
    </select>
    @error('tipo_vivienda')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<x-campo-condicional trigger="tipo_vivienda" show-when="propia_pagando" id="seccion_vivienda_pagando">
    <div class="row">
        <div class="col-lg-6">
            <div class="form-group">
                <label for="monto_hipoteca" class="form-label">
                    Monto mensual de hipoteca (Q.)
                </label>
                <input type="number" 
                       class="form-control @error('monto_hipoteca') is-invalid @enderror" 
                       id="monto_hipoteca" 
                       name="monto_hipoteca" 
                       value="{{ old('monto_hipoteca', $respuestasExistentes['monto_hipoteca'] ?? '') }}"
                       min="0" 
                       step="0.01">
                @error('monto_hipoteca')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="form-group">
                <label for="anos_restantes_hipoteca" class="form-label">
                    Años restantes de hipoteca
                </label>
                <input type="number" 
                       class="form-control @error('anos_restantes_hipoteca') is-invalid @enderror" 
                       id="anos_restantes_hipoteca" 
                       name="anos_restantes_hipoteca" 
                       value="{{ old('anos_restantes_hipoteca', $respuestasExistentes['anos_restantes_hipoteca'] ?? '') }}"
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
        <label for="monto_alquiler" class="form-label">
            Monto mensual de alquiler (Q.)
        </label>
        <input type="number" 
               class="form-control @error('monto_alquiler') is-invalid @enderror" 
               id="monto_alquiler" 
               name="monto_alquiler" 
               value="{{ old('monto_alquiler', $respuestasExistentes['monto_alquiler'] ?? '') }}"
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
           value="{{ old('personas_contribuyen_gastos', $respuestasExistentes['personas_contribuyen_gastos'] ?? '') }}"
           min="1" 
           max="20"
           required>
    <div class="form-text">Incluyéndose a usted mismo si contribuye</div>
    @error('personas_contribuyen_gastos')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

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
    const personasHogar = document.getElementById('personas_hogar');
    const dependientesEconomicos = document.getElementById('dependientes_economicos');

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

    function validarDependientesEconomicos() {
        const totalPersonas = parseInt(personasHogar.value, 10) || 0;
        const dependientes = parseInt(dependientesEconomicos.value, 10) || 0;

        if (dependientes >= totalPersonas) {
            dependientesEconomicos.value = Math.max(0, totalPersonas - 1);
        }
    }

    numeroHijos?.addEventListener('change', function() {
        validarHijosMenores();
        validarHijosDependientes();
    });
    hijosMenores?.addEventListener('change', validarHijosMenores);
    hijosDependientes?.addEventListener('change', validarHijosDependientes);
    personasHogar?.addEventListener('change', validarDependientesEconomicos);
    dependientesEconomicos?.addEventListener('change', validarDependientesEconomicos);

    if (seccionHijos && !seccionHijos.classList.contains('d-none')) {
        asegurarFilaHijosInicial();
    }
});
</script>
@endpush
