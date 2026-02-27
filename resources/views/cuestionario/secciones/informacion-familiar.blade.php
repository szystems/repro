{{-- Sección 2: Información Familiar --}}

<div class="alert alert-info">
    <i class="fas fa-info-circle"></i>
    <strong>Información sobre su núcleo familiar y estado socioeconómico</strong>
</div>

<div class="row">
    <div class="col-lg-6">
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

<div id="seccion_hijos" class="d-none">
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
                       max="20">
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
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="form-group">
            <label for="vive_con_pareja" class="form-label">
                ¿Vive con pareja? <span class="required">*</span>
            </label>
            <select class="form-control @error('vive_con_pareja') is-invalid @enderror" 
                    id="vive_con_pareja" 
                    name="vive_con_pareja" 
                    required>
                <option value="">Seleccione...</option>
                <option value="si" {{ old('vive_con_pareja', $respuestasExistentes['vive_con_pareja'] ?? '') == 'si' ? 'selected' : '' }}>Sí</option>
                <option value="no" {{ old('vive_con_pareja', $respuestasExistentes['vive_con_pareja'] ?? '') == 'no' ? 'selected' : '' }}>No</option>
            </select>
            @error('vive_con_pareja')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="col-lg-6" id="seccion_pareja" class="d-none">
        <div class="form-group">
            <label for="pareja_trabaja" class="form-label">
                ¿Su pareja trabaja?
            </label>
            <select class="form-control @error('pareja_trabaja') is-invalid @enderror" 
                    id="pareja_trabaja" 
                    name="pareja_trabaja">
                <option value="">Seleccione...</option>
                <option value="si" {{ old('pareja_trabaja', $respuestasExistentes['pareja_trabaja'] ?? '') == 'si' ? 'selected' : '' }}>Sí</option>
                <option value="no" {{ old('pareja_trabaja', $respuestasExistentes['pareja_trabaja'] ?? '') == 'no' ? 'selected' : '' }}>No</option>
            </select>
            @error('pareja_trabaja')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

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

<div id="seccion_vivienda_pagando" class="d-none">
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
</div>

<div id="seccion_alquiler" class="d-none">
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
</div>

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
    const tieneHijos = document.getElementById('tiene_hijos');
    const seccionHijos = document.getElementById('seccion_hijos');
    const viveConPareja = document.getElementById('vive_con_pareja');
    const seccionPareja = document.getElementById('seccion_pareja');
    const tipoVivienda = document.getElementById('tipo_vivienda');
    const seccionHipoteca = document.getElementById('seccion_vivienda_pagando');
    const seccionAlquiler = document.getElementById('seccion_alquiler');
    
    // Mostrar/ocultar sección de hijos
    function toggleSeccionHijos() {
        if (tieneHijos.value === 'si') {
            seccionHijos.classList.remove('d-none');
            // Hacer campos requeridos
            document.getElementById('numero_hijos').required = true;
        } else {
            seccionHijos.classList.add('d-none');
            // Remover requerimientos y limpiar valores
            document.getElementById('numero_hijos').required = false;
            document.getElementById('numero_hijos').value = '';
            document.getElementById('hijos_menores').value = '';
            document.getElementById('hijos_dependientes').value = '';
        }
    }
    
    // Mostrar/ocultar sección de pareja
    function toggleSeccionPareja() {
        if (viveConPareja.value === 'si') {
            seccionPareja.classList.remove('d-none');
        } else {
            seccionPareja.classList.add('d-none');
            document.getElementById('pareja_trabaja').value = '';
        }
    }
    
    // Mostrar/ocultar secciones de vivienda
    function toggleSeccionesVivienda() {
        // Ocultar todas las secciones
        seccionHipoteca.classList.add('d-none');
        seccionAlquiler.classList.add('d-none');
        
        // Limpiar campos
        document.getElementById('monto_hipoteca').value = '';
        document.getElementById('anos_restantes_hipoteca').value = '';
        document.getElementById('monto_alquiler').value = '';
        
        // Mostrar sección correspondiente
        if (tipoVivienda.value === 'propia_pagando') {
            seccionHipoteca.classList.remove('d-none');
        } else if (tipoVivienda.value === 'alquilada') {
            seccionAlquiler.classList.remove('d-none');
        }
    }
    
    // Event listeners
    tieneHijos.addEventListener('change', toggleSeccionHijos);
    viveConPareja.addEventListener('change', toggleSeccionPareja);
    tipoVivienda.addEventListener('change', toggleSeccionesVivienda);
    
    // Validaciones numéricas
    const numeroHijos = document.getElementById('numero_hijos');
    const hijosMenores = document.getElementById('hijos_menores');
    const hijosDependientes = document.getElementById('hijos_dependientes');
    const personasHogar = document.getElementById('personas_hogar');
    const dependientesEconomicos = document.getElementById('dependientes_economicos');
    
    // Validar que hijos menores no sea mayor que número total
    function validarHijosMenores() {
        const total = parseInt(numeroHijos.value) || 0;
        const menores = parseInt(hijosMenores.value) || 0;
        
        if (menores > total) {
            hijosMenores.value = total;
        }
    }
    
    // Validar que hijos dependientes no sea mayor que número total
    function validarHijosDependientes() {
        const total = parseInt(numeroHijos.value) || 0;
        const dependientes = parseInt(hijosDependientes.value) || 0;
        
        if (dependientes > total) {
            hijosDependientes.value = total;
        }
    }
    
    // Validar que dependientes económicos no sea mayor que personas en hogar
    function validarDependientesEconomicos() {
        const totalPersonas = parseInt(personasHogar.value) || 0;
        const dependientes = parseInt(dependientesEconomicos.value) || 0;
        
        if (dependientes >= totalPersonas) {
            dependientesEconomicos.value = Math.max(0, totalPersonas - 1);
        }
    }
    
    numeroHijos.addEventListener('change', function() {
        validarHijosMenores();
        validarHijosDependientes();
    });
    
    hijosMenores.addEventListener('change', validarHijosMenores);
    hijosDependientes.addEventListener('change', validarHijosDependientes);
    personasHogar.addEventListener('change', validarDependientesEconomicos);
    dependientesEconomicos.addEventListener('change', validarDependientesEconomicos);
    
    // Inicializar estado al cargar
    toggleSeccionHijos();
    toggleSeccionPareja();
    toggleSeccionesVivienda();
});
</script>
@endpush