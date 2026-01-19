{{-- Partial para editar Sección 5: Información Adicional --}}
<div class="section-edit-content">
    <h6 class="text-primary mb-3">
        <i class="bi bi-info-circle"></i> Información Adicional
    </h6>
    
    {{-- Disponibilidad Laboral --}}
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0">Disponibilidad Laboral</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="horario_preferido" class="form-label">Horario Preferido</label>
                        <select class="form-control" 
                                id="horario_preferido" 
                                name="seccion_{{ $seccion }}[disponibilidad_laboral][horario_preferido]">
                            <option value="">Seleccione...</option>
                            <option value="tiempo_completo" 
                                {{ old('seccion_' . $seccion . '.disponibilidad_laboral.horario_preferido', $respuestas['disponibilidad_laboral']['horario_preferido'] ?? '') == 'tiempo_completo' ? 'selected' : '' }}>
                                Tiempo Completo
                            </option>
                            <option value="medio_tiempo" 
                                {{ old('seccion_' . $seccion . '.disponibilidad_laboral.horario_preferido', $respuestas['disponibilidad_laboral']['horario_preferido'] ?? '') == 'medio_tiempo' ? 'selected' : '' }}>
                                Medio Tiempo
                            </option>
                            <option value="flexible" 
                                {{ old('seccion_' . $seccion . '.disponibilidad_laboral.horario_preferido', $respuestas['disponibilidad_laboral']['horario_preferido'] ?? '') == 'flexible' ? 'selected' : '' }}>
                                Flexible
                            </option>
                        </select>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="fecha_inicio_posible" class="form-label">Fecha Posible de Inicio</label>
                        <input type="date" 
                               class="form-control" 
                               id="fecha_inicio_posible" 
                               name="seccion_{{ $seccion }}[disponibilidad_laboral][fecha_inicio_posible]" 
                               value="{{ old('seccion_' . $seccion . '.disponibilidad_laboral.fecha_inicio_posible', $respuestas['disponibilidad_laboral']['fecha_inicio_posible'] ?? '') }}">
                    </div>
                </div>
                
                <div class="col-12">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="disponibilidad_inmediata" 
                                       name="seccion_{{ $seccion }}[disponibilidad_laboral][disponibilidad_inmediata]" 
                                       value="1"
                                       {{ old('seccion_' . $seccion . '.disponibilidad_laboral.disponibilidad_inmediata', $respuestas['disponibilidad_laboral']['disponibilidad_inmediata'] ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="disponibilidad_inmediata">
                                    Disponibilidad inmediata
                                </label>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="disponibilidad_fines_semana" 
                                       name="seccion_{{ $seccion }}[disponibilidad_laboral][disponibilidad_fines_semana]" 
                                       value="1"
                                       {{ old('seccion_' . $seccion . '.disponibilidad_laboral.disponibilidad_fines_semana', $respuestas['disponibilidad_laboral']['disponibilidad_fines_semana'] ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="disponibilidad_fines_semana">
                                    Fines de semana
                                </label>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="disponibilidad_horas_extra" 
                                       name="seccion_{{ $seccion }}[disponibilidad_laboral][disponibilidad_horas_extra]" 
                                       value="1"
                                       {{ old('seccion_' . $seccion . '.disponibilidad_laboral.disponibilidad_horas_extra', $respuestas['disponibilidad_laboral']['disponibilidad_horas_extra'] ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="disponibilidad_horas_extra">
                                    Horas extra
                                </label>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="disponibilidad_viajes" 
                                       name="seccion_{{ $seccion }}[disponibilidad_laboral][disponibilidad_viajes]" 
                                       value="1"
                                       {{ old('seccion_' . $seccion . '.disponibilidad_laboral.disponibilidad_viajes', $respuestas['disponibilidad_laboral']['disponibilidad_viajes'] ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="disponibilidad_viajes">
                                    Disponible para viajar
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-12">
                    <div class="form-group">
                        <label for="restricciones_horarios" class="form-label">Restricciones de Horario</label>
                        <textarea class="form-control" 
                                  id="restricciones_horarios" 
                                  name="seccion_{{ $seccion }}[disponibilidad_laboral][restricciones_horarios]" 
                                  rows="2"
                                  placeholder="Describa cualquier restricción de horario...">{{ old('seccion_' . $seccion . '.disponibilidad_laboral.restricciones_horarios', $respuestas['disponibilidad_laboral']['restricciones_horarios'] ?? '') }}</textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Contacto de Emergencia --}}
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0">Contacto de Emergencia</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="contacto_emergencia_nombre" class="form-label">Nombre</label>
                        <input type="text" 
                               class="form-control" 
                               id="contacto_emergencia_nombre" 
                               name="seccion_{{ $seccion }}[contacto_emergencia][nombre]" 
                               value="{{ old('seccion_' . $seccion . '.contacto_emergencia.nombre', $respuestas['contacto_emergencia']['nombre'] ?? '') }}">
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="contacto_emergencia_relacion" class="form-label">Relación</label>
                        <input type="text" 
                               class="form-control" 
                               id="contacto_emergencia_relacion" 
                               name="seccion_{{ $seccion }}[contacto_emergencia][relacion]" 
                               value="{{ old('seccion_' . $seccion . '.contacto_emergencia.relacion', $respuestas['contacto_emergencia']['relacion'] ?? '') }}"
                               placeholder="Ej: Madre, Esposo/a, Hermano/a">
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="contacto_emergencia_telefono" class="form-label">Teléfono</label>
                        <input type="tel" 
                               class="form-control" 
                               id="contacto_emergencia_telefono" 
                               name="seccion_{{ $seccion }}[contacto_emergencia][telefono]" 
                               value="{{ old('seccion_' . $seccion . '.contacto_emergencia.telefono', $respuestas['contacto_emergencia']['telefono'] ?? '') }}">
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="contacto_emergencia_direccion" class="form-label">Dirección</label>
                        <input type="text" 
                               class="form-control" 
                               id="contacto_emergencia_direccion" 
                               name="seccion_{{ $seccion }}[contacto_emergencia][direccion]" 
                               value="{{ old('seccion_' . $seccion . '.contacto_emergencia.direccion', $respuestas['contacto_emergencia']['direccion'] ?? '') }}">
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Motivaciones y Expectativas --}}
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0">Motivaciones y Expectativas</h6>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label for="motivacion_aplicar" class="form-label">¿Por qué quiere trabajar con nosotros?</label>
                <textarea class="form-control" 
                          id="motivacion_aplicar" 
                          name="seccion_{{ $seccion }}[motivaciones_expectativas][motivacion_aplicar]" 
                          rows="3">{{ old('seccion_' . $seccion . '.motivaciones_expectativas.motivacion_aplicar', $respuestas['motivaciones_expectativas']['motivacion_aplicar'] ?? '') }}</textarea>
            </div>
            
            <div class="form-group">
                <label for="expectativas_puesto" class="form-label">Expectativas del Puesto</label>
                <textarea class="form-control" 
                          id="expectativas_puesto" 
                          name="seccion_{{ $seccion }}[motivaciones_expectativas][expectativas_puesto]" 
                          rows="3">{{ old('seccion_' . $seccion . '.motivaciones_expectativas.expectativas_puesto', $respuestas['motivaciones_expectativas']['expectativas_puesto'] ?? '') }}</textarea>
            </div>
            
            <div class="form-group">
                <label for="metas_profesionales" class="form-label">Metas Profesionales a 5 años</label>
                <textarea class="form-control" 
                          id="metas_profesionales" 
                          name="seccion_{{ $seccion }}[motivaciones_expectativas][metas_profesionales]" 
                          rows="3">{{ old('seccion_' . $seccion . '.motivaciones_expectativas.metas_profesionales', $respuestas['motivaciones_expectativas']['metas_profesionales'] ?? '') }}</textarea>
            </div>
        </div>
    </div>
    
    {{-- Información Adicional --}}
    <div class="form-group">
        <label for="informacion_adicional" class="form-label">Información Adicional</label>
        <textarea class="form-control" 
                  id="informacion_adicional" 
                  name="seccion_{{ $seccion }}[informacion_adicional]" 
                  rows="4"
                  placeholder="Cualquier información adicional relevante...">{{ old('seccion_' . $seccion . '.informacion_adicional', $respuestas['informacion_adicional'] ?? '') }}</textarea>
    </div>
    
    {{-- Consentimientos --}}
    <div class="mt-4">
        <h6 class="text-secondary mb-3">Consentimientos y Autorizaciones</h6>
        <div class="row">
            <div class="col-md-6">
                <div class="form-check">
                    <input class="form-check-input" 
                           type="checkbox" 
                           id="consentimiento_datos" 
                           name="seccion_{{ $seccion }}[consentimientos][tratamiento_datos]" 
                           value="1"
                           {{ old('seccion_' . $seccion . '.consentimientos.tratamiento_datos', $respuestas['consentimientos']['tratamiento_datos'] ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="consentimiento_datos">
                        Autoriza tratamiento de datos personales
                    </label>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="form-check">
                    <input class="form-check-input" 
                           type="checkbox" 
                           id="consentimiento_referencias" 
                           name="seccion_{{ $seccion }}[consentimientos][verificacion_referencias]" 
                           value="1"
                           {{ old('seccion_' . $seccion . '.consentimientos.verificacion_referencias', $respuestas['consentimientos']['verificacion_referencias'] ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="consentimiento_referencias">
                        Autoriza verificación de referencias
                    </label>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="form-check">
                    <input class="form-check-input" 
                           type="checkbox" 
                           id="consentimiento_examenes" 
                           name="seccion_{{ $seccion }}[consentimientos][examenes_medicos]" 
                           value="1"
                           {{ old('seccion_' . $seccion . '.consentimientos.examenes_medicos', $respuestas['consentimientos']['examenes_medicos'] ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="consentimiento_examenes">
                        Acepta exámenes médicos si es requerido
                    </label>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="form-check">
                    <input class="form-check-input" 
                           type="checkbox" 
                           id="consentimiento_comunicaciones" 
                           name="seccion_{{ $seccion }}[consentimientos][comunicaciones]" 
                           value="1"
                           {{ old('seccion_' . $seccion . '.consentimientos.comunicaciones', $respuestas['consentimientos']['comunicaciones'] ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="consentimiento_comunicaciones">
                        Acepta recibir comunicaciones sobre el proceso
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>