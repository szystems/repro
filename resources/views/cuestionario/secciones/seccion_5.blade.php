{{-- Sección 5: Antecedentes y Referencias --}}

<div class="alert alert-info">
    <i class="fas fa-shield-alt"></i>
    <strong>Información sobre antecedentes y referencias personales</strong>
</div>

<h5 class="section-subtitle mb-3">
    <i class="fas fa-gavel"></i> Antecedentes Legales
</h5>

<div class="row">
    <div class="col-lg-6">
        <div class="form-group">
            <label for="antecedentes_penales" class="form-label">
                ¿Ha tenido antecedentes penales? <span class="required">*</span>
            </label>
            <select class="form-control @error('antecedentes_penales') is-invalid @enderror" 
                    id="antecedentes_penales" 
                    name="antecedentes_penales" 
                    required>
                <option value="">Seleccione...</option>
                <option value="no" {{ old('antecedentes_penales', $respuestas['antecedentes_penales'] ?? '') == 'no' ? 'selected' : '' }}>No</option>
                <option value="si" {{ old('antecedentes_penales', $respuestas['antecedentes_penales'] ?? '') == 'si' ? 'selected' : '' }}>Sí</option>
            </select>
            @error('antecedentes_penales')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="form-group">
            <label for="antecedentes_policiacos" class="form-label">
                ¿Ha tenido antecedentes policíacos? <span class="required">*</span>
            </label>
            <select class="form-control @error('antecedentes_policiacos') is-invalid @enderror" 
                    id="antecedentes_policiacos" 
                    name="antecedentes_policiacos" 
                    required>
                <option value="">Seleccione...</option>
                <option value="no" {{ old('antecedentes_policiacos', $respuestas['antecedentes_policiacos'] ?? '') == 'no' ? 'selected' : '' }}>No</option>
                <option value="si" {{ old('antecedentes_policiacos', $respuestas['antecedentes_policiacos'] ?? '') == 'si' ? 'selected' : '' }}>Sí</option>
            </select>
            @error('antecedentes_policiacos')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div id="seccion_antecedentes_penales" class="d-none">
    <div class="form-group">
        <label for="detalle_antecedentes_penales" class="form-label">
            Detalle de antecedentes penales
        </label>
        <textarea class="form-control @error('detalle_antecedentes_penales') is-invalid @enderror" 
                  id="detalle_antecedentes_penales" 
                  name="detalle_antecedentes_penales" 
                  rows="3"
                  placeholder="Describa brevemente el tipo de antecedente, fecha aproximada y resolución...">{{ old('detalle_antecedentes_penales', $respuestas['detalle_antecedentes_penales'] ?? '') }}</textarea>
        @error('detalle_antecedentes_penales')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div id="seccion_antecedentes_policiacos" class="d-none">
    <div class="form-group">
        <label for="detalle_antecedentes_policiacos" class="form-label">
            Detalle de antecedentes policíacos
        </label>
        <textarea class="form-control @error('detalle_antecedentes_policiacos') is-invalid @enderror" 
                  id="detalle_antecedentes_policiacos" 
                  name="detalle_antecedentes_policiacos" 
                  rows="3"
                  placeholder="Describa brevemente el tipo de antecedente, fecha aproximada y resolución...">{{ old('detalle_antecedentes_policiacos', $respuestas['detalle_antecedentes_policiacos'] ?? '') }}</textarea>
        @error('detalle_antecedentes_policiacos')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="form-group">
            <label for="problemas_drogas" class="form-label">
                ¿Ha tenido problemas con drogas o alcohol? <span class="required">*</span>
            </label>
            <select class="form-control @error('problemas_drogas') is-invalid @enderror" 
                    id="problemas_drogas" 
                    name="problemas_drogas" 
                    required>
                <option value="">Seleccione...</option>
                <option value="no" {{ old('problemas_drogas', $respuestas['problemas_drogas'] ?? '') == 'no' ? 'selected' : '' }}>No</option>
                <option value="si" {{ old('problemas_drogas', $respuestas['problemas_drogas'] ?? '') == 'si' ? 'selected' : '' }}>Sí</option>
            </select>
            @error('problemas_drogas')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="form-group">
            <label for="despedido_trabajo" class="form-label">
                ¿Ha sido despedido de algún trabajo? <span class="required">*</span>
            </label>
            <select class="form-control @error('despedido_trabajo') is-invalid @enderror" 
                    id="despedido_trabajo" 
                    name="despedido_trabajo" 
                    required>
                <option value="">Seleccione...</option>
                <option value="no" {{ old('despedido_trabajo', $respuestas['despedido_trabajo'] ?? '') == 'no' ? 'selected' : '' }}>No</option>
                <option value="si" {{ old('despedido_trabajo', $respuestas['despedido_trabajo'] ?? '') == 'si' ? 'selected' : '' }}>Sí</option>
            </select>
            @error('despedido_trabajo')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div id="seccion_problemas_drogas" class="d-none">
    <div class="form-group">
        <label for="detalle_problemas_drogas" class="form-label">
            Detalle de problemas con drogas o alcohol
        </label>
        <textarea class="form-control @error('detalle_problemas_drogas') is-invalid @enderror" 
                  id="detalle_problemas_drogas" 
                  name="detalle_problemas_drogas" 
                  rows="3"
                  placeholder="Describa brevemente la situación y si recibió tratamiento...">{{ old('detalle_problemas_drogas', $respuestas['detalle_problemas_drogas'] ?? '') }}</textarea>
        @error('detalle_problemas_drogas')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div id="seccion_despedido" class="d-none">
    <div class="form-group">
        <label for="detalle_despedido" class="form-label">
            Detalle del despido laboral
        </label>
        <textarea class="form-control @error('detalle_despedido') is-invalid @enderror" 
                  id="detalle_despedido" 
                  name="detalle_despedido" 
                  rows="3"
                  placeholder="Describa brevemente las razones del despido y las circunstancias...">{{ old('detalle_despedido', $respuestas['detalle_despedido'] ?? '') }}</textarea>
        @error('detalle_despedido')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<h5 class="section-subtitle mt-4 mb-3">
    <i class="fas fa-user-friends"></i> Referencias Personales
</h5>

<div class="alert alert-warning">
    <i class="fas fa-exclamation-triangle"></i>
    <strong>Importante:</strong> Proporcione al menos 2 referencias personales que no sean familiares y que lo conozcan bien.
</div>

{{-- Referencia Personal 1 --}}
<h6 class="mt-3 mb-2">
    <i class="fas fa-user"></i> Referencia Personal #1 <span class="required">*</span>
</h6>

<div class="row">
    <div class="col-lg-6">
        <div class="form-group">
            <label for="ref1_nombre" class="form-label">
                Nombre completo <span class="required">*</span>
            </label>
            <input type="text" 
                   class="form-control @error('ref1_nombre') is-invalid @enderror" 
                   id="ref1_nombre" 
                   name="ref1_nombre" 
                   value="{{ old('ref1_nombre', $respuestas['ref1_nombre'] ?? '') }}"
                   required>
            @error('ref1_nombre')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="form-group">
            <label for="ref1_telefono" class="form-label">
                Teléfono <span class="required">*</span>
            </label>
            <input type="tel" 
                   class="form-control @error('ref1_telefono') is-invalid @enderror" 
                   id="ref1_telefono" 
                   name="ref1_telefono" 
                   value="{{ old('ref1_telefono', $respuestas['ref1_telefono'] ?? '') }}"
                   required>
            @error('ref1_telefono')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="form-group">
            <label for="ref1_relacion" class="form-label">
                Relación/Conoce por <span class="required">*</span>
            </label>
            <select class="form-control @error('ref1_relacion') is-invalid @enderror" 
                    id="ref1_relacion" 
                    name="ref1_relacion" 
                    required>
                <option value="">Seleccione...</option>
                <option value="amigo" {{ old('ref1_relacion', $respuestas['ref1_relacion'] ?? '') == 'amigo' ? 'selected' : '' }}>Amigo</option>
                <option value="vecino" {{ old('ref1_relacion', $respuestas['ref1_relacion'] ?? '') == 'vecino' ? 'selected' : '' }}>Vecino</option>
                <option value="compañero_trabajo" {{ old('ref1_relacion', $respuestas['ref1_relacion'] ?? '') == 'compañero_trabajo' ? 'selected' : '' }}>Compañero de trabajo</option>
                <option value="jefe_supervisor" {{ old('ref1_relacion', $respuestas['ref1_relacion'] ?? '') == 'jefe_supervisor' ? 'selected' : '' }}>Jefe/Supervisor</option>
                <option value="compañero_estudios" {{ old('ref1_relacion', $respuestas['ref1_relacion'] ?? '') == 'compañero_estudios' ? 'selected' : '' }}>Compañero de estudios</option>
                <option value="conocido" {{ old('ref1_relacion', $respuestas['ref1_relacion'] ?? '') == 'conocido' ? 'selected' : '' }}>Conocido</option>
                <option value="otro" {{ old('ref1_relacion', $respuestas['ref1_relacion'] ?? '') == 'otro' ? 'selected' : '' }}>Otro</option>
            </select>
            @error('ref1_relacion')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="form-group">
            <label for="ref1_tiempo_conocerse" class="form-label">
                Tiempo de conocerse (años) <span class="required">*</span>
            </label>
            <input type="number" 
                   class="form-control @error('ref1_tiempo_conocerse') is-invalid @enderror" 
                   id="ref1_tiempo_conocerse" 
                   name="ref1_tiempo_conocerse" 
                   value="{{ old('ref1_tiempo_conocerse', $respuestas['ref1_tiempo_conocerse'] ?? '') }}"
                   min="0" 
                   max="80"
                   required>
            @error('ref1_tiempo_conocerse')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

{{-- Referencia Personal 2 --}}
<h6 class="mt-4 mb-2">
    <i class="fas fa-user"></i> Referencia Personal #2 <span class="required">*</span>
</h6>

<div class="row">
    <div class="col-lg-6">
        <div class="form-group">
            <label for="ref2_nombre" class="form-label">
                Nombre completo <span class="required">*</span>
            </label>
            <input type="text" 
                   class="form-control @error('ref2_nombre') is-invalid @enderror" 
                   id="ref2_nombre" 
                   name="ref2_nombre" 
                   value="{{ old('ref2_nombre', $respuestas['ref2_nombre'] ?? '') }}"
                   required>
            @error('ref2_nombre')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="form-group">
            <label for="ref2_telefono" class="form-label">
                Teléfono <span class="required">*</span>
            </label>
            <input type="tel" 
                   class="form-control @error('ref2_telefono') is-invalid @enderror" 
                   id="ref2_telefono" 
                   name="ref2_telefono" 
                   value="{{ old('ref2_telefono', $respuestas['ref2_telefono'] ?? '') }}"
                   required>
            @error('ref2_telefono')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="form-group">
            <label for="ref2_relacion" class="form-label">
                Relación/Conoce por <span class="required">*</span>
            </label>
            <select class="form-control @error('ref2_relacion') is-invalid @enderror" 
                    id="ref2_relacion" 
                    name="ref2_relacion" 
                    required>
                <option value="">Seleccione...</option>
                <option value="amigo" {{ old('ref2_relacion', $respuestas['ref2_relacion'] ?? '') == 'amigo' ? 'selected' : '' }}>Amigo</option>
                <option value="vecino" {{ old('ref2_relacion', $respuestas['ref2_relacion'] ?? '') == 'vecino' ? 'selected' : '' }}>Vecino</option>
                <option value="compañero_trabajo" {{ old('ref2_relacion', $respuestas['ref2_relacion'] ?? '') == 'compañero_trabajo' ? 'selected' : '' }}>Compañero de trabajo</option>
                <option value="jefe_supervisor" {{ old('ref2_relacion', $respuestas['ref2_relacion'] ?? '') == 'jefe_supervisor' ? 'selected' : '' }}>Jefe/Supervisor</option>
                <option value="compañero_estudios" {{ old('ref2_relacion', $respuestas['ref2_relacion'] ?? '') == 'compañero_estudios' ? 'selected' : '' }}>Compañero de estudios</option>
                <option value="conocido" {{ old('ref2_relacion', $respuestas['ref2_relacion'] ?? '') == 'conocido' ? 'selected' : '' }}>Conocido</option>
                <option value="otro" {{ old('ref2_relacion', $respuestas['ref2_relacion'] ?? '') == 'otro' ? 'selected' : '' }}>Otro</option>
            </select>
            @error('ref2_relacion')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="form-group">
            <label for="ref2_tiempo_conocerse" class="form-label">
                Tiempo de conocerse (años) <span class="required">*</span>
            </label>
            <input type="number" 
                   class="form-control @error('ref2_tiempo_conocerse') is-invalid @enderror" 
                   id="ref2_tiempo_conocerse" 
                   name="ref2_tiempo_conocerse" 
                   value="{{ old('ref2_tiempo_conocerse', $respuestas['ref2_tiempo_conocerse'] ?? '') }}"
                   min="0" 
                   max="80"
                   required>
            @error('ref2_tiempo_conocerse')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

{{-- Referencia Personal 3 (Opcional) --}}
<h6 class="mt-4 mb-2">
    <i class="fas fa-user"></i> Referencia Personal #3 (Opcional)
</h6>

<div class="row">
    <div class="col-lg-6">
        <div class="form-group">
            <label for="ref3_nombre" class="form-label">
                Nombre completo
            </label>
            <input type="text" 
                   class="form-control @error('ref3_nombre') is-invalid @enderror" 
                   id="ref3_nombre" 
                   name="ref3_nombre" 
                   value="{{ old('ref3_nombre', $respuestas['ref3_nombre'] ?? '') }}">
            @error('ref3_nombre')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="form-group">
            <label for="ref3_telefono" class="form-label">
                Teléfono
            </label>
            <input type="tel" 
                   class="form-control @error('ref3_telefono') is-invalid @enderror" 
                   id="ref3_telefono" 
                   name="ref3_telefono" 
                   value="{{ old('ref3_telefono', $respuestas['ref3_telefono'] ?? '') }}">
            @error('ref3_telefono')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="form-group">
            <label for="ref3_relacion" class="form-label">
                Relación/Conoce por
            </label>
            <select class="form-control @error('ref3_relacion') is-invalid @enderror" 
                    id="ref3_relacion" 
                    name="ref3_relacion">
                <option value="">Seleccione...</option>
                <option value="amigo" {{ old('ref3_relacion', $respuestas['ref3_relacion'] ?? '') == 'amigo' ? 'selected' : '' }}>Amigo</option>
                <option value="vecino" {{ old('ref3_relacion', $respuestas['ref3_relacion'] ?? '') == 'vecino' ? 'selected' : '' }}>Vecino</option>
                <option value="compañero_trabajo" {{ old('ref3_relacion', $respuestas['ref3_relacion'] ?? '') == 'compañero_trabajo' ? 'selected' : '' }}>Compañero de trabajo</option>
                <option value="jefe_supervisor" {{ old('ref3_relacion', $respuestas['ref3_relacion'] ?? '') == 'jefe_supervisor' ? 'selected' : '' }}>Jefe/Supervisor</option>
                <option value="compañero_estudios" {{ old('ref3_relacion', $respuestas['ref3_relacion'] ?? '') == 'compañero_estudios' ? 'selected' : '' }}>Compañero de estudios</option>
                <option value="conocido" {{ old('ref3_relacion', $respuestas['ref3_relacion'] ?? '') == 'conocido' ? 'selected' : '' }}>Conocido</option>
                <option value="otro" {{ old('ref3_relacion', $respuestas['ref3_relacion'] ?? '') == 'otro' ? 'selected' : '' }}>Otro</option>
            </select>
            @error('ref3_relacion')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="form-group">
            <label for="ref3_tiempo_conocerse" class="form-label">
                Tiempo de conocerse (años)
            </label>
            <input type="number" 
                   class="form-control @error('ref3_tiempo_conocerse') is-invalid @enderror" 
                   id="ref3_tiempo_conocerse" 
                   name="ref3_tiempo_conocerse" 
                   value="{{ old('ref3_tiempo_conocerse', $respuestas['ref3_tiempo_conocerse'] ?? '') }}"
                   min="0" 
                   max="80">
            @error('ref3_tiempo_conocerse')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<h5 class="section-subtitle mt-4 mb-3">
    <i class="fas fa-comment-alt"></i> Información Adicional
</h5>

<div class="form-group">
    <label for="informacion_adicional" class="form-label">
        ¿Hay algo más que considere importante mencionar?
    </label>
    <textarea class="form-control @error('informacion_adicional') is-invalid @enderror" 
              id="informacion_adicional" 
              name="informacion_adicional" 
              rows="4"
              placeholder="Cualquier información adicional que considere relevante para su evaluación...">{{ old('informacion_adicional', $respuestas['informacion_adicional'] ?? '') }}</textarea>
    @error('informacion_adicional')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label for="motivacion_trabajo" class="form-label">
        ¿Por qué está interesado en este puesto de trabajo? <span class="required">*</span>
    </label>
    <textarea class="form-control @error('motivacion_trabajo') is-invalid @enderror" 
              id="motivacion_trabajo" 
              name="motivacion_trabajo" 
              rows="3"
              placeholder="Describa sus motivaciones e interés en el puesto..."
              required>{{ old('motivacion_trabajo', $respuestas['motivacion_trabajo'] ?? '') }}</textarea>
    @error('motivacion_trabajo')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="alert alert-success mt-4">
    <h6><i class="fas fa-check-circle"></i> Declaración de Veracidad</h6>
    <div class="form-check">
        <input class="form-check-input @error('declaracion_veracidad') is-invalid @enderror" 
               type="checkbox" 
               id="declaracion_veracidad" 
               name="declaracion_veracidad" 
               value="1"
               {{ old('declaracion_veracidad', $respuestas['declaracion_veracidad'] ?? '') ? 'checked' : '' }}
               required>
        <label class="form-check-label" for="declaracion_veracidad">
            <strong>Declaro bajo juramento que toda la información proporcionada en este cuestionario es veraz y completa.</strong> 
            Entiendo que cualquier falsedad o información incorrecta puede resultar en la descalificación del proceso de selección o, 
            en caso de ser contratado, en la terminación inmediata de mi relación laboral. <span class="required">*</span>
        </label>
        @error('declaracion_veracidad')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Campos que activan secciones de detalle
    const antecedentes = {
        'antecedentes_penales': 'seccion_antecedentes_penales',
        'antecedentes_policiacos': 'seccion_antecedentes_policiacos',
        'problemas_drogas': 'seccion_problemas_drogas',
        'despedido_trabajo': 'seccion_despedido'
    };
    
    // Función para mostrar/ocultar secciones de detalle
    function toggleSeccionDetalle(campo, seccion) {
        const campoElement = document.getElementById(campo);
        const seccionElement = document.getElementById(seccion);
        
        if (campoElement.value === 'si') {
            seccionElement.classList.remove('d-none');
            // Hacer el campo de detalle requerido
            const textarea = seccionElement.querySelector('textarea');
            if (textarea) {
                textarea.required = true;
            }
        } else {
            seccionElement.classList.add('d-none');
            // Remover requerimiento y limpiar
            const textarea = seccionElement.querySelector('textarea');
            if (textarea) {
                textarea.required = false;
                textarea.value = '';
            }
        }
    }
    
    // Configurar event listeners para antecedentes
    Object.keys(antecedentes).forEach(campo => {
        const campoElement = document.getElementById(campo);
        campoElement.addEventListener('change', function() {
            toggleSeccionDetalle(campo, antecedentes[campo]);
        });
        
        // Inicializar estado
        toggleSeccionDetalle(campo, antecedentes[campo]);
    });
    
    // Validación de teléfonos (solo números y longitud)
    const telefonos = ['ref1_telefono', 'ref2_telefono', 'ref3_telefono'];
    telefonos.forEach(campo => {
        const input = document.getElementById(campo);
        input.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '');
            if (this.value.length > 8) {
                this.value = this.value.substring(0, 8);
            }
        });
    });
    
    // Validación para evitar referencias duplicadas
    function validarReferenciasUnicas() {
        const ref1Telefono = document.getElementById('ref1_telefono').value;
        const ref2Telefono = document.getElementById('ref2_telefono').value;
        const ref3Telefono = document.getElementById('ref3_telefono').value;
        
        const telefonos = [ref1Telefono, ref2Telefono, ref3Telefono].filter(t => t.length > 0);
        const telefonosUnicos = [...new Set(telefonos)];
        
        if (telefonos.length !== telefonosUnicos.length) {
            document.getElementById('ref2_telefono').setCustomValidity('No puede repetir el mismo número de teléfono en las referencias');
            return false;
        } else {
            document.getElementById('ref2_telefono').setCustomValidity('');
            return true;
        }
    }
    
    // Event listeners para validación de referencias
    ['ref1_telefono', 'ref2_telefono', 'ref3_telefono'].forEach(campo => {
        document.getElementById(campo).addEventListener('blur', validarReferenciasUnicas);
    });
    
    // Validar que las referencias no sean familiares
    const relacionesProhibidas = ['padre', 'madre', 'hermano', 'hermana', 'hijo', 'hija', 'esposo', 'esposa', 'familiar'];
    
    function validarRelacion(campo) {
        const select = document.getElementById(campo);
        const valor = select.value.toLowerCase();
        
        relacionesProhibidas.forEach(prohibida => {
            if (valor.includes(prohibida)) {
                select.setCustomValidity('Las referencias no pueden ser familiares');
                return;
            }
        });
        
        select.setCustomValidity('');
    }
    
    ['ref1_relacion', 'ref2_relacion', 'ref3_relacion'].forEach(campo => {
        document.getElementById(campo).addEventListener('change', function() {
            validarRelacion(campo);
        });
    });
    
    // Validar formulario antes del envío
    const form = document.getElementById('cuestionarioForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!validarReferenciasUnicas()) {
                e.preventDefault();
                cuestionarioHelpers.showAlert('Por favor, corrija los errores en las referencias antes de continuar', 'warning');
                return false;
            }
        });
    }
});
</script>
@endpush