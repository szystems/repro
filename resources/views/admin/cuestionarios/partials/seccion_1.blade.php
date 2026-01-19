{{-- Sección 1: Datos Generales --}}
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
        <i class="bi bi-person"></i> Datos Generales del Evaluado
    </h5>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Información Personal</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td class="fw-bold">Nombre:</td>
                            <td>{{ $respuestas['nombre'] ?? 'No proporcionado' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Apellidos:</td>
                            <td>{{ $respuestas['apellidos'] ?? 'No proporcionado' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">DPI:</td>
                            <td>{{ $respuestas['dpi'] ?? 'No proporcionado' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Fecha de Nacimiento:</td>
                            <td>{{ $respuestas['fecha_nacimiento'] ?? 'No proporcionado' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Edad:</td>
                            <td>{{ $respuestas['edad'] ?? 'No calculada' }} años</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Género:</td>
                            <td>{{ ucfirst($respuestas['genero'] ?? 'No especificado') }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Estado Civil:</td>
                            <td>{{ ucfirst($respuestas['estado_civil'] ?? 'No especificado') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Información de Contacto</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td class="fw-bold">Email:</td>
                            <td>{{ $respuestas['email'] ?? 'No proporcionado' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Teléfono:</td>
                            <td>{{ $respuestas['telefono'] ?? 'No proporcionado' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Dirección:</td>
                            <td>{{ $respuestas['direccion'] ?? 'No proporcionada' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Departamento:</td>
                            <td>{{ $respuestas['departamento'] ?? 'No especificado' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Municipio:</td>
                            <td>{{ $respuestas['municipio'] ?? 'No especificado' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Nacionalidad:</td>
                            <td>{{ $respuestas['nacionalidad'] ?? 'No especificada' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Información adicional --}}
    @if(isset($respuestas['observaciones_personales']))
        <div class="row mt-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Observaciones Personales</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ $respuestas['observaciones_personales'] }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>