{{-- Sección 1: Datos Generales (E2.1 Pre-empleo) --}}
@php
    use App\Support\DatosPersonalesCampos;
    $nombre = $respuestas['nombres_completos'] ?? $respuestas['nombre'] ?? null;
    $apellidos = $respuestas['apellidos_completos'] ?? $respuestas['apellidos'] ?? null;
    $email = $respuestas['email_personal'] ?? $respuestas['email'] ?? null;
    $telefono = $respuestas['telefono_personal'] ?? $respuestas['telefono'] ?? null;
    $telefonoEmergencia = $respuestas['telefono_alternativo'] ?? null;
    $direccion = $respuestas['direccion_residencia'] ?? $respuestas['direccion'] ?? null;
    $edad = $respuestas['edad'] ?? null;
    if (! $edad && ! empty($respuestas['fecha_nacimiento'])) {
        try {
            $edad = \Carbon\Carbon::parse($respuestas['fecha_nacimiento'])->age;
        } catch (\Exception $e) {
            $edad = null;
        }
    }
    $lugarNacimiento = trim(($respuestas['departamento_nacimiento'] ?? '').(($respuestas['municipio_nacimiento'] ?? '') !== '' ? ', '.$respuestas['municipio_nacimiento'] : ''));
@endphp
<div class="section-content">
    @if(!($ocultarEstadoSeccion ?? false))
    @if($completada)
        <div class="alert alert-success mb-3">
            <i class="bi bi-check-circle-fill"></i> Sección completada
        </div>
    @else
        <div class="alert alert-warning mb-3">
            <i class="bi bi-exclamation-triangle"></i> Sección pendiente o incompleta
        </div>
    @endif
    @endif

    <h5 class="section-title mb-4">
        <i class="bi bi-person"></i> {{ $nombreSeccion ?? 'Datos Generales del Evaluado' }}
    </h5>

    @if(!empty($fotoCandidatoUrl))
        <div class="row mb-4">
            <div class="col-md-4 col-lg-3">
                <div class="card">
                    <div class="card-header py-2">
                        <h6 class="mb-0"><i class="bi bi-camera"></i> Fotografía del candidato</h6>
                    </div>
                    <div class="card-body text-center p-2">
                        <a href="{{ $fotoCandidatoUrl }}" target="_blank" rel="noopener">
                            <img src="{{ $fotoCandidatoUrl }}"
                                 alt="Fotografía del candidato"
                                 class="img-fluid rounded border"
                                 style="max-height: 320px; object-fit: cover;">
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><h6 class="mb-0">Información personal</h6></div>
                <div class="card-body">
                    <table class="table table-borderless table-sm mb-0">
                        <tr><td class="fw-bold">Nombre:</td><td>{{ $nombre ?? '—' }}</td></tr>
                        <tr><td class="fw-bold">Apellidos:</td><td>{{ $apellidos ?? '—' }}</td></tr>
                        <tr><td class="fw-bold">Tipo ID:</td><td>{{ DatosPersonalesCampos::etiquetaTipoIdentificacion($respuestas['tipo_identificacion'] ?? 'dpi') }}</td></tr>
                        <tr><td class="fw-bold">No. identificación:</td><td>{{ $respuestas['dpi'] ?? '—' }}</td></tr>
                        <tr><td class="fw-bold">Fecha nacimiento:</td><td>{{ $respuestas['fecha_nacimiento'] ?? '—' }}</td></tr>
                        <tr><td class="fw-bold">Edad:</td><td>{{ $edad ? $edad.' años' : '—' }}</td></tr>
                        <tr><td class="fw-bold">Nacionalidad:</td><td>{{ $respuestas['nacionalidad'] ?? '—' }}</td></tr>
                        <tr><td class="fw-bold">Estado civil:</td><td>{{ ucfirst(str_replace('_', ' ', $respuestas['estado_civil'] ?? '—')) }}</td></tr>
                        <tr><td class="fw-bold">Nacimiento:</td><td>{{ $lugarNacimiento !== '' ? $lugarNacimiento : '—' }}</td></tr>
                        <tr><td class="fw-bold">IGSS:</td><td>{{ $respuestas['igss'] ?? '—' }}</td></tr>
                        <tr><td class="fw-bold">NIT:</td><td>{{ $respuestas['nit'] ?? '—' }}</td></tr>
                        <tr><td class="fw-bold">Licencia:</td><td>{{ DatosPersonalesCampos::etiquetaLicencia($respuestas['licencia_conducir'] ?? null) ?: '—' }}</td></tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><h6 class="mb-0">Contacto y residencia</h6></div>
                <div class="card-body">
                    <table class="table table-borderless table-sm mb-0">
                        <tr><td class="fw-bold">Correo:</td><td>{{ $email ?? '—' }}</td></tr>
                        <tr><td class="fw-bold">Teléfono:</td><td>{{ $telefono ?? '—' }}</td></tr>
                        @if($telefonoEmergencia)
                        <tr><td class="fw-bold">Emergencia:</td><td>{{ $telefonoEmergencia }}</td></tr>
                        @endif
                        <tr><td class="fw-bold">Dirección:</td><td>{!! nl2br(e($direccion ?? '—')) !!}</td></tr>
                        <tr><td class="fw-bold">Depto. residencia:</td><td>{{ $respuestas['departamento'] ?? '—' }}</td></tr>
                        <tr><td class="fw-bold">Municipio residencia:</td><td>{{ $respuestas['municipio'] ?? '—' }}</td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
