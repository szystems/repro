{{-- Partial para editar Sección 4 --}}
@php
    $tipoFormulario = $tipoFormulario ?? 'preempleo';
    $nombreSeccion = $nombreSeccion ?? 'Situación Económica';
@endphp
<div class="section-edit-content">
    <h6 class="text-primary mb-3">
        <i class="bi bi-cash-stack"></i> {{ $nombreSeccion }}
    </h6>
    
    {{-- Campos de Situación Económica (común para preempleo y periodica en sección 4) --}}
    <div class="row">
        <div class="col-12 mb-3">
            <h6 class="text-secondary border-bottom pb-2">Ingresos Mensuales</h6>
        </div>
        
        <div class="col-md-4">
            <div class="form-group">
                <label for="ingresos_principales" class="form-label">Ingresos Principales (Q)</label>
                <input type="number" class="form-control" id="ingresos_principales" 
                       name="respuestas[ingresos_principales]" 
                       value="{{ $respuestas['ingresos_principales'] ?? '' }}"
                       step="0.01" min="0">
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="form-group">
                <label for="ingresos_adicionales" class="form-label">Ingresos Adicionales (Q)</label>
                <input type="number" class="form-control" id="ingresos_adicionales" 
                       name="respuestas[ingresos_adicionales]" 
                       value="{{ $respuestas['ingresos_adicionales'] ?? '' }}"
                       step="0.01" min="0">
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="form-group">
                <label for="ingresos_familiares" class="form-label">Ingresos Familiares (Q)</label>
                <input type="number" class="form-control" id="ingresos_familiares" 
                       name="respuestas[ingresos_familiares]" 
                       value="{{ $respuestas['ingresos_familiares'] ?? '' }}"
                       step="0.01" min="0">
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="form-group">
                <label for="total_ingresos" class="form-label"><strong>Total Ingresos (Q)</strong></label>
                <input type="number" class="form-control bg-light" id="total_ingresos" 
                       name="respuestas[total_ingresos]" 
                       value="{{ $respuestas['total_ingresos'] ?? '' }}"
                       step="0.01" min="0">
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-12 mb-3">
            <h6 class="text-secondary border-bottom pb-2">Gastos Mensuales</h6>
        </div>
        
        <div class="col-md-4">
            <div class="form-group">
                <label for="gastos_vivienda" class="form-label">Gastos Vivienda (Q)</label>
                <input type="number" class="form-control" id="gastos_vivienda" 
                       name="respuestas[gastos_vivienda]" 
                       value="{{ $respuestas['gastos_vivienda'] ?? '' }}"
                       step="0.01" min="0">
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="form-group">
                <label for="gastos_alimentacion" class="form-label">Gastos Alimentación (Q)</label>
                <input type="number" class="form-control" id="gastos_alimentacion" 
                       name="respuestas[gastos_alimentacion]" 
                       value="{{ $respuestas['gastos_alimentacion'] ?? '' }}"
                       step="0.01" min="0">
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="form-group">
                <label for="gastos_transporte" class="form-label">Gastos Transporte (Q)</label>
                <input type="number" class="form-control" id="gastos_transporte" 
                       name="respuestas[gastos_transporte]" 
                       value="{{ $respuestas['gastos_transporte'] ?? '' }}"
                       step="0.01" min="0">
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="form-group">
                <label for="gastos_educacion" class="form-label">Gastos Educación (Q)</label>
                <input type="number" class="form-control" id="gastos_educacion" 
                       name="respuestas[gastos_educacion]" 
                       value="{{ $respuestas['gastos_educacion'] ?? '' }}"
                       step="0.01" min="0">
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="form-group">
                <label for="gastos_salud" class="form-label">Gastos Salud (Q)</label>
                <input type="number" class="form-control" id="gastos_salud" 
                       name="respuestas[gastos_salud]" 
                       value="{{ $respuestas['gastos_salud'] ?? '' }}"
                       step="0.01" min="0">
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="form-group">
                <label for="gastos_otros" class="form-label">Otros Gastos (Q)</label>
                <input type="number" class="form-control" id="gastos_otros" 
                       name="respuestas[gastos_otros]" 
                       value="{{ $respuestas['gastos_otros'] ?? '' }}"
                       step="0.01" min="0">
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="form-group">
                <label for="total_gastos" class="form-label"><strong>Total Gastos (Q)</strong></label>
                <input type="number" class="form-control bg-light" id="total_gastos" 
                       name="respuestas[total_gastos]" 
                       value="{{ $respuestas['total_gastos'] ?? '' }}"
                       step="0.01" min="0">
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="form-group">
                <label for="balance_mensual" class="form-label"><strong>Balance Mensual (Q)</strong></label>
                <input type="number" class="form-control bg-light" id="balance_mensual" 
                       name="respuestas[balance_mensual]" 
                       value="{{ $respuestas['balance_mensual'] ?? '' }}"
                       step="0.01">
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-12 mb-3">
            <h6 class="text-secondary border-bottom pb-2">Situación Financiera</h6>
        </div>
        
        <div class="col-md-6">
            <div class="form-group">
                <label for="tiene_ahorros" class="form-label">¿Tiene Ahorros?</label>
                <select class="form-control" id="tiene_ahorros" name="respuestas[tiene_ahorros]">
                    <option value="">Seleccione...</option>
                    <option value="si" {{ ($respuestas['tiene_ahorros'] ?? '') == 'si' ? 'selected' : '' }}>Sí</option>
                    <option value="no" {{ ($respuestas['tiene_ahorros'] ?? '') == 'no' ? 'selected' : '' }}>No</option>
                </select>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="form-group">
                <label for="tiene_deudas" class="form-label">¿Tiene Deudas?</label>
                <select class="form-control" id="tiene_deudas" name="respuestas[tiene_deudas]">
                    <option value="">Seleccione...</option>
                    <option value="si" {{ ($respuestas['tiene_deudas'] ?? '') == 'si' ? 'selected' : '' }}>Sí</option>
                    <option value="no" {{ ($respuestas['tiene_deudas'] ?? '') == 'no' ? 'selected' : '' }}>No</option>
                </select>
            </div>
        </div>
        
        <div class="col-12">
            <div class="form-group">
                <label for="detalle_deudas" class="form-label">Detalle de Deudas</label>
                <textarea class="form-control" id="detalle_deudas" 
                          name="respuestas[detalle_deudas]" 
                          rows="3" placeholder="Describa sus deudas actuales...">{{ $respuestas['detalle_deudas'] ?? '' }}</textarea>
            </div>
        </div>
        
        <div class="col-12">
            <div class="form-group">
                <label for="observaciones_economicas" class="form-label">Observaciones Económicas</label>
                <textarea class="form-control" id="observaciones_economicas" 
                          name="respuestas[observaciones_economicas]" 
                          rows="3">{{ $respuestas['observaciones_economicas'] ?? '' }}</textarea>
            </div>
        </div>
    </div>
</div>
