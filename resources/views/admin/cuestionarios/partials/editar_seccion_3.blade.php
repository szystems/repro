{{-- Partial para editar Sección 3: Experiencia Laboral --}}
<div class="section-edit-content">
    <h6 class="text-primary mb-3">
        <i class="bi bi-briefcase"></i> Experiencia Laboral
    </h6>
    
    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i>
        <strong>Nota:</strong> La edición de experiencia laboral requiere un formulario complejo. 
        Para modificaciones detalladas, utilice la interfaz especializada.
    </div>
    
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label for="anios_experiencia_total" class="form-label">Años de Experiencia Total</label>
                <input type="number" 
                       class="form-control" 
                       id="anios_experiencia_total" 
                       name="seccion_{{ $seccion }}[anios_experiencia_total]" 
                       value="{{ old('seccion_' . $seccion . '.anios_experiencia_total', $respuestas['anios_experiencia_total'] ?? '') }}"
                       min="0"
                       max="50">
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="form-group">
                <label for="numero_empleos" class="form-label">Número de Empleos Anteriores</label>
                <input type="number" 
                       class="form-control" 
                       id="numero_empleos" 
                       name="seccion_{{ $seccion }}[numero_empleos]" 
                       value="{{ old('seccion_' . $seccion . '.numero_empleos', $respuestas['numero_empleos'] ?? '') }}"
                       min="0"
                       max="20">
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="form-group">
                <label for="salario_actual" class="form-label">Salario Actual/Esperado</label>
                <input type="text" 
                       class="form-control" 
                       id="salario_actual" 
                       name="seccion_{{ $seccion }}[salario_actual]" 
                       value="{{ old('seccion_' . $seccion . '.salario_actual', $respuestas['salario_actual'] ?? '') }}"
                       placeholder="Q0.00">
            </div>
        </div>
        
        <div class="col-12">
            <div class="form-group">
                <label for="resumen_experiencia" class="form-label">Resumen de Experiencia</label>
                <textarea class="form-control" 
                          id="resumen_experiencia" 
                          name="seccion_{{ $seccion }}[resumen_experiencia]" 
                          rows="4"
                          placeholder="Descripción general de la experiencia laboral...">{{ old('seccion_' . $seccion . '.resumen_experiencia', $respuestas['resumen_experiencia'] ?? '') }}</textarea>
            </div>
        </div>
    </div>
    
    {{-- Nota sobre edición avanzada --}}
    <div class="mt-3">
        <div class="card bg-light">
            <div class="card-body">
                <h6 class="card-title">
                    <i class="bi bi-gear"></i> Edición Avanzada
                </h6>
                <p class="card-text">
                    El historial completo de empleos, referencias laborales y expectativas se pueden editar 
                    usando el editor JSON avanzado o contactando al administrador del sistema.
                </p>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="mostrarEditorJSON('experiencia_laboral')">
                    <i class="bi bi-code-slash"></i> Editor JSON
                </button>
            </div>
        </div>
        
        <textarea id="experiencia_laboral_json" 
                  name="seccion_{{ $seccion }}[datos_json]" 
                  class="form-control mt-2" 
                  style="display: none; font-family: monospace;"
                  rows="10">{{ json_encode($respuestas, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</textarea>
    </div>
</div>

@push('scripts')
<script>
function mostrarEditorJSON(campo) {
    const textarea = document.getElementById(campo + '_json');
    const button = event.target;
    
    if (textarea.style.display === 'none') {
        textarea.style.display = 'block';
        button.innerHTML = '<i class="bi bi-eye-slash"></i> Ocultar JSON';
    } else {
        textarea.style.display = 'none';
        button.innerHTML = '<i class="bi bi-code-slash"></i> Editor JSON';
    }
}
</script>
@endpush