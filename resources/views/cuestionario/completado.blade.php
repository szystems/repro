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
                @php
                    $tipoCuestionarioLabel = match ($evaluado->tipoFormularioCuestionario()) {
                        'socioeconomico' => 'socioeconómico',
                        'periodica' => 'periódico',
                        'especifica' => 'específico',
                        default => 'de evaluación',
                    };
                @endphp
                <div class="alert alert-success">
                    <h5><i class="fas fa-trophy"></i> ¡Felicidades!</h5>
                    <p class="mb-0">Ha completado satisfactoriamente el cuestionario {{ $tipoCuestionarioLabel }} para REPRO Guatemala.</p>
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
                            @foreach(\App\Support\MensajesInformacionImportante::viñetasCompletado($evaluado->tipoFormularioCuestionario()) as $viñeta)
                                <li>{{ $viñeta }}</li>
                            @endforeach
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

                @include('cuestionario.partials.documentos-candidato', [
                    'puedeSubirDocumentos' => $puedeSubirDocumentos ?? false,
                ])
                
                <div class="mt-4">
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-exclamation-triangle"></i> Importante</h6>
                        <p class="mb-0">
                            <strong>Las respuestas del cuestionario ya no pueden modificarse.</strong><br>
                            Si necesita corregir datos del formulario o tiene alguna consulta, contacte a REPRO o a la empresa solicitante.
                            @if($puedeSubirDocumentos ?? false)
                                <br><small>Puede seguir adjuntando documentos en la sección anterior mientras su enlace esté vigente.</small>
                            @endif
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
                        
                        <button type="button" class="btn btn-secondary" id="btnCerrarVentana">
                            <i class="fas fa-times"></i> Cerrar Ventana
                        </button>
                    </div>
                    <div id="cerrarVentanaAyuda" class="alert alert-info mt-3 text-start" role="status">
                        <strong><i class="fas fa-info-circle"></i> Para salir</strong>
                        <p class="mb-0 small mt-1">
                            Cuando termine, cierre esta pestaña del navegador manualmente
                            (<kbd>Ctrl</kbd>+<kbd>W</kbd> en Windows/Linux, <kbd>⌘</kbd>+<kbd>W</kbd> en Mac)
                            o use el botón <strong>Cerrar Ventana</strong> para ver estas instrucciones en pantalla.
                        </p>
                    </div>
                </div>
                
                <div class="footer-info mt-4">
                    <p class="mb-1"><strong>REPRO </strong></p>
                    <p class="mb-0">Guatemala • Evaluaciones de Confiabilidad • {{ date('Y') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

@if(isset($evaluado->cuestionario) && $evaluado->cuestionario->firma_digital)
    {{-- Modal para mostrar firma si se desea --}}
    <div class="modal fade" id="firmaModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Firma Digital Registrada</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img src="{{ $evaluado->cuestionario->firma_digital }}" alt="Firma Digital" class="img-fluid" style="max-height: 200px; border: 1px solid #ddd;">
                    <p class="text-muted mt-2 small">
                        Firma registrada el {{ $evaluado->cuestionario->completado_at?->format('d/m/Y H:i:s') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
@endif
@endsection

@push('styles')
<style>
@media print {
    /* Ocultar elementos no necesarios */
    .btn, .modal, nav, .navbar, header, footer, .d-grid, 
    .cuestionario-footer { 
        display: none !important; 
    }
    
    /* Resetear márgenes y tamaños */
    @page {
        size: letter portrait;
        margin: 0.3cm 0.5cm;
    }
    
    * {
        box-sizing: border-box !important;
    }
    
    html, body {
        width: 100% !important;
        height: auto !important;
        margin: 0 !important;
        padding: 0 !important;
        font-size: 10px !important;
        background: white !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        overflow: visible !important;
        position: static !important;
    }
    
    /* Mantener cabecera visible y sin posición fija */
    .cuestionario-header {
        position: static !important;
        display: block !important;
        padding: 5px 0 !important;
        margin-bottom: 5px !important;
    }
    
    .cuestionario-footer {
        display: none !important;
    }
    
    .container, .container-fluid {
        width: 100% !important;
        max-width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
        position: static !important;
    }
    
    .row {
        margin: 0 !important;
        display: flex !important;
        flex-wrap: wrap !important;
    }
    
    .col-lg-8, .col-md-6, .col-12, [class*="col-"] {
        width: auto !important;
        max-width: 100% !important;
        padding: 2px !important;
        flex: 1 1 auto !important;
    }
    
    .justify-content-center {
        justify-content: flex-start !important;
    }
    
    .form-card {
        box-shadow: none !important;
        border: 1px solid #333 !important;
        margin: 0 !important;
        padding: 5px !important;
        position: static !important;
    }
    
    .form-header {
        padding: 8px !important;
        margin-bottom: 5px !important;
        position: static !important;
    }
    
    .form-header h1 {
        font-size: 14px !important;
        margin: 0 !important;
    }
    
    .form-header p, .form-header i.fa-3x {
        display: none !important;
    }
    
    .form-content {
        padding: 3px !important;
        position: static !important;
    }
    
    .alert {
        padding: 5px !important;
        margin-bottom: 5px !important;
        font-size: 9px !important;
        border: 1px solid #999 !important;
    }
    
    .alert h5, .alert h6 {
        font-size: 10px !important;
        margin-bottom: 2px !important;
    }
    
    .alert ul {
        margin: 0 !important;
        padding-left: 15px !important;
    }
    
    .alert li {
        font-size: 8px !important;
        margin-bottom: 1px !important;
    }
    
    .card {
        margin-bottom: 3px !important;
        border: 1px solid #ccc !important;
    }
    
    .card-body {
        padding: 5px !important;
    }
    
    .card-title {
        font-size: 9px !important;
        margin-bottom: 2px !important;
    }
    
    .card-body p {
        font-size: 8px !important;
        margin-bottom: 1px !important;
        line-height: 1.2 !important;
    }
    
    .mt-4, .mt-3 {
        margin-top: 5px !important;
    }
    
    .pt-3 {
        padding-top: 3px !important;
    }
    
    .mb-3, .mb-1 {
        margin-bottom: 3px !important;
    }
    
    h6 {
        font-size: 10px !important;
        margin-bottom: 3px !important;
    }
    
    .progress {
        height: 12px !important;
        margin-bottom: 5px !important;
    }
    
    .progress-bar {
        font-size: 8px !important;
        line-height: 12px !important;
    }
    
    ul.list-unstyled {
        margin: 0 !important;
        padding: 0 !important;
    }
    
    ul.list-unstyled li {
        font-size: 9px !important;
        margin-bottom: 1px !important;
    }
    
    .footer-info {
        font-size: 8px !important;
        margin-top: 5px !important;
        padding-top: 3px !important;
        border-top: 1px solid #333 !important;
    }
    
    .footer-info p {
        margin-bottom: 1px !important;
    }
    
    .text-muted, .text-muted small, small {
        font-size: 7px !important;
    }
    
    .border-top {
        border-top: 1px solid #ccc !important;
        padding-top: 3px !important;
        margin-top: 5px !important;
    }
    
    /* Evitar saltos de página */
    .form-card, .card, .alert, .row, .form-content {
        page-break-inside: avoid !important;
        break-inside: avoid !important;
    }
    
    /* Asegurar que todo cabe en una página */
    .form-card {
        max-height: 100vh !important;
        overflow: visible !important;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var btnCerrar = document.getElementById('btnCerrarVentana');
    var ayudaCerrar = document.getElementById('cerrarVentanaAyuda');

    function mostrarInstruccionesCierre() {
        if (ayudaCerrar) {
            ayudaCerrar.classList.remove('d-none');
            ayudaCerrar.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'info',
                title: 'Puede cerrar esta pestaña',
                html: 'Los navegadores no permiten cerrar pestañas abiertas desde un enlace.<br><br>' +
                    'Cierre esta ventana manualmente con <strong>Ctrl+W</strong> (Windows/Linux) ' +
                    'o <strong>⌘+W</strong> (Mac).',
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#6c757d',
            });
            return;
        }

        window.alert('Puede cerrar esta pestaña del navegador manualmente (Ctrl+W o ⌘+W).');
    }

    if (btnCerrar) {
        btnCerrar.addEventListener('click', function() {
            try {
                window.close();
            } catch (e) {
                // Ignorar: la mayoría de navegadores bloquean window.close().
            }

            mostrarInstruccionesCierre();

            btnCerrar.disabled = true;
            btnCerrar.innerHTML = '<i class="fas fa-check"></i> Instrucciones mostradas';
        });
    }

    // Mostrar mensaje de éxito con confetti effect (si está disponible)
    if (typeof confetti !== 'undefined') {
        confetti({
            particleCount: 100,
            spread: 70,
            origin: { y: 0.6 }
        });
    }

    // Mostrar firma en modal si se hace clic
    @if(isset($evaluado->cuestionario) && $evaluado->cuestionario->firma_digital)
        const showFirmaBtn = document.createElement('button');
        showFirmaBtn.type = 'button';
        showFirmaBtn.className = 'btn btn-outline-info btn-sm mt-2';
        showFirmaBtn.innerHTML = '<i class="fas fa-signature"></i> Ver Firma Registrada';
        showFirmaBtn.setAttribute('data-bs-toggle', 'modal');
        showFirmaBtn.setAttribute('data-bs-target', '#firmaModal');

        const firmaAnchor = document.getElementById('btnCerrarVentana');
        if (firmaAnchor && firmaAnchor.parentNode) {
            firmaAnchor.parentNode.parentNode.appendChild(showFirmaBtn);
        }
    @endif
});
</script>
@endpush