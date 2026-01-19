@extends('layouts.cuestionario')

@section('title', 'Cuestionario Completado - REPRO')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="form-card">
            <div class="form-header" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                <i class="fas fa-check-circle fa-3x mb-3"></i>
                <h1>¡Cuestionario Completado Exitosamente!</h1>
                <p>Su información ha sido registrada correctamente</p>
            </div>
            
            <div class="form-content text-center">
                <div class="alert alert-success">
                    <h5><i class="fas fa-trophy"></i> ¡Felicidades!</h5>
                    <p class="mb-0">Ha completado satisfactoriamente el cuestionario socioeconómico para REPRO Guatemala.</p>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="fas fa-user text-primary"></i> Evaluado
                                </h6>
                                <p class="mb-1"><strong>{{ $evaluado->nombres }} {{ $evaluado->apellidos }}</strong></p>
                                <p class="mb-0"><small class="text-muted">DPI: {{ $evaluado->dpi }}</small></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="fas fa-calendar text-success"></i> Fecha de Completado
                                </h6>
                                <p class="mb-1"><strong>{{ $evaluado->completado_at?->format('d/m/Y') ?? now()->format('d/m/Y') }}</strong></p>
                                <p class="mb-0"><small class="text-muted">{{ $evaluado->completado_at?->format('H:i:s') ?? now()->format('H:i:s') }}</small></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="fas fa-building text-info"></i> Empresa Solicitante
                                </h6>
                                <p class="mb-1"><strong>{{ $evaluado->orden->empresa->nombre_comercial }}</strong></p>
                                <p class="mb-0"><small class="text-muted">Orden #{{ $evaluado->orden->id }}</small></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="fas fa-briefcase text-warning"></i> Puesto a Evaluar
                                </h6>
                                <p class="mb-1"><strong>{{ $evaluado->puesto_evaluar ?? 'No especificado' }}</strong></p>
                                <p class="mb-0"><small class="text-muted">{{ ucfirst($evaluado->orden->tipo_servicio) }}</small></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4">
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle"></i> Información Importante</h6>
                        <ul class="text-start mb-0">
                            <li><strong>Confidencialidad:</strong> Su información será tratada de manera confidencial</li>
                            <li><strong>Proceso:</strong> REPRO revisará sus respuestas como parte de la evaluación</li>
                            <li><strong>Contacto:</strong> Si hay alguna consulta, se comunicarán con usted</li>
                            <li><strong>Resultado:</strong> Los resultados serán comunicados por la empresa solicitante</li>
                        </ul>
                    </div>
                </div>
                
                <div class="mt-4">
                    <h6>Resumen de Secciones Completadas</h6>
                    <div class="row">
                        <div class="col-12">
                            <div class="progress mb-3" style="height: 25px;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: 100%">
                                    100% Completado
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row text-start">
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success"></i> Datos Personales</li>
                                <li><i class="fas fa-check text-success"></i> Información Familiar</li>
                                <li><i class="fas fa-check text-success"></i> Historial Laboral</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success"></i> Situación Económica</li>
                                <li><i class="fas fa-check text-success"></i> Antecedentes y Referencias</li>
                                <li><i class="fas fa-check text-success"></i> Firma Digital</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4">
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-exclamation-triangle"></i> Importante</h6>
                        <p class="mb-0">
                            <strong>Este cuestionario ya no puede ser modificado.</strong><br>
                            Si necesita hacer alguna corrección o tiene alguna consulta, 
                            contacte directamente con REPRO o con la empresa solicitante.
                        </p>
                    </div>
                </div>
                
                <div class="mt-4 pt-3 border-top">
                    <p class="text-muted mb-1">
                        <small>
                            <i class="fas fa-shield-alt"></i> 
                            Cuestionario completado de forma segura el {{ $evaluado->completado_at?->format('d/m/Y \a \l\a\s H:i:s') ?? now()->format('d/m/Y \a \l\a\s H:i:s') }}
                        </small>
                    </p>
                    <p class="text-muted mb-0">
                        <small>
                            <i class="fas fa-fingerprint"></i> 
                            Token de sesión: {{ substr($evaluado->token_unico, 0, 8) }}...
                        </small>
                    </p>
                </div>
                
                <div class="mt-4">
                    <div class="d-grid gap-2 d-md-block">
                        <button type="button" class="btn btn-success" onclick="window.print()">
                            <i class="fas fa-print"></i> Imprimir Confirmación
                        </button>
                        
                        <button type="button" class="btn btn-secondary" onclick="window.close()">
                            <i class="fas fa-times"></i> Cerrar Ventana
                        </button>
                    </div>
                </div>
                
                <div class="footer-info mt-4">
                    <p class="mb-1"><strong>REPRO - Registro de Personas Reprobadas</strong></p>
                    <p class="mb-0">Guatemala • Evaluaciones de Confiabilidad • {{ date('Y') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

@if($evaluado->firma_digital)
    {{-- Modal para mostrar firma si se desea --}}
    <div class="modal fade" id="firmaModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Firma Digital Registrada</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img src="{{ $evaluado->firma_digital }}" alt="Firma Digital" class="img-fluid" style="max-height: 200px; border: 1px solid #ddd;">
                </div>
            </div>
        </div>
    </div>
@endif
@endsection

@push('styles')
<style>
@media print {
    .btn, .footer-info, .modal { 
        display: none !important; 
    }
    
    .form-card {
        box-shadow: none !important;
        border: 1px solid #000 !important;
    }
    
    body {
        background: white !important;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mostrar mensaje de éxito con confetti effect (si está disponible)
    if (typeof confetti !== 'undefined') {
        confetti({
            particleCount: 100,
            spread: 70,
            origin: { y: 0.6 }
        });
    }
    
    // Auto-cerrar ventana después de 5 minutos de inactividad
    let autoCloseTimer = setTimeout(function() {
        if (confirm('¿Desea cerrar esta ventana automáticamente?')) {
            window.close();
        }
    }, 300000); // 5 minutos
    
    // Cancelar auto-cierre si hay actividad
    document.addEventListener('click', function() {
        clearTimeout(autoCloseTimer);
    });
    
    // Función mejorada de impresión
    window.print = function() {
        const printWindow = window.open('', '_blank');
        const content = document.documentElement.outerHTML;
        
        printWindow.document.write(content);
        printWindow.document.close();
        printWindow.focus();
        printWindow.print();
        printWindow.close();
    };
    
    // Mostrar firma en modal si se hace clic
    @if($evaluado->firma_digital)
        const showFirmaBtn = document.createElement('button');
        showFirmaBtn.className = 'btn btn-outline-info btn-sm mt-2';
        showFirmaBtn.innerHTML = '<i class="fas fa-signature"></i> Ver Firma Registrada';
        showFirmaBtn.setAttribute('data-bs-toggle', 'modal');
        showFirmaBtn.setAttribute('data-bs-target', '#firmaModal');
        
        const footer = document.querySelector('.footer-info');
        footer.parentNode.insertBefore(showFirmaBtn, footer);
    @endif
});
</script>
@endpush