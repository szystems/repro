{{-- Sección 4: Competencias y Habilidades --}}
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
        <i class="bi bi-star"></i> Competencias y Habilidades
    </h5>
    
    {{-- Habilidades Técnicas --}}
    @if(isset($respuestas['habilidades_tecnicas']) && is_array($respuestas['habilidades_tecnicas']))
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Habilidades Técnicas</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($respuestas['habilidades_tecnicas'] as $categoria => $habilidades)
                        <div class="col-md-6 mb-3">
                            <h6 class="text-primary">{{ ucfirst(str_replace('_', ' ', $categoria)) }}</h6>
                            @if(is_array($habilidades))
                                @foreach($habilidades as $habilidad => $nivel)
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span>{{ ucfirst($habilidad) }}</span>
                                        <div class="d-flex align-items-center">
                                            <div class="progress me-2" style="width: 100px; height: 20px;">
                                                <div class="progress-bar 
                                                    @if($nivel >= 80) bg-success
                                                    @elseif($nivel >= 60) bg-info
                                                    @elseif($nivel >= 40) bg-warning
                                                    @else bg-danger
                                                    @endif
                                                " style="width: {{ $nivel }}%"></div>
                                            </div>
                                            <small class="text-muted">{{ $nivel }}%</small>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <p class="text-muted">{{ $habilidades }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
    
    {{-- Habilidades Blandas --}}
    @if(isset($respuestas['habilidades_blandas']) && is_array($respuestas['habilidades_blandas']))
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Habilidades Blandas</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($respuestas['habilidades_blandas'] as $habilidad => $descripcion)
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-start">
                                <i class="bi bi-check-circle-fill text-success me-2 mt-1"></i>
                                <div>
                                    <strong>{{ ucfirst(str_replace('_', ' ', $habilidad)) }}</strong>
                                    @if(is_string($descripcion) && !empty($descripcion))
                                        <p class="text-muted mb-0 small">{{ $descripcion }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
    
    {{-- Test de Personalidad --}}
    @if(isset($respuestas['test_personalidad']))
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Perfil de Personalidad</h6>
            </div>
            <div class="card-body">
                @if(is_array($respuestas['test_personalidad']))
                    <div class="row">
                        @foreach($respuestas['test_personalidad'] as $dimension => $valor)
                            <div class="col-md-6 mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <strong>{{ ucfirst(str_replace('_', ' ', $dimension)) }}</strong>
                                    <span class="badge 
                                        @if($valor >= 80) bg-success
                                        @elseif($valor >= 60) bg-info
                                        @elseif($valor >= 40) bg-warning
                                        @else bg-danger
                                        @endif
                                    ">{{ $valor }}%</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar 
                                        @if($valor >= 80) bg-success
                                        @elseif($valor >= 60) bg-info
                                        @elseif($valor >= 40) bg-warning
                                        @else bg-danger
                                        @endif
                                    " style="width: {{ $valor }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p>{{ $respuestas['test_personalidad'] }}</p>
                @endif
            </div>
        </div>
    @endif
    
    {{-- Competencias de Liderazgo --}}
    @if(isset($respuestas['competencias_liderazgo']))
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Competencias de Liderazgo</h6>
            </div>
            <div class="card-body">
                @if(is_array($respuestas['competencias_liderazgo']))
                    <div class="row">
                        @foreach($respuestas['competencias_liderazgo'] as $competencia => $nivel)
                            <div class="col-md-4 mb-3">
                                <div class="text-center">
                                    <div class="position-relative d-inline-block">
                                        <svg width="80" height="80" class="circular-progress">
                                            <circle cx="40" cy="40" r="35" 
                                                    fill="none" 
                                                    stroke="#e9ecef" 
                                                    stroke-width="5"/>
                                            <circle cx="40" cy="40" r="35" 
                                                    fill="none" 
                                                    stroke="
                                                        @if($nivel >= 80) #28a745
                                                        @elseif($nivel >= 60) #17a2b8
                                                        @elseif($nivel >= 40) #ffc107
                                                        @else #dc3545
                                                        @endif
                                                    " 
                                                    stroke-width="5"
                                                    stroke-dasharray="{{ (2 * 3.14159 * 35) }}"
                                                    stroke-dashoffset="{{ (2 * 3.14159 * 35) * (1 - $nivel/100) }}"
                                                    transform="rotate(-90 40 40)"/>
                                        </svg>
                                        <div class="position-absolute top-50 start-50 translate-middle">
                                            <strong>{{ $nivel }}%</strong>
                                        </div>
                                    </div>
                                    <p class="mt-2 mb-0 small">{{ ucfirst(str_replace('_', ' ', $competencia)) }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p>{{ $respuestas['competencias_liderazgo'] }}</p>
                @endif
            </div>
        </div>
    @endif
    
    {{-- Fortalezas y Áreas de Mejora --}}
    <div class="row">
        @if(isset($respuestas['fortalezas_principales']))
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0">
                            <i class="bi bi-hand-thumbs-up"></i> Fortalezas Principales
                        </h6>
                    </div>
                    <div class="card-body">
                        @if(is_array($respuestas['fortalezas_principales']))
                            <ul class="list-unstyled">
                                @foreach($respuestas['fortalezas_principales'] as $fortaleza)
                                    <li class="mb-2">
                                        <i class="bi bi-check text-success me-2"></i>
                                        {{ $fortaleza }}
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="mb-0">{{ $respuestas['fortalezas_principales'] }}</p>
                        @endif
                    </div>
                </div>
            </div>
        @endif
        
        @if(isset($respuestas['areas_mejora']))
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0">
                            <i class="bi bi-graph-up"></i> Áreas de Mejora
                        </h6>
                    </div>
                    <div class="card-body">
                        @if(is_array($respuestas['areas_mejora']))
                            <ul class="list-unstyled">
                                @foreach($respuestas['areas_mejora'] as $area)
                                    <li class="mb-2">
                                        <i class="bi bi-arrow-up text-warning me-2"></i>
                                        {{ $area }}
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="mb-0">{{ $respuestas['areas_mejora'] }}</p>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
    
    {{-- Autoevaluación General --}}
    @if(isset($respuestas['autoevaluacion_general']))
        <div class="card mt-4">
            <div class="card-header">
                <h6 class="mb-0">Autoevaluación General</h6>
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $respuestas['autoevaluacion_general'] }}</p>
            </div>
        </div>
    @endif
</div>

@push('styles')
<style>
.circular-progress {
    transform: rotate(-90deg);
}

.progress {
    border-radius: 10px;
}

.progress-bar {
    border-radius: 10px;
}

.section-content .card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.section-content .card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid rgba(0, 0, 0, 0.125);
}
</style>
@endpush