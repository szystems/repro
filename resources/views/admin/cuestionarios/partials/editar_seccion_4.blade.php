{{-- Partial para editar Sección 4: Competencias y Habilidades --}}
<div class="section-edit-content">
    <h6 class="text-primary mb-3">
        <i class="bi bi-star"></i> Competencias y Habilidades
    </h6>
    
    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i>
        <strong>Nota:</strong> Esta sección contiene datos complejos de evaluación de competencias.
        Use el editor JSON para modificaciones detalladas.
    </div>
    
    {{-- Fortalezas Principales --}}
    <div class="form-group">
        <label for="fortalezas_principales" class="form-label">
            <i class="bi bi-hand-thumbs-up text-success"></i> Fortalezas Principales
        </label>
        <textarea class="form-control" 
                  id="fortalezas_principales" 
                  name="seccion_{{ $seccion }}[fortalezas_principales]" 
                  rows="3"
                  placeholder="Liste las principales fortalezas del evaluado...">{{ old('seccion_' . $seccion . '.fortalezas_principales', is_array($respuestas['fortalezas_principales'] ?? null) ? implode("\n", $respuestas['fortalezas_principales']) : ($respuestas['fortalezas_principales'] ?? '')) }}</textarea>
        <small class="form-text text-muted">Una fortaleza por línea</small>
    </div>
    
    {{-- Áreas de Mejora --}}
    <div class="form-group">
        <label for="areas_mejora" class="form-label">
            <i class="bi bi-graph-up text-warning"></i> Áreas de Mejora
        </label>
        <textarea class="form-control" 
                  id="areas_mejora" 
                  name="seccion_{{ $seccion }}[areas_mejora]" 
                  rows="3"
                  placeholder="Liste las áreas de mejora identificadas...">{{ old('seccion_' . $seccion . '.areas_mejora', is_array($respuestas['areas_mejora'] ?? null) ? implode("\n", $respuestas['areas_mejora']) : ($respuestas['areas_mejora'] ?? '')) }}</textarea>
        <small class="form-text text-muted">Un área por línea</small>
    </div>
    
    {{-- Autoevaluación General --}}
    <div class="form-group">
        <label for="autoevaluacion_general" class="form-label">Autoevaluación General</label>
        <textarea class="form-control" 
                  id="autoevaluacion_general" 
                  name="seccion_{{ $seccion }}[autoevaluacion_general]" 
                  rows="4"
                  placeholder="Descripción general de la autoevaluación del evaluado...">{{ old('seccion_' . $seccion . '.autoevaluacion_general', $respuestas['autoevaluacion_general'] ?? '') }}</textarea>
    </div>
    
    {{-- Habilidades Técnicas Simplificadas --}}
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label class="form-label">Nivel de Informática (0-100)</label>
                <input type="range" 
                       class="form-range" 
                       id="informatica_nivel" 
                       name="seccion_{{ $seccion }}[habilidades_tecnicas][informatica]"
                       min="0" 
                       max="100" 
                       value="{{ old('seccion_' . $seccion . '.habilidades_tecnicas.informatica', $respuestas['habilidades_tecnicas']['informatica'] ?? 50) }}">
                <div class="d-flex justify-content-between">
                    <small>Básico (0)</small>
                    <small id="informatica_valor">{{ old('seccion_' . $seccion . '.habilidades_tecnicas.informatica', $respuestas['habilidades_tecnicas']['informatica'] ?? 50) }}%</small>
                    <small>Experto (100)</small>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="form-group">
                <label class="form-label">Nivel de Comunicación (0-100)</label>
                <input type="range" 
                       class="form-range" 
                       id="comunicacion_nivel" 
                       name="seccion_{{ $seccion }}[habilidades_blandas][comunicacion]"
                       min="0" 
                       max="100" 
                       value="{{ old('seccion_' . $seccion . '.habilidades_blandas.comunicacion', $respuestas['habilidades_blandas']['comunicacion'] ?? 50) }}">
                <div class="d-flex justify-content-between">
                    <small>Básico (0)</small>
                    <small id="comunicacion_valor">{{ old('seccion_' . $seccion . '.habilidades_blandas.comunicacion', $respuestas['habilidades_blandas']['comunicacion'] ?? 50) }}%</small>
                    <small>Excelente (100)</small>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Editor JSON para datos complejos --}}
    <div class="mt-4">
        <div class="card bg-light">
            <div class="card-body">
                <h6 class="card-title">
                    <i class="bi bi-gear"></i> Edición Completa de Competencias
                </h6>
                <p class="card-text">
                    Para editar habilidades técnicas, test de personalidad, competencias de liderazgo 
                    y otros datos complejos, use el editor JSON.
                </p>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="mostrarEditorJSON('competencias')">
                    <i class="bi bi-code-slash"></i> Editor JSON
                </button>
            </div>
        </div>
        
        <textarea id="competencias_json" 
                  name="seccion_{{ $seccion }}[datos_json]" 
                  class="form-control mt-2" 
                  style="display: none; font-family: monospace;"
                  rows="15">{{ json_encode($respuestas, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</textarea>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Actualizar valores de los rangesliders
    const informaticaRange = document.getElementById('informatica_nivel');
    const comunicacionRange = document.getElementById('comunicacion_nivel');
    
    if (informaticaRange) {
        informaticaRange.addEventListener('input', function() {
            document.getElementById('informatica_valor').textContent = this.value + '%';
        });
    }
    
    if (comunicacionRange) {
        comunicacionRange.addEventListener('input', function() {
            document.getElementById('comunicacion_valor').textContent = this.value + '%';
        });
    }
});

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