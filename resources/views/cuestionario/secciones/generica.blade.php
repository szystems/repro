{{-- Vista genérica para secciones no definidas --}}
<div class="alert alert-warning">
    <i class="fas fa-exclamation-triangle"></i>
    <strong>Sección no disponible</strong>
    <p class="mb-0">Esta sección no tiene contenido definido. Por favor, contacte al administrador.</p>
</div>

<div class="form-group mt-4">
    <label for="observaciones_genericas" class="form-label">Observaciones adicionales</label>
    <textarea class="form-control" 
              name="observaciones_genericas" 
              id="observaciones_genericas" 
              rows="4" 
              placeholder="Ingrese cualquier información adicional que considere relevante...">{{ $respuestas['observaciones_genericas'] ?? '' }}</textarea>
</div>
