{{-- Sección 3: Experiencia Laboral --}}
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
        <i class="bi bi-briefcase"></i> Experiencia Laboral
    </h5>
    
    {{-- Resumen de Experiencia --}}
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-primary">{{ $respuestas['anios_experiencia_total'] ?? '0' }}</h3>
                    <p class="mb-0">Años de experiencia total</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-info">{{ $respuestas['numero_empleos'] ?? '0' }}</h3>
                    <p class="mb-0">Empleos anteriores</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-success">{{ $respuestas['salario_actual'] ?? 'N/A' }}</h3>
                    <p class="mb-0">Salario actual/esperado</p>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Historial de Empleos --}}
    @if(isset($respuestas['historial_empleos']) && is_array($respuestas['historial_empleos']))
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Historial de Empleos</h6>
            </div>
            <div class="card-body">
                @foreach($respuestas['historial_empleos'] as $index => $empleo)
                    <div class="row mb-4 {{ $index > 0 ? 'border-top pt-4' : '' }}">
                        <div class="col-md-8">
                            <h6 class="text-primary">
                                {{ $empleo['puesto'] ?? 'Puesto no especificado' }}
                                @if($empleo['es_actual'] ?? false)
                                    <span class="badge bg-success ms-2">Actual</span>
                                @endif
                            </h6>
                            <p class="mb-1">
                                <i class="bi bi-building"></i> 
                                <strong>{{ $empleo['empresa'] ?? 'Empresa no especificada' }}</strong>
                            </p>
                            <p class="mb-1">
                                <i class="bi bi-calendar3"></i> 
                                {{ $empleo['fecha_inicio'] ?? 'N/A' }} - 
                                {{ ($empleo['es_actual'] ?? false) ? 'Presente' : ($empleo['fecha_fin'] ?? 'N/A') }}
                            </p>
                            <p class="mb-1">
                                <i class="bi bi-geo-alt"></i> 
                                {{ $empleo['ubicacion'] ?? 'Ubicación no especificada' }}
                            </p>
                            @if(isset($empleo['salario']))
                                <p class="mb-1">
                                    <i class="bi bi-currency-dollar"></i> 
                                    Q{{ number_format($empleo['salario'], 2) }}
                                </p>
                            @endif
                        </div>
                        
                        <div class="col-md-4">
                            @if(isset($empleo['tipo_contrato']))
                                <span class="badge bg-info mb-2">{{ ucfirst($empleo['tipo_contrato']) }}</span>
                            @endif
                            @if(isset($empleo['area_trabajo']))
                                <span class="badge bg-secondary mb-2">{{ $empleo['area_trabajo'] }}</span>
                            @endif
                            @if(isset($empleo['nivel_jerarquico']))
                                <span class="badge bg-warning mb-2">{{ ucfirst($empleo['nivel_jerarquico']) }}</span>
                            @endif
                        </div>
                        
                        @if(isset($empleo['responsabilidades']))
                            <div class="col-12 mt-2">
                                <strong>Responsabilidades principales:</strong>
                                <p class="text-muted">{{ $empleo['responsabilidades'] }}</p>
                            </div>
                        @endif
                        
                        @if(isset($empleo['logros']))
                            <div class="col-12 mt-2">
                                <strong>Logros destacados:</strong>
                                <p class="text-muted">{{ $empleo['logros'] }}</p>
                            </div>
                        @endif
                        
                        @if(isset($empleo['motivo_salida']) && !($empleo['es_actual'] ?? false))
                            <div class="col-12 mt-2">
                                <strong>Motivo de salida:</strong>
                                <p class="text-muted">{{ $empleo['motivo_salida'] }}</p>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="card">
            <div class="card-body text-center">
                <i class="bi bi-briefcase" style="font-size: 3rem;" class="text-muted mb-3"></i>
                <p class="text-muted">No se ha registrado experiencia laboral</p>
            </div>
        </div>
    @endif
    
    {{-- Referencias Laborales --}}
    @if(isset($respuestas['referencias_laborales']) && is_array($respuestas['referencias_laborales']))
        <div class="card mt-4">
            <div class="card-header">
                <h6 class="mb-0">Referencias Laborales</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($respuestas['referencias_laborales'] as $referencia)
                        <div class="col-md-6 mb-3">
                            <div class="border p-3 rounded">
                                <h6 class="text-primary">{{ $referencia['nombre'] ?? 'Nombre no proporcionado' }}</h6>
                                <p class="mb-1">
                                    <strong>Puesto:</strong> {{ $referencia['puesto'] ?? 'No especificado' }}
                                </p>
                                <p class="mb-1">
                                    <strong>Empresa:</strong> {{ $referencia['empresa'] ?? 'No especificada' }}
                                </p>
                                <p class="mb-1">
                                    <strong>Teléfono:</strong> {{ $referencia['telefono'] ?? 'No proporcionado' }}
                                </p>
                                <p class="mb-1">
                                    <strong>Email:</strong> {{ $referencia['email'] ?? 'No proporcionado' }}
                                </p>
                                <p class="mb-0">
                                    <strong>Relación:</strong> {{ $referencia['relacion'] ?? 'No especificada' }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
    
    {{-- Expectativas Laborales --}}
    @if(isset($respuestas['expectativas_laborales']))
        <div class="card mt-4">
            <div class="card-header">
                <h6 class="mb-0">Expectativas Laborales</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    @if(isset($respuestas['expectativas_laborales']['salario_esperado']))
                        <div class="col-md-4">
                            <strong>Salario esperado:</strong>
                            <p>Q{{ number_format($respuestas['expectativas_laborales']['salario_esperado'], 2) }}</p>
                        </div>
                    @endif
                    
                    @if(isset($respuestas['expectativas_laborales']['disponibilidad_horario']))
                        <div class="col-md-4">
                            <strong>Disponibilidad:</strong>
                            <p>{{ ucfirst($respuestas['expectativas_laborales']['disponibilidad_horario']) }}</p>
                        </div>
                    @endif
                    
                    @if(isset($respuestas['expectativas_laborales']['disponibilidad_viajes']))
                        <div class="col-md-4">
                            <strong>Disponibilidad para viajar:</strong>
                            <p>{{ $respuestas['expectativas_laborales']['disponibilidad_viajes'] ? 'Sí' : 'No' }}</p>
                        </div>
                    @endif
                </div>
                
                @if(isset($respuestas['expectativas_laborales']['objetivos_profesionales']))
                    <div class="mt-3">
                        <strong>Objetivos profesionales:</strong>
                        <p class="text-muted">{{ $respuestas['expectativas_laborales']['objetivos_profesionales'] }}</p>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>