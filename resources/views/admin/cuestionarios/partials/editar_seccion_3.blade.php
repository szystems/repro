{{-- Partial para editar Sección 3 --}}
@php
    $tipoFormulario = $tipoFormulario ?? 'preempleo';
    $nombreSeccion = $nombreSeccion ?? 'Historial Laboral';
@endphp
<div class="section-edit-content">
    <h6 class="text-primary mb-3">
        <i class="bi bi-briefcase"></i> {{ $nombreSeccion }}
    </h6>
    
    @if(in_array($tipoFormulario, ['periodica']))
        {{-- Campos para tipo PERIÓDICA - Situación Laboral Actual --}}
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="situacion_laboral_actual" class="form-label">Situación Laboral Actual</label>
                    <select class="form-control" id="situacion_laboral_actual" name="respuestas[situacion_laboral_actual]">
                        <option value="">Seleccione...</option>
                        <option value="empleado" {{ ($respuestas['situacion_laboral_actual'] ?? '') == 'empleado' ? 'selected' : '' }}>Empleado</option>
                        <option value="desempleado" {{ ($respuestas['situacion_laboral_actual'] ?? '') == 'desempleado' ? 'selected' : '' }}>Desempleado</option>
                        <option value="independiente" {{ ($respuestas['situacion_laboral_actual'] ?? '') == 'independiente' ? 'selected' : '' }}>Independiente</option>
                        <option value="estudiante" {{ ($respuestas['situacion_laboral_actual'] ?? '') == 'estudiante' ? 'selected' : '' }}>Estudiante</option>
                        <option value="jubilado" {{ ($respuestas['situacion_laboral_actual'] ?? '') == 'jubilado' ? 'selected' : '' }}>Jubilado</option>
                    </select>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="form-group">
                    <label for="empresa_actual" class="form-label">Empresa Actual</label>
                    <input type="text" class="form-control" id="empresa_actual" 
                           name="respuestas[empresa_actual]" 
                           value="{{ $respuestas['empresa_actual'] ?? '' }}">
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="form-group">
                    <label for="puesto_actual" class="form-label">Puesto Actual</label>
                    <input type="text" class="form-control" id="puesto_actual" 
                           name="respuestas[puesto_actual]" 
                           value="{{ $respuestas['puesto_actual'] ?? '' }}">
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="form-group">
                    <label for="fecha_inicio_actual" class="form-label">Fecha de Inicio</label>
                    <input type="date" class="form-control" id="fecha_inicio_actual" 
                           name="respuestas[fecha_inicio_actual]" 
                           value="{{ $respuestas['fecha_inicio_actual'] ?? '' }}">
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="form-group">
                    <label for="salario_actual" class="form-label">Salario Actual (Q)</label>
                    <input type="number" class="form-control" id="salario_actual" 
                           name="respuestas[salario_actual]" 
                           value="{{ $respuestas['salario_actual'] ?? '' }}"
                           step="0.01" min="0">
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="form-group">
                    <label for="jefe_inmediato" class="form-label">Jefe Inmediato</label>
                    <input type="text" class="form-control" id="jefe_inmediato" 
                           name="respuestas[jefe_inmediato]" 
                           value="{{ $respuestas['jefe_inmediato'] ?? '' }}">
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="form-group">
                    <label for="anos_experiencia_laboral" class="form-label">Años de Experiencia Laboral</label>
                    <input type="number" class="form-control" id="anos_experiencia_laboral" 
                           name="respuestas[anos_experiencia_laboral]" 
                           value="{{ $respuestas['anos_experiencia_laboral'] ?? '' }}"
                           min="0" max="50">
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="form-group">
                    <label for="motivo_busqueda" class="form-label">Motivo de Búsqueda de Empleo</label>
                    <select class="form-control" id="motivo_busqueda" name="respuestas[motivo_busqueda]">
                        <option value="">Seleccione...</option>
                        <option value="mejora_salarial" {{ ($respuestas['motivo_busqueda'] ?? '') == 'mejora_salarial' ? 'selected' : '' }}>Mejora Salarial</option>
                        <option value="crecimiento_profesional" {{ ($respuestas['motivo_busqueda'] ?? '') == 'crecimiento_profesional' ? 'selected' : '' }}>Crecimiento Profesional</option>
                        <option value="cambio_de_area" {{ ($respuestas['motivo_busqueda'] ?? '') == 'cambio_de_area' ? 'selected' : '' }}>Cambio de Área</option>
                        <option value="desempleo" {{ ($respuestas['motivo_busqueda'] ?? '') == 'desempleo' ? 'selected' : '' }}>Desempleo</option>
                        <option value="otro" {{ ($respuestas['motivo_busqueda'] ?? '') == 'otro' ? 'selected' : '' }}>Otro</option>
                    </select>
                </div>
            </div>
            
            <div class="col-12">
                <div class="form-group">
                    <label for="empleos_anteriores" class="form-label">Empleos Anteriores</label>
                    <textarea class="form-control" id="empleos_anteriores" 
                              name="respuestas[empleos_anteriores]" 
                              rows="4" placeholder="Liste sus empleos anteriores...">{{ $respuestas['empleos_anteriores'] ?? '' }}</textarea>
                </div>
            </div>
        </div>
    @else
        {{-- Campos para tipo PREEMPLEO y otros - Historial Laboral estándar --}}
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="anos_experiencia_laboral" class="form-label">Años de Experiencia Total</label>
                    <input type="number" class="form-control" id="anos_experiencia_laboral" 
                           name="respuestas[anos_experiencia_laboral]" 
                           value="{{ $respuestas['anos_experiencia_laboral'] ?? $respuestas['anios_experiencia_total'] ?? '' }}"
                           min="0" max="50">
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="form-group">
                    <label for="situacion_laboral_actual" class="form-label">Situación Laboral</label>
                    <select class="form-control" id="situacion_laboral_actual" name="respuestas[situacion_laboral_actual]">
                        <option value="">Seleccione...</option>
                        <option value="empleado" {{ ($respuestas['situacion_laboral_actual'] ?? '') == 'empleado' ? 'selected' : '' }}>Empleado</option>
                        <option value="desempleado" {{ ($respuestas['situacion_laboral_actual'] ?? '') == 'desempleado' ? 'selected' : '' }}>Desempleado</option>
                        <option value="independiente" {{ ($respuestas['situacion_laboral_actual'] ?? '') == 'independiente' ? 'selected' : '' }}>Independiente</option>
                    </select>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="form-group">
                    <label for="salario_actual" class="form-label">Salario Actual/Esperado (Q)</label>
                    <input type="number" class="form-control" id="salario_actual" 
                           name="respuestas[salario_actual]" 
                           value="{{ $respuestas['salario_actual'] ?? '' }}"
                           step="0.01" min="0">
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="form-group">
                    <label for="empresa_actual" class="form-label">Empresa Actual</label>
                    <input type="text" class="form-control" id="empresa_actual" 
                           name="respuestas[empresa_actual]" 
                           value="{{ $respuestas['empresa_actual'] ?? '' }}">
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="form-group">
                    <label for="puesto_actual" class="form-label">Puesto Actual</label>
                    <input type="text" class="form-control" id="puesto_actual" 
                           name="respuestas[puesto_actual]" 
                           value="{{ $respuestas['puesto_actual'] ?? '' }}">
                </div>
            </div>
            
            <div class="col-12">
                <div class="form-group">
                    <label for="empleos_anteriores" class="form-label">Historial de Empleos Anteriores</label>
                    <textarea class="form-control" id="empleos_anteriores" 
                              name="respuestas[empleos_anteriores]" 
                              rows="4" placeholder="Liste sus empleos anteriores con fechas y responsabilidades...">{{ $respuestas['empleos_anteriores'] ?? '' }}</textarea>
                </div>
            </div>
        </div>
    @endif
</div>
