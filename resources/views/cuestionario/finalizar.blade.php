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
                                <p class="mb-1"><strong>Estado:</strong> {{ ucfirst(str_replace('_', ' ', $historialLaboral['situacion_laboral_actual'] ?? 'No especificado')) }}</p>
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
                
                {{-- Firma digital --}}
                <div class="mt-4">
                    <div class="section-title">
                        <i class="fas fa-signature"></i> Firma Digital
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Instrucciones para la firma:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Dibuje su firma en el recuadro a continuación</li>
                            <li>Use el mouse o su dedo si está en dispositivo táctil</li>
                            <li>Puede borrar y volver a firmar si es necesario</li>
                            <li>La firma es requerida para completar el cuestionario</li>
                        </ul>
                    </div>
                    
                    <form action="{{ route('cuestionario.completar', $token) }}" method="POST" id="finalizarForm">
                        @csrf
                        
                        <div class="form-group">
                            <label for="firma_canvas" class="form-label">
                                Firma Digital <span class="required">*</span>
                            </label>
                            
                            <div class="signature-pad-container" style="border: 2px solid #ddd; border-radius: 8px; background: white;">
                                <canvas id="firma_canvas" width="600" height="200" style="border-radius: 6px; cursor: crosshair;"></canvas>
                            </div>
                            
                            <input type="hidden" id="firma_data" name="firma_digital" required>
                            
                            <div class="d-flex gap-2 mt-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="limpiarFirma">
                                    <i class="fas fa-eraser"></i> Limpiar Firma
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-info" id="verificarFirma">
                                    <i class="fas fa-check"></i> Verificar Firma
                                </button>
                            </div>
                            
                            @error('firma_digital')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        
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
    // Configuración del canvas para firma
    const canvas = document.getElementById('firma_canvas');
    const ctx = canvas.getContext('2d');
    const firmaInput = document.getElementById('firma_data');
    const limpiarBtn = document.getElementById('limpiarFirma');
    const verificarBtn = document.getElementById('verificarFirma');
    const form = document.getElementById('finalizarForm');
    
    let isDrawing = false;
    let hasSignature = false;
    
    // Configurar canvas
    ctx.strokeStyle = '#000';
    ctx.lineWidth = 2;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
    
    // Función para obtener coordenadas correctas
    function getCoordinates(e) {
        const rect = canvas.getBoundingClientRect();
        const scaleX = canvas.width / rect.width;
        const scaleY = canvas.height / rect.height;
        
        if (e.touches) {
            return {
                x: (e.touches[0].clientX - rect.left) * scaleX,
                y: (e.touches[0].clientY - rect.top) * scaleY
            };
        } else {
            return {
                x: (e.clientX - rect.left) * scaleX,
                y: (e.clientY - rect.top) * scaleY
            };
        }
    }
    
    // Eventos de mouse
    canvas.addEventListener('mousedown', startDrawing);
    canvas.addEventListener('mousemove', draw);
    canvas.addEventListener('mouseup', stopDrawing);
    canvas.addEventListener('mouseout', stopDrawing);
    
    // Eventos táctiles
    canvas.addEventListener('touchstart', function(e) {
        e.preventDefault();
        startDrawing(e);
    });
    
    canvas.addEventListener('touchmove', function(e) {
        e.preventDefault();
        draw(e);
    });
    
    canvas.addEventListener('touchend', function(e) {
        e.preventDefault();
        stopDrawing(e);
    });
    
    function startDrawing(e) {
        isDrawing = true;
        const coords = getCoordinates(e);
        ctx.beginPath();
        ctx.moveTo(coords.x, coords.y);
    }
    
    function draw(e) {
        if (!isDrawing) return;
        
        const coords = getCoordinates(e);
        ctx.lineTo(coords.x, coords.y);
        ctx.stroke();
        hasSignature = true;
    }
    
    function stopDrawing() {
        if (isDrawing) {
            isDrawing = false;
            updateSignatureData();
        }
    }
    
    function updateSignatureData() {
        if (hasSignature) {
            firmaInput.value = canvas.toDataURL();
        }
    }
    
    function clearSignature() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        hasSignature = false;
        firmaInput.value = '';
        
        // Agregar línea de guía
        ctx.strokeStyle = '#ddd';
        ctx.lineWidth = 1;
        ctx.beginPath();
        ctx.moveTo(50, canvas.height - 50);
        ctx.lineTo(canvas.width - 50, canvas.height - 50);
        ctx.stroke();
        
        // Restaurar estilo de firma
        ctx.strokeStyle = '#000';
        ctx.lineWidth = 2;
        
        cuestionarioHelpers.showAlert('Firma limpiada. Puede firmar nuevamente.', 'info');
    }
    
    function verifySignature() {
        const container = canvas.parentElement;
        if (hasSignature) {
            container.style.borderColor = '#28a745';
            container.style.boxShadow = '0 0 10px rgba(40, 167, 69, 0.5)';
            alert('✅ Firma válida detectada. Puede continuar.');
        } else {
            container.style.borderColor = '#dc3545';
            container.style.boxShadow = '0 0 10px rgba(220, 53, 69, 0.5)';
            alert('⚠️ No se ha detectado una firma. Por favor, firme en el recuadro.');
        }
        // Restaurar estilo después de 3 segundos
        setTimeout(() => {
            container.style.borderColor = '#ddd';
            container.style.boxShadow = 'none';
        }, 3000);
    }
    
    // Event listeners para botones
    limpiarBtn.addEventListener('click', clearSignature);
    verificarBtn.addEventListener('click', verifySignature);
    
    // Validación del formulario
    form.addEventListener('submit', function(e) {
        cuestionarioHelpers.showLoading();
        
        if (!hasSignature) {
            e.preventDefault();
            cuestionarioHelpers.hideLoading();
            cuestionarioHelpers.showAlert('Por favor, proporcione su firma digital antes de enviar el cuestionario.', 'error');
            canvas.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return false;
        }
        
        const confirmacion = document.getElementById('confirmacion_final');
        if (!confirmacion.checked) {
            e.preventDefault();
            cuestionarioHelpers.hideLoading();
            cuestionarioHelpers.showAlert('Debe confirmar que ha revisado la información antes de enviar.', 'error');
            confirmacion.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return false;
        }
        
        // Confirmación final
        if (!confirm('¿Está seguro de que desea enviar el cuestionario? No podrá realizar cambios después de enviarlo.')) {
            e.preventDefault();
            cuestionarioHelpers.hideLoading();
            return false;
        }
        
        // Deshabilitar botón para evitar doble envío
        document.getElementById('btnFinalizar').disabled = true;
        document.getElementById('btnFinalizar').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
    });
    
    // Inicializar con línea de guía
    clearSignature();
    
    // Variable para controlar si el formulario se está enviando
    let isSubmitting = false;
    
    // Prevenir salida accidental (solo si hay firma y no se está enviando)
    function beforeUnloadHandler(e) {
        if (hasSignature && !isSubmitting) {
            e.preventDefault();
            e.returnValue = '¿Está seguro de salir? Perderá la firma que ha creado.';
            return e.returnValue;
        }
    }
    
    window.addEventListener('beforeunload', beforeUnloadHandler);
    
    // Modificar el submit para marcar que se está enviando
    const originalSubmitHandler = form.onsubmit;
    form.addEventListener('submit', function(e) {
        // Marcar que se está enviando para evitar el mensaje de beforeunload
        isSubmitting = true;
        window.removeEventListener('beforeunload', beforeUnloadHandler);
    }, true); // usar capture para que se ejecute primero
});
</script>
@endpush