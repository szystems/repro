{{-- Sección 2: Educación y Formación --}}
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
        <i class="bi bi-mortarboard"></i> Educación y Formación Académica
    </h5>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Nivel Educativo</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td class="fw-bold">Nivel más alto completado:</td>
                            <td>{{ ucfirst($respuestas['nivel_educativo'] ?? 'No especificado') }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Especialidad/Carrera:</td>
                            <td>{{ $respuestas['especialidad'] ?? 'No especificada' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Institución:</td>
                            <td>{{ $respuestas['institucion_educativa'] ?? 'No especificada' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Año de graduación:</td>
                            <td>{{ $respuestas['anio_graduacion'] ?? 'No especificado' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">¿Título en trámite?:</td>
                            <td>
                                @if(isset($respuestas['titulo_en_tramite']))
                                    {{ $respuestas['titulo_en_tramite'] ? 'Sí' : 'No' }}
                                @else
                                    No especificado
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Idiomas</h6>
                </div>
                <div class="card-body">
                    @if(isset($respuestas['idiomas']) && is_array($respuestas['idiomas']))
                        @foreach($respuestas['idiomas'] as $idioma => $nivel)
                            <div class="row mb-2">
                                <div class="col-6">
                                    <strong>{{ ucfirst($idioma) }}:</strong>
                                </div>
                                <div class="col-6">
                                    <span class="badge 
                                        @switch($nivel)
                                            @case('basico') bg-warning @break
                                            @case('intermedio') bg-info @break
                                            @case('avanzado') bg-success @break
                                            @case('nativo') bg-primary @break
                                            @default bg-secondary
                                        @endswitch
                                    ">
                                        {{ ucfirst($nivel) }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted">No se han registrado idiomas</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    {{-- Cursos y Certificaciones --}}
    <div class="row mt-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Cursos y Certificaciones</h6>
                </div>
                <div class="card-body">
                    @if(isset($respuestas['cursos_certificaciones']) && is_array($respuestas['cursos_certificaciones']))
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Curso/Certificación</th>
                                        <th>Institución</th>
                                        <th>Año</th>
                                        <th>Duración</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($respuestas['cursos_certificaciones'] as $curso)
                                        <tr>
                                            <td>{{ $curso['nombre'] ?? 'N/A' }}</td>
                                            <td>{{ $curso['institucion'] ?? 'N/A' }}</td>
                                            <td>{{ $curso['anio'] ?? 'N/A' }}</td>
                                            <td>{{ $curso['duracion'] ?? 'N/A' }}</td>
                                            <td>
                                                <span class="badge {{ ($curso['completado'] ?? false) ? 'bg-success' : 'bg-warning' }}">
                                                    {{ ($curso['completado'] ?? false) ? 'Completado' : 'En progreso' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">No se han registrado cursos o certificaciones</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    {{-- Competencias Técnicas --}}
    @if(isset($respuestas['competencias_tecnicas']))
        <div class="row mt-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Competencias Técnicas</h6>
                    </div>
                    <div class="card-body">
                        @if(is_array($respuestas['competencias_tecnicas']))
                            <div class="row">
                                @foreach($respuestas['competencias_tecnicas'] as $competencia => $nivel)
                                    <div class="col-md-4 mb-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span>{{ ucfirst($competencia) }}:</span>
                                            <div class="progress" style="width: 100px; height: 20px;">
                                                <div class="progress-bar 
                                                    @if($nivel >= 80) bg-success
                                                    @elseif($nivel >= 60) bg-info
                                                    @elseif($nivel >= 40) bg-warning
                                                    @else bg-danger
                                                    @endif
                                                " style="width: {{ $nivel }}%">{{ $nivel }}%</div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="mb-0">{{ $respuestas['competencias_tecnicas'] }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>