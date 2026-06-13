@extends('layouts.cuestionario')

@section('title', 'Finalizar Cuestionario - REPRO')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="form-card">
            <div class="form-header">
                <h1><i class="fas fa-flag-checkered"></i> Finalizar Cuestionario</h1>
                <p>Revise y confirme la información antes de enviar</p>
            </div>
            
            <div class="progress-container">
                <div class="progress">
                    <div class="progress-bar" style="width: 100%"></div>
                </div>
                <div class="progress-text">
                    <i class="fas fa-check-circle text-success"></i> Todas las secciones completadas
                </div>
            </div>
            
            <div class="form-content">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <h6><i class="fas fa-exclamation-triangle"></i> Errores encontrados</h6>
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <div class="alert alert-success">
                    <h6><i class="fas fa-check-circle"></i> ¡Felicidades!</h6>
                    <p class="mb-0">Ha completado todas las secciones del cuestionario socioeconómico. Por favor, revise la información antes de finalizar.</p>
                </div>
                
                <div class="section-title">
                    <i class="fas fa-clipboard-list"></i> Resumen del Cuestionario
                </div>
                
                {{-- Resumen por secciones --}}
                <div class="row">
                    @foreach($resumenSecciones as $numero => $seccion)
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="fas fa-{{ $seccion['icono'] }}"></i> 
                                        Sección {{ $numero }}: {{ $seccion['nombre'] }}
                                        @if($seccion['completada'])
                                            <span class="badge bg-success ms-2">
                                                <i class="fas fa-check"></i> Completada
                                            </span>
                                        @else
                                            <span class="badge bg-warning ms-2">
                                                <i class="fas fa-exclamation-triangle"></i> Incompleta
                                            </span>
                                        @endif
                                    </h6>
                                    <p class="card-text small text-muted">
                                        @if($seccion['completada'])
                                            <i class="fas fa-check-circle text-success"></i> Información guardada correctamente
                                        @else
                                            <i class="fas fa-exclamation-circle text-warning"></i> Pendiente de completar
                                        @endif
                                    </p>
                                    @if(!$seccion['completada'])
                                        <a href="{{ route('cuestionario.seccion', ['token' => $token, 'numero' => $numero]) }}" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i> Completar
                                        </a>
                                    @else
                                        <a href="{{ route('cuestionario.seccion', ['token' => $token, 'numero' => $numero]) }}" 
                                           class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-eye"></i> Revisar
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                {{-- Información clave del cuestionario --}}
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title"><i class="fas fa-user"></i> Información Personal</h6>
                                <p class="mb-1"><strong>Nombre:</strong> {{ $datosPersonales['nombres_completos'] ?? '' }} {{ $datosPersonales['apellidos_completos'] ?? '' }}</p>
                                <p class="mb-1"><strong>DPI:</strong> {{ $evaluadoOrden->dpi }}</p>
                                @php
                                    $edad = null;
                                    if (!empty($datosPersonales['fecha_nacimiento'])) {
                                        try {
                                            $fechaNac = \Carbon\Carbon::parse($datosPersonales['fecha_nacimiento']);
                                            $edad = $fechaNac->age;
                                        } catch (\Exception $e) {}
                                    }
                                @endphp
                                <p class="mb-0"><strong>Edad:</strong> {{ $edad ?? 'No especificada' }} años</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title"><i class="fas fa-briefcase"></i> Situación Laboral</h6>
                                <p class="mb-1"><strong>Situación laboral:</strong> {{ ucfirst(str_replace('_', ' ', $historialLaboral['situacion_laboral_actual'] ?? 'No especificado')) }}</p>
                                <p class="mb-1"><strong>Experiencia:</strong> {{ $historialLaboral['anos_experiencia_laboral'] ?? 'No especificada' }} años</p>
                                @php
                                    $ingresosPrincipales = floatval($situacionEconomica['ingresos_principales'] ?? 0);
                                    $ingresosAdicionales = floatval($situacionEconomica['ingresos_adicionales'] ?? 0);
                                    $ingresosTotales = $ingresosPrincipales + $ingresosAdicionales;
                                @endphp
                                <p class="mb-0"><strong>Ingresos mensuales:</strong> Q. {{ number_format($ingresosTotales, 2) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                {{-- Documentos del Evaluado --}}
                <div class="mt-4">
                    <div class="section-title">
                        <i class="fas fa-folder-open"></i> Documentos Adjuntos
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Documentos opcionales:</strong> Si tiene documentos relevantes (DPI, constancias, etc.) puede subirlos aquí.
                        Los documentos serán verificados por REPRO.
                    </div>

                    {{-- Documentos ya subidos --}}
                    @if($evaluadoOrden->documentos->count() > 0)
                        <div class="mb-3">
                            <h6>Documentos cargados:</h6>
                            <ul class="list-group list-group-flush">
                                @foreach($evaluadoOrden->documentos as $doc)
                                    <li class="list-group-item d-flex justify-content-between align-items-start py-2">
                                        <span>
                                            <i class="fas fa-file"></i>
                                            {{ $doc->tipo_documento_texto }} — <small class="text-muted">{{ $doc->nombre_original }}</small>
                                            @if($doc->notas_verificacion && $doc->estado_verificacion === 'rechazado')
                                                <br><small class="text-danger"><i class="fas fa-exclamation-circle"></i> {{ $doc->notas_verificacion }}</small>
                                            @endif
                                        </span>
                                        <span class="badge bg-{{ $doc->estado_verificacion_color }}">{{ $doc->estado_verificacion_texto }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Formulario de subida --}}
                    <form action="{{ route('cuestionario.subir-documento', $token) }}" method="POST" enctype="multipart/form-data" class="border rounded p-3 bg-light">
                        @csrf
                        <div class="row g-2 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label">Tipo de Documento</label>
                                <select name="tipo_documento" class="form-select form-select-sm" required>
                                    <option value="">Seleccione...</option>
                                    @foreach(\App\Models\DocumentoEvaluado::tiposDocumento() as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">Archivo <small class="text-muted">(máx. 10 MB)</small></label>
                                <input type="file" name="archivo" class="form-control form-control-sm"
                                       accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" capture="environment" required>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-outline-primary btn-sm w-100">
                                    <i class="fas fa-upload"></i> Subir
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- Enviar Cuestionario --}}
                <div class="mt-4">
                    <div class="section-title">
                        <i class="fas fa-paper-plane"></i> Enviar Cuestionario
                    </div>
                    
                    <form action="{{ route('cuestionario.completar', $token) }}" method="POST" id="finalizarForm">
                        @csrf
                        
                        <div class="form-group">
                            <div class="form-check">
                                <input class="form-check-input @error('confirmacion_final') is-invalid @enderror" 
                                       type="checkbox" 
                                       id="confirmacion_final" 
                                       name="confirmacion_final" 
                                       value="1"
                                       required>
                                <label class="form-check-label" for="confirmacion_final">
                                    <strong>Confirmo que he revisado toda la información y es correcta.</strong> 
                                    Entiendo que una vez enviado el cuestionario no podré realizar modificaciones. <span class="required">*</span>
                                </label>
                                @error('confirmacion_final')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="navigation-buttons">
                            <div>
                                <a href="{{ route('cuestionario.seccion', ['token' => $token, 'numero' => 5]) }}" 
                                   class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Regresar a Sección 5
                                </a>
                            </div>
                            
                            <div>
                                <button type="submit" class="btn btn-success btn-lg" id="btnFinalizar">
                                    <i class="fas fa-paper-plane"></i> Enviar Cuestionario Completo
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('finalizarForm');
    
    form.addEventListener('submit', function(e) {
        const confirmacion = document.getElementById('confirmacion_final');
        if (!confirmacion.checked) {
            e.preventDefault();
            if (typeof cuestionarioHelpers !== 'undefined') {
                cuestionarioHelpers.showAlert('Debe confirmar que ha revisado la información antes de enviar.', 'error');
            }
            confirmacion.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return false;
        }
        
        if (!confirm('¿Está seguro de que desea enviar el cuestionario? No podrá realizar cambios después de enviarlo.')) {
            e.preventDefault();
            return false;
        }
        
        document.getElementById('btnFinalizar').disabled = true;
        document.getElementById('btnFinalizar').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
    });
});
</script>
@endpush