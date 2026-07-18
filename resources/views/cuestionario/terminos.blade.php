@extends('layouts.cuestionario')

@section('title', 'Autorización y Términos - REPRO')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="form-card">
            <div class="form-header">
                <h1><i class="fas fa-file-contract"></i> Autorización y Términos</h1>
                <p>Lea cuidadosamente y firme para continuar</p>
            </div>

            <div class="form-content">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                {{-- Información del evaluado --}}
                <div class="alert alert-info">
                    <strong><i class="fas fa-user"></i> {{ $evaluado->nombre }} {{ $evaluado->apellidos }}</strong><br>
                    <small>DPI: {{ $evaluado->dpi }} | Empresa: {{ $evaluado->orden->empresa->nombre ?? 'N/A' }}</small>
                </div>

                {{-- Contenido de autorización legal (Fase A — plantilla según servicio + formulario) --}}
                <div class="section-title">
                    <i class="fas fa-scroll"></i> {{ \App\Support\AutorizacionesLegales::titulo($evaluado) }}
                </div>

                @include('cuestionario.partials.autorizacion-legal', ['evaluado' => $evaluado])

                {{-- Formulario de aceptación --}}
                <form action="{{ route('cuestionario.aceptar-terminos', $token) }}" method="POST" id="terminosForm">
                    @csrf

                    <input type="hidden" name="tipo_proceso" value="{{ $tipoServicio }}">

                    {{-- Firma digital --}}
                    <div class="section-title mt-4">
                        <i class="fas fa-signature"></i> Firma Digital
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Instrucciones para la firma:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Dibuje su firma en el recuadro a continuación</li>
                            <li>Use el mouse o su dedo si está en dispositivo táctil</li>
                            <li>Puede borrar y volver a firmar si es necesario</li>
                            <li>La firma es requerida para aceptar los términos</li>
                        </ul>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="firma_canvas" class="form-label">
                            Firma Digital <span class="text-danger">*</span>
                        </label>
                        
                        <div class="signature-pad-container" style="border: 2px solid #ddd; border-radius: 8px; background: white;">
                            <canvas id="firma_canvas" width="600" height="200" style="border-radius: 6px; cursor: crosshair; max-width: 100%;"></canvas>
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

                    {{-- Checkbox de aceptación --}}
                    <div class="form-check mb-4">
                        <input class="form-check-input @error('acepta_terminos') is-invalid @enderror"
                               type="checkbox" id="acepta_terminos" name="acepta_terminos" value="1" required>
                        <label class="form-check-label" for="acepta_terminos">
                            <strong>He leído, entiendo y acepto los términos y condiciones anteriores. Autorizo la realización de la evaluación.</strong>
                        </label>
                        @error('acepta_terminos')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-actions mt-4">
                        <button type="submit" class="btn btn-primary btn-lg w-100" id="btnAceptar" disabled>
                            <i class="fas fa-check-circle"></i> Firmo, Acepto y Continuar al Cuestionario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnAceptar = document.getElementById('btnAceptar');
    const checkTerminos = document.getElementById('acepta_terminos');
    const form = document.getElementById('terminosForm');

    // Configuración del canvas para firma
    const canvas = document.getElementById('firma_canvas');
    const ctx = canvas.getContext('2d');
    const firmaInput = document.getElementById('firma_data');
    const limpiarBtn = document.getElementById('limpiarFirma');
    const verificarBtn = document.getElementById('verificarFirma');
    
    let isDrawing = false;
    let hasSignature = false;
    
    ctx.strokeStyle = '#000';
    ctx.lineWidth = 2;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
    
    function getCoordinates(e) {
        const rect = canvas.getBoundingClientRect();
        const scaleX = canvas.width / rect.width;
        const scaleY = canvas.height / rect.height;
        if (e.touches) {
            return { x: (e.touches[0].clientX - rect.left) * scaleX, y: (e.touches[0].clientY - rect.top) * scaleY };
        }
        return { x: (e.clientX - rect.left) * scaleX, y: (e.clientY - rect.top) * scaleY };
    }
    
    canvas.addEventListener('mousedown', startDrawing);
    canvas.addEventListener('mousemove', draw);
    canvas.addEventListener('mouseup', stopDrawing);
    canvas.addEventListener('mouseout', stopDrawing);
    canvas.addEventListener('touchstart', function(e) { e.preventDefault(); startDrawing(e); });
    canvas.addEventListener('touchmove', function(e) { e.preventDefault(); draw(e); });
    canvas.addEventListener('touchend', function(e) { e.preventDefault(); stopDrawing(e); });
    
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
        toggleSubmit();
    }
    
    function stopDrawing() {
        if (isDrawing) {
            isDrawing = false;
            if (hasSignature) firmaInput.value = canvas.toDataURL();
        }
    }
    
    function clearSignature() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        hasSignature = false;
        firmaInput.value = '';
        ctx.strokeStyle = '#ddd'; ctx.lineWidth = 1;
        ctx.beginPath(); ctx.moveTo(50, canvas.height - 50); ctx.lineTo(canvas.width - 50, canvas.height - 50); ctx.stroke();
        ctx.strokeStyle = '#000'; ctx.lineWidth = 2;
        toggleSubmit();
    }
    
    limpiarBtn.addEventListener('click', clearSignature);
    verificarBtn.addEventListener('click', function() {
        const container = canvas.parentElement;
        if (hasSignature) {
            container.style.borderColor = '#28a745';
            alert('✅ Firma válida detectada.');
        } else {
            container.style.borderColor = '#dc3545';
            alert('⚠️ No se ha detectado una firma. Por favor, firme en el recuadro.');
        }
        setTimeout(() => { container.style.borderColor = '#ddd'; }, 3000);
    });

    function toggleSubmit() {
        btnAceptar.disabled = !(checkTerminos.checked && hasSignature);
    }

    checkTerminos.addEventListener('change', toggleSubmit);
    
    form.addEventListener('submit', function(e) {
        if (!hasSignature) {
            e.preventDefault();
            alert('Debe proporcionar su firma digital antes de continuar.');
            canvas.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return false;
        }
    });
    
    clearSignature();
});
</script>
@endpush
