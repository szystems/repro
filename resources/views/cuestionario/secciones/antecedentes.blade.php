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