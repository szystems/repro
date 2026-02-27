{{-- Sección 1: Datos Generales --}}
@php
    // Mapeo de campos para compatibilidad entre diferentes tipos de formulario
    $nombre = $respuestas['nombres_completos'] ?? $respuestas['nombre'] ?? null;
    $apellidos = $respuestas['apellidos_completos'] ?? $respuestas['apellidos'] ?? null;
    $email = $respuestas['email_personal'] ?? $respuestas['email'] ?? null;
    $telefono = $respuestas['telefono_personal'] ?? $respuestas['telefono'] ?? null;
    $telefonoAlt = $respuestas['telefono_alternativo'] ?? null;
    $direccion = $respuestas['direccion_residencia'] ?? $respuestas['direccion'] ?? null;
    $lugarNacimiento = $respuestas['lugar_nacimiento'] ?? null;
    $nivelEducativo = $respuestas['nivel_educativo'] ?? null;
    $profesion = $respuestas['profesion_oficio'] ?? $respuestas['profesion'] ?? null;
    // Campo codigo_postal removido — no aplica en Guatemala
    
    // Calcular edad si hay fecha de nacimiento
    $edad = null;
    if (isset($respuestas['fecha_nacimiento']) && $respuestas['fecha_nacimiento']) {
        try {
            $edad = \Carbon\Carbon::parse($respuestas['fecha_nacimiento'])->age;
        } catch (\Exception $e) {
            $edad = null;
        }
    }
@endphp
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
        <i class="bi bi-person"></i> {{ $nombreSeccion ?? 'Datos Generales del Evaluado' }}
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
                            <td>{{ $nombre ?? 'No proporcionado' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Apellidos:</td>
                            <td>{{ $apellidos ?? 'No proporcionado' }}</td>
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
                            <td>{{ $edad ?? 'No calculada' }} años</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Género:</td>
                            <td>{{ ucfirst($respuestas['genero'] ?? 'No especificado') }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Estado Civil:</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $respuestas['estado_civil'] ?? 'No especificado')) }}</td>
                        </tr>
                        @if($lugarNacimiento)
                        <tr>
                            <td class="fw-bold">Lugar de Nacimiento:</td>
                            <td>{{ $lugarNacimiento }}</td>
                        </tr>
                        @endif
                        @if($nivelEducativo)
                        <tr>
                            <td class="fw-bold">Nivel Educativo:</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $nivelEducativo)) }}</td>
                        </tr>
                        @endif
                        @if($profesion)
                        <tr>
                            <td class="fw-bold">Profesión/Oficio:</td>
                            <td>{{ $profesion }}</td>
                        </tr>
                        @endif
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
                            <td>{{ $email ?? 'No proporcionado' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Teléfono:</td>
                            <td>{{ $telefono ?? 'No proporcionado' }}</td>
                        </tr>
                        @if($telefonoAlt)
                        <tr>
                            <td class="fw-bold">Teléfono Alternativo:</td>
                            <td>{{ $telefonoAlt }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td class="fw-bold">Dirección:</td>
                            <td>{!! nl2br(e($direccion ?? 'No proporcionada')) !!}</td>
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