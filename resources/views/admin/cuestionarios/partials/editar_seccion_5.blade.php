{{-- Partial para editar Sección 5 --}}
@php
    $tipoFormulario = $tipoFormulario ?? 'preempleo';
    $nombreSeccion = $nombreSeccion ?? 'Antecedentes y Referencias';
@endphp
<div class="section-edit-content">
    <h6 class="text-primary mb-3">
        <i class="bi bi-shield-check"></i> {{ $nombreSeccion }}
    </h6>
    
    {{-- Antecedentes --}}
    <div class="row">
        <div class="col-12 mb-3">
            <h6 class="text-secondary border-bottom pb-2">Antecedentes</h6>
        </div>
        
        <div class="col-md-6">
            <div class="form-group">
                <label for="antecedentes_penales" class="form-label">¿Tiene Antecedentes Penales?</label>
                <select class="form-control" id="antecedentes_penales" name="respuestas[antecedentes_penales]">
                    <option value="">Seleccione...</option>
                    <option value="si" {{ ($respuestas['antecedentes_penales'] ?? '') == 'si' ? 'selected' : '' }}>Sí</option>
                    <option value="no" {{ ($respuestas['antecedentes_penales'] ?? '') == 'no' ? 'selected' : '' }}>No</option>
                </select>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="form-group">
                <label for="despedido_trabajo" class="form-label">¿Ha sido despedido de algún trabajo?</label>
                <select class="form-control" id="despedido_trabajo" name="respuestas[despedido_trabajo]">
                    <option value="">Seleccione...</option>
                    <option value="si" {{ ($respuestas['despedido_trabajo'] ?? '') == 'si' ? 'selected' : '' }}>Sí</option>
                    <option value="no" {{ ($respuestas['despedido_trabajo'] ?? '') == 'no' ? 'selected' : '' }}>No</option>
                </select>
            </div>
        </div>
        
        <div class="col-12">
            <div class="form-group">
                <label for="motivo_despido" class="form-label">Motivo del Despido (si aplica)</label>
                <textarea class="form-control" id="motivo_despido" 
                          name="respuestas[motivo_despido]" 
                          rows="2">{{ $respuestas['motivo_despido'] ?? '' }}</textarea>
            </div>
        </div>
        
        <div class="col-12">
            <div class="form-group">
                <label for="detalle_antecedentes" class="form-label">Detalle de Antecedentes</label>
                <textarea class="form-control" id="detalle_antecedentes" 
                          name="respuestas[detalle_antecedentes]" 
                          rows="2">{{ $respuestas['detalle_antecedentes'] ?? '' }}</textarea>
            </div>
        </div>
    </div>
    
    {{-- Salud --}}
    <div class="row mt-4">
        <div class="col-12 mb-3">
            <h6 class="text-secondary border-bottom pb-2">Salud y Hábitos</h6>
        </div>
        
        <div class="col-md-4">
            <div class="form-group">
                <label for="problemas_salud_mental" class="form-label">¿Problemas de Salud Mental?</label>
                <select class="form-control" id="problemas_salud_mental" name="respuestas[problemas_salud_mental]">
                    <option value="">Seleccione...</option>
                    <option value="si" {{ ($respuestas['problemas_salud_mental'] ?? '') == 'si' ? 'selected' : '' }}>Sí</option>
                    <option value="no" {{ ($respuestas['problemas_salud_mental'] ?? '') == 'no' ? 'selected' : '' }}>No</option>
                </select>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="form-group">
                <label for="detalle_salud_mental" class="form-label">Detalle (si aplica)</label>
                <input type="text" class="form-control" id="detalle_salud_mental" 
                       name="respuestas[detalle_salud_mental]" 
                       value="{{ $respuestas['detalle_salud_mental'] ?? '' }}">
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="form-group">
                <label for="consume_alcohol" class="form-label">Consumo de Alcohol</label>
                <select class="form-control" id="consume_alcohol" name="respuestas[consume_alcohol]">
                    <option value="">Seleccione...</option>
                    <option value="nunca" {{ ($respuestas['consume_alcohol'] ?? '') == 'nunca' ? 'selected' : '' }}>Nunca</option>
                    <option value="ocasionalmente" {{ ($respuestas['consume_alcohol'] ?? '') == 'ocasionalmente' ? 'selected' : '' }}>Ocasionalmente</option>
                    <option value="frecuentemente" {{ ($respuestas['consume_alcohol'] ?? '') == 'frecuentemente' ? 'selected' : '' }}>Frecuentemente</option>
                </select>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="form-group">
                <label for="consume_drogas" class="form-label">Consumo de Drogas</label>
                <select class="form-control" id="consume_drogas" name="respuestas[consume_drogas]">
                    <option value="">Seleccione...</option>
                    <option value="nunca" {{ ($respuestas['consume_drogas'] ?? '') == 'nunca' ? 'selected' : '' }}>Nunca</option>
                    <option value="ocasionalmente" {{ ($respuestas['consume_drogas'] ?? '') == 'ocasionalmente' ? 'selected' : '' }}>Ocasionalmente</option>
                    <option value="frecuentemente" {{ ($respuestas['consume_drogas'] ?? '') == 'frecuentemente' ? 'selected' : '' }}>Frecuentemente</option>
                </select>
            </div>
        </div>
    </div>
    
    {{-- Referencias --}}
    <div class="row mt-4">
        <div class="col-12 mb-3">
            <h6 class="text-secondary border-bottom pb-2">Referencias Personales</h6>
        </div>
        
        {{-- Referencia 1 --}}
        <div class="col-12 mb-2">
            <strong class="text-muted">Referencia 1</strong>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="referencia1_nombre" class="form-label">Nombre Completo</label>
                <input type="text" class="form-control" id="referencia1_nombre" 
                       name="respuestas[referencia1_nombre]" 
                       value="{{ $respuestas['referencia1_nombre'] ?? '' }}">
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="referencia1_relacion" class="form-label">Relación</label>
                <input type="text" class="form-control" id="referencia1_relacion" 
                       name="respuestas[referencia1_relacion]" 
                       value="{{ $respuestas['referencia1_relacion'] ?? '' }}"
                       placeholder="Ej: Amigo, Exjefe, Vecino...">
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="referencia1_telefono" class="form-label">Teléfono</label>
                <input type="tel" class="form-control" id="referencia1_telefono" 
                       name="respuestas[referencia1_telefono]" 
                       value="{{ $respuestas['referencia1_telefono'] ?? '' }}">
            </div>
        </div>
        
        {{-- Referencia 2 --}}
        <div class="col-12 mb-2 mt-3">
            <strong class="text-muted">Referencia 2</strong>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="referencia2_nombre" class="form-label">Nombre Completo</label>
                <input type="text" class="form-control" id="referencia2_nombre" 
                       name="respuestas[referencia2_nombre]" 
                       value="{{ $respuestas['referencia2_nombre'] ?? '' }}">
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="referencia2_relacion" class="form-label">Relación</label>
                <input type="text" class="form-control" id="referencia2_relacion" 
                       name="respuestas[referencia2_relacion]" 
                       value="{{ $respuestas['referencia2_relacion'] ?? '' }}"
                       placeholder="Ej: Amigo, Exjefe, Vecino...">
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="referencia2_telefono" class="form-label">Teléfono</label>
                <input type="tel" class="form-control" id="referencia2_telefono" 
                       name="respuestas[referencia2_telefono]" 
                       value="{{ $respuestas['referencia2_telefono'] ?? '' }}">
            </div>
        </div>
        
        {{-- Referencia 3 (opcional) --}}
        <div class="col-12 mb-2 mt-3">
            <strong class="text-muted">Referencia 3 (Opcional)</strong>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="referencia3_nombre" class="form-label">Nombre Completo</label>
                <input type="text" class="form-control" id="referencia3_nombre" 
                       name="respuestas[referencia3_nombre]" 
                       value="{{ $respuestas['referencia3_nombre'] ?? '' }}">
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="referencia3_relacion" class="form-label">Relación</label>
                <input type="text" class="form-control" id="referencia3_relacion" 
                       name="respuestas[referencia3_relacion]" 
                       value="{{ $respuestas['referencia3_relacion'] ?? '' }}"
                       placeholder="Ej: Amigo, Exjefe, Vecino...">
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="referencia3_telefono" class="form-label">Teléfono</label>
                <input type="tel" class="form-control" id="referencia3_telefono" 
                       name="respuestas[referencia3_telefono]" 
                       value="{{ $respuestas['referencia3_telefono'] ?? '' }}">
            </div>
        </div>
    </div>
    
    {{-- Observaciones --}}
    <div class="row mt-4">
        <div class="col-12">
            <div class="form-group">
                <label for="observaciones_adicionales" class="form-label">Observaciones Adicionales</label>
                <textarea class="form-control" id="observaciones_adicionales" 
                          name="respuestas[observaciones_adicionales]" 
                          rows="3">{{ $respuestas['observaciones_adicionales'] ?? '' }}</textarea>
            </div>
        </div>
    </div>
</div>
