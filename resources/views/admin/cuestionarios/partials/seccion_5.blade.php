{{-- Sección 5: Información Adicional --}}
<div class="section-content">
    @if($completada)
        <div class="alert alert-success mb-3">
            <i class="bi bi-check-circle-fill"></i> Sección completada
        </div>
    @else
        <div class="alert alert-warning mb-3">
            <i class="bi bi-exclamation-triangle"></i> Sección pendiente o incompleta
        </div>
    @endif
    
    <h5 class="section-title mb-4">
        <i class="bi bi-info-circle"></i> Información Adicional
    </h5>
    
    {{-- Disponibilidad y Condiciones Laborales --}}
    @if(isset($respuestas['disponibilidad_laboral']))
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Disponibilidad Laboral</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    @if(isset($respuestas['disponibilidad_laboral']['horario_preferido']))
                        <div class="col-md-4">
                            <strong>Horario preferido:</strong>
                            <p class="text-muted">{{ $respuestas['disponibilidad_laboral']['horario_preferido'] }}</p>
                        </div>
                    @endif
                    
                    @if(isset($respuestas['disponibilidad_laboral']['disponibilidad_inmediata']))
                        <div class="col-md-4">
                            <strong>Disponibilidad inmediata:</strong>
                            <p class="text-muted">
                                {{ $respuestas['disponibilidad_laboral']['disponibilidad_inmediata'] ? 'Sí' : 'No' }}
                            </p>
                        </div>
                    @endif
                    
                    @if(isset($respuestas['disponibilidad_laboral']['fecha_inicio_posible']))
                        <div class="col-md-4">
                            <strong>Fecha posible de inicio:</strong>
                            <p class="text-muted">{{ $respuestas['disponibilidad_laboral']['fecha_inicio_posible'] }}</p>
                        </div>
                    @endif
                    
                    @if(isset($respuestas['disponibilidad_laboral']['disponibilidad_fines_semana']))
                        <div class="col-md-4">
                            <strong>Disponibilidad fines de semana:</strong>
                            <p class="text-muted">
                                {{ $respuestas['disponibilidad_laboral']['disponibilidad_fines_semana'] ? 'Sí' : 'No' }}
                            </p>
                        </div>
                    @endif
                    
                    @if(isset($respuestas['disponibilidad_laboral']['disponibilidad_horas_extra']))
                        <div class="col-md-4">
                            <strong>Disponibilidad horas extra:</strong>
                            <p class="text-muted">
                                {{ $respuestas['disponibilidad_laboral']['disponibilidad_horas_extra'] ? 'Sí' : 'No' }}
                            </p>
                        </div>
                    @endif
                    
                    @if(isset($respuestas['disponibilidad_laboral']['disponibilidad_viajes']))
                        <div class="col-md-4">
                            <strong>Disponibilidad para viajar:</strong>
                            <p class="text-muted">
                                {{ $respuestas['disponibilidad_laboral']['disponibilidad_viajes'] ? 'Sí' : 'No' }}
                            </p>
                        </div>
                    @endif
                </div>
                
                @if(isset($respuestas['disponibilidad_laboral']['restricciones_horarios']))
                    <div class="mt-3">
                        <strong>Restricciones de horario:</strong>
                        <p class="text-muted">{{ $respuestas['disponibilidad_laboral']['restricciones_horarios'] }}</p>
                    </div>
                @endif
            </div>
        </div>
    @endif
    
    {{-- Situación Familiar y Personal --}}
    @if(isset($respuestas['situacion_familiar']))
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Situación Familiar</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    @if(isset($respuestas['situacion_familiar']['personas_dependientes']))
                        <div class="col-md-4">
                            <strong>Personas dependientes:</strong>
                            <p class="text-muted">{{ $respuestas['situacion_familiar']['personas_dependientes'] }}</p>
                        </div>
                    @endif
                    
                    @if(isset($respuestas['situacion_familiar']['cuidado_menores']))
                        <div class="col-md-4">
                            <strong>Cuidado de menores:</strong>
                            <p class="text-muted">
                                {{ $respuestas['situacion_familiar']['cuidado_menores'] ? 'Sí' : 'No' }}
                            </p>
                        </div>
                    @endif
                    
                    @if(isset($respuestas['situacion_familiar']['apoyo_familiar']))
                        <div class="col-md-4">
                            <strong>Apoyo familiar disponible:</strong>
                            <p class="text-muted">
                                {{ $respuestas['situacion_familiar']['apoyo_familiar'] ? 'Sí' : 'No' }}
                            </p>
                        </div>
                    @endif
                </div>
                
                @if(isset($respuestas['situacion_familiar']['observaciones']))
                    <div class="mt-3">
                        <strong>Observaciones:</strong>
                        <p class="text-muted">{{ $respuestas['situacion_familiar']['observaciones'] }}</p>
                    </div>
                @endif
            </div>
        </div>
    @endif
    
    {{-- Salud y Condiciones Físicas --}}
    @if(isset($respuestas['condiciones_salud']))
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Condiciones de Salud</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    @if(isset($respuestas['condiciones_salud']['estado_salud_general']))
                        <div class="col-md-6">
                            <strong>Estado de salud general:</strong>
                            <p class="text-muted">{{ ucfirst($respuestas['condiciones_salud']['estado_salud_general']) }}</p>
                        </div>
                    @endif
                    
                    @if(isset($respuestas['condiciones_salud']['limitaciones_fisicas']))
                        <div class="col-md-6">
                            <strong>Limitaciones físicas:</strong>
                            <p class="text-muted">
                                {{ $respuestas['condiciones_salud']['limitaciones_fisicas'] ? 'Sí' : 'No' }}
                            </p>
                        </div>
                    @endif
                </div>
                
                @if(isset($respuestas['condiciones_salud']['detalles_limitaciones']))
                    <div class="mt-3">
                        <strong>Detalles de limitaciones:</strong>
                        <p class="text-muted">{{ $respuestas['condiciones_salud']['detalles_limitaciones'] }}</p>
                    </div>
                @endif
                
                @if(isset($respuestas['condiciones_salud']['medicamentos_regulares']))
                    <div class="mt-3">
                        <strong>Medicamentos regulares:</strong>
                        <p class="text-muted">
                            {{ $respuestas['condiciones_salud']['medicamentos_regulares'] ? 'Sí' : 'No' }}
                        </p>
                    </div>
                @endif
            </div>
        </div>
    @endif
    
    {{-- Intereses y Actividades --}}
    @if(isset($respuestas['intereses_actividades']))
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Intereses y Actividades</h6>
            </div>
            <div class="card-body">
                @if(is_array($respuestas['intereses_actividades']))
                    <div class="row">
                        @foreach($respuestas['intereses_actividades'] as $categoria => $actividades)
                            <div class="col-md-6 mb-3">
                                <h6 class="text-primary">{{ ucfirst(str_replace('_', ' ', $categoria)) }}</h6>
                                @if(is_array($actividades))
                                    <div class="d-flex flex-wrap">
                                        @foreach($actividades as $actividad)
                                            <span class="badge bg-light text-dark me-1 mb-1">{{ $actividad }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-muted">{{ $actividades }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <p>{{ $respuestas['intereses_actividades'] }}</p>
                @endif
            </div>
        </div>
    @endif
    
    {{-- Información de Contacto de Emergencia --}}
    @if(isset($respuestas['contacto_emergencia']))
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Contacto de Emergencia</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    @if(isset($respuestas['contacto_emergencia']['nombre']))
                        <div class="col-md-4">
                            <strong>Nombre:</strong>
                            <p class="text-muted">{{ $respuestas['contacto_emergencia']['nombre'] }}</p>
                        </div>
                    @endif
                    
                    @if(isset($respuestas['contacto_emergencia']['relacion']))
                        <div class="col-md-4">
                            <strong>Relación:</strong>
                            <p class="text-muted">{{ $respuestas['contacto_emergencia']['relacion'] }}</p>
                        </div>
                    @endif
                    
                    @if(isset($respuestas['contacto_emergencia']['telefono']))
                        <div class="col-md-4">
                            <strong>Teléfono:</strong>
                            <p class="text-muted">{{ $respuestas['contacto_emergencia']['telefono'] }}</p>
                        </div>
                    @endif
                    
                    @if(isset($respuestas['contacto_emergencia']['direccion']))
                        <div class="col-md-12 mt-2">
                            <strong>Dirección:</strong>
                            <p class="text-muted">{{ $respuestas['contacto_emergencia']['direccion'] }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
    
    {{-- Motivaciones y Expectativas --}}
    @if(isset($respuestas['motivaciones_expectativas']))
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Motivaciones y Expectativas</h6>
            </div>
            <div class="card-body">
                @if(isset($respuestas['motivaciones_expectativas']['motivacion_aplicar']))
                    <div class="mb-3">
                        <strong>¿Por qué quiere trabajar con nosotros?</strong>
                        <p class="text-muted">{{ $respuestas['motivaciones_expectativas']['motivacion_aplicar'] }}</p>
                    </div>
                @endif
                
                @if(isset($respuestas['motivaciones_expectativas']['expectativas_puesto']))
                    <div class="mb-3">
                        <strong>Expectativas del puesto:</strong>
                        <p class="text-muted">{{ $respuestas['motivaciones_expectativas']['expectativas_puesto'] }}</p>
                    </div>
                @endif
                
                @if(isset($respuestas['motivaciones_expectativas']['metas_profesionales']))
                    <div class="mb-3">
                        <strong>Metas profesionales a 5 años:</strong>
                        <p class="text-muted">{{ $respuestas['motivaciones_expectativas']['metas_profesionales'] }}</p>
                    </div>
                @endif
                
                @if(isset($respuestas['motivaciones_expectativas']['razon_cambio_trabajo']))
                    <div class="mb-3">
                        <strong>Razón para cambiar de trabajo:</strong>
                        <p class="text-muted">{{ $respuestas['motivaciones_expectativas']['razon_cambio_trabajo'] }}</p>
                    </div>
                @endif
            </div>
        </div>
    @endif
    
    {{-- Información Adicional o Comentarios --}}
    @if(isset($respuestas['informacion_adicional']))
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Información Adicional</h6>
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $respuestas['informacion_adicional'] }}</p>
            </div>
        </div>
    @endif
    
    {{-- Consentimientos y Autorizaciones --}}
    @if(isset($respuestas['consentimientos']))
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Consentimientos y Autorizaciones</h6>
            </div>
            <div class="card-body">
                @if(is_array($respuestas['consentimientos']))
                    <div class="row">
                        @foreach($respuestas['consentimientos'] as $consentimiento => $autorizado)
                            <div class="col-md-6 mb-2">
                                <div class="d-flex align-items-center">
                                    @if($autorizado)
                                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                                    @else
                                        <i class="bi bi-x-circle text-danger me-2"></i>
                                    @endif
                                    <span>{{ ucfirst(str_replace('_', ' ', $consentimiento)) }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="mb-0">{{ $respuestas['consentimientos'] }}</p>
                @endif
            </div>
        </div>
    @endif
</div>